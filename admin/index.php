<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";

// Check if user is logged in and is an admin
if (!isLoggedIn()) {
    redirect('/login.php');
}

if ($_SESSION['user_role'] !== 'admin') {
    redirect('/customer/index.php');
}

// Get admin information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Admin';

// Get current language
$lang = $_GET['lang'] ?? 'si';

// Get dashboard statistics
$conn = getDBConnection();

// Total counts
$stats = [
    'total_users' => 0,
    'total_orders' => 0,
    'total_products' => 0,
    'total_categories' => 0,
    'pending_orders' => 0,
    'low_stock_items' => 0,
    'total_revenue' => 0,
    'today_orders' => 0
];

// Get total users
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
$stats['total_users'] = $result->fetch_assoc()['count'];

// Get total orders
$result = $conn->query("SELECT COUNT(*) as count, SUM(total_amount) as revenue FROM orders");
$row = $result->fetch_assoc();
$stats['total_orders'] = $row['count'];
$stats['total_revenue'] = $row['revenue'] ?? 0;

// Get total products
$result = $conn->query("SELECT COUNT(*) as count FROM items WHERE status = 'active'");
$stats['total_products'] = $result->fetch_assoc()['count'];

// Get total categories
$result = $conn->query("SELECT COUNT(*) as count FROM categories");
$stats['total_categories'] = $result->fetch_assoc()['count'];

// Get pending orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status IN ('pending', 'confirmed')");
$stats['pending_orders'] = $result->fetch_assoc()['count'];

// Get low stock items
$result = $conn->query("SELECT COUNT(*) as count FROM items WHERE stock_quantity <= low_stock_threshold AND status = 'active'");
$stats['low_stock_items'] = $result->fetch_assoc()['count'];

// Get today's orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) = CURDATE()");
$stats['today_orders'] = $result->fetch_assoc()['count'];

// Get recent orders
$recent_orders_query = "SELECT o.*, u.username, u.full_name 
                        FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.user_id 
                        ORDER BY o.order_date DESC 
                        LIMIT 5";
$recent_orders = $conn->query($recent_orders_query)->fetch_all(MYSQLI_ASSOC);

// Get low stock items
$low_stock_query = "SELECT i.*, c.category_name_en, c.category_name_si 
                    FROM items i 
                    LEFT JOIN categories c ON i.category_id = c.category_id 
                    WHERE i.stock_quantity <= i.low_stock_threshold 
                    AND i.status = 'active' 
                    ORDER BY i.stock_quantity ASC 
                    LIMIT 5";
$low_stock_items = $conn->query($low_stock_query)->fetch_all(MYSQLI_ASSOC);

$conn->close();

// Status translations
$status_labels = [
    'pending' => ['en' => 'Pending', 'si' => 'පොරොත්තු වෙමින්', 'color' => '#ffc107'],
    'confirmed' => ['en' => 'Confirmed', 'si' => 'තහවුරු කළ', 'color' => '#17a2b8'],
    'processing' => ['en' => 'Processing', 'si' => 'සකස් කරමින්', 'color' => '#007bff'],
    'shipped' => ['en' => 'Shipped', 'si' => 'යවා ඇත', 'color' => '#6f42c1'],
    'delivered' => ['en' => 'Delivered', 'si' => 'භාර දුන්නා', 'color' => '#28a745'],
    'cancelled' => ['en' => 'Cancelled', 'si' => 'අවලංගු කළ', 'color' => '#dc3545']
];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang == 'si' ? 'පරිපාලක පැනලය - සුමින්ද ස්ටෝර්ස්' : 'Admin Dashboard - Suminda Stores'; ?></title>
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
            max-width: 1600px;
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

        .user-menu {
            background: rgba(255,255,255,0.2);
            padding: 10px 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            position: relative;
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

        /* Container */
        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h2 {
            color: #333;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .page-header p {
            color: #666;
            font-size: 14px;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .action-btn {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-decoration: none;
            color: #333;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .action-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: white;
        }

        .action-info h3 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .action-info p {
            font-size: 12px;
            color: #666;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
        }

        .stat-change {
            font-size: 12px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #f0f0f0;
        }

        .stat-change.positive {
            color: #28a745;
        }

        .stat-change.negative {
            color: #dc3545;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        .content-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i {
            color: #667eea;
        }

        .view-all {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        /* Orders List */
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            background: #f8f9fa;
            transition: all 0.3s;
        }

        .order-item:hover {
            background: #e9ecef;
        }

        .order-info {
            flex: 1;
        }

        .order-id {
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
        }

        .order-customer {
            font-size: 13px;
            color: #666;
        }

        .order-status {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 10px;
        }

        .order-amount {
            font-weight: 700;
            color: #667eea;
        }

        /* Stock Items */
        .stock-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }

        .stock-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffc107;
            font-size: 20px;
        }

        .stock-info {
            flex: 1;
        }

        .stock-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
            font-size: 14px;
        }

        .stock-category {
            font-size: 12px;
            color: #666;
        }

        .stock-quantity {
            font-weight: 700;
            color: #dc3545;
            font-size: 16px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }
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
                    <p><?php echo $lang == 'si' ? 'පරිපාලක පැනලය' : 'Admin Dashboard'; ?></p>
                </div>
            </a>

            <div class="header-actions">
                <div class="dropdown">
                    <div class="user-menu">
                        <i class="fas fa-user-shield"></i>
                        <span><?php echo htmlspecialchars($username); ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 12px;"></i>
                    </div>
                    <div class="dropdown-menu">
                        <a href="profile.php">
                            <i class="fas fa-user-circle"></i>
                            <?php echo $lang == 'si' ? 'මගේ ගිණුම' : 'My Profile'; ?>
                        </a>
                        <a href="settings.php">
                            <i class="fas fa-cog"></i>
                            <?php echo $lang == 'si' ? 'සැකසීම්' : 'Settings'; ?>
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
            <h2><?php echo $lang == 'si' ? 'ආයුබෝවන්, ' . htmlspecialchars($username) . '!' : 'Welcome, ' . htmlspecialchars($username) . '!'; ?></h2>
            <p><?php echo $lang == 'si' ? 'ඔබගේ වෙළඳසැල් කළමනාකරණය' : 'Manage your store'; ?></p>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="products.php" class="action-btn">
                <div class="action-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-box"></i>
                </div>
                <div class="action-info">
                    <h3><?php echo $lang == 'si' ? 'භාණ්ඩ' : 'Products'; ?></h3>
                    <p><?php echo $lang == 'si' ? 'කළමනාකරණය කරන්න' : 'Manage'; ?></p>
                </div>
            </a>

            <a href="orders.php" class="action-btn">
                <div class="action-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="action-info">
                    <h3><?php echo $lang == 'si' ? 'ඇණවුම්' : 'Orders'; ?></h3>
                    <p><?php echo $lang == 'si' ? 'බලන්න' : 'View'; ?></p>
                </div>
            </a>

            <a href="categories.php" class="action-btn">
                <div class="action-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="action-info">
                    <h3><?php echo $lang == 'si' ? 'වර්ග' : 'Categories'; ?></h3>
                    <p><?php echo $lang == 'si' ? 'කළමනාකරණය කරන්න' : 'Manage'; ?></p>
                </div>
            </a>

            <a href="customers.php" class="action-btn">
                <div class="action-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="action-info">
                    <h3><?php echo $lang == 'si' ? 'ගනුදෙනුකරුවන්' : 'Customers'; ?></h3>
                    <p><?php echo $lang == 'si' ? 'බලන්න' : 'View'; ?></p>
                </div>
            </a>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo number_format($stats['total_orders']); ?></div>
                        <div class="stat-label"><?php echo $lang == 'si' ? 'මුළු ඇණවුම්' : 'Total Orders'; ?></div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> <?php echo $stats['today_orders']; ?> <?php echo $lang == 'si' ? 'අද' : 'today'; ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $lang == 'si' ? 'රු. ' : 'LKR '; ?><?php echo number_format($stats['total_revenue'], 2); ?></div>
                        <div class="stat-label"><?php echo $lang == 'si' ? 'මුළු ආදායම' : 'Total Revenue'; ?></div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo number_format($stats['total_products']); ?></div>
                        <div class="stat-label"><?php echo $lang == 'si' ? 'භාණ්ඩ' : 'Products'; ?></div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
                <div class="stat-change <?php echo $stats['low_stock_items'] > 0 ? 'negative' : ''; ?>">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $stats['low_stock_items']; ?> <?php echo $lang == 'si' ? 'අඩු තොගයක්' : 'low stock'; ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                        <div class="stat-label"><?php echo $lang == 'si' ? 'ගනුදෙනුකරුවන්' : 'Customers'; ?></div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Recent Orders -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i>
                        <?php echo $lang == 'si' ? 'මෑත ඇණවුම්' : 'Recent Orders'; ?>
                    </h3>
                    <a href="orders.php" class="view-all">
                        <?php echo $lang == 'si' ? 'සියල්ල බලන්න' : 'View All'; ?> <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <?php if (!empty($recent_orders)): ?>
                    <?php foreach ($recent_orders as $order): ?>
                        <?php 
                        $status = $order['status'];
                        $status_info = $status_labels[$status] ?? $status_labels['pending'];
                        ?>
                        <div class="order-item">
                            <div class="order-info">
                                <div class="order-id">#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></div>
                                <div class="order-customer"><?php echo htmlspecialchars($order['full_name']); ?></div>
                            </div>
                            <span class="order-status" style="background-color: <?php echo $status_info['color']; ?>20; color: <?php echo $status_info['color']; ?>;">
                                <?php echo $lang == 'si' ? $status_info['si'] : $status_info['en']; ?>
                            </span>
                            <div class="order-amount">
                                <?php echo $lang == 'si' ? 'රු. ' : 'LKR '; ?><?php echo number_format($order['total_amount'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p><?php echo $lang == 'si' ? 'ඇණවුම් නොමැත' : 'No recent orders'; ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Low Stock Items -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo $lang == 'si' ? 'අඩු තොගය' : 'Low Stock'; ?>
                    </h3>
                    <a href="products.php?filter=low_stock" class="view-all">
                        <?php echo $lang == 'si' ? 'සියල්ල බලන්න' : 'View All'; ?> <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <?php if (!empty($low_stock_items)): ?>
                    <?php foreach ($low_stock_items as $item): ?>
                        <div class="stock-item">
                            <div class="stock-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="stock-info">
                                <div class="stock-name">
                                    <?php echo $lang == 'si' ? htmlspecialchars($item['item_name_si']) : htmlspecialchars($item['item_name_en']); ?>
                                </div>
                                <div class="stock-category">
                                    <?php echo $lang == 'si' ? htmlspecialchars($item['category_name_si']) : htmlspecialchars($item['category_name_en']); ?>
                                </div>
                            </div>
                            <div class="stock-quantity">
                                <?php echo $item['stock_quantity']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <p><?php echo $lang == 'si' ? 'සියලු භාණ්ඩ තොගයේ තිබේ' : 'All items in stock'; ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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
    </script>
</body>
</html>