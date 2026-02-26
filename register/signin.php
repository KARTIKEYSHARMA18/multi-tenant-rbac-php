<?php
$errors = [];
$name = $email="";
if($_SERVER["REQUEST_METHOD"]=="POST"){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    if($name===''){
        $errors['name'] = 'name is required.' ;
    }
    if($email === ''){
        $errors['email'] = "Email is required";

    }
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
       $errors['email'] = "invalid email format";
    }
    if(empty($errors)){
        require_once __DIR__ . '/../db.php';
        
        $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $name, $email);
        
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
   
    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Enter your email" required>
   
      <button class="btn" >Submit</button>

</form>
<?php if (isset($errors['form'])): ?>
    <p style="color:red"><?= $errors['form'] ?></p>
<?php endif; ?>
    </div>
</body>
</html>