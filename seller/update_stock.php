<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller') {
    header("Location: ../login.php");
    exit();
}

// Language handling - Default to English
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'en';
}

// Handle language switch
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'si'])) {
    $_SESSION['language'] = $_GET['lang'];
    header('Location: update_stock.php');
    exit();
}

$lang = $_SESSION['language'];

// Language translations
$translations = [
    'en' => [
        'page_title' => 'Update Stock',
        'dashboard' => 'Dashboard',
        'manage_items' => 'Manage Items',
        'update_stock' => 'Update Stock',
        'stock_management' => 'Stock Management',
        'search_items' => 'Search items...',
        'search' => 'Search',
        'item_name' => 'Item Name',
        'category' => 'Category',
        'current_stock' => 'Current Stock',
        'threshold' => 'Threshold',
        'new_stock' => 'New Stock',
        'actions' => 'Actions',
        'update' => 'Update',
        'add_stock' => 'Add Stock',
        'remove_stock' => 'Remove Stock',
        'set_stock' => 'Set Stock',
        'no_items' => 'No items found',
        'update_success' => 'Stock updated successfully',
        'update_error' => 'Error updating stock',
        'invalid_quantity' => 'Invalid quantity',
        'in_stock' => 'In Stock',
        'low_stock' => 'Low Stock',
        'out_of_stock' => 'Out of Stock',
        'all_categories' => 'All Categories',
        'filter' => 'Filter',
        'language' => 'Language',
        'logout' => 'Logout',
        'stock_level' => 'Stock Level',
        'quick_actions' => 'Quick Actions',
        'quantity' => 'Quantity',
        'operation' => 'Operation',
        'add' => 'Add',
        'subtract' => 'Subtract',
        'set' => 'Set',
        'total_items' => 'Total Items',
        'low_stock_items' => 'Low Stock Items',
        'out_of_stock_items' => 'Out of Stock',
        'stock_alerts' => 'Stock Alerts'
    ],
    'si' => [
        'page_title' => 'තොග යාවත්කාලීන කරන්න',
        'dashboard' => 'උපකරණ පුවරුව',
        'manage_items' => 'භාණ්ඩ කළමනාකරණය',
        'update_stock' => 'තොග යාවත්කාලීන කරන්න',
        'stock_management' => 'තොග කළමනාකරණය',
        'search_items' => 'භාණ්ඩ සොයන්න...',
        'search' => 'සොයන්න',
        'item_name' => 'භාණ්ඩ නාමය',
        'category' => 'වර්ගය',
        'current_stock' => 'වත්මන් තොගය',
        'threshold' => 'සීමාව',
        'new_stock' => 'නව තොගය',
        'actions' => 'ක්‍රියා',
        'update' => 'යාවත්කාලීන කරන්න',
        'add_stock' => 'තොගය එකතු කරන්න',
        'remove_stock' => 'තොගය ඉවත් කරන්න',
        'set_stock' => 'තොගය සකසන්න',
        'no_items' => 'භාණ්ඩ හමු නොවීය',
        'update_success' => 'තොගය සාර්ථකව යාවත්කාලීන කරන ලදී',
        'update_error' => 'තොගය යාවත්කාලීන කිරීමේ දෝෂයකි',
        'invalid_quantity' => 'වලංගු නොවන ප්‍රමාණයක්',
        'in_stock' => 'තොගයේ ඇත',
        'low_stock' => 'අඩු තොගයක්',
        'out_of_stock' => 'තොගයෙන් අවසන්',
        'all_categories' => 'සියලුම වර්ග',
        'filter' => 'පෙරීම',
        'language' => 'භාෂාව',
        'logout' => 'ඉවත් වන්න',
        'stock_level' => 'තොග මට්ටම',
        'quick_actions' => 'ඉක්මන් ක්‍රියා',
        'quantity' => 'ප්‍රමාණය',
        'operation' => 'ක්‍රියාව',
        'add' => 'එකතු කරන්න',
        'subtract' => 'අඩු කරන්න',
        'set' => 'සකසන්න',
        'total_items' => 'මුළු භාණ්ඩ',
        'low_stock_items' => 'අඩු තොග භාණ්ඩ',
        'out_of_stock_items' => 'තොගයෙන් අවසන්',
        'stock_alerts' => 'තොග අනතුරු ඇඟවීම්'
    ]
];

$t = $translations[$lang];
$page_title = $t['page_title'];

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_stock'])) {
    $item_id = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']);
    $operation = clean_input($_POST['operation']);
    
    if ($quantity < 0) {
        $error = $t['invalid_quantity'];
    } else {
        // Get current stock
        $check_query = "SELECT stock_quantity FROM items WHERE item_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $item_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $item_data = $check_result->fetch_assoc();
            $current_stock = $item_data['stock_quantity'];
            $new_stock = $current_stock;
            
            // Calculate new stock based on operation
            switch ($operation) {
                case 'add':
                    $new_stock = $current_stock + $quantity;
                    break;
                case 'subtract':
                    $new_stock = max(0, $current_stock - $quantity);
                    break;
                case 'set':
                    $new_stock = $quantity;
                    break;
            }
            
            // Update stock
            $update_query = "UPDATE items SET stock_quantity = ? WHERE item_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ii", $new_stock, $item_id);
            
            if ($update_stmt->execute()) {
                $success = $t['update_success'];
            } else {
                $error = $t['update_error'];
            }
        }
    }
}

// Get search and filter parameters
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$stock_filter = isset($_GET['stock']) ? clean_input($_GET['stock']) : 'all';

// Build query
$query = "SELECT i.*, 
          c.category_name_si, 
          c.category_name_en
          FROM items i
          LEFT JOIN categories c ON i.category_id = c.category_id
          WHERE 1=1";

$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (i.item_name_si LIKE ? OR i.item_name_en LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if ($category_filter > 0) {
    $query .= " AND i.category_id = ?";
    $params[] = $category_filter;
    $types .= "i";
}

// Stock level filter
if ($stock_filter == 'low') {
    $query .= " AND i.stock_quantity > 0 AND i.stock_quantity <= i.low_stock_threshold";
} elseif ($stock_filter == 'out') {
    $query .= " AND i.stock_quantity = 0";
}

$query .= " ORDER BY i.stock_quantity ASC, i.item_name_en";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$items_result = $stmt->get_result();

// Get categories
$categories_query = "SELECT * FROM categories ORDER BY " . ($lang == 'si' ? 'category_name_si' : 'category_name_en');
$categories_result = $conn->query($categories_query);

// Get statistics
$stats_query = "SELECT 
                COUNT(*) as total_items,
                SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
                SUM(CASE WHEN stock_quantity > 0 AND stock_quantity <= low_stock_threshold THEN 1 ELSE 0 END) as low_stock
                FROM items";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['page_title']; ?> - Suminda Stores</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
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
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .header h1 {
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .lang-btn {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s;
            border: 2px solid white;
            background: transparent;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .lang-btn:hover {
            background: white;
            color: #667eea;
        }
        
        .lang-btn.active {
            background: white;
            color: #667eea;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
            font-family: 'Noto Sans Sinhala', sans-serif;
        }
        
        .btn-light {
            background: white;
            color: #667eea;
        }
        
        .btn-light:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255,255,255,0.3);
        }
        
        /* Container */
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
        }
        
        .stat-icon.blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stat-icon.yellow {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
        }
        
        .stat-icon.red {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-content h3 {
            font-size: 32px;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .stat-content p {
            color: #718096;
            font-size: 14px;
        }
        
        /* Filters */
        .filters {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-weight: 600;
            color: #2d3748;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Noto Sans Sinhala', sans-serif;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        /* Items Table */
        .items-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .items-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .items-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .items-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .stock-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .stock-in {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .stock-low {
            background: #feebc8;
            color: #7c2d12;
        }
        
        .stock-out {
            background: #fed7d7;
            color: #742a2a;
        }
        
        .stock-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .stock-input {
            width: 80px;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-family: 'Noto Sans Sinhala', sans-serif;
        }
        
        .stock-select {
            width: 100px;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-family: 'Noto Sans Sinhala', sans-serif;
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
        }
        
        .btn-success {
            background: #48bb78;
            color: white;
        }
        
        .btn-success:hover {
            background: #38a169;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }
        
        .empty-state i {
            font-size: 60px;
            color: #cbd5e0;
            margin-bottom: 20px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .alert-error {
            background: #fed7d7;
            color: #742a2a;
        }
        
        @media (max-width: 968px) {
            .filter-row {
                grid-template-columns: 1fr;
            }
            
            .items-table {
                font-size: 14px;
            }
            
            .stock-form {
                flex-direction: column;
            }
            
            .stock-input,
            .stock-select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-container">
            <h1><i class="fas fa-warehouse"></i> <?php echo $t['stock_management']; ?></h1>
            <div class="header-actions">
                <!-- Language Switcher -->
                <a href="?lang=en" class="lang-btn <?php echo $lang == 'en' ? 'active' : ''; ?>">
                    <i class="fas fa-flag-usa"></i> EN
                </a>
                <a href="?lang=si" class="lang-btn <?php echo $lang == 'si' ? 'active' : ''; ?>">
                    <i class="fas fa-flag"></i> සිං
                </a>
                
                <a href="index.php" class="btn btn-light">
                    <i class="fas fa-tachometer-alt"></i> <?php echo $t['dashboard']; ?>
                </a>
                <a href="manage_items.php" class="btn btn-light">
                    <i class="fas fa-box"></i> <?php echo $t['manage_items']; ?>
                </a>
                <a href="../logout.php" class="btn btn-light">
                    <i class="fas fa-sign-out-alt"></i> <?php echo $t['logout']; ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_items']; ?></h3>
                    <p><?php echo $t['total_items']; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['low_stock']; ?></h3>
                    <p><?php echo $t['low_stock_items']; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['out_of_stock']; ?></h3>
                    <p><?php echo $t['out_of_stock_items']; ?></p>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="filters">
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="form-group">
                        <label><i class="fas fa-search"></i> <?php echo $t['search']; ?></label>
                        <input type="text" name="search" placeholder="<?php echo $t['search_items']; ?>" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-list"></i> <?php echo $t['category']; ?></label>
                        <select name="category">
                            <option value="0"><?php echo $t['all_categories']; ?></option>
                            <?php while ($category = $categories_result->fetch_assoc()): ?>
                                <option value="<?php echo $category['category_id']; ?>" 
                                    <?php echo $category_filter == $category['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo $lang == 'si' ? $category['category_name_si'] : $category['category_name_en']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-layer-group"></i> <?php echo $t['stock_level']; ?></label>
                        <select name="stock">
                            <option value="all" <?php echo $stock_filter == 'all' ? 'selected' : ''; ?>>All</option>
                            <option value="low" <?php echo $stock_filter == 'low' ? 'selected' : ''; ?>><?php echo $t['low_stock']; ?></option>
                            <option value="out" <?php echo $stock_filter == 'out' ? 'selected' : ''; ?>><?php echo $t['out_of_stock']; ?></option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> <?php echo $t['filter']; ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Items Table -->
        <div class="items-card">
            <?php if ($items_result->num_rows > 0): ?>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th><?php echo $t['item_name']; ?></th>
                            <th><?php echo $t['category']; ?></th>
                            <th><?php echo $t['current_stock']; ?></th>
                            <th><?php echo $t['threshold']; ?></th>
                            <th><?php echo $t['quick_actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $items_result->fetch_assoc()): 
                            $item_name = $lang == 'si' ? $item['item_name_si'] : $item['item_name_en'];
                            $category_name = $lang == 'si' ? $item['category_name_si'] : $item['category_name_en'];
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($item_name); ?></strong></td>
                            <td><?php echo htmlspecialchars($category_name); ?></td>
                            <td>
                                <?php if ($item['stock_quantity'] > $item['low_stock_threshold']): ?>
                                    <span class="stock-badge stock-in">
                                        <i class="fas fa-check-circle"></i> <?php echo $item['stock_quantity']; ?>
                                    </span>
                                <?php elseif ($item['stock_quantity'] > 0): ?>
                                    <span class="stock-badge stock-low">
                                        <i class="fas fa-exclamation-circle"></i> <?php echo $item['stock_quantity']; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="stock-badge stock-out">
                                        <i class="fas fa-times-circle"></i> 0
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $item['low_stock_threshold']; ?></td>
                            <td>
                                <form method="POST" action="" class="stock-form">
                                    <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                    <input type="number" name="quantity" class="stock-input" min="0" value="1" required>
                                    <select name="operation" class="stock-select">
                                        <option value="add"><?php echo $t['add']; ?></option>
                                        <option value="subtract"><?php echo $t['subtract']; ?></option>
                                        <option value="set"><?php echo $t['set']; ?></option>
                                    </select>
                                    <button type="submit" name="update_stock" class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i> <?php echo $t['update']; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3><?php echo $t['no_items']; ?></h3>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>