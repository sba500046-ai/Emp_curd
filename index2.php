<?php
// -------------------------
// CONFIG & DATABASE SETUP
// -------------------------
$host = "localhost";
$user = "root";
$pass = "";
$db   = "emp_crud";

$conn = new mysqli($host, $user, $pass, $db);
if($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Create table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL
)");

// -------------------------
// HANDLE CRUD ACTIONS
// -------------------------
$action = $_GET['action'] ?? '';
$msg = '';

if($action == 'delete' && isset($_GET['id'])){
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM employees WHERE id=?");
    if($stmt){
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $msg = "Employee deleted successfully!";
    } else {
        $msg = "Database error: ".$conn->error;
    }
}

$name = $email = $phone = '';
$errors = [];
$edit_id = null;

if($action == 'edit' && isset($_GET['id'])){
    $edit_id = intval($_GET['id']);
    $res = $conn->query("SELECT * FROM employees WHERE id=$edit_id");
    if($res && $res->num_rows){
        $emp = $res->fetch_assoc();
        $name = $emp['name'];
        $email = $emp['email'];
        $phone = $emp['phone'];
    } else {
        $edit_id = null;
    }
}

// -------------------------
// HANDLE FORM SUBMISSION
// -------------------------
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    if(!$name) $errors[] = "Name is required";
    if(!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if(!$phone) $errors[] = "Phone is required";

    // -------------------------
    // Check for duplicate email
    // -------------------------
    if(empty($errors)){
        if($edit_id){
            $stmt = $conn->prepare("SELECT id FROM employees WHERE email=? AND id<>?");
            if($stmt){
                $stmt->bind_param("si", $email, $edit_id);
                $stmt->execute();
                $stmt->store_result();
                if($stmt->num_rows > 0){
                    $errors[] = "Email already exists";
                }
                $stmt->close();
            } else {
                $errors[] = "Database error: ".$conn->error;
            }
        } else {
            $stmt = $conn->prepare("SELECT id FROM employees WHERE email=?");
            if($stmt){
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                if($stmt->num_rows > 0){
                    $errors[] = "Email already exists";
                }
                $stmt->close();
            } else {
                $errors[] = "Database error: ".$conn->error;
            }
        }
    }

    // -------------------------
    // Insert or Update Employee
    // -------------------------
    if(empty($errors)){
        if($edit_id){
            $stmt = $conn->prepare("UPDATE employees SET name=?, email=?, phone=? WHERE id=?");
            if($stmt){
                $stmt->bind_param("sssi", $name, $email, $phone, $edit_id);
                $stmt->execute();
                $stmt->close();
                $msg = "Employee updated successfully!";
                $edit_id = null;
            } else {
                $errors[] = "Database error: ".$conn->error;
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO employees (name,email,phone) VALUES (?,?,?)");
            if($stmt){
                $stmt->bind_param("sss", $name, $email, $phone);
                $stmt->execute();
                $stmt->close();
                $msg = "Employee added successfully!";
            } else {
                $errors[] = "Database error: ".$conn->error;
            }
        }
        if(empty($errors)){
            $name = $email = $phone = '';
        }
    }
}

// -------------------------
// FETCH ALL EMPLOYEES
// -------------------------
$employees = $conn->query("SELECT * FROM employees ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Management</title>
<style>
body { font-family: Arial; background:#f4f4f4; }
.container { max-width: 800px; margin: 20px auto; background:#fff; padding:20px; border-radius:8px; }
h2 { text-align:center; }
table { width:100%; border-collapse: collapse; margin-top:20px; }
table, th, td { border:1px solid #ddd; }
th, td { padding:10px; text-align:center; }
input { width:100%; padding:8px; margin:5px 0 15px; border:1px solid #ccc; border-radius:4px; }
button, a.btn { padding:10px 15px; background:#28a745; color:#fff; border:none; border-radius:4px; cursor:pointer; text-decoration:none; display:inline-block; }
a.edit { background:#007BFF; }
a.delete { background:#dc3545; }
.alert { padding:10px; margin:10px 0; border-radius:4px; }
.alert.success { background:#d4edda; color:#155724; }
.alert.error { background:#f8d7da; color:#721c24; }
@media(max-width:600px){
    table, thead, tbody, th, td, tr { display:block; }
    th { display:none; }
    td { border:none; position:relative; padding-left:50%; text-align:left; }
    td:before { position:absolute; left:10px; width:45%; white-space:nowrap; font-weight:bold; content:attr(data-label); }
}
</style>
</head>
<body>
<div class="container">
    <h2><?= $edit_id ? "Edit Employee" : "Add New Employee" ?></h2>

    <?php if($msg): ?>
        <div class="alert success"><?= $msg ?></div>
    <?php endif; ?>
    <?php if($errors): ?>
        <div class="alert error"><?= implode('<br>',$errors) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="name" placeholder="Name" value="<?= htmlspecialchars($name) ?>" required>
        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>" required>
        <input type="text" name="phone" placeholder="Phone" value="<?= htmlspecialchars($phone) ?>" required>
        <button type="submit"><?= $edit_id ? "Update Employee" : "Add Employee" ?></button>
        <?php if($edit_id): ?>
            <a href="index.php" class="btn">Cancel</a>
        <?php endif; ?>
    </form>

    <h2>Employee List</h2>
    <table>
        <thead>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if($employees && $employees->num_rows > 0):
            while($row = $employees->fetch_assoc()): ?>
                <tr>
                    <td data-label="ID"><?= $row['id'] ?></td>
                    <td data-label="Name"><?= $row['name'] ?></td>
                    <td data-label="Email"><?= $row['email'] ?></td>
                    <td data-label="Phone"><?= $row['phone'] ?></td>
                    <td data-label="Actions">
                        <a href="?action=edit&id=<?= $row['id'] ?>" class="edit">Edit</a>
                        <a href="?action=delete&id=<?= $row['id'] ?>" class="delete" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile;
        else: ?>
            <tr><td colspan="5">No employees found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<script>
setTimeout(()=>{document.querySelector('.alert')?.remove();},5000);
</script>
</body>
</html>
