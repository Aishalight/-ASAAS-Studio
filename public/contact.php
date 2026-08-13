<?php
$seoTitle = 'Contact | ASAAS Studio Somalia';
$seoDesc = 'Get in touch with ASAAS Studio in Mogadishu, Somalia. Tell us about your project and we will reply with next steps and a clear quote.';
$seoKeywords = 'contact ASAAS studio, hire web designer Somalia, book consultation Somalia, web design inquiry Mogadishu, ASAAS studio contact';
require __DIR__ . '/../includes/header.php'; ?>

<?php
$db = Database::getInstance()->getConnection();
$formType = $_POST['form_type'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $formType === 'contact') {
    $rateKey = 'contact_submit_' . ($_SERVER['REMOTE_ADDR'] ?? '0');
    if (isset($_SESSION[$rateKey]) && time() - $_SESSION[$rateKey] < 60) {
        $error = 'Please wait a moment before submitting again.';
    } elseif (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        if ($name && $email && $message && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $sql = "INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$name, $email, $subject, $message]);
                $_SESSION[$rateKey] = time();
                $success = 'Thanks for reaching out. We review every message personally and will respond by email.';
            } catch (Exception $e) {
                error_log('Contact form error: ' . $e->getMessage());
                $error = 'Something went wrong. Please try again.';
            }
        } else {
            $error = 'Please fill in all required fields with a valid email.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $formType === 'booking') {
    $rateKey = 'booking_submit_' . ($_SERVER['REMOTE_ADDR'] ?? '0');
    if (isset($_SESSION[$rateKey]) && time() - $_SESSION[$rateKey] < 60) {
        $bookingError = 'Please wait a moment before submitting again.';
    } elseif (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $bookingError = 'Invalid security token. Please try again.';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $preferredDate = sanitize($_POST['preferred_date'] ?? '');
        $preferredTime = sanitize($_POST['preferred_time'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        if ($name && $email && $preferredDate && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $sql = "INSERT INTO bookings (name, email, phone, preferred_date, preferred_time, message) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$name, $email, $phone, $preferredDate, $preferredTime, $message]);
                $_SESSION[$rateKey] = time();
                $bookingSuccess = 'Your consultation call has been booked! We will contact you shortly to confirm.';
            } catch (Exception $e) {
                error_log('Booking form error: ' . $e->getMessage());
                $bookingError = 'Something went wrong. Please try again.';
            }
        } else {
            $bookingError = 'Please fill in your name, email, and preferred date.';
        }
    }
}
?>

<main class="page-transition">
    <section style="padding-top:calc(var(--header-height) + 60px);background:var(--bg-light)">
        <div class="container" style="padding:60px 0">
            <div class="fade-in-up" style="text-align:center;margin-bottom:56px">
                <div class="section-tag"><i data-lucide="mail" size="16"></i>Get in Touch</div>
                <h1 style="font-size:clamp(2rem,4vw,3rem);margin-bottom:12px">Let's Start a <span class="gradient-text">Conversation</span></h1>
                <p style="color:var(--text-secondary);font-size:17px;max-width:520px;margin:0 auto">Tell us what you want to build and we will get back to you with next steps and a clear quote.</p>
            </div>

            <div class="grid" style="grid-template-columns:1.4fr 1fr;gap:32px;align-items:start;margin-bottom:32px">

                <!-- ===== CONTACT FORM ===== -->
                <div class="reveal">
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success" style="margin-bottom:20px"><i data-lucide="check-circle" size="20"></i> <?= $success ?></div>
                    <?php endif; ?>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-error" style="margin-bottom:20px"><i data-lucide="alert-circle" size="20"></i> <?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST" data-validate style="background:var(--bg-white);padding:36px;border-radius:var(--radius-lg);border:1px solid var(--border);box-shadow:var(--shadow-md)">
                        <input type="hidden" name="form_type" value="contact">
                        <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label">Full Name <span style="color:var(--primary)">*</span></label>
                                <input type="text" name="name" class="form-input" placeholder="John Doe" required>
                                <div class="form-error"></div>
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label">Email <span style="color:var(--primary)">*</span></label>
                                <input type="email" name="email" class="form-input" placeholder="john@example.com" required>
                                <div class="form-error"></div>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:16px">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-input" placeholder="How can we help?">
                        </div>
                        <div class="form-group" style="margin-bottom:20px">
                            <label class="form-label">Message <span style="color:var(--primary)">*</span></label>
                            <textarea name="message" class="form-textarea" placeholder="Tell us about your project..." rows="5" required></textarea>
                            <div class="form-error"></div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg">
                            Send Message <i data-lucide="send" size="18"></i>
                        </button>
                    </form>
                </div>

                <!-- ===== CONTACT INFO ===== -->
                <div class="reveal">
                    <div style="background:var(--bg-white);padding:28px;border-radius:var(--radius-lg);border:1px solid var(--border);box-shadow:var(--shadow-sm)">
                        <h4 style="font-size:16px;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid var(--border)">Contact Information</h4>
                        <div style="display:flex;flex-direction:column;gap:20px">
                            <div style="display:flex;align-items:center;gap:14px">
                                <div style="width:40px;height:40px;min-width:40px;border-radius:var(--radius-sm);background:var(--primary-alpha);display:flex;align-items:center;justify-content:center;color:var(--primary)">
                                    <i data-lucide="mail" size="18"></i>
                                </div>
                                <div>
                                    <p style="font-size:13px;font-weight:600;color:var(--text-muted);margin-bottom:1px">Email</p>
                                    <a href="mailto:info@asaas-studio.tech" style="font-size:14px;color:var(--text-primary);text-decoration:none">info@asaas-studio.tech</a>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:14px">
                                <div style="width:40px;height:40px;min-width:40px;border-radius:var(--radius-sm);background:var(--primary-alpha);display:flex;align-items:center;justify-content:center;color:var(--primary)">
                                    <i data-lucide="map-pin" size="18"></i>
                                </div>
                                <div>
                                    <p style="font-size:13px;font-weight:600;color:var(--text-muted);margin-bottom:1px">Location</p>
                                    <p style="font-size:14px;color:var(--text-primary)">Mogadishu, Somalia</p>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:14px">
                                <div style="width:40px;height:40px;min-width:40px;border-radius:var(--radius-sm);background:var(--primary-alpha);display:flex;align-items:center;justify-content:center;color:var(--primary)">
                                    <i data-lucide="globe" size="18"></i>
                                </div>
                                <div>
                                    <p style="font-size:13px;font-weight:600;color:var(--text-muted);margin-bottom:1px">Website</p>
                                    <a href="https://asaas-studio.tech" target="_blank" rel="noopener" style="font-size:14px;color:var(--text-primary);text-decoration:none">asaas-studio.tech</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== BOOK A CALL ===== -->
            <div class="reveal" id="booking" style="background:var(--bg-dark);padding:32px;border-radius:var(--radius-lg);position:relative;overflow:hidden">
                <div style="position:absolute;top:-60px;right:-60px;width:160px;height:160px;border-radius:50%;background:var(--primary);opacity:0.06"></div>
                <div style="position:relative;z-index:1">
                    <div style="text-align:center;margin-bottom:24px">
                        <div class="section-tag" style="background:rgba(232,99,42,0.15);color:#f07840;margin-bottom:10px"><i data-lucide="calendar" size="14"></i>Free Consultation</div>
                        <h4 style="color:white;font-size:18px;margin-bottom:4px">Book a Call</h4>
                        <p style="color:var(--text-light);font-size:13px">Pick a date and time that works for you. We will confirm by email.</p>
                    </div>

                    <?php if (isset($bookingSuccess)): ?>
                        <div class="alert alert-success" style="margin-bottom:20px;max-width:500px;margin-left:auto;margin-right:auto"><i data-lucide="check-circle" size="18"></i> <?= $bookingSuccess ?></div>
                    <?php endif; ?>
                    <?php if (isset($bookingError)): ?>
                        <div class="alert alert-error" style="margin-bottom:20px;max-width:500px;margin-left:auto;margin-right:auto"><i data-lucide="alert-circle" size="18"></i> <?= $bookingError ?></div>
                    <?php endif; ?>

                    <form method="POST" style="max-width:640px;margin:0 auto">
                        <input type="hidden" name="form_type" value="booking">
                        <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label" style="color:rgba(255,255,255,0.6);font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:0.5px">Name <span style="color:var(--primary)">*</span></label>
                                <input type="text" name="name" class="form-input" placeholder="Your name" required style="background:rgba(255,255,255,0.06);border-color:rgba(255,255,255,0.1);color:white;font-size:13px;padding:10px 12px">
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label" style="color:rgba(255,255,255,0.6);font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:0.5px">Email <span style="color:var(--primary)">*</span></label>
                                <input type="email" name="email" class="form-input" placeholder="your@email.com" required style="background:rgba(255,255,255,0.06);border-color:rgba(255,255,255,0.1);color:white;font-size:13px;padding:10px 12px">
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px">
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label" style="color:rgba(255,255,255,0.6);font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:0.5px">Phone</label>
                                <input type="tel" name="phone" class="form-input" placeholder="Phone (optional)" style="background:rgba(255,255,255,0.06);border-color:rgba(255,255,255,0.1);color:white;font-size:13px;padding:10px 12px">
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label" style="color:rgba(255,255,255,0.6);font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:0.5px">Date <span style="color:var(--primary)">*</span></label>
                                <input type="date" name="preferred_date" class="form-input" required style="background:rgba(255,255,255,0.06);border-color:rgba(255,255,255,0.1);color:white;font-size:13px;padding:10px 12px">
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label" style="color:rgba(255,255,255,0.6);font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:0.5px">Time</label>
                                <input type="time" name="preferred_time" class="form-input" style="background:rgba(255,255,255,0.06);border-color:rgba(255,255,255,0.1);color:white;font-size:13px;padding:10px 12px">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:16px">
                            <label class="form-label" style="color:rgba(255,255,255,0.6);font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:0.5px">Project Details</label>
                            <textarea name="message" class="form-textarea" placeholder="Tell us briefly about your project..." rows="2" style="background:rgba(255,255,255,0.06);border-color:rgba(255,255,255,0.1);color:white;font-size:13px;padding:10px 12px;resize:vertical"></textarea>
                        </div>
                        <div style="text-align:center">
                            <button type="submit" class="btn btn-primary" style="padding:10px 32px;font-size:14px">
                                Book a Call <i data-lucide="calendar" size="16"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
