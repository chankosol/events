INSERT INTO platform_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('app_name', 'Workshop OS', 'string', 'Platform application name', 1),
('app_tagline', 'Complete Workshop Operating System', 'string', 'Platform tagline', 1),
('default_currency', 'USD', 'string', 'Default currency', 0),
('default_language', 'en', 'string', 'Default language', 0),
('default_timezone', 'Asia/Phnom_Penh', 'string', 'Default timezone', 0),
('registration_enabled', '1', 'boolean', 'Allow new business registration', 0),
('max_upload_size', '10485760', 'integer', 'Max file upload size in bytes', 0),
('invoice_prefix', 'INV', 'string', 'Invoice number prefix', 0),
('certificate_prefix', 'CERT', 'string', 'Certificate number prefix', 0),
('registration_code_prefix', 'REG', 'string', 'Registration code prefix', 0),
('support_email', 'support@workshopos.com', 'string', 'Platform support email', 1),
('maintenance_mode', '0', 'boolean', 'Enable maintenance mode', 0),
('ai_enabled', '0', 'boolean', 'AI features enabled', 0),
('email_verification_required', '0', 'boolean', 'Require email verification', 0),
('platform_logo', '', 'string', 'Platform logo path', 1);

INSERT INTO platform_pricing_rules (name, min_capacity, max_capacity, price, currency, status, sort_order, description) VALUES
('Starter (1-50)', 1, 50, 10.00, 'USD', 'active', 1, 'For workshops with up to 50 participants'),
('Basic (51-100)', 51, 100, 15.00, 'USD', 'active', 2, 'For workshops with 51 to 100 participants'),
('Standard (101-300)', 101, 300, 30.00, 'USD', 'active', 3, 'For workshops with 101 to 300 participants'),
('Professional (301-500)', 301, 500, 50.00, 'USD', 'active', 4, 'For workshops with 301 to 500 participants'),
('Business (501-1000)', 501, 1000, 80.00, 'USD', 'active', 5, 'For workshops with 501 to 1000 participants'),
('Enterprise (1001-2000)', 1001, 2000, 120.00, 'USD', 'active', 6, 'For workshops with 1001 to 2000 participants'),
('Large (2001-5000)', 2001, 5000, 200.00, 'USD', 'active', 7, 'For workshops with 2001 to 5000 participants'),
('Custom (5000+)', 5001, NULL, 0.00, 'USD', 'inactive', 8, 'Custom pricing - contact platform admin');

INSERT INTO roles (name, slug, scope, description, is_system) VALUES
('Super Admin', 'super_admin', 'platform', 'Full platform access and control', 1),
('Platform Finance', 'platform_finance', 'platform', 'Platform billing and payment management', 1),
('Platform Support', 'platform_support', 'platform', 'Customer support access', 1),
('Platform Moderator', 'platform_moderator', 'platform', 'Content moderation across platform', 1),
('Business Owner', 'business_owner', 'business', 'Full access to own business', 1),
('Business Admin', 'business_admin', 'business', 'Full business management except billing', 1),
('Workshop Manager', 'workshop_manager', 'business', 'Manage workshops and registrations', 1),
('Registration Staff', 'registration_staff', 'business', 'Handle participant registrations', 1),
('Check-in Staff', 'checkin_staff', 'business', 'Event check-in operations only', 1),
('Moderator', 'moderator', 'business', 'Moderate Q&A and live session', 1),
('Trainer', 'trainer', 'business', 'View workshop content and manage Q&A', 1),
('Gift Staff', 'gift_staff', 'business', 'Gift distribution operations', 1),
('Certificate Staff', 'certificate_staff', 'business', 'Issue and manage certificates', 1),
('Finance Staff', 'finance_staff', 'business', 'View financial reports and verify payments', 1),
('Viewer', 'viewer', 'business', 'Read-only access to business data', 1);

INSERT INTO permissions (name, slug, group_name, description) VALUES
('Platform Admin Access', 'platform.admin', 'platform', 'Access platform admin panel'),
('Platform Billing Management', 'platform.billing', 'platform', 'Manage platform billing and invoices'),
('Platform Support Access', 'platform.support', 'platform', 'Access support tools'),
('Platform Moderation', 'platform.moderate', 'platform', 'Moderate platform content'),
('Platform Settings', 'platform.settings', 'platform', 'Manage platform settings'),
('Platform Analytics', 'platform.analytics', 'platform', 'View platform analytics'),
('View Business', 'business.view', 'business', 'View business information'),
('Edit Business Settings', 'business.settings', 'business', 'Edit business settings'),
('View Billing', 'billing.view', 'billing', 'View billing information'),
('Manage Billing', 'billing.manage', 'billing', 'Manage billing and payments'),
('View Workshops', 'workshop.view', 'workshop', 'View workshops'),
('Create Workshop', 'workshop.create', 'workshop', 'Create new workshops'),
('Edit Workshop', 'workshop.edit', 'workshop', 'Edit workshop details'),
('Delete Workshop', 'workshop.delete', 'workshop', 'Delete workshops'),
('Publish Workshop', 'workshop.publish', 'workshop', 'Publish/activate workshops'),
('Duplicate Workshop', 'workshop.duplicate', 'workshop', 'Duplicate workshop configurations'),
('View Registrations', 'registration.view', 'registration', 'View participant registrations'),
('Create Registration', 'registration.create', 'registration', 'Create registrations for participants'),
('Edit Registration', 'registration.edit', 'registration', 'Edit registration details'),
('Verify Registration', 'registration.verify', 'registration', 'Approve or reject registrations'),
('Export Registrations', 'registration.export', 'registration', 'Export registration data'),
('View Payments', 'payment.view', 'payment', 'View payment information'),
('Verify Payments', 'payment.verify', 'payment', 'Approve or reject payment proofs'),
('Export Payments', 'payment.export', 'payment', 'Export payment data'),
('Manage Refunds', 'payment.refund', 'payment', 'Process refunds'),
('View Participants', 'participant.view', 'participant', 'View participant profiles'),
('Edit Participants', 'participant.edit', 'participant', 'Edit participant information'),
('Export Participants', 'participant.export', 'participant', 'Export participant data'),
('Use Check-in', 'checkin.use', 'checkin', 'Use check-in interface'),
('Manage Attendance', 'attendance.manage', 'checkin', 'Manage attendance records'),
('View Attendance', 'attendance.view', 'checkin', 'View attendance records'),
('View Questions', 'question.view', 'qa', 'View submitted questions'),
('Moderate Questions', 'question.moderate', 'qa', 'Approve, reject, pin questions'),
('Answer Questions', 'question.answer', 'qa', 'Answer questions'),
('View Polls', 'poll.view', 'polls', 'View polls'),
('Manage Polls', 'poll.manage', 'polls', 'Create and manage polls'),
('View Requests', 'request.view', 'requests', 'View participant requests'),
('Manage Requests', 'request.manage', 'requests', 'Assign and resolve requests'),
('View Gifts', 'gift.view', 'gifts', 'View gift inventory'),
('Distribute Gifts', 'gift.distribute', 'gifts', 'Record gift distribution'),
('Manage Gifts', 'gift.manage', 'gifts', 'Configure gift inventory'),
('View Certificates', 'certificate.view', 'certificates', 'View certificates'),
('Issue Certificates', 'certificate.issue', 'certificates', 'Issue certificates to participants'),
('Revoke Certificates', 'certificate.revoke', 'certificates', 'Revoke issued certificates'),
('Manage Certificate Templates', 'certificate.template', 'certificates', 'Manage certificate templates'),
('View Reports', 'report.view', 'reports', 'View reports'),
('Export Reports', 'report.export', 'reports', 'Export report data'),
('Manage Staff', 'staff.manage', 'staff', 'Invite and manage staff members'),
('View Staff', 'staff.view', 'staff', 'View staff list'),
('Manage Tests', 'test.manage', 'tests', 'Create and manage tests'),
('View Tests', 'test.view', 'tests', 'View test results'),
('View Feedback', 'feedback.view', 'feedback', 'View feedback responses'),
('Manage Feedback', 'feedback.manage', 'feedback', 'Configure feedback forms');

-- Role-Permission Mappings
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.slug = 'super_admin';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.slug = 'platform_finance'
AND p.slug IN ('platform.admin', 'platform.billing', 'billing.view', 'billing.manage', 'report.view', 'report.export');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.slug = 'platform_support'
AND p.slug IN ('platform.admin', 'platform.support', 'business.view', 'workshop.view', 'registration.view', 'participant.view', 'report.view');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.slug = 'platform_moderator'
AND p.slug IN ('platform.moderate', 'workshop.view', 'question.moderate');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.slug = 'business_owner'
AND p.slug IN (
  'business.view','business.settings','billing.view','billing.manage',
  'workshop.view','workshop.create','workshop.edit','workshop.delete','workshop.publish','workshop.duplicate',
  'registration.view','registration.create','registration.edit','registration.verify','registration.export',
  'payment.view','payment.verify','payment.export','payment.refund',
  'participant.view','participant.edit','participant.export',
  'checkin.use','attendance.manage','attendance.view',
  'question.view','question.moderate','question.answer',
  'poll.view','poll.manage','request.view','request.manage',
  'gift.view','gift.distribute','gift.manage',
  'certificate.view','certificate.issue','certificate.revoke','certificate.template',
  'report.view','report.export','staff.manage','staff.view',
  'test.manage','test.view','feedback.view','feedback.manage'
);

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.slug = 'business_admin'
AND p.slug IN (
  'business.view','business.settings','billing.view',
  'workshop.view','workshop.create','workshop.edit','workshop.delete','workshop.publish','workshop.duplicate',
  'registration.view','registration.create','registration.edit','registration.verify','registration.export',
  'payment.view','payment.verify','payment.export','payment.refund',
  'participant.view','participant.edit','participant.export',
  'checkin.use','attendance.manage','attendance.view',
  'question.view','question.moderate','question.answer',
  'poll.view','poll.manage','request.view','request.manage',
  'gift.view','gift.distribute','gift.manage',
  'certificate.view','certificate.issue','certificate.revoke','certificate.template',
  'report.view','report.export','staff.manage','staff.view',
  'test.manage','test.view','feedback.view','feedback.manage'
);

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.slug = 'workshop_manager'
AND p.slug IN (
  'workshop.view','workshop.create','workshop.edit','workshop.publish','workshop.duplicate',
  'registration.view','registration.create','registration.edit','registration.verify',
  'payment.view','payment.verify',
  'participant.view','participant.edit',
  'checkin.use','attendance.manage','attendance.view',
  'question.view','question.moderate','question.answer',
  'poll.view','poll.manage','request.view','request.manage',
  'gift.view','gift.distribute','gift.manage',
  'certificate.view','certificate.issue','certificate.revoke','certificate.template',
  'report.view','report.export','test.manage','test.view','feedback.view','feedback.manage'
);

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.slug = 'registration_staff'
AND p.slug IN (
  'workshop.view', 'registration.view', 'registration.create', 'registration.edit', 'registration.verify',
  'payment.view', 'payment.verify', 'participant.view', 'participant.edit'
);

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.slug = 'checkin_staff'
AND p.slug IN ('workshop.view', 'checkin.use', 'attendance.view', 'attendance.manage', 'participant.view', 'gift.distribute', 'gift.view', 'certificate.view');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.slug = 'moderator'
AND p.slug IN ('workshop.view', 'question.view', 'question.moderate', 'question.answer', 'poll.view', 'poll.manage', 'request.view', 'request.manage');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.slug = 'trainer'
AND p.slug IN ('workshop.view', 'question.view', 'question.answer', 'poll.view', 'test.view', 'feedback.view', 'attendance.view');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.slug = 'gift_staff'
AND p.slug IN ('workshop.view', 'gift.view', 'gift.distribute', 'participant.view', 'checkin.use');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.slug = 'certificate_staff'
AND p.slug IN ('workshop.view', 'certificate.view', 'certificate.issue', 'certificate.revoke', 'participant.view', 'attendance.view');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.slug = 'finance_staff'
AND p.slug IN ('workshop.view', 'billing.view', 'payment.view', 'payment.verify', 'payment.export', 'payment.refund', 'report.view', 'report.export');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.slug = 'viewer'
AND p.slug IN ('workshop.view', 'registration.view', 'participant.view', 'report.view', 'attendance.view');

INSERT INTO users (business_id, name, email, password, status, email_verified_at) VALUES
(NULL, 'Super Admin', 'admin@workshopos.com', '$2y$12$Zv.sK/m.W8KPqUAc5a5WgOhSxkFxF3RJp9.q2l8KrGj1iUbOWcL9O', 'active', NOW());

INSERT INTO user_roles (user_id, role_id, business_id)
SELECT u.id, r.id, NULL
FROM users u, roles r
WHERE u.email = 'admin@workshopos.com' AND r.slug = 'super_admin';

INSERT INTO notification_templates (business_id, template_key, name, channel, subject, body, variables) VALUES
(NULL, 'registration_confirmed', 'Registration Confirmed', 'email',
 'Registration Confirmed - {{workshop_name}}',
 'Dear {{participant_name}},\n\nYour registration for {{workshop_name}} has been confirmed!\n\nDate: {{date}}\nTime: {{time}}\nVenue: {{venue}}\n\nYour QR Code: {{qr_url}}\n\nThank you,\n{{organizer_name}}',
 '{"participant_name": "Participant full name", "workshop_name": "Workshop name", "date": "Event date", "time": "Event time", "venue": "Event venue", "qr_url": "QR code URL", "organizer_name": "Organizer name"}'),
(NULL, 'payment_approved', 'Payment Approved', 'email',
 'Payment Approved - {{workshop_name}}',
 'Dear {{participant_name}},\n\nYour payment for {{workshop_name}} has been approved.\n\nAmount: {{amount}} {{currency}}\n\nYour registration is now CONFIRMED.\n\nYour QR Code will be sent shortly: {{qr_url}}\n\nThank you,\n{{organizer_name}}',
 '{"participant_name": "Participant name", "workshop_name": "Workshop name", "amount": "Payment amount", "currency": "Currency", "qr_url": "QR URL", "organizer_name": "Organizer"}'),
(NULL, 'payment_rejected', 'Payment Rejected', 'email',
 'Payment Verification Failed - {{workshop_name}}',
 'Dear {{participant_name}},\n\nUnfortunately, your payment proof for {{workshop_name}} could not be verified.\n\nReason: {{rejection_reason}}\n\nPlease resubmit your payment proof or contact us at {{support_email}}.\n\nThank you,\n{{organizer_name}}',
 '{"participant_name": "Participant name", "workshop_name": "Workshop name", "rejection_reason": "Reason", "support_email": "Support email", "organizer_name": "Organizer"}'),
(NULL, 'workshop_reminder_7d', 'Workshop Reminder (7 Days)', 'email',
 'Reminder: {{workshop_name}} is in 7 days!',
 'Dear {{participant_name}},\n\nThis is a reminder that {{workshop_name}} is coming up in 7 days!\n\nDate: {{date}}\nTime: {{time}}\nVenue: {{venue}}\n\nYour QR Code: {{qr_url}}\n\nSee you there!\n{{organizer_name}}',
 '{"participant_name": "Participant name", "workshop_name": "Workshop name", "date": "Date", "time": "Time", "venue": "Venue", "qr_url": "QR URL", "organizer_name": "Organizer"}'),
(NULL, 'workshop_reminder_1d', 'Workshop Reminder (1 Day)', 'email',
 'Tomorrow: {{workshop_name}}!',
 'Dear {{participant_name}},\n\n{{workshop_name}} is TOMORROW!\n\nDate: {{date}}\nTime: {{time}}\nVenue: {{venue}}\n\nDon\'t forget your QR code: {{qr_url}}\n\nSee you tomorrow!\n{{organizer_name}}',
 '{"participant_name": "Participant name", "workshop_name": "Workshop name", "date": "Date", "time": "Time", "venue": "Venue", "qr_url": "QR URL", "organizer_name": "Organizer"}'),
(NULL, 'certificate_available', 'Certificate Available', 'email',
 'Your Certificate is Ready - {{workshop_name}}',
 'Dear {{participant_name}},\n\nCongratulations! Your certificate for {{workshop_name}} is now available.\n\nCertificate Number: {{certificate_number}}\nDownload: {{certificate_url}}\nVerify: {{verification_url}}\n\nThank you for participating!\n{{organizer_name}}',
 '{"participant_name": "Participant name", "workshop_name": "Workshop name", "certificate_number": "Cert number", "certificate_url": "Download URL", "verification_url": "Verify URL", "organizer_name": "Organizer"}'),
(NULL, 'feedback_request', 'Feedback Request', 'email',
 'Share Your Feedback - {{workshop_name}}',
 'Dear {{participant_name}},\n\nThank you for attending {{workshop_name}}!\n\nWe\'d love to hear your feedback. Please take 2 minutes to complete our feedback form:\n\n{{feedback_url}}\n\nYour feedback helps us improve future workshops.\n\nThank you,\n{{organizer_name}}',
 '{"participant_name": "Participant name", "workshop_name": "Workshop name", "feedback_url": "Feedback form URL", "organizer_name": "Organizer"}'),
(NULL, 'waitlist_available', 'Waitlist Seat Available', 'email',
 'A Seat is Available - {{workshop_name}}',
 'Dear {{participant_name}},\n\nGood news! A seat has become available for {{workshop_name}}.\n\nTo secure your spot, please complete your registration within 24 hours:\n\n{{registration_url}}\n\nThis offer expires: {{expiry_time}}\n\nThank you,\n{{organizer_name}}',
 '{"participant_name": "Participant name", "workshop_name": "Workshop name", "registration_url": "Registration URL", "expiry_time": "Expiry time", "organizer_name": "Organizer"}'),
(NULL, 'workshop_activated', 'Workshop Activated', 'email',
 'Your Workshop is Now Active - {{workshop_name}}',
 'Dear {{contact_name}},\n\nYour workshop platform fee has been verified and {{workshop_name}} is now ACTIVE!\n\nYou can now:\n- Configure registration fields\n- Publish your workshop\n- Start accepting registrations\n\nManage your workshop: {{workshop_url}}\n\nThank you,\nWorkshop OS Team',
 '{"contact_name": "Contact name", "workshop_name": "Workshop name", "workshop_url": "Workshop management URL"}');
