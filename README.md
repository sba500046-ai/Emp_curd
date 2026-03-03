Employee CRUD Application
A complete PHP-based CRUD (Create, Read, Update, Delete) application for managing employee records with status alerts and URL-based ID passing.

Features
✅ Create - Add new employees with form validation ✅ Read - View all employees in a responsive table ✅ Update - Edit employee details by passing ID through URL ✅ Delete - Remove employees with confirmation ✅ Status Alerts - Visual alerts for success/error messages ✅ Form Validation - Server-side validation for all inputs ✅ Duplicate Prevention - Email uniqueness validation ✅ Responsive Design - Mobile-friendly interface ✅ Auto-dismissing Alerts - Alerts disappear after 5 seconds

Project Structure
lab-5/
├── index.php          # Main dashboard - Lists all employees
├── create.php         # Form to add new employee
├── edit.php           # Form to edit employee (ID passed via URL)
├── delete.php         # Handles employee deletion
├── process.php        # Backend logic for create/update operations
├── config.php         # Database configuration
├── database.sql       # SQL script to create database and tables
└── README.md         # Documentation
