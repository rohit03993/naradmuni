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

function nm_new_page_slug($raw) {
	$s = strtolower(trim((string) $raw));
	$s = preg_replace("/[^a-z0-9]+/", "-", $s);
	return trim((string) $s, "-");
}

$err = "";
$form = array("page" => "", "page_url" => "", "metat" => "", "metad" => "", "description" => "");

if (isset($_POST["add"])) {
	$form["page"] = trim((string) ($_POST["page"] ?? ""));
	$form["page_url"] = trim((string) ($_POST["page_url"] ?? ""));
	$form["metat"] = trim((string) ($_POST["metat"] ?? ""));
	$form["metad"] = trim((string) ($_POST["metad"] ?? ""));
	$form["description"] = (string) ($_POST["description"] ?? "");
	$slug = nm_new_page_slug($form["page_url"] !== "" ? $form["page_url"] : $form["page"]);

	if ($form["page"] === "") {
		$err = "Page name is required.";
	} elseif ($slug === "") {
		$err = "Give a URL slug such as about-us.";
	} elseif (trim(strip_tags($form["description"])) === "") {
		$err = "Add the page content.";
	} else {
		$slugEsc = mysqli_real_escape_string($con, $slug);
		$dup = mysqli_query($con, "SELECT `p_id` FROM `pages` WHERE `page_url`='$slugEsc' LIMIT 1");
		if ($dup instanceof mysqli_result && mysqli_num_rows($dup) > 0) {
			$err = "That URL is already used. Pick a different slug.";
		} else {
			$pageEsc = mysqli_real_escape_string($con, $form["page"]);
			$descEsc = mysqli_real_escape_string($con, $form["description"]);
			$metatEsc = mysqli_real_escape_string($con, $form["metat"]);
			$metadEsc = mysqli_real_escape_string($con, $form["metad"]);
			$okIns = mysqli_query(
				$con,
				"INSERT INTO `pages` (`page`, `description`, `page_url`, `metat`, `metad`) VALUES ('$pageEsc','$descEsc','$slugEsc','$metatEsc','$metadEsc')"
			);
			if ($okIns) {
				nm_js_notice("Page added.", "pages.php");
			} else {
				$err = "Could not save. Try again.";
			}
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Add page — Admin</title>
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
      <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> <a href="pages.php">Pages</a> <i class="fa fa-angle-right"></i> Add</li>
    </ol>
    <div class="container-fluid page-content nm-pages nm-pages-edit">
      <div class="nm-pages-head">
        <div>
          <h2>Add a site page</h2>
          <p>Use this for a new footer page only. Existing About / Contact URLs stay on their current slug.</p>
        </div>
      </div>
      <?php if ($err) { ?><div class="alert alert-danger"><?php echo nm_h($err); ?></div><?php } ?>

      <form method="post">
        <div class="form-group">
          <label for="nm-page-name">Page name</label>
          <input class="form-control" id="nm-page-name" type="text" name="page" required value="<?php echo nm_h($form["page"]); ?>" placeholder="Contact Us">
        </div>
        <div class="form-group">
          <label for="nm-page-url">URL slug</label>
          <input class="form-control" id="nm-page-url" type="text" name="page_url" value="<?php echo nm_h($form["page_url"]); ?>" placeholder="contact-us">
          <small class="text-muted">Public address will be /page/your-slug — do not copy an existing slug.</small>
        </div>
        <div class="form-group">
          <label for="nm-page-metat">SEO title</label>
          <input class="form-control" id="nm-page-metat" type="text" name="metat" value="<?php echo nm_h($form["metat"]); ?>">
        </div>
        <div class="form-group">
          <label for="nm-page-metad">SEO description</label>
          <textarea class="form-control" id="nm-page-metad" name="metad" rows="3"><?php echo nm_h($form["metad"]); ?></textarea>
        </div>
        <div class="form-group">
          <label for="description">Page content</label>
          <textarea class="ckeditor form-control" id="description" name="description"><?php echo nm_h($form["description"]); ?></textarea>
        </div>
        <div class="nm-page-actions">
          <button type="submit" name="add" class="btn btn-success">Add page</button>
          <a class="btn btn-outline-secondary" href="pages.php">Cancel</a>
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
