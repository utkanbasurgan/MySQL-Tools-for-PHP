Overview
MySQL-Tools-for-PHP is a comprehensive PHP library designed to simplify database interactions, provide robust connection management, and offer advanced querying capabilities for MySQL databases.
Features

🔌 Seamless MySQL database connections
🚀 Efficient query execution and result handling
🛡️ Advanced query protection against SQL injection
📊 Complex data manipulation utilities
🔍 Simplified data retrieval and transaction management

Prerequisites
s
PHP 7.4 or highers
MySQL 5.7 or higher
Composer package managers

Installation
Install the library using Composer:
bashCopycomposer require utkanbasurgan/MySQL-Tools-for-PHP
Quick Start
Establishing a Connection
phpCopyuse MySQLTools\DatabaseConnection;

$db = new DatabaseConnection([
    'host' => 'localhost',
    'username' => 'your_username',
    'password' => 'your_password',
    'database' => 'your_database'
]);
Basic Query Execution
phpCopy// Fetch all users
$users = $db->query("SELECT * FROM users")->fetchAll();

// Insert a new record
$db->query("INSERT INTO users (name, email) VALUES (?, ?)", [
    $name, 
    $email
]);
Advanced Usage
Transaction Management
phpCopy$db->beginTransaction();
try {
    // Multiple queries
    $db->query("UPDATE accounts SET balance = balance - 100 WHERE id = ?", [$fromAccount]);
    $db->query("UPDATE accounts SET balance = balance + 100 WHERE id = ?", [$toAccount]);
    
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    throw $e;
}
Configuration Options
OptionTypeDescriptionDefaulthoststringDatabase hostlocalhostusernamestringDatabase username-passwordstringDatabase password-databasestringDatabase name-portintDatabase port3306charsetstringConnection character setutf8mb4
Security

Prepared statements for preventing SQL injection
Automatic connection encryption
Connection timeout management
s
Error Handling
phpCopytry {
    // Database operations
} catch (DatabaseException $e) {
    // Log or handle specific database errors
    echo $e->getMessage();
}
Contributing

Fork the repository
Create your feature branch (git checkout -b feature/AmazingFeature)
Commit your changes (git commit -m 'Add some AmazingFeature')
Push to the branch (git push origin feature/AmazingFeature)
Open a Pull Request

License
Distributed under the MIT License. See LICENSE for more information.
Contact
Utkan Başurgan - utkan@basurgan.com
Project Link: https://github.com/utkanbasurgan/MySQL-Tools-for-PHP
