<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('/login.php');
}

$user_id = $_SESSION['user_id'];
$lang = $_GET['lang'] ?? 'si';

$error = '';
$success = '';

// Get user information
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    redirect('/login.php');
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    // Validation
    if (empty($full_name) || empty($email)) {
        $error = $lang == 'si' ? 'සම්පූර්ණ නම සහ විද්‍යුත් තැපැල අත්‍යවශ්‍යයි' : 'Full name and email are required';
    } else {
        // Check if email is already used by another user
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = $lang == 'si' ? 'මෙම විද්‍යුත් තැපැල දැනටමත් භාවිතා වේ' : 'This email is already in use';
        } else {
            // Update profile
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE user_id = ?");
            $stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);
            
            if ($stmt->execute()) {
                $success = $lang == 'si' ? 'ගිණුම සාර්ථකව යාවත්කාලීන කරන ලදී' : 'Profile updated successfully';
                // Refresh user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
            } else {
                $error = $lang == 'si' ? 'යාවත්කාලීන කිරීමේ දෝෂයක්' : 'Error updating profile';
            }
        }
        $stmt->close();
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = $lang == 'si' ? 'සියලුම මුරපද ක්ෂේත්‍ර අත්‍යවශ්‍යයි' : 'All password fields are required';
    } elseif ($new_password !== $confirm_password) {
        $error = $lang == 'si' ? 'නව මුරපද ගැලපෙන්නේ නැත' : 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = $lang == 'si' ? 'මුරපදය අවම වශයෙන් අක්ෂර 6ක් විය යුතුය' : 'Password must be at least 6 characters';
    } else {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success = $lang == 'si' ? 'මුරපදය සාර්ථකව වෙනස් කරන ලදී' : 'Password changed successfully';
            } else {
                $error = $lang == 'si' ? 'මුරපදය වෙනස් කිරීමේ දෝෂයක්' : 'Error changing password';
            }
            $stmt->close();
        } else {
            $error = $lang == 'si' ? 'වත්මන් මුරපදය වැරදිය' : 'Current password is incorrect';
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang == 'si' ? 'මගේ ගිණුම - සුමින්ද ස්ටෝර්ස්' : 'My Profile - Suminda Stores'; ?></title>
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
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .back-btn {
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
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

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-title {
            color: #333;
            font-size: 28px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-title i {
            color: #667eea;
        }

        /* Profile Grid */
        .profile-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }

        /* Sidebar */
        .profile-sidebar {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            height: fit-content;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            margin: 0 auto 20px;
        }

        .profile-name {
            text-align: center;
            font-size: 22px;
            color: #333;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .profile-role {
            text-align: center;
            color: #667eea;
            font-size: 14px;
            margin-bottom: 20px;
            padding: 5px 15px;
            background: #f0f3ff;
            border-radius: 20px;
            display: inline-block;
            width: 100%;
        }

        .profile-info {
            border-top: 1px solid #e0e0e0;
            padding-top: 20px;
            margin-top: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
            color: #666;
            font-size: 14px;
        }

        .info-item i {
            width: 20px;
            color: #667eea;
        }

        /* Main Content */
        .profile-main {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .card-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .card-title i {
            color: #667eea;
        }

        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group label i {
            margin-right: 8px;
            color: #667eea;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: 'Noto Sans Sinhala', 'Roboto', sans-serif;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group input:disabled {
            background: #f5f5f5;
            cursor: not-allowed;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            font-family: 'Noto Sans Sinhala', 'Roboto', sans-serif;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert i {
            font-size: 18px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px;
            border-radius: 15px;
            color: white;
            text-align: center;
        }

        .stat-card i {
            font-size: 36px;
            margin-bottom: 10px;
            opacity: 0.9;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        @media (max-width: 992px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
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
                </div>
            </a>

            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                <?php echo $lang == 'si' ? 'ආපසු යන්න' : 'Back to Home'; ?>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <h1 class="page-title">
            <i class="fas fa-user-circle"></i>
            <?php echo $lang == 'si' ? 'මගේ ගිණුම' : 'My Profile'; ?>
        </h1>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="profile-grid">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                <div class="profile-role">
                    <?php 
                    $role_display = [
                        'customer' => $lang == 'si' ? 'ගනුදෙනුකරු' : 'Customer',
                        'seller' => $lang == 'si' ? 'විකුණුම්කරු' : 'Seller',
                        'admin' => $lang == 'si' ? 'පරිපාලක' : 'Administrator'
                    ];
                    echo $role_display[$user['role']] ?? $user['role'];
                    ?>
                </div>

                <div class="profile-info">
                    <div class="info-item">
                        <i class="fas fa-user"></i>
                        <span><?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <?php if ($user['phone']): ?>
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <span><?php echo htmlspecialchars($user['phone']); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo $lang == 'si' ? 'සම්බන්ධ වූ දිනය: ' : 'Joined: '; ?><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="profile-main">
                <!-- Statistics (Optional) -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-shopping-bag"></i>
                        <div class="stat-value">0</div>
                        <div class="stat-label"><?php echo $lang == 'si' ? 'මුළු ඇණවුම්' : 'Total Orders'; ?></div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-clock"></i>
                        <div class="stat-value">0</div>
                        <div class="stat-label"><?php echo $lang == 'si' ? 'අපේක්ෂිත' : 'Pending'; ?></div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-check-circle"></i>
                        <div class="stat-value">0</div>
                        <div class="stat-label"><?php echo $lang == 'si' ? 'සම්පූර්ණ කළ' : 'Completed'; ?></div>
                    </div>
                </div>

                <!-- Edit Profile Form -->
                <div class="profile-card">
                    <h2 class="card-title">
                        <i class="fas fa-edit"></i>
                        <?php echo $lang == 'si' ? 'ගිණුම් තොරතුරු යාවත්කාලීන කරන්න' : 'Update Profile Information'; ?>
                    </h2>

                    <form method="POST" action="">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>
                                    <i class="fas fa-user"></i>
                                    <?php echo $lang == 'si' ? 'පරිශීලක නාමය' : 'Username'; ?>
                                </label>
                                <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            </div>

                            <div class="form-group">
                                <label>
                                    <i class="fas fa-shield-alt"></i>
                                    <?php echo $lang == 'si' ? 'භූමිකාව' : 'Role'; ?>
                                </label>
                                <input type="text" value="<?php echo $role_display[$user['role']] ?? $user['role']; ?>" disabled>
                            </div>

                            <div class="form-group full-width">
                                <label>
                                    <i class="fas fa-id-card"></i>
                                    <?php echo $lang == 'si' ? 'සම්පූර්ණ නම' : 'Full Name'; ?> *
                                </label>
                                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>
                                    <i class="fas fa-envelope"></i>
                                    <?php echo $lang == 'si' ? 'විද්‍යුත් තැපැල' : 'Email'; ?> *
                                </label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>
                                    <i class="fas fa-phone"></i>
                                    <?php echo $lang == 'si' ? 'දුරකථන අංකය' : 'Phone Number'; ?>
                                </label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="+94 77 123 4567">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?php echo $lang == 'si' ? 'වෙනස්කම් සුරකින්න' : 'Save Changes'; ?>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Form -->
                <div class="profile-card">
                    <h2 class="card-title">
                        <i class="fas fa-key"></i>
                        <?php echo $lang == 'si' ? 'මුරපදය වෙනස් කරන්න' : 'Change Password'; ?>
                    </h2>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-lock"></i>
                                <?php echo $lang == 'si' ? 'වත්මන් මුරපදය' : 'Current Password'; ?> *
                            </label>
                            <input type="password" name="current_password" required>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label>
                                    <i class="fas fa-lock"></i>
                                    <?php echo $lang == 'si' ? 'නව මුරපදය' : 'New Password'; ?> *
                                </label>
                                <input type="password" name="new_password" minlength="6" required>
                            </div>

                            <div class="form-group">
                                <label>
                                    <i class="fas fa-lock"></i>
                                    <?php echo $lang == 'si' ? 'මුරපදය තහවුරු කරන්න' : 'Confirm Password'; ?> *
                                </label>
                                <input type="password" name="confirm_password" minlength="6" required>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="fas fa-key"></i>
                                <?php echo $lang == 'si' ? 'මුරපදය වෙනස් කරන්න' : 'Change Password'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>