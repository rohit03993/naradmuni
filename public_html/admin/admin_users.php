<?php
/**
 * Admin login accounts: create email/password, role Admin|Author, link Team profile
 * so article byline shows "By {Name} / The Naradmuni".
 */
include "config.php";

if (!isset($_SESSION["aemail"])) {
	$_SESSION["msg"] = "You must log in first";
	header("location: ../manage.php");
	exit;
}

nm_require_admin($con);

$usersession = $_SESSION["aemail"];
$userRow = nm_admin_row($con, $usersession);
if (!$userRow) {
	header("location: logout.php");
	exit;
}

$err = "";
$ok = "";
$editId = isset($_GET["edit"]) ? (int) $_GET["edit"] : 0;

if (isset($_GET["del"])) {
	$delId = (int) $_GET["del"];
	$selfId = (int) $userRow["id"];
	if ($delId > 0 && $delId !== $selfId) {
		mysqli_query($con, "DELETE FROM `admin` WHERE `id`='$delId' LIMIT 1");
		$ok = "Login account deleted.";
	} else {
		$err = "You cannot delete your own login.";
	}
}

function nm_save_team_image($fileKey)
{
	if (empty($_FILES[$fileKey]["name"]) || (int) $_FILES[$fileKey]["error"] !== UPLOAD_ERR_OK) {
		return "";
	}
	$ext = strtolower(pathinfo($_FILES[$fileKey]["name"], PATHINFO_EXTENSION));
	$allowed = array("jpg", "jpeg", "png", "gif", "webp");
	if (!in_array($ext, $allowed, true)) {
		return "";
	}
	$dir = dirname(__DIR__) . "/team";
	if (!is_dir($dir)) {
		@mkdir($dir, 0755, true);
	}
	$name = bin2hex(random_bytes(8)) . "." . $ext;
	$dest = $dir . "/" . $name;
	if (!move_uploaded_file($_FILES[$fileKey]["tmp_name"], $dest)) {
		return "";
	}
	return $name;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_user"])) {
	$aid = isset($_POST["id"]) ? (int) $_POST["id"] : 0;
	$aname = trim((string) ($_POST["aname"] ?? ""));
	$aemail = trim((string) ($_POST["aemail"] ?? ""));
	$apwd = (string) ($_POST["apwd"] ?? "");
	$role = isset($_POST["role"]) && $_POST["role"] === "Author" ? "Author" : "Admin";
	$profileMode = isset($_POST["profile_mode"]) ? (string) $_POST["profile_mode"] : "link";
	$linkTeamId = isset($_POST["team_id"]) ? (int) $_POST["team_id"] : 0;
	$pname = trim((string) ($_POST["profile_name"] ?? $aname));
	$pdesig = trim((string) ($_POST["profile_designation"] ?? ""));
	$pabout = trim((string) ($_POST["profile_about"] ?? ""));
	$teamId = 0;

	if ($aname === "" || $aemail === "") {
		$err = "Name and email are required.";
	} elseif (!filter_var($aemail, FILTER_VALIDATE_EMAIL)) {
		$err = "Enter a valid email (this is the login ID).";
	} elseif ($aid <= 0 && $apwd === "") {
		$err = "Password is required for new logins.";
	} else {
		$escEmail = mysqli_real_escape_string($con, $aemail);
		$dupSql = "SELECT `id` FROM `admin` WHERE `aemail`='$escEmail' LIMIT 1";
		if ($aid > 0) {
			$dupSql = "SELECT `id` FROM `admin` WHERE `aemail`='$escEmail' AND `id`<>'$aid' LIMIT 1";
		}
		$dup = mysqli_query($con, $dupSql);
		if ($dup && mysqli_num_rows($dup) > 0) {
			$err = "That email/login already exists.";
		} else {
			if ($profileMode === "new") {
				if ($pname === "") {
					$pname = $aname;
				}
				$img = nm_save_team_image("profile_image");
				$escN = mysqli_real_escape_string($con, $pname);
				$escD = mysqli_real_escape_string($con, $pdesig);
				$escA = mysqli_real_escape_string($con, $pabout);
				$escI = mysqli_real_escape_string($con, $img);
				mysqli_query(
					$con,
					"INSERT INTO `team` (`name`,`email`,`designation`,`image`,`fb_link`,`tw_link`,`short`)
					 VALUES ('$escN','$escA','$escD','$escI','','',0)"
				);
				$teamId = (int) mysqli_insert_id($con);
			} elseif ($profileMode === "link" && $linkTeamId > 0) {
				$teamId = $linkTeamId;
			} elseif ($aid > 0) {
				$prev = mysqli_query($con, "SELECT `team_id` FROM `admin` WHERE `id`='$aid' LIMIT 1");
				if ($prev && ($pr = mysqli_fetch_assoc($prev))) {
					$teamId = (int) $pr["team_id"];
				}
			}

			$escName = mysqli_real_escape_string($con, $aname);
			$escRole = mysqli_real_escape_string($con, $role);
			if ($aid > 0) {
				$setPwd = "";
				if ($apwd !== "") {
					$escPwd = mysqli_real_escape_string($con, $apwd);
					$setPwd = ", `apwd`='$escPwd'";
				}
				$okQ = mysqli_query(
					$con,
					"UPDATE `admin` SET `aname`='$escName', `aemail`='$escEmail', `role`='$escRole', `team_id`='$teamId' $setPwd WHERE `id`='$aid' LIMIT 1"
				);
				$ok = $okQ ? "Account updated." : ("Save failed: " . mysqli_error($con));
				$editId = $aid;
			} else {
				$escPwd = mysqli_real_escape_string($con, $apwd);
				$okQ = mysqli_query(
					$con,
					"INSERT INTO `admin` (`aname`,`aemail`,`apwd`,`role`,`team_id`,`image`,`urlroot`)
					 VALUES ('$escName','$escEmail','$escPwd','$escRole','$teamId','','')"
				);
				$ok = $okQ ? "Login created. Share email + password with the user." : ("Save failed: " . mysqli_error($con));
				if ($okQ) {
					$editId = 0;
				}
			}
		}
	}
}

$editRow = null;
if ($editId > 0) {
	$eq = mysqli_query($con, "SELECT * FROM `admin` WHERE `id`='$editId' LIMIT 1");
	$editRow = $eq ? mysqli_fetch_assoc($eq) : null;
}

$accounts = array();
$aq = mysqli_query($con, "SELECT a.*, t.name AS team_name FROM `admin` a LEFT JOIN `team` t ON t.t_id = a.team_id ORDER BY a.id ASC");
if ($aq) {
	while ($r = mysqli_fetch_assoc($aq)) {
		$accounts[] = $r;
	}
}

$teams = array();
$tq = mysqli_query($con, "SELECT `t_id`,`name`,`designation` FROM `team` ORDER BY `name` ASC");
if ($tq) {
	while ($r = mysqli_fetch_assoc($tq)) {
		$teams[] = $r;
	}
}

$form = array(
	"id" => $editRow ? (int) $editRow["id"] : 0,
	"aname" => $editRow ? (string) $editRow["aname"] : "",
	"aemail" => $editRow ? (string) $editRow["aemail"] : "",
	"role" => $editRow && ($editRow["role"] ?? "") === "Author" ? "Author" : "Admin",
	"team_id" => $editRow ? (int) ($editRow["team_id"] ?? 0) : 0,
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin users</title>
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
      <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> Admin users</li>
    </ol>
    <div class="container-fluid page-content">
      <?php if ($err) { ?><div class="alert alert-danger"><?php echo nm_h($err); ?></div><?php } ?>
      <?php if ($ok) { ?><div class="alert alert-success"><?php echo nm_h($ok); ?></div><?php } ?>

      <div class="card" style="margin-bottom:20px;">
        <div class="card-header"><strong><?php echo $form["id"] ? "Edit login" : "Create login"; ?></strong></div>
        <div class="card-body">
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo (int) $form["id"]; ?>">
            <div class="row">
              <div class="col-md-4 form-group">
                <label>Display name</label>
                <input class="form-control" type="text" name="aname" required value="<?php echo nm_h($form["aname"]); ?>">
              </div>
              <div class="col-md-4 form-group">
                <label>Login email (ID)</label>
                <input class="form-control" type="email" name="aemail" required value="<?php echo nm_h($form["aemail"]); ?>">
              </div>
              <div class="col-md-4 form-group">
                <label>Password <?php echo $form["id"] ? "(leave blank to keep)" : ""; ?></label>
                <input class="form-control" type="text" name="apwd" <?php echo $form["id"] ? "" : "required"; ?> autocomplete="new-password">
              </div>
              <div class="col-md-4 form-group">
                <label>Role</label>
                <select class="form-control" name="role">
                  <option value="Admin" <?php echo $form["role"] === "Admin" ? "selected" : ""; ?>>Admin — full CMS</option>
                  <option value="Author" <?php echo $form["role"] === "Author" ? "selected" : ""; ?>>Author — news + own profile</option>
                </select>
              </div>
              <div class="col-md-8 form-group">
                <label>Public profile (article byline)</label>
                <div style="display:flex;flex-wrap:wrap;gap:14px;margin-top:6px;">
                  <label style="font-weight:600;margin:0;">
                    <input type="radio" name="profile_mode" value="link" id="nm-prof-link" checked> Link existing Team
                  </label>
                  <label style="font-weight:600;margin:0;">
                    <input type="radio" name="profile_mode" value="new" id="nm-prof-new"> Create new Team profile
                  </label>
                  <label style="font-weight:600;margin:0;">
                    <input type="radio" name="profile_mode" value="none" id="nm-prof-none"> No profile yet
                  </label>
                </div>
              </div>
              <div class="col-md-6 form-group" id="nm-link-wrap">
                <label>Team profile</label>
                <select class="form-control" name="team_id">
                  <option value="0">— select —</option>
                  <?php foreach ($teams as $t) { ?>
                    <option value="<?php echo (int) $t["t_id"]; ?>" <?php echo ((int) $form["team_id"] === (int) $t["t_id"]) ? "selected" : ""; ?>>
                      <?php echo nm_h($t["name"] . ($t["designation"] ? " — " . $t["designation"] : "")); ?>
                    </option>
                  <?php } ?>
                </select>
                <small class="text-muted">Shows as “By Name” + photo on /news/… pages.</small>
              </div>
              <div class="col-md-12" id="nm-new-wrap" style="display:none;">
                <div class="row">
                  <div class="col-md-4 form-group">
                    <label>Profile name</label>
                    <input class="form-control" type="text" name="profile_name" value="<?php echo nm_h($form["aname"]); ?>">
                  </div>
                  <div class="col-md-4 form-group">
                    <label>Designation</label>
                    <input class="form-control" type="text" name="profile_designation" placeholder="e.g. Special correspondent">
                  </div>
                  <div class="col-md-4 form-group">
                    <label>Photo</label>
                    <input class="form-control" type="file" name="profile_image" accept="image/*">
                  </div>
                  <div class="col-md-12 form-group">
                    <label>About (author bio)</label>
                    <textarea class="form-control" name="profile_about" rows="2"></textarea>
                  </div>
                </div>
              </div>
            </div>
            <button type="submit" name="save_user" class="btn btn-success"><?php echo $form["id"] ? "Update" : "Create login"; ?></button>
            <?php if ($form["id"]) { ?>
              <a class="btn btn-default" href="admin_users.php">Cancel edit</a>
            <?php } ?>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><strong>Login accounts</strong></div>
        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Login email</th>
                <th>Role</th>
                <th>Public profile</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($accounts as $i => $a) { ?>
                <tr>
                  <td><?php echo $i + 1; ?></td>
                  <td><?php echo nm_h($a["aname"]); ?></td>
                  <td><?php echo nm_h($a["aemail"]); ?></td>
                  <td>
                    <?php if (($a["role"] ?? "") === "Author") { ?>
                      <span class="badge badge-info">Author</span>
                    <?php } else { ?>
                      <span class="badge badge-danger">Admin</span>
                    <?php } ?>
                  </td>
                  <td>
                    <?php
                    $tid = (int) ($a["team_id"] ?? 0);
                    if ($tid > 0) {
                      echo nm_h($a["team_name"] ?: ("Team #" . $tid));
                      if (!empty($publicroot)) {
                        echo ' <a href="' . nm_h(rtrim($publicroot, "/") . "/author/" . $tid) . '" target="_blank" rel="noopener">view</a>';
                      }
                    } else {
                      echo "—";
                    }
                    ?>
                  </td>
                  <td>
                    <a class="btn btn-sm btn-primary" href="admin_users.php?edit=<?php echo (int) $a["id"]; ?>">Edit</a>
                    <?php if ((int) $a["id"] !== (int) $userRow["id"]) { ?>
                      <a class="btn btn-sm btn-danger" href="admin_users.php?del=<?php echo (int) $a["id"]; ?>" onclick="return confirm('Delete this login?');">Delete</a>
                    <?php } ?>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <?php include "footer.php"; ?>
  </div>
</div>
<script>
(function () {
  function sync() {
    var mode = (document.querySelector('input[name="profile_mode"]:checked') || {}).value || 'link';
    document.getElementById('nm-link-wrap').style.display = mode === 'link' ? '' : 'none';
    document.getElementById('nm-new-wrap').style.display = mode === 'new' ? '' : 'none';
  }
  document.querySelectorAll('input[name="profile_mode"]').forEach(function (r) {
    r.addEventListener('change', sync);
  });
  sync();
})();
</script>
</body>
</html>
