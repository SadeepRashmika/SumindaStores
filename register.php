<?php
require_once __DIR__ . "/config/config.php";
require_once __DIR__ . "/config/database.php";

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = 'customer'; // Default role for registration
    
    // Validation
    if (empty($username) || empty($password) || empty($full_name) || empty($email)) {
        setMessage('කරුණාකර සියලුම අත්‍යාවශ්‍ය ක්ෂේත්‍ර පුරවන්න / Please fill all required fields', 'error');
        $error = $_SESSION['error_message'];
    } elseif ($password !== $confirm_password) {
        setMessage('මුරපද ගැලපෙන්නේ නැත / Passwords do not match', 'error');
        $error = $_SESSION['error_message'];
    } elseif (strlen($password) < 6) {
        setMessage('මුරපදය අවම වශයෙන් අක්ෂර 6ක් විය යුතුය / Password must be at least 6 characters', 'error');
        $error = $_SESSION['error_message'];
    } else {
        // Get database connection
        $conn = getDBConnection();
        
        try {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                setMessage('පරිශීලක නාමය දැනටමත් භාවිතා වේ / Username already exists', 'error');
                $error = $_SESSION['error_message'];
            } else {
                // Check if email already exists
                $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    setMessage('විද්‍යුත් තැපෑල දැනටමත් භාවිතා වේ / Email already exists', 'error');
                    $error = $_SESSION['error_message'];
                } else {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user
                    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, phone, role, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
                    $stmt->bind_param("ssssss", $username, $hashed_password, $full_name, $email, $phone, $role);
                    
                    if ($stmt->execute()) {
                        setMessage('ලියාපදිංචිය සාර්ථකයි! දැන් ඔබට පුරනය විය හැක / Registration successful! You can now login', 'success');
                        $success = $_SESSION['success_message'];
                        // Optionally auto-login the user
                        // $_SESSION['user_id'] = $conn->insert_id;
                        // $_SESSION['username'] = $username;
                        // $_SESSION['user_role'] = $role;
                        // redirect('/index.php');
                    } else {
                        setMessage('ලියාපදිංචිය අසාර්ථකයි / Registration failed', 'error');
                        $error = $_SESSION['error_message'];
                    }
                }
            }
            $stmt->close();
        } catch (Exception $e) {
            setMessage('දත්ත සමුදා දෝෂයක් / Database error: ' . $e->getMessage(), 'error');
            $error = $_SESSION['error_message'];
        }
        
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ලියාපදිංචි වන්න - සුමින්ද ස්ටෝර්ස් | Register - Suminda Stores</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .header {
            background: white;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .logo-text h1 {
            font-size: 24px;
            color: #333;
            font-weight: 700;
        }

        .logo-text p {
            font-size: 12px;
            color: #666;
        }

        .header-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-header {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-login {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-login:hover {
            background: #667eea;
            color: white;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 500px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 15px;
        }

        .register-header h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 20px;
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
            font-size: 16px;
            transition: all 0.3s;
            font-family: 'Noto Sans Sinhala', 'Roboto', sans-serif;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .required {
            color: #e74c3c;
        }

        .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .register-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .register-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .register-footer a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        @media (max-width: 768px) {
            .register-container {
                padding: 30px 20px;
            }

            .header-content {
                flex-direction: column;
                gap: 15px;
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
                    <h1>සුමින්ද ස්ටෝර්ස්</h1>
                    <p>ඔබේ විශ්වාසනීය සැපයුම් මධ්‍යස්ථානය</p>
                </div>
            </div>
            <div class="header-buttons">
                <a href="<?php echo BASE_URL; ?>/login.php" class="btn-header btn-login">
                    <i class="fas fa-sign-in-alt"></i> පිවිසෙන්න
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="register-container">
            <div class="register-header">
                <i class="fas fa-user-plus"></i>
                <h2>ලියාපදිංචි වන්න</h2>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>
                        <i class="fas fa-user"></i>
                        පරිශීලක නාමය / Username <span class="required">*</span>
                    </label>
                    <input type="text" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-id-card"></i>
                        සම්පූර්ණ නම / Full Name <span class="required">*</span>
                    </label>
                    <input type="text" name="full_name" required value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-envelope"></i>
                        විද්‍යුත් තැපෑල / Email <span class="required">*</span>
                    </label>
                    <input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-phone"></i>
                        දුරකථන අංකය / Phone Number
                    </label>
                    <input type="tel" name="phone" placeholder="+94 77 123 4567" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-lock"></i>
                        මුරපදය / Password <span class="required">*</span>
                    </label>
                    <input type="password" name="password" required minlength="6">
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-lock"></i>
                        මුරපදය තහවුරු කරන්න / Confirm Password <span class="required">*</span>
                    </label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>

                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i>
                    ලියාපදිංචි වන්න
                </button>
            </form>

            <div class="register-footer">
                දැනටමත් ගිණුමක් තිබේද? <a href="<?php echo BASE_URL; ?>/login.php">දැන් පිවිසෙන්න</a>
            </div>
        </div>
    </div>
</body>
</html>