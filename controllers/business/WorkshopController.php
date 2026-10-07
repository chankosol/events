<?php
// C:\xampp\htdocs\workshopos\controllers\business\WorkshopController.php

class WorkshopController {
    /**
     * List all workshops for current business
     */
    public static function index(): void {
        requireAuth();
        requirePermission('workshop.view');
        
        if (Auth::isPlatformAdmin()) {
            redirect(APP_URL . '/platform/workshops');
        }

        $businessId = Tenant::id();
        $db = Database::getInstance();
        
        $status  = $_GET['status'] ?? '';
        $search  = trim($_GET['q'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $offset  = ($page - 1) * $perPage;
        
        $where  = 'WHERE w.business_id = ? AND w.deleted_at IS NULL';
        $params = [$businessId];
        
        if ($status) {
            $where  .= ' AND w.status = ?';
            $params[] = $status;
        }
        if ($search) {
            $where  .= ' AND (w.name LIKE ? OR w.trainer_name LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $totalRow = $db->queryOne("SELECT COUNT(*) as c FROM workshops w {$where}", $params);
        $total    = $totalRow ? (int)$totalRow['c'] : 0;

        $queryParams = $params;
        $queryParams[] = $perPage;
        $queryParams[] = $offset;

        $workshops = $db->query(
            "SELECT w.*,
                    (SELECT COUNT(*) FROM registrations r WHERE r.workshop_id = w.id AND r.deleted_at IS NULL) as registration_count,
                    (SELECT COUNT(*) FROM registrations r WHERE r.workshop_id = w.id AND r.status IN ('confirmed','attended','completed')) as confirmed_count,
                    wb.payment_status as billing_status, wb.platform_fee
             FROM workshops w
             LEFT JOIN workshop_billing wb ON wb.workshop_id = w.id
             {$where}
             ORDER BY w.created_at DESC
             LIMIT ? OFFSET ?",
            $queryParams
        );
        
        $totalPages = max(1, (int)ceil($total / $perPage));
        $title = 'My Workshops - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/business/workshops/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }
    
    /**
     * Show workshop creation wizard
     */
    public static function create(): void {
        requireAuth();
        requirePermission('workshop.create');
        
        $db = Database::getInstance();
        $businessId = Tenant::id();
        
        // Get business payment methods
        $paymentMethods = $db->query(
            "SELECT * FROM business_payment_methods WHERE business_id = ? AND status = 'active' ORDER BY sort_order",
            [$businessId]
        );
        
        $step        = max(1, min(5, (int)($_GET['step'] ?? 1)));
        $workshopId  = (int)($_GET['workshop_id'] ?? 0);
        $workshop    = null;
        $branding    = null;
        $customFields = [];
        $tickets     = [];
        $billing     = null;

        if ($workshopId) {
            if (Auth::isPlatformAdmin()) {
                $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND deleted_at IS NULL", [$workshopId]);
                if ($workshop) {
                    $businessId = (int)$workshop['business_id'];
                }
            } else {
                $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
            }
            if ($workshop) {
                $branding = $db->queryOne("SELECT * FROM workshop_branding WHERE workshop_id = ?", [$workshopId]);
                $customFields = $db->query("SELECT * FROM registration_fields WHERE workshop_id = ? ORDER BY sort_order", [$workshopId]);
                $tickets = $db->query("SELECT * FROM tickets WHERE workshop_id = ? ORDER BY sort_order", [$workshopId]);
                $billing = $db->queryOne("SELECT wb.*, pr.name as pricing_tier FROM workshop_billing wb LEFT JOIN platform_pricing_rules pr ON pr.id = wb.pricing_rule_id WHERE wb.workshop_id = ?", [$workshopId]);
            }
        }

        $title  = 'Create Workshop - ' . APP_NAME;
        $errors = Session::flash('errors') ?? [];
        $old    = Session::flash('old') ?? [];
        
        ob_start();
        require VIEWS_PATH . '/business/workshops/create.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }

    /**
     * Store or update wizard steps
     */
    public static function store(): void {
        requireAuth();
        requirePermission('workshop.create');
        requireCsrf();
        
        $businessId = Tenant::id();
        $db = Database::getInstance();
        $step = (int)($_POST['step'] ?? 1);
        $workshopId = (int)($_POST['workshop_id'] ?? 0);
        
        switch ($step) {
            case 1: self::storeStep1($db, $businessId, $workshopId); break;
            case 2: self::storeStep2($db, $businessId, $workshopId); break;
            case 3: self::storeStep3($db, $businessId, $workshopId); break;
            case 4: self::storeStep4($db, $businessId, $workshopId); break;
            case 5: self::storeStep5($db, $businessId, $workshopId); break;
            default: redirect(APP_URL . '/workshops/create?step=1');
        }
    }

    private static function storeStep1(Database $db, int $businessId, int $workshopId): void {
        $data = [
            'name'                    => trim($_POST['name'] ?? ''),
            'description'             => trim($_POST['description'] ?? ''),
            'short_description'       => trim($_POST['short_description'] ?? ''),
            'category'                => trim($_POST['category'] ?? ''),
            'workshop_type'           => in_array($_POST['workshop_type'] ?? '', ['in-person','online','hybrid']) ? $_POST['workshop_type'] : 'in-person',
            'trainer_title'           => trim($_POST['trainer_title'] ?? '') ?: 'គ្រូបណ្តុះបណ្តាល',
            'trainer_name'            => trim($_POST['trainer_name'] ?? ''),
            'trainer_bio'             => trim($_POST['trainer_bio'] ?? ''),
            'organizer'               => trim($_POST['organizer'] ?? ''),
            'start_date'              => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
            'end_date'                => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
            'start_time'              => !empty($_POST['start_time']) ? $_POST['start_time'] : null,
            'end_time'                => !empty($_POST['end_time']) ? $_POST['end_time'] : null,
            'timezone'                => $_POST['timezone'] ?? 'Asia/Phnom_Penh',
            'venue'                   => trim($_POST['venue'] ?? ''),
            'address'                 => trim($_POST['address'] ?? ''),
            'google_maps_url'         => trim($_POST['google_maps_url'] ?? ''),
            'online_meeting_url'      => trim($_POST['online_meeting_url'] ?? ''),
            'capacity'                => max(1, (int)($_POST['capacity'] ?? 50)),
            'language'                => trim($_POST['language'] ?? 'English'),
            'visibility'              => in_array($_POST['visibility'] ?? 'public', ['public','private','unlisted']) ? $_POST['visibility'] : 'public',
            'registration_open_date'  => !empty($_POST['registration_open_date']) ? $_POST['registration_open_date'] : null,
            'registration_close_date' => !empty($_POST['registration_close_date']) ? $_POST['registration_close_date'] : null,
            'requires_approval'       => isset($_POST['requires_approval']) ? 1 : 0,
            'auto_confirm'            => isset($_POST['auto_confirm']) ? 1 : 0,
            'allow_waitlist'          => isset($_POST['allow_waitlist']) ? 1 : 0,
            'payment_mode'            => in_array($_POST['payment_mode'] ?? 'free', ['free','paid','freemium']) ? $_POST['payment_mode'] : 'free',
        ];

        $errors = [];
        if (empty($data['name']))       $errors['name']       = 'Workshop name is required.';
        if (!$data['start_date'])       $errors['start_date'] = 'Start date is required.';
        if ($data['capacity'] < 1)      $errors['capacity']   = 'Capacity must be at least 1.';

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $_POST);
            $redirectUrl = $workshopId 
                ? APP_URL . '/workshops/create?step=1&workshop_id=' . $workshopId 
                : APP_URL . '/workshops/create?step=1';
            redirect($redirectUrl);
        }

        if ($workshopId) {
            $existing = $db->queryOne("SELECT id FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
            if (!$existing) redirect(APP_URL . '/workshops');

            $db->execute(
                "UPDATE workshops SET name=?, description=?, short_description=?, category=?, workshop_type=?,
                 trainer_title=?, trainer_name=?, trainer_bio=?, organizer=?, start_date=?, end_date=?, start_time=?, end_time=?,
                 timezone=?, venue=?, address=?, google_maps_url=?, online_meeting_url=?, capacity=?, language=?,
                 visibility=?, registration_open_date=?, registration_close_date=?, requires_approval=?,
                 auto_confirm=?, allow_waitlist=?, payment_mode=?, updated_at=NOW()
                 WHERE id = ? AND business_id = ?",
                [
                    $data['name'], $data['description'], $data['short_description'], $data['category'], $data['workshop_type'],
                    $data['trainer_title'], $data['trainer_name'], $data['trainer_bio'], $data['organizer'], $data['start_date'], $data['end_date'],
                    $data['start_time'], $data['end_time'], $data['timezone'], $data['venue'], $data['address'],
                    $data['google_maps_url'], $data['online_meeting_url'], $data['capacity'], $data['language'],
                    $data['visibility'], $data['registration_open_date'], $data['registration_close_date'],
                    $data['requires_approval'], $data['auto_confirm'], $data['allow_waitlist'], $data['payment_mode'],
                    $workshopId, $businessId
                ]
            );

            // Re-calculate billing fee if still unpaid
            self::updateBillingCapacity($db, $businessId, $workshopId, $data['capacity']);

            auditLog('workshop_updated', 'workshop', $workshopId);
            if (!empty($_POST['save_only'])) {
                Session::flash('success', 'បានរក្សាទុកព័ត៌មានទូទៅនៃសិក្ខាសាលារួចរាល់!');
                redirect(APP_URL . '/workshops/create?step=1&workshop_id=' . $workshopId);
            }
            redirect(APP_URL . '/workshops/create?step=2&workshop_id=' . $workshopId);
        } else {
            $slug = uniqueSlug($data['name'], 'workshops', 'slug', null, $businessId);
            $db->execute(
                "INSERT INTO workshops (business_id, slug, name, description, short_description, category, workshop_type,
                 trainer_title, trainer_name, trainer_bio, organizer, start_date, end_date, start_time, end_time, timezone, venue,
                 address, google_maps_url, online_meeting_url, capacity, language, visibility, registration_open_date,
                 registration_close_date, requires_approval, auto_confirm, allow_waitlist, payment_mode, status, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?)",
                [
                    $businessId, $slug,
                    $data['name'], $data['description'], $data['short_description'], $data['category'], $data['workshop_type'],
                    $data['trainer_title'], $data['trainer_name'], $data['trainer_bio'], $data['organizer'], $data['start_date'], $data['end_date'],
                    $data['start_time'], $data['end_time'], $data['timezone'], $data['venue'], $data['address'],
                    $data['google_maps_url'], $data['online_meeting_url'], $data['capacity'], $data['language'],
                    $data['visibility'], $data['registration_open_date'], $data['registration_close_date'],
                    $data['requires_approval'], $data['auto_confirm'], $data['allow_waitlist'], $data['payment_mode'],
                    Auth::id()
                ]
            );
            $newWorkshopId = (int)$db->lastInsertId();

            self::createBillingRecord($db, $businessId, $newWorkshopId, $data['capacity']);
            $db->execute("INSERT INTO workshop_branding (workshop_id, business_id) VALUES (?, ?)", [$newWorkshopId, $businessId]);

            // Add standard ticket by default
            $db->execute(
                "INSERT INTO tickets (workshop_id, business_id, name, ticket_type, price, currency, status, sort_order)
                 VALUES (?, ?, 'Standard Ticket', 'standard', ?, 'USD', 'active', 0)",
                [$newWorkshopId, $businessId, $data['payment_mode'] === 'free' ? 0.00 : 20.00]
            );

            auditLog('workshop_created', 'workshop', $newWorkshopId, null, ['name' => $data['name'], 'capacity' => $data['capacity']]);
            if (!empty($_POST['save_only'])) {
                Session::flash('success', 'បានបង្កើត និងរក្សាទុកសិក្ខាសាលារួចរាល់!');
                redirect(APP_URL . '/workshops/create?step=1&workshop_id=' . $newWorkshopId);
            }
            redirect(APP_URL . '/workshops/create?step=2&workshop_id=' . $newWorkshopId);
        }
    }

    private static function createBillingRecord(Database $db, int $businessId, int $workshopId, int $capacity): void {
        $rule = $db->queryOne(
            "SELECT * FROM platform_pricing_rules
             WHERE status = 'active' AND min_capacity <= ?
             AND (max_capacity IS NULL OR max_capacity >= ?)
             ORDER BY sort_order ASC LIMIT 1",
            [$capacity, $capacity]
        );

        $fee       = $rule ? (float)$rule['price'] : 10.00;
        $currency  = $rule ? $rule['currency'] : 'USD';
        $ruleId    = $rule ? $rule['id'] : null;
        $invoiceNo = generateInvoiceNumber();

        $db->execute(
            "INSERT INTO workshop_billing (business_id, workshop_id, pricing_rule_id, capacity, platform_fee, currency, payment_status, invoice_number, due_date)
             VALUES (?, ?, ?, ?, ?, ?, 'unpaid', ?, DATE_ADD(CURDATE(), INTERVAL 7 DAY))",
            [$businessId, $workshopId, $ruleId, $capacity, $fee, $currency, $invoiceNo]
        );
        $billingId = (int)$db->lastInsertId();

        $db->execute(
            "INSERT INTO platform_invoices (invoice_number, business_id, workshop_billing_id, invoice_type, description, amount, currency, status, issue_date, due_date)
             VALUES (?, ?, ?, 'workshop_activation', 'Workshop Activation Fee', ?, ?, 'unpaid', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY))",
            [$invoiceNo, $businessId, $billingId, $fee, $currency]
        );
    }

    private static function updateBillingCapacity(Database $db, int $businessId, int $workshopId, int $capacity): void {
        $billing = $db->queryOne("SELECT * FROM workshop_billing WHERE workshop_id = ?", [$workshopId]);
        if ($billing && $billing['payment_status'] === 'unpaid') {
            $rule = $db->queryOne(
                "SELECT * FROM platform_pricing_rules
                 WHERE status = 'active' AND min_capacity <= ?
                 AND (max_capacity IS NULL OR max_capacity >= ?)
                 ORDER BY sort_order ASC LIMIT 1",
                [$capacity, $capacity]
            );
            if ($rule) {
                $db->execute(
                    "UPDATE workshop_billing SET capacity = ?, platform_fee = ?, pricing_rule_id = ?, updated_at = NOW() WHERE id = ?",
                    [$capacity, $rule['price'], $rule['id'], $billing['id']]
                );
                $db->execute(
                    "UPDATE platform_invoices SET amount = ? WHERE workshop_billing_id = ? AND status = 'unpaid'",
                    [$rule['price'], $billing['id']]
                );
            }
        }
    }

    private static function storeStep2(Database $db, int $businessId, int $workshopId): void {
        $workshop = $db->queryOne("SELECT id FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
        if (!$workshop) redirect(APP_URL . '/workshops');

        $themeColor = preg_match('/^#[0-9A-Fa-f]{6}$/', $_POST['theme_color'] ?? '') ? $_POST['theme_color'] : '#0d6efd';

        $coverPath = null;
        if (!empty($_FILES['cover_image']['name'])) {
            $upload = uploadFile($_FILES['cover_image'], 'cover', $businessId, $workshopId);
            if ($upload['success']) $coverPath = $upload['path'];
        }

        $logoPath = null;
        if (!empty($_FILES['logo']['name'])) {
            $upload = uploadFile($_FILES['logo'], 'logo', $businessId, $workshopId);
            if ($upload['success']) $logoPath = $upload['path'];
        }

        $bannerPath = null;
        if (!empty($_FILES['banner']['name'])) {
            $upload = uploadFile($_FILES['banner'], 'banner', $businessId, $workshopId);
            if ($upload['success']) $bannerPath = $upload['path'];
        }

        $branding = $db->queryOne("SELECT id FROM workshop_branding WHERE workshop_id = ?", [$workshopId]);
        if ($branding) {
            $updates = ['theme_color = ?'];
            $params  = [$themeColor];
            if ($coverPath) { $updates[] = 'cover_image = ?'; $params[] = $coverPath; }
            if ($logoPath)  { $updates[] = 'logo = ?';        $params[] = $logoPath; }
            if ($bannerPath){ $updates[] = 'banner = ?';      $params[] = $bannerPath; }
            $params[] = $workshopId;
            $db->execute("UPDATE workshop_branding SET " . implode(', ', $updates) . " WHERE workshop_id = ?", $params);
        } else {
            $db->execute(
                "INSERT INTO workshop_branding (workshop_id, business_id, cover_image, logo, banner, theme_color) VALUES (?, ?, ?, ?, ?, ?)",
                [$workshopId, $businessId, $coverPath, $logoPath, $bannerPath, $themeColor]
            );
        }

        auditLog('workshop_branding_updated', 'workshop_branding', $workshopId);
        if (!empty($_POST['save_only'])) {
            Session::flash('success', 'បានរក្សាទុកការរចនា និងស្លាកសញ្ញារួចរាល់!');
            redirect(APP_URL . '/workshops/create?step=2&workshop_id=' . $workshopId);
        }
        redirect(APP_URL . '/workshops/create?step=3&workshop_id=' . $workshopId);
    }

    private static function storeStep3(Database $db, int $businessId, int $workshopId): void {
        $workshop = $db->queryOne("SELECT id FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
        if (!$workshop) redirect(APP_URL . '/workshops');

        $fields = $_POST['fields'] ?? [];
        $existingFields = $db->query(
            "SELECT * FROM registration_fields WHERE workshop_id = ? AND business_id = ?",
            [$workshopId, $businessId]
        );
        $existingById = array_column($existingFields, null, 'id');
        $retainedIds = [];

        foreach ($fields as $idx => $field) {
            if (empty($field['label'])) continue;
            $fieldName = slugify($field['label']) . '_' . $idx;
            $options = !empty($field['options']) ? array_map('trim', explode(',', $field['options'])) : null;
            $type = $field['type'] ?? 'text';
            $isRequired = isset($field['required']) ? 1 : 0;
            $isInternal = isset($field['internal']) ? 1 : 0;
            $placeholder = $field['placeholder'] ?? null;
            $helpText = $field['help_text'] ?? null;

            $fieldId = (int)($field['id'] ?? 0);
            if ($fieldId > 0 && isset($existingById[$fieldId])) {
                $db->execute(
                    "UPDATE registration_fields 
                     SET field_name = ?, field_label = ?, field_type = ?, field_options = ?, is_required = ?, is_internal = ?, placeholder = ?, help_text = ?, sort_order = ?, status = 'active'
                     WHERE id = ? AND workshop_id = ? AND business_id = ?",
                    [$fieldName, $field['label'], $type, $options ? json_encode($options) : null, $isRequired, $isInternal, $placeholder, $helpText, $idx, $fieldId, $workshopId, $businessId]
                );
                $retainedIds[] = $fieldId;
            } else {
                $db->execute(
                    "INSERT INTO registration_fields (workshop_id, business_id, field_name, field_label, field_type, field_options, is_required, is_internal, placeholder, help_text, sort_order)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$workshopId, $businessId, $fieldName, $field['label'], $type, $options ? json_encode($options) : null, $isRequired, $isInternal, $placeholder, $helpText, $idx]
                );
                $retainedIds[] = (int)$db->lastInsertId();
            }
        }

        // Clean up unreferenced fields
        foreach ($existingFields as $ex) {
            if (!in_array((int)$ex['id'], $retainedIds, true)) {
                $hasAnswers = (int)$db->queryOne("SELECT COUNT(*) as c FROM registration_answers WHERE field_id = ?", [$ex['id']])['c'];
                if ($hasAnswers > 0) {
                    $db->execute("UPDATE registration_fields SET status = 'inactive' WHERE id = ?", [$ex['id']]);
                } else {
                    $db->execute("DELETE FROM registration_fields WHERE id = ?", [$ex['id']]);
                }
            }
        }

        auditLog('registration_fields_updated', 'registration_fields', $workshopId);
        if (!empty($_POST['save_only'])) {
            Session::flash('success', 'បានរក្សាទុកទម្រង់ព័ត៌មានចុះឈ្មោះរួចរាល់!');
            redirect(APP_URL . '/workshops/create?step=3&workshop_id=' . $workshopId);
        }
        redirect(APP_URL . '/workshops/create?step=4&workshop_id=' . $workshopId);
    }

    private static function storeStep4(Database $db, int $businessId, int $workshopId): void {
        $workshop = $db->queryOne("SELECT id FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
        if (!$workshop) redirect(APP_URL . '/workshops');

        $tickets = $_POST['tickets'] ?? [];
        if (!empty($tickets)) {
            $existingTickets = $db->query(
                "SELECT * FROM tickets WHERE workshop_id = ? AND business_id = ?",
                [$workshopId, $businessId]
            );
            $existingById = array_column($existingTickets, null, 'id');
            $retainedIds = [];

            foreach ($tickets as $idx => $ticket) {
                if (empty($ticket['name'])) continue;
                $price = max(0, (float)($ticket['price'] ?? 0));
                $type = in_array($ticket['type'] ?? '', ['standard','vip','early_bird','student','group','complimentary','custom']) ? $ticket['type'] : 'standard';
                $currency = $ticket['currency'] ?? 'USD';
                $capacity = !empty($ticket['capacity']) ? (int)$ticket['capacity'] : null;
                $description = $ticket['description'] ?? '';

                $ticketId = (int)($ticket['id'] ?? 0);
                if ($ticketId > 0 && isset($existingById[$ticketId])) {
                    // Update existing ticket in-place
                    $db->execute(
                        "UPDATE tickets 
                         SET name = ?, description = ?, ticket_type = ?, price = ?, currency = ?, capacity = ?, status = 'active', sort_order = ?
                         WHERE id = ? AND workshop_id = ? AND business_id = ?",
                        [$ticket['name'], $description, $type, $price, $currency, $capacity, $idx, $ticketId, $workshopId, $businessId]
                    );
                    $retainedIds[] = $ticketId;
                } else {
                    // Insert new ticket
                    $db->execute(
                        "INSERT INTO tickets (workshop_id, business_id, name, description, ticket_type, price, currency, capacity, status, sort_order)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)",
                        [$workshopId, $businessId, $ticket['name'], $description, $type, $price, $currency, $capacity, $idx]
                    );
                    $retainedIds[] = (int)$db->lastInsertId();
                }
            }

            // For existing tickets not in retained list
            foreach ($existingTickets as $ex) {
                if (!in_array((int)$ex['id'], $retainedIds, true)) {
                    $hasRegs = (int)$db->queryOne(
                        "SELECT COUNT(*) as c FROM registrations WHERE ticket_id = ? AND deleted_at IS NULL",
                        [$ex['id']]
                    )['c'];
                    if ($hasRegs > 0) {
                        $db->execute("UPDATE tickets SET status = 'inactive' WHERE id = ?", [$ex['id']]);
                    } else {
                        $db->execute("DELETE FROM tickets WHERE id = ?", [$ex['id']]);
                    }
                }
            }
        }

        auditLog('tickets_updated', 'tickets', $workshopId);
        if (!empty($_POST['save_only'])) {
            Session::flash('success', 'បានរក្សាទុកប្រភេទសំបុត្រ និងតម្លៃរួចរាល់!');
            redirect(APP_URL . '/workshops/create?step=4&workshop_id=' . $workshopId);
        }
        redirect(APP_URL . '/workshops/create?step=5&workshop_id=' . $workshopId);
    }

    private static function storeStep5(Database $db, int $businessId, int $workshopId): void {
        $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
        if (!$workshop) redirect(APP_URL . '/workshops');
        
        if (!empty($_POST['save_only'])) {
            Session::flash('success', 'បានរក្សាទុកព័ត៌មានសិក្ខាសាលារួចរាល់!');
            redirect(APP_URL . '/workshops/create?step=5&workshop_id=' . $workshopId);
        }

        Session::flash('success', 'បានបញ្ចប់ការកំណត់រចនាសម្ព័ន្ធ! សូមបន្តទៅកាន់ការទូទាត់ប្រព័ន្ធ។');
        redirect(APP_URL . '/workshops/' . $workshopId . '/activate');
    }

    /**
     * Show workshop detail management page
     */
    public static function show(int $id): void {
        requireAuth();
        requirePermission('workshop.view');
        $businessId = Tenant::id();
        $db = Database::getInstance();

        if (Auth::isPlatformAdmin()) {
            $workshop = $db->queryOne(
                "SELECT w.*, wb.cover_image, wb.logo, wb.banner, wb.theme_color
                 FROM workshops w
                 LEFT JOIN workshop_branding wb ON wb.workshop_id = w.id
                 WHERE w.id = ? AND w.deleted_at IS NULL",
                [$id]
            );
            if ($workshop) {
                $businessId = (int)$workshop['business_id'];
            }
        } else {
            $workshop = $db->queryOne(
                "SELECT w.*, wb.cover_image, wb.logo, wb.banner, wb.theme_color
                 FROM workshops w
                 LEFT JOIN workshop_branding wb ON wb.workshop_id = w.id
                 WHERE w.id = ? AND w.business_id = ? AND w.deleted_at IS NULL",
                [$id, $businessId]
            );
        }
        if (!$workshop) { 
            http_response_code(404); 
            die('Workshop not found.'); 
        }

        $billing = $db->queryOne("SELECT wb.*, pr.name as pricing_tier FROM workshop_billing wb LEFT JOIN platform_pricing_rules pr ON pr.id = wb.pricing_rule_id WHERE wb.workshop_id = ?", [$id]);
        $sessions = $db->query("SELECT * FROM workshop_sessions WHERE workshop_id = ? ORDER BY session_date, start_time", [$id]);
        $tickets  = $db->query("SELECT * FROM tickets WHERE workshop_id = ? ORDER BY sort_order", [$id]);
        $customFields = $db->query("SELECT * FROM registration_fields WHERE workshop_id = ? ORDER BY sort_order", [$id]);
        
        $stats = $db->queryOne(
            "SELECT
               COUNT(*) as total_registrations,
               SUM(CASE WHEN status IN ('confirmed','attended','completed') THEN 1 ELSE 0 END) as confirmed,
               SUM(CASE WHEN payment_status IN ('paid','paid_cash','complimentary','waived') THEN 1 ELSE 0 END) as paid,
               SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_payment,
               SUM(CASE WHEN status = 'waitlisted' THEN 1 ELSE 0 END) as waitlisted
             FROM registrations WHERE workshop_id = ? AND deleted_at IS NULL",
            [$id]
        );

        $title = $workshop['name'] . ' - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/business/workshops/show.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }

    /**
     * Show workshop activation (billing) page
     */
    public static function activate(int $id): void {
        requireAuth();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        if (Auth::isPlatformAdmin()) {
            $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND deleted_at IS NULL", [$id]);
            if ($workshop) {
                $businessId = (int)$workshop['business_id'];
            }
        } else {
            $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$id, $businessId]);
        }
        if (!$workshop) { 
            http_response_code(404); 
            die('Workshop not found.'); 
        }

        $billing = $db->queryOne(
            "SELECT wb.*, pr.name as pricing_tier 
             FROM workshop_billing wb 
             LEFT JOIN platform_pricing_rules pr ON pr.id = wb.pricing_rule_id 
             WHERE wb.workshop_id = ?", 
            [$id]
        );

        if ($billing && (empty($billing['bakong_md5']) || empty($billing['bakong_qr_text']))) {
            $khqrData = bakong_create_khqr($billing['invoice_number'], (float)$billing['platform_fee'], $billing['currency']);
            $db->execute(
                "UPDATE workshop_billing SET bakong_md5 = ?, bakong_qr_text = ? WHERE id = ?",
                [$khqrData['md5'], $khqrData['qr_text'], $billing['id']]
            );
            $billing['bakong_md5'] = $khqrData['md5'];
            $billing['bakong_qr_text'] = $khqrData['qr_text'];
        }

        $paymentProofs = $billing ? $db->query("SELECT * FROM platform_payment_proofs WHERE workshop_billing_id = ? ORDER BY submitted_at DESC", [$billing['id']]) : [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireCsrf();
            if (!empty($_FILES['proof_file']['name']) && $billing) {
                $upload = uploadFile($_FILES['proof_file'], 'platform_proof', $businessId);
                if ($upload['success']) {
                    $invoice = $db->queryOne("SELECT id FROM platform_invoices WHERE workshop_billing_id = ?", [$billing['id']]);
                    $db->execute(
                        "INSERT INTO platform_payment_proofs (workshop_billing_id, business_id, invoice_id, proof_file, amount_claimed, currency, payment_method, transaction_reference, payment_date, notes)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                        [
                            $billing['id'], $businessId,
                            $invoice ? $invoice['id'] : null,
                            $upload['path'],
                            (float)($_POST['amount_claimed'] ?? $billing['platform_fee']),
                            $billing['currency'],
                            trim($_POST['payment_method'] ?? 'Bank QR'),
                            trim($_POST['transaction_reference'] ?? ''),
                            $_POST['payment_date'] ?? date('Y-m-d'),
                            trim($_POST['notes'] ?? ''),
                        ]
                    );
                    $db->execute("UPDATE workshop_billing SET payment_status='pending' WHERE id = ?", [$billing['id']]);
                    $db->execute("UPDATE workshops SET status='pending_payment' WHERE id = ?", [$id]);
                    auditLog('platform_payment_proof_uploaded', 'workshop_billing', $billing['id']);
                    Session::flash('success', 'Payment proof submitted! Platform finance will review and activate your workshop.');
                    redirect(APP_URL . '/workshops/' . $id . '/activate');
                } else {
                    Session::flash('error', $upload['message'] ?? 'Upload failed.');
                }
            }
        }

        $title = 'Activate Workshop - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/business/workshops/activate.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }

    /**
     * Auto-confirm Bakong KHQR payment instantly
     */
    public static function confirmBakong(int $id): void {
        requireAuth();
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$id, $businessId]);
        if (!$workshop) {
            jsonResponse(false, 'រកមិនឃើញសិក្ខាសាលាទេ។');
        }

        $billing = $db->queryOne("SELECT * FROM workshop_billing WHERE workshop_id = ?", [$id]);
        if (!$billing) {
            jsonResponse(false, 'រកមិនឃើញវិក្កយបត្រដំណើរការប្រព័ន្ធទេ។');
        }

        if ($billing['payment_status'] === 'paid') {
            jsonResponse(true, 'វិក្កយបត្រនេះត្រូវបានបង់រួចរាល់ហើយ!', ['already_paid' => true]);
        }

        $trxRef = 'BK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

        $db->beginTransaction();
        try {
            // Update workshop billing to paid
            $db->execute(
                "UPDATE workshop_billing 
                 SET payment_status = 'paid', payment_method = 'Bakong KHQR', transaction_reference = ?, paid_at = NOW() 
                 WHERE id = ?",
                [$trxRef, $billing['id']]
            );

            // Update platform invoice to paid
            $db->execute(
                "UPDATE platform_invoices 
                 SET status = 'paid', paid_date = CURDATE() 
                 WHERE workshop_billing_id = ?",
                [$billing['id']]
            );

            // Activate workshop
            $db->execute("UPDATE workshops SET status = 'active' WHERE id = ?", [$id]);

            // Create auto-verified payment proof record for audit trail
            $db->execute(
                "INSERT INTO platform_payment_proofs (
                    workshop_billing_id, business_id, proof_file, amount_claimed, currency, 
                    payment_method, transaction_reference, payment_date, notes, 
                    status, reviewed_by, reviewed_at, review_notes, submitted_at
                 ) VALUES (?, ?, 'bakong_khqr_auto_verified', ?, ?, 'Bakong KHQR', ?, CURDATE(), 'Bakong KHQR Instant Auto-Confirmed', 'approved', ?, NOW(), 'Auto confirmed via Bakong KHQR', NOW())",
                [
                    $billing['id'], $businessId, $billing['platform_fee'], $billing['currency'],
                    $trxRef, Auth::id()
                ]
            );

            auditLog('bakong_khqr_auto_confirmed', 'workshop_billing', $billing['id'], null, ['trx' => $trxRef, 'amount' => $billing['platform_fee']], $businessId);

            $db->commit();
            jsonResponse(true, 'ការទូទាត់ Bakong KHQR ទទួលបានជោគជ័យ! សិក្ខាសាលាត្រូវបានបើកដំណើរការរួចរាល់។', [
                'transaction_reference' => $trxRef,
                'paid_at' => date('Y-m-d H:i:s'),
                'status' => 'paid'
            ]);
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log($e->getMessage());
            jsonResponse(false, 'មានបញ្ហាក្នុងការផ្ទៀងផ្ទាត់ការទូទាត់៖ ' . $e->getMessage());
        }
    }

    /**
     * Check real-time payment status via AJAX polling & Bakong NBC Open API
     */
    public static function checkPaymentStatus(int $id): void {
        requireAuth();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $billing = $db->queryOne("SELECT * FROM workshop_billing WHERE workshop_id = ? AND business_id = ?", [$id, $businessId]);
        if (!$billing) {
            jsonResponse(false, 'Not found');
        }

        if ($billing['payment_status'] === 'paid') {
            jsonResponse(true, 'Status checked', [
                'payment_status' => 'paid',
                'is_paid' => true,
                'paid_at' => $billing['paid_at']
            ]);
        }

        // Live check against Bakong NBC Open API using multi-token limiter rotation
        if (!empty($billing['bakong_md5'])) {
            $bakongRes = bakong_check_payment_by_md5($billing['bakong_md5']);
            if (!empty($bakongRes['paid'])) {
                $trxRef = $bakongRes['data']['hash'] ?? ('BK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4))));
                $db->beginTransaction();
                try {
                    $db->execute(
                        "UPDATE workshop_billing SET payment_status = 'paid', payment_method = 'Bakong KHQR', transaction_reference = ?, paid_at = NOW() WHERE id = ?",
                        [$trxRef, $billing['id']]
                    );
                    $db->execute("UPDATE platform_invoices SET status = 'paid', paid_date = CURDATE() WHERE workshop_billing_id = ?", [$billing['id']]);
                    $db->execute("UPDATE workshops SET status = 'active' WHERE id = ?", [$id]);
                    $db->execute(
                        "INSERT INTO platform_payment_proofs (workshop_billing_id, business_id, proof_file, amount_claimed, currency, payment_method, transaction_reference, payment_date, notes, status, reviewed_by, reviewed_at, review_notes, submitted_at)
                         VALUES (?, ?, 'bakong_api_verified', ?, ?, 'Bakong KHQR', ?, CURDATE(), 'Auto verified via Bakong NBC Open API', 'approved', NULL, NOW(), 'Bakong live verification', NOW())",
                        [$billing['id'], $businessId, $billing['platform_fee'], $billing['currency'], $trxRef]
                    );
                    $db->commit();
                    auditLog('bakong_khqr_api_verified', 'workshop_billing', $billing['id'], null, ['trx' => $trxRef, 'amount' => $billing['platform_fee']], $businessId);

                    jsonResponse(true, 'Payment verified via Bakong NBC Open API', [
                        'payment_status' => 'paid',
                        'is_paid' => true,
                        'paid_at' => date('Y-m-d H:i:s'),
                        'transaction_reference' => $trxRef
                    ]);
                } catch (Exception $e) {
                    $db->rollback();
                }
            }
        }

        jsonResponse(true, 'Status checked', [
            'payment_status' => $billing['payment_status'],
            'is_paid' => false,
            'paid_at' => null
        ]);
    }

    /**
     * Publish workshop to public registration
     */
    public static function publish(int $id): void {
        requireAuth();
        requirePermission('workshop.publish');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        if (Auth::isPlatformAdmin()) {
            $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND deleted_at IS NULL", [$id]);
            if ($workshop) {
                $businessId = (int)$workshop['business_id'];
            }
        } else {
            $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$id, $businessId]);
        }
        if (!$workshop) {
            Session::flash('error', 'Workshop not found.');
            redirect(APP_URL . '/workshops');
        }

        $billing = $db->queryOne("SELECT * FROM workshop_billing WHERE workshop_id = ?", [$id]);
        if (!$billing || !in_array($billing['payment_status'], ['paid', 'waived'])) {
            Session::flash('error', 'Workshop cannot be published yet. The platform activation fee must be paid and verified first.');
            redirect(APP_URL . '/workshops/' . $id . '/activate');
        }

        $db->execute("UPDATE workshops SET status='registration_open', updated_at=NOW() WHERE id = ?", [$id]);
        auditLog('workshop_published', 'workshop', $id);
        Session::flash('success', 'Workshop is now live! Public registration is open.');
        redirect(APP_URL . '/workshops/' . $id);
    }

    /**
     * Duplicate workshop
     */
    public static function duplicate(int $id): void {
        requireAuth();
        requirePermission('workshop.duplicate');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $source = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$id, $businessId]);
        if (!$source) {
            jsonResponse(false, 'Workshop not found.', [], 404);
        }

        try {
            $db->beginTransaction();

            $newSlug = uniqueSlug($source['name'] . ' Copy', 'workshops', 'slug', null, $businessId);
            $db->execute(
                "INSERT INTO workshops (business_id, slug, name, description, short_description, category, workshop_type,
                 trainer_title, trainer_name, trainer_bio, organizer, start_date, end_date, start_time, end_time, timezone, venue,
                 address, google_maps_url, online_meeting_url, capacity, language, visibility, requires_approval,
                 auto_confirm, allow_waitlist, payment_mode, status, duplicated_from, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, ?)",
                [
                    $businessId, $newSlug, '[Copy] ' . $source['name'], $source['description'], $source['short_description'],
                    $source['category'], $source['workshop_type'], $source['trainer_title'] ?? 'គ្រូបណ្តុះបណ្តាល', $source['trainer_name'], $source['trainer_bio'],
                    $source['organizer'], null, null, $source['start_time'], $source['end_time'], $source['timezone'],
                    $source['venue'], $source['address'], $source['google_maps_url'], $source['online_meeting_url'],
                    $source['capacity'], $source['language'], $source['visibility'], $source['requires_approval'],
                    $source['auto_confirm'], $source['allow_waitlist'], $source['payment_mode'], $id, Auth::id()
                ]
            );
            $newId = (int)$db->lastInsertId();

            // Copy branding
            $branding = $db->queryOne("SELECT * FROM workshop_branding WHERE workshop_id = ?", [$id]);
            if ($branding) {
                $db->execute(
                    "INSERT INTO workshop_branding (workshop_id, business_id, cover_image, logo, banner, theme_color, custom_registration_image)
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$newId, $businessId, $branding['cover_image'], $branding['logo'], $branding['banner'], $branding['theme_color'], $branding['custom_registration_image']]
                );
            }

            // Copy registration fields
            $fields = $db->query("SELECT * FROM registration_fields WHERE workshop_id = ?", [$id]);
            foreach ($fields as $f) {
                $db->execute(
                    "INSERT INTO registration_fields (workshop_id, business_id, field_name, field_label, field_type, field_options, is_required, is_internal, placeholder, help_text, sort_order)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$newId, $businessId, $f['field_name'], $f['field_label'], $f['field_type'], $f['field_options'], $f['is_required'], $f['is_internal'], $f['placeholder'], $f['help_text'], $f['sort_order']]
                );
            }

            // Copy tickets
            $tickets = $db->query("SELECT * FROM tickets WHERE workshop_id = ?", [$id]);
            foreach ($tickets as $t) {
                $db->execute(
                    "INSERT INTO tickets (workshop_id, business_id, name, description, ticket_type, price, currency, capacity, status, sort_order)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)",
                    [$newId, $businessId, $t['name'], $t['description'], $t['ticket_type'], $t['price'], $t['currency'], $t['capacity'], $t['sort_order']]
                );
            }

            // Create new billing record
            self::createBillingRecord($db, $businessId, $newId, $source['capacity']);

            $db->commit();
            auditLog('workshop_duplicated', 'workshop', $newId, null, ['source_id' => $id]);
            jsonResponse(true, 'Workshop duplicated successfully.', ['redirect' => APP_URL . '/workshops/' . $newId]);

        } catch (Exception $e) {
            $db->rollback();
            error_log('Duplicate workshop failed: ' . $e->getMessage());
            jsonResponse(false, 'Failed to duplicate workshop.');
        }
    }

    /**
     * Upgrade capacity
     */
    public static function upgradeCapacity(int $id): void {
        requireAuth();
        requirePermission('workshop.edit');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$id, $businessId]);
        $billing  = $db->queryOne("SELECT * FROM workshop_billing WHERE workshop_id = ?", [$id]);

        if (!$workshop || !$billing) {
            jsonResponse(false, 'Workshop billing record not found.');
        }

        $newCapacity = max((int)($_POST['new_capacity'] ?? 0), $workshop['capacity'] + 1);
        
        $newRule = $db->queryOne(
            "SELECT * FROM platform_pricing_rules WHERE status='active' AND min_capacity <= ? AND (max_capacity IS NULL OR max_capacity >= ?) ORDER BY sort_order ASC LIMIT 1",
            [$newCapacity, $newCapacity]
        );

        if (!$newRule) {
            jsonResponse(false, 'No pricing rule found for requested capacity.');
        }

        $oldFee     = (float)$billing['platform_fee'];
        $newFee     = (float)$newRule['price'];
        $upgradeFee = max(0, $newFee - $oldFee);

        if (!empty($_POST['confirm'])) {
            $db->beginTransaction();
            try {
                $db->execute(
                    "INSERT INTO capacity_upgrades (workshop_billing_id, workshop_id, business_id, old_capacity, new_capacity, old_fee, new_fee, upgrade_fee, currency, old_pricing_rule_id, new_pricing_rule_id, payment_status, created_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid', ?)",
                    [$billing['id'], $id, $businessId, $workshop['capacity'], $newCapacity, $oldFee, $newFee, $upgradeFee, $newRule['currency'], $billing['pricing_rule_id'], $newRule['id'], Auth::id()]
                );

                $db->execute("UPDATE workshops SET capacity = ?, updated_at=NOW() WHERE id = ?", [$newCapacity, $id]);
                $db->execute("UPDATE workshop_billing SET capacity = ?, platform_fee = ?, pricing_rule_id = ?, updated_at=NOW() WHERE id = ?",
                    [$newCapacity, $newFee, $newRule['id'], $billing['id']]);

                $db->commit();
                auditLog('capacity_upgraded', 'workshop', $id, ['capacity' => $workshop['capacity']], ['capacity' => $newCapacity, 'upgrade_fee' => $upgradeFee]);
                jsonResponse(true, "Capacity upgraded to {$newCapacity} participants!", ['new_capacity' => $newCapacity]);
            } catch (Exception $e) {
                $db->rollback();
                jsonResponse(false, 'Upgrade failed. Please try again.');
            }
        } else {
            jsonResponse(true, 'Upgrade calculation', [
                'old_capacity' => $workshop['capacity'],
                'new_capacity' => $newCapacity,
                'old_fee'      => $oldFee,
                'new_fee'      => $newFee,
                'upgrade_fee'  => $upgradeFee,
                'currency'     => $newRule['currency'],
            ]);
        }
    }

    public static function edit(int $id): void {
        redirect(APP_URL . '/workshops/create?step=1&workshop_id=' . $id);
    }
    
    public static function update(int $id): void {
        $_POST['workshop_id'] = $id;
        $_POST['step'] = 1;
        self::store();
    }
}
