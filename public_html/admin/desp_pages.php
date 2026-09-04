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

				$queryCases = array("page");
				if(in_array($k,$queryCases)) {
					if(!empty($queryCondition)) {
						$queryCondition .= " AND ";
					} else {
						$queryCondition .= " WHERE ";
					}
				}
				switch($k) {
					    
                        case "page":
						$page = $v;
						$queryCondition .= "page LIKE '" . $v . "%'";
                        break;
				}
			}
		}
	}
$orderby = " ORDER BY p_id desc";
$sql = "SELECT * from pages" . $queryCondition;
$paginationlink = "desp_pages.php?page=";	
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
                            <th>Page Title</th>
                            <th>Page Url</th>
                            <th>Meta Title</th>
							<th>Meta Description</th>
							<th>Action</th>
						  </tr>
                            </thead>
                            <tbody>
                            <?php
                                $i=1;
                                foreach($faq as $k=>$v) {
                                    
                                    $q33 = mysqli_query($con,"SELECT `maincat` FROM `categories` WHERE `id`='".$faq[$k]["page"]."'");
                                    $cat = mysqli_fetch_array($q33);
                            ?>
                            <tr>
							<td><?php echo $i+$k; ?></td>
                            <td><?php echo $faq[$k]["page"]; ?></td>
                            <td><?php echo $faq[$k]["page_url"]; ?></td>
                            <td><textarea cols="20" rows="5"><?php echo $faq[$k]["metat"]; ?></textarea></td>
                            <td><textarea cols="20" rows="5"><?php echo $faq[$k]["metad"]; ?></textarea></td>
                             <td>
                                <a class="" href="edit_pages.php?eid=<?php echo $faq[$k]["p_id"]; ?>"><i class="fas fa-edit"></i></a>
                                <a type="button" name="delete" id="<?php echo $faq[$k]["p_id"]; ?>" class="delete fas fa-trash-alt"></a>
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