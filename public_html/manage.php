<?php
include "admin/config.php";

// Already logged in → dashboard (single admin app)
if (isset($_SESSION["aemail"]) && !isset($_POST["login_admin"])) {
	header("Location: " . $urlroot . "admin/dashboard.php");
	exit;
}

if (isset($_POST["login_admin"])) {
	$aemail = mysqli_real_escape_string($con, $_POST["aemail"]);
	$apwd_1 = mysqli_real_escape_string($con, $_POST["apwd"]);

	if (empty($aemail)) {
		array_push($errors, "User ID is required");
	}
	if (empty($apwd_1)) {
		array_push($errors, "Password is required");
	}
	if (count($errors) == 0) {
		$apwd = $apwd_1;
		$log = "SELECT * FROM admin WHERE aemail='$aemail' AND apwd='$apwd'";
		$results = mysqli_query($con, $log);

		if (mysqli_num_rows($results) == 1) {
			$time1 = "10:00:00";
			$time2 = "18:00:00";
			$startTime = strtotime($time1);
			$endTime = strtotime($time2);
			$diff = $startTime - $endTime;
			$time3 = abs($diff);
			$duration = $time3 / 60;

			$_SESSION["duration"] = "$duration";
			$_SESSION["start_time"] = date("Y-m-d H:i:s");
			$_SESSION["end_time"] = date("Y-m-d H:i:s", strtotime("+" . $_SESSION["duration"] . "minutes", strtotime($_SESSION["start_time"])));
			$_SESSION["timestamp"] = time();
			$_SESSION["aemail"] = $aemail;
			$_SESSION["success"] = "You are now logged in";
			header("Location: " . $urlroot . "admin/dashboard.php");
			exit;
		}
		array_push($errors, "Wrong User ID/Password combination");
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Naradmuni Admin Login</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="include/css/bootstrap.min.css">
  <link rel="stylesheet" href="include/css/font-awesome.css">
  <link rel="stylesheet" href="admin/css/admin-modern.css?v=3">
  <script src="include/js/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="include/js/bootstrap.min.js"></script>
</head>
<body class="nm-login">
  <div class="nm-login-card card">
    <div class="card-header">
      <h2>Naradmuni</h2>
      <p>Admin CMS · one login</p>
    </div>
    <div class="card-body">
      <form method="post">
        <div class="message"><?php include("errors.php"); ?></div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" name="aemail" class="form-control" id="email" required autocomplete="username">
        </div>
        <div class="form-group">
          <label for="pwd">Password</label>
          <input type="password" name="apwd" class="form-control" id="pwd" required autocomplete="current-password">
        </div>
        <button type="submit" name="login_admin" class="btn btn-primary">Sign in</button>
      </form>
    </div>
  </div>
</body>
</html>
