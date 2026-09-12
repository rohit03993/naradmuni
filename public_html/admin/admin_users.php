<?php
/**
 * Admin login accounts: create email/password, role Admin|Author, link Team profile
 * so article byline shows "By {Name} / The Naradmuni".
 */
include "config.php";

if (!function_exists("nm_require_admin")) {
	require_once __DIR__ . "/admin_helpers.php";
}

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
$schemaReady = true;

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

$cols = nm_admin_column_map($con);
if (empty($cols["role"]) || empty($cols["team_id"])) {
	nm_ensure_admin_accounts($con);
	$cols = nm_admin_column_map($con);
}
$schemaReady = !empty($cols["role"]) && !empty($cols["team_id"]);

if (isset($_GET["del"])) {
	$delId = (int) $_GET["del"];
	$selfId = (int) $userRow["id"];
	if ($delId > 0 && $delId !== $selfId) {
		@mysqli_query($con, "DELETE FROM `admin` WHERE `id`='$delId' LIMIT 1");
		$ok = "Login account deleted.";
	} else {
		$err = "You cannot delete your own login.";
	}
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_user"])) {
	if (!$schemaReady) {
		$err = "Database columns role/team_id are missing. Run the ALTER SQL, then try again.";
	} else {
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
			$dup = @mysqli_query($con, $dupSql);
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
					@mysqli_query(
						$con,
						"INSERT INTO `team` (`name`,`email`,`designation`,`image`,`fb_link`,`tw_link`,`short`)
						 VALUES ('$escN','$escA','$escD','$escI','','',0)"
					);
					$teamId = (int) mysqli_insert_id($con);
				} elseif ($profileMode === "link" && $linkTeamId > 0) {
					$teamId = $linkTeamId;
				} elseif ($aid > 0) {
					$prev = @mysqli_query($con, "SELECT `team_id` FROM `admin` WHERE `id`='$aid' LIMIT 1");
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
					$okQ = @mysqli_query(
						$con,
						"UPDATE `admin` SET `aname`='$escName', `aemail`='$escEmail', `role`='$escRole', `team_id`='$teamId' $setPwd WHERE `id`='$aid' LIMIT 1"
					);
					$ok = $okQ ? "Account updated." : ("Save failed: " . mysqli_error($con));
					$editId = $aid;
				} else {
					$escPwd = mysqli_real_escape_string($con, $apwd);
					$okQ = @mysqli_query(
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
}

$editRow = null;
if ($editId > 0) {
	$eq = @mysqli_query($con, "SELECT * FROM `admin` WHERE `id`='$editId' LIMIT 1");
	$editRow = $eq ? mysqli_fetch_assoc($eq) : null;
}

$accounts = array();
if (!empty($cols["team_id"])) {
	$aq = @mysqli_query($con, "SELECT a.*, t.name AS team_name FROM `admin` a LEFT JOIN `team` t ON t.t_id = a.team_id ORDER BY a.id ASC");
} else {
	$aq = @mysqli_query($con, "SELECT a.*, '' AS team_name FROM `admin` a ORDER BY a.id ASC");
}
if ($aq) {
	while ($r = mysqli_fetch_assoc($aq)) {
		$accounts[] = $r;
	}
}

$teams = array();
$tq = @mysqli_query($con, "SELECT `t_id`,`name`,`designation` FROM `team` ORDER BY `name` ASC");
if ($tq) {
	while ($r = mysqli_fetch_assoc($tq)) {
		$teams[] = $r;
	}
}

$form = array(
	"id" => $editRow ? (int) $editRow["id"] : 0,
	"aname" => $editRow ? (string) $editRow["aname"] : "",
	"aemail" => $editRow ? (string) $editRow["aemail"] : "",
	"role" => $editRow && (($editRow["role"] ?? "") === "Author") ? "Author" : "Admin",
	"team_id" => $editRow ? (int) ($editRow["team_id"] ?? 0) : 0,
);

$panel = "padding:18px 20px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;margin:0 0 18px;width:100%;box-sizing:border-box;";
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
  <link rel="stylesheet" href="css/admin-modern.css?v=17">
  <script src="../include/js/jquery.min.js"></script>
  <style>
    /* style.css .card { margin:50% } breaks pages — never use bare .card here */
    .nm-au-wrap { max-width: 980px; }
    .nm-au-wrap h2 { margin: 0 0 6px; font-size: 22px; color: #111; }
    .nm-au-wrap .nm-au-sub { margin: 0 0 18px; color: #6b7280; font-size: 14px; }
    .nm-au-wrap .nm-au-title { margin: 0 0 14px; font-size: 16px; font-weight: 700; color: #111; }
    .nm-au-grid { display: flex; flex-wrap: wrap; gap: 12px 16px; }
    .nm-au-field { flex: 1 1 220px; min-width: 200px; }
    .nm-au-field label { display: block; font-weight: 600; margin-bottom: 6px; color: #111; }
    .nm-au-field .form-control { width: 100%; }
    .nm-au-modes { display: flex; flex-wrap: wrap; gap: 12px 18px; margin-top: 6px; }
    .nm-au-modes label { font-weight: 600; margin: 0; color: #111; }
    .nm-au-actions { margin-top: 14px; display: flex; flex-wrap: wrap; gap: 8px; }
    .nm-au-table { width: 100%; margin: 0; }
    .nm-au-badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
    .nm-au-badge--admin { background: #fee2e2; color: #b91c1c; }
    .nm-au-badge--author { background: #e0f2fe; color: #0369a1; }
  </style>
</head>
<body>
<div class="wrapper">
  <?php include "sidebar.php"; ?>
  <div id="content">
    <?php include "header.php"; ?>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> Admin users</li>
    </ol>
    <div class="container-fluid page-content nm-au-wrap">
      <h2>Admin users</h2>
      <p class="nm-au-sub">Create login email + password. Link a Team profile so articles show photo and &quot;By Name / The Naradmuni&quot;.</p>

      <?php if (!$schemaReady) { ?>
        <div class="alert alert-warning">
          Missing DB columns. Run once in MySQL, then reload:<br>
          <code>ALTER TABLE `admin` ADD COLUMN `role` VARCHAR(20) NOT NULL DEFAULT 'Admin';</code><br>
          <code>ALTER TABLE `admin` ADD COLUMN `team_id` INT(11) NOT NULL DEFAULT 0;</code>
        </div>
      <?php } ?>
      <?php if ($err) { ?><div class="alert alert-danger"><?php echo nm_h($err); ?></div><?php } ?>
      <?php if ($ok) { ?><div class="alert alert-success"><?php echo nm_h($ok); ?></div><?php } ?>

      <div style="<?php echo $panel; ?>">
        <div class="nm-au-title"><?php echo $form["id"] ? "Edit login" : "Create login"; ?></div>
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?php echo (int) $form["id"]; ?>">
          <div class="nm-au-grid">
            <div class="nm-au-field">
              <label for="aname">Display name</label>
              <input class="form-control" id="aname" type="text" name="aname" required value="<?php echo nm_h($form["aname"]); ?>">
            </div>
            <div class="nm-au-field">
              <label for="aemail">Login email (ID)</label>
              <input class="form-control" id="aemail" type="email" name="aemail" required value="<?php echo nm_h($form["aemail"]); ?>">
            </div>
            <div class="nm-au-field">
              <label for="apwd">Password <?php echo $form["id"] ? "(blank = keep)" : ""; ?></label>
              <input class="form-control" id="apwd" type="text" name="apwd" <?php echo $form["id"] ? "" : "required"; ?> autocomplete="new-password">
            </div>
            <div class="nm-au-field">
              <label for="role">Role</label>
              <select class="form-control" id="role" name="role">
                <option value="Admin" <?php echo $form["role"] === "Admin" ? "selected" : ""; ?>>Admin</option>
                <option value="Author" <?php echo $form["role"] === "Author" ? "selected" : ""; ?>>Author (same full CMS access)</option>
              </select>
              <small class="text-muted">Every login gets full CMS access. Link a Team profile for the article byline.</small>
            </div>
          </div>

          <div style="margin-top:14px;">
            <label style="font-weight:600;color:#111;">Public profile (article byline)</label>
            <div class="nm-au-modes">
              <label><input type="radio" name="profile_mode" value="link" checked> Link existing Team</label>
              <label><input type="radio" name="profile_mode" value="new"> Create new Team profile</label>
              <label><input type="radio" name="profile_mode" value="none"> No profile yet</label>
            </div>
          </div>

          <div id="nm-link-wrap" style="margin-top:12px;">
            <div class="nm-au-field" style="max-width:420px;">
              <label for="team_id">Team profile</label>
              <select class="form-control" id="team_id" name="team_id">
                <option value="0">- select -</option>
                <?php foreach ($teams as $t) { ?>
                  <option value="<?php echo (int) $t["t_id"]; ?>" <?php echo ((int) $form["team_id"] === (int) $t["t_id"]) ? "selected" : ""; ?>>
                    <?php echo nm_h($t["name"] . (!empty($t["designation"]) ? (" - " . $t["designation"]) : "")); ?>
                  </option>
                <?php } ?>
              </select>
              <small class="text-muted">Used for By Name + photo on news pages.</small>
            </div>
          </div>

          <div id="nm-new-wrap" style="display:none;margin-top:12px;">
            <div class="nm-au-grid">
              <div class="nm-au-field">
                <label>Profile name</label>
                <input class="form-control" type="text" name="profile_name" value="<?php echo nm_h($form["aname"]); ?>">
              </div>
              <div class="nm-au-field">
                <label>Designation</label>
                <input class="form-control" type="text" name="profile_designation" placeholder="e.g. Special correspondent">
              </div>
              <div class="nm-au-field">
                <label>Photo</label>
                <input class="form-control" type="file" name="profile_image" accept="image/*">
              </div>
              <div class="nm-au-field" style="flex-basis:100%;">
                <label>About (author bio)</label>
                <textarea class="form-control" name="profile_about" rows="2"></textarea>
              </div>
            </div>
          </div>

          <div class="nm-au-actions">
            <button type="submit" name="save_user" class="btn btn-success" <?php echo $schemaReady ? "" : "disabled"; ?>>
              <?php echo $form["id"] ? "Update account" : "Create login"; ?>
            </button>
            <?php if ($form["id"]) { ?>
              <a class="btn btn-default" href="admin_users.php">Cancel</a>
            <?php } ?>
          </div>
        </form>
      </div>

      <div style="<?php echo $panel; ?>">
        <div class="nm-au-title">Login accounts (<?php echo count($accounts); ?>)</div>
        <div class="table-responsive">
          <table class="table table-striped nm-au-table">
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
              <?php if (!count($accounts)) { ?>
                <tr><td colspan="6">No accounts found.</td></tr>
              <?php } ?>
              <?php foreach ($accounts as $i => $a) { ?>
                <tr>
                  <td><?php echo $i + 1; ?></td>
                  <td><?php echo nm_h($a["aname"]); ?></td>
                  <td><?php echo nm_h($a["aemail"]); ?></td>
                  <td>
                    <?php if (($a["role"] ?? "") === "Author") { ?>
                      <span class="nm-au-badge nm-au-badge--author">Author</span>
                    <?php } else { ?>
                      <span class="nm-au-badge nm-au-badge--admin">Admin</span>
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
                      echo "-";
                    }
                    ?>
                  </td>
                  <td style="white-space:nowrap;">
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
    var mode = (document.querySelector('input[name="profile_mode"]:checked') || {}).value || "link";
    document.getElementById("nm-link-wrap").style.display = mode === "link" ? "" : "none";
    document.getElementById("nm-new-wrap").style.display = mode === "new" ? "" : "none";
  }
  document.querySelectorAll('input[name="profile_mode"]').forEach(function (r) {
    r.addEventListener("change", sync);
  });
  sync();
})();
</script>
</body>
</html>
