<?php
// controllers/business/DelegationController.php

class DelegationController {
    
    /**
     * List and manage delegations for a workshop
     */
    public static function index(int $workshopId): void {
        requireAuth();
        requirePermission('workshop.view');
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $workshop = $db->queryOne(
            "SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL",
            [$workshopId, $businessId]
        );
        if (!$workshop) {
            http_response_code(404);
            die('Workshop not found.');
        }

        // Get all delegations with registration and attendance stats
        $delegations = $db->query(
            "SELECT d.*,
                    COUNT(r.id) as registered_count,
                    SUM(CASE WHEN r.status IN ('confirmed','attended') THEN 1 ELSE 0 END) as confirmed_count,
                    SUM(CASE WHEN a.checked_in_at IS NOT NULL THEN 1 ELSE 0 END) as attended_count,
                    SUM(CASE WHEN ad.id IS NOT NULL THEN 1 ELSE 0 END) as disbursed_count,
                    COALESCE(SUM(ad.amount), 0) as disbursed_amount
             FROM workshop_delegations d
             LEFT JOIN registrations r ON r.delegation_id = d.id AND r.deleted_at IS NULL
             LEFT JOIN attendance a ON a.registration_id = r.id AND a.checked_in_at IS NOT NULL
             LEFT JOIN allowance_disbursements ad ON ad.registration_id = r.id
             WHERE d.workshop_id = ? AND d.business_id = ?
             GROUP BY d.id
             ORDER BY d.province ASC",
            [$workshopId, $businessId]
        );

        // Calculate summary stats
        $stats = [
            'total_delegations' => count($delegations),
            'total_quota'       => array_sum(array_column($delegations, 'quota_seats')),
            'total_registered'  => array_sum(array_column($delegations, 'registered_count')),
            'total_attended'    => array_sum(array_column($delegations, 'attended_count')),
            'total_disbursed'   => array_sum(array_column($delegations, 'disbursed_count')),
            'disbursed_amount'  => array_sum(array_column($delegations, 'disbursed_amount')),
        ];

        // All registered members under this workshop for substitution selection
        $members = $db->query(
            "SELECT r.id, r.registration_code, r.status, p.name, p.phone, p.province, d.province as delegation_province, d.id as delegation_id
             FROM registrations r
             JOIN participants p ON p.id = r.participant_id
             LEFT JOIN workshop_delegations d ON d.id = r.delegation_id
             WHERE r.workshop_id = ? AND r.deleted_at IS NULL
             ORDER BY p.name ASC",
            [$workshopId]
        );

        $provinces = cambodiaProvinces();
        $title = 'គ្រប់គ្រងកូតា និងប្រតិភូខេត្ត - ' . $workshop['name'];

        $defaultDelegationConfig = [
            'show_phone'           => 1,
            'require_phone'        => 1,
            'show_gender'          => 1,
            'require_gender'       => 0,
            'show_position'        => 0,
            'require_position'     => 0,
            'show_organization'    => 0,
            'require_organization' => 0,
            'show_id_card'         => 1,
            'require_id_card'      => 0,
            'show_bank_name'       => 1,
            'require_bank_name'    => 0,
            'show_bank_account'    => 1,
            'require_bank_account' => 0,
            'show_email'           => 0,
            'require_email'        => 0,
        ];

        $delegationFormFields = $defaultDelegationConfig;
        if (!empty($workshop['delegation_form_fields'])) {
            $saved = is_string($workshop['delegation_form_fields'])
                ? json_decode($workshop['delegation_form_fields'], true)
                : $workshop['delegation_form_fields'];
            if (is_array($saved)) {
                $delegationFormFields = array_merge($defaultDelegationConfig, $saved);
            }
        }

        ob_start();
        require VIEWS_PATH . '/business/delegations/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }

    /**
     * Save custom delegation form field settings
     */
    public static function saveFormFields(int $workshopId): void {
        requireAuth();
        requirePermission('workshop.edit');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $workshop = $db->queryOne(
            "SELECT id FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL",
            [$workshopId, $businessId]
        );
        if (!$workshop) {
            http_response_code(404);
            die('Workshop not found.');
        }

        $config = [
            'show_phone'           => isset($_POST['show_phone']) ? 1 : 0,
            'require_phone'        => isset($_POST['require_phone']) ? 1 : 0,
            'show_gender'          => isset($_POST['show_gender']) ? 1 : 0,
            'require_gender'       => isset($_POST['require_gender']) ? 1 : 0,
            'show_position'        => isset($_POST['show_position']) ? 1 : 0,
            'require_position'     => isset($_POST['require_position']) ? 1 : 0,
            'show_organization'    => isset($_POST['show_organization']) ? 1 : 0,
            'require_organization' => isset($_POST['require_organization']) ? 1 : 0,
            'show_id_card'         => isset($_POST['show_id_card']) ? 1 : 0,
            'require_id_card'      => isset($_POST['require_id_card']) ? 1 : 0,
            'show_bank_name'       => isset($_POST['show_bank_name']) ? 1 : 0,
            'require_bank_name'    => isset($_POST['require_bank_name']) ? 1 : 0,
            'show_bank_account'    => isset($_POST['show_bank_account']) ? 1 : 0,
            'require_bank_account' => isset($_POST['require_bank_account']) ? 1 : 0,
            'show_email'           => isset($_POST['show_email']) ? 1 : 0,
            'require_email'        => isset($_POST['require_email']) ? 1 : 0,
        ];

        foreach (['phone', 'gender', 'position', 'organization', 'id_card', 'bank_name', 'bank_account', 'email'] as $f) {
            if (empty($config['show_' . $f])) {
                $config['require_' . $f] = 0;
            }
        }

        $db->execute(
            "UPDATE workshops SET delegation_form_fields = ? WHERE id = ? AND business_id = ?",
            [json_encode($config), $workshopId, $businessId]
        );

        Session::flash('success', 'បានរក្សាទុកការកំណត់ទម្រង់ប្រតិភូជោគជ័យ!');
        redirect(APP_URL . "/workshops/{$workshopId}/delegations");
    }

    /**
     * Auto-generate all 25 Cambodian provinces for this workshop
     */
    public static function autoGenerate(int $workshopId): void {
        requireAuth();
        requirePermission('workshop.edit');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $workshop = $db->queryOne("SELECT id FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
        if (!$workshop) {
            jsonResponse(false, 'រកមិនឃើញសិក្ខាសាលាទេ', [], 404);
        }

        $quotaPerProvince = max(1, (int)($_POST['quota_per_province'] ?? 5));
        $provinces = cambodiaProvinces();
        $insertedCount = 0;

        foreach ($provinces as $prov) {
            // Check if province already exists
            $exists = $db->queryOne(
                "SELECT id FROM workshop_delegations WHERE workshop_id = ? AND province = ?",
                [$workshopId, $prov]
            );
            if (!$exists) {
                $token = bin2hex(random_bytes(24));
                $db->execute(
                    "INSERT INTO workshop_delegations (workshop_id, business_id, province, organization, quota_seats, delegation_token, status)
                     VALUES (?, ?, ?, ?, ?, ?, 'active')",
                    [$workshopId, $businessId, $prov, 'ប្រតិភូ ' . $prov, $quotaPerProvince, $token]
                );
                $insertedCount++;
            }
        }

        Session::flash('success', "បានបង្កើតកូតា ២៥ រាជធានី-ខេត្តដោយជោគជ័យ (បានបន្ថែមថ្មី {$insertedCount} ខេត្ត)!");
        redirect(APP_URL . "/workshops/{$workshopId}/delegations");
    }

    /**
     * Store single delegation
     */
    public static function store(int $workshopId): void {
        requireAuth();
        requirePermission('workshop.edit');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $province     = trim($_POST['province'] ?? '');
        $organization = trim($_POST['organization'] ?? '');
        $headName     = trim($_POST['head_name'] ?? '');
        $headPhone    = trim($_POST['head_phone'] ?? '');
        $headEmail    = trim($_POST['head_email'] ?? '');
        $quota        = max(1, (int)($_POST['quota_seats'] ?? 5));

        if (empty($province)) {
            Session::flash('error', 'សូមជ្រើសរើស ឬបញ្ចូលឈ្មោះរាជធានី-ខេត្ត');
            redirect(APP_URL . "/workshops/{$workshopId}/delegations");
        }

        $token = bin2hex(random_bytes(24));
        $db->execute(
            "INSERT INTO workshop_delegations 
             (workshop_id, business_id, province, organization, head_name, head_phone, head_email, quota_seats, delegation_token, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')",
            [$workshopId, $businessId, $province, $organization ?: "ប្រតិភូ {$province}", $headName, $headPhone, $headEmail, $quota, $token]
        );

        Session::flash('success', "បានបង្កើតកូតាសម្រាប់ {$province} ជោគជ័យ!");
        redirect(APP_URL . "/workshops/{$workshopId}/delegations");
    }

    /**
     * Update delegation
     */
    public static function update(int $workshopId, int $delegationId): void {
        requireAuth();
        requirePermission('workshop.edit');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $organization = trim($_POST['organization'] ?? '');
        $headName     = trim($_POST['head_name'] ?? '');
        $headPhone    = trim($_POST['head_phone'] ?? '');
        $headEmail    = trim($_POST['head_email'] ?? '');
        $quota        = max(1, (int)($_POST['quota_seats'] ?? 5));

        $db->execute(
            "UPDATE workshop_delegations 
             SET organization = ?, head_name = ?, head_phone = ?, head_email = ?, quota_seats = ?
             WHERE id = ? AND workshop_id = ? AND business_id = ?",
            [$organization, $headName, $headPhone, $headEmail, $quota, $delegationId, $workshopId, $businessId]
        );

        Session::flash('success', 'បានកែសម្រួលព័ត៌មានប្រតិភូជោគជ័យ!');
        redirect(APP_URL . "/workshops/{$workshopId}/delegations");
    }

    /**
     * Delete delegation
     */
    public static function delete(int $workshopId, int $delegationId): void {
        requireAuth();
        requirePermission('workshop.edit');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $db->execute(
            "DELETE FROM workshop_delegations WHERE id = ? AND workshop_id = ? AND business_id = ?",
            [$delegationId, $workshopId, $businessId]
        );

        Session::flash('success', 'បានលុបកូតាប្រតិភូនេះចេញ!');
        redirect(APP_URL . "/workshops/{$workshopId}/delegations");
    }

    /**
     * Quick On-Site Member Substitution ("ជំនួសសមាជិក")
     * If pre-registered member is absent and a substitute arrived on-site
     */
    public static function substitute(int $workshopId): void {
        requireAuth();
        requirePermission('registration.edit');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $oldRegId     = (int)($_POST['old_registration_id'] ?? 0);
        $newName      = trim($_POST['new_name'] ?? '');
        $newPhone     = trim($_POST['new_phone'] ?? '');
        $newGender    = trim($_POST['new_gender'] ?? 'male');
        $newIdCard    = trim($_POST['new_id_card'] ?? '');
        $newBankName  = trim($_POST['new_bank_name'] ?? '');
        $newBankAcc   = trim($_POST['new_bank_account'] ?? '');
        $reason       = trim($_POST['reason'] ?? 'មកជំនួសនៅថ្ងៃកម្មវិធី (On-site substitution)');

        if (!$oldRegId || empty($newName)) {
            Session::flash('error', 'សូមជ្រើសរើសសមាជិកចាស់ និងបញ្ចូលឈ្មោះសមាជិកថ្មីដែលមកជំនួស!');
            redirect(APP_URL . "/workshops/{$workshopId}/delegations");
        }

        $oldReg = $db->queryOne(
            "SELECT r.*, p.name as old_name, p.province, p.email as old_email
             FROM registrations r
             JOIN participants p ON p.id = r.participant_id
             WHERE r.id = ? AND r.workshop_id = ? AND r.business_id = ?",
            [$oldRegId, $workshopId, $businessId]
        );

        if (!$oldReg) {
            Session::flash('error', 'រកមិនឃើញទិន្នន័យសមាជិកចាស់ទេ');
            redirect(APP_URL . "/workshops/{$workshopId}/delegations");
        }

        // 1. Mark old registration as cancelled / substituted
        $db->execute(
            "UPDATE registrations SET status = 'cancelled', notes = CONCAT(COALESCE(notes,''), '\n[ជំនួសដោយ: ', ?, ' នៅថ្ងៃ ', NOW(), ']') WHERE id = ?",
            [$newName, $oldRegId]
        );

        // 2. Create or find participant profile for new member
        $emailPlaceholder = 'sub_' . time() . '_' . rand(100,999) . '@delegate.local';
        $participant = null;
        if (!empty($newPhone)) {
            $participant = $db->queryOne("SELECT * FROM participants WHERE phone = ?", [$newPhone]);
        }
        if (!$participant) {
            $db->execute(
                "INSERT INTO participants (email, name, phone, gender, province, address, status)
                 VALUES (?, ?, ?, ?, ?, ?, 'active')",
                [$emailPlaceholder, $newName, $newPhone, $newGender, $oldReg['province'], $newIdCard]
            );
            $participantId = $db->lastInsertId();
        } else {
            $participantId = $participant['id'];
            $db->execute(
                "UPDATE participants SET name = ?, gender = ?, province = ? WHERE id = ?",
                [$newName, $newGender, $oldReg['province'], $participantId]
            );
        }

        // 3. Create new active registration for substitute
        $regCode = generateCode('REG', 8);
        $db->execute(
            "INSERT INTO registrations 
             (registration_code, business_id, workshop_id, participant_id, ticket_id, delegation_id,
              status, payment_status, ticket_price, final_amount, id_card_number, bank_name, bank_account_number,
              substituted_for_id, notes, confirmed_at)
             VALUES (?, ?, ?, ?, ?, ?, 'confirmed', 'paid', 0, 0, ?, ?, ?, ?, ?, NOW())",
            [
                $regCode, $businessId, $workshopId, $participantId, $oldReg['ticket_id'], $oldReg['delegation_id'],
                $newIdCard, $newBankName, $newBankAcc, $oldRegId, "ជំនួសលោក/លោកស្រី {$oldReg['old_name']} (មូលហេតុ: {$reason})"
            ]
        );
        $newRegId = $db->lastInsertId();

        // 4. Generate QR code for the new substitute
        $qrToken = bin2hex(random_bytes(24));
        $db->execute(
            "INSERT INTO participant_qr (registration_id, participant_id, business_id, workshop_id, token, is_active)
             VALUES (?, ?, ?, ?, ?, 1)",
            [$newRegId, $participantId, $businessId, $workshopId, $qrToken]
        );

        auditLog('member_substituted', 'registration', $newRegId, ['old_name' => $oldReg['old_name']], ['new_name' => $newName], $businessId);

        Session::flash('success', "បានផ្លាស់ប្តូរជំនួសសមាជិកជោគជ័យ! សមាជិកថ្មី៖ {$newName} (កូដ: {$regCode}) អាចស្កេន ឬចូលរួមបានភ្លាមៗ។");
        redirect(APP_URL . "/workshops/{$workshopId}/delegations");
    }

    /**
     * Public Self-service Portal for Provincial Head (`/delegation/{token}`)
     */
    /**
     * Public Self-service Portal for Provincial Head (`/delegation/{token}`)
     */
    public static function portal(string $token): void {
        $db = Database::getInstance();
        $delegation = $db->queryOne(
            "SELECT d.*, w.name as workshop_name, w.start_date, w.end_date, w.start_time, w.venue, w.google_maps_url, w.slug as workshop_slug,
                    w.payment_mode, w.delegation_form_fields,
                    b.name as business_name, bb.logo_path
             FROM workshop_delegations d
             JOIN workshops w ON w.id = d.workshop_id
             JOIN businesses b ON b.id = d.business_id
             LEFT JOIN business_branding bb ON bb.business_id = b.id
             WHERE d.delegation_token = ?",
            [$token]
        );

        if (!$delegation) {
            http_response_code(404);
            die('<h3>404 - រកមិនឃើញតំណភ្ជាប់ប្រតិភូទេ (Invalid Delegation Link)</h3>');
        }

        $defaultDelegationConfig = [
            'show_phone'           => 1,
            'require_phone'        => 1,
            'show_gender'          => 1,
            'require_gender'       => 0,
            'show_position'        => 0,
            'require_position'     => 0,
            'show_organization'    => 0,
            'require_organization' => 0,
            'show_id_card'         => 1,
            'require_id_card'      => 0,
            'show_bank_name'       => 1,
            'require_bank_name'    => 0,
            'show_bank_account'    => 1,
            'require_bank_account' => 0,
            'show_email'           => 0,
            'require_email'        => 0,
        ];

        $delegationFormFields = $defaultDelegationConfig;
        if (!empty($delegation['delegation_form_fields'])) {
            $saved = is_string($delegation['delegation_form_fields'])
                ? json_decode($delegation['delegation_form_fields'], true)
                : $delegation['delegation_form_fields'];
            if (is_array($saved)) {
                $delegationFormFields = array_merge($defaultDelegationConfig, $saved);
            }
        }

        $successMsg = Session::flash('success');
        $errorMsg   = Session::flash('error');
        $newlyRegistered = null;

        // Handle POST to add new member under this delegation
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $name         = trim($_POST['name'] ?? '');
            $phone        = trim($_POST['phone'] ?? '');
            $emailInput   = trim($_POST['email'] ?? '');
            $gender       = trim($_POST['gender'] ?? 'male');
            $position     = trim($_POST['position'] ?? '');
            $organization = trim($_POST['organization'] ?? '');
            $idCard       = trim($_POST['id_card_number'] ?? '');
            $bankName     = trim($_POST['bank_name'] ?? '');
            $bankAcc      = trim($_POST['bank_account_number'] ?? '');

            // Check current members count vs quota
            $currentCount = (int)($db->queryOne(
                "SELECT COUNT(*) as c FROM registrations WHERE delegation_id = ? AND status != 'cancelled' AND deleted_at IS NULL",
                [$delegation['id']]
            )['c'] ?? 0);

            if ($currentCount >= (int)$delegation['quota_seats']) {
                Session::flash('error', "កូតាសមាជិកពេញហើយ! (កំណត់ត្រឹម {$delegation['quota_seats']} នាក់)");
                redirect(shareableUrl("/delegation/{$token}"));
                return;
            } elseif (empty($name)) {
                Session::flash('error', "សូមបញ្ចូលឈ្មោះសមាជិក!");
                redirect(shareableUrl("/delegation/{$token}"));
                return;
            } elseif (!empty($delegationFormFields['show_phone']) && !empty($delegationFormFields['require_phone']) && empty($phone)) {
                Session::flash('error', "សូមបញ្ចូលលេខទូរស័ព្ទ!");
                redirect(shareableUrl("/delegation/{$token}"));
                return;
            } elseif (!empty($delegationFormFields['show_email']) && !empty($delegationFormFields['require_email']) && empty($emailInput)) {
                Session::flash('error', "សូមបញ្ចូលអ៊ីមែល!");
                redirect(shareableUrl("/delegation/{$token}"));
                return;
            } elseif (!empty($delegationFormFields['show_gender']) && !empty($delegationFormFields['require_gender']) && empty($gender)) {
                Session::flash('error', "សូមជ្រើសរើសភេទ!");
                redirect(shareableUrl("/delegation/{$token}"));
                return;
            } elseif (!empty($delegationFormFields['show_position']) && !empty($delegationFormFields['require_position']) && empty($position)) {
                Session::flash('error', "សូមបញ្ចូលតួនាទី / មុខតំណែង!");
                redirect(shareableUrl("/delegation/{$token}"));
                return;
            } elseif (!empty($delegationFormFields['show_organization']) && !empty($delegationFormFields['require_organization']) && empty($organization)) {
                Session::flash('error', "សូមបញ្ចូលអង្គភាព / ស្ថាប័ន!");
                redirect(shareableUrl("/delegation/{$token}"));
                return;
            } elseif (!empty($delegationFormFields['show_id_card']) && !empty($delegationFormFields['require_id_card']) && empty($idCard)) {
                Session::flash('error', "សូមបញ្ចូលលេខអត្តសញ្ញាណប័ណ្ណ!");
                redirect(shareableUrl("/delegation/{$token}"));
                return;
            } elseif (!empty($delegationFormFields['show_bank_name']) && !empty($delegationFormFields['require_bank_name']) && empty($bankName)) {
                Session::flash('error', "សូមបញ្ចូលឈ្មោះធនាគារ!");
                redirect(shareableUrl("/delegation/{$token}"));
                return;
            } elseif (!empty($delegationFormFields['show_bank_account']) && !empty($delegationFormFields['require_bank_account']) && empty($bankAcc)) {
                Session::flash('error', "សូមបញ្ចូលលេខគណនីធនាគារ!");
                redirect(shareableUrl("/delegation/{$token}"));
                return;
            } else {
                // Find or create participant profile
                $participant = null;
                if (!empty($emailInput)) {
                    $participant = $db->queryOne("SELECT id FROM participants WHERE email = ?", [$emailInput]);
                }
                if (!$participant && !empty($phone)) {
                    $participant = $db->queryOne("SELECT id FROM participants WHERE phone = ?", [$phone]);
                }

                if (!$participant) {
                    $finalEmail = !empty($emailInput) ? $emailInput : ('del_' . time() . '_' . rand(100,999) . '@provincial.gov.kh');
                    $db->execute(
                        "INSERT INTO participants (email, name, phone, gender, position, company, province, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')",
                        [$finalEmail, $name, $phone, $gender, $position, $organization, $delegation['province']]
                    );
                    $pId = $db->lastInsertId();
                } else {
                    $pId = $participant['id'];
                    $db->execute(
                        "UPDATE participants SET name = ?, gender = ?, position = COALESCE(NULLIF(?, ''), position), company = COALESCE(NULLIF(?, ''), company), province = ? WHERE id = ?",
                        [$name, $gender, $position, $organization, $delegation['province'], $pId]
                    );
                }

                // Strict Duplicate Check: Prevent registering the same person twice in this delegation
                $dupConditions = ["r.participant_id = ?"];
                $dupParams = [(int)$pId];

                if (!empty($phone)) {
                    $dupConditions[] = "p.phone = ?";
                    $dupParams[] = $phone;
                }
                if (!empty($idCard)) {
                    $dupConditions[] = "r.id_card_number = ?";
                    $dupParams[] = $idCard;
                }

                $whereDup = implode(' OR ', $dupConditions);
                $existingMember = $db->queryOne(
                    "SELECT r.id, r.registration_code, p.name 
                     FROM registrations r
                     JOIN participants p ON p.id = r.participant_id
                     WHERE r.delegation_id = ? 
                       AND r.status != 'cancelled' 
                       AND r.deleted_at IS NULL
                       AND ({$whereDup})
                     LIMIT 1",
                    array_merge([(int)$delegation['id']], $dupParams)
                );

                if ($existingMember) {
                    Session::flash('error', "សមាជិកឈ្មោះ «{$existingMember['name']}» ធ្លាប់បានចុះឈ្មោះក្នុងប្រតិភូនេះរួចហើយ! (កូដ៖ {$existingMember['registration_code']})");
                    redirect(shareableUrl("/delegation/{$token}"));
                    return;
                }

                $regCode = generateCode('REG', 8);
                $paymentStatus = ($delegation['payment_mode'] ?? 'free') === 'free' ? 'complimentary' : 'paid';

                $db->execute(
                    "INSERT INTO registrations 
                     (registration_code, business_id, workshop_id, participant_id, delegation_id, attendee_type, status, payment_status, id_card_number, bank_name, bank_account_number, confirmed_at)
                     VALUES (?, ?, ?, ?, ?, 'delegate', 'confirmed', ?, ?, ?, ?, NOW())",
                    [$regCode, $delegation['business_id'], $delegation['workshop_id'], $pId, $delegation['id'], $paymentStatus, $idCard, $bankName, $bankAcc]
                );
                $newRegId = $db->lastInsertId();

                $qrToken = bin2hex(random_bytes(24));
                $db->execute(
                    "INSERT INTO participant_qr (registration_id, participant_id, business_id, workshop_id, token, is_active)
                     VALUES (?, ?, ?, ?, ?, 1)",
                    [$newRegId, $pId, $delegation['business_id'], $delegation['workshop_id'], $qrToken]
                );

                // Post-Redirect-Get (PRG) pattern prevents any duplicate form resubmission on page refresh / reload!
                Session::flash('newly_registered_id', $newRegId);
                Session::flash('success', "បានចុះឈ្មោះសមាជិក {$name} ចូលក្នុងប្រតិភូ {$delegation['province']} ជោគជ័យ!");
                redirect(shareableUrl("/delegation/{$token}"));
                return;
            }
        }

        // On GET: Check if a newly registered member was just created (via PRG)
        $newlyRegisteredId = Session::flash('newly_registered_id');
        if ($newlyRegisteredId) {
            $newlyRegistered = $db->queryOne(
                "SELECT r.id, r.registration_code, p.name, pq.token as qr_token, d.province
                 FROM registrations r
                 JOIN participants p ON p.id = r.participant_id
                 JOIN workshop_delegations d ON d.id = r.delegation_id
                 LEFT JOIN participant_qr pq ON pq.registration_id = r.id
                 WHERE r.id = ?",
                [$newlyRegisteredId]
            );
        }

        // Get members list
        $members = $db->query(
            "SELECT r.id, r.registration_code, r.id_card_number, r.status, r.created_at,
                    p.name, p.phone, p.gender, p.position, p.company, pq.token as qr_token,
                    (SELECT checked_in_at FROM attendance a WHERE a.registration_id = r.id LIMIT 1) as checked_in_at
             FROM registrations r
             JOIN participants p ON p.id = r.participant_id
             LEFT JOIN participant_qr pq ON pq.registration_id = r.id
             WHERE r.delegation_id = ? AND r.deleted_at IS NULL AND r.status != 'cancelled'
             ORDER BY r.id ASC",
            [$delegation['id']]
        );

        $title = 'បញ្ជីប្រតិភូ ' . $delegation['province'] . ' - ' . $delegation['workshop_name'];
        require VIEWS_PATH . '/business/delegations/portal.php';
    }
}
