<?php
include "config.php";
require_once __DIR__ . "/admin_helpers.php";

if (!isset($_SESSION["aemail"])) {
	$_SESSION["msg"] = "You must log in first";
	header("location: ../manage.php");
	exit;
}

nm_require_admin($con);

$usersession = $_SESSION["aemail"];
$userRow = nm_admin_row($con, $usersession);

$srid = isset($_GET["eid"]) ? (int) $_GET["eid"] : (isset($_GET["id"]) ? (int) $_GET["id"] : 0);
$rs = array();
if ($srid > 0) {
	$ex = mysqli_query($con, "SELECT * FROM `pages` WHERE `p_id`='$srid' LIMIT 1");
	if ($ex instanceof mysqli_result) {
		$row = mysqli_fetch_array($ex, MYSQLI_ASSOC);
		if (is_array($row)) {
			$rs = $row;
		}
	}
}
if (!$rs) {
	nm_js_notice("Page not found.", "pages.php", "error");
}

$err = "";
$ok = "";

if (isset($_POST["update"])) {
	$page = trim((string) ($_POST["page"] ?? ""));
	$description = (string) ($_POST["description"] ?? "");
	$metat = trim((string) ($_POST["metat"] ?? ""));
	$metad = trim((string) ($_POST["metad"] ?? ""));
	$lockedUrl = (string) $rs["page_url"];

	if ($page === "") {
		$err = "Page name is required.";
	} elseif (trim(strip_tags($description)) === "") {
		$err = "Add the page content.";
	} else {
		$pageEsc = mysqli_real_escape_string($con, $page);
		$descEsc = mysqli_real_escape_string($con, $description);
		$metatEsc = mysqli_real_escape_string($con, $metat);
		$metadEsc = mysqli_real_escape_string($con, $metad);
		$up = "UPDATE `pages` SET `page`='$pageEsc', `description`='$descEsc', `metat`='$metatEsc', `metad`='$metadEsc' WHERE `p_id`='$srid' LIMIT 1";
		if (mysqli_query($con, $up)) {
			nm_js_notice("Page updated. Public site shows this within about 1 minute.", "pages.php");
		} else {
			$err = "Could not save. Try again.";
		}
		$rs["page"] = $page;
		$rs["description"] = $description;
		$rs["metat"] = $metat;
		$rs["metad"] = $metad;
		$rs["page_url"] = $lockedUrl;
	}
}

$slug = (string) $rs["page_url"];
$live = "/page/" . rawurlencode($slug);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Edit page — Admin</title>
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
      <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> <a href="pages.php">Pages</a> <i class="fa fa-angle-right"></i> Edit</li>
    </ol>
    <div class="container-fluid page-content nm-pages nm-pages-edit">
      <div class="nm-pages-head">
        <div>
          <h2>Edit <?php echo nm_h($rs["page"]); ?></h2>
          <p>Replace the text below. The live address stays <strong>/page/<?php echo nm_h($slug); ?></strong> so Google rankings are not affected.</p>
        </div>
        <a class="btn btn-outline-secondary" href="<?php echo nm_h($live); ?>" target="_blank" rel="noopener">View live</a>
      </div>
      <?php if ($err) { ?><div class="alert alert-danger"><?php echo nm_h($err); ?></div><?php } ?>
      <?php if ($ok) { ?><div class="alert alert-success"><?php echo nm_h($ok); ?></div><?php } ?>

      <form method="post">
        <div class="form-group">
          <label>Public URL (locked)</label>
          <input class="form-control" type="text" value="/page/<?php echo nm_h($slug); ?>" readonly>
        </div>
        <div class="form-group">
          <label for="nm-page-name">Page name (footer + headline)</label>
          <input class="form-control" id="nm-page-name" type="text" name="page" required value="<?php echo nm_h($rs["page"]); ?>">
        </div>
        <div class="form-group">
          <label for="nm-page-metat">SEO title</label>
          <input class="form-control" id="nm-page-metat" type="text" name="metat" value="<?php echo nm_h($rs["metat"]); ?>">
        </div>
        <div class="form-group">
          <label for="nm-page-metad">SEO description</label>
          <textarea class="form-control" id="nm-page-metad" name="metad" rows="3"><?php echo nm_h($rs["metad"]); ?></textarea>
        </div>
        <div class="form-group">
          <label for="description">Page content</label>
          <textarea class="ckeditor form-control" id="description" name="description"><?php echo nm_h($rs["description"]); ?></textarea>
        </div>
        <div class="nm-page-actions">
          <button type="submit" name="update" class="btn btn-success">Save page</button>
          <a class="btn btn-outline-secondary" href="pages.php">Back to list</a>
        </div>
      </form>
    </div>
    <?php include "footer.php"; ?>
  </div>
</div>
<script src="ckeditor/ckeditor.js"></script>
<script><?php echo nm_ckeditor_js("description"); ?></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="../include/js/bootstrap.min.js"></script>
</body>
</html>
