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
if (isset($_GET['lang']) && in_array($_GET['lang'], ['si', 'en'])) {
    $_SESSION['language'] = $_GET['lang'];
    header('Location: manage_items.php');
    exit();
}

$lang = $_SESSION['language'];

// Language translations
$translations = [
    'en' => [
        'page_title' => 'Manage Items',
        'dashboard' => 'Dashboard',
        'manage_items' => 'Manage Items',
        'add_new_item' => 'Add New Item',
        'item_list' => 'Item List',
        'search_items' => 'Search items...',
        'search' => 'Search',
        'item_name' => 'Item Name',
        'category' => 'Category',
        'price' => 'Price',
        'stock' => 'Stock',
        'status' => 'Status',
        'actions' => 'Actions',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'no_items' => 'No items found',
        'items_found' => 'items found',
        'delete_confirm' => 'Are you sure you want to delete this item?',
        'delete_success' => 'Item deleted successfully',
        'delete_error' => 'Error deleting item',
        'in_stock' => 'In Stock',
        'low_stock' => 'Low Stock',
        'out_of_stock' => 'Out of Stock',
        'view' => 'View',
        'all_categories' => 'All Categories',
        'filter' => 'Filter',
        'language' => 'Language',
        'logout' => 'Logout'
    ],
    'si' => [
        'page_title' => 'භාණ්ඩ කළමනාකරණය',
        'dashboard' => 'උපකරණ පුවරුව',
        'manage_items' => 'භාණ්ඩ කළමනාකරණය',
        'add_new_item' => 'නව භාණ්ඩයක් එක් කරන්න',
        'item_list' => 'භාණ්ඩ ලැයිස්තුව',
        'search_items' => 'භාණ්ඩ සොයන්න...',
        'search' => 'සොයන්න',
        'item_name' => 'භාණ්ඩ නාමය',
        'category' => 'වර්ගය',
        'price' => 'මිල',
        'stock' => 'තොගය',
        'status' => 'තත්ත්වය',
        'actions' => 'ක්‍රියා',
        'edit' => 'සංස්කරණය',
        'delete' => 'මකන්න',
        'active' => 'ක්‍රියාත්මක',
        'inactive' => 'අක්‍රිය',
        'no_items' => 'භාණ්ඩ හමු නොවීය',
        'items_found' => 'භාණ්ඩ හමු විය',
        'delete_confirm' => 'ඔබට මෙම භාණ්ඩය මකා දැමීමට අවශ්‍යද?',
        'delete_success' => 'භාණ්ඩය සාර්ථකව මකා දැමිණි',
        'delete_error' => 'භාණ්ඩය මකා දැමීමේ දෝෂයකි',
        'in_stock' => 'තොගයේ ඇත',
        'low_stock' => 'අඩු තොගයක්',
        'out_of_stock' => 'තොගයෙන් අවසන්',
        'view' => 'බලන්න',
        'all_categories' => 'සියලුම වර්ග',
        'filter' => 'පෙරීම',
        'language' => 'භාෂාව',
        'logout' => 'ඉවත් වන්න'
    ]
];

$t = $translations[$lang];
$page_title = $t['page_title'];

// Handle item deletion
if (isset($_GET['delete'])) {
    $item_id = intval($_GET['delete']);
    
    // Check if item exists
    $check_query = "SELECT * FROM items WHERE item_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $item_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $delete_query = "DELETE FROM items WHERE item_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $item_id);
        
        if ($delete_stmt->execute()) {
            $success = $t['delete_success'];
        } else {
            $error = $t['delete_error'];
        }
    }
}

// Get search and filter parameters
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Build query
$item_name_field = $lang == 'si' ? 'i.item_name_si' : 'i.item_name_en';
$category_name_field = $lang == 'si' ? 'c.category_name_si' : 'c.category_name_en';

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

$query .= " ORDER BY i.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$items_result = $stmt->get_result();

// Get categories for filter
$categories_query = "SELECT * FROM categories ORDER BY " . ($lang == 'si' ? 'category_name_si' : 'category_name_en');
$categories_result = $conn->query($categories_query);

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
        
        /* Page Header */
        .page-header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .page-header h2 {
            font-size: 28px;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 12px;
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
            grid-template-columns: 1fr 1fr auto;
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
        
        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .item-placeholder {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-active {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .status-inactive {
            background: #fed7d7;
            color: #742a2a;
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
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-sm {
            padding: 8px 14px;
            font-size: 13px;
        }
        
        .btn-info {
            background: #4299e1;
            color: white;
        }
        
        .btn-warning {
            background: #ed8936;
            color: white;
        }
        
        .btn-danger {
            background: #f56565;
            color: white;
        }
        
        .btn-sm:hover {
            opacity: 0.9;
            transform: translateY(-2px);
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
        
        @media (max-width: 768px) {
            .filter-row {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .items-table {
                font-size: 14px;
            }
            
            .items-table th,
            .items-table td {
                padding: 10px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-container">
            <h1><i class="fas fa-store"></i> <?php echo $t['manage_items']; ?></h1>
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
                <a href="../logout.php" class="btn btn-light">
                    <i class="fas fa-sign-out-alt"></i> <?php echo $t['logout']; ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h2><i class="fas fa-box"></i> <?php echo $t['item_list']; ?></h2>
            <a href="add_item.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?php echo $t['add_new_item']; ?>
            </a>
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
                        <label><i class="fas fa-filter"></i> <?php echo $t['category']; ?></label>
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
                            <th><?php echo $t['price']; ?></th>
                            <th><?php echo $t['stock']; ?></th>
                            <th><?php echo $t['status']; ?></th>
                            <th><?php echo $t['actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $items_result->fetch_assoc()): 
                            $item_display_name = $lang == 'si' ? $item['item_name_si'] : $item['item_name_en'];
                            $category_display_name = $lang == 'si' ? $item['category_name_si'] : $item['category_name_en'];
                            
                            // Construct image path
                            $image_path = '';
                            if (!empty($item['image_path'])) {
                                if (strpos($item['image_path'], 'uploads/items/') === 0) {
                                    $image_path = '../' . $item['image_path'];
                                } else {
                                    $image_path = '../uploads/items/' . $item['image_path'];
                                }
                            }
                        ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <?php if (!empty($image_path) && file_exists($image_path)): ?>
                                        <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($item_display_name); ?>" class="item-image">
                                    <?php else: ?>
                                        <div class="item-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                    <strong><?php echo htmlspecialchars($item_display_name); ?></strong>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($category_display_name); ?></td>
                            <td><strong>Rs. <?php echo number_format($item['price'], 2); ?></strong></td>
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
                            <td>
                                <?php if ($item['status'] == 'active'): ?>
                                    <span class="status-badge status-active">
                                        <i class="fas fa-check"></i> <?php echo $t['active']; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-inactive">
                                        <i class="fas fa-times"></i> <?php echo $t['inactive']; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="view_item.php?id=<?php echo $item['item_id']; ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> <?php echo $t['view']; ?>
                                    </a>
                                    <a href="edit_item.php?id=<?php echo $item['item_id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> <?php echo $t['edit']; ?>
                                    </a>
                                    <a href="?delete=<?php echo $item['item_id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('<?php echo $t['delete_confirm']; ?>')">
                                        <i class="fas fa-trash"></i> <?php echo $t['delete']; ?>
                                    </a>
                                </div>
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

    <script>
        // Confirm delete
        document.querySelectorAll('.btn-danger').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('<?php echo $t['delete_confirm']; ?>')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>