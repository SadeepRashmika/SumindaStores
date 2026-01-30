<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$order_id = intval($_POST['order_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

$conn = getDBConnection();

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Verify order belongs to user and is cancellable
    $stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Order not found');
    }
    
    $order = $result->fetch_assoc();
    $stmt->close();
    
    // Check if order can be cancelled
    if (!in_array($order['status'], ['pending', 'confirmed'])) {
        throw new Exception('This order cannot be cancelled');
    }
    
    // Get order items to restore stock
    $stmt = $conn->prepare("SELECT item_id, quantity FROM order_items WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items_result = $stmt->get_result();
    $stmt->close();
    
    // Restore stock for each item
    while ($item = $items_result->fetch_assoc()) {
        $stmt = $conn->prepare("UPDATE items SET stock_quantity = stock_quantity + ? WHERE item_id = ?");
        $stmt->bind_param("ii", $item['quantity'], $item['item_id']);
        $stmt->execute();
        $stmt->close();
    }
    
    // Update order status to cancelled
    $cancelled_date = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled', cancelled_date = ?, updated_at = NOW() WHERE order_id = ?");
    $stmt->bind_param("si", $cancelled_date, $order_id);
    $stmt->execute();
    $stmt->close();
    
    // Log status change in history
    $stmt = $conn->prepare("INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, notes) VALUES (?, ?, 'cancelled', ?, 'Cancelled by customer')");
    $stmt->bind_param("isi", $order_id, $order['status'], $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>