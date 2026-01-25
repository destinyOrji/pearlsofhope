<?php
/**
 * Contact page - displays contact information and contact form
 * Improved version with better error handling and admin integration
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/contact_fallback.php';

// Set page title
$page_title = 'Contact Us';

// Initialize variables
$success_message = '';
$error_message = '';
$form_data = [
    'name' => '',
    'email' => '',
    'subject' => '',
    'message' => ''
];

// Check for success message from redirect
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = 'Thank you for your message! We will get back to you soon.';
}

// Process contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_form_submit'])) {
    error_log("Contact form submission received");
    
    // Get and sanitize form data
    $form_data['name'] = sanitizeInput($_POST['name'] ?? '');
    $form_data['email'] = sanitizeInput($_POST['email'] ?? '');
    $form_data['subject'] = sanitizeInput($_POST['subject'] ?? '');
    $form_data['message'] = sanitizeInput($_POST['message'] ?? '');
    
    error_log("Form data: " . json_encode($form_data));
    
    $errors = [];
    
    // Comprehensive validation
    if (empty($form_data['name']) || strlen($form_data['name']) < 2) {
        $errors[] = 'Name must be at least 2 characters long';
    } elseif (strlen($form_data['name']) > 100) {
        $errors[] = 'Name must be less than 100 characters';
    }
    
    if (empty($form_data['email']) || !filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    } elseif (strlen($form_data['email']) > 100) {
        $errors[] = 'Email must be less than 100 characters';
    }
    
    if (empty($form_data['subject'])) {
        $errors[] = 'Subject is required';
    } elseif (strlen($form_data['subject']) > 255) {
        $errors[] = 'Subject must be less than 255 characters';
    }
    
    if (empty($form_data['message']) || strlen($form_data['message']) < 10) {
        $errors[] = 'Message must be at least 10 characters long';
    } elseif (strlen($form_data['message']) > 5000) {
        $errors[] = 'Message must be less than 5000 characters';
    }
    
    // Check for honeypot spam (but don't block if empty)
    if (isset($_POST['website']) && !empty($_POST['website'])) {
        $errors[] = 'Spam detected';
        error_log("Spam detected via honeypot field");
    }
    
    if (empty($errors)) {
        // Save message to database - prioritize MongoDB Atlas
        $saved = false;
        $save_method = '';
        
        try {
            // Try MongoDB Atlas first
            $contactMessagesCollection = getCollection('contact_messages');
            
            if ($contactMessagesCollection) {
                $messageDocument = [
                    'name' => $form_data['name'],
                    'email' => $form_data['email'],
                    'subject' => $form_data['subject'],
                    'message' => $form_data['message'],
                    'status' => 'unread',
                    'created_at' => class_exists('MongoDB\BSON\UTCDateTime') ? new MongoDB\BSON\UTCDateTime() : date('Y-m-d H:i:s'), 
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                    'source' => 'contact_form'
                ];
                
                $result = insertDocument($contactMessagesCollection, $messageDocument);
                if ($result && $result->getInsertedCount() > 0) {
                    $saved = true;
                    $save_method = 'MongoDB Atlas';
                    error_log("Contact message saved to MongoDB Atlas with ID: " . $result->getInsertedId());
                }
            }
            
        } catch (Exception $e) {
            error_log("MongoDB Atlas save failed: " . $e->getMessage());
        }
        
        // If MongoDB save failed and not in production, try file fallback
        if (!$saved && !IS_PRODUCTION) {
            try {
                $saved = saveContactMessageToFile($form_data);
                if ($saved) {
                    $save_method = 'File';
                    error_log("Contact message saved to file (MongoDB unavailable)");
                }
            } catch (Exception $e) {
                error_log("File save failed: " . $e->getMessage());
            }
        }
        
        if ($saved) {
            // Log successful submission
            error_log("Contact form submission successful via {$save_method} - Name: {$form_data['name']}, Email: {$form_data['email']}");
            
            // Redirect immediately to prevent form resubmission
            header('Location: contact.php?success=1');
            exit();
        } else {
            $error_message = IS_PRODUCTION ? 
                'Sorry, there was an error sending your message. Please try again later.' :
                'Sorry, there was an error saving your message. Please try again later or contact us directly via email.';
            error_log("Contact form submission failed - all save methods failed");
        }
    } else {
        $error_message = implode('<br>', $errors);
        error_log("Contact form validation failed: " . implode(', ', $errors));
    }
}

// Get contact page content from MongoDB
$pagesCollection = getCollection('pages');
$contactContent = null;

if ($pagesCollection) {
    try {
        $contactContent = findOneDocument($pagesCollection, ['page_name' => 'contact']);
    } catch (Exception $e) {
        error_log("Error fetching contact page content: " . $e->getMessage());
    }
}

// Default content if not found in database
$defaultTitle = 'Contact Us';
$defaultContent = 'We would love to hear from you. Get in touch with us for any questions, suggestions, or to learn more about how you can get involved with our mission.';

$contactTitle = $contactContent ? sanitizeOutput($contactContent['title'] ?? $defaultTitle) : $defaultTitle;
$contactText = $contactContent ? sanitizeOutput($contactContent['content'] ?? $defaultContent) : $defaultContent;

// Include header
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary mb-3"><?php echo $contactTitle; ?></h1>
                <div class="border-bottom border-primary mx-auto mb-4" style="width: 100px; height: 3px;"></div>
                <p class="lead text-muted"><?php echo nl2br($contactText); ?></p>
            </div>

            <div class="row">
                <!-- Contact Information -->
                <div class="col-lg-4 mb-4">
                    <div class="contact-info">
                        <h4><i class="fas fa-info-circle me-2"></i>Get In Touch</h4>
                        
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Address:</strong><br>
                                5, Prof. Abowie street,<br>
                                GRA Phase 3 Port Harcourt
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Phone:</strong><br>
                                +(234) 7043717685
                            </div>
                        </div>
                        
                        <div class="contact-item"> 
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Email:</strong><br>
                                pearlsofhope001@gmail.com
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Office Hours:</strong><br>
                                Monday - Friday: 9:00 AM - 5:00 PM<br>
                                Saturday: 10:00 AM - 2:00 PM<br>
                                Sunday: Closed
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="text-center mt-4">
                        <h5 class="mb-3">Follow Us</h5>
                        <div class="social-links">
                            <a href="#" class="btn btn-outline-primary btn-sm me-2 mb-2">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </a>
                            <a href="#" class="btn btn-outline-info btn-sm me-2 mb-2">
                                <i class="fab fa-twitter"></i> Twitter
                            </a>
                            <a href="#" class="btn btn-outline-danger btn-sm me-2 mb-2">
                                <i class="fab fa-instagram"></i> Instagram
                            </a>
                            <a href="#" class="btn btn-outline-primary btn-sm mb-2">
                                <i class="fab fa-linkedin-in"></i> LinkedIn
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h4 class="card-title mb-4">
                                <i class="fas fa-paper-plane me-2 text-primary"></i>Send Us a Message
                            </h4>

                            <!-- Success/Error Messages -->
                            <?php if (!empty($success_message)): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <!-- Contact Form -->
                            <form method="POST" action="" id="contactForm" novalidate>
                                <input type="hidden" name="contact_form_submit" value="1">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">
                                            <i class="fas fa-user me-1"></i>Full Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="name" 
                                               name="name" 
                                               value="<?php echo sanitizeOutput($form_data['name']); ?>"
                                               required 
                                               minlength="2" 
                                               maxlength="100"
                                               placeholder="Enter your full name">
                                        <div class="invalid-feedback">
                                            Please provide a valid name (2-100 characters).
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope me-1"></i>Email Address <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="email" 
                                               name="email" 
                                               value="<?php echo sanitizeOutput($form_data['email']); ?>"
                                               required 
                                               maxlength="100"
                                               placeholder="Enter your email address">
                                        <div class="invalid-feedback">
                                            Please provide a valid email address.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="subject" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Subject <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="subject" name="subject" required>
                                        <option value="">Choose a subject...</option>
                                        <option value="General Inquiry" <?php echo $form_data['subject'] === 'General Inquiry' ? 'selected' : ''; ?>>General Inquiry</option>
                                        <option value="Volunteer Opportunity" <?php echo $form_data['subject'] === 'Volunteer Opportunity' ? 'selected' : ''; ?>>Volunteer Opportunity</option>
                                        <option value="Donation Information" <?php echo $form_data['subject'] === 'Donation Information' ? 'selected' : ''; ?>>Donation Information</option>
                                        <option value="Partnership" <?php echo $form_data['subject'] === 'Partnership' ? 'selected' : ''; ?>>Partnership</option>
                                        <option value="Media Inquiry" <?php echo $form_data['subject'] === 'Media Inquiry' ? 'selected' : ''; ?>>Media Inquiry</option>
                                        <option value="Other" <?php echo $form_data['subject'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a subject.
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="message" class="form-label">
                                        <i class="fas fa-comment me-1"></i>Message <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" 
                                              id="message" 
                                              name="message" 
                                              rows="6" 
                                              required 
                                              minlength="10" 
                                              maxlength="5000"
                                              placeholder="Enter your message (minimum 10 characters)"><?php echo sanitizeOutput($form_data['message']); ?></textarea>
                                    <div class="invalid-feedback">
                                        Please provide a message (10-5000 characters).
                                    </div>
                                    <div class="form-text">
                                        <span id="message-count">0</span> / 5000 characters
                                    </div>
                                </div>
                                
                                <!-- Simple honeypot for spam protection -->
                                <div style="display: none;">
                                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="contact_form_submit" class="btn btn-primary btn-lg" id="submitBtn">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="bg-light p-4 rounded">
                        <div class="row text-center">
                            <div class="col-md-4 mb-3">
                                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 60px; height: 60px;">
                                    <i class="fas fa-question-circle fa-lg"></i>
                                </div>
                                <h5>Have Questions?</h5>
                                <p class="text-muted mb-0">We're here to help! Don't hesitate to reach out with any questions about our programs or how to get involved.</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 60px; height: 60px;">
                                    <i class="fas fa-handshake fa-lg"></i>
                                </div>
                                <h5>Want to Volunteer?</h5>
                                <p class="text-muted mb-0">Join our team of dedicated volunteers and make a direct impact in your community. Every helping hand counts!</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 60px; height: 60px;">
                                    <i class="fas fa-heart fa-lg"></i>
                                </div>
                                <h5>Make a Donation?</h5>
                                <p class="text-muted mb-0">Your generous donations help us continue our mission and expand our reach to help more people in need.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced JavaScript for form validation and user experience -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Enhanced contact form JavaScript loaded');
    
    const form = document.getElementById('contactForm');
    const messageTextarea = document.getElementById('message');
    const messageCount = document.getElementById('message-count');
    const submitBtn = document.getElementById('submitBtn');
    
    // Character count for message textarea
    function updateCharacterCount() {
        if (messageTextarea && messageCount) {
            const count = messageTextarea.value.length;
            messageCount.textContent = count;
            
            // Update color based on character count
            if (count > 5000) {
                messageCount.style.color = '#dc3545';
                messageCount.parentElement.classList.add('text-danger');
            } else if (count > 4500) {
                messageCount.style.color = '#ffc107';
                messageCount.parentElement.classList.remove('text-danger');
            } else {
                messageCount.style.color = '#6c757d';
                messageCount.parentElement.classList.remove('text-danger');
            }
        }
    }
    
    // Initialize character count
    if (messageTextarea) {
        messageTextarea.addEventListener('input', updateCharacterCount);
        updateCharacterCount();
    }
    
    // Enhanced form validation
    function validateForm() {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            }
        });
        
        // Email validation
        const email = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email.value && !emailRegex.test(email.value)) {
            email.classList.add('is-invalid');
            isValid = false;
        }
        
        // Message length validation
        if (messageTextarea.value.length < 10 || messageTextarea.value.length > 5000) {
            messageTextarea.classList.add('is-invalid');
            isValid = false;
        }
        
        return isValid;
    }
    
    // Real-time validation
    form.querySelectorAll('input, select, textarea').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
    
    // Form submission handling
    if (form) {
        form.addEventListener('submit', function(event) {
            console.log('Form submission started');
            
            // Check honeypot
            const honeypot = form.querySelector('input[name="website"]');
            if (honeypot && honeypot.value) {
                event.preventDefault();
                alert('Spam detected. Please try again.');
                return false;
            }
            
            // Validate form
            if (!validateForm()) {
                event.preventDefault();
                alert('Please fill in all required fields correctly.');
                return false;
            }
            
            // Show loading state
            if (submitBtn) {
                const originalContent = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
                
                // Re-enable after timeout as safety measure
                setTimeout(function() {
                    if (submitBtn.disabled) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalContent;
                        alert('Form submission is taking longer than expected. Please try again.');
                    }
                }, 10000); // 10 seconds timeout
            }
        });
    }
    
    // Auto-dismiss alerts after 8 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert .btn-close');
        alerts.forEach(function(closeBtn) {
            closeBtn.click();
        });
    }, 8000);
    
    console.log('Enhanced contact form JavaScript initialized');
});
</script>

<style>
/* Enhanced CSS for better user experience */
.contact-info {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 10px;
    margin-bottom: 2rem;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.contact-item i {
    color: #007bff;
    font-size: 1.2rem;
    margin-right: 1rem;
    margin-top: 0.2rem;
    min-width: 20px;
}

.contact-item div {
    flex: 1;
}

.social-links .btn {
    transition: all 0.3s ease;
}

.social-links .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.is-valid {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
}

.alert {
    animation: slideIn 0.3s ease-out;
    border: none;
    border-radius: 10px;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.card {
    border-radius: 15px;
    overflow: hidden;
}

.btn-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border: none;
    border-radius: 25px;
    padding: 0.75rem 2rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0, 123, 255, 0.3);
}

/* Responsive improvements */
@media (max-width: 768px) {
    .contact-info {
        padding: 1.5rem;
    }
    
    .contact-item {
        padding: 0.75rem;
        margin-bottom: 1rem;
    }
    
    .social-links .btn {
        margin-bottom: 0.5rem;
        width: 100%;
    }
}
</style>

<?php
// Include footer
include __DIR__ . '/../includes/footer.php';
?>