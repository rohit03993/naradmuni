<?php
include "config.php";
require_once("dbcontroller.php");
require_once("pagination.class.php");
$db_handle = new DBController();
$perPage = new PerPage();

$by_todate = isset($_GET['by_todate']) ? $_GET['by_todate'] : '';
$queryCondition = "";
$hasFilter = false;

if (!empty($_GET["search"])) {
	foreach ($_GET["search"] as $k => $v) {
		if (!empty($v)) {
			$queryCases = array("title", "by_date");
			if (in_array($k, $queryCases)) {
				$hasFilter = true;
				if (!empty($queryCondition)) {
					$queryCondition .= " AND ";
				} else {
					$queryCondition .= " WHERE ";
				}
			}
			switch ($k) {
				case "title":
					$queryCondition .= "n.title LIKE '%" . $db_handle->escape($v) . "%'";
					break;
				case "by_date":
					$queryCondition .= "n.date BETWEEN '" . $db_handle->escape($v) . "' AND '" . $db_handle->escape($by_todate) . "'";
					break;
			}
		}
	}
}

/*
 * How Post Views works:
 * - Every public page view inserts a row into news_views (newsid + datetime).
 * - This screen groups those rows per article (COUNT), joins news for title/date,
 *   sorts by most viewed, and shows 10 per page.
 * - news_views can be millions of rows — that is why the first load is heavy.
 */

$page = !empty($_GET["page"]) ? max(1, (int) $_GET["page"]) : 1;
$start = ($page - 1) * $perPage->perpage;
if ($start < 0) {
	$start = 0;
}

// Aggregate + limit in the subquery so we only join 10 news rows (not the full grouped set)
$sqlPage = "SELECT n.`newsid`, n.`title`, n.`date`, v.post_views
	FROM (
		SELECT `newsid`, COUNT(*) AS post_views
		FROM `news_views`
		GROUP BY `newsid`
		ORDER BY post_views DESC
		LIMIT " . (int) $start . "," . (int) $perPage->perpage . "
	) v
	INNER JOIN `news` n ON n.`newsid` = v.`newsid`";

// When filtering by title/date we must filter before LIMIT
if ($hasFilter) {
	$sqlPage = "SELECT n.`newsid`, n.`title`, n.`date`, COUNT(v.`count_id`) AS post_views
		FROM `news` n
		INNER JOIN `news_views` v ON v.`newsid` = n.`newsid`
		" . $queryCondition . "
		GROUP BY n.`newsid`, n.`title`, n.`date`
		ORDER BY post_views DESC
		LIMIT " . (int) $start . "," . (int) $perPage->perpage;
}

$faq = $db_handle->runQuery($sqlPage);
if (empty($faq)) {
	$faq = array();
}

// Reuse rowcount from AJAX so page 2+ does not recount ~2.6M rows
if (!empty($_GET["rowcount"]) && ctype_digit((string) $_GET["rowcount"])) {
	$rowcount = (int) $_GET["rowcount"];
} else {
	if ($hasFilter) {
		$countSql = "SELECT COUNT(*) AS c FROM (
			SELECT n.`newsid`
			FROM `news` n
			INNER JOIN `news_views` v ON v.`newsid` = n.`newsid`
			" . $queryCondition . "
			GROUP BY n.`newsid`
		) t";
	} else {
		// Cheaper: distinct articles that have at least one view (no news join)
		$countSql = "SELECT COUNT(DISTINCT `newsid`) AS c FROM `news_views`";
	}
	$countRows = $db_handle->runQuery($countSql);
	$rowcount = !empty($countRows[0]["c"]) ? (int) $countRows[0]["c"] : 0;
}

$paginationlink = "desp_post_views.php?page=";
$pagination_setting = isset($_GET["pagination_setting"]) ? $_GET["pagination_setting"] : '';

if ($pagination_setting == "prev-next") {
	$perpageresult = $perPage->getPrevNext($rowcount, $paginationlink, $pagination_setting);
} else {
	$perpageresult = $perPage->getAllPageLinks($rowcount, $paginationlink, $pagination_setting);
}

$from = $rowcount ? ($start + 1) : 0;
$to = min($start + count($faq), $rowcount);
?>

<input type="hidden" name="rowcount" id="rowcount" value="<?php echo (int) $rowcount; ?>" />
<p style="margin:0 0 10px;color:#555;">
	Showing <b><?php echo (int) $from; ?></b>–<b><?php echo (int) $to; ?></b>
	of <b><?php echo number_format($rowcount); ?></b> articles that have views
	(<?php echo (int) $perPage->perpage; ?> per page).
	<small>Each site visit adds a row in <code>news_views</code>; this page counts those rows per article.</small>
</p>
                        <table id="myTable" class="table table-bordered">
                            <thead class="bg-info">
                              <tr>
                                <th>S.N.</th>
                                <th>News Title</th>
                                <th>Post Date</th>
                                <th>Total Views</th>
                              </tr>
                            </thead>
                            <tbody>
                            <?php
                                $i = $start + 1;
                                foreach ($faq as $k => $v) {
                            ?>
                            <tr>
							<td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($faq[$k]["title"]); ?></td>
                            <td><?php echo htmlspecialchars($faq[$k]["date"]); ?></td>
                            <td><span class="badge badge-pill badge-primary" style="font-size:14px;"><b><?php echo (int) $faq[$k]["post_views"]; ?></b></span></td>
						  </tr>
                        <?php
                        }
                        ?>
                            </tbody>
                        </table>
<?php
if (!empty($perpageresult)) {
	echo '<div id="pagination">' . $perpageresult . '</div>';
}
?>
<script type="text/javascript">
$(document).ready(function(){
  // Do NOT use DataTables paging/info here — server already paginates.
  // DataTables "1 to 10 of 10" was wrong (it only saw the current page's 10 rows).
  if ($.fn.DataTable && $.fn.DataTable.isDataTable('#myTable')) {
    $('#myTable').DataTable().destroy();
  }
  $('#myTable').DataTable({
    responsive: true,
    paging: false,
    searching: false,
    info: false,
    ordering: false
  });
});
</script>
