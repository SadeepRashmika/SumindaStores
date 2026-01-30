<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";

// Check if user is logged in and is a customer
if (!isLoggedIn()) {
    redirect('/login.php');
}

// Get customer information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Customer';
$user_role = $_SESSION['user_role'] ?? 'customer';

// Get current language (default: Sinhala)
$lang = $_GET['lang'] ?? 'si';

// Get search query and category filter
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Get all categories for filter
$conn = getDBConnection();
$categories_query = "SELECT * FROM categories ORDER BY category_name_en";
$categories_result = $conn->query($categories_query);
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}

// Build items query with filters
$items_query = "SELECT i.*, c.category_name_en, c.category_name_si 
                FROM items i 
                LEFT JOIN categories c ON i.category_id = c.category_id 
                WHERE i.status = 'active'";

if (!empty($search)) {
    $search_safe = $conn->real_escape_string($search);
    $items_query .= " AND (i.item_name_en LIKE '%$search_safe%' OR i.item_name_si LIKE '%$search_safe%')";
}

if (!empty($category_filter)) {
    $category_safe = $conn->real_escape_string($category_filter);
    $items_query .= " AND i.category_id = '$category_safe'";
}

$items_query .= " ORDER BY i.created_at DESC";
$items_result = $conn->query($items_query);
$items = [];
while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
}

// Get cart count (if cart functionality is implemented)
$cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang == 'si' ? 'සුමින්ද ස්ටෝර්ස් - මුල් පිටුව' : 'Suminda Stores - Home'; ?></title>
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

        .header-search {
            flex: 1;
            max-width: 500px;
        }

        .search-form {
            position: relative;
        }

        .search-form input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Noto Sans Sinhala', 'Roboto', sans-serif;
        }

        .search-form button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .cart-btn, .user-menu {
            background: rgba(255,255,255,0.2);
            padding: 10px 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            position: relative;
            transition: all 0.3s;
        }

        .cart-btn:hover, .user-menu:hover {
            background: rgba(255,255,255,0.3);
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
        }

        /* Main Content */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        /* Welcome Section */
        .welcome-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .welcome-section h2 {
            color: #333;
            margin-bottom: 5px;
            font-size: 24px;
        }

        .welcome-section p {
            color: #666;
            font-size: 14px;
        }

        /* Category Filter */
        .category-filter {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .category-filter h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .category-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .category-chip {
            padding: 8px 16px;
            border-radius: 20px;
            border: 2px solid #e0e0e0;
            background: white;
            color: #333;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }

        .category-chip:hover, .category-chip.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        /* Products Grid */
        .products-section h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }

        .product-image {
            width: 100%;
            height: 200px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: #ccc;
            position: relative;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .in-stock {
            background: #d4edda;
            color: #155724;
        }

        .low-stock {
            background: #fff3cd;
            color: #856404;
        }

        .out-of-stock {
            background: #f8d7da;
            color: #721c24;
        }

        .product-info {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-category {
            color: #667eea;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .product-name {
            color: #333;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .product-description {
            color: #666;
            font-size: 13px;
            margin-bottom: 15px;
            flex: 1;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        .product-price {
            color: #667eea;
            font-size: 22px;
            font-weight: 700;
        }

        .add-to-cart-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .add-to-cart-btn:hover {
            background: #5568d3;
            transform: scale(1.05);
        }

        .add-to-cart-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: scale(1);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
        }

        .empty-state i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #666;
        }

        /* Dropdown Menu */
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

        @media (max-width: 768px) {
            .header-content {
                flex-wrap: wrap;
            }

            .header-search {
                order: 3;
                max-width: 100%;
                width: 100%;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }

            .logo-text p {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-store"></i>
                </div>
                <div class="logo-text">
                    <h1><?php echo $lang == 'si' ? 'සුමින්ද ස්ටෝර්ස්' : 'Suminda Stores'; ?></h1>
                    <p><?php echo $lang == 'si' ? 'ඔබේ විශ්වාසනීය සාප්පු මධ්‍යස්ථානය' : 'Your Trusted Shopping Center'; ?></p>
                </div>
            </div>

            <div class="header-search">
                <form class="search-form" method="GET" action="">
                    <input type="text" name="search" placeholder="<?php echo $lang == 'si' ? 'භාණ්ඩ සොයන්න...' : 'Search products...'; ?>" value="<?php echo htmlspecialchars($search); ?>">
                    <?php if ($category_filter): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_filter); ?>">
                    <?php endif; ?>
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="header-actions">
                <a href="cart.php" class="cart-btn" style="text-decoration: none; color: white;">
                    <i class="fas fa-shopping-cart"></i>
                    <?php echo $lang == 'si' ? 'කරත්තය' : 'Cart'; ?>
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
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h2><?php echo $lang == 'si' ? 'ආයුබෝවන්, ' . htmlspecialchars($username) . '!' : 'Welcome, ' . htmlspecialchars($username) . '!'; ?></h2>
            <p><?php echo $lang == 'si' ? 'අපගේ නවතම භාණ්ඩ එකතුව බලන්න' : 'Browse our latest collection of products'; ?></p>
        </div>

        <!-- Category Filter -->
        <div class="category-filter">
            <h3>
                <i class="fas fa-filter"></i>
                <?php echo $lang == 'si' ? 'වර්ග අනුව පෙරන්න' : 'Filter by Category'; ?>
            </h3>
            <div class="category-chips">
                <a href="?" class="category-chip <?php echo empty($category_filter) ? 'active' : ''; ?>">
                    <?php echo $lang == 'si' ? 'සියල්ල' : 'All'; ?>
                </a>
                <?php foreach ($categories as $category): ?>
                    <a href="?category=<?php echo $category['category_id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                       class="category-chip <?php echo $category_filter == $category['category_id'] ? 'active' : ''; ?>">
                        <?php echo $lang == 'si' ? htmlspecialchars($category['category_name_si']) : htmlspecialchars($category['category_name_en']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Products Section -->
        <div class="products-section">
            <h3>
                <i class="fas fa-shopping-bag"></i>
                <?php echo $lang == 'si' ? 'භාණ්ඩ' : 'Products'; ?>
                <?php if ($search): ?>
                    - <?php echo $lang == 'si' ? 'සෙවුම්: ' : 'Search: '; ?>"<?php echo htmlspecialchars($search); ?>"
                <?php endif; ?>
            </h3>

            <?php if (count($items) > 0): ?>
                <div class="products-grid">
                    <?php foreach ($items as $item): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if ($item['image_path'] && file_exists(__DIR__ . '/../' . $item['image_path'])): ?>
                                    <img src="<?php echo BASE_URL . '/' . htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['item_name_en']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-box"></i>
                                <?php endif; ?>
                                
                                <?php if ($item['stock_quantity'] <= 0): ?>
                                    <span class="stock-badge out-of-stock">
                                        <?php echo $lang == 'si' ? 'තොගයේ නැත' : 'Out of Stock'; ?>
                                    </span>
                                <?php elseif ($item['stock_quantity'] <= $item['low_stock_threshold']): ?>
                                    <span class="stock-badge low-stock">
                                        <?php echo $lang == 'si' ? 'අඩු තොගයක්' : 'Low Stock'; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="stock-badge in-stock">
                                        <?php echo $lang == 'si' ? 'තොගයේ ඇත' : 'In Stock'; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-info">
                                <div class="product-category">
                                    <?php echo $lang == 'si' ? htmlspecialchars($item['category_name_si']) : htmlspecialchars($item['category_name_en']); ?>
                                </div>
                                <div class="product-name">
                                    <?php echo $lang == 'si' ? htmlspecialchars($item['item_name_si']) : htmlspecialchars($item['item_name_en']); ?>
                                </div>
                                <div class="product-description">
                                    <?php 
                                    $description = $lang == 'si' ? $item['description_si'] : $item['description_en'];
                                    echo $description ? htmlspecialchars(substr($description, 0, 80)) . '...' : '';
                                    ?>
                                </div>
                                
                                <div class="product-footer">
                                    <div class="product-price">
                                        <?php echo $lang == 'si' ? 'රු. ' : 'LKR '; ?><?php echo number_format($item['price'], 2); ?>
                                    </div>
                                    <button class="add-to-cart-btn" 
                                            onclick="addToCart(<?php echo $item['item_id']; ?>)"
                                            <?php echo $item['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                                        <i class="fas fa-cart-plus"></i>
                                        <?php echo $lang == 'si' ? 'එකතු කරන්න' : 'Add'; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h3><?php echo $lang == 'si' ? 'භාණ්ඩ හමු නොවීය' : 'No Products Found'; ?></h3>
                    <p><?php echo $lang == 'si' ? 'කරුණාකර වෙනත් සෙවුමක් උත්සාහ කරන්න' : 'Please try a different search'; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Dropdown menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const dropdown = document.querySelector('.dropdown');
            const userMenu = document.querySelector('.user-menu');
            
            userMenu.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.classList.toggle('active');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('active');
                }
            });
        });

        function addToCart(itemId) {
            // AJAX call to add item to cart
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'item_id=' + itemId + '&quantity=1'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('<?php echo $lang == 'si' ? 'භාණ්ඩය කරත්තයට එකතු කරන ලදී!' : 'Item added to cart!'; ?>');
                    // Update cart count
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
    </script>
</body>
</html>