<?php
/**
 * Lightweight preview for cleanup — no STR_TO_DATE in SQL, no news_views scans.
 * (Full delete = CLI: cli_cleanup_old_news.php --fast)
 */
include "config.php";
require_once "dbcontroller.php";
require_once "pagination.class.php";
require_once "news_media.php";

@set_time_limit(60);

$db_handle = new DBController();
$perPage = new PerPage();
$perPage->perpage = 25;

$months = isset($_GET["months"]) ? max(1, (int) $_GET["months"]) : 6;
$minViews = nm_min_views_keep();
$cutoff = new DateTime("today");
$cutoff->modify("-{$months} months");

$page = !empty($_GET["page"]) ? max(1, (int) $_GET["page"]) : 1;
$per = (int) $perPage->perpage;
$needFrom = ($page - 1) * $per;
$needTo = $needFrom + $per;

function nm_parse_dmy($dateStr) {
	$dateStr = trim((string) $dateStr);
	if ($dateStr === "") {
		return null;
	}
	$dt = DateTime::createFromFormat("d-m-Y", $dateStr);
	if ($dt instanceof DateTime) {
		$dt->setTime(0, 0, 0);
		return $dt;
	}
	return null;
}

// Walk by PK; filter date in PHP (safe on local after CLI shrink)
$matched = array();
$totalMatched = 0;
$afterId = 0;
$guard = 0;
$maxScan = 80000;

while ($guard < $maxScan) {
	$guard += 2000;
	$q = mysqli_query(
		$con,
		"SELECT `newsid`,`newsurl`,`title`,`image`,`video_file`,`category`,`date`,`status`
		 FROM `news` WHERE `newsid` > $afterId ORDER BY `newsid` ASC LIMIT 2000"
	);
	if (!$q || mysqli_num_rows($q) === 0) {
		break;
	}
	while ($r = mysqli_fetch_assoc($q)) {
		$afterId = (int) $r["newsid"];
		$dt = nm_parse_dmy($r["date"]);
		if (!$dt || $dt >= $cutoff) {
			continue;
		}
		if ($totalMatched >= $needFrom && $totalMatched < $needTo) {
			$matched[] = $r;
		}
		$totalMatched++;
	}
}

$rowcount = $totalMatched;
$faq = $matched;
$start = $needFrom;
$from = $rowcount ? ($start + 1) : 0;
$to = min($start + count($faq), $rowcount);

$paginationlink = "desp_cleanup_news.php?page=";
$perpageresult = $perPage->getAllPageLinks($rowcount, $paginationlink);
?>
<input type="hidden" id="rowcount" value="<?php echo (int) $rowcount; ?>" />
<p id="summary-inline" style="margin:0 0 12px;">
	<strong><?php echo number_format($rowcount); ?></strong> posts older than <strong><?php echo (int) $months; ?></strong> months
	· showing <?php echo (int) $from; ?>–<?php echo (int) $to; ?>
	· <em>Views not loaded here</em> (avoids hanging Apache). Delete via CLI for bulk.
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
      <th>Will delete?</th>
    </tr>
  </thead>
  <tbody>
<?php
foreach ($faq as $row) {
	$catName = "";
	$cq = mysqli_query($con, "SELECT hindi_name, maincat FROM categories WHERE id='" . mysqli_real_escape_string($con, $row["category"]) . "' LIMIT 1");
	if ($cq && ($cr = mysqli_fetch_assoc($cq))) {
		$catName = nm_cat_label($cr);
	}
	?>
    <tr>
      <td><?php echo htmlspecialchars($row["date"]); ?></td>
      <td><code style="font-size:12px;"><?php echo htmlspecialchars($row["newsurl"]); ?></code></td>
      <td><?php echo htmlspecialchars(substr($row["title"], 0, 80)); ?></td>
      <td><?php echo htmlspecialchars($catName); ?></td>
      <td><?php echo htmlspecialchars($row["status"]); ?></td>
      <td><span class="badge badge-danger">Age match (CLI recommended)</span></td>
    </tr>
	<?php
}
if (!count($faq)) {
	echo '<tr><td colspan="6">No posts older than ' . (int) $months . ' months.</td></tr>';
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
