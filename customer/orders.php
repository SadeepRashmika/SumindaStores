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

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Initialize cart count
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    $cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));
}

// Get orders with items
$conn = getDBConnection();

$query = "SELECT o.*, 
          COUNT(oi.order_item_id) as item_count,
          SUM(oi.quantity) as total_items
          FROM orders o
          LEFT JOIN order_items oi ON o.order_id = oi.order_id
          WHERE o.user_id = ?";

$params = [$user_id];
$types = "i";

if ($status_filter != 'all') {
    $query .= " AND o.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($search)) {
    $query .= " AND o.order_id LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

$query .= " GROUP BY o.order_id ORDER BY o.order_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders_result = $stmt->get_result();
$orders = $orders_result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

// Status translations
$status_labels = [
    'pending' => [
        'en' => 'Pending',
        'si' => 'පොරොත්තු වෙමින්',
        'color' => '#ffc107',
        'icon' => 'clock'
    ],
    'confirmed' => [
        'en' => 'Confirmed',
        'si' => 'තහවුරු කළා',
        'color' => '#17a2b8',
        'icon' => 'check-circle'
    ],
    'processing' => [
        'en' => 'Processing',
        'si' => 'සකස් කරමින්',
        'color' => '#007bff',
        'icon' => 'cog'
    ],
    'shipped' => [
        'en' => 'Shipped',
        'si' => 'යවා ඇත',
        'color' => '#6f42c1',
        'icon' => 'truck'
    ],
    'delivered' => [
        'en' => 'Delivered',
        'si' => 'භාර දුන්නා',
        'color' => '#28a745',
        'icon' => 'check-double'
    ],
    'cancelled' => [
        'en' => 'Cancelled',
        'si' => 'අවලංගු කළා',
        'color' => '#dc3545',
        'icon' => 'times-circle'
    ]
];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang == 'si' ? 'සුමින්ද ස්ටෝර්ස් - මගේ ඇණවුම්' : 'Suminda Stores - My Orders'; ?></title>
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

        .cart-btn {
            background: rgba(255,255,255,0.2);
            padding: 10px 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: white;
            transition: all 0.3s;
            position: relative;
        }

        .cart-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
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
            margin-bottom: 20px;
        }

        /* Filters */
        .filters {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 20px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
            font-weight: 500;
            color: #666;
        }

        .filter-btn:hover {
            border-color: #667eea;
            color: #667eea;
        }

        .filter-btn.active {
            background: #667eea;
            border-color: #667eea;
            color: white;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 10px 40px 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #667eea;
        }

        .search-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        /* Orders List */
        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .order-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: all 0.3s;
        }

        .order-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .order-header {
            background: #f8f9fa;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .order-info {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .order-info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .order-info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }

        .order-info-value {
            font-size: 15px;
            font-weight: 600;
            color: #333;
        }

        .order-status {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
        }

        .order-body {
            padding: 20px;
        }

        .order-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .order-items-info {
            color: #666;
            font-size: 14px;
        }

        .order-total {
            font-size: 22px;
            font-weight: 700;
            color: #667eea;
        }

        .order-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .btn-outline {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-outline:hover {
            background: #f5f7fa;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        /* Empty State */
        .empty-orders {
            background: white;
            border-radius: 15px;
            padding: 60px 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .empty-orders i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-orders h3 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .empty-orders p {
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
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-info {
                width: 100%;
            }

            .order-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .filters {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: 100%;
            }
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 20px;
            color: #333;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            color: #999;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-body {
            padding: 20px;
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
                <a href="cart.php" class="cart-btn">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
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
                <i class="fas fa-box"></i>
                <?php echo $lang == 'si' ? 'මගේ ඇණවුම්' : 'My Orders'; ?>
            </h2>

            <div class="filters">
                <div class="filter-group">
                    <button class="filter-btn <?php echo $status_filter == 'all' ? 'active' : ''; ?>" onclick="filterOrders('all')">
                        <?php echo $lang == 'si' ? 'සියල්ල' : 'All'; ?>
                    </button>
                    <button class="filter-btn <?php echo $status_filter == 'pending' ? 'active' : ''; ?>" onclick="filterOrders('pending')">
                        <?php echo $lang == 'si' ? 'පොරොත්තු වෙමින්' : 'Pending'; ?>
                    </button>
                    <button class="filter-btn <?php echo $status_filter == 'processing' ? 'active' : ''; ?>" onclick="filterOrders('processing')">
                        <?php echo $lang == 'si' ? 'සකස් කරමින්' : 'Processing'; ?>
                    </button>
                    <button class="filter-btn <?php echo $status_filter == 'shipped' ? 'active' : ''; ?>" onclick="filterOrders('shipped')">
                        <?php echo $lang == 'si' ? 'යවා ඇත' : 'Shipped'; ?>
                    </button>
                    <button class="filter-btn <?php echo $status_filter == 'delivered' ? 'active' : ''; ?>" onclick="filterOrders('delivered')">
                        <?php echo $lang == 'si' ? 'භාර දුන්නා' : 'Delivered'; ?>
                    </button>
                </div>

                <div class="search-box">
                    <input type="text" placeholder="<?php echo $lang == 'si' ? 'ඇණවුම් අංකයෙන් සොයන්න...' : 'Search by order ID...'; ?>" value="<?php echo htmlspecialchars($search); ?>" onchange="searchOrders(this.value)">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>

        <?php if (!empty($orders)): ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <div class="order-info-item">
                                    <span class="order-info-label"><?php echo $lang == 'si' ? 'ඇණවුම් අංකය' : 'Order ID'; ?></span>
                                    <span class="order-info-value">#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></span>
                                </div>
                                <div class="order-info-item">
                                    <span class="order-info-label"><?php echo $lang == 'si' ? 'දිනය' : 'Date'; ?></span>
                                    <span class="order-info-value"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></span>
                                </div>
                                <div class="order-info-item">
                                    <span class="order-info-label"><?php echo $lang == 'si' ? 'භාණ්ඩ' : 'Items'; ?></span>
                                    <span class="order-info-value"><?php echo $order['total_items']; ?></span>
                                </div>
                            </div>
                            
                            <?php 
                            $status = $order['status'];
                            $status_info = $status_labels[$status] ?? $status_labels['pending'];
                            ?>
                            <div class="order-status" style="background-color: <?php echo $status_info['color']; ?>20; color: <?php echo $status_info['color']; ?>;">
                                <i class="fas fa-<?php echo $status_info['icon']; ?>"></i>
                                <?php echo $lang == 'si' ? $status_info['si'] : $status_info['en']; ?>
                            </div>
                        </div>

                        <div class="order-body">
                            <div class="order-summary">
                                <div class="order-items-info">
                                    <?php echo $order['item_count']; ?> <?php echo $lang == 'si' ? 'වර්ගයේ භාණ්ඩ' : 'product types'; ?>
                                </div>
                                <div class="order-total">
                                    <?php echo $lang == 'si' ? 'රු. ' : 'LKR '; ?><?php echo number_format($order['total_amount'], 2); ?>
                                </div>
                            </div>

                            <div class="order-actions">
                                <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i>
                                    <?php echo $lang == 'si' ? 'විස්තර බලන්න' : 'View Details'; ?>
                                </a>
                                
                                <?php if ($order['status'] == 'pending'): ?>
                                    <button class="btn btn-danger" onclick="cancelOrder(<?php echo $order['order_id']; ?>)">
                                        <i class="fas fa-times"></i>
                                        <?php echo $lang == 'si' ? 'අවලංගු කරන්න' : 'Cancel Order'; ?>
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($order['status'] == 'delivered'): ?>
                                    <a href="index.php" class="btn btn-outline">
                                        <i class="fas fa-redo"></i>
                                        <?php echo $lang == 'si' ? 'නැවත ඇණවුම් කරන්න' : 'Reorder'; ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-orders">
                <i class="fas fa-box-open"></i>
                <h3><?php echo $lang == 'si' ? 'ඇණවුම් නැත' : 'No Orders Found'; ?></h3>
                <p><?php echo $lang == 'si' ? 'ඔබ තවම ඇණවුම් කර නැත' : 'You haven\'t placed any orders yet'; ?></p>
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

        function filterOrders(status) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('status', status);
            window.location.href = currentUrl.toString();
        }

        function searchOrders(searchTerm) {
            const currentUrl = new URL(window.location.href);
            if (searchTerm) {
                currentUrl.searchParams.set('search', searchTerm);
            } else {
                currentUrl.searchParams.delete('search');
            }
            window.location.href = currentUrl.toString();
        }

        function cancelOrder(orderId) {
            if (confirm('<?php echo $lang == 'si' ? 'මෙම ඇණවුම අවලංගු කිරීමට අවශ්‍යද?' : 'Are you sure you want to cancel this order?'; ?>')) {
                fetch('cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `order_id=${orderId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('<?php echo $lang == 'si' ? 'ඇණවුම අවලංගු කරන ලදී' : 'Order cancelled successfully'; ?>');
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
    </script>
</body>
</html>