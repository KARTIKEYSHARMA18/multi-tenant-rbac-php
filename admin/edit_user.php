<?php
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../config/db.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: list.php");
    exit;
}
$user_id = (int) $_GET['id'];
$stmt = mysqli_prepare($conn, "SELECT id, name, email, role FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
if(!$user){
    header("Location: list.php?error=notfound");
    exit;
}

$errors=[];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role  = $_POST['role'] ?? '';
    
    /* ---- Validation ---- */

    if ($name === '') {
        $errors['name'] = "Name is required.";
    }

    if ($email === '') {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    if (!in_array($role, ['admin', 'user'])) {
        $errors['role'] = "Invalid role selected.";
    }
    /* ---- Duplicate Email Check ---- */
    if (empty($errors)) {
        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ? AND id != ?"
        );
        mysqli_stmt_bind_param($check, "si", $email, $user_id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $errors['email'] = "Email already exists.";
        }

        mysqli_stmt_close($check);
    }
 
     /* ---- If No Errors → Update ---- */
    if (empty($errors)) {
        if ($user_id == $_SESSION['user_id'] && $role !== 'admin') {
            $errors['role'] = "You cannot remove your own admin role.";
        }
        $update = mysqli_prepare(
            $conn,
            "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?"
        );

        mysqli_stmt_bind_param($update, "sssi", $name, $email, $role, $user_id);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);

        mysqli_close($conn);

        header("Location: list.php?success=updated");
        exit;
    }

}
else {
    
    $name  = $user['name'];
    $email = $user['email'];
    $role  = $user['role'];
}




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EDIT USER</title>
</head>
<body>
    <form method = "post">
        <label>Name:</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>">
    <span style="color:red"><?= $errors['name'] ?? '' ?></span>
    <br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
    <span style="color:red"><?= $errors['email'] ?? '' ?></span>
    <br><br>

    <label>Role:</label><br>
    <select name="role">
        <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User</option>
        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
    </select>
    <span style="color:red"><?= $errors['role'] ?? '' ?></span>
    <br><br>

    <button type="submit">Update</button>

</form>

<br>
<a href="list.php">Back to List</a>
</body>
</html>