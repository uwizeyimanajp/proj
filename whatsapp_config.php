<?php
// WhatsApp Configuration for ordering system

// Business information
define('BUSINESS_NAME', 'FreshHarvest Produce');
define('BUSINESS_EMAIL', 'orders@freshtarvest.com');
define('DELIVERY_CUTOFF_TIME', '2 PM');
define('SUPPORT_HOURS', '24/7');

// Default WhatsApp number (fallback)
define('DEFAULT_WHATSAPP_NUMBER', '250123456789'); // Rwanda format example

// Function to get WhatsApp number from database
function getWhatsAppNumber() {
    try {
        // Include config if not already included
        if (!function_exists('getDBConnection')) {
            require_once 'includes/config.php';
        }

        $conn = getDBConnection();
        $result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'whatsapp_number' LIMIT 1");

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $number = trim($row['setting_value']);
            if (!empty($number)) {
                // Clean the number - remove any non-numeric characters except +
                $number = preg_replace('/[^\d+]/', '', $number);
                // If it starts with +, remove it for WhatsApp URL
                if (strpos($number, '+') === 0) {
                    $number = substr($number, 1);
                }
                $conn->close();
                return $number;
            }
        }
        $conn->close();
    } catch (Exception $e) {
        // Database not available, use default
    }

    return DEFAULT_WHATSAPP_NUMBER;
}

// Function to save WhatsApp number to database
function saveWhatsAppNumber($number) {
    try {
        // Include config if not already included
        if (!function_exists('getDBConnection')) {
            require_once 'includes/config.php';
        }

        $conn = getDBConnection();

        // Clean the number
        $number = trim($number);
        $number = preg_replace('/[^\d+]/', '', $number);

        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('whatsapp_number', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("ss", $number, $number);
        $result = $stmt->execute();
        $stmt->close();
        $conn->close();

        return $result;
    } catch (Exception $e) {
        return false;
    }
}

// Helper function to generate WhatsApp order URL
function generateWhatsAppOrderURL($productName, $price, $quantity = 1) {
    $whatsappNumber = getWhatsAppNumber();
    $message = "Hi! I would like to order {$quantity}x {$productName} ($$price each). Please let me know the next steps.";
    $encodedMessage = urlencode($message);
    return "https://wa.me/{$whatsappNumber}?text={$encodedMessage}";
}

// Helper function to generate bulk order URL
function generateBulkOrderURL() {
    $whatsappNumber = getWhatsAppNumber();
    $message = "Hi! I'm interested in bulk ordering. Can you please provide wholesale pricing information?";
    $encodedMessage = urlencode($message);
    return "https://wa.me/{$whatsappNumber}?text={$encodedMessage}";
}

// Helper function to generate general inquiry URL
function generateInquiryURL($subject = "General Inquiry") {
    $whatsappNumber = getWhatsAppNumber();
    $message = "Hi! I have a {$subject}. Can you please help me?";
    $encodedMessage = urlencode($message);
    return "https://wa.me/{$whatsappNumber}?text={$encodedMessage}";
}

// Function to validate Rwandan phone number format
function isValidRwandanNumber($number) {
    // Clean the number
    $number = preg_replace('/[^\d+]/', '', $number);

    // Check if it starts with +250 or 250 (Rwanda country code)
    if (preg_match('/^(?:\+250|250)/', $number)) {
        // Remove country code to check length
        $localNumber = preg_match('/^(\+250|250)/', $number, $matches) ? substr($number, strlen($matches[1])) : $number;

        // Rwandan mobile numbers are 9 digits after country code
        return strlen($localNumber) === 9 && is_numeric($localNumber);
    }

    return false;
}
?>
