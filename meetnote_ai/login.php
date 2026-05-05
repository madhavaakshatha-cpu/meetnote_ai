<?php
session_start();
include("includes/db.php");

$message = "";

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepared statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $user = $result->fetch_assoc();

        // Verify password
        if(password_verify($password, $user['password'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if($user['role'] == "admin"){
                header("Location: admin/admin_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit();
        } else {
            $message = "Wrong password";
        }

    } else {
        $message = "User not found";
    }
}
?>

<?php include("includes/header.php"); ?>

<div class="row justify-content-center">
<div class="col-md-4">

<div class="card p-4 text-dark shadow">

<h3 class="text-center">Login</h3>

<?php if($message != ""){ ?>
<div class="alert alert-danger"><?php echo $message; ?></div>
<?php } ?>

<form method="POST">

<input type="email" name="email" class="form-control mb-3" placeholder="Enter Email" required>

<input type="password" name="password" class="form-control mb-3" placeholder="Enter Password" required>

<button name="login" class="btn btn-primary w-100">Login</button>

<p class="text-center mt-3">
<a href="register.php">Create Account</a>
</p>

</form>

</div>

</div>
</div>

</div>
</body>
</html>