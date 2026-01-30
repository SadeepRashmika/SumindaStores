<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="සුමින්ද ස්ටෝර්ස් - ඔබේ දෛනික අවශ්‍යතා සඳහා විශ්වසනීය සාප්පු ස්ථානය">
    <meta name="keywords" content="සිංහල, කඩ, සුපිරි වෙළඳසැල, සිල්ලර භාණ්ඩ, එළවළු, පලතුරු">
    <title><?php echo isset($page_title) ? $page_title . ' - සුමින්ද ස්ටෝර්ස්' : 'සුමින්ද ස්ටෝර්ස්'; ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Noto Sans Sinhala for better Sinhala support -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Noto Sans Sinhala', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding-bottom: 50px;
        }
        
        .top-bar {
            background: rgba(255, 255, 255, 0.95);
            padding: 10px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .top-bar .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .contact-info {
            display: flex;
            gap: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .contact-info i {
            color: #667eea;
            margin-right: 5px;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
        }
        
        .social-links a {
            color: #667eea;
            font-size: 18px;
            transition: color 0.3s;
        }
        
        .social-links a:hover {
            color: #764ba2;
        }
        
        header {
            background: white;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #333;
        }
        
        .logo i {
            font-size: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-right: 15px;
        }
        
        .logo-text h1 {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .logo-text p {
            font-size: 12px;
            color: #666;
        }
        
        .header-actions {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .header-btn {
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-outline {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-outline:hover {
            background: #667eea;
            color: white;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Mobile Menu Toggle */
        .menu-toggle {
            display: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 20px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .top-bar .container {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .contact-info {
                flex-direction: column;
                gap: 5px;
            }
            
            .header-container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .menu-toggle {
                display: block;
                align-self: flex-end;
            }
            
            .header-actions {
                display: none;
                width: 100%;
                flex-direction: column;
                gap: 10px;
            }
            
            .header-actions.active {
                display: flex;
            }
            
            .header-btn {
                width: 100%;
                justify-content: center;
            }
            
            .logo-text h1 {
                font-size: 24px;
            }
        }
        
        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin: 20px auto;
            max-width: 1160px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="contact-info">
                <span><i class="fas fa-phone"></i> +94 777640334</span>
                <span><i class="fas fa-envelope"></i> sumindapradeep1111@gmail.com</span>
                <span><i class="fas fa-map-marker-alt"></i> අකුරැස්ස, මාතර</span>
            </div>
            <div class="social-links">
                <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header>
        <div class="header-container">
            <a href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/index.php" class="logo">
                <i class="fas fa-store"></i>
                <div class="logo-text">
                    <h1>සුමින්ද ස්ටෝර්ස්</h1>
                    <p>ඔබේ විශ්වාසනීය සාප්පු ස්ථානය</p>
                </div>
            </a>
            
            <button class="menu-toggle" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="header-actions" id="headerActions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php 
                    $user_role = isset($_SESSION['role']) ? $_SESSION['role'] : (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'customer');
                    ?>
                    <?php if ($user_role == 'admin'): ?>
                        <a href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/admin/index.php" class="header-btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i> පරිපාලක පුවරුව
                        </a>
                    <?php elseif ($user_role == 'seller'): ?>
                        <a href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/seller/index.php" class="header-btn btn-primary">
                            <i class="fas fa-store"></i> විකුණුම්කරු පුවරුව
                        </a>
                    <?php else: ?>
                        <a href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/customer/index.php" class="header-btn btn-primary">
                            <i class="fas fa-user"></i> මගේ ගිණුම
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/logout.php" class="header-btn btn-outline">
                        <i class="fas fa-sign-out-alt"></i> ඉවත් වන්න
                    </a>
                <?php else: ?>
                    <a href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/login.php" class="header-btn btn-outline">
                        <i class="fas fa-sign-in-alt"></i> පිවිසෙන්න
                    </a>
                    <a href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/register.php" class="header-btn btn-primary">
                        <i class="fas fa-user-plus"></i> ලියාපදිංචි වන්න
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <script>
        function toggleMenu() {
            const menu = document.getElementById('headerActions');
            menu.classList.toggle('active');
        }
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('headerActions');
            const toggle = document.querySelector('.menu-toggle');
            
            if (menu && toggle && !menu.contains(event.target) && !toggle.contains(event.target)) {
                menu.classList.remove('active');
            }
        });
    </script>

    <?php
    // Display session messages
    if (isset($_SESSION['success_message'])) {
        echo '<div class="container"><div class="alert alert-success"><i class="fas fa-check-circle"></i> ' . htmlspecialchars($_SESSION['success_message']) . '</div></div>';
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])) {
        echo '<div class="container"><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($_SESSION['error_message']) . '</div></div>';
        unset($_SESSION['error_message']);
    }
    if (isset($_SESSION['warning_message'])) {
        echo '<div class="container"><div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> ' . htmlspecialchars($_SESSION['warning_message']) . '</div></div>';
        unset($_SESSION['warning_message']);
    }
    if (isset($_SESSION['info_message'])) {
        echo '<div class="container"><div class="alert alert-info"><i class="fas fa-info-circle"></i> ' . htmlspecialchars($_SESSION['info_message']) . '</div></div>';
        unset($_SESSION['info_message']);
    }
    ?>