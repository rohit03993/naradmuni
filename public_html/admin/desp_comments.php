<?php
require_once("dbcontroller.php");
require_once("pagination.class.php");
$db_handle = new DBController();
$perPage = new PerPage();

    $by_todate = isset($_GET['by_todate']) ? $_GET['by_todate'] : "";
    $queryCondition = "";

	if(!empty($_GET["search"])) {
		foreach($_GET["search"] as $k=>$v){
			if(!empty($v)) {

				$queryCases = array("title","by_date");
				if(in_array($k,$queryCases)) {
					if(!empty($queryCondition)) {
						$queryCondition .= " AND ";
					} else {
						$queryCondition .= " WHERE ";
					}
				}
				switch($k) {
					    case "title":
						$title = $v;
						$queryCondition .= "news.title LIKE '%" . $v . "%'";
						break;
                        case "by_date":
                        $by_date = $v;
						$queryCondition .= "comments.date BETWEEN '$by_date' AND '$by_todate'";
						break;
				}
			}
		}
	}
$orderby = " ORDER BY comments.c_id desc";
$sql = "SELECT comments.*,news.title FROM comments INNER JOIN news ON comments.newsid=news.newsid" . $queryCondition;
$paginationlink = "desp_comments.php?page=";
$pagination_setting = isset($_GET["pagination_setting"]) ? $_GET["pagination_setting"] : "";

$page = 1;
if(!empty($_GET["page"])) {
$page = $_GET["page"];
}

$start = ($page-1)*$perPage->perpage;
if($start < 0) $start = 0;

$query =  $sql . $orderby . " limit " . $start . "," . $perPage->perpage;

$faq = $db_handle->runQuery($query);
if (empty($faq)) { $faq = array(); }

if(empty($_GET["rowcount"])) {
$_GET["rowcount"] = $db_handle->numRows($sql);
}

if($pagination_setting == "prev-next") {
	$perpageresult = $perPage->getPrevNext($_GET["rowcount"], $paginationlink,$pagination_setting);
} else {
	$perpageresult = $perPage->getAllPageLinks($_GET["rowcount"], $paginationlink,$pagination_setting);
}

?>

                        <div class="nm-table-wrap nm-table-wrap--fit">
                        <table id="myTable" class="table table-bordered nm-comments-table">
                            <thead class="bg-info">
                              <tr>
                                <th>S.N.</th>
                                <th>News Title</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Comment</th>
                                <th>Date</th>
                                <th>Status</th>
                              </tr>
                            </thead>
                            <tbody>
                            <?php
                                $i=1;
                                foreach($faq as $k=>$v) {
                            ?>
                            <tr>
							<td><?php echo $i++; ?></td>
                            <td><div class="nm-title-cell" title="<?php echo htmlspecialchars($faq[$k]["title"]); ?>"><?php echo htmlspecialchars($faq[$k]["title"]); ?></div></td>
                            <td><?php echo htmlspecialchars($faq[$k]["name"]); ?></td>
                            <td><span class="nm-email-cell"><?php echo htmlspecialchars($faq[$k]["u_id"]); ?></span></td>
                            <td><div class="nm-comment-cell"><?php echo htmlspecialchars($faq[$k]["comment"]); ?></div></td>
                            <td class="nm-date-cell">
                              <?php echo htmlspecialchars($faq[$k]["date"]); ?><br>
                              <span class="nm-muted"><?php echo htmlspecialchars($faq[$k]["time"]); ?></span>
                            </td>
                            <td>
                                   <form id="SubmitForm<?php echo (int) $faq[$k]["c_id"]; ?>">
                                    <select name="status" class="status custom-select" id="<?php echo (int) $faq[$k]["c_id"]; ?>">
                                        <option value="<?php echo htmlspecialchars($faq[$k]["status"]); ?>"><?php echo htmlspecialchars($faq[$k]["status"]); ?></option>
                                        <option value="Approved">Approve</option>
                                        <option value="Declined">Decline</option>
                                      </select>
                                    </form>
                            </td>
						  </tr>
                        <?php
                        }
                        ?>
                            </tbody>
                        </table>
                        </div>
                        <?php
                        if(!empty($perpageresult)) {
                          echo '<div id="pagination">' . $perpageresult . '</div>';
                        }
                        ?>
<script type="text/javascript" language="javascript" >
        $(document).ready(function(){
        $('#myTable').DataTable( {
           responsive: false,
           "bPaginate": false,
            "searching": false,
            "autoWidth": false,
            "scrollX": false,
            "ordering": false
           } );
        });

    </script>
