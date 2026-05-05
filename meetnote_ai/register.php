<?php
include("includes/db.php");

if(isset($_POST['register'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $cpass = $_POST['confirm_password'];

    if($pass != $cpass){
        echo "Passwords do not match";
    } else {
        $password = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
        $stmt->bind_param("sss",$name,$email,$password);

        if($stmt->execute()){
            header("Location: login.php");
        } else {
            echo "Email already exists";
        }
    }
}
?>

<?php include("includes/header.php"); ?>

<div class="row justify-content-center">
<div class="col-md-4">
<div class="card p-4 text-dark">

<h3>Register</h3>

<form method="POST">
<input type="text" name="name" class="form-control mb-2" placeholder="Name" required>
<input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
<input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
<input type="password" name="confirm_password" class="form-control mb-2" placeholder="Confirm Password" required>

<button name="register" class="btn btn-success w-100">Register</button>
</form>

</div>
</div>
</div>

</div>
</body>
</html>