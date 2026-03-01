<?php
require_once __DIR__ . '/../includes/auth_check.php';

require_once __DIR__ . '/../includes/permission.php';
require_once __DIR__ . '/../config/db.php';

/*  1. Permission Enforcement  */
requirePermission('edit_user');
$tenant_id = $_SESSION['tenant_id'];


/*  2. Validate ID */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: list.php");
    exit;
}

$user_id = (int) $_GET['id'];

/*  3. Fetch User */

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, name, email, role_id 
    FROM users 
    WHERE id = ? AND tenant_id = ?"
);

mysqli_stmt_bind_param($stmt, "ii", $user_id, $tenant_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

/*  Check User Exists BEFORE Using It  */

if (!$user) {
    header("Location: list.php?error=notfound");
    exit;
}

$target_role_id  = (int) $user['role_id'];
$current_role_id = (int) $_SESSION['role_id'];

/*  Prevent Editing Another Admin  */

if ($target_role_id === $current_role_id && $user['id'] !== $_SESSION['user_id']) {
    header("Location: list.php?error=protected2");
    exit;
}

/*  4. Fetch Roles  */

$roles_result = mysqli_query($conn, "SELECT id, name FROM roles ORDER BY id ASC");

$errors = [];

/*  5. Handle POST  */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $role_id = $_POST['role_id'] ?? '';

    /*  Validation */

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

    $role_id = (int) $role_id;

    /*  Duplicate Email Check  */

    if (empty($errors)) {
        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM users 
            WHERE email = ? 
            AND id !=  ?
            AND tenant_id = ?"
        );

        mysqli_stmt_bind_param($check, "sii", $email, $user_id, $tenant_id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $errors['email'] = "Email already exists.";
        }

        mysqli_stmt_close($check);
    }

    /*  Prevent Self Role Change  */

    if (empty($errors)) {
        if ($user_id === (int) $_SESSION['user_id'] && $role_id !== $current_role_id) {
            $errors['role'] = "You cannot change your own role.";
        }
    }

    /*  Prevent Promoting Others to Admin  */

    if (empty($errors)) {
        if ($role_id === $current_role_id && $user_id !== (int) $_SESSION['user_id']) {
            $errors['role'] = "You cannot assign admin role to another user.";
        }
    }

    //update

    if (empty($errors)) {

        $update = mysqli_prepare(
            $conn,
            "UPDATE users 
            SET name = ?, email = ?, role_id = ? 
            WHERE id = ? AND tenant_id = ?"
        );

        mysqli_stmt_bind_param($update, "ssiii", $name, $email, $role_id, $user_id, $tenant_id);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);

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