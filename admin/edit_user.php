<?php
session_start();

require_once __DIR__ . '/../includes/permission.php';
require_once __DIR__ . '/../config/db.php';

/* -------------------------
   1. Permission Enforcement
-------------------------- */

if (!hasPermission('edit_user')) {
    die("Unauthorized Access");
}

/* -------------------------
   2. Validate ID
-------------------------- */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: list.php");
    exit;
}

$user_id = (int) $_GET['id'];

/* -------------------------
   3. Fetch User
-------------------------- */

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, name, email, role_id FROM users WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    header("Location: list.php?error=notfound");
    exit;
}

/* -------------------------
   4. Fetch All Roles
-------------------------- */

$roles_result = mysqli_query($conn, "SELECT id, name FROM roles ORDER BY id ASC");

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $role_id = $_POST['role_id'] ?? '';

    /* ---- Validation ---- */

    if ($name === '') {
        $errors['name'] = "Name is required.";
    }

    if ($email === '') {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    if (!is_numeric($role_id)) {
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

    /* ---- Prevent Self Role Downgrade ---- */

    if (empty($errors)) {
        if ($user_id == $_SESSION['user_id'] && $role_id != $_SESSION['role_id']) {
            $errors['role'] = "You cannot change your own role.";
        }
    }

    /* ---- Update If No Errors ---- */

    if (empty($errors)) {

        $update = mysqli_prepare(
            $conn,
            "UPDATE users SET name = ?, email = ?, role_id = ? WHERE id = ?"
        );

        mysqli_stmt_bind_param($update, "ssii", $name, $email, $role_id, $user_id);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);

        mysqli_close($conn);

        header("Location: list.php?success=updated");
        exit;
    }

} else {

    $name    = $user['name'];
    $email   = $user['email'];
    $role_id = $user['role_id'];
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
</head>
<body>

<h2>Edit User</h2>

<form method="POST">

    <label>Name:</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>">
    <span style="color:red"><?= $errors['name'] ?? '' ?></span>
    <br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
    <span style="color:red"><?= $errors['email'] ?? '' ?></span>
    <br><br>

    <label>Role:</label><br>
    <select name="role_id">
        <?php while ($role = mysqli_fetch_assoc($roles_result)) : ?>
            <option value="<?= (int)$role['id']; ?>"
                <?= $role_id == $role['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($role['name']); ?>
            </option>
        <?php endwhile; ?>
    </select>
    <span style="color:red"><?= $errors['role'] ?? '' ?></span>
    <br><br>

    <button type="submit">Update</button>

</form>

<br>
<a href="list.php">Back to List</a>

</body>
</html>