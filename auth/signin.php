<?php
session_start();
$errors = [];
$email = $password = "";
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
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
        $checkuser = mysqli_prepare($conn, "SELECT id, email, password, role_id, tenant_id FROM users WHERE email = ?");
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
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['tenant_id'] = $user['tenant_id'];


                /* Fetch Permissions */

                $sql = "
                SELECT p.name 
                FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = ?";

                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $user['role_id']);
                mysqli_stmt_execute($stmt);
                $permResult = mysqli_stmt_get_result($stmt);

                $permissions = [];

                while ($row = mysqli_fetch_assoc($permResult)) {
                $permissions[] = $row['name'];
                }

                $_SESSION['permissions'] = $permissions;
                
               if (in_array('view_users', $_SESSION['permissions'])) {
                header("Location: ../admin/admin.php");
                } 
                else{
                    header("Location: ../dashboard.php");
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