<?php
include("../includes/auth.php");
include("../includes/db.php");

// Check admin role
if($_SESSION['role'] != 'admin'){
    die("Access Denied");
}

$result = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-dark text-white">

<div class="container mt-5">

<h2>👑 Admin Dashboard</h2>

<a href="../logout.php" class="btn btn-danger mb-3">Logout</a>

<div class="card p-3 text-dark">

<h4>All Users</h4>

<table class="table table-bordered">
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()){ ?>

<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['email']; ?></td>
<td><?php echo $row['role']; ?></td>

<td>
<a href="users.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
</td>
</tr>

<?php } ?>

</table>

</div>

</div>
</body>
</html>