<style>
    .footer {
        background: rgba(255, 255, 255, 0.95);
        margin-top: 50px;
        padding: 40px 0 20px;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    }
    
    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .footer-section h3 {
        color: #667eea;
        font-size: 18px;
        margin-bottom: 15px;
        font-weight: 700;
    }
    
    .footer-section p,
    .footer-section ul {
        color: #666;
        line-height: 1.8;
        margin: 0;
    }
    
    .footer-section ul {
        list-style: none;
        padding: 0;
    }
    
    .footer-section ul li {
        margin-bottom: 10px;
    }
    
    .footer-section ul li a {
        color: #666;
        text-decoration: none;
        transition: color 0.3s;
    }
    
    .footer-section ul li a:hover {
        color: #667eea;
    }
    
    .footer-section ul li i {
        color: #667eea;
        margin-right: 8px;
        width: 20px;
    }
    
    .footer-bottom {
        text-align: center;
        padding-top: 20px;
        border-top: 1px solid #e0e0e0;
        max-width: 1200px;
        margin: 0 auto;
        padding-left: 20px;
        padding-right: 20px;
    }
    
    .footer-bottom p {
        color: #999;
        font-size: 14px;
        margin: 5px 0;
    }
    
    .footer-social {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-top: 15px;
    }
    
    .footer-social a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50%;
        text-decoration: none;
        transition: transform 0.3s;
    }
    
    .footer-social a:hover {
        transform: translateY(-3px);
    }
    
    @media (max-width: 768px) {
        .footer-content {
            grid-template-columns: 1fr;
            text-align: center;
        }
    }
</style>

<footer class="footer">
    <div class="footer-content">
        <!-- About Section -->
        <div class="footer-section">
            <h3><i class="fas fa-store"></i> සුමින්ද ස්ටෝර්ස්</h3>
            <p>ඔබේ දෛනික අවශ්‍යතා සඳහා විශ්වසනීය සිල්ලර වෙළඳසැල. උසස් තත්ත්වයේ භාණ්ඩ සාධාරණ මිලට.</p>
        </div>
        
        <!-- Quick Links -->
        <div class="footer-section">
            <h3>ඉක්මන් සබැඳි</h3>
            <ul>
                <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/index.php"><i class="fas fa-home"></i> මුල් පිටුව</a></li>
                <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/about.php"><i class="fas fa-info-circle"></i> අප ගැන</a></li>
                <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/contact.php"><i class="fas fa-phone"></i> අමතන්න</a></li>
                <?php if (!isset($_SESSION['user_id'])): ?>
                <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/register.php"><i class="fas fa-user-plus"></i> ලියාපදිංචි වන්න</a></li>
                <?php endif; ?>
            </ul>
        </div>
        
        <!-- Contact Info -->
        <div class="footer-section">
            <h3>අප අමතන්න</h3>
            <ul>
                <li><i class="fas fa-map-marker-alt"></i> අකුරැස්ස, මාතර</li>
                <li><i class="fas fa-phone"></i> +94 777640334</li>
                <li><i class="fas fa-envelope"></i> sumindapradeep1111@gmail.com</li>
                <li><i class="fas fa-clock"></i> සඳුදා - ඉරිදා: 7:00 AM - 9:00 PM</li>
            </ul>
        </div>
    </div>
    
    <div class="footer-bottom">
        <div class="footer-social">
            <a href="#" title="Facebook" aria-label="Facebook">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" title="Instagram" aria-label="Instagram">
                <i class="fab fa-instagram"></i>
            </a>
            <a href="#" title="WhatsApp" aria-label="WhatsApp">
                <i class="fab fa-whatsapp"></i>
            </a>
            <a href="#" title="Twitter" aria-label="Twitter">
                <i class="fab fa-twitter"></i>
            </a>
        </div>
        <p style="margin-top: 15px;">
            <strong>සුමින්ද ස්ටෝර්ස්</strong> | ඔබේ විශ්වාසනීය සිල්ලර වෙළඳසැල
        </p>
        <p>© <?php echo date('Y'); ?> Suminda Stores. සියලුම හිමිකම් ඇවිරිණි.</p>
        <p style="font-size: 12px; margin-top: 10px; color: #aaa;">
            Made with <i class="fas fa-heart" style="color: #e74c3c;"></i> in Sri Lanka
        </p>
    </div>
</footer>

<?php if (file_exists(__DIR__ . '/../assets/js/script.js')): ?>
    <script src="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/js/script.js"></script>
<?php endif; ?>

</body>
</html>