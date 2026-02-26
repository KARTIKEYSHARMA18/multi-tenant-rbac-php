<?php
$errors = [];
$name=$email=$user_password=$confirm_password="";
if($_SERVER["REQUEST_METHOD"]=="POST"){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $user_password = $_POST['password'] ?? '';
    $confirm_password=$_POST['confirm_password'] ?? '';
    if($name===''){
        $errors['name'] = 'name is required.' ;
    }
    if($email === ''){
        $errors['email'] = "Email is required";

    }
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
       $errors['email'] = "invalid email format";
    }
    if($user_password===''){
        $errors['password'] = "password is req.";
    }
    else if(strlen($user_password)<6){
        $errors['password'] = "password must be at least 6 characters";
    }
    if($confirm_password === ""){
        $errors['confirm_password'] = "confirm your password";

    }
    else if($user_password!== $confirm_password){
        $errors['confirm_password'] = "password do not match";
    }

    if(empty($errors)){
        require_once __DIR__ . '/../db.php';
        $hashedpassword = password_hash($user_password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashedpassword);
        
        if(mysqli_stmt_execute($stmt)){
            header("Location: signin.php?success=1");
            exit;
        }
        
        else{
            if(mysqli_errno($conn)===1062){
            $errors['email']= 'email already exists';
            }
            else{
                $errors['form']='something went wrong. Please try again';
            }
        }
        mysqli_stmt_close($stmt);
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