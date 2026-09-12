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
$userRow = $res ? mysqli_fetch_array($res, MYSQLI_ASSOC) : null;

nm_ensure_site_settings($con);

$DEFAULT_LOGO = "Logo @2x.png";
$logoDir = realpath(__DIR__ . "/../images/logo");
if ($logoDir === false) {
	@mkdir(__DIR__ . "/../images/logo", 0755, true);
	$logoDir = realpath(__DIR__ . "/../images/logo");
}

$msg = "";
$err = "";

function nm_brand_safe_ext($name, $allowed) {
	$ext = strtolower(pathinfo((string) $name, PATHINFO_EXTENSION));
	return in_array($ext, $allowed, true) ? $ext : "";
}

function nm_brand_upload($field, $prefix, $allowed, $logoDir, &$err) {
	if (empty($_FILES[$field]["name"]) || (string) $_FILES[$field]["name"] === "") {
		return null;
	}
	if (empty($_FILES[$field]["tmp_name"]) || !is_uploaded_file($_FILES[$field]["tmp_name"])) {
		$err = "Upload failed for " . $field . ".";
		return false;
	}
	if (!empty($_FILES[$field]["error"]) && (int) $_FILES[$field]["error"] !== UPLOAD_ERR_OK) {
		$err = "Upload failed for " . $field . " (error " . (int) $_FILES[$field]["error"] . ").";
		return false;
	}
	$size = (int) ($_FILES[$field]["size"] ?? 0);
	if ($size <= 0 || $size > 2 * 1024 * 1024) {
		$err = "Each file must be under 2 MB.";
		return false;
	}
	$ext = nm_brand_safe_ext($_FILES[$field]["name"], $allowed);
	if ($ext === "") {
		$err = "Allowed types: " . implode(", ", $allowed);
		return false;
	}
	if ($logoDir === false || !is_dir($logoDir) || !is_writable($logoDir)) {
		$err = "Logo folder is not writable: images/logo/";
		return false;
	}
	$filename = $prefix . "-" . date("YmdHis") . "-" . substr(md5(uniqid((string) mt_rand(), true)), 0, 8) . "." . $ext;
	$dest = $logoDir . DIRECTORY_SEPARATOR . $filename;
	if (!move_uploaded_file($_FILES[$field]["tmp_name"], $dest)) {
		$err = "Could not save uploaded file.";
		return false;
	}
	@chmod($dest, 0644);
	return $filename;
}

if (isset($_POST["reset_branding"])) {
	$ok = nm_setting_set($con, "brand_logo", "") && nm_setting_set($con, "brand_favicon", "");
	$msg = $ok ? "Reset to default logo and favicon." : "Could not reset settings.";
}

if (isset($_POST["save_branding"])) {
	$logoFile = nm_brand_upload("brand_logo", "brand-logo", array("png", "jpg", "jpeg", "webp", "svg"), $logoDir, $err);
	$favFile = nm_brand_upload("brand_favicon", "brand-favicon", array("png", "ico", "jpg", "jpeg", "webp"), $logoDir, $err);

	if ($err === "") {
		$ok = true;
		if ($logoFile) {
			$ok = $ok && nm_setting_set($con, "brand_logo", $logoFile);
		}
		if ($favFile) {
			$ok = $ok && nm_setting_set($con, "brand_favicon", $favFile);
		}
		if (!$logoFile && !$favFile) {
			$err = "Choose a logo and/or favicon file to upload.";
		} elseif ($ok) {
			$msg = "Branding saved. Public site picks this up within about 1 minute (or after Next restart).";
		} else {
			$err = "Could not save settings. Check DB permissions.";
		}
	}
}

$brandLogo = nm_setting_get($con, "brand_logo", "");
$brandFavicon = nm_setting_get($con, "brand_favicon", "");
$activeLogo = $brandLogo !== "" ? $brandLogo : $DEFAULT_LOGO;
$logoPreview = "../images/logo/" . rawurlencode($activeLogo);
$favPreview = $brandFavicon !== "" ? ("../images/logo/" . rawurlencode($brandFavicon)) : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Branding — Admin</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../include/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/all.min.css">
  <link rel="stylesheet" href="../include/css/style.css">
  <link rel="stylesheet" href="css/admin-modern.css?v=16">
  <script src="../include/js/jquery.min.js"></script>
</head>
<body>
<div class="wrapper">
  <?php include "sidebar.php"; ?>
  <div id="content">
    <?php include "header.php"; ?>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> Branding</li>
    </ol>
    <div class="container-fluid page-content" style="max-width:720px;">
      <h2 style="margin-top:0;">Logo &amp; favicon</h2>
      <p class="text-muted">
        Upload a new header logo and browser favicon. Files stay under
        <code>/images/logo/</code> (same public path as today). No article URLs change.
      </p>

      <?php if ($msg) { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
      <?php } ?>
      <?php if ($err) { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
      <?php } ?>

      <div class="card" style="padding:20px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;margin-bottom:16px;">
        <h3 style="margin-top:0;font-size:16px;">Current</h3>
        <div style="display:flex;flex-wrap:wrap;gap:24px;align-items:flex-start;">
          <div>
            <div class="text-muted" style="font-size:12px;margin-bottom:6px;">Logo</div>
            <div style="background:#f8f9fa;border:1px solid #e5e7eb;border-radius:8px;padding:12px;min-width:180px;">
              <img src="<?php echo htmlspecialchars($logoPreview); ?>?t=<?php echo time(); ?>" alt="Logo" style="max-height:64px;max-width:220px;display:block;">
            </div>
            <code style="font-size:11px;"><?php echo htmlspecialchars($activeLogo); ?></code>
          </div>
          <div>
            <div class="text-muted" style="font-size:12px;margin-bottom:6px;">Favicon</div>
            <div style="background:#f8f9fa;border:1px solid #e5e7eb;border-radius:8px;padding:12px;min-width:80px;min-height:64px;display:flex;align-items:center;justify-content:center;">
              <?php if ($favPreview) { ?>
                <img src="<?php echo htmlspecialchars($favPreview); ?>?t=<?php echo time(); ?>" alt="Favicon" style="width:48px;height:48px;object-fit:contain;">
              <?php } else { ?>
                <span class="text-muted" style="font-size:12px;">Site default</span>
              <?php } ?>
            </div>
            <code style="font-size:11px;"><?php echo $brandFavicon !== "" ? htmlspecialchars($brandFavicon) : "web/public/favicon.png"; ?></code>
          </div>
        </div>
      </div>

      <form method="post" enctype="multipart/form-data" class="card" style="padding:20px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;">
        <div class="form-group">
          <label for="brand_logo">Header logo</label>
          <input type="file" class="form-control" id="brand_logo" name="brand_logo" accept=".png,.jpg,.jpeg,.webp,.svg,image/*">
          <small class="form-text text-muted">PNG/JPG/WebP/SVG · max 2 MB · recommended ~240×60 or @2x.</small>
        </div>
        <div class="form-group">
          <label for="brand_favicon">Favicon</label>
          <input type="file" class="form-control" id="brand_favicon" name="brand_favicon" accept=".png,.ico,.jpg,.jpeg,.webp,image/*">
          <small class="form-text text-muted">PNG or ICO · max 2 MB · square 32×32 or 512×512 works best.</small>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
          <button type="submit" name="save_branding" value="1" class="btn btn-danger">Save branding</button>
          <button type="submit" name="reset_branding" value="1" class="btn btn-outline-secondary" onclick="return confirm('Reset to default logo and favicon?');">Reset defaults</button>
        </div>
      </form>

      <div class="alert alert-info" style="margin-top:16px;">
        After saving, wait up to <strong>1 minute</strong> or restart Next once, then hard-refresh the public site.
      </div>
    </div>
    <?php include "footer.php"; ?>
  </div>
</div>
</body>
</html>
