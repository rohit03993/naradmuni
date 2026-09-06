<?php
include "config.php";
require_once __DIR__ . "/site_settings_lib.php";

if (!isset($_SESSION["aemail"])) {
	$_SESSION["msg"] = "You must log in first";
	header("location: ../manage.php");
	exit;
}

if (isset($_GET["logout"])) {
	session_destroy();
	unset($_SESSION["aemail"]);
	header("location: ../manage.php");
	exit;
}

$usersession = $_SESSION["aemail"];
$res = mysqli_query($con, "SELECT * FROM admin WHERE aemail='" . mysqli_real_escape_string($con, $usersession) . "'");
$userRow = mysqli_fetch_array($res, MYSQLI_ASSOC);

nm_ensure_site_settings($con);

$msg = "";
$err = "";

if (isset($_POST["save_shorts"])) {
	$enabled = isset($_POST["shorts_enabled"]) ? "1" : "0";
	$apiKey = trim((string) ($_POST["youtube_api_key"] ?? ""));
	$channel = trim((string) ($_POST["youtube_channel"] ?? ""));
	$count = (int) ($_POST["shorts_count"] ?? 8);
	if ($count < 1) {
		$count = 1;
	}
	if ($count > 16) {
		$count = 16;
	}

	$ok = nm_setting_set($con, "shorts_enabled", $enabled)
		&& nm_setting_set($con, "youtube_api_key", $apiKey)
		&& nm_setting_set($con, "youtube_channel", $channel)
		&& nm_setting_set($con, "shorts_count", (string) $count);

	if ($ok) {
		$msg = "YouTube Shorts settings saved. Homepage refreshes Shorts about every 10 minutes.";
	} else {
		$err = "Could not save settings. Check DB permissions.";
	}
}

$shortsEnabled = nm_setting_get($con, "shorts_enabled", "0") === "1";
$apiKey = nm_setting_get($con, "youtube_api_key", "");
$channel = nm_setting_get($con, "youtube_channel", "");
$count = (int) nm_setting_get($con, "shorts_count", "8");
if ($count < 1) {
	$count = 8;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>YouTube Shorts — Admin</title>
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
      <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> YouTube Shorts</li>
    </ol>
    <div class="container-fluid page-content" style="max-width:720px;">
      <h2 style="margin-top:0;">YouTube Shorts on homepage</h2>
      <p class="text-muted">
        Show latest Shorts from your channel above <strong>ताज़ा समाचार</strong>.
        The public site caches results for about <strong>10 minutes</strong>, then picks up new Shorts automatically.
      </p>

      <?php if ($msg) { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
      <?php } ?>
      <?php if ($err) { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
      <?php } ?>

      <form method="post" class="card" style="padding:20px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;">
        <div class="form-group form-check">
          <input type="checkbox" class="form-check-input" id="shorts_enabled" name="shorts_enabled" value="1" <?php echo $shortsEnabled ? "checked" : ""; ?>>
          <label class="form-check-label" for="shorts_enabled">Enable Shorts strip on homepage</label>
        </div>

        <div class="form-group">
          <label for="youtube_channel">YouTube channel</label>
          <input type="text" class="form-control" id="youtube_channel" name="youtube_channel" value="<?php echo htmlspecialchars($channel); ?>" placeholder="@yourchannel or https://youtube.com/@yourchannel or channel ID (UCxxxx)">
          <small class="form-text text-muted">Handle, full channel URL, or Channel ID starting with UC.</small>
        </div>

        <div class="form-group">
          <label for="youtube_api_key">YouTube Data API key</label>
          <input type="password" class="form-control" id="youtube_api_key" name="youtube_api_key" value="<?php echo htmlspecialchars($apiKey); ?>" autocomplete="off" placeholder="AIza...">
          <small class="form-text text-muted">
            Create once in Google Cloud → enable <em>YouTube Data API v3</em> → create API key.
            Only admins see this page.
          </small>
        </div>

        <div class="form-group">
          <label for="shorts_count">How many Shorts to show</label>
          <input type="number" class="form-control" id="shorts_count" name="shorts_count" min="1" max="16" value="<?php echo (int) $count; ?>">
        </div>

        <button type="submit" name="save_shorts" value="1" class="btn btn-danger">Save settings</button>
      </form>

      <div class="alert alert-info" style="margin-top:16px;">
        <strong>Tip:</strong> After saving, wait up to 10 minutes (or restart Next once) to see changes on
        <a href="<?php echo htmlspecialchars($publicroot); ?>" target="_blank" rel="noreferrer"><?php echo htmlspecialchars($publicroot); ?></a>.
      </div>
    </div>
    <?php include "footer.php"; ?>
  </div>
</div>
</body>
</html>
