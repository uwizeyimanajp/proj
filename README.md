# FreshHarvest - Vegetable & Fruit Stock Management System

A comprehensive web-based inventory management system designed specifically for produce vendors, grocery stores, and farmers markets to manage their fresh vegetable and fruit stock efficiently.

## 🚀 Features

### Admin Dashboard
- **Modern Interface**: Beautiful, responsive admin dashboard with sidebar navigation
- **Real-time Statistics**: Live inventory counts, low stock alerts, and category breakdowns
- **Interactive Charts**: Visual representation of inventory data using Chart.js
- **Search Functionality**: Real-time search through inventory items
- **Recent Activity**: Track latest inventory changes and additions

### Inventory Management
- **CRUD Operations**: Complete Create, Read, Update, Delete functionality for stock items
- **Category Management**: Organize items into vegetable and fruit categories
- **Stock Level Monitoring**: Color-coded alerts for low stock items
- **Bulk Operations**: Add, edit, and manage multiple items efficiently

### WhatsApp Ordering System
- **Direct Ordering**: Customers can place orders directly through WhatsApp
- **Product Showcase**: Display available fresh produce with prices and stock levels
- **Automated Messages**: Pre-formatted order messages with product details
- **Bulk Order Support**: Special contact option for wholesale orders
- **Real-time Inventory**: Only shows items that are currently in stock
- **Admin Configurable**: Admins can set and update WhatsApp business number
- **Rwandan Number Validation**: Ensures only valid Rwandan phone numbers are accepted

### Landing Page
- **Professional Design**: Modern, mobile-responsive landing page
- **Live Statistics**: Dynamic display of current inventory stats
- **Feature Showcase**: Highlight system capabilities and benefits
- **Customer Testimonials**: Social proof from satisfied users
- **WhatsApp Integration**: Seamless ordering experience

## 📋 Requirements

- **PHP 7.4+**
- **MySQL 5.7+**
- **Apache/Nginx Web Server**
- **Modern Web Browser**

## 🛠️ Installation

### 1. Database Setup
Run the setup script to initialize the database:
```bash
php setup.php
```

Or manually import `setup.sql` into your MySQL database.

### 2. WhatsApp Configuration
Edit `whatsapp_config.php` to customize your WhatsApp business number:
```php
define('WHATSAPP_NUMBER', '1234567890'); // Replace with your number
```

### 3. File Permissions
Ensure the web server has write permissions for necessary directories.

### 4. Access the Application
- **Public Site**: `http://localhost/vegetable/`
- **Admin Login**: `http://localhost/vegetable/admin/login.php`
- **Default Admin**: Username: `admin`, Password: `admin123`

## 📱 WhatsApp Ordering Setup

### 1. Configure Your WhatsApp Business Number
Edit `whatsapp_config.php`:
```php
// Replace with your actual WhatsApp number (without + or spaces)
define('WHATSAPP_NUMBER', '1234567890'); // Example: +1 (234) 567-890 becomes 1234567890
```

### 2. WhatsApp Business Account (Recommended)
- Set up a WhatsApp Business account for professional ordering
- Create automated responses for common inquiries
- Set up quick replies for order confirmations

### 3. Order Flow
1. **Customer visits** the landing page
2. **Browses available products** in the Order section
3. **Clicks "Order via WhatsApp"** button
4. **WhatsApp opens** with pre-filled order message
5. **Business responds** to confirm order details
6. **Delivery arranged** through WhatsApp conversation

### 4. Sample Order Message
When a customer clicks an order button, WhatsApp opens with:
```
Hi! I would like to order Organic Spinach ($2.99 per lb). Please let me know the next steps.
```

## 🎨 Customization

### Colors and Branding
- **Primary Color**: Green (#27ae60) for vegetables
- **Secondary Color**: Blue (#3498db) for UI elements
- **Accent Colors**: Orange (#e67e22) for fruits

### WhatsApp Integration
- **Custom Messages**: Modify message templates in `whatsapp_config.php`
- **Business Information**: Update contact details and business info
- **Order Processing**: Customize order confirmation workflows

## 📊 Admin Features

### Dashboard Overview
- **Statistics Cards**: Total items, vegetables, fruits, low stock alerts
- **Category Chart**: Visual breakdown of inventory by category
- **Recent Activity**: Latest inventory additions and changes
- **Quick Actions**: Fast access to common admin tasks

### Inventory Management
- **Add Items**: Create new inventory items with categories
- **Edit Items**: Update prices, quantities, and descriptions
- **Delete Items**: Remove items with confirmation
- **Category Management**: Add/edit/delete product categories

### Search & Filter
- **Real-time Search**: Instant filtering of inventory table
- **Category Filters**: Filter by vegetable/fruit types
- **Stock Level Filters**: View low stock or out-of-stock items

## 🔒 Security Features

- **Secure Authentication**: Password-protected admin access
- **SQL Injection Protection**: Prepared statements for all database queries
- **XSS Protection**: Input sanitization and output escaping
- **Session Management**: Secure admin session handling

## 📱 Mobile Responsive

- **Responsive Design**: Works perfectly on all device sizes
- **Touch-Friendly**: Optimized for mobile interactions
- **Fast Loading**: Optimized assets and efficient code
- **Progressive Web App**: Can be installed as a mobile app

## 🛠️ Technical Details

### Database Schema
- **items**: Product inventory with categories, prices, quantities
- **categories**: Product categories (vegetables/fruits)
- **admins**: Admin user accounts

### File Structure
```
vegetable/
├── index.php                 # Landing page with WhatsApp ordering
├── setup.php                 # Database setup script
├── setup.sql                 # Database schema and sample data
├── whatsapp_config.php       # WhatsApp configuration
├── includes/
│   └── config.php           # Database configuration
├── assets/
│   ├── css/style.css        # Main styling
│   └── js/main.js          # JavaScript functionality
└── admin/
    ├── login.php            # Admin authentication
    ├── dashboard.php        # Modern admin dashboard
    ├── add_item.php         # Add inventory items
    ├── edit_item.php        # Edit inventory items
    └── categories.php       # Manage categories
```

## 📞 Support

For technical support or feature requests:
- **Email**: support@freshtarvest.com
- **WhatsApp**: Contact through the website ordering system

## 📄 License

This project is open-source and available under the MIT License.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues for bugs and feature requests.

---

**FreshHarvest** - Making produce inventory management simple, efficient, and connected with WhatsApp ordering for the modern marketplace.
