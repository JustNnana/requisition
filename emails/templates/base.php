<?php
/**
 * GateWey Requisition Management System
 * Base Email Template
 * 
 * File: emails/templates/base.php
 * Purpose: Master email template with header, footer, and branding
 * 
 * Variables available:
 * - $content: Main email content (HTML)
 * - $title: Email title/heading
 * - $preheader: Email preheader text (optional)
 */

// Prevent direct access
defined('APP_ACCESS') or die('Direct access not permitted');

// Default values
$title = $title ?? 'Notification';
$preheader = $preheader ?? '';
$content = $content ?? '<p>No content provided.</p>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo htmlspecialchars($title); ?></title>
    <style>
        /* Reset Styles */
        body {
            margin: 0;
            padding: 0;
            min-width: 100%;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
        }
        
        /* Client-specific Styles */
        #outlook a { padding: 0; }
        .ReadMsgBody { width: 100%; }
        .ExternalClass { width: 100%; }
        .ExternalClass * { line-height: 100%; }
        
        /* Container */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        
       .email-header {
    background: #EC3338;
    padding: 30px 20px;
    text-align: center;
}
        
        .email-header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 28px;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #ffffff;
            margin-bottom: 10px;
            display: block;
        }
        .logo-img {
            height: 40px;
            width: auto;
            transition: var(--transition-normal);
        }
        
        /* Content */
        .email-content {
            padding: 40px 30px;
            background-color: #ffffff;
        }
        
        .email-content h2 {
            color: #333333;
            font-size: 24px;
            margin-top: 0;
            margin-bottom: 20px;
        }
        
        .email-content p {
            margin: 0 0 16px 0;
            color: #555555;
            line-height: 1.8;
        }
        
/* Info Box */
.info-box {
    background-color: #f8f9fa;
    border-left: 4px solid #EC3338;
    padding: 20px;
    margin: 25px 0;
    border-radius: 4px;
}

.info-box h3 {
    margin: 0 0 15px 0;
    color: #009F6C;
    font-size: 18px;
}
        
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            display: table-cell;
            width: 40%;
            font-weight: 600;
            color: #333333;
            padding: 8px 0;
        }
        
        .info-value {
            display: table-cell;
            width: 60%;
            color: #555555;
            padding: 8px 0;
        }
        
/* Button */
.button-container {
    text-align: center;
    margin: 30px 0;
}

.button {
    display: inline-block;
    padding: 14px 32px;
    background: #EC3338;
    color: #ffffff !important;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    font-size: 16px;
    box-shadow: 0 4px 6px rgba(0, 159, 108, 0.3);
    transition: all 0.3s ease;
}

.button:hover {
    box-shadow: 0 6px 12px rgba(0, 159, 108, 0.4);
    transform: translateY(-2px);
}
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-paid {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        /* Alert Box */
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            border-left: 4px solid #0c5460;
            color: #0c5460;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            border-left: 4px solid #856404;
            color: #856404;
        }
        
        .alert-success {
            background-color: #d4edda;
            border-left: 4px solid #155724;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-left: 4px solid #721c24;
            color: #721c24;
        }
        
        /* Footer */
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px 20px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        
        .email-footer p {
            margin: 5px 0;
            font-size: 14px;
            color: #6c757d;
        }
        
        .email-footer a {
            color: #667eea;
            text-decoration: none;
        }
        
        /* Divider */
        .divider {
            height: 1px;
            background-color: #dee2e6;
            margin: 30px 0;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }
            
            .email-content {
                padding: 30px 20px !important;
            }
            
            .email-header {
                padding: 20px 15px !important;
            }
            
            .email-header h1 {
                font-size: 24px !important;
            }
            
            .info-label,
            .info-value {
                display: block !important;
                width: 100% !important;
            }
            
            .info-label {
                padding-bottom: 4px !important;
            }
            
            .info-value {
                padding-top: 0 !important;
                padding-bottom: 12px !important;
            }
            .login-logo {
                width: 60px;
                height: 60px;
                font-size: 2rem;
            }
        }
        
    </style>
</head>
<body>
    <!-- Preheader Text -->
    <?php if ($preheader): ?>
    <div style="display: none; max-height: 0px; overflow: hidden;">
        <?php echo htmlspecialchars($preheader); ?>
    </div>
    <?php endif; ?>
    
    <!-- Email Wrapper -->
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <!-- Email Container -->
                <table class="email-container" cellpadding="0" cellspacing="0" border="0" width="600" style="background-color: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td class="email-header">
                            <span class="logo login-logo"><img src="<?php echo BASE_URL; ?>/assets/images/icons/kadick-logo-black.png" alt="Kadick Logo" class="logo-img logo-light"></span>
                            <h1><?php echo htmlspecialchars($title); ?></h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td class="email-content">
                            <?php echo $content; ?>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td class="email-footer">
                            <p><strong><?php echo htmlspecialchars(APP_NAME); ?></strong></p>
                            <p>Requisition Management System</p>
                            <p style="margin-top: 15px;">
                                <a href="<?php echo htmlspecialchars(APP_URL); ?>">Visit Dashboard</a> | 
                                <a href="<?php echo htmlspecialchars(APP_URL); ?>/help">Help Center</a>
                            </p>
                            <p style="margin-top: 15px; font-size: 12px; color: #999999;">
                                This is an automated message. Please do not reply to this email.<br>
                                © <?php echo date('Y'); ?> <?php echo htmlspecialchars(APP_NAME); ?>. All rights reserved.
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>