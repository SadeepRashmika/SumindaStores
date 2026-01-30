<?php
require_once '../config/config.php';
require_once '../config/database.php';

require_role('seller');

$page_title = "නව භාණ්ඩයක් එක් කරන්න";

// Get categories for dropdown
$categories_query = "SELECT category_id, category_name_si FROM categories ORDER BY category_name_si ASC";
$categories = $conn->query($categories_query);

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_name_si = trim($_POST['item_name_si']);
    $item_name_en = trim($_POST['item_name_en']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $in_stock = isset($_POST['in_stock']) ? 1 : 0; // 1 if checked, 0 if not
    $stock_quantity = $in_stock; // Store 1 or 0
    $low_stock_threshold = 10; // Default value
    $description_si = trim($_POST['description_si']);
    $description_en = trim($_POST['description_en']);
    $added_by = $_SESSION['user_id'];
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $file_type = $_FILES['item_image']['type'];
        $file_size = $_FILES['item_image']['size'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file_type, $allowed_types)) {
            $error_message = "කරුණාකර PNG හෝ JPEG පින්තූරයක් උඩුගත කරන්න";
        } elseif ($file_size > $max_size) {
            $error_message = "පින්තූරය ඉතා විශාලයි. උපරිම ප්‍රමාණය 5MB";
        }
        
        if (empty($error_message)) {
            // Create uploads directory if it doesn't exist
            $upload_dir = '../uploads/items/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($_FILES['item_image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('item_') . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['item_image']['tmp_name'], $upload_path)) {
                $image_path = 'uploads/items/' . $new_filename;
            } else {
                $error_message = "පින්තූරය උඩුගත කිරීමට අසමත් විය";
            }
        }
    }
    
    // Validation
    if (empty($error_message)) {
        if (empty($item_name_si)) {
            $error_message = "භාණ්ඩ නම (සිංහල) අවශ්‍යයි";
        } elseif ($category_id <= 0) {
            $error_message = "වර්ගයක් තෝරන්න";
        } elseif ($price <= 0) {
            $error_message = "වලංගු මිලක් ඇතුළත් කරන්න";
        }
    }
    
    // Insert item if no errors
    if (empty($error_message)) {
        $purchase_price = !empty($_POST['purchase_price']) ? floatval($_POST['purchase_price']) : null;

$stmt = $conn->prepare("INSERT INTO items (item_name_si, item_name_en, category_id, price, purchase_price, stock_quantity, low_stock_threshold, description_si, description_en, image_path, added_by, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())");
$stmt->bind_param("ssiddiisssi", $item_name_si, $item_name_en, $category_id, $price, $purchase_price, $stock_quantity, $low_stock_threshold, $description_si, $description_en, $image_path, $added_by);

        
        if ($stmt->execute()) {
            $success_message = "භාණ්ඩය සාර්ථකව එකතු කරන ලදී!";
            // Clear form
            $_POST = array();
        } else {
            $error_message = "දෝෂයක්: " . $conn->error;
        }
        $stmt->close();
    }
}

include '../includes/header.php';
?>

<style>
    .add-item-container {
        max-width: 800px;
        margin: 30px auto;
        padding: 0 20px;
    }
    
    .page-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .page-header h1 {
        font-size: 36px;
        font-weight: 700;
        color: white;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        margin-bottom: 10px;
    }
    
    .page-header p {
        color: rgba(255,255,255,0.9);
        font-size: 16px;
    }
    
    .form-card {
        background: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 25px;
        font-weight: 500;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-label {
        display: block;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .required {
        color: #dc3545;
        margin-left: 3px;
    }
    
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 15px;
        transition: all 0.3s;
        box-sizing: border-box;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    
    .checkbox-group {
        display: flex;
        align-items: center;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 2px solid #e0e0e0;
        transition: all 0.3s;
        cursor: pointer;
    }
    
    .checkbox-group:hover {
        border-color: #667eea;
        background: #f0f2ff;
    }
    
    .checkbox-group input[type="checkbox"] {
        width: 24px;
        height: 24px;
        margin-right: 12px;
        cursor: pointer;
        accent-color: #667eea;
    }
    
    .checkbox-label {
        font-weight: 600;
        color: #333;
        cursor: pointer;
        user-select: none;
    }
    
    .image-upload-area {
        border: 2px dashed #e0e0e0;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        transition: all 0.3s;
        background: #f8f9fa;
        cursor: pointer;
    }
    
    .image-upload-area:hover {
        border-color: #667eea;
        background: #f0f2ff;
    }
    
    .image-upload-area.dragover {
        border-color: #667eea;
        background: #e8ebff;
    }
    
    .upload-icon {
        font-size: 48px;
        color: #667eea;
        margin-bottom: 15px;
    }
    
    .upload-text {
        color: #666;
        margin-bottom: 10px;
    }
    
    .upload-hint {
        font-size: 12px;
        color: #999;
    }
    
    #item_image {
        display: none;
    }
    
    .image-preview {
        margin-top: 15px;
        display: none;
    }
    
    .image-preview img {
        max-width: 200px;
        max-height: 200px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .remove-image {
        display: inline-block;
        margin-top: 10px;
        color: #dc3545;
        cursor: pointer;
        font-size: 14px;
    }
    
    .remove-image:hover {
        text-decoration: underline;
    }
    
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }
    
    .btn {
        padding: 12px 30px;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        flex: 1;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
        flex: 0 0 150px;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }
    
    .input-icon {
        color: #667eea;
        margin-right: 5px;
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .form-card {
            padding: 25px;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn-secondary {
            flex: 1;
        }
    }
</style>

<div class="add-item-container">
    <div class="page-header">
        <h1><i class="fas fa-plus-circle"></i> නව භාණ්ඩයක් එක් කරන්න</h1>
        <p>ඔබගේ ඉන්වෙන්ටරියට නව භාණ්ඩයක් එක් කිරීමට පහත පෝරමය සම්පූර්ණ කරන්න</p>
    </div>
    
    <div class="form-card">
        <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-box input-icon"></i>
                        භාණ්ඩ නම (සිංහල)<span class="required">*</span>
                    </label>
                    <input type="text" name="item_name_si" class="form-control" 
                           value="<?php echo isset($_POST['item_name_si']) ? htmlspecialchars($_POST['item_name_si']) : ''; ?>" 
                           required placeholder="උදා: හොන්ඩා බයික් පෙට්‍රල් ටැංකිය">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-box input-icon"></i>
                        භාණ්ඩ නම (ඉංග්‍රීසි)
                    </label>
                    <input type="text" name="item_name_en" class="form-control" 
                           value="<?php echo isset($_POST['item_name_en']) ? htmlspecialchars($_POST['item_name_en']) : ''; ?>" 
                           placeholder="e.g.: Honda Bike Petrol Tank">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-tags input-icon"></i>
                    වර්ගය<span class="required">*</span>
                </label>
                <select name="category_id" class="form-control" required>
                    <option value="">වර්ගයක් තෝරන්න</option>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $category['category_id']; ?>" 
                            <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['category_name_si']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-image input-icon"></i>
                    භාණ්ඩ පින්තූරය
                </label>
                <div class="image-upload-area" id="uploadArea">
                    <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                    <div class="upload-text"><strong>පින්තූරයක් උඩුගත කිරීමට ක්ලික් කරන්න</strong></div>
                    <div class="upload-hint">හෝ පින්තූරය මෙතැනට ඇද දමන්න (PNG හෝ JPEG, උපරිම 5MB)</div>
                    <input type="file" id="item_image" name="item_image" accept="image/png, image/jpeg, image/jpg">
                </div>
                <div class="image-preview" id="imagePreview">
                    <img id="previewImg" src="" alt="Preview">
                    <div class="remove-image" id="removeImage">
                        <i class="fas fa-times-circle"></i> පින්තූරය ඉවත් කරන්න
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-rupee-sign input-icon"></i>
                    ඒකක මිල (රු.)<span class="required">*</span>
                </label>
                <input type="number" name="price" class="form-control" 
                       value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>" 
                       step="0.01" min="0" required placeholder="0.00">
            </div>
            <div class="form-group">
    <label class="form-label">
        <i class="fas fa-money-bill-wave input-icon"></i>
        ලබාගත් මිල (රු.)
    </label>
    <input type="number" name="purchase_price" class="form-control" 
           value="<?php echo isset($_POST['purchase_price']) ? $_POST['purchase_price'] : ''; ?>" 
           step="0.01" min="0" placeholder="0.00">
</div>
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-warehouse input-icon"></i>
                    තොග තත්ත්වය
                </label>
                <div class="checkbox-group">
                    <input type="checkbox" id="in_stock" name="in_stock" value="1" 
                           <?php echo (isset($_POST['in_stock']) || !isset($_POST['submit'])) ? 'checked' : ''; ?>>
                    <label for="in_stock" class="checkbox-label">
                        <i class="fas fa-check-circle" style="color: #28a745; margin-right: 5px;"></i>
                        තොගයේ ඇත (In Stock)
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-align-left input-icon"></i>
                    විස්තරය (සිංහල)
                </label>
                <textarea name="description_si" class="form-control" 
                          placeholder="භාණ්ඩය පිළිබඳ අමතර තොරතුරු..."><?php echo isset($_POST['description_si']) ? htmlspecialchars($_POST['description_si']) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-align-left input-icon"></i>
                    විස්තරය (ඉංග්‍රීසි)
                </label>
                <textarea name="description_en" class="form-control" 
                          placeholder="Additional information about the item..."><?php echo isset($_POST['description_en']) ? htmlspecialchars($_POST['description_en']) : ''; ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> භාණ්ඩය එක් කරන්න
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> අවලංගු කරන්න
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('item_image');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const removeImage = document.getElementById('removeImage');
    
    // Click to upload
    uploadArea.addEventListener('click', () => {
        fileInput.click();
    });
    
    // File input change
    fileInput.addEventListener('change', (e) => {
        handleFile(e.target.files[0]);
    });
    
    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const file = e.dataTransfer.files[0];
        if (file && (file.type === 'image/png' || file.type === 'image/jpeg' || file.type === 'image/jpg')) {
            fileInput.files = e.dataTransfer.files;
            handleFile(file);
        }
    });
    
    // Remove image
    removeImage.addEventListener('click', () => {
        fileInput.value = '';
        imagePreview.style.display = 'none';
        uploadArea.style.display = 'block';
    });
    
    function handleFile(file) {
        if (file && (file.type === 'image/png' || file.type === 'image/jpeg' || file.type === 'image/jpg')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImg.src = e.target.result;
                uploadArea.style.display = 'none';
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }
</script>

<?php include '../includes/footer.php'; ?>