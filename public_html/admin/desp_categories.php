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
$sql = "SELECT * from categories" . $queryCondition;
$paginationlink = "desp_categories.php?page=";	
$pagination_setting = $_GET["pagination_setting"];
				
$page = 1;
if(!empty($_GET["page"])) {
$page = $_GET["page"];
}

$start = ($page-1)*$perPage->perpage;
if($start < 0) $start = 0;

$query =  $sql . $orderby . " limit " . $start . "," . $perPage->perpage; 

$faq = $db_handle->runQuery($query);

if(empty($_GET["rowcount"])) {
$_GET["rowcount"] = $db_handle->numRows($sql);
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
							<th>Category</th>
                            <th>Parent</th>
                            <th>Category Url</th>
                            <th>Short Order</th>
                            <th>Child Category</th>
                            <th>Menu</th>
                            <th>Meta Title</th>
                            <th>Meta Desc.</th>
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
                            <td><?php echo $faq[$k]["maincat"]; ?></td>
                            <td><?php
                            $catid=$faq[$k]["parent"];
                                    if(isset($catid)){
                                        $qry12=mysqli_query($con,"SELECT * FROM `categories` WHERE id='$catid'");
                            $rs99=mysqli_fetch_array($qry12);
                            echo $rs99["maincat"];
                                    }
                             ?></td>
                            <td><?php echo $faq[$k]["cat_url"]; ?></td>
                            <td><?php echo $faq[$k]["short"]; ?></td>
                            <td><?php echo $faq[$k]["main_heading"]; ?></td>
                            <td><?php echo $faq[$k]["menu"]; ?></td>
                            <td><?php echo $faq[$k]["metat"]; ?></td>
                            <td><?php echo $faq[$k]["metad"]; ?></td>
                             <td>
                                <a class="" href="edit_category.php?eid=<?php echo $faq[$k]["id"]; ?>"><i class="fas fa-edit"></i></a>
                                <a type="button" name="delete" id="<?php echo $faq[$k]["id"]; ?>" class="delete fas fa-trash-alt"></a>
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