<?php
include "config.php";
require_once("dbcontroller.php");
require_once("pagination.class.php");
$db_handle = new DBController();
$perPage = new PerPage();

    $by_todate=$_GET['by_todate'];
    $queryCondition = "";

	if(!empty($_GET["search"])) {
		foreach($_GET["search"] as $k=>$v){
			if(!empty($v)) {

				$queryCases = array("email","by_date");
				if(in_array($k,$queryCases)) {
					if(!empty($queryCondition)) {
						$queryCondition .= " AND ";
					} else {
						$queryCondition .= " WHERE ";
					}
				}
				switch($k) {
					    case "email":
						$email = $v;
						$queryCondition .= "email LIKE '%" . $v . "%'";
						break;
                        case "by_date":
                        $by_date = $v;
						$queryCondition .= "date BETWEEN '$by_date' AND '$by_todate'";
						break;
				}
			}
		}
	}
$orderby = " ORDER BY l_id desc";
$sql = "SELECT * FROM news_latter" . $queryCondition;
$paginationlink = "desp_news_latter.php?page=";	
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
                                <th>Email</th>
                                <th>Date</th>
                                <th>Time</th>
                              </tr>
                            </thead>
                            <tbody>
                            <?php
                                $i=1;
                                foreach($faq as $k=>$v) {
                            ?>
                            <tr>
							<td><?php echo $i++;?></td>
                            <td><?php echo $faq[$k]["email"]; ?></td>
                            <td><?php echo $faq[$k]["date"]; ?></td>
                            <td><?php echo $faq[$k]["time"]; ?></td>
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