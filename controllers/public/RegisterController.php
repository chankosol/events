<?php
class PublicRegisterController {
    public static function show(string $slug): void {
        $db = Database::getInstance();
        $workshop = $db->queryOne(
            "SELECT w.*, b.name as business_name, b.id as business_id,
                    wb.cover_image, wb.theme_color
             FROM workshops w
             JOIN businesses b ON b.id = w.business_id
             LEFT JOIN workshop_branding wb ON wb.workshop_id = w.id
             WHERE w.slug = ? AND w.status IN ('registration_open') AND w.deleted_at IS NULL",
            [$slug]
        );

        if (!$workshop) {
            Session::flash('error', 'Registration is not currently open for this workshop.');
            redirect(APP_URL . '/event/' . $slug);
        }

        // Check capacity
        $regCount = $db->queryOne(
            "SELECT COUNT(*) as c FROM registrations WHERE workshop_id = ? AND status NOT IN ('cancelled','rejected') AND deleted_at IS NULL",
            [$workshop['id']]
        )['c'];

        $isFull = $regCount >= $workshop['capacity'];
        if ($isFull && !$workshop['allow_waitlist']) {
            Session::flash('error', 'This workshop is fully booked and does not accept waitlist registrations.');
            redirect(APP_URL . '/event/' . $slug);
        }

        // Get tickets
        $tickets = $db->query(
            "SELECT * FROM tickets WHERE workshop_id = ? AND status = 'active' AND is_visible = 1
             AND (sale_start IS NULL OR sale_start <= NOW())
             AND (sale_end IS NULL OR sale_end >= NOW())
             ORDER BY sort_order",
            [$workshop['id']]
        );

        // Get custom registration fields
        $fields = $db->query(
            "SELECT * FROM registration_fields WHERE workshop_id = ? AND status = 'active' AND is_internal = 0 ORDER BY sort_order",
            [$workshop['id']]
        );

        // Get payment methods if paid workshop
        $paymentMethods = [];
        if ($workshop['payment_mode'] !== 'free') {
            $paymentMethods = $db->query(
                "SELECT * FROM business_payment_methods WHERE business_id = ? AND status = 'active' ORDER BY sort_order",
                [$workshop['business_id']]
            );
        }

        // Form field configuration
        $formConfig = [
            'show_email'       => 1,
            'require_email'    => 0,
            'show_phone'       => 1,
            'require_phone'    => 1,
            'show_gender'      => 0,
            'require_gender'   => 0,
            'show_province'    => 0,
            'require_province' => 0,
            'show_company'     => 1,
            'require_company'  => 0,
            'show_position'    => 1,
            'require_position' => 0,
        ];
        if (!empty($workshop['form_fields'])) {
            $saved = is_string($workshop['form_fields']) ? json_decode($workshop['form_fields'], true) : $workshop['form_fields'];
            if (is_array($saved)) {
                $formConfig = array_merge($formConfig, $saved);
            }
        }

        $errors = Session::flash('errors') ?? [];
        $old    = Session::flash('old') ?? [];
        $title  = 'Register - ' . $workshop['name'];
        ob_start();
        require VIEWS_PATH . '/public/register.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/public.php';
    }

    public static function submit(string $slug): void {
        requireCsrf();
        
        $db = Database::getInstance();
        $workshop = $db->queryOne(
            "SELECT w.*, b.id as business_id FROM workshops w JOIN businesses b ON b.id = w.business_id
             WHERE w.slug = ? AND w.status IN ('registration_open') AND w.deleted_at IS NULL",
            [$slug]
        );

        if (!$workshop) {
            Session::flash('error', 'Registration is not currently open.');
            redirect(APP_URL . '/event/' . $slug);
        }

        $formConfig = [
            'show_email'       => 1,
            'require_email'    => 0,
            'show_phone'       => 1,
            'require_phone'    => 1,
            'show_gender'      => 0,
            'require_gender'   => 0,
            'show_province'    => 0,
            'require_province' => 0,
            'show_company'     => 1,
            'require_company'  => 0,
            'show_position'    => 1,
            'require_position' => 0,
        ];
        if (!empty($workshop['form_fields'])) {
            $saved = is_string($workshop['form_fields']) ? json_decode($workshop['form_fields'], true) : $workshop['form_fields'];
            if (is_array($saved)) {
                $formConfig = array_merge($formConfig, $saved);
            }
        }

        // Validate inputs
        $name     = trim($_POST['name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $gender   = trim($_POST['gender'] ?? '');
        $province = trim($_POST['province'] ?? '');
        $company  = trim($_POST['company'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $ticketId = (int)($_POST['ticket_id'] ?? 0);

        $errors = [];
        if (empty($name)) {
            $errors['name'] = 'សូមបញ្ចូលឈ្មោះពេញ (Full name is required).';
        }

        // Phone validation
        if (!empty($formConfig['show_phone']) && !empty($formConfig['require_phone']) && empty($phone)) {
            $errors['phone'] = 'សូមបញ្ចូលលេខទូរស័ព្ទ (Phone number is required).';
        }

        // Require at least phone or email so participant has contact info
        if (empty($phone) && empty($email)) {
            $errors['phone'] = 'សូមបញ្ចូលយ៉ាងហោចណាស់លេខទូរស័ព្ទ ឬអ៊ីមែលមួយ (Please provide at least phone number or email).';
        }

        // Email validation
        if (!empty($formConfig['show_email']) && !empty($formConfig['require_email']) && empty($email)) {
            $errors['email'] = 'សូមបញ្ចូលអាសយដ្ឋានអ៊ីមែល (Email is required).';
        } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'ទម្រង់អ៊ីមែលមិនត្រឹមត្រូវទេ (Invalid email format).';
        }
        $emailValue = !empty($email) ? $email : null;

        // Gender validation
        if (!empty($formConfig['show_gender']) && !empty($formConfig['require_gender']) && empty($gender)) {
            $errors['gender'] = 'សូមជ្រើសរើសភេទ (Gender is required).';
        }

        // Province validation
        if (!empty($formConfig['show_province']) && !empty($formConfig['require_province']) && empty($province)) {
            $errors['province'] = 'សូមជ្រើសរើសរាជធានី-ខេត្ត (Province is required).';
        }

        // Company validation
        if (!empty($formConfig['show_company']) && !empty($formConfig['require_company']) && empty($company)) {
            $errors['company'] = 'សូមបញ្ចូលឈ្មោះក្រុមហ៊ុន ឬស្ថាប័ន (Company/Organization is required).';
        }

        // Position validation
        if (!empty($formConfig['show_position']) && !empty($formConfig['require_position']) && empty($position)) {
            $errors['position'] = 'សូមបញ្ចូលតួនាទី ឬមុខតំណែង (Position/Role is required).';
        }

        // Validate ticket
        $ticket = null;
        if (!$ticketId && $workshop['payment_mode'] === 'free') {
            $defaultTicket = $db->queryOne(
                "SELECT * FROM tickets WHERE workshop_id = ? AND status = 'active' ORDER BY sort_order ASC LIMIT 1",
                [$workshop['id']]
            );
            if ($defaultTicket) {
                $ticket = $defaultTicket;
                $ticketId = (int)$defaultTicket['id'];
            }
        } elseif ($ticketId) {
            $ticket = $db->queryOne(
                "SELECT * FROM tickets WHERE id = ? AND workshop_id = ? AND status = 'active'",
                [$ticketId, $workshop['id']]
            );
            if (!$ticket) $errors['ticket_id'] = 'Selected ticket is invalid.';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $_POST);
            redirect(APP_URL . '/event/' . $slug . '/register');
        }

        // Check capacity again
        $regCount = $db->queryOne(
            "SELECT COUNT(*) as c FROM registrations WHERE workshop_id = ? AND status NOT IN ('cancelled','rejected') AND deleted_at IS NULL",
            [$workshop['id']]
        )['c'];
        $isWaitlist = $regCount >= $workshop['capacity'];

        try {
            $db->beginTransaction();

            // Find or create participant profile (by email or phone)
            $participant = null;
            if (!empty($emailValue)) {
                $participant = $db->queryOne("SELECT * FROM participants WHERE email = ? AND deleted_at IS NULL", [$emailValue]);
            }
            if (!$participant && !empty($phone)) {
                $participant = $db->queryOne("SELECT * FROM participants WHERE phone = ? AND deleted_at IS NULL", [$phone]);
            }

            $gender   = !empty($_POST['gender']) ? trim($_POST['gender']) : null;
            $province = !empty($_POST['province']) ? trim($_POST['province']) : null;
            $company  = !empty($_POST['company']) ? trim($_POST['company']) : null;
            $position = !empty($_POST['position']) ? trim($_POST['position']) : null;

            if (!$participant) {
                $db->execute(
                    "INSERT INTO participants (email, name, phone, gender, province, company, position, preferred_language) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [$emailValue, $name, $phone, $gender, $province, $company, $position, $_POST['preferred_language'] ?? 'en']
                );
                $participantId = (int)$db->lastInsertId();
            } else {
                $participantId = (int)$participant['id'];
                $updateFields = [];
                $updateParams = [];
                if (!empty($name) && empty($participant['name'])) { $updateFields[] = "name = ?"; $updateParams[] = $name; }
                if (!empty($phone) && empty($participant['phone'])) { $updateFields[] = "phone = ?"; $updateParams[] = $phone; }
                if (!empty($emailValue) && empty($participant['email'])) { $updateFields[] = "email = ?"; $updateParams[] = $emailValue; }
                if (!empty($gender) && empty($participant['gender'])) { $updateFields[] = "gender = ?"; $updateParams[] = $gender; }
                if (!empty($province) && empty($participant['province'])) { $updateFields[] = "province = ?"; $updateParams[] = $province; }
                if (!empty($company) && empty($participant['company'])) { $updateFields[] = "company = ?"; $updateParams[] = $company; }
                if (!empty($position) && empty($participant['position'])) { $updateFields[] = "position = ?"; $updateParams[] = $position; }
                if (!empty($updateFields)) {
                    $updateParams[] = $participantId;
                    $db->execute("UPDATE participants SET " . implode(', ', $updateFields) . " WHERE id = ?", $updateParams);
                }
            }

            // Check if already registered
            $existing = $db->queryOne(
                "SELECT id FROM registrations WHERE workshop_id = ? AND participant_id = ? AND status NOT IN ('cancelled','rejected') AND deleted_at IS NULL",
                [$workshop['id'], $participantId]
            );
            if ($existing) {
                $db->rollback();
                Session::flash('error', 'You are already registered for this workshop.');
                redirect(APP_URL . '/event/' . $slug . '/register');
            }

            // Calculate price
            $ticketPrice  = $ticket ? (float)$ticket['price'] : 0;
            $promoCodeId  = null;
            $discountAmt  = 0;
            $finalAmount  = $ticketPrice;

            // Apply promo code if provided
            if (!empty($_POST['promo_code'])) {
                $promo = $db->queryOne(
                    "SELECT * FROM promo_codes WHERE code = ? AND workshop_id = ? AND status = 'active'
                     AND (start_date IS NULL OR start_date <= NOW())
                     AND (end_date IS NULL OR end_date >= NOW())
                     AND (usage_limit IS NULL OR used_count < usage_limit)",
                    [strtoupper(trim($_POST['promo_code'])), $workshop['id']]
                );
                if ($promo) {
                    $promoCodeId = $promo['id'];
                    if ($promo['discount_type'] === 'percentage') {
                        $discountAmt = $ticketPrice * ($promo['discount_value'] / 100);
                    } else {
                        $discountAmt = min($promo['discount_value'], $ticketPrice);
                    }
                    $finalAmount = max(0, $ticketPrice - $discountAmt);
                    // Increment usage
                    $db->execute("UPDATE promo_codes SET used_count = used_count + 1 WHERE id = ?", [$promo['id']]);
                }
            }

            // Determine initial status
            $isFree = $workshop['payment_mode'] === 'free' || $finalAmount == 0;
            $requiresApproval = (bool)$workshop['requires_approval'];
            $inviteCode = trim($_POST['invite'] ?? $_GET['invite'] ?? $_GET['direct'] ?? '');
            $isPrivateInvite = !empty($inviteCode);

            if ($isWaitlist) {
                $status = 'waitlisted';
                $paymentStatus = 'unpaid';
                $waitlistPos = ($db->queryOne("SELECT COUNT(*) as c FROM registrations WHERE workshop_id = ? AND status='waitlisted'", [$workshop['id']])['c'] ?? 0) + 1;
            } elseif ($isPrivateInvite) {
                // Private invite link bypasses manual approval: auto-confirm & instant QR
                $status = 'confirmed';
                $paymentStatus = $isFree ? 'complimentary' : 'pending';
            } elseif ($isFree && $workshop['auto_confirm']) {
                // Free mode with auto_confirm enabled: instant QR
                $status = 'confirmed';
                $paymentStatus = 'complimentary';
            } elseif ($isFree && $requiresApproval) {
                $status = 'pending_approval';
                $paymentStatus = 'complimentary';
            } elseif (!$isFree) {
                $status = 'pending_verification';
                $paymentStatus = 'pending';
            } else {
                $status = 'confirmed';
                $paymentStatus = $isFree ? 'complimentary' : 'paid';
            }

            // Generate registration code
            $regCode = generateCode('REG', 10);

            $db->execute(
                "INSERT INTO registrations (registration_code, business_id, workshop_id, participant_id, ticket_id, status, payment_status,
                 ticket_price, currency, discount_amount, promo_code_id, final_amount, is_vip, waitlist_position, confirmed_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $regCode,
                    $workshop['business_id'],
                    $workshop['id'],
                    $participantId,
                    $ticket ? $ticket['id'] : null,
                    $status,
                    $paymentStatus,
                    $ticketPrice,
                    $ticket ? $ticket['currency'] : 'USD',
                    $discountAmt,
                    $promoCodeId,
                    $finalAmount,
                    $ticket && $ticket['ticket_type'] === 'vip' ? 1 : 0,
                    $isWaitlist ? $waitlistPos : null,
                    $status === 'confirmed' ? date('Y-m-d H:i:s') : null,
                ]
            );
            $registrationId = (int)$db->lastInsertId();

            // Save custom field answers
            $fields = $db->query("SELECT * FROM registration_fields WHERE workshop_id = ? AND status = 'active' AND is_internal = 0", [$workshop['id']]);
            foreach ($fields as $field) {
                $answer = $_POST['field_' . $field['id']] ?? null;
                if ($answer === null || $answer === '') {
                    if (!empty($company) && (str_contains($field['field_label'], 'ក្រុមហ៊ុន') || str_contains($field['field_label'], 'ស្ថាប័ន'))) {
                        $answer = $company;
                    } elseif (!empty($position) && (str_contains($field['field_label'], 'តួនាទី') || str_contains($field['field_label'], 'មុខតំណែង'))) {
                        $answer = $position;
                    }
                }
                if ($answer !== null && $answer !== '') {
                    $db->execute(
                        "INSERT INTO registration_answers (registration_id, field_id, business_id, answer_text) VALUES (?, ?, ?, ?)",
                        [$registrationId, $field['id'], $workshop['business_id'], is_array($answer) ? json_encode($answer) : $answer]
                    );
                }
            }

            // Create payment record if paid
            if (!$isFree && $finalAmount > 0) {
                $db->execute(
                    "INSERT INTO payments (business_id, workshop_id, registration_id, participant_id, amount, currency, status)
                     VALUES (?, ?, ?, ?, ?, ?, 'pending')",
                    [$workshop['business_id'], $workshop['id'], $registrationId, $participantId, $finalAmount, $ticket ? $ticket['currency'] : 'USD']
                );
            }

            // If confirmed, generate QR code
            $qrToken = null;
            if ($status === 'confirmed') {
                $qrToken = self::generateParticipantQR($db, $registrationId, $participantId, $workshop['business_id'], $workshop['id']);
            }

            // Update ticket sold count
            if ($ticket) {
                $db->execute("UPDATE tickets SET sold_count = sold_count + 1 WHERE id = ?", [$ticket['id']]);
            }

            $db->commit();

            auditLog('registration_created', 'registration', $registrationId, null, ['workshop_id' => $workshop['id'], 'status' => $status]);

            // Store registration info in session for confirmation page
            Session::set('last_registration', [
                'id'            => $registrationId,
                'code'          => $regCode,
                'status'        => $status,
                'payment_status'=> $paymentStatus,
                'is_waitlist'   => $isWaitlist,
                'workshop_name' => $workshop['name'],
                'final_amount'  => $finalAmount,
                'currency'      => $ticket ? $ticket['currency'] : 'USD',
                'participant_email' => $email,
                'qr_token'      => $qrToken,
            ]);

            redirect(APP_URL . '/event/' . $slug . '/confirmation');

        } catch (Exception $e) {
            $db->rollback();
            error_log('Registration failed: ' . $e->getMessage());
            Session::flash('error', 'Registration failed. Please try again.');
            redirect(APP_URL . '/event/' . $slug . '/register');
        }
    }

    public static function showConfirmation(string $slug): void {
        $registration = Session::get('last_registration');
        if (!$registration) {
            redirect(APP_URL . '/event/' . $slug);
        }
        $title = 'Registration Confirmation';
        ob_start();
        require VIEWS_PATH . '/public/confirmation.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/public.php';
    }

    private static function generateParticipantQR(object $db, int $registrationId, int $participantId, int $businessId, int $workshopId): string {
        $token = bin2hex(random_bytes(32));
        $db->execute(
            "INSERT INTO participant_qr (registration_id, participant_id, business_id, workshop_id, token, is_active)
             VALUES (?, ?, ?, ?, ?, 1)",
            [$registrationId, $participantId, $businessId, $workshopId, $token]
        );
        return $token;
    }
}
