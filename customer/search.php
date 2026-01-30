<?php
// Start session
session_start();

// Language handling
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'en';
}
$lang = $_SESSION['language'];

// Language translations
$translations = [
    'en' => [
        'page_title' => 'Search Results',
        'site_name' => 'Suminda Stores',
        'search_results' => 'Search Results',
        'showing_results' => 'Showing results for',
        'no_results' => 'No products found',
        'try_different' => 'Try using different keywords',
        'in_stock' => 'In Stock',
        'low_stock' => 'Low Stock',
        'out_of_stock' => 'Out of Stock',
        'view_details' => 'View Details',
        'search_placeholder' => 'Search for products...',
        'search_btn' => 'Search'
    ],
    'si' => [
        'page_title' => 'සෙවුම් ප්‍රතිඵල',
        'site_name' => 'සුමින්ද ස්ටෝර්ස්',
        'search_results' => 'සෙවුම් ප්‍රතිඵල',
        'showing_results' => 'සඳහා ප්‍රතිඵල පෙන්වයි',
        'no_results' => 'භාණ්ඩ හමු නොවීය',
        'try_different' => 'වෙනත් මූල පද භාවිතා කර බලන්න',
        'in_stock' => 'තොගයේ ඇත',
        'low_stock' => 'අඩු තොගයක්',
        'out_of_stock' => 'තොගයෙන් අවසන්',
        'view_details' => 'විස්තර බලන්න',
        'search_placeholder' => 'භාණ්ඩ සොයන්න...',
        'search_btn' => 'සොයන්න'
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

// Get search query
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$items_result = null;

if (!empty($search_query)) {
    // Escape search query for SQL
    $search_term = $conn->real_escape_string($search_query);
    
    // Search in both English and Sinhala names
    $items_query = "SELECT i.*, 
                    c.category_name_si, 
                    c.category_name_en
                    FROM items i 
                    LEFT JOIN categories c ON i.category_id = c.category_id 
                    WHERE i.status = 'active' 
                    AND (
                        i.item_name_en LIKE '%$search_term%' 
                        OR i.item_name_si LIKE '%$search_term%'
                        OR c.category_name_en LIKE '%$search_term%'
                        OR c.category_name_si LIKE '%$search_term%'
                        OR i.description_en LIKE '%$search_term%'
                        OR i.description_si LIKE '%$search_term%'
                    )
                    ORDER BY i.created_at DESC";
    
    $items_result = $conn->query($items_query);
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        .search-info {
            font-size: 18px;
            opacity: 0.9;
        }
        
        .search-box {
            max-width: 600px;
            margin: 20px auto;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 16px 140px 16px 24px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
        
        .item-category {
            color: #667eea;
            font-size: 14px;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .item-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #2d3748;
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
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .back-link:hover {
            color: #764ba2;
            transform: translateX(-5px);
        }
    </style>
</head>
<body>
    <div class="page-header">
        <h1><i class="fas fa-search"></i> <?php echo $t['search_results']; ?></h1>
        <?php if (!empty($search_query)): ?>
            <p class="search-info">
                <?php echo $t['showing_results']; ?>: <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong>
            </p>
        <?php endif; ?>
        
        <div class="search-box">
            <form action="search.php" method="GET">
                <input type="text" name="q" placeholder="<?php echo $t['search_placeholder']; ?>" value="<?php echo htmlspecialchars($search_query); ?>" required>
                <button type="submit"><i class="fas fa-search"></i> <?php echo $t['search_btn']; ?></button>
            </form>
        </div>
    </div>

    <div class="container">
        <a href="<?php echo BASE_URL; ?>/index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
        
        <?php if (!empty($search_query)): ?>
            <?php if ($items_result && $items_result->num_rows > 0): ?>
                <div class="items-grid">
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
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h3><?php echo $t['no_results']; ?></h3>
                    <p><?php echo $t['try_different']; ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
$conn->close();
?>