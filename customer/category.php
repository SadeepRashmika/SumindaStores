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
    $redirect_url = 'category.php';
    if (isset($_GET['id'])) {
        $redirect_url .= '?id=' . $_GET['id'];
    }
    header('Location: ' . $redirect_url);
    exit();
}

$lang = $_SESSION['language'];

// Language translations
$translations = [
    'en' => [
        'page_title' => 'Category',
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
        'category_products' => 'Products in',
        'products_found' => 'products found',
        'no_products' => 'No products found in this category',
        'no_products_desc' => 'Check back later or browse other categories',
        'in_stock' => 'In Stock',
        'low_stock' => 'Low Stock',
        'out_of_stock' => 'Out of Stock',
        'language' => 'Language',
        'back_to_categories' => 'Back to Categories',
        'category_not_found' => 'Category Not Found',
        'invalid_category' => 'The category you are looking for does not exist'
    ],
    'si' => [
        'page_title' => 'වර්ගය',
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
        'category_products' => 'භාණ්ඩ',
        'products_found' => 'භාණ්ඩ හමු විය',
        'no_products' => 'මෙම වර්ගයේ භාණ්ඩ නොමැත',
        'no_products_desc' => 'පසුව නැවත පරීක්ෂා කරන්න හෝ වෙනත් වර්ග බලන්න',
        'in_stock' => 'තොගයේ ඇත',
        'low_stock' => 'අඩු තොගයක්',
        'out_of_stock' => 'තොගයෙන් අවසන්',
        'language' => 'භාෂාව',
        'back_to_categories' => 'වර්ග වෙත ආපසු',
        'category_not_found' => 'වර්ගය හමු නොවීය',
        'invalid_category' => 'ඔබ සොයන වර්ගය නොපවතී'
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

// Get category ID from URL
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$category = null;
$items = [];

if ($category_id > 0) {
    // Get category details
    $category_query = "SELECT * FROM categories WHERE category_id = ?";
    $stmt = $conn->prepare($category_query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();
    
    if ($category) {
        // Get items in this category
        $items_query = "SELECT i.*, 
                        c.category_name_si, 
                        c.category_name_en
                        FROM items i 
                        LEFT JOIN categories c ON i.category_id = c.category_id 
                        WHERE i.category_id = ? AND i.status = 'active'
                        ORDER BY i.created_at DESC";
        
        $stmt = $conn->prepare($items_query);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $items_result = $stmt->get_result();
        $items = $items_result->fetch_all(MYSQLI_ASSOC);
    }
}

$category_display_name = '';
if ($category) {
    $category_display_name = $lang == 'si' ? $category['category_name_si'] : $category['category_name_en'];
    $page_title = $category_display_name;
} else {
    $page_title = $t['category_not_found'];
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $t['site_name']; ?> - <?php echo $category_display_name; ?>">
    <title><?php echo $page_title; ?> - <?php echo $t['site_name']; ?></title>
    
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
            flex-wrap: wrap;
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
        
        /* Category Header */
        .category-header {
            background: white;
            padding: 40px 20px;
            margin: 30px 0;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-align: center;
        }
        
        .category-header h1 {
            font-size: 36px;
            color: #2d3748;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .category-header h1 i {
            color: #667eea;
        }
        
        .category-info {
            color: #718096;
            font-size: 16px;
            margin-top: 10px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            margin-top: 15px;
            transition: all 0.3s;
        }
        
        .back-link:hover {
            color: #764ba2;
            transform: translateX(-3px);
        }
        
        /* Items Grid */
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
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 12px;
            margin: 40px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .empty-state i {
            font-size: 80px;
            color: #cbd5e0;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #2d3748;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #718096;
            font-size: 16px;
            margin-bottom: 20px;
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
            
            .category-header h1 {
                font-size: 28px;
                flex-direction: column;
            }
            
            .navbar .container {
                gap: 0;
            }
            
            .nav-link {
                padding: 14px 16px;
                font-size: 14px;
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
                    <a href="?lang=en&id=<?php echo $category_id; ?>" class="lang-btn <?php echo $lang == 'en' ? 'active' : ''; ?>">
                        <i class="fas fa-flag-usa"></i> EN
                    </a>
                    <a href="?lang=si&id=<?php echo $category_id; ?>" class="lang-btn <?php echo $lang == 'si' ? 'active' : ''; ?>">
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

    <!-- Main Content -->
    <div class="container">
        <?php if ($category): ?>
            <!-- Category Header -->
            <div class="category-header">
                <h1>
                    <i class="fas fa-tag"></i>
                    <?php echo htmlspecialchars($category_display_name); ?>
                </h1>
                <p class="category-info">
                    <?php echo count($items); ?> <?php echo $t['products_found']; ?>
                </p>
                <a href="categories.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> <?php echo $t['back_to_categories']; ?>
                </a>
            </div>

            <!-- Products Grid -->
            <?php if (count($items) > 0): ?>
                <div class="items-grid">
                    <?php foreach ($items as $item): 
                        $item_display_name = $lang == 'si' ? $item['item_name_si'] : $item['item_name_en'];
                        
                        $image_path = '';
                        if (!empty($item['image_path'])) {
                            if (strpos($item['image_path'], 'uploads/items/') === 0) {
                                $image_path = BASE_URL . '/' . $item['image_path'];
                            } else {
                                $image_path = BASE_URL . '/uploads/items/' . $item['image_path'];
                            }
                        }
                    ?>
                        <div class="item-card" onclick="window.location.href='item_details.php?id=<?php echo $item['item_id']; ?>'">
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
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3><?php echo $t['no_products']; ?></h3>
                    <p><?php echo $t['no_products_desc']; ?></p>
                    <a href="categories.php" class="header-btn btn-primary">
                        <i class="fas fa-arrow-left"></i> <?php echo $t['back_to_categories']; ?>
                    </a>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Category Not Found -->
            <div class="empty-state">
                <i class="fas fa-exclamation-triangle"></i>
                <h3><?php echo $t['category_not_found']; ?></h3>
                <p><?php echo $t['invalid_category']; ?></p>
                <a href="categories.php" class="header-btn btn-primary">
                    <i class="fas fa-arrow-left"></i> <?php echo $t['back_to_categories']; ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
<?php
$conn->close();
?>