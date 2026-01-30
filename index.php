<?php
// Start session
session_start();

// Language handling - Default to English
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'en';
}

// Handle language switch
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'si'])) {
    $_SESSION['language'] = $_GET['lang'];
    header('Location: index.php');
    exit();
}

$lang = $_SESSION['language'];

// Language translations
$translations = [
    'en' => [
        'page_title' => 'Home',
        'phone' => 'Phone',
        'email' => 'Email',
        'address' => 'Akuressa, Matara',
        'site_name' => 'Suminda Stores',
        'site_tagline' => 'Your Trusted Service Center',
        'admin_dashboard' => 'Admin Dashboard',
        'my_account' => 'My Account',
        'logout' => 'Logout',
        'login' => 'Login',
        'register' => 'Register',
        'home' => 'Home',
        'categories' => 'Categories',
        'products' => 'Products',
        'about' => 'About Us',
        'contact' => 'Contact',
        'welcome' => 'Welcome to Suminda Stores',
        'tagline' => 'All your daily needs in one place',
        'search_placeholder' => 'Search for products... (Sinhala or English)',
        'search_btn' => 'Search',
        'categories_title' => 'Categories',
        'featured_items' => 'Featured Products',
        'in_stock' => 'In Stock',
        'low_stock' => 'Low Stock',
        'out_of_stock' => 'Out of Stock',
        'no_categories' => 'No categories added yet',
        'no_items' => 'No products added yet',
        'language' => 'Language'
    ],
    'si' => [
        'page_title' => 'මුල් පිටුව',
        'phone' => 'දුරකථනය',
        'email' => 'විද්‍යුත් ලිපිනය',
        'address' => 'අකුරැස්ස, මාතර',
        'site_name' => 'සුමින්ද ස්ටෝර්ස්',
        'site_tagline' => 'ඔබේ විශ්වසනීය සේවා ස්ථානය',
        'admin_dashboard' => 'පරිපාලක පුවරුව',
        'my_account' => 'මගේ ගිණුම',
        'logout' => 'ඉවත් වන්න',
        'login' => 'පිවිසෙන්න',
        'register' => 'ලියාපදිංචි වන්න',
        'home' => 'මුල් පිටුව',
        'categories' => 'වර්ග',
        'products' => 'භාණ්ඩ',
        'about' => 'අප ගැන',
        'contact' => 'අමතන්න',
        'welcome' => 'සුමින්ද ස්ටෝර්ස් වෙත සාදරයෙන් පිළිගනිමු',
        'tagline' => 'ඔබට අවශ්‍ය සියලු භාණ්ඩ එකම තැනකින්',
        'search_placeholder' => 'භාණ්ඩ සොයන්න... (සිංහල හෝ ඉංග්‍රීසි)',
        'search_btn' => 'සොයන්න',
        'categories_title' => 'වර්ග',
        'featured_items' => 'විශේෂාංග භාණ්ඩ',
        'in_stock' => 'තොගයේ ඇත',
        'low_stock' => 'අඩු තොගයක්',
        'out_of_stock' => 'තොගයෙන් අවසන්',
        'no_categories' => 'වර්ග තවමත් එකතු කර නොමැත',
        'no_items' => 'භාණ්ඩ තවමත් එකතු කර නොමැත',
        'language' => 'භාෂාව'
    ]
];

$t = $translations[$lang];

// Database configuration
$host = 'localhost';
$dbname = 'suminda_stores';
$username = 'root';
$password = '';

// Base URL
define('BASE_URL', 'http://localhost/SumindaStores');

// Connect to database
try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

$page_title = $t['page_title'];

// Get categories
$category_name_field = $lang == 'si' ? 'category_name_si' : 'category_name_en';
$categories_query = "SELECT * FROM categories ORDER BY $category_name_field";
$categories_result = $conn->query($categories_query);

// Get featured items
$item_name_field = $lang == 'si' ? 'i.item_name_si' : 'i.item_name_en';
$items_query = "SELECT i.*, 
                c.category_name_si, 
                c.category_name_en
                FROM items i 
                LEFT JOIN categories c ON i.category_id = c.category_id 
                WHERE i.status = 'active' 
                ORDER BY i.created_at DESC 
                LIMIT 12";
$items_result = $conn->query($items_query);

// Get active advertisements
$ads_query = "SELECT * FROM advertisements WHERE status = 'active' AND position = 'home_banner' AND (end_date IS NULL OR end_date >= CURDATE()) LIMIT 1";
$ads_result = $conn->query($ads_query);
$ad = $ads_result ? $ads_result->fetch_assoc() : null;
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $t['site_name']; ?> - <?php echo $t['site_tagline']; ?>">
    <title><?php echo $t['page_title']; ?> - <?php echo $t['site_name']; ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Noto Sans Sinhala -->
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
            background: #f5f7fa;
            min-height: 100vh;
        }
        
        /* Language Switcher */
        .language-switcher {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .language-switcher span {
            font-size: 14px;
            color: #555;
            font-weight: 600;
        }
        
        .lang-btn {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .lang-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }
        
        .lang-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }
        
        /* Top Bar - White with subtle shadow */
        .top-bar {
            background: white;
            padding: 10px 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .top-bar .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .contact-info {
            display: flex;
            gap: 20px;
            font-size: 14px;
            color: #555;
            flex-wrap: wrap;
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
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            color: #764ba2;
            transform: translateY(-2px);
        }
        
        /* Header - White background */
        header {
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
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
            color: #667eea;
            margin-right: 15px;
        }
        
        .logo-text h1 {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
        }
        
        .logo-text p {
            font-size: 12px;
            color: #718096;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .header-btn {
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-outline {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-outline:hover {
            background: #667eea;
            color: white;
        }
        
        /* Navigation - Gradient background */
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 0;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }
        
        .navbar .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: center;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .nav-link {
            text-decoration: none;
            color: white;
            font-weight: 600;
            padding: 16px 24px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-bottom: 3px solid transparent;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            border-bottom-color: white;
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Hero Section - Gradient */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
            border-radius: 0;
            margin: 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .hero p {
            font-size: 20px;
            margin-bottom: 30px;
            opacity: 0.95;
        }
        
        /* Search Box */
        .search-box {
            max-width: 600px;
            margin: 30px auto;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 16px 140px 16px 24px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            font-family: 'Noto Sans Sinhala', sans-serif;
        }
        
        .search-box button {
            position: absolute;
            right: 6px;
            top: 6px;
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            border: none;
            padding: 12px 28px;
            border-radius: 50px;
            color: white;
            cursor: pointer;
            font-weight: 600;
            font-family: 'Noto Sans Sinhala', sans-serif;
            transition: all 0.3s;
        }
        
        .search-box button:hover {
            box-shadow: 0 4px 12px rgba(72, 187, 120, 0.4);
            transform: translateX(-2px);
        }
        
        /* Content Area - Light background */
        .content-wrapper {
            background: #f5f7fa;
            padding: 50px 0;
        }
        
        /* Categories - White cards */
        .categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin: 40px 0;
        }
        
        .category-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: #2d3748;
            border: 2px solid transparent;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.15);
            border-color: #667eea;
        }
        
        .category-card i {
            font-size: 40px;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .category-card h3 {
            font-size: 16px;
            font-weight: 600;
        }
        
        /* Items Grid - White cards */
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin: 40px 0;
        }
        
        .item-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            border-color: #667eea;
        }
        
        .item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: block;
        }
        
        .item-image-placeholder {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 60px;
        }
        
        .item-info {
            padding: 20px;
        }
        
        .item-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #2d3748;
        }
        
        .item-category {
            color: #667eea;
            font-size: 14px;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .item-price {
            font-size: 24px;
            font-weight: 700;
            color: #48bb78;
            margin-bottom: 10px;
        }
        
        .stock-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .in-stock {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .low-stock {
            background: #feebc8;
            color: #7c2d12;
        }
        
        .out-of-stock {
            background: #fed7d7;
            color: #742a2a;
        }
        
        .section-title {
            font-size: 32px;
            font-weight: 700;
            color: #2d3748;
            text-align: center;
            margin: 50px 0 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .section-title i {
            color: #667eea;
        }
        
        .ad-banner {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin: 30px auto;
            max-width: 1160px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-align: center;
        }
        
        .ad-banner img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }
        
        .ad-banner h2 {
            color: #2d3748;
            margin-top: 20px;
        }
        
        .ad-banner p {
            color: #718096;
            margin-top: 10px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 12px;
            margin: 40px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .empty-state i {
            font-size: 60px;
            color: #cbd5e0;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #718096;
            font-size: 18px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .top-bar .container {
                flex-direction: column;
                gap: 10px;
            }
            
            .contact-info {
                flex-direction: column;
                gap: 5px;
                text-align: center;
            }
            
            .hero h1 {
                font-size: 32px;
            }
            
            .hero p {
                font-size: 16px;
            }
            
            .navbar .container {
                gap: 0;
            }
            
            .nav-link {
                padding: 14px 16px;
                font-size: 14px;
            }
            
            .section-title {
                font-size: 24px;
            }
            
            .items-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .language-switcher {
                justify-content: center;
            }
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
                <span><i class="fas fa-map-marker-alt"></i> <?php echo $t['address']; ?></span>
            </div>
            <div class="social-links">
                <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header>
        <div class="header-container">
            <a href="<?php echo BASE_URL; ?>/index.php" class="logo">
                <i class="fas fa-store"></i>
                <div class="logo-text">
                    <h1><?php echo $t['site_name']; ?></h1>
                    <p><?php echo $t['site_tagline']; ?></p>
                </div>
            </a>
            
            <div class="header-actions">
                <!-- Language Switcher -->
                <div class="language-switcher">
                    <span><i class="fas fa-globe"></i> <?php echo $t['language']; ?>:</span>
                    <a href="?lang=en" class="lang-btn <?php echo $lang == 'en' ? 'active' : ''; ?>">
                        <i class="fas fa-flag-usa"></i> EN
                    </a>
                    <a href="?lang=si" class="lang-btn <?php echo $lang == 'si' ? 'active' : ''; ?>">
                        <i class="fas fa-flag"></i> සිං
                    </a>
                </div>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] == 'admin'): ?>
                        <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="header-btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i> <?php echo $t['admin_dashboard']; ?>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/customer/dashboard.php" class="header-btn btn-primary">
                            <i class="fas fa-user"></i> <?php echo $t['my_account']; ?>
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>/logout.php" class="header-btn btn-outline">
                        <i class="fas fa-sign-out-alt"></i> <?php echo $t['logout']; ?>
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login.php" class="header-btn btn-outline">
                        <i class="fas fa-sign-in-alt"></i> <?php echo $t['login']; ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/register.php" class="header-btn btn-primary">
                        <i class="fas fa-user-plus"></i> <?php echo $t['register']; ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo BASE_URL; ?>/index.php" class="nav-link active">
                <i class="fas fa-home"></i> <?php echo $t['home']; ?>
            </a>
            <a href="<?php echo BASE_URL; ?>/customer/categories.php" class="nav-link">
                <i class="fas fa-th-large"></i> <?php echo $t['categories']; ?>
            </a>
            <a href="<?php echo BASE_URL; ?>/customer/products.php" class="nav-link">
                <i class="fas fa-shopping-bag"></i> <?php echo $t['products']; ?>
            </a>
            <a href="<?php echo BASE_URL; ?>/customer/about.php" class="nav-link">
                <i class="fas fa-info-circle"></i> <?php echo $t['about']; ?>
            </a>
            <a href="<?php echo BASE_URL; ?>/customer/contact.php" class="nav-link">
                <i class="fas fa-phone"></i> <?php echo $t['contact']; ?>
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero">
        <h1><?php echo $t['welcome']; ?></h1>
        <p><?php echo $t['tagline']; ?></p>
        
        <div class="search-box">
            <form action="customer/search.php" method="GET">
                <input type="text" name="q" placeholder="<?php echo $t['search_placeholder']; ?>" required>
                <button type="submit"><i class="fas fa-search"></i> <?php echo $t['search_btn']; ?></button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="container">
            <?php if ($ad): ?>
            <div class="ad-banner">
                <?php if ($ad['image_path']): ?>
                    <img src="<?php echo BASE_URL . '/uploads/ads/' . $ad['image_path']; ?>" alt="Advertisement">
                <?php endif; ?>
                <h2><?php echo $lang == 'si' ? $ad['title_si'] : $ad['title_en']; ?></h2>
                <p><?php echo $lang == 'si' ? $ad['content_si'] : $ad['content_en']; ?></p>
            </div>
            <?php endif; ?>

            <!-- Categories -->
            <h2 class="section-title"><i class="fas fa-th-large"></i> <?php echo $t['categories_title']; ?></h2>
            <div class="categories">
                <?php 
                $category_icons = [
    'සහල්' => 'fa-seedling',
    'Rice' => 'fa-seedling',
    'පොල්තෙල්' => 'fa-oil-can',
    'Coconut Oil' => 'fa-oil-can',
    'ඉලෙක්ට්‍රික්බඩු' => 'fa-plug',
    'Electrical Items' => 'fa-plug',
    'හාඩ්වයාර්' => 'fa-tools',
    'Hardware' => 'fa-tools',
    'සබන්' => 'fa-hands-wash',
    'Soap' => 'fa-hands-wash',
    'කුළුබඩු' => 'fa-pepper-hot',
    'Spices' => 'fa-pepper-hot',
    'බිස්කට්' => 'fa-cookie-bite',
    'Biscuits' => 'fa-cookie-bite'
];
                
                
                if ($categories_result && $categories_result->num_rows > 0):
                    while ($category = $categories_result->fetch_assoc()): 
                        $category_display_name = $lang == 'si' ? $category['category_name_si'] : $category['category_name_en'];
                        $icon = $category_icons[$category_display_name] ?? 'fa-box';
                ?>
                    <a href="customer/category.php?id=<?php echo $category['category_id']; ?>" class="category-card">
                        <i class="fas <?php echo $icon; ?>"></i>
                        <h3><?php echo htmlspecialchars($category_display_name); ?></h3>
                    </a>
                <?php 
                    endwhile;
                else:
                ?>
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <i class="fas fa-th-large"></i>
                        <h3><?php echo $t['no_categories']; ?></h3>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Featured Items -->
            <h2 class="section-title"><i class="fas fa-star"></i> <?php echo $t['featured_items']; ?></h2>
            <div class="items-grid">
                <?php 
                if ($items_result && $items_result->num_rows > 0):
                    while ($item = $items_result->fetch_assoc()): 
                        // Get item name based on language
                        $item_display_name = $lang == 'si' ? $item['item_name_si'] : $item['item_name_en'];
                        $category_display_name = $lang == 'si' ? $item['category_name_si'] : $item['category_name_en'];
                        
                        // Construct proper image path
                        $image_path = '';
                        if (!empty($item['image_path'])) {
                            if (strpos($item['image_path'], 'uploads/items/') === 0) {
                                $image_path = BASE_URL . '/' . $item['image_path'];
                            } else {
                                $image_path = BASE_URL . '/uploads/items/' . $item['image_path'];
                            }
                        }
                ?>
                    <div class="item-card" onclick="window.location.href='customer/item_details.php?id=<?php echo $item['item_id']; ?>'">
                        <?php if (!empty($image_path)): ?>
                            <img src="<?php echo $image_path; ?>" 
                                 alt="<?php echo htmlspecialchars($item_display_name); ?>" 
                                 class="item-image"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="item-image-placeholder" style="display:none;">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php else: ?>
                            <div class="item-image-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="item-info">
                            <div class="item-category">
                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($category_display_name); ?>
                            </div>
                            <h3 class="item-name"><?php echo htmlspecialchars($item_display_name); ?></h3>
                            <div class="item-price">Rs. <?php echo number_format($item['price'], 2); ?></div>
                            
                            <?php if ($item['stock_quantity'] > $item['low_stock_threshold']): ?>
                                <span class="stock-badge in-stock"><i class="fas fa-check-circle"></i> <?php echo $t['in_stock']; ?></span>
                            <?php elseif ($item['stock_quantity'] > 0): ?>
                                <span class="stock-badge low-stock"><i class="fas fa-exclamation-circle"></i> <?php echo $t['low_stock']; ?></span>
                            <?php else: ?>
                                <span class="stock-badge out-of-stock"><i class="fas fa-times-circle"></i> <?php echo $t['out_of_stock']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php 
                    endwhile;
                else:
                ?>
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <i class="fas fa-box-open"></i>
                        <h3><?php echo $t['no_items']; ?></h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>
<?php
$conn->close();
?>
<?php include 'includes/footer.php'; ?>