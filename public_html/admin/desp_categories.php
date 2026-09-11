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

				$queryCases = array("product_name","status");
				if(in_array($k,$queryCases)) {
					if(!empty($queryCondition)) {
						$queryCondition .= " AND ";
					} else {
						$queryCondition .= " WHERE ";
					}
				}
				switch($k) {
					    case "product_name":
						$name = $v;
						$queryCondition .= "product_name LIKE '%" . $v . "%'";
						break;
                        case "status":
						$status = $v;
						$queryCondition .= "status LIKE '" . $v . "%'";
                        break;
				}
			}
		}
	}
$orderby = " ORDER BY id desc";
$sql = "SELECT id, hindi_name, maincat, parent, cat_url, short, main_heading, menu from categories" . $queryCondition;
$paginationlink = "desp_categories.php?page=";	
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
$_GET["rowcount"] = $db_handle->numRows("SELECT id from categories" . $queryCondition);
}

if($pagination_setting == "prev-next") {
	$perpageresult = $perPage->getPrevNext($_GET["rowcount"], $paginationlink,$pagination_setting);	
} else {
	$perpageresult = $perPage->getAllPageLinks($_GET["rowcount"], $paginationlink,$pagination_setting);	
}

$output = '';

?>

                        <div class="nm-table-wrap nm-table-wrap--fit">
                        <table id="myTable" class="table table-bordered nm-cats-table">
                            <thead class="bg-info">
                              <tr>
							<th>S.N.</th>
							<th>Category</th>
                            <th>Parent</th>
                            <th>URL</th>
                            <th>Order</th>
                            <th>Child</th>
                            <th>Menu</th>
							<th>Action</th>
						  </tr>
                            </thead>
                            <tbody>
                            <?php
                                $i=1;
                                foreach($faq as $k=>$v) {
                            ?>
                            <tr>
							<td><?php echo $i+$k; ?></td>
                            <td><?php echo nm_h(nm_cat_label($faq[$k])); ?></td>
                            <td><?php
                            $catid=$faq[$k]["parent"];
                                    if(isset($catid) && $catid !== "" && $catid !== null){
                                        $qry12=mysqli_query($con,"SELECT hindi_name, maincat FROM `categories` WHERE id='".mysqli_real_escape_string($con, (string)$catid)."'");
                            $rs99=mysqli_fetch_array($qry12);
                            echo nm_h(is_array($rs99) ? nm_cat_label($rs99) : '');
                                    }
                             ?></td>
                            <td><code class="nm-url-cell" title="<?php echo htmlspecialchars($faq[$k]["cat_url"]); ?>"><?php echo htmlspecialchars($faq[$k]["cat_url"]); ?></code></td>
                            <td><?php echo htmlspecialchars($faq[$k]["short"]); ?></td>
                            <td><?php echo htmlspecialchars($faq[$k]["main_heading"]); ?></td>
                            <td><?php echo htmlspecialchars($faq[$k]["menu"]); ?></td>
                             <td class="nm-actions-cell">
                                <a class="btn btn-warning text-white btn-sm" href="edit_category.php?eid=<?php echo (int)$faq[$k]["id"]; ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                <a type="button" name="delete" id="<?php echo (int)$faq[$k]["id"]; ?>" class="delete btn btn-danger text-white btn-sm" title="Delete"><i class="fas fa-trash-alt"></i></a>
                              </td>
						  </tr>
                        <?php
                        }
                        if(!empty($perpageresult)) {
                        $output .= '<div id="pagination">' . $perpageresult . '</div>';
                        }
                        print $output;
                        ?>
                        </table>
                        </div>
    <script type="text/javascript" language="javascript" >
        $(document).ready(function(){
        if ($.fn.DataTable.isDataTable('#myTable')) {
          $('#myTable').DataTable().destroy();
        }
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
