<?php
session_start();
$errors = [];
$email = $password = "";
if($_SERVER['REQUEST_METHOD']=="POST"){
    $email =trim($_POST['email']) ?? '';
    $password=trim($_POST['password']) ?? '';
    //validation
    if($email === ''){
        $errors['email'] = 'email is req';
        
    }
    else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors['email'] = "invalid email format";

    }
    if($password===''){
        $errors['password'] = 'password is req.. ';
    }
  
    if(empty($errors)){
        require_once __DIR__ .'/../config/db.php';
        $checkuser = mysqli_prepare($conn, "SELECT id, email, password, role FROM users WHERE email = ?");
        mysqli_stmt_bind_param($checkuser, "s", $email);
        mysqli_stmt_execute($checkuser);
        $result = mysqli_stmt_get_result($checkuser);
        if(mysqli_num_rows($result)===0){
                //email not found.
                $errors['login'] = 'Invalid email or password';
            
        }
        else{
            $user = mysqli_fetch_assoc($result);
            if(!password_verify($password, $user['password'])){
                $errors['login'] = 'Invalid email or password';
            }
            else{

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role']=$user['role'];
                if($user['role']==='admin'){
                    header("Location: ../admin/admin.php");
                }
                else{
                    header("location: ../dashboard.php");    
                }
                exit;
            }
            
        }
        mysqli_close($conn);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login form </title>
    <link href="../style.css" rel = "stylesheet" >
    <link href = "https://fonts.googleapis.com/css?family=Roboto|Sriracha&display=swap" rel = "stylesheet">
</head>
<body>
    <div class="container">
    <h1>Login</h1>
    <?php if (isset($errors['login'])): ?>
        <p style="color:red"><?= $errors['login'] ?></p>
    <?php endif; ?>
        <form action = "<?=  htmlspecialchars($_SERVER['PHP_SELF'])?>" method = "post">
            <input type = "email" name = "email" value = "<?= htmlspecialchars($email) ?>" placeholder="enter your email..">
            <span style="color:red"><?= $errors['email'] ?? '' ?></span>
            
            <input type = "password" name = "password" placeholder="enter password">
            <span style="color:red"><?= $errors['password'] ?? '' ?></span>
        <button type = "submit">Login</button>
            
            
            

        </form>
    </div>
</body>
</html>