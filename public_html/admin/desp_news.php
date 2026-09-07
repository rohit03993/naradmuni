<?php
include "config.php";
require_once("dbcontroller.php");
require_once("pagination.class.php");
$db_handle = new DBController();
$perPage = new PerPage();

    $queryCondition = "";

	if(!empty($_GET["search"])) {
		foreach($_GET["search"] as $k=>$v){
			if(!empty($v)) {

				$queryCases = array("title","category","slider","latest_news");
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
						$queryCondition .= "title LIKE '%" . $v . "%'";
						break;
                        case "category":
						$category = $v;
						$queryCondition .= "category LIKE '" . $v . "%'";
                        break;
                        case "slider":
						$slider = $v;
						$queryCondition .= "slider LIKE '" . $v . "%'";
                        break;
                        case "latest_news":
						$latest_news = $v;
						$queryCondition .= "latest_news LIKE '" . $v . "%'";
                        break;
				}
			}
		}
	}
$orderby = " ORDER BY newsid desc";
$sql = "SELECT `newsid`, `newsurl`, `title`, `image`, `video_file`, `newstype`, `category`, `team_id`, `latest_news`, `breaking`, `slider`, `latest_priority`, `slider_priority`, `date`, `time`, `status`, `pub_date_time` from news" . $queryCondition;
$paginationlink = "desp_news.php?page=";
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
$_GET["rowcount"] = $db_handle->numRows("SELECT newsid from news" . $queryCondition);
}

if($pagination_setting == "prev-next") {
	$perpageresult = $perPage->getPrevNext($_GET["rowcount"], $paginationlink,$pagination_setting);	
} else {
	$perpageresult = $perPage->getAllPageLinks($_GET["rowcount"], $paginationlink,$pagination_setting);	
}

$output = '';

?>

                        <div class="nm-table-wrap nm-table-wrap--fit">
                        <table id="myTable" class="table table-bordered nm-news-table">
                            <thead class="bg-info">
                              <tr>
							<th>S.N.</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Photo</th>
                            <th>Status</th>
                            <th>Date</th>
							<th>Action</th>
						  </tr>
                            </thead>
                            <tbody>
                            <?php
                                $i=1;
                                foreach($faq as $k=>$v) {
                                    
                                    $q33 = mysqli_query($con,"SELECT `maincat` FROM `categories` WHERE `id`='".$faq[$k]["category"]."'");
                                    $cat = mysqli_fetch_array($q33);
                            ?>
                            <tr>
							<td><?php echo $i+$k; ?></td>
							<td>
                              <div class="nm-title-cell"><?php echo htmlspecialchars($faq[$k]["title"]); ?></div>
                              <code class="nm-url-cell" title="<?php echo htmlspecialchars($faq[$k]["newsurl"]); ?>"><?php echo htmlspecialchars($faq[$k]["newsurl"]); ?></code>
                            </td>
                            <td><?php echo htmlspecialchars(isset($cat["maincat"]) ? $cat["maincat"] : ""); ?></td>
                            <td>
                                <?php if (!empty($faq[$k]["image"])) { ?>
                                <img src="../images/news/<?php echo htmlspecialchars($faq[$k]["image"]); ?>" width="72" height="54" class="img-thumbnail" alt="" loading="lazy" decoding="async">
                                <?php } else { echo "—"; } ?>
                            </td>
                            <td>
                                   <form id="SubmitForm<?php echo $faq[$k]["newsid"]; ?>">
                                    <select name="status" class="status custom-select" id="<?php echo $faq[$k]["newsid"]; ?>">
                                        <option value="<?php echo $faq[$k]["status"]; ?>"><?php echo $faq[$k]["status"]; ?></option>
                                        <option value="Unpublished">Unpublished</option>
                                        <option value="Published">Published</option>
                                      </select>
                                    </form>
                            </td>
                            <td class="nm-date-cell">
                            <?php echo htmlspecialchars($faq[$k]["date"]); ?><br><span class="nm-muted"><?php echo htmlspecialchars($faq[$k]["time"]); ?></span>
                            </td>
                             <td class="nm-actions-cell">
                                 <a class="btn btn-info" href="<?php echo $publicroot.'news/'.$faq[$k]["newsurl"]; ?>" target="_blank" title="View on public site"><i class="fas fa-eye"></i></a>
                                <a class="btn btn-warning text-white" href="edit_news.php?eid=<?php echo $faq[$k]["newsid"]; ?>"><i class="fas fa-edit"></i></a>
                                <a type="button" class="delete btn btn-danger text-white" name="delete" id="<?php echo $faq[$k]["newsid"]; ?>"><i class="delete fas fa-trash-alt"></i></a>
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