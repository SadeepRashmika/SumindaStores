
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
    header('Location: products.php');
    exit();
}

$lang = $_SESSION['language'];

// Language translations
$translations = [
    'en' => [
        'page_title' => 'Products',
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
        'all_products' => 'All Products',
        'filter_by_category' => 'Filter by Category',
        'all_categories' => 'All Categories',
        'sort_by' => 'Sort by',
        'newest_first' => 'Newest First',
        'price_low_high' => 'Price: Low to High',
        'price_high_low' => 'Price: High to Low',
        'name_a_z' => 'Name: A to Z',
        'name_z_a' => 'Name: Z to A',
        'search_products' => 'Search Products',
        'showing_results' => 'Showing {count} products',
        'no_products' => 'No products found',
        'try_different_search' => 'Try adjusting your filters or search term',
        'in_stock' => 'In Stock',
        'low_stock' => 'Low Stock',
        'out_of_stock' => 'Out of Stock',
        'view_details' => 'View Details',
        'language' => 'Language'
    ],
    'si' => [
        'page_title' => 'භාණ්ඩ',
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
        'all_products' => 'සියලුම භාණ්ඩ',
        'filter_by_category' => 'වර්ගය අනුව පෙරහන්',
        'all_categories' => 'සියලුම වර්ග',
        'sort_by' => 'පිළිවෙළ කරන්න',
        'newest_first' => 'නවතම පළමුව',
        'price_low_high' => 'මිල: අඩුයි ඉහළ',
        'price_high_low' => 'මිල: ඉහළ අඩු',
        'name_a_z' => 'නම: අ සිට ය',
        'name_z_a' => 'නම: ය සිට අ',
        'search_products' => 'භාණ්ඩ සොයන්න',
        'showing_results' => 'භාණ්ඩ {count} ක් පෙන්වයි',
        'no_products' => 'භාණ්ඩ සොයාගත නොහැකි විය',
        'try_different_search' => 'ඔබේ පෙරහන් හෝ සෙවුම් පදය වෙනස් කිරීමට උත්සාහ කරන්න',
        'in_stock' => 'තොගයේ ඇත',
        'low_stock' => 'අඩු තොගයක්',
        'out_of_stock' => 'තොගයෙන් අවසන්',
        'view_details' => 'විස්තර බලන්න',
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

// Get filter parameters
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get categories for filter dropdown
$category_name_field = $lang == 'si' ? 'category_name_si' : 'category_name_en';
$categories_query = "SELECT * FROM categories ORDER BY $category_name_field";
$categories_result = $conn->query($categories_query);

// Build products query
$item_name_field = $lang == 'si' ? 'i.item_name_si' : 'i.item_name_en';
$items_query = "SELECT i.*, 
                c.category_name_si, 
                c.category_name_en
                FROM items i 
                LEFT JOIN categories c ON i.category_id = c.category_id 
                WHERE i.status = 'active'";

// Apply category filter
if ($category_filter > 0) {
    $items_query .= " AND i.category_id = " . $category_filter;
}

// Apply search filter
if (!empty($search_query)) {
    $search_term = $conn->real_escape_string($search_query);
    $items_query .= " AND (i.item_name_en LIKE '%$search_term%' 
                      OR i.item_name_si LIKE '%$search_term%'
                      OR i.description_en LIKE '%$search_term%'
                      OR i.description_si LIKE '%$search_term%')";
}

// Apply sorting
switch ($sort_by) {
    case 'price_low':
        $items_query .= " ORDER BY i.price ASC";
        break;
    case 'price_high':
        $items_query .= " ORDER BY i.price DESC";
        break;
    case 'name_asc':
        $items_query .= " ORDER BY $item_name_field ASC";
        break;
    case 'name_desc':
        $items_query .= " ORDER BY $item_name_field DESC";
        break;
    case 'newest':
    default:
        $items_query .= " ORDER BY i.created_at DESC";
        break;
}

$items_result = $conn->query($items_query);
$total_products = $items_result ? $items_result->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $t['site_name']; ?> - <?php echo $t['all_products']; ?>">
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
        
        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        
        .page-header h1 {
            font-size: 42px;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .page-header p {
            font-size: 18px;
            opacity: 0.95;
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Content Wrapper */
        .content-wrapper {
            background: #f5f7fa;
            padding: 40px 0;
        }
        
        /* Filter Bar */
        .filter-bar {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .filter-controls {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .filter-group select,
        .filter-group input[type="text"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-family: 'Noto Sans Sinhala', sans-serif;
            font-size: 15px;
            transition: all 0.3s;
            background: white;
        }
        
        .filter-group select:focus,
        .filter-group input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .filter-btn {
            padding: 12px 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Noto Sans Sinhala', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 26px;
        }
        
        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .results-count {
            background: white;
            padding: 15px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            font-weight: 600;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .results-count i {
            color: #667eea;
        }
        
        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            border-color: #667eea;
        }
        
        .product-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: block;
        }
        
        .product-image-placeholder {
            width: 100%;
            height: 220px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 60px;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-category {
            color: #667eea;
            font-size: 13px;
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .product-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #2d3748;
            line-height: 1.4;
        }
        
        .product-price {
            font-size: 26px;
            font-weight: 700;
            color: #48bb78;
            margin-bottom: 12px;
        }
        
        .stock-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 12px;
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
        
        .view-details-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .view-details-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .empty-state i {
            font-size: 80px;
            color: #cbd5e0;
            margin-bottom: 25px;
        }
        
        .empty-state h3 {
            color: #2d3748;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .empty-state p {
            color: #718096;
            font-size: 16px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 28px;
            }
            
            .filter-controls {
                flex-direction: column;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .filter-btn {
                width: 100%;
                margin-top: 10px;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .top-bar .container {
                flex-direction: column;
                gap: 10px;
            }
            
            .contact-info {
                flex-direction: column;
                gap: 5px;
                text-align: center;
            }
            
            .language-switcher {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<!-- Filter Bar -->
<div class="filter-bar">
    <form method="GET" action="">
        <div class="filter-controls">
            <div class="filter-group">
                <label><i class="fas fa-search"></i> <?php echo $t['search_products']; ?></label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="<?php echo $t['search_products']; ?>...">
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-filter"></i> <?php echo $t['filter_by_category']; ?></label>
                <select name="category" onchange="this.form.submit()">
                    <option value="0"><?php echo $t['all_categories']; ?></option>
                    <?php 
                    if ($categories_result && $categories_result->num_rows > 0):
                        mysqli_data_seek($categories_result, 0);
                        while ($category = $categories_result->fetch_assoc()): 
                            $category_display_name = $lang == 'si' ? $category['category_name_si'] : $category['category_name_en'];
                    ?>
                        <option value="<?php echo $category['category_id']; ?>" <?php echo $category_filter == $category['category_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category_display_name); ?>
                        </option>
                    <?php 
                        endwhile;
                    endif;
                    ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-sort"></i> <?php echo $t['sort_by']; ?></label>
                <select name="sort" onchange="this.form.submit()">
                    <option value="newest" <?php echo $sort_by == 'newest' ? 'selected' : ''; ?>><?php echo $t['newest_first']; ?></option>
                    <option value="price_low" <?php echo $sort_by == 'price_low' ? 'selected' : ''; ?>><?php echo $t['price_low_high']; ?></option>
                    <option value="price_high" <?php echo $sort_by == 'price_high' ? 'selected' : ''; ?>><?php echo $t['price_high_low']; ?></option>
                    <option value="name_asc" <?php echo $sort_by == 'name_asc' ? 'selected' : ''; ?>><?php echo $t['name_a_z']; ?></option>
                    <option value="name_desc" <?php echo $sort_by == 'name_desc' ? 'selected' : ''; ?>><?php echo $t['name_z_a']; ?></option>
                </select>
            </div>
            
            <button type="submit" class="filter-btn">
                <i class="fas fa-search"></i> <?php echo $lang == 'si' ? 'සොයන්න' : 'Search'; ?>
            </button>
        </div>
    </form>
</div>

        <!-- Results Count -->
        <div class="results-count">
            <i class="fas fa-box"></i>
            <?php echo str_replace('{count}', $total_products, $t['showing_results']); ?>
        </div>

        <!-- Products Grid -->
        <?php if ($items_result && $items_result->num_rows > 0): ?>
            <div class="products-grid">
                <?php while ($item = $items_result->fetch_assoc()): 
                    $item_display_name = $lang == 'si' ? $item['item_name_si'] : $item['item_name_en'];
                    $category_display_name = $lang == 'si' ? $item['category_name_si'] : $item['category_name_en'];
                    
                    $image_path = '';
                    if (!empty($item['image_path'])) {
                        if (strpos($item['image_path'], 'uploads/items/') === 0) {
                            $image_path = BASE_URL . '/' . $item['image_path'];
                        } else {
                            $image_path = BASE_URL . '/uploads/items/' . $item['image_path'];
                        }
                    }
                ?>
                    <div class="product-card">
                        <?php if (!empty($image_path)): ?>
                            <img src="<?php echo $image_path; ?>" 
                                 alt="<?php echo htmlspecialchars($item_display_name); ?>" 
                                 class="product-image"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="product-image-placeholder" style="display:none;">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php else: ?>
                            <div class="product-image-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-info">
                            <div class="product-category">
                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($category_display_name); ?>
                            </div>
                            <h3 class="product-name"><?php echo htmlspecialchars($item_display_name); ?></h3>
                            <div class="product-price">Rs. <?php echo number_format($item['price'], 2); ?></div>
                            
                            <?php if ($item['stock_quantity'] > $item['low_stock_threshold']): ?>
                                <span class="stock-badge in-stock"><i class="fas fa-check-circle"></i> <?php echo $t['in_stock']; ?></span>
                            <?php elseif ($item['stock_quantity'] > 0): ?>
                                <span class="stock-badge low-stock"><i class="fas fa-exclamation-circle"></i> <?php echo $t['low_stock']; ?></span>
                            <?php else: ?>
                                <span class="stock-badge out-of-stock"><i class="fas fa-times-circle"></i> <?php echo $t['out_of_stock']; ?></span>
                            <?php endif; ?>
                            
                            <a href="item_details.php?id=<?php echo $item['item_id']; ?>" class="view-details-btn">
                                <i class="fas fa-eye"></i> <?php echo $t['view_details']; ?>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3><?php echo $t['no_products']; ?></h3>
                <p><?php echo $t['try_different_search']; ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>