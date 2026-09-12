<?php
include "config.php";
require_once __DIR__ . "/site_settings_lib.php";

nm_require_admin($con);

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

$DEFAULTS = array(
	"wa_share_invite_text" => "मध्य प्रदेश एवं छत्तीसगढ़ समेत देश-विदेश की तमाम खबर पाने के लिए द नारदमुनि से अभी जुड़ें",
	"wa_share_group_link" => "https://chat.whatsapp.com/BkZoIpOAGBS6YFMSn2xSoM",
	"wa_share_app_text" => "देश दुनिया की खबर पाने के लिए अभी डाउनलोड करें द नारदमुनि एप\n\nDownload The TheNaradMuni App",
	"wa_share_app_link" => "http://onelink.to/kqnpym",
);

$msg = "";
$err = "";

if (isset($_POST["reset_wa_share"])) {
	$ok = true;
	foreach ($DEFAULTS as $key => $val) {
		$ok = $ok && nm_setting_set($con, $key, "");
	}
	$msg = $ok ? "Reset to built-in defaults." : "Could not reset settings.";
}

if (isset($_POST["save_wa_share"])) {
	$invite = trim((string) ($_POST["wa_share_invite_text"] ?? ""));
	$group = trim((string) ($_POST["wa_share_group_link"] ?? ""));
	$appText = trim((string) ($_POST["wa_share_app_text"] ?? ""));
	$appLink = trim((string) ($_POST["wa_share_app_link"] ?? ""));

	if ($group !== "" && !preg_match('#^https?://#i', $group)) {
		$err = "WhatsApp group link should start with https://";
	} elseif ($appLink !== "" && !preg_match('#^https?://#i', $appLink)) {
		$err = "App download link should start with http:// or https://";
	} else {
		$ok = nm_setting_set($con, "wa_share_invite_text", $invite)
			&& nm_setting_set($con, "wa_share_group_link", $group)
			&& nm_setting_set($con, "wa_share_app_text", $appText)
			&& nm_setting_set($con, "wa_share_app_link", $appLink);
		if ($ok) {
			$msg = "WhatsApp share text saved. Public articles pick this up within about 1 minute (or after Next restart).";
		} else {
			$err = "Could not save settings. Check DB permissions.";
		}
	}
}

function nm_wa_val($con, $key, $defaults) {
	$v = nm_setting_get($con, $key, "");
	return $v !== "" ? $v : $defaults[$key];
}

$invite = nm_wa_val($con, "wa_share_invite_text", $DEFAULTS);
$group = nm_wa_val($con, "wa_share_group_link", $DEFAULTS);
$appText = nm_wa_val($con, "wa_share_app_text", $DEFAULTS);
$appLink = nm_wa_val($con, "wa_share_app_link", $DEFAULTS);

$previewParts = array();
if (trim($invite) !== "") {
	$previewParts[] = $invite;
}
if (trim($group) !== "") {
	$previewParts[] = $group;
}
$appBlock = trim(implode("\n", array_filter(array(trim($appText), trim($appLink)))));
if ($appBlock !== "") {
	$previewParts[] = $appBlock;
}
$previewFooter = implode("\n\n", $previewParts);
$sampleTitle = "Sample news headline — उदाहरण समाचार शीर्षक";
$sampleUrl = rtrim((string) $publicroot, "/") . "/news/example-story-slug";
$fullPreview = $sampleTitle . "\n" . $sampleUrl . "\n\n" . $previewFooter;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>WhatsApp share — Admin</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../include/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/all.min.css">
  <link rel="stylesheet" href="../include/css/style.css">
  <link rel="stylesheet" href="css/admin-modern.css?v=16">
  <script src="../include/js/jquery.min.js"></script>
  <style>
    .wa-preview {
      background: #e5ddd5;
      border-radius: 12px;
      padding: 16px;
      font-family: system-ui, Segoe UI, sans-serif;
    }
    .wa-bubble {
      background: #dcf8c6;
      border-radius: 8px;
      padding: 12px 14px;
      white-space: pre-wrap;
      word-break: break-word;
      font-size: 14px;
      line-height: 1.45;
      color: #111;
      box-shadow: 0 1px 1px rgba(0,0,0,.08);
      max-width: 100%;
    }
    .wa-field-hint { color: #6b7280; font-size: 12px; margin-top: 4px; }
  </style>
</head>
<body>
<div class="wrapper">
  <?php include "sidebar.php"; ?>
  <div id="content">
    <?php include "header.php"; ?>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> WhatsApp share</li>
    </ol>
    <div class="container-fluid page-content" style="max-width:820px;">
      <h2 style="margin-top:0;"><i class="fab fa-whatsapp" style="color:#25D366;"></i> WhatsApp article share</h2>
      <p class="text-muted">
        When readers tap WhatsApp on an article, the message is:
        <strong>title</strong> + <strong>article link</strong> + the text and links you set below.
      </p>

      <?php if ($msg) { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
      <?php } ?>
      <?php if ($err) { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
      <?php } ?>

      <div class="row">
        <div class="col-md-7">
          <form method="post" class="card" style="padding:20px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;">
            <div class="form-group">
              <label for="wa_share_invite_text">Invite / community text</label>
              <textarea class="form-control" id="wa_share_invite_text" name="wa_share_invite_text" rows="3"><?php echo htmlspecialchars($invite); ?></textarea>
              <p class="wa-field-hint">Hindi/English lines inviting readers to join Naradmuni.</p>
            </div>

            <div class="form-group">
              <label for="wa_share_group_link">WhatsApp group / community link</label>
              <input type="url" class="form-control" id="wa_share_group_link" name="wa_share_group_link" value="<?php echo htmlspecialchars($group); ?>" placeholder="https://chat.whatsapp.com/...">
              <p class="wa-field-hint">Full https://chat.whatsapp.com/... link.</p>
            </div>

            <div class="form-group">
              <label for="wa_share_app_text">App download text</label>
              <textarea class="form-control" id="wa_share_app_text" name="wa_share_app_text" rows="3"><?php echo htmlspecialchars($appText); ?></textarea>
              <p class="wa-field-hint">Lines shown above the app store / OneLink URL.</p>
            </div>

            <div class="form-group">
              <label for="wa_share_app_link">App download link</label>
              <input type="url" class="form-control" id="wa_share_app_link" name="wa_share_app_link" value="<?php echo htmlspecialchars($appLink); ?>" placeholder="https://onelink.to/... or Play Store URL">
            </div>

            <div style="display:flex;flex-wrap:wrap;gap:8px;">
              <button type="submit" name="save_wa_share" value="1" class="btn btn-success">
                <i class="fab fa-whatsapp"></i> Save WhatsApp share
              </button>
              <button type="submit" name="reset_wa_share" value="1" class="btn btn-outline-secondary" onclick="return confirm('Reset to default WhatsApp share text and links?');">
                Reset defaults
              </button>
            </div>
          </form>
        </div>

        <div class="col-md-5">
          <div class="card" style="padding:16px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;margin-bottom:16px;">
            <h3 style="margin:0 0 10px;font-size:15px;">Live preview</h3>
            <div class="wa-preview">
              <div class="wa-bubble"><?php echo htmlspecialchars($fullPreview); ?></div>
            </div>
            <p class="wa-field-hint" style="margin-top:10px;">Title and article URL are always added automatically for each story.</p>
          </div>
        </div>
      </div>

      <div class="alert alert-info" style="margin-top:8px;">
        After saving, wait up to <strong>1 minute</strong> or restart Next once, then open any article and tap WhatsApp to verify.
      </div>
    </div>
    <?php include "footer.php"; ?>
  </div>
</div>
</body>
</html>
