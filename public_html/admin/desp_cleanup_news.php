<?php
include "config.php";
require_once "dbcontroller.php";
require_once "pagination.class.php";
require_once "news_media.php";

$db_handle = new DBController();
$perPage = new PerPage();
$perPage->perpage = 25;

$months = isset($_GET["months"]) ? max(1, (int) $_GET["months"]) : 6;
$minViews = nm_min_views_keep();
$ageSql = nm_age_where_sql($months);

$baseWhere = " WHERE " . $ageSql;
$countSql = "SELECT COUNT(*) AS c FROM news" . $baseWhere;
$listSql = "SELECT `newsid`, `newsurl`, `title`, `image`, `description`, `video_file`, `category`, `date`, `status`
	FROM news" . $baseWhere . " ORDER BY STR_TO_DATE(`date`,'%d-%m-%Y') ASC";

$page = !empty($_GET["page"]) ? max(1, (int) $_GET["page"]) : 1;
$start = ($page - 1) * $perPage->perpage;
if ($start < 0) {
	$start = 0;
}

if (!empty($_GET["rowcount"]) && ctype_digit((string) $_GET["rowcount"])) {
	$rowcount = (int) $_GET["rowcount"];
} else {
	$cr = $db_handle->runQuery($countSql);
	$rowcount = !empty($cr[0]["c"]) ? (int) $cr[0]["c"] : 0;
}

$query = $listSql . " LIMIT " . (int) $start . "," . (int) $perPage->perpage;
$faq = $db_handle->runQuery($query);
if (empty($faq)) {
	$faq = array();
}

$viewMap = array();
if ($faq) {
	$ids = array();
	foreach ($faq as $row) {
		$ids[] = (int) $row["newsid"];
	}
	$viewMap = nm_batch_view_counts($con, $ids);
}

$paginationlink = "desp_cleanup_news.php?page=";
$perpageresult = $perPage->getAllPageLinks($rowcount, $paginationlink);

$pageDisk = 0;
$pageBase64 = 0;
$pageBytes = 0;
$pageProtected = 0;
foreach ($faq as $row) {
	$views = isset($viewMap[(int) $row["newsid"]]) ? (int) $viewMap[(int) $row["newsid"]] : 0;
	if (nm_is_view_protected($views)) {
		$pageProtected++;
	}
	$a = nm_analyze_media($row["image"], $row["description"], isset($row["video_file"]) ? $row["video_file"] : "");
	$pageDisk += $a["disk_count"];
	$pageBase64 += $a["base64_embeds"];
	$pageBytes += nm_featured_bytes($row["image"], isset($row["video_file"]) ? $row["video_file"] : "");
}

$from = $rowcount ? ($start + 1) : 0;
$to = min($start + count($faq), $rowcount);
?>
<input type="hidden" id="rowcount" value="<?php echo (int) $rowcount; ?>" />
<p id="summary-inline" style="margin:0 0 12px;">
	<strong><?php echo number_format($rowcount); ?></strong> posts older than <strong><?php echo (int) $months; ?></strong> months
	· showing <?php echo (int) $from; ?>–<?php echo (int) $to; ?>
	· this page: <strong><?php echo (int) $pageDisk; ?></strong> files (~<?php echo htmlspecialchars(nm_format_bytes($pageBytes)); ?>)
	<?php if ($pageProtected) { ?> · <strong><?php echo (int) $pageProtected; ?></strong> KEEP (<?php echo (int) $minViews; ?>+ views)<?php } ?>
	<?php if ($pageBase64) { ?> · <strong><?php echo (int) $pageBase64; ?></strong> base64 embeds<?php } ?>
</p>
<script>
$("#summary").html($("#summary-inline").html());
if (typeof syncDeleteSummary === "function") syncDeleteSummary();
</script>

<table class="table table-bordered table-sm" id="cleanupTable">
  <thead class="bg-warning">
    <tr>
      <th>Date</th>
      <th>URL</th>
      <th>Title</th>
      <th>Category</th>
      <th>Status</th>
      <th>Views</th>
      <th>Will delete?</th>
      <th>Images</th>
    </tr>
  </thead>
  <tbody>
<?php
foreach ($faq as $row) {
	$catName = "";
	$cq = mysqli_query($con, "SELECT maincat FROM categories WHERE id='" . mysqli_real_escape_string($con, $row["category"]) . "' LIMIT 1");
	if ($cq && ($cr = mysqli_fetch_assoc($cq))) {
		$catName = $cr["maincat"];
	}
	$media = nm_analyze_media($row["image"], $row["description"], isset($row["video_file"]) ? $row["video_file"] : "");
	$nid = (int) $row["newsid"];
	$views = isset($viewMap[$nid]) ? (int) $viewMap[$nid] : 0;
	$protected = nm_is_view_protected($views);
	?>
    <tr<?php echo $protected ? ' class="table-success"' : ""; ?>>
      <td><?php echo htmlspecialchars($row["date"]); ?></td>
      <td><code style="font-size:12px;"><?php echo htmlspecialchars($row["newsurl"]); ?></code></td>
      <td><?php echo htmlspecialchars(substr($row["title"], 0, 80)); ?></td>
      <td><?php echo htmlspecialchars($catName); ?></td>
      <td><?php echo htmlspecialchars($row["status"]); ?></td>
      <td><span class="badge badge-info"><?php echo number_format($views); ?></span></td>
      <td>
        <?php if ($protected) { ?>
          <span class="badge badge-success">KEEP</span>
        <?php } else { ?>
          <span class="badge badge-danger">Yes (if you run delete)</span>
        <?php } ?>
      </td>
      <td><span class="badge badge-dark"><?php echo htmlspecialchars(nm_format_media_badge($media)); ?></span></td>
    </tr>
	<?php
}
if (!count($faq)) {
	echo '<tr><td colspan="8">No posts older than ' . (int) $months . ' months.</td></tr>';
}
?>
  </tbody>
</table>
<?php
if (!empty($perpageresult)) {
	echo '<div id="pagination">' . $perpageresult . '</div>';
}
?>
<script>
window.getresult = function (url) {
  $("#overlay").show();
  var pageMatch = /page=(\d+)/.exec(url || "");
  $.ajax({
    url: "desp_cleanup_news.php",
    type: "GET",
    data: {
      months: $("#months").val(),
      rowcount: $("#rowcount").val() || "",
      page: pageMatch ? pageMatch[1] : 1
    },
    success: function (data) {
      $("#pagination-result").html(data);
      $("#overlay").hide();
      if (typeof syncDeleteSummary === "function") syncDeleteSummary();
    },
    error: function () { $("#overlay").hide(); }
  });
};
</script>
