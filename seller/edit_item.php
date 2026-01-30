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
    header('Location: edit_item.php' . $redirect_id);
    exit();
}

$lang = $_SESSION['language'];

// Language translations
$translations = [
    'en' => [
        'page_title' => 'Edit Item',
        'dashboard' => 'Dashboard',
        'manage_items' => 'Manage Items',
        'edit_item' => 'Edit Item',
        'back_to_list' => 'Back to List',
        'item_name_en' => 'Item Name (English)',
        'item_name_si' => 'Item Name (Sinhala)',
        'description_en' => 'Description (English)',
        'description_si' => 'Description (Sinhala)',
        'category' => 'Category',
        'select_category' => 'Select Category',
        'price' => 'Price (Rs.)',
        'stock_quantity' => 'Stock Quantity',
        'low_stock_threshold' => 'Low Stock Alert (Threshold)',
        'status' => 'Status',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'current_image' => 'Current Image',
        'change_image' => 'Change Image (Optional)',
        'update_item' => 'Update Item',
        'update_success' => 'Item updated successfully',
        'update_error' => 'Error updating item',
        'item_not_found' => 'Item not found',
        'invalid_item' => 'Invalid item ID',
        'required_fields' => 'Please fill in all required fields',
        'image_upload_error' => 'Error uploading image',
        'language' => 'Language',
        'logout' => 'Logout'
    ],
    'si' => [
        'page_title' => 'භාණ්ඩය සංස්කරණය',
        'dashboard' => 'උපකරණ පුවරුව',
        'manage_items' => 'භාණ්ඩ කළමනාකරණය',
        'edit_item' => 'භාණ්ඩය සංස්කරණය',
        'back_to_list' => 'ලැයිස්තුවට ආපසු',
        'item_name_en' => 'භාණ්ඩ නාමය (ඉංග්‍රීසි)',
        'item_name_si' => 'භාණ්ඩ නාමය (සිංහල)',
        'description_en' => 'විස්තරය (ඉංග්‍රීසි)',
        'description_si' => 'විස්තරය (සිංහල)',
        'category' => 'වර්ගය',
        'select_category' => 'වර්ගය තෝරන්න',
        'price' => 'මිල (රු.)',
        'stock_quantity' => 'තොග ප්‍රමාණය',
        'low_stock_threshold' => 'අඩු තොග අනතුරු ඇඟවීම',
        'status' => 'තත්ත්වය',
        'active' => 'ක්‍රියාත්මක',
        'inactive' => 'අක්‍රිය',
        'current_image' => 'වත්මන් රූපය',
        'change_image' => 'රූපය වෙනස් කරන්න (අනිවාර්ය නොවේ)',
        'update_item' => 'භාණ්ඩය යාවත්කාලීන කරන්න',
        'update_success' => 'භාණ්ඩය සාර්ථකව යාවත්කාලීන කරන ලදී',
        'update_error' => 'භාණ්ඩය යාවත්කාලීන කිරීමේ දෝෂයකි',
        'item_not_found' => 'භාණ්ඩය හමු නොවීය',
        'invalid_item' => 'වලංගු නොවන භාණ්ඩ හැඳුනුම්පත',
        'required_fields' => 'කරුණාකර සියලුම අවශ්‍ය ක්ෂේත්‍ර පුරවන්න',
        'image_upload_error' => 'රූපය උඩුගත කිරීමේ දෝෂයකි',
        'language' => 'භාෂාව',
        'logout' => 'ඉවත් වන්න'
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

// Fetch item details
$query = "SELECT * FROM items WHERE item_id = ?";
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_name_en = clean_input($_POST['item_name_en']);
    $item_name_si = clean_input($_POST['item_name_si']);
    $description_en = clean_input($_POST['description_en']);
    $description_si = clean_input($_POST['description_si']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $low_stock_threshold = intval($_POST['low_stock_threshold']);
    $status = clean_input($_POST['status']);
    
    // Validate required fields
    if (empty($item_name_en) || empty($item_name_si) || $category_id == 0 || $price <= 0) {
        $error = $t['required_fields'];
    } else {
        // Handle image upload
        $image_path = $item['image_path']; // Keep existing image by default
        
        if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['item_image']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                $new_filename = 'item_' . time() . '_' . uniqid() . '.' . $filetype;
                $upload_path = '../uploads/items/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                if (move_uploaded_file($_FILES['item_image']['tmp_name'], $upload_path . $new_filename)) {
                    // Delete old image if exists
                    if (!empty($item['image_path'])) {
                        $old_image = '../' . $item['image_path'];
                        if (file_exists($old_image)) {
                            unlink($old_image);
                        }
                    }
                    $image_path = 'uploads/items/' . $new_filename;
                } else {
                    $error = $t['image_upload_error'];
                }
            }
        }
        
        if (!isset($error)) {
            // Update item
            $update_query = "UPDATE items SET 
                            item_name_en = ?,
                            item_name_si = ?,
                            description_en = ?,
                            description_si = ?,
                            category_id = ?,
                            price = ?,
                            stock_quantity = ?,
                            low_stock_threshold = ?,
                            image_path = ?,
                            status = ?
                            WHERE item_id = ?";
            
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ssssiddissi", 
                $item_name_en, 
                $item_name_si, 
                $description_en, 
                $description_si, 
                $category_id, 
                $price, 
                $stock_quantity, 
                $low_stock_threshold, 
                $image_path, 
                $status,
                $item_id
            );
            
            if ($update_stmt->execute()) {
                $_SESSION['success'] = $t['update_success'];
                header("Location: manage_items.php");
                exit();
            } else {
                $error = $t['update_error'];
            }
        }
    }
}

// Get categories
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
            max-width: 1000px;
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
        
        .btn-secondary {
            background: #718096;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4a5568;
            transform: translateY(-2px);
        }
        
        /* Form Card */
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            font-weight: 600;
            color: #2d3748;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
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
        
        .current-image {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            border: 3px solid #e0e0e0;
        }
        
        .image-placeholder {
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            font-size: 16px;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
            width: 100%;
            justify-content: center;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
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
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .form-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-container">
            <h1><i class="fas fa-edit"></i> <?php echo $t['edit_item']; ?></h1>
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
            <h2><i class="fas fa-box"></i> <?php echo $t['edit_item']; ?></h2>
            <a href="manage_items.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> <?php echo $t['back_to_list']; ?>
            </a>
        </div>

        <!-- Alerts -->
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <div class="form-card">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-text"></i> <?php echo $t['item_name_en']; ?> *</label>
                        <input type="text" name="item_name_en" value="<?php echo htmlspecialchars($item['item_name_en']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-text"></i> <?php echo $t['item_name_si']; ?> *</label>
                        <input type="text" name="item_name_si" value="<?php echo htmlspecialchars($item['item_name_si']); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label><i class="fas fa-align-left"></i> <?php echo $t['description_en']; ?></label>
                        <textarea name="description_en"><?php echo htmlspecialchars($item['description_en']); ?></textarea>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label><i class="fas fa-align-left"></i> <?php echo $t['description_si']; ?></label>
                        <textarea name="description_si"><?php echo htmlspecialchars($item['description_si']); ?></textarea>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-list"></i> <?php echo $t['category']; ?> *</label>
                        <select name="category_id" required>
                            <option value="0"><?php echo $t['select_category']; ?></option>
                            <?php while ($category = $categories_result->fetch_assoc()): ?>
                                <option value="<?php echo $category['category_id']; ?>" 
                                    <?php echo $item['category_id'] == $category['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo $lang == 'si' ? $category['category_name_si'] : $category['category_name_en']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> <?php echo $t['price']; ?> *</label>
                        <input type="number" name="price" step="0.01" min="0" value="<?php echo $item['price']; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-boxes"></i> <?php echo $t['stock_quantity']; ?></label>
                        <input type="number" name="stock_quantity" min="0" value="<?php echo $item['stock_quantity']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-exclamation-triangle"></i> <?php echo $t['low_stock_threshold']; ?></label>
                        <input type="number" name="low_stock_threshold" min="0" value="<?php echo $item['low_stock_threshold']; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-toggle-on"></i> <?php echo $t['status']; ?></label>
                        <select name="status">
                            <option value="active" <?php echo $item['status'] == 'active' ? 'selected' : ''; ?>><?php echo $t['active']; ?></option>
                            <option value="inactive" <?php echo $item['status'] == 'inactive' ? 'selected' : ''; ?>><?php echo $t['inactive']; ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label><i class="fas fa-image"></i> <?php echo $t['current_image']; ?></label>
                        <?php 
                        $image_path = '';
                        if (!empty($item['image_path'])) {
                            if (strpos($item['image_path'], 'uploads/items/') === 0) {
                                $image_path = '../' . $item['image_path'];
                            } else {
                                $image_path = '../uploads/items/' . $item['image_path'];
                            }
                        }
                        ?>
                        
                        <?php if (!empty($image_path) && file_exists($image_path)): ?>
                            <img src="<?php echo $image_path; ?>" alt="Current Image" class="current-image">
                        <?php else: ?>
                            <div class="image-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label><i class="fas fa-upload"></i> <?php echo $t['change_image']; ?></label>
                        <input type="file" name="item_image" accept="image/*">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $t['update_item']; ?>
                </button>
            </form>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>