Electrical Billing System
A comprehensive web-based application for managing electricity billing, consumption tracking, and customer feedback.

🚀 Features
User Authentication
- Dual Role Login: Admin and Customer portals
- Secure Authentication: Password hashing with bcrypt
- Session Management: Secure session handling with timeout

Admin Panel
- Dashboard: Overview with statistics and recent activities
- Customer Management: Add, view, edit, and delete customer data
- Usage Recording: Track electricity meter readings
- Billing Management: Create, update, and manage electricity bills
- Feedback System: View and respond to customer feedback
- Reporting: Generate and print various reports

Customer Panel
- Dashboard: Personal information and latest bill status
- Bills View: View current and past electricity bills
- Payment History: Complete payment transaction history
- Feedback Submission: Send feedback, suggestions, or complaints
- Usage Calculation: View electricity consumption calculations

Technical Features
- Real-time Calculations: Automatic bill calculation based on usage
- Responsive Design: Mobile-friendly interface
- Data Validation: Comprehensive input validation
- Error Handling: User-friendly error messages
- Export Options: Print and export functionality

📋 Requirements
PHP 7.4 or higher
MySQL 5.7 or higher
Web server (Apache/Nginx)

🛠️ Installation
1. Clone/Download the Project
bash
# Clone the repository
git clone https://github.com/yourusername/tagihan-listrik.git

# Or download and extract the ZIP file
2. Database Setup bash
# Method 1: Using phpMyAdmin
1. Create a new database named 'db_tagihan_listrik'
2. Import the 'db_tagihan_listrik.sql' file
3. Update database credentials in 'config/koneksi.php'

# Method 2: Using MySQL Command Line
mysql -u root -p
CREATE DATABASE db_tagihan_listrik;
USE db_tagihan_listrik;
SOURCE db_tagihan_listrik.sql;
3. Configuration
Edit config/koneksi.php with your database credentials:

# For XAMPP/WAMP:
1. Copy the project folder to 'htdocs' (XAMPP) or 'www' (WAMP)
2. Start Apache and MySQL services
3. Access via: http://localhost/tagihan-listrik/
📁 Project Structure
tagihan-listrik/
│
├── config/
│   └── koneksi.php           # Database configuration
│
├── auth/
│   ├── login.php             # Login page
│   ├── proses_login.php      # Login processing
│   └── logout.php            # Logout script
│
├── admin/
│   ├── dashboard.php         # Admin dashboard
│   ├── konsumen.php          # Customer management
│   ├── pemakaian.php         # Electricity usage recording
│   ├── tagihan.php           # Billing management
│   ├── feedback.php          # Feedback management
│   └── sidebar.php           # Admin sidebar
│
├── konsumen/
│   ├── beranda.php           # Customer dashboard
│   ├── tagihan.php           # Customer bills view
│   ├── riwayat.php           # Payment history
│   ├── feedback.php          # Feedback submission
│   └── sidebar_konsumen.php  # Customer sidebar
│
├── assets/
│   ├── css/
│   │   └── style.css         # Main stylesheet
│   ├── js/
│   │   └── script.js         # JavaScript functions
│   └── img/                  # Images directory
│
├── index.php                 # Main login page
└── db_tagihan_listrik.sql    # Database schema

💡 Usage Guide
For Administrators
Login with admin credentials
Add Customers through the customer management page
Record Usage by entering meter readings
Manage Bills - create, update, and track payments
Respond to Feedback from the feedback management page

For Customers
Login with customer credentials
View Dashboard for personal information
Check Bills - view current and past bills
Make Payments - mark bills as paid
Submit Feedback - send suggestions or complaints

🔧 Database Schema
Main Tables
users - User authentication data
konsumen - Customer information
pemakaian - Electricity usage records
tagihan - Billing information
feedback - Customer feedback

Key Relationships
Each customer has one user account
Each usage record belongs to one customer
Each bill is linked to one usage record
Feedback is linked to user accounts
