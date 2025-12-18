    <?php
    /**
     * Helper functions
     */

    /**
     * Get email configuration
     * 
     * @return array Email configuration
     */
    function getEmailConfig() {
        static $config = null;
        
        if ($config === null) {
            $config = require __DIR__ . '/email.php';
        }
        
        return $config;
    }

    /**
     * Get admin email
     * 
     * @return string Admin email address
     */
    function getAdminEmail() {
        $config = getEmailConfig();
        return $config['admin_email'] ?? 'admin@rollmate.com';
    }
