<style>
    .navbar {
        background: rgba(255, 255, 255, 0.95);
        padding: 15px 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 0;
    }
    
    .navbar .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 30px;
        flex-wrap: wrap;
    }
    
    .nav-link {
        text-decoration: none;
        color: #333;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 8px;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .nav-link:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        transform: translateY(-2px);
    }
    
    .nav-link.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    @media (max-width: 768px) {
        .navbar .container {
            gap: 10px;
        }
        
        .nav-link {
            padding: 8px 15px;
            font-size: 14px;
        }
    }
</style>

<nav class="navbar">
    <div class="container">
        <a href="<?php echo BASE_URL; ?>/index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> මුල් පිටුව
        </a>
        
        <a href="<?php echo BASE_URL; ?>/customer/categories.php" class="nav-link">
            <i class="fas fa-th-large"></i> වර්ග
        </a>
        
        <a href="<?php echo BASE_URL; ?>/customer/products.php" class="nav-link">
            <i class="fas fa-shopping-bag"></i> භාණ්ඩ
        </a>
        
        <a href="<?php echo BASE_URL; ?>/customer/about.php" class="nav-link">
            <i class="fas fa-info-circle"></i> අප ගැන
        </a>
        
        <a href="<?php echo BASE_URL; ?>/customer/contact.php" class="nav-link">
            <i class="fas fa-phone"></i> අමතන්න
        </a>
    </div>
</nav>