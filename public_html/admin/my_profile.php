<?php
/**
 * Author (or Admin) edits their linked Team public profile — byline photo/name/bio.
 */
include "config.php";
if (!function_exists("nm_admin_row")) {
	require_once __DIR__ . "/admin_helpers.php";
}

if (!isset($_SESSION["aemail"])) {
	$_SESSION["msg"] = "You must log in first";
	header("location: ../manage.php");
	exit;
}

$usersession = $_SESSION["aemail"];
$userRow = function_exists("nm_admin_row") ? nm_admin_row($con, $usersession) : null;
if (!$userRow) {
	$esc = mysqli_real_escape_string($con, $usersession);
	$res = @mysqli_query($con, "SELECT * FROM admin WHERE aemail='$esc' LIMIT 1");
	$userRow = ($res instanceof mysqli_result) ? mysqli_fetch_array($res, MYSQLI_ASSOC) : null;
}
if (!$userRow) {
	header("location: logout.php");
	exit;
}

$teamId = (isset($userRow["team_id"]) && $userRow["team_id"] !== "") ? (int) $userRow["team_id"] : 0;
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
			$cols = function_exists("nm_admin_column_map") ? nm_admin_column_map($con) : array();
			if (!empty($cols["team_id"])) {
				@mysqli_query($con, "UPDATE `admin` SET `team_id`='$teamId' WHERE `id`='$aid' LIMIT 1");
			}
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

      <div class="nm-profile">
        <div class="nm-profile-card">
          <div class="nm-profile-intro">
            <h2>Public author profile</h2>
            <p>This is what readers see under the headline: photo + “By your name” + The Naradmuni.</p>
          </div>
          <form method="post" enctype="multipart/form-data">
            <div class="nm-profile-grid">
              <div class="nm-profile-photo">
                <?php
                $photo = ($team && !empty($team["image"])) ? $team["image"] : "";
                $nameForInitial = trim((string) ($team["name"] ?? $userRow["aname"] ?? "N"));
                $initial = nm_h(function_exists("mb_substr") ? mb_substr($nameForInitial, 0, 1) : substr($nameForInitial, 0, 1));
                if ($photo !== "") {
                  echo '<img src="../team/' . nm_h($photo) . '" alt="">';
                } else {
                  echo '<div class="nm-profile-ph">' . $initial . '</div>';
                }
                ?>
                <label for="nm-profile-image">Photo</label>
                <input class="form-control" id="nm-profile-image" type="file" name="image" accept="image/*">
              </div>
              <div class="nm-profile-fields">
                <div class="form-group">
                  <label for="nm-profile-name">Name</label>
                  <input class="form-control" id="nm-profile-name" type="text" name="name" required value="<?php echo nm_h($team["name"] ?? $userRow["aname"]); ?>">
                </div>
                <div class="form-group">
                  <label for="nm-profile-desig">Designation</label>
                  <input class="form-control" id="nm-profile-desig" type="text" name="designation" value="<?php echo nm_h($team["designation"] ?? ""); ?>">
                </div>
                <div class="form-group">
                  <label for="nm-profile-about">About</label>
                  <textarea class="form-control" id="nm-profile-about" name="email" rows="3"><?php echo nm_h($team["email"] ?? ""); ?></textarea>
                </div>
                <div class="nm-profile-actions">
                  <button type="submit" name="save_profile" class="btn btn-success">Save profile</button>
                  <?php if ($teamId > 0 && $publicBase) { ?>
                    <a class="btn btn-outline-secondary" href="<?php echo nm_h($publicBase . "/author/" . $teamId); ?>" target="_blank" rel="noopener">Preview public page</a>
                  <?php } ?>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php include "footer.php"; ?>
  </div>
</div>
</body>
</html>
