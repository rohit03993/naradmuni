<?php
include "config.php";
require_once __DIR__ . "/admin_helpers.php";

if (!isset($_SESSION["aemail"])) {
	$_SESSION["msg"] = "You must log in first";
	header("location: ../manage.php");
	exit;
}

nm_require_admin($con);
nm_ensure_cms_pages($con);

$usersession = $_SESSION["aemail"];
$userRow = nm_admin_row($con, $usersession);

function nm_page_is_ads_txt($url, $title) {
	$s = strtolower(str_replace("_", "-", (string) $url . " " . (string) $title));
	return (bool) preg_match("/ads[\s.\-]*txt/", $s);
}

$rows = array();
$q = mysqli_query($con, "SELECT `p_id`, `page`, `page_url`, `metat`, `metad` FROM `pages` ORDER BY `p_id` ASC");
if ($q instanceof mysqli_result) {
	while ($r = mysqli_fetch_assoc($q)) {
		$rows[] = $r;
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Pages — Admin</title>
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
      <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> Pages</li>
    </ol>
    <div class="container-fluid page-content nm-pages">
      <div class="nm-pages-head">
        <div>
          <h2>Site pages</h2>
          <p>About, Terms, Privacy, Contact — same public URLs. Edit the text only. Authors cannot open this screen.</p>
        </div>
        <a class="btn btn-success" href="add_pages.php"><i class="fa fa-plus"></i> Add page</a>
      </div>

      <?php if (!$rows) { ?>
        <p class="text-muted" style="margin:0;">No pages yet. Add About / Contact first.</p>
      <?php } else { ?>
        <div class="nm-pages-grid">
          <?php foreach ($rows as $row) {
            $pid = (int) $row["p_id"];
            $slug = (string) $row["page_url"];
            $title = (string) $row["page"];
            $isAds = nm_page_is_ads_txt($slug, $title);
            $live = $isAds ? "/app-ads.txt" : "/page/" . rawurlencode($slug);
          ?>
            <article class="nm-page-card<?php echo $isAds ? " is-ads" : ""; ?>">
              <div class="nm-page-card-top">
                <h3><?php echo nm_h($title !== "" ? $title : "Untitled"); ?></h3>
                <?php if ($isAds) { ?>
                  <span class="nm-page-badge">Not a public article</span>
                <?php } ?>
              </div>
              <p class="nm-page-url">/page/<?php echo nm_h($slug); ?></p>
              <?php if (!empty($row["metad"])) { ?>
                <p class="nm-page-meta"><?php echo nm_h($row["metad"]); ?></p>
              <?php } ?>
              <div class="nm-page-actions">
                <a class="btn btn-info" href="edit_pages.php?eid=<?php echo $pid; ?>">Edit content</a>
                <a class="btn btn-outline-secondary" href="<?php echo nm_h($live); ?>" target="_blank" rel="noopener">View live</a>
                <button type="button" class="btn btn-outline-danger nm-page-delete" data-id="<?php echo $pid; ?>">Delete</button>
              </div>
            </article>
          <?php } ?>
        </div>
      <?php } ?>
    </div>
    <?php include "footer.php"; ?>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="../include/js/bootstrap.min.js"></script>
<script>
$(document).on("click", ".nm-page-delete", function () {
  var $btn = $(this);
  var id = $btn.attr("data-id");
  if (!id) return;
  nmConfirm("Delete this page? The footer link will also disappear. This cannot be undone.", {
    title: "Delete page",
    okText: "Delete"
  }).then(function (ok) {
    if (!ok) return;
    $btn.prop("disabled", true);
    $.ajax({
      url: "ajax_pages.php",
      method: "POST",
      dataType: "json",
      data: { id: id, actions: "delete" },
      success: function (res) {
        if (res && res.ok) {
          $btn.closest(".nm-page-card").fadeOut(200, function () { $(this).remove(); });
          nmToast(res.message || "Deleted.", "success");
        } else {
          $btn.prop("disabled", false);
          nmAlert((res && res.message) ? res.message : "Delete failed.");
        }
      },
      error: function () {
        $btn.prop("disabled", false);
        nmAlert("Delete failed.");
      }
    });
  });
});
</script>
</body>
</html>
