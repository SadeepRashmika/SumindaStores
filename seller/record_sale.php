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
    header('Location: record_sale.php');
    exit();
}

$lang = $_SESSION['language'];

// Language translations
$translations = [
    'en' => [
        'page_title' => 'Record Sale',
        'dashboard' => 'Dashboard',
        'manage_items' => 'Manage Items',
        'record_sale' => 'Record Sale',
        'new_sale' => 'New Sale',
        'select_item' => 'Select Item',
        'item_name' => 'Item',
        'quantity' => 'Quantity',
        'unit_price' => 'Unit Price',
        'total' => 'Total',
        'add_item' => 'Add Item',
        'remove' => 'Remove',
        'sale_items' => 'Sale Items',
        'no_items_added' => 'No items added yet',
        'customer_name' => 'Customer Name (Optional)',
        'customer_phone' => 'Customer Phone (Optional)',
        'payment_method' => 'Payment Method',
        'cash' => 'Cash',
        'card' => 'Card',
        'bank_transfer' => 'Bank Transfer',
        'mobile_payment' => 'Mobile Payment',
        'notes' => 'Notes (Optional)',
        'subtotal' => 'Subtotal',
        'complete_sale' => 'Complete Sale',
        'clear_all' => 'Clear All',
        'sale_success' => 'Sale recorded successfully',
        'sale_error' => 'Error recording sale',
        'insufficient_stock' => 'Insufficient stock for',
        'invalid_quantity' => 'Invalid quantity',
        'no_items_in_cart' => 'Please add items to complete sale',
        'select_item_first' => 'Please select an item',
        'available_stock' => 'Available',
        'out_of_stock' => 'Out of Stock',
        'language' => 'Language',
        'logout' => 'Logout',
        'recent_sales' => 'Recent Sales',
        'date' => 'Date',
        'customer' => 'Customer',
        'items' => 'Items',
        'amount' => 'Amount',
        'view_all_sales' => 'View All Sales',
        'search_items' => 'Search items...',
        'cart' => 'Cart'
    ],
    'si' => [
        'page_title' => 'විකුණුම් වාර්තාව',
        'dashboard' => 'උපකරණ පුවරුව',
        'manage_items' => 'භාණ්ඩ කළමනාකරණය',
        'record_sale' => 'විකුණුම් වාර්තාව',
        'new_sale' => 'නව විකුණුම',
        'select_item' => 'භාණ්ඩය තෝරන්න',
        'item_name' => 'භාණ්ඩය',
        'quantity' => 'ප්‍රමාණය',
        'unit_price' => 'ඒකක මිල',
        'total' => 'මුළු එකතුව',
        'add_item' => 'භාණ්ඩය එකතු කරන්න',
        'remove' => 'ඉවත් කරන්න',
        'sale_items' => 'විකුණුම් භාණ්ඩ',
        'no_items_added' => 'තවමත් භාණ්ඩ එකතු කර නැත',
        'customer_name' => 'පාරිභෝගික නම (අනිවාර්ය නොවේ)',
        'customer_phone' => 'පාරිභෝගික දුරකථනය (අනිවාර්ය නොවේ)',
        'payment_method' => 'ගෙවීමේ ක්‍රමය',
        'cash' => 'මුදල්',
        'card' => 'කාඩ්පත',
        'bank_transfer' => 'බැංකු මාරුව',
        'mobile_payment' => 'ජංගම ගෙවීම',
        'notes' => 'සටහන් (අනිවාර්ය නොවේ)',
        'subtotal' => 'උප එකතුව',
        'complete_sale' => 'විකුණුම සම්පූර්ණ කරන්න',
        'clear_all' => 'සියල්ල ඉවත් කරන්න',
        'sale_success' => 'විකුණුම සාර්ථකව වාර්තා කරන ලදී',
        'sale_error' => 'විකුණුම වාර්තා කිරීමේ දෝෂයකි',
        'insufficient_stock' => 'ප්‍රමාණවත් තොගයක් නොමැත',
        'invalid_quantity' => 'වලංගු නොවන ප්‍රමාණයක්',
        'no_items_in_cart' => 'විකුණුම සම්පූර්ණ කිරීමට භාණ්ඩ එකතු කරන්න',
        'select_item_first' => 'කරුණාකර භාණ්ඩයක් තෝරන්න',
        'available_stock' => 'තොගයේ ඇත',
        'out_of_stock' => 'තොගයෙන් අවසන්',
        'language' => 'භාෂාව',
        'logout' => 'ඉවත් වන්න',
        'recent_sales' => 'මෑත විකුණුම්',
        'date' => 'දිනය',
        'customer' => 'පාරිභෝගිකයා',
        'items' => 'භාණ්ඩ',
        'amount' => 'මුදල',
        'view_all_sales' => 'සියලුම විකුණුම් බලන්න',
        'search_items' => 'භාණ්ඩ සොයන්න...',
        'cart' => 'කරත්තය'
    ]
];

$t = $translations[$lang];
$page_title = $t['page_title'];

// Initialize cart in session
if (!isset($_SESSION['sale_cart'])) {
    $_SESSION['sale_cart'] = [];
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    $item_id = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']);
    
    if ($item_id > 0 && $quantity > 0) {
        // Get item details
        $item_query = "SELECT * FROM items WHERE item_id = ? AND status = 'active'";
        $item_stmt = $conn->prepare($item_query);
        $item_stmt->bind_param("i", $item_id);
        $item_stmt->execute();
        $item_result = $item_stmt->get_result();
        
        if ($item_result->num_rows > 0) {
            $item = $item_result->fetch_assoc();
            
            // Check stock
            if ($item['stock_quantity'] >= $quantity) {
                // Add to cart or update quantity
                if (isset($_SESSION['sale_cart'][$item_id])) {
                    $_SESSION['sale_cart'][$item_id]['quantity'] += $quantity;
                } else {
                    $_SESSION['sale_cart'][$item_id] = [
                        'item_id' => $item['item_id'],
                        'item_name_en' => $item['item_name_en'],
                        'item_name_si' => $item['item_name_si'],
                        'price' => $item['price'],
                        'quantity' => $quantity,
                        'stock_quantity' => $item['stock_quantity']
                    ];
                }
            } else {
                $error = $t['insufficient_stock'] . ' ' . ($lang == 'si' ? $item['item_name_si'] : $item['item_name_en']);
            }
        }
    }
}

// Handle remove from cart
if (isset($_GET['remove'])) {
    $item_id = intval($_GET['remove']);
    if (isset($_SESSION['sale_cart'][$item_id])) {
        unset($_SESSION['sale_cart'][$item_id]);
    }
    header('Location: record_sale.php');
    exit();
}

// Handle clear cart
if (isset($_POST['clear_cart'])) {
    $_SESSION['sale_cart'] = [];
}

// Handle complete sale
if (isset($_POST['complete_sale'])) {
    if (empty($_SESSION['sale_cart'])) {
        $error = $t['no_items_in_cart'];
    } else {
        $customer_name = clean_input($_POST['customer_name']);
        $customer_phone = clean_input($_POST['customer_phone']);
        $payment_method = clean_input($_POST['payment_method']);
        $notes = clean_input($_POST['notes']);
        $seller_id = $_SESSION['user_id'];
        
        // Calculate total
        $total_amount = 0;
        foreach ($_SESSION['sale_cart'] as $cart_item) {
            $total_amount += $cart_item['price'] * $cart_item['quantity'];
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert sale record
            $sale_query = "INSERT INTO sales (seller_id, customer_name, customer_phone, total_amount, payment_method, notes, sale_date) 
                          VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $sale_stmt = $conn->prepare($sale_query);
            $sale_stmt->bind_param("issdss", $seller_id, $customer_name, $customer_phone, $total_amount, $payment_method, $notes);
            $sale_stmt->execute();
            
            $sale_id = $conn->insert_id;
            
            // Insert sale items and update stock
            foreach ($_SESSION['sale_cart'] as $cart_item) {
                // Insert sale item
                $item_query = "INSERT INTO sale_items (sale_id, item_id, quantity, unit_price, total_price) 
                              VALUES (?, ?, ?, ?, ?)";
                $item_stmt = $conn->prepare($item_query);
                $item_total = $cart_item['price'] * $cart_item['quantity'];
                $item_stmt->bind_param("iiidd", $sale_id, $cart_item['item_id'], $cart_item['quantity'], $cart_item['price'], $item_total);
                $item_stmt->execute();
                
                // Update stock
                $update_query = "UPDATE items SET stock_quantity = stock_quantity - ? WHERE item_id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("ii", $cart_item['quantity'], $cart_item['item_id']);
                $update_stmt->execute();
            }
            
            // Commit transaction
            $conn->commit();
            
            // Clear cart
            $_SESSION['sale_cart'] = [];
            
            $success = $t['sale_success'];
        } catch (Exception $e) {
            $conn->rollback();
            $error = $t['sale_error'];
        }
    }
}

// Get all active items for dropdown
$items_query = "SELECT item_id, item_name_en, item_name_si, price, stock_quantity 
                FROM items 
                WHERE status = 'active' 
                ORDER BY " . ($lang == 'si' ? 'item_name_si' : 'item_name_en');
$items_result = $conn->query($items_query);

// Get recent sales
$recent_sales_query = "SELECT s.*, COUNT(si.sale_item_id) as item_count 
                       FROM sales s 
                       LEFT JOIN sale_items si ON s.sale_id = si.sale_id 
                       GROUP BY s.sale_id 
                       ORDER BY s.sale_date DESC 
                       LIMIT 5";
$recent_sales_result = $conn->query($recent_sales_query);

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
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 450px;
            gap: 30px;
        }
        
        /* Add Item Card */
        .add-item-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .card-title {
            font-size: 22px;
            color: #2d3748;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Noto Sans Sinhala', sans-serif;
            transition: all 0.3s;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .stock-info {
            font-size: 13px;
            color: #718096;
            margin-top: 5px;
        }
        
        .stock-info.low {
            color: #ed8936;
        }
        
        .stock-info.out {
            color: #f56565;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
            width: 100%;
            justify-content: center;
            padding: 15px;
            font-size: 16px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-danger {
            background: #f56565;
            color: white;
        }
        
        .btn-danger:hover {
            background: #e53e3e;
        }
        
        .btn-success {
            background: #48bb78;
            color: white;
            width: 100%;
            justify-content: center;
            padding: 15px;
            font-size: 16px;
        }
        
        .btn-success:hover {
            background: #38a169;
            transform: translateY(-2px);
        }
        
        /* Cart Card */
        .cart-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .cart-items {
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        
        .cart-item {
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }
        
        .cart-item-info h4 {
            font-size: 15px;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .cart-item-details {
            font-size: 13px;
            color: #718096;
        }
        
        .cart-item-price {
            text-align: right;
        }
        
        .cart-item-price .price {
            font-size: 16px;
            font-weight: 700;
            color: #667eea;
        }
        
        .cart-item-price .remove-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #f56565;
            text-decoration: none;
            font-size: 13px;
            margin-top: 5px;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .cart-item-price .remove-btn:hover {
            background: #fed7d7;
        }
        
        .empty-cart {
            text-align: center;
            padding: 40px 20px;
            color: #a0aec0;
        }
        
        .empty-cart i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .cart-total {
            border-top: 2px solid #e0e0e0;
            padding-top: 20px;
            margin-top: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 20px;
        }
        
        .total-amount {
            font-size: 28px;
            color: #667eea;
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        /* Recent Sales */
        .recent-sales-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .sales-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .sales-table th {
            background: #f7fafc;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            font-size: 13px;
        }
        
        .sales-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
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
        
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .cart-card {
                position: static;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-container">
            <h1><i class="fas fa-cash-register"></i> <?php echo $t['record_sale']; ?></h1>
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

        <div class="content-grid">
            <!-- Left Column -->
            <div>
                <!-- Add Item Form -->
                <div class="add-item-card">
                    <h2 class="card-title">
                        <i class="fas fa-plus-circle"></i> <?php echo $t['add_item']; ?>
                    </h2>
                    
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-box"></i> <?php echo $t['select_item']; ?> *</label>
                                <select name="item_id" id="item_select" required onchange="updateItemInfo()">
                                    <option value=""><?php echo $t['select_item']; ?></option>
                                    <?php while ($item = $items_result->fetch_assoc()): ?>
                                        <option value="<?php echo $item['item_id']; ?>" 
                                                data-price="<?php echo $item['price']; ?>"
                                                data-stock="<?php echo $item['stock_quantity']; ?>">
                                            <?php echo $lang == 'si' ? $item['item_name_si'] : $item['item_name_en']; ?>
                                            (Rs. <?php echo number_format($item['price'], 2); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div id="stock_info" class="stock-info"></div>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-hashtag"></i> <?php echo $t['quantity']; ?> *</label>
                                <input type="number" name="quantity" id="quantity" min="1" value="1" required>
                            </div>
                        </div>
                        
                        <button type="submit" name="add_to_cart" class="btn btn-primary">
                            <i class="fas fa-cart-plus"></i> <?php echo $t['add_item']; ?>
                        </button>
                    </form>
                </div>

                <!-- Recent Sales -->
                <div class="recent-sales-card">
                    <h2 class="card-title">
                        <i class="fas fa-history"></i> <?php echo $t['recent_sales']; ?>
                    </h2>
                    
                    <?php if ($recent_sales_result->num_rows > 0): ?>
                        <table class="sales-table">
                            <thead>
                                <tr>
                                    <th><?php echo $t['date']; ?></th>
                                    <th><?php echo $t['customer']; ?></th>
                                    <th><?php echo $t['items']; ?></th>
                                    <th><?php echo $t['amount']; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($sale = $recent_sales_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M j, g:i A', strtotime($sale['sale_date'])); ?></td>
                                    <td><?php echo !empty($sale['customer_name']) ? htmlspecialchars($sale['customer_name']) : '-'; ?></td>
                                    <td><?php echo $sale['item_count']; ?> items</td>
                                    <td><strong>Rs. <?php echo number_format($sale['total_amount'], 2); ?></strong></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-cart">
                            <i class="fas fa-receipt"></i>
                            <p>No recent sales</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column - Cart -->
            <div class="cart-card">
                <h2 class="card-title">
                    <i class="fas fa-shopping-cart"></i> <?php echo $t['cart']; ?>
                    <?php if (!empty($_SESSION['sale_cart'])): ?>
                        <span style="background: #667eea; color: white; padding: 4px 10px; border-radius: 20px; font-size: 14px; margin-left: auto;">
                            <?php echo count($_SESSION['sale_cart']); ?>
                        </span>
                    <?php endif; ?>
                </h2>
                
                <div class="cart-items">
                    <?php if (empty($_SESSION['sale_cart'])): ?>
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart"></i>
                            <p><?php echo $t['no_items_added']; ?></p>
                        </div>
                    <?php else: ?>
                        <?php 
                        $subtotal = 0;
                        foreach ($_SESSION['sale_cart'] as $cart_item): 
                            $item_total = $cart_item['price'] * $cart_item['quantity'];
                            $subtotal += $item_total;
                            $item_name = $lang == 'si' ? $cart_item['item_name_si'] : $cart_item['item_name_en'];
                        ?>
                        <div class="cart-item">
                            <div class="cart-item-info">
                                <h4><?php echo htmlspecialchars($item_name); ?></h4>
                                <div class="cart-item-details">
                                    <?php echo $cart_item['quantity']; ?> x Rs. <?php echo number_format($cart_item['price'], 2); ?>
                                </div>
                            </div>
                            <div class="cart-item-price">
                                <div class="price">Rs. <?php echo number_format($item_total, 2); ?></div>
                                <a href="?remove=<?php echo $cart_item['item_id']; ?>" class="remove-btn">
                                    <i class="fas fa-trash"></i> <?php echo $t['remove']; ?>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($_SESSION['sale_cart'])): ?>
                    <div class="cart-total">
                        <div class="total-row">
                            <span><?php echo $t['subtotal']; ?>:</span>
                            <span class="total-amount">Rs. <?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <form method="POST" action="">
                            <div class="form-group">
                                <label><?php echo $t['customer_name']; ?></label>
                                <input type="text" name="customer_name">
                            </div>
                            
                            <div class="form-group">
                                <label><?php echo $t['customer_phone']; ?></label>
                                <input type="tel" name="customer_phone">
                            </div>
                            
                            <div class="form-group">
                                <label><?php echo $t['payment_method']; ?> *</label>
                                <select name="payment_method" required>
                                    <option value="cash"><?php echo $t['cash']; ?></option>
                                    <option value="card"><?php echo $t['card']; ?></option>
                                    <option value="bank_transfer"><?php echo $t['bank_transfer']; ?></option>
                                    <option value="mobile_payment"><?php echo $t['mobile_payment']; ?></option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><?php echo $t['notes']; ?></label>
                                <textarea name="notes" rows="3"></textarea>
                            </div>
                            
                            <div class="action-buttons">
                                <button type="submit" name="complete_sale" class="btn btn-success">
                                    <i class="fas fa-check-circle"></i> <?php echo $t['complete_sale']; ?>
                                </button>
                                <button type="submit" name="clear_cart" class="btn btn-danger">
                                    <i class="fas fa-times-circle"></i> <?php echo $t['clear_all']; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function updateItemInfo() {
            const select = document.getElementById('item_select');
            const stockInfo = document.getElementById('stock_info');
            const quantityInput = document.getElementById('quantity');
            
            if (select.value) {
                const option = select.options[select.selectedIndex];
                const stock = parseInt(option.getAttribute('data-stock'));
                
                if (stock > 0) {
                    stockInfo.innerHTML = '<i class="fas fa-check-circle"></i> <?php echo $t['available_stock']; ?>: ' + stock;
                    stockInfo.className = 'stock-info';
                    quantityInput.max = stock;
                } else {
                    stockInfo.innerHTML = '<i class="fas fa-times-circle"></i> <?php echo $t['out_of_stock']; ?>';
                    stockInfo.className = 'stock-info out';
                    quantityInput.max = 0;
                    quantityInput.value = 0;
                }
            } else {
                stockInfo.innerHTML = '';
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>