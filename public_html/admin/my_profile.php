<?php
/**
 * Author (or Admin) edits their linked Team public profile — byline photo/name/bio.
 */
include "config.php";

if (!isset($_SESSION["aemail"])) {
	$_SESSION["msg"] = "You must log in first";
	header("location: ../manage.php");
	exit;
}

$usersession = $_SESSION["aemail"];
$userRow = nm_admin_row($con, $usersession);
if (!$userRow) {
	header("location: logout.php");
	exit;
}

$teamId = (int) ($userRow["team_id"] ?? 0);
$err = "";
$ok = "";
$team = null;

if ($teamId > 0) {
	$tq = mysqli_query($con, "SELECT * FROM `team` WHERE `t_id`='$teamId' LIMIT 1");
	$team = $tq ? mysqli_fetch_assoc($tq) : null;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_profile"])) {
	$name = trim((string) ($_POST["name"] ?? ""));
	$designation = trim((string) ($_POST["designation"] ?? ""));
	$about = trim((string) ($_POST["email"] ?? ""));
	if ($name === "") {
		$err = "Name is required.";
	} else {
		$imgSql = "";
		if (!empty($_FILES["image"]["name"]) && (int) $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
			$ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
			if (in_array($ext, array("jpg", "jpeg", "png", "gif", "webp"), true)) {
				$dir = dirname(__DIR__) . "/team";
				if (!is_dir($dir)) {
					@mkdir($dir, 0755, true);
				}
				$fname = bin2hex(random_bytes(8)) . "." . $ext;
				if (move_uploaded_file($_FILES["image"]["tmp_name"], $dir . "/" . $fname)) {
					$imgSql = ", `image`='" . mysqli_real_escape_string($con, $fname) . "'";
				}
			}
		}
		$escN = mysqli_real_escape_string($con, $name);
		$escD = mysqli_real_escape_string($con, $designation);
		$escA = mysqli_real_escape_string($con, $about);
		if ($teamId > 0 && $team) {
			mysqli_query(
				$con,
				"UPDATE `team` SET `name`='$escN', `designation`='$escD', `email`='$escA' $imgSql WHERE `t_id`='$teamId' LIMIT 1"
			);
			$ok = "Profile updated. It shows on your articles as the byline.";
		} else {
			mysqli_query(
				$con,
				"INSERT INTO `team` (`name`,`email`,`designation`,`image`,`fb_link`,`tw_link`,`short`)
				 VALUES ('$escN','$escA','$escD','','','',0)"
			);
			$teamId = (int) mysqli_insert_id($con);
			$aid = (int) $userRow["id"];
			mysqli_query($con, "UPDATE `admin` SET `team_id`='$teamId' WHERE `id`='$aid' LIMIT 1");
			$ok = "Profile created and linked to your login.";
		}
		$tq = mysqli_query($con, "SELECT * FROM `team` WHERE `t_id`='$teamId' LIMIT 1");
		$team = $tq ? mysqli_fetch_assoc($tq) : null;
	}
}

$publicBase = isset($publicroot) ? rtrim($publicroot, "/") : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>My profile</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../include/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/all.min.css">
  <link rel="stylesheet" href="../include/css/style.css">
  <script src="../include/js/jquery.min.js"></script>
</head>
<body>
<div class="wrapper">
  <?php include "sidebar.php"; ?>
  <div id="content">
    <?php include "header.php"; ?>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> My profile</li>
    </ol>
    <div class="container-fluid page-content">
      <?php if ($err) { ?><div class="alert alert-danger"><?php echo nm_h($err); ?></div><?php } ?>
      <?php if ($ok) { ?><div class="alert alert-success"><?php echo nm_h($ok); ?></div><?php } ?>

      <div class="card" style="max-width:720px;">
        <div class="card-header"><strong>Public author profile</strong></div>
        <div class="card-body">
          <p class="text-muted">This is what readers see under the headline: photo + “By your name” + The Naradmuni.</p>
          <?php if ($team && !empty($team["image"])) { ?>
            <p>
              <img src="../team/<?php echo nm_h($team["image"]); ?>" alt="" width="64" height="64" style="border-radius:50%;object-fit:cover;">
            </p>
          <?php } ?>
          <form method="post" enctype="multipart/form-data">
            <div class="form-group">
              <label>Name</label>
              <input class="form-control" type="text" name="name" required value="<?php echo nm_h($team["name"] ?? $userRow["aname"]); ?>">
            </div>
            <div class="form-group">
              <label>Designation</label>
              <input class="form-control" type="text" name="designation" value="<?php echo nm_h($team["designation"] ?? ""); ?>">
            </div>
            <div class="form-group">
              <label>About</label>
              <textarea class="form-control" name="email" rows="3"><?php echo nm_h($team["email"] ?? ""); ?></textarea>
            </div>
            <div class="form-group">
              <label>Photo</label>
              <input class="form-control" type="file" name="image" accept="image/*">
            </div>
            <button type="submit" name="save_profile" class="btn btn-success">Save profile</button>
            <?php if ($teamId > 0 && $publicBase) { ?>
              <a class="btn btn-link" href="<?php echo nm_h($publicBase . "/author/" . $teamId); ?>" target="_blank" rel="noopener">Preview public page</a>
            <?php } ?>
          </form>
        </div>
      </div>
    </div>
    <?php include "footer.php"; ?>
  </div>
</div>
</body>
</html>
