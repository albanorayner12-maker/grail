<?php
session_start();
require_once 'db.php'; // Siguraduhing tama ang path ng iyong pdo db connection

// Generate CSRF token for security
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if we need to display the Pop-up UI
$show_thank_you_popup = false;
$generated_token_display = '';
$was_submission_anonymous = false;

if (isset($_SESSION['trigger_thank_you']) && $_SESSION['trigger_thank_you'] === true) {
    $show_thank_you_popup = true;
    $generated_token_display = $_SESSION['last_generated_token'] ?? '';
    $was_submission_anonymous = $_SESSION['last_submission_anonymous'] ?? false;
    
    // Clear it instantly so it won't loop on refresh
    unset($_SESSION['trigger_thank_you']);
    unset($_SESSION['last_generated_token']);
    unset($_SESSION['last_submission_anonymous']);
}

// ========================================================
// BACKEND LOGIC: TATAKBO ITO KAPAG PININDOT ANG SUBMIT BUTTON
// ========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. I-verify ang CSRF Token laban sa form manipulation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Security validation failed. Invalid CSRF token.";
        header("Location: submit_report.php");
        exit();
    }

    // 2. Kuhanin at linisin ang mga data mula sa inputs
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    
    // Kung anonymous, i-save natin bilang "Anonymous", kung hindi, kuhanin ang tinype niya
    $reporter_name = $is_anonymous ? "Anonymous" : trim($_POST['reporter_name']);
    $reporter_email = $is_anonymous ? null : trim($_POST['reporter_email']);
    $reporter_phone = $is_anonymous ? null : trim($_POST['reporter_phone']);
    $user_type = $is_anonymous ? "Other" : $_POST['user_type'];
    $reporter_id = $is_anonymous ? null : trim($_POST['reporter_id']);
    
    $category = $_POST['category'];
    $subject = trim($_POST['subject']);
    $incident_date = !empty($_POST['incident_date']) ? $_POST['incident_date'] : date('Y-m-d');
    $priority = $_POST['priority'];
    $incident_location = trim($_POST['incident_location']);
    $description = trim($_POST['description']);
    $status = 'Pending'; // Default state para sa dashboard pipeline
    
    // GENERATE UNIQUE TRACKING TOKEN
    $token_segment_1 = strtoupper(bin2hex(random_bytes(2))); 
    $token_segment_2 = strtoupper(bin2hex(random_bytes(2))); 
    $tracking_token = "GRL-" . $token_segment_1 . "-" . $token_segment_2;

    $evidence_path = null;

    // 3. Simple Validation para sa mga Required Fields
    if (empty($subject) || empty($description) || empty($category)) {
        $_SESSION['error'] = "Please fill in all required fields.";
    } else {
        try {
            // 4. File Upload Handler para sa Supporting Evidence/Documents
            if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] == UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = pathinfo($_FILES['evidence']['name'], PATHINFO_EXTENSION);
                // Bigyan ng unique name ang file para walang kaparehas sa storage
                $new_file_name = uniqid('evidence_', true) . '.' . $file_extension;
                $target_file = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($_FILES['evidence']['tmp_name'], $target_file)) {
                    $evidence_path = $target_file;
                }
            }

            // 5. I-execute ang Secure SQL Query papunta sa grievances table (including the tracking token)
            $sql = "INSERT INTO grievances (
                        is_anonymous, name, email, phone, user_type, id_number, 
                        category, subject, incident_date, priority, location, 
                        description, evidence, status, tracking_token, created_at
                    ) VALUES (
                        :is_anonymous, :name, :email, :phone, :user_type, :id_number, 
                        :category, :subject, :incident_date, :priority, :location, 
                        :description, :evidence, :status, :tracking_token, NOW()
                    )";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                'is_anonymous' => $is_anonymous,
                'name' => $reporter_name,
                'email' => $reporter_email,
                'phone' => $reporter_phone,
                'user_type' => $user_type,
                'id_number' => $reporter_id,
                'category' => $category,
                'subject' => $subject,
                'incident_date' => $incident_date,
                'priority' => $priority,
                'location' => $incident_location,
                'description' => $description,
                'evidence' => $evidence_path,
                'status' => $status,
                'tracking_token' => $tracking_token
            ]);

            if ($result) {
                // SUCCESS LOGIC: Pass the token to session variables for display in our layout pop-up modal
                $_SESSION['trigger_thank_you'] = true;
                $_SESSION['last_generated_token'] = $tracking_token;
                $_SESSION['last_submission_anonymous'] = (bool)$is_anonymous;
                header("Location: submit_report.php");
                exit();
            } else {
                $_SESSION['error'] = "Failed to submit report. Please try again.";
            }

        } catch (PDOException $e) {
            $_SESSION['error'] = "Database Error: " . $e->getMessage();
        }
    }
    
    // Kung may error sa validation, i-refresh ang page para ipakita ang error box
    header("Location: submit_report.php");
    exit();
}

// Display error messages kung nag-reload ang form
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GRAIL | Submit Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/home.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; display: flex; flex-direction: column; }
        .top-header { background-color: #ffffff; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e0e0e0; }
        .location-text { display: flex; align-items: center; gap: 10px; text-align: right; }
        .location-pin { color: #f4105c; font-size: 20px; }
        .location-text span { color: #1c1c1c; font-size: 16px; }
        .sub-nav { background-color: #053d15f6; padding: 12px 40px; }
        .back-btn { color: #ffffff; text-decoration: none; font-weight: 600; font-size: 16px; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; }
        .back-btn:hover { color: #a5d6a7; }
        .report-container { flex: 1; background-color: #2e7d32; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
        .report-card { background-color: #f5f5f5; width: 100%; max-width: 650px; border-radius: 20px; padding: 50px 40px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); }
        .report-title { text-align: center; font-size: 32px; font-weight: 700; color: #333; margin-bottom: 40px; }
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; color: #2e7d32; font-weight: 600; font-size: 15px; margin-bottom: 8px; }
        .form-input { width: 100%; padding: 12px 5px; border: none; border-bottom: 2px solid #2e7d32; background: transparent; font-size: 16px; color: #333; outline: none; transition: 0.3s; }
        .form-input:focus { border-bottom-color: #1b5e20; background-color: rgba(46, 125, 50, 0.05); }
        .form-input::placeholder { color: #999; }
        .form-select { width: 100%; padding: 12px 5px; border: none; border-bottom: 2px solid #2e7d32; background: transparent; font-size: 16px; color: #333; outline: none; cursor: pointer; }
        .form-select:focus { border-bottom-color: #1b5e20; }
        .form-textarea { width: 100%; padding: 12px 5px; border: none; border-bottom: 2px solid #2e7d32; background: transparent; font-size: 16px; color: #333; outline: none; resize: vertical; min-height: 100px; }
        .form-textarea:focus { border-bottom-color: #1b5e20; background-color: rgba(46, 125, 50, 0.05); }
        .form-file { width: 100%; padding: 10px 5px; border: none; border-bottom: 2px solid #2e7d32; background: transparent; font-size: 14px; color: #333; }
        .submit-btn { width: 100%; padding: 15px; background-color: #2e7d32; color: #ffffff; border: none; border-radius: 30px; font-size: 18px; font-weight: 600; cursor: pointer; transition: 0.3s; margin-top: 20px; }
        .submit-btn:hover { background-color: #1b5e20; transform: translateY(-2px); box-shadow: 0 5px 20px rgba(46, 125, 50, 0.4); }
        .alert-custom { border-radius: 10px; margin-bottom: 20px; }
        .anonymous-check { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
        .anonymous-check input { width: 18px; height: 18px; accent-color: #2e7d32; }
        .anonymous-check label { color: #555; font-size: 14px; }
        .confirm-check { display: flex; align-items: flex-start; gap: 10px; margin-top: 20px; }
        .confirm-check input { width: 18px; height: 18px; margin-top: 4px; accent-color: #2e7d32; }
        .confirm-check label { color: #555; font-size: 14px; }
        .required-field::after { content: " *"; color: #d32f2f; }
    </style>
</head>
<body>

    <header class="top-header">
       <div class="container d-flex justify-content-between align-items-center">
        <img src="assets/css/img/wword-removebg.png" alt="GRAIL" height="60">
        <div class="location-text">
            <i class="fas fa-map-marker-alt location-pin"></i>
            <span>Quezon St., Bayombong, Nueva Vizcaya</span>
        </div>
       </div>
    </header>

    <nav class="sub-nav">
        <a href="index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </nav>

    <div class="report-container">
        <div class="report-card">
            
            <h2 class="report-title">Report a Grievance</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="submit_report.php" method="POST" enctype="multipart/form-data" id="grievanceForm">
                
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="anonymous-check">
                    <input type="checkbox" id="anonymous" name="is_anonymous" value="1">
                    <label for="anonymous">Submit anonymously (hide my identity)</label>
                </div>

                <div id="contactSection">
                    <div class="form-group">
                        <label class="required-field">Full Name</label>
                        <input type="text" class="form-input" id="reporter_name" name="reporter_name" placeholder="Enter your full name" required>
                    </div>

                    <div class="form-group">
                        <label class="required-field">Email Address</label>
                        <input type="email" class="form-input" id="reporter_email" name="reporter_email" placeholder="your@email.com" required>
                    </div>

                    <div class="form-group">
                        <label class="required-field">Phone Number</label>
                        <input type="tel" class="form-input" id="reporter_phone" name="reporter_phone" placeholder="+1234567890" required>
                    </div>

                    <div class="form-group">
                         <label class="required-field">USER</label>
                        <select class="form-select" id="user_type" name="user_type" required>
                            <option value="" disabled selected>Select User Type</option>
                            <option value="Instructor">Instructor</option>
                            <option value="Student">Student</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="required-field">ID Number </label>
                        <input type="text" class="form-input" id="reporter_id" name="reporter_id" placeholder="231-000" required>
                    </div>
                </div>
             
                <div class="form-group">
                    <label class="required-field">Grievance Type</label>
                    <select class="form-select" name="category" required>
                        <option value="" disabled selected>Select Grievance Type</option>
                        <option value="harassment">Harassment</option>
                        <option value="discrimination">Discrimination</option>
                        <option value="safety">Safety Concern</option>
                        <option value="academic">Academic Issue</option>
                        <option value="administrative">Administrative</option>
                        <option value="financial">Financial</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="required-field">Subject / Title</label>
                    <input type="text" class="form-input" name="subject" required placeholder="Brief summary of the issue" maxlength="200">
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Date of Incident</label>
                            <input type="date" class="form-input" name="incident_date" max="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Priority Level</label>
                            <select class="form-select" name="priority">
                                <option value="medium" selected>Medium</option>
                                <option value="low">Low</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Location of Incident</label>
                    <input type="text" class="form-input" name="incident_location" placeholder="Where did this occur?">
                </div>

                <div class="form-group">
                    <label class="required-field">Description</label>
                    <textarea class="form-textarea" name="description" rows="5" required placeholder="Please provide all relevant details..."></textarea>
                </div>

                <div class="form-group">
                    <label>Upload Supporting File (Optional)</label>
                    <input type="file" class="form-file" name="evidence" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <small style="color: #666; font-size: 12px;">Max size: 5MB. Allowed: PDF, Images, Word docs</small>
                </div>

                <div class="confirm-check">
                    <input type="checkbox" required id="confirm">
                    <label for="confirm">I confirm that the information provided is accurate to the best of my knowledge.</label>
                </div>

                <button type="submit" class="submit-btn">Submit Report</button>
            </form>
        </div>
    </div>

  <?php if ($show_thank_you_popup): ?>
    <div id="thankYouOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 99999; display: flex; justify-content: center; align-items: center;">
        <div style="background: white; padding: 40px 30px; border-radius: 20px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.3); max-width: 460px; width: 90%; animation: fadeIn 0.3s ease-in-out;">
            
            <?php if ($was_submission_anonymous): ?>
                <div style="font-size: 65px; color: #e67e22; margin-bottom: 15px;">
                    <i class="fa-solid fa-user-secret"></i>
                </div>
                <h3 style="font-weight: 700; color: #333; margin-bottom: 10px; font-size: 24px;">Anonymous Report Logged</h3>
                <p style="color: #666; margin-bottom: 20px; line-height: 1.5; font-size: 14px;">
                    Your privacy is secure. Because you chose anonymity, you <strong>must save the secure token below</strong> to monitor progress or check updates later.
                </p>
                
                <div style="display: flex; align-items: center; justify-content: center; background: #f8f9fa; border: 1px solid #cbd5e1; padding: 12px; border-radius: 10px; margin-bottom: 25px;">
                    <code id="trackingTokenCode" style="font-size: 1.2rem; font-weight: 700; color: #1f6b3e; letter-spacing: 0.5px; margin-right: 15px; font-family: monospace;"><?= htmlspecialchars($generated_token_display) ?></code>
                    <button class="btn btn-sm btn-outline-secondary" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($generated_token_display) ?>'); alert('Tracking token copied to clipboard!');">
                        <i class="fa-regular fa-copy"></i> Copy
                    </button>
                </div>
            <?php else: ?>
                <div style="font-size: 75px; color: #2e7d32; margin-bottom: 20px;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h3 style="font-weight: 700; color: #333; margin-bottom: 10px; font-size: 26px;">Submission Received!</h3>
                <p style="color: #666; margin-bottom: 15px; line-height: 1.6; font-size: 15px;">Thank you for sending your report. The administration team will review your data promptly.</p>
                <p style="color: #888; font-size: 13px; margin-bottom: 25px;">Your tracking number is: <strong><?= htmlspecialchars($generated_token_display) ?></strong></p>
            <?php endif; ?>
            
            <a href="index.php" style="display: inline-block; background-color: #2e7d32; color: white; text-decoration: none; padding: 12px 35px; border-radius: 30px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(46,125,50,0.3); transition: 0.2s; width: 100%;">
                Dismiss to Home
            </a>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle contact fields and validation dynamically when anonymous is checked
        document.getElementById('anonymous').addEventListener('change', function() {
            const section = document.getElementById('contactSection');
            const fields = [
                document.getElementById('reporter_name'),
                document.getElementById('reporter_email'),
                document.getElementById('reporter_phone'),
                document.getElementById('user_type'),
                document.getElementById('reporter_id')
            ];
            
            if (this.checked) {
                section.style.opacity = '0.3';
                fields.forEach(field => {
                    field.required = false;
                    field.disabled = true;
                    field.value = ''; 
                });
            } else {
                section.style.opacity = '1';
                fields.forEach(field => {
                    field.disabled = false;
                    field.required = true;
                });
            }
        });
    </script>
</body>
</html>