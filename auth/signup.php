<?php
$errors = [];
$name = $email = $password = $confirm_password = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if ($name === '') {
        $errors['name'] = 'Name is required';
    }

    if ($email === '') {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }

    if ($confirm_password === '') {
        $errors['confirm_password'] = 'Confirm your password';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }

    if (empty($errors)) {

        require_once __DIR__ . '/../config/db.php';

        // Check duplicate email
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);

        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            $errors['email'] = "Email already exists";
        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users (name, email, password) VALUES (?, ?, ?)"
            );

            mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashedPassword);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: signin.php?success=1");
                exit;
            } else {
                $errors['form'] = "Something went wrong. Please try again.";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($checkStmt);
        mysqli_close($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <p>Create an account to register for the event.</p>
       <?php if(isset($_GET['success'])): ?>
  <p style="color: green; font-size: 18px;">
   
    Success! Your details have been submitted.
  </p>
<?php endif; ?>
<form action = "<?=  htmlspecialchars($_SERVER['PHP_SELF']) ?>" method = "post">
    <input type = "text" name = "name" value = "<?= htmlspecialchars($name) ?>"placeholder = "enter your name " required>
    <span style="color:red"><?= $errors['name'] ?? '' ?></span>
   
    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Enter your email" required>
    <span style="color:red"><?= $errors['email'] ?? '' ?></span>
    <input type = "password" name = "password" placeholder="enter password">
    <span style="color:red"><?= $errors['password'] ?? '' ?></span>
    <input type = "password" name = "confirm_password" placeholder="confirm password">
    <span style="color:red"><?= $errors['confirm_password'] ?? '' ?></span>
      <button class="btn" >Submit</button>

</form>
<?php if (isset($errors['form'])): ?>
    <p style="color:red"><?= $errors['form'] ?></p>
<?php endif; ?>
    </div>
</body>
</html>