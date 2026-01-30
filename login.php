<?php
require_once 'config/config.php';
require_once 'config/database.php';

// Language handling - Default to English
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'en';
}

// Handle language switch
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'si'])) {
    $_SESSION['language'] = $_GET['lang'];
    header('Location: login.php');
    exit();
}

$lang = $_SESSION['language'];

// Language translations
$translations = [
    'en' => [
        'page_title' => 'Login',
        'login_title' => 'Login',
        'username' => 'Username',
        'password' => 'Password',
        'login_btn' => 'Login',
        'no_account' => "Don't have an account?",
        'register_now' => 'Register Now',
        'all_fields_required' => 'Please fill all fields',
        'invalid_credentials' => 'Invalid username or password',
        'registration_success' => 'Registration successful! You can now login.',
        'language' => 'Language'
    ],
    'si' => [
        'page_title' => 'පුරනය වන්න',
        'login_title' => 'පුරනය වන්න',
        'username' => 'පරිශීලක නාමය',
        'password' => 'මුරපදය',
        'login_btn' => 'පුරනය වන්න',
        'no_account' => 'ගිණුමක් නැද්ද?',
        'register_now' => 'දැන් ලියාපදිංචි වන්න',
        'all_fields_required' => 'කරුණාකර සියලු ක්ෂේත්‍ර පුරවන්න',
        'invalid_credentials' => 'අවලංගු පරිශීලක නාමය හෝ මුරපදය',
        'registration_success' => 'ලියාපදිංචිය සාර්ථකයි! දැන් ඔබට පුරනය විය හැක.',
        'language' => 'භාෂාව'
    ]
];

$t = $translations[$lang];
$page_title = $t['page_title'];

$error = '';
$success = '';

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    if ($role == 'admin') {
        header("Location: admin/index.php");
    } elseif ($role == 'seller') {
        header("Location: seller/index.php");
    } else {
        header("Location: customer/index.php");
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = $t['all_fields_required'];
    } else {
        $query = "SELECT * FROM users WHERE username = ? AND status = 'active'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                
                if ($user['role'] == 'admin') {
                    header("Location: admin/index.php");
                } elseif ($user['role'] == 'seller') {
                    header("Location: seller/index.php");
                } else {
                    header("Location: customer/index.php");
                }
                exit();
            } else {
                $error = $t['invalid_credentials'];
            }
        } else {
            $error = $t['invalid_credentials'];
        }
    }
}

include 'includes/header.php';

?>

<style>
    .auth-container {
        max-width: 450px;
        margin: 50px auto;
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        position: relative;
    }
    
    .language-switcher-top {
        position: absolute;
        top: 20px;
        right: 20px;
        display: flex;
        gap: 8px;
        align-items: center;
    }
    
    .lang-btn-small {
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        font-size: 12px;
        transition: all 0.3s;
        border: 2px solid #667eea;
        background: white;
        color: #667eea;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    .lang-btn-small:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
    }
    
    .lang-btn-small.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: #667eea;
    }
    
    .auth-title {
        text-align: center;
        font-size: 32px;
        font-weight: 700;
        color: #333;
        margin-bottom: 30px;
        margin-top: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }
    
    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 16px;
        transition: all 0.3s;
        font-family: 'Noto Sans Sinhala', sans-serif;
    }
    
    .form-group input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .btn-submit {
        width: 100%;
        padding: 15px;
        font-size: 18px;
        font-weight: 600;
        margin-top: 10px;
    }
    
    .auth-links {
        text-align: center;
        margin-top: 20px;
        color: #666;
    }
    
    .auth-links a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }
    
    .auth-links a:hover {
        text-decoration: underline;
    }
    
    .demo-credentials {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        margin-top: 20px;
        font-size: 14px;
    }
    
    .demo-credentials strong {
        color: #667eea;
    }
    
    @media (max-width: 480px) {
        .auth-container {
            padding: 30px 20px;
            margin: 20px;
        }
        
        .language-switcher-top {
            top: 15px;
            right: 15px;
        }
        
        .auth-title {
            font-size: 26px;
        }
    }
</style>

<div class="container">
    <div class="auth-container">
        <!-- Language Switcher -->
        <div class="language-switcher-top">
            <a href="?lang=en" class="lang-btn-small <?php echo $lang == 'en' ? 'active' : ''; ?>">
                <i class="fas fa-flag-usa"></i> EN
            </a>
            <a href="?lang=si" class="lang-btn-small <?php echo $lang == 'si' ? 'active' : ''; ?>">
                <i class="fas fa-flag"></i> සිං
            </a>
        </div>
        
        <h2 class="auth-title"><i class="fas fa-sign-in-alt"></i> <?php echo $t['login_title']; ?></h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $t['registration_success']; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> <?php echo $t['username']; ?></label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> <?php echo $t['password']; ?></label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-submit">
                <i class="fas fa-sign-in-alt"></i> <?php echo $t['login_btn']; ?>
            </button>
        </form>
        
        <div class="auth-links">
            <?php echo $t['no_account']; ?> <a href="register.php"><?php echo $t['register_now']; ?></a>
        </div>
        
    </div>
</div>

<?php include 'includes/footer.php'; ?>