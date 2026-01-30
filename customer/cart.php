<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('/login.php');
}

// Get customer information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Customer';

// Get current language
$lang = $_GET['lang'] ?? 'si';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get cart items with full details
$cart_items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $conn = getDBConnection();
    $item_ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', array_map('intval', $item_ids));
    
    $query = "SELECT i.*, c.category_name_en, c.category_name_si 
              FROM items i 
              LEFT JOIN categories c ON i.category_id = c.category_id 
              WHERE i.item_id IN ($ids_string)";
    
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $item_id = $row['item_id'];
        $quantity = $_SESSION['cart'][$item_id]['quantity'];
        $subtotal = $row['price'] * $quantity;
        
        $cart_items[] = [
            'item' => $row,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
        
        $total += $subtotal;
    }
    
    $conn->close();
}

$cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang == 'si' ? 'සුමින්ද ස්ටෝර්ස් - කරත්තය' : 'Suminda Stores - Cart'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@400;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans Sinhala', 'Roboto', sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: white;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            font-size: 22px;
        }

        .logo-text h1 {
            font-size: 20px;
            font-weight: 700;
        }

        .logo-text p {
            font-size: 11px;
            opacity: 0.9;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .back-btn {
            background: rgba(255,255,255,0.2);
            padding: 10px 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: white;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .user-menu {
            background: rgba(255,255,255,0.2);
            padding: 10px 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        /* Main Content */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .page-header h2 {
            color: #333;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cart-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }

        /* Cart Items */
        .cart-items {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .cart-item {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            gap: 20px;
            position: relative;
        }

        .item-image {
            width: 120px;
            height: 120px;
            border-radius: 10px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }

        .item-image i {
            font-size: 40px;
            color: #ccc;
        }

        .item-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .item-category {
            color: #667eea;
            font-size: 12px;
            font-weight: 600;
        }

        .item-name {
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }

        .item-price {
            color: #666;
            font-size: 16px;
        }

        .item-stock {
            font-size: 13px;
            margin-top: auto;
        }

        .stock-available {
            color: #28a745;
        }

        .stock-low {
            color: #ffc107;
        }

        .stock-unavailable {
            color: #dc3545;
        }

        .item-actions {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
            gap: 10px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f5f7fa;
            border-radius: 8px;
            padding: 5px;
        }

        .qty-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            font-size: 16px;
            transition: all 0.3s;
        }

        .qty-btn:hover {
            background: #667eea;
            color: white;
        }

        .qty-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .quantity {
            min-width: 40px;
            text-align: center;
            font-weight: 600;
            color: #333;
        }

        .item-subtotal {
            color: #667eea;
            font-size: 20px;
            font-weight: 700;
        }

        .remove-btn {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .remove-btn:hover {
            background: #fee;
        }

        /* Cart Summary */
        .cart-summary {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .cart-summary h3 {
            color: #333;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            color: #666;
        }

        .summary-row.total {
            font-size: 22px;
            font-weight: 700;
            color: #333;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }

        .summary-row.total .amount {
            color: #667eea;
        }

        .checkout-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .checkout-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .continue-shopping {
            width: 100%;
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            padding: 12px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            text-align: center;
            text-decoration: none;
            display: block;
            transition: all 0.3s;
        }

        .continue-shopping:hover {
            background: #f5f7fa;
        }

        /* Empty Cart */
        .empty-cart {
            background: white;
            border-radius: 15px;
            padding: 60px 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .empty-cart i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-cart h3 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .empty-cart p {
            color: #666;
            margin-bottom: 30px;
        }

        .shop-now-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .shop-now-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .cart-content {
                grid-template-columns: 1fr;
            }

            .cart-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
            }

            .item-actions {
                flex-direction: row;
                justify-content: space-between;
                width: 100%;
            }

            .item-image {
                width: 100%;
                height: 200px;
            }
        }

        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            min-width: 200px;
            margin-top: 10px;
            display: none;
            overflow: hidden;
            z-index: 1001;
        }

        .dropdown.active .dropdown-menu {
            display: block;
        }

        .dropdown-menu a {
            display: block;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dropdown-menu a:hover {
            background: #f5f7fa;
            color: #667eea;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <a href="index.php" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-store"></i>
                </div>
                <div class="logo-text">
                    <h1><?php echo $lang == 'si' ? 'සුමින්ද ස්ටෝර්ස්' : 'Suminda Stores'; ?></h1>
                    <p><?php echo $lang == 'si' ? 'ඔබේ විශ්වාසනීය සාප්පු මධ්‍යස්ථානය' : 'Your Trusted Shopping Center'; ?></p>
                </div>
            </a>

            <div class="header-actions">
                <a href="index.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    <?php echo $lang == 'si' ? 'ආපසු' : 'Back'; ?>
                </a>

                <div class="dropdown">
                    <div class="user-menu">
                        <i class="fas fa-user"></i>
                        <span><?php echo htmlspecialchars($username); ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 12px;"></i>
                    </div>
                    <div class="dropdown-menu">
                        <a href="profile.php">
                            <i class="fas fa-user-circle"></i>
                            <?php echo $lang == 'si' ? 'මගේ ගිණුම' : 'My Profile'; ?>
                        </a>
                        <a href="orders.php">
                            <i class="fas fa-box"></i>
                            <?php echo $lang == 'si' ? 'මගේ ඇණවුම්' : 'My Orders'; ?>
                        </a>
                        <a href="?lang=<?php echo $lang == 'si' ? 'en' : 'si'; ?>">
                            <i class="fas fa-language"></i>
                            <?php echo $lang == 'si' ? 'English' : 'සිංහල'; ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <?php echo $lang == 'si' ? 'පිටවන්න' : 'Logout'; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="page-header">
            <h2>
                <i class="fas fa-shopping-cart"></i>
                <?php echo $lang == 'si' ? 'මගේ කරත්තය' : 'My Shopping Cart'; ?>
                <?php if ($cart_count > 0): ?>
                    (<?php echo $cart_count; ?> <?php echo $lang == 'si' ? 'භාණ්ඩ' : 'items'; ?>)
                <?php endif; ?>
            </h2>
        </div>

        <?php if (!empty($cart_items)): ?>
            <div class="cart-content">
                <!-- Cart Items -->
                <div class="cart-items">
                    <?php foreach ($cart_items as $cart_item): 
                        $item = $cart_item['item'];
                        $quantity = $cart_item['quantity'];
                        $subtotal = $cart_item['subtotal'];
                    ?>
                        <div class="cart-item" data-item-id="<?php echo $item['item_id']; ?>">
                            <div class="item-image">
                                <?php if ($item['image_path'] && file_exists(__DIR__ . '/../' . $item['image_path'])): ?>
                                    <img src="<?php echo BASE_URL . '/' . htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['item_name_en']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-box"></i>
                                <?php endif; ?>
                            </div>

                            <div class="item-details">
                                <div class="item-category">
                                    <?php echo $lang == 'si' ? htmlspecialchars($item['category_name_si']) : htmlspecialchars($item['category_name_en']); ?>
                                </div>
                                <div class="item-name">
                                    <?php echo $lang == 'si' ? htmlspecialchars($item['item_name_si']) : htmlspecialchars($item['item_name_en']); ?>
                                </div>
                                <div class="item-price">
                                    <?php echo $lang == 'si' ? 'රු. ' : 'LKR '; ?><?php echo number_format($item['price'], 2); ?> <?php echo $lang == 'si' ? 'බැගින්' : 'each'; ?>
                                </div>
                                <div class="item-stock">
                                    <?php if ($item['stock_quantity'] > 0): ?>
                                        <span class="<?php echo $item['stock_quantity'] <= $item['low_stock_threshold'] ? 'stock-low' : 'stock-available'; ?>">
                                            <i class="fas fa-check-circle"></i>
                                            <?php echo $lang == 'si' ? 'තොගයේ ඇත' : 'In Stock'; ?>
                                            (<?php echo $item['stock_quantity']; ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="stock-unavailable">
                                            <i class="fas fa-times-circle"></i>
                                            <?php echo $lang == 'si' ? 'තොගයේ නැත' : 'Out of Stock'; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="item-actions">
                                <div class="quantity-controls">
                                    <button class="qty-btn" onclick="updateQuantity(<?php echo $item['item_id']; ?>, -1)" <?php echo $quantity <= 1 ? 'disabled' : ''; ?>>
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="quantity"><?php echo $quantity; ?></span>
                                    <button class="qty-btn" onclick="updateQuantity(<?php echo $item['item_id']; ?>, 1)" <?php echo $quantity >= $item['stock_quantity'] ? 'disabled' : ''; ?>>
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>

                                <div class="item-subtotal">
                                    <?php echo $lang == 'si' ? 'රු. ' : 'LKR '; ?><?php echo number_format($subtotal, 2); ?>
                                </div>

                                <button class="remove-btn" onclick="removeFromCart(<?php echo $item['item_id']; ?>)" title="<?php echo $lang == 'si' ? 'ඉවත් කරන්න' : 'Remove'; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h3><?php echo $lang == 'si' ? 'ඇණවුම් සාරාංශය' : 'Order Summary'; ?></h3>

                    <div class="summary-row">
                        <span><?php echo $lang == 'si' ? 'උප එකතුව' : 'Subtotal'; ?>:</span>
                        <span><?php echo $lang == 'si' ? 'රු. ' : 'LKR '; ?><?php echo number_format($total, 2); ?></span>
                    </div>

                    <div class="summary-row">
                        <span><?php echo $lang == 'si' ? 'බදු (0%)' : 'Tax (0%)'; ?>:</span>
                        <span><?php echo $lang == 'si' ? 'රු. ' : 'LKR '; ?>0.00</span>
                    </div>

                    <div class="summary-row">
                        <span><?php echo $lang == 'si' ? 'බෙදා හැරීම' : 'Shipping'; ?>:</span>
                        <span><?php echo $lang == 'si' ? 'ගණනය කරනු ලබයි' : 'Calculated'; ?></span>
                    </div>

                    <div class="summary-row total">
                        <span><?php echo $lang == 'si' ? 'මුළු එකතුව' : 'Total'; ?>:</span>
                        <span class="amount"><?php echo $lang == 'si' ? 'රු. ' : 'LKR '; ?><?php echo number_format($total, 2); ?></span>
                    </div>

                    <button class="checkout-btn" onclick="proceedToCheckout()">
                        <i class="fas fa-credit-card"></i>
                        <?php echo $lang == 'si' ? 'ගෙවීමට යන්න' : 'Proceed to Checkout'; ?>
                    </button>

                    <a href="index.php" class="continue-shopping">
                        <?php echo $lang == 'si' ? 'සාප්පු සවාරිය දිගටම කරන්න' : 'Continue Shopping'; ?>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3><?php echo $lang == 'si' ? 'ඔබේ කරත්තය හිස්' : 'Your cart is empty'; ?></h3>
                <p><?php echo $lang == 'si' ? 'සාප්පු සවාරිය ආරම්භ කරන්න!' : 'Start shopping now!'; ?></p>
                <a href="index.php" class="shop-now-btn">
                    <i class="fas fa-shopping-bag"></i>
                    <?php echo $lang == 'si' ? 'සාප්පු සවාරියට යන්න' : 'Start Shopping'; ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Dropdown menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const dropdown = document.querySelector('.dropdown');
            const userMenu = document.querySelector('.user-menu');
            
            if (userMenu) {
                userMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdown.classList.toggle('active');
                });
                
                document.addEventListener('click', function(e) {
                    if (!dropdown.contains(e.target)) {
                        dropdown.classList.remove('active');
                    }
                });
            }
        });

        function updateQuantity(itemId, change) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `item_id=${itemId}&change=${change}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || '<?php echo $lang == 'si' ? 'දෝෂයක් ඇතිවිය' : 'An error occurred'; ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php echo $lang == 'si' ? 'දෝෂයක් ඇතිවිය' : 'An error occurred'; ?>');
            });
        }

        function removeFromCart(itemId) {
            if (confirm('<?php echo $lang == 'si' ? 'මෙම භාණ්ඩය ඉවත් කිරීමට අවශ්‍යද?' : 'Are you sure you want to remove this item?'; ?>')) {
                fetch('remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `item_id=${itemId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || '<?php echo $lang == 'si' ? 'දෝෂයක් ඇතිවිය' : 'An error occurred'; ?>');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?php echo $lang == 'si' ? 'දෝෂයක් ඇතිවිය' : 'An error occurred'; ?>');
                });
            }
        }

        function proceedToCheckout() {
            window.location.href = 'checkout.php';
        }
    </script>
</body>
</html>