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
    $redirect_id = isset($_GET['id']) ? '?id=' . $_GET['id'] : '';
    header('Location: view_item.php' . $redirect_id);
    exit();
}

$lang = $_SESSION['language'];

// Language translations
$translations = [
    'en' => [
        'page_title' => 'View Item',
        'dashboard' => 'Dashboard',
        'manage_items' => 'Manage Items',
        'view_item' => 'View Item Details',
        'back_to_list' => 'Back to List',
        'edit_item' => 'Edit Item',
        'delete_item' => 'Delete Item',
        'item_name' => 'Item Name',
        'description' => 'Description',
        'category' => 'Category',
        'price' => 'Price',
        'purchase_price' => 'Purchase Price',
        'profit' => 'Profit',
        'profit_margin' => 'Profit Margin',
        'stock_quantity' => 'Stock Quantity',
        'low_stock_threshold' => 'Low Stock Alert',
        'status' => 'Status',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'item_image' => 'Item Image',
        'no_image' => 'No Image Available',
        'created_at' => 'Created On',
        'updated_at' => 'Last Updated',
        'item_not_found' => 'Item not found',
        'delete_confirm' => 'Are you sure you want to delete this item?',
        'in_stock' => 'In Stock',
        'low_stock' => 'Low Stock',
        'out_of_stock' => 'Out of Stock',
        'language' => 'Language',
        'logout' => 'Logout',
        'item_details' => 'Item Details',
        'pricing_stock' => 'Pricing & Stock',
        'additional_info' => 'Additional Information'
    ],
    'si' => [
        'page_title' => 'භාණ්ඩය බලන්න',
        'dashboard' => 'උපකරණ පුවරුව',
        'manage_items' => 'භාණ්ඩ කළමනාකරණය',
        'view_item' => 'භාණ්ඩ විස්තර බලන්න',
        'back_to_list' => 'ලැයිස්තුවට ආපසු',
        'edit_item' => 'භාණ්ඩය සංස්කරණය',
        'delete_item' => 'භාණ්ඩය මකන්න',
        'item_name' => 'භාණ්ඩ නාමය',
        'description' => 'විස්තරය',
        'category' => 'වර්ගය',
        'price' => 'මිල',
        'purchase_price' => 'ලබාගත් මිල',
        'profit' => 'ලාභය',
        'profit_margin' => 'ලාභ ප්‍රතිශතය',
        'stock_quantity' => 'තොග ප්‍රමාණය',
        'low_stock_threshold' => 'අඩු තොග අනතුරු ඇඟවීම',
        'status' => 'තත්ත්වය',
        'active' => 'ක්‍රියාත්මක',
        'inactive' => 'අක්‍රිය',
        'item_image' => 'භාණ්ඩ රූපය',
        'no_image' => 'රූපයක් නොමැත',
        'created_at' => 'නිර්මාණය කළ දිනය',
        'updated_at' => 'අවසන් යාවත්කාලීනය',
        'item_not_found' => 'භාණ්ඩය හමු නොවීය',
        'delete_confirm' => 'ඔබට මෙම භාණ්ඩය මකා දැමීමට අවශ්‍යද?',
        'in_stock' => 'තොගයේ ඇත',
        'low_stock' => 'අඩු තොගයක්',
        'out_of_stock' => 'තොගයෙන් අවසන්',
        'language' => 'භාෂාව',
        'logout' => 'ඉවත් වන්න',
        'item_details' => 'භාණ්ඩ විස්තර',
        'pricing_stock' => 'මිල සහ තොගය',
        'additional_info' => 'අතිරේක තොරතුරු'
    ]
];

$t = $translations[$lang];
$page_title = $t['page_title'];

// Get item ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_items.php");
    exit();
}

$item_id = intval($_GET['id']);

// Fetch item details with category
$query = "SELECT i.*, 
          c.category_name_si, 
          c.category_name_en
          FROM items i
          LEFT JOIN categories c ON i.category_id = c.category_id
          WHERE i.item_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = $t['item_not_found'];
    header("Location: manage_items.php");
    exit();
}

$item = $result->fetch_assoc();

// Calculate profit if purchase price exists
$profit = 0;
$profit_margin = 0;
if (!empty($item['purchase_price']) && $item['purchase_price'] > 0) {
    $profit = $item['price'] - $item['purchase_price'];
    $profit_margin = ($profit / $item['purchase_price']) * 100;
}

// Get display values based on language
$item_name = $lang == 'si' ? $item['item_name_si'] : $item['item_name_en'];
$item_description = $lang == 'si' ? $item['description_si'] : $item['description_en'];
$category_name = $lang == 'si' ? $item['category_name_si'] : $item['category_name_en'];

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
            max-width: 1200px;
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
        
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-secondary {
            background: #718096;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4a5568;
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background: #ed8936;
            color: white;
        }
        
        .btn-warning:hover {
            background: #dd6b20;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: #f56565;
            color: white;
        }
        
        .btn-danger:hover {
            background: #e53e3e;
            transform: translateY(-2px);
        }
        
        /* Content Layout */
        .content-grid {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        /* Image Card */
        .image-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            height: fit-content;
        }
        
        .item-image-large {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 12px;
            border: 3px solid #e0e0e0;
        }
        
        .image-placeholder-large {
            width: 100%;
            height: 400px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 80px;
        }
        
        /* Details Card */
        .details-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .section-title {
            font-size: 20px;
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .detail-row {
            display: grid;
            grid-template-columns: 200px 1fr;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
            gap: 20px;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #4a5568;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .detail-value {
            color: #2d3748;
            font-size: 16px;
        }
        
        .detail-value.large {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
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
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
        
        .description-box {
            background: #f7fafc;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            color: #2d3748;
            line-height: 1.6;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .profit-highlight {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin: 15px 0;
            box-shadow: 0 4px 12px rgba(72, 187, 120, 0.3);
        }
        
        .profit-highlight.negative {
            background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
            box-shadow: 0 4px 12px rgba(245, 101, 101, 0.3);
        }
        
        @media (max-width: 968px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .detail-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: stretch;
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
            <h1><i class="fas fa-eye"></i> <?php echo $t['view_item']; ?></h1>
            <div class="header-actions">
                <!-- Language Switcher -->
                <a href="?lang=en&id=<?php echo $item_id; ?>" class="lang-btn <?php echo $lang == 'en' ? 'active' : ''; ?>">
                    <i class="fas fa-flag-usa"></i> EN
                </a>
                <a href="?lang=si&id=<?php echo $item_id; ?>" class="lang-btn <?php echo $lang == 'si' ? 'active' : ''; ?>">
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
            <h2><i class="fas fa-box"></i> <?php echo htmlspecialchars($item_name); ?></h2>
            <div class="action-buttons">
                <a href="manage_items.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> <?php echo $t['back_to_list']; ?>
                </a>
                <a href="edit_item.php?id=<?php echo $item_id; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> <?php echo $t['edit_item']; ?>
                </a>
                <a href="manage_items.php?delete=<?php echo $item_id; ?>" 
                   class="btn btn-danger" 
                   onclick="return confirm('<?php echo $t['delete_confirm']; ?>')">
                    <i class="fas fa-trash"></i> <?php echo $t['delete_item']; ?>
                </a>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Image Card -->
            <div class="image-card">
                <h3 class="section-title">
                    <i class="fas fa-image"></i> <?php echo $t['item_image']; ?>
                </h3>
                <?php if (!empty($image_path) && file_exists($image_path)): ?>
                    <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($item_name); ?>" class="item-image-large">
                <?php else: ?>
                    <div class="image-placeholder-large">
                        <i class="fas fa-image"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Details Card -->
            <div class="details-card">
                <!-- Item Details Section -->
                <div class="info-section">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle"></i> <?php echo $t['item_details']; ?>
                    </h3>
                    
                    <div class="detail-row">
                        <div class="detail-label">
                            <i class="fas fa-tag"></i> <?php echo $t['item_name']; ?>
                        </div>
                        <div class="detail-value">
                            <strong><?php echo htmlspecialchars($item_name); ?></strong>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-label">
                            <i class="fas fa-list"></i> <?php echo $t['category']; ?>
                        </div>
                        <div class="detail-value">
                            <?php echo htmlspecialchars($category_name); ?>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-label">
                            <i class="fas fa-align-left"></i> <?php echo $t['description']; ?>
                        </div>
                        <div class="detail-value">
                            <?php if (!empty($item_description)): ?>
                                <div class="description-box">
                                    <?php echo nl2br(htmlspecialchars($item_description)); ?>
                                </div>
                            <?php else: ?>
                                <em style="color: #a0aec0;">No description available</em>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Stock Section -->
                <div class="info-section">
                    <h3 class="section-title">
                        <i class="fas fa-dollar-sign"></i> <?php echo $t['pricing_stock']; ?>
                    </h3>
                    
                    <div class="detail-row">
                        <div class="detail-label">
                            <i class="fas fa-money-bill-wave"></i> <?php echo $t['price']; ?>
                        </div>
                        <div class="detail-value large">
                            Rs. <?php echo number_format($item['price'], 2); ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($item['purchase_price']) && $item['purchase_price'] > 0): ?>
                    <div class="detail-row">
                        <div class="detail-label">
                            <i class="fas fa-shopping-cart"></i> <?php echo $t['purchase_price']; ?>
                        </div>
                        <div class="detail-value" style="font-size: 20px; font-weight: 600; color: #e53e3e;">
                            Rs. <?php echo number_format($item['purchase_price'], 2); ?>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-label">
                            <i class="fas fa-chart-line"></i> <?php echo $t['profit']; ?>
                        </div>
                        <div class="detail-value">
                            <div class="profit-highlight <?php echo $profit < 0 ? 'negative' : ''; ?>">
                                <div style="font-size: 24px; font-weight: 700; margin-bottom: 8px;">
                                    <i class="fas fa-<?php echo $profit >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                                    Rs. <?php echo number_format($profit, 2); ?>
                                </div>
                                <div style="font-size: 16px; opacity: 0.9;">
                                    <?php echo $t['profit_margin']; ?>: <?php echo number_format($profit_margin, 2); ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="detail-row">
                        <div class="detail-label">
                            <i class="fas fa-boxes"></i> <?php echo $t['stock_quantity']; ?>
                        </div>
                        <div class="detail-value">
                            <?php if ($item['stock_quantity'] > $item['low_stock_threshold']): ?>
                                <span class="stock-badge stock-in">
                                    <i class="fas fa-check-circle"></i> <?php echo $item['stock_quantity']; ?> units
                                </span>
                            <?php elseif ($item['stock_quantity'] > 0): ?>
                                <span class="stock-badge stock-low">
                                    <i class="fas fa-exclamation-circle"></i> <?php echo $item['stock_quantity']; ?> units
                                </span>
                            <?php else: ?>
                                <span class="stock-badge stock-out">
                                    <i class="fas fa-times-circle"></i> 0 units
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-label">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $t['low_stock_threshold']; ?>
                        </div>
                        <div class="detail-value">
                            <?php echo $item['low_stock_threshold']; ?> units
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-label">
                            <i class="fas fa-toggle-on"></i> <?php echo $t['status']; ?>
                        </div>
                        <div class="detail-value">
                            <?php if ($item['status'] == 'active'): ?>
                                <span class="status-badge status-active">
                                    <i class="fas fa-check"></i> <?php echo $t['active']; ?>
                                </span>
                            <?php else: ?>
                                <span class="status-badge status-inactive">
                                    <i class="fas fa-times"></i> <?php echo $t['inactive']; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="info-section">
                    <h3 class="section-title">
                        <i class="fas fa-clock"></i> <?php echo $t['additional_info']; ?>
                    </h3>
                    
                    <div class="detail-row">
                        <div class="detail-label">
                            <i class="fas fa-calendar-plus"></i> <?php echo $t['created_at']; ?>
                        </div>
                        <div class="detail-value">
                            <?php echo date('F j, Y, g:i a', strtotime($item['created_at'])); ?>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-label">
                            <i class="fas fa-calendar-check"></i> <?php echo $t['updated_at']; ?>
                        </div>
                        <div class="detail-value">
                            <?php echo date('F j, Y, g:i a', strtotime($item['updated_at'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>