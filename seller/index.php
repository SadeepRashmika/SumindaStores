<?php
require_once '../config/config.php';
require_once '../config/database.php';

require_role('seller');

$page_title = "විකුණුම්කරු පැනලය";

// Get statistics
$total_items_query = "SELECT COUNT(*) as count FROM items WHERE status = 'active'";
$total_items = $conn->query($total_items_query)->fetch_assoc()['count'];

$low_stock_query = "SELECT COUNT(*) as count FROM items WHERE stock_quantity <= low_stock_threshold AND status = 'active'";
$low_stock_count = $conn->query($low_stock_query)->fetch_assoc()['count'];

$today_sales_query = "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total FROM sales WHERE sale_date = CURDATE()";
$today_sales = $conn->query($today_sales_query)->fetch_assoc();

$month_sales_query = "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total FROM sales WHERE MONTH(sale_date) = MONTH(CURDATE()) AND YEAR(sale_date) = YEAR(CURDATE())";
$month_sales = $conn->query($month_sales_query)->fetch_assoc();

// Get low stock items
$low_stock_items_query = "SELECT i.*, c.category_name_si FROM items i LEFT JOIN categories c ON i.category_id = c.category_id WHERE i.stock_quantity <= i.low_stock_threshold AND i.status = 'active' ORDER BY i.stock_quantity ASC LIMIT 5";
$low_stock_items = $conn->query($low_stock_items_query);

// Get recent sales
$recent_sales_query = "SELECT s.*, i.item_name_si FROM sales s LEFT JOIN items i ON s.item_id = i.item_id ORDER BY s.created_at DESC LIMIT 5";
$recent_sales = $conn->query($recent_sales_query);

include '../includes/header.php';

?>

<style>
    .dashboard {
        max-width: 1200px;
        margin: 30px auto;
        padding: 0 20px;
    }
    
    .dashboard-title {
        font-size: 36px;
        font-weight: 700;
        color: white;
        margin-bottom: 30px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }
    
    .stat-card {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .stat-icon {
        font-size: 40px;
        margin-bottom: 15px;
    }
    
    .stat-card.blue .stat-icon { color: #667eea; }
    .stat-card.orange .stat-icon { color: #f093fb; }
    .stat-card.green .stat-icon { color: #4facfe; }
    .stat-card.red .stat-icon { color: #fa709a; }
    
    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #333;
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: #666;
        font-size: 14px;
    }
    
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 40px;
    }
    
    .action-btn {
        background: white;
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        text-decoration: none;
        color: #333;
        font-weight: 600;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s;
    }
    
    .action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }
    
    .action-btn i {
        font-size: 30px;
        display: block;
        margin-bottom: 10px;
        color: #667eea;
    }
    
    .data-table {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    
    .table-title {
        font-size: 24px;
        font-weight: 700;
        color: #333;
        margin-bottom: 20px;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        text-align: left;
        font-weight: 600;
    }
    
    td {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    tr:hover {
        background: #f8f9fa;
    }
</style>

<div class="dashboard">
    <h1 class="dashboard-title"><i class="fas fa-tachometer-alt"></i> විකුණුම්කරු පැනලය</h1>
    
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-icon"><i class="fas fa-boxes"></i></div>
            <div class="stat-value"><?php echo $total_items; ?></div>
            <div class="stat-label">මුළු භාණ්ඩ</div>
        </div>
        
        <div class="stat-card red">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-value"><?php echo $low_stock_count; ?></div>
            <div class="stat-label">අඩු තොග අනතුරු ඇඟවීම්</div>
        </div>
        
        <div class="stat-card green">
            <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-value"><?php echo $today_sales['count']; ?></div>
            <div class="stat-label">අද විකුණුම්</div>
        </div>
        
        <div class="stat-card orange">
            <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
            <div class="stat-value">රු. <?php echo number_format($today_sales['total'], 2); ?></div>
            <div class="stat-label">අද ආදායම</div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="add_item.php" class="action-btn">
            <i class="fas fa-plus-circle"></i>
            නව භාණ්ඩයක් එක් කරන්න
        </a>
        <a href="manage_items.php" class="action-btn">
            <i class="fas fa-edit"></i>
            භාණ්ඩ කළමනාකරණය
        </a>
        <a href="update_stock.php" class="action-btn">
            <i class="fas fa-warehouse"></i>
            තොග යාවත්කාලීන කරන්න
        </a>
    </div>
    
    <!-- Low Stock Items -->
    <?php if ($low_stock_items->num_rows > 0): ?>
    <div class="data-table">
        <h3 class="table-title"><i class="fas fa-exclamation-triangle"></i> අඩු තොග භාණ්ඩ</h3>
        <table>
            <thead>
                <tr>
                    <th>භාණ්ඩය</th>
                    <th>වර්ගය</th>
                    <th>තොගය</th>
                    <th>අඩු තොග සීමාව</th>
                    <th>ක්‍රියාමාර්ග</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $low_stock_items->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo $item['item_name_si']; ?></strong></td>
                    <td><?php echo $item['category_name_si']; ?></td>
                    <td><span class="stock-badge out-of-stock"><?php echo $item['stock_quantity']; ?></span></td>
                    <td><?php echo $item['low_stock_threshold']; ?></td>
                    <td>
                        <a href="update_stock.php?id=<?php echo $item['item_id']; ?>" class="btn btn-primary" style="padding: 8px 15px; font-size: 14px;">
                            <i class="fas fa-plus"></i> තොගය එක් කරන්න
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <!-- Recent Sales -->
    <?php if ($recent_sales->num_rows > 0): ?>
    <div class="data-table">
        <h3 class="table-title"><i class="fas fa-clock"></i> මෑත විකුණුම්</h3>
        <table>
            <thead>
                <tr>
                    <th>භාණ්ඩය</th>
                    <th>ප්‍රමාණය</th>
                    <th>මුදල</th>
                    <th>පාරිභෝගිකයා</th>
                    <th>දිනය</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($sale = $recent_sales->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $sale['item_name_si']; ?></td>
                    <td><?php echo $sale['quantity_sold']; ?></td>
                    <td><strong>රු. <?php echo number_format($sale['total_amount'], 2); ?></strong></td>
                    <td><?php echo $sale['customer_name'] ?: 'N/A'; ?></td>
                    <td><?php echo date('Y-m-d', strtotime($sale['sale_date'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>