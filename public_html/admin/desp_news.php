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
$sql = "SELECT `newsid`, `newsurl`, `title`, `image`, `description`, `video_file`, `newstype`, `category`, `team_id`, `latest_news`, `breaking`, `slider`, `latest_priority`, `slider_priority`, `date`, `time`, `status`, `pub_date_time` from news" . $queryCondition;
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

require_once __DIR__ . "/news_media.php";

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

                        <table id="myTable" class="table table-bordered">
                            <thead class="bg-info">
                              <tr>
							<th>S.N.</th>
							<th>News URL</th>
                            <th>Title</th>
							<th>News Type</th>
							<th>Slider</th>
							<th>Latest</th>
                            <th>Category</th>
                            <th>Photo</th>
                            <th>Images</th>
                            <th>Status</th>
                            <th>Date / Time</th>
							<th>Action</th>
						  </tr>
                            </thead>
                            <tbody>
                            <?php
                                $i=1;
                                foreach($faq as $k=>$v) {
                                    
                                    $q33 = mysqli_query($con,"SELECT `maincat` FROM `categories` WHERE `id`='".$faq[$k]["category"]."'");
                                    $cat = mysqli_fetch_array($q33);
                                    $media = nm_analyze_media(
                                        isset($faq[$k]["image"]) ? $faq[$k]["image"] : "",
                                        isset($faq[$k]["description"]) ? $faq[$k]["description"] : "",
                                        isset($faq[$k]["video_file"]) ? $faq[$k]["video_file"] : ""
                                    );
                            ?>
                            <tr>
							<td><?php echo $i+$k; ?></td>
                            <td><?php echo htmlspecialchars($faq[$k]["newsurl"]); ?></td>
							<td><textarea cols="20" rows="5"><?php echo htmlspecialchars($faq[$k]["title"]); ?></textarea></td>
                            <td><?php echo htmlspecialchars($faq[$k]["newstype"]); ?></td>
                            <td><?php echo htmlspecialchars($faq[$k]["slider"].' / '.$faq[$k]['slider_priority']); ?></td>
                            <td><?php echo htmlspecialchars($faq[$k]["latest_news"].' / '.$faq[$k]['latest_priority']); ?></td>
                            <td><?php echo htmlspecialchars(isset($cat["maincat"]) ? $cat["maincat"] : ""); ?></td>
                            <td>
                                <?php if (!empty($faq[$k]["image"])) { ?>
                                <img src="../images/news/<?php echo htmlspecialchars($faq[$k]["image"]); ?>" width="100" class="img-thumbnail" alt="">
                                <?php } else { echo "—"; } ?>
                            </td>
                            <td>
                                <span class="badge badge-secondary" title="Disk files + optional base64 embeds in body"><?php echo htmlspecialchars(nm_format_media_badge($media)); ?></span>
                            </td>
                            <td width="10%">
                                   <form id="SubmitForm<?php echo $faq[$k]["newsid"]; ?>">
                                    <select name="status" class="status custom-select" id="<?php echo $faq[$k]["newsid"]; ?>">
                                        <option value="<?php echo $faq[$k]["status"]; ?>"><?php echo $faq[$k]["status"]; ?></option>
                                        <option value="Unpublished">Unpublished</option>
                                        <option value="Published">Published</option>
                                      </select>
                                    </form>
                            </td>
                            <td width="10%">
                            <?php echo htmlspecialchars($faq[$k]["date"]); ?> / <?php echo htmlspecialchars($faq[$k]["time"]); ?>
                            </td>
                             <td width="15%">
                                 <a class="btn btn-info" href="<?php echo $publicroot.'news/'.$faq[$k]["newsurl"]; ?>" target="_blank" title="View on public site"><i class="fas fa-eye"></i></a>
                                <a class="btn btn-warning text-white" href="edit_news.php?eid=<?php echo $faq[$k]["newsid"]; ?>"><i class="fas fa-edit"></i></a>
                                <a type="button" class="delete btn btn-danger text-white" name="delete" id="<?php echo $faq[$k]["newsid"]; ?>"><i class="delete fas fa-trash-alt"></i></a>
                              </td>
						  </tr>
                        <?php
                        }
                        if(!empty($perpageresult)) {
                        $output .= '<table class="table" id="table"><tr><div id="pagination">' . $perpageresult . '</div></tr></table>';
                        }
                        print $output;
                        ?>
    <script type="text/javascript" language="javascript" >
        $(document).ready(function(){
        $('#myTable').DataTable( {
           responsive: true,
           "bPaginate": false,
            "searching": false
           } );
        });

    </script>