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
    header('Location: categories.php');
    exit();
}

$lang = $_SESSION['language'];

// Language translations
$translations = [
    'en' => [
        'page_title' => 'All Categories',
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
        'all_categories' => 'All Categories',
        'browse_categories' => 'Browse our product categories',
        'view_products' => 'View Products',
        'items' => 'items',
        'no_categories' => 'No categories available',
        'no_categories_desc' => 'Check back later for new categories',
        'language' => 'Language'
    ],
    'si' => [
        'page_title' => 'සියලුම වර්ග',
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
        'all_categories' => 'සියලුම වර්ග',
        'browse_categories' => 'අපගේ නිෂ්පාදන වර්ග බලන්න',
        'view_products' => 'භාණ්ඩ බලන්න',
        'items' => 'භාණ්ඩ',
        'no_categories' => 'වර්ග නොමැත',
        'no_categories_desc' => 'නව වර්ග සඳහා පසුව නැවත පරීක්ෂා කරන්න',
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

// Get categories with item counts
$category_name_field = $lang == 'si' ? 'category_name_si' : 'category_name_en';
$categories_query = "SELECT c.*, COUNT(i.item_id) as item_count 
                     FROM categories c 
                     LEFT JOIN items i ON c.category_id = i.category_id AND i.status = 'active'
                     GROUP BY c.category_id 
                     ORDER BY $category_name_field";
$categories_result = $conn->query($categories_query);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $t['all_categories']; ?> - <?php echo $t['site_name']; ?>">
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
        
        /* Top Bar */
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
        
        /* Header */
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
        
        /* Navigation */
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
        
        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
            margin-bottom: 50px;
        }
        
        .page-header h1 {
            font-size: 42px;
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .page-header p {
            font-size: 18px;
            opacity: 0.95;
        }
        
        /* Breadcrumb */
        .breadcrumb {
            background: white;
            padding: 15px 0;
            margin-bottom: 30px;
        }
        
        .breadcrumb ul {
            list-style: none;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .breadcrumb a:hover {
            color: #764ba2;
        }
        
        .breadcrumb span {
            color: #718096;
        }
        
        /* Categories Grid */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin: 40px 0;
            padding-bottom: 60px;
        }
        
        .category-card {
            background: white;
            border-radius: 16px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: #2d3748;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }
        
        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: scaleX(0);
            transition: transform 0.3s;
        }
        
        .category-card:hover::before {
            transform: scaleX(1);
        }
        
        .category-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.2);
            border-color: #667eea;
        }
        
        .category-icon {
            font-size: 60px;
            color: #667eea;
            margin-bottom: 20px;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .category-card:hover .category-icon {
            transform: scale(1.1);
            color: #764ba2;
        }
        
        .category-name {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #2d3748;
        }
        
        .category-count {
            color: #718096;
            font-size: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .view-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .view-btn:hover {
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            transform: scale(1.05);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 16px;
            margin: 40px 0;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        
        .empty-state i {
            font-size: 80px;
            color: #cbd5e0;
            margin-bottom: 25px;
        }
        
        .empty-state h3 {
            color: #2d3748;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #718096;
            font-size: 16px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 32px;
            }
            
            .page-header p {
                font-size: 16px;
            }
            
            .categories-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }
            
            .category-icon {
                font-size: 50px;
            }
            
            .category-name {
                font-size: 20px;
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
            <a href="<?php echo BASE_URL; ?>/index.php" class="nav-link">
                <i class="fas fa-home"></i> <?php echo $t['home']; ?>
            </a>
            <a href="<?php echo BASE_URL; ?>/customer/categories.php" class="nav-link active">
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

    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-th-large"></i> <?php echo $t['all_categories']; ?></h1>
        <p><?php echo $t['browse_categories']; ?></p>
    </div>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/index.php"><i class="fas fa-home"></i> <?php echo $t['home']; ?></a></li>
                <li><i class="fas fa-chevron-right"></i></li>
                <li><span><?php echo $t['categories']; ?></span></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="categories-grid">
            <?php 
            // Category icons mapping
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
                <div class="category-card" onclick="window.location.href='category.php?id=<?php echo $category['category_id']; ?>'">
                    <div class="category-icon">
                        <i class="fas <?php echo $icon; ?>"></i>
                    </div>
                    <h3 class="category-name"><?php echo htmlspecialchars($category_display_name); ?></h3>
                    <div class="category-count">
                        <i class="fas fa-box"></i>
                        <span><?php echo $category['item_count']; ?> <?php echo $t['items']; ?></span>
                    </div>
                    <a href="category.php?id=<?php echo $category['category_id']; ?>" class="view-btn" onclick="event.stopPropagation();">
                        <?php echo $t['view_products']; ?> <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="fas fa-th-large"></i>
                    <h3><?php echo $t['no_categories']; ?></h3>
                    <p><?php echo $t['no_categories_desc']; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
<?php
$conn->close();
?>