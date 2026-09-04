<?php
require_once("dbcontroller.php");
require_once("pagination.class.php");
$db_handle = new DBController();
$perPage = new PerPage();

    $by_todate=$_GET['by_todate'];
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
						$queryCondition .= "applications.name LIKE '%" . $v . "%'";
						break;
                        case "by_date":
                        $by_date = $v;
						$queryCondition .= "applications.date BETWEEN '$by_date' AND '$by_todate'";
						break;
				}
			}
		}
	}
$orderby = " ORDER BY applications.a_id desc";
$sql = "SELECT applications.*,jobs.post_name,jobs.location FROM applications INNER JOIN jobs ON applications.j_id=jobs.j_id" . $queryCondition;
$paginationlink = "desp_applications.php?page=";	
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
                                <th>Job Title</th>
                                <th>Location</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone No.</th>
                                <th>Resume</th>
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
							<td><?php echo $i++;?></td>
                            <td><?php echo $faq[$k]["post_name"]; ?></td>
                            <td><?php echo $faq[$k]["location"]; ?></td>
                            <td><?php echo $faq[$k]["name"]; ?></td>
                            <td><?php echo $faq[$k]["email"]; ?></td>
                            <td><?php echo $faq[$k]["num"]; ?></td>
                            <td><a href="../applications/<?php echo $faq[$k]["file"]; ?>" target="_blank">View Resume</a></td>
                            <td><?php echo $faq[$k]["date"]; ?></td>
                            
                            <td>
                                   <form id="SubmitForm<?php echo $faq[$k]["a_id"]; ?>">
                                    <select name="status" class="status custom-select" id="<?php echo $faq[$k]["a_id"]; ?>">
                                        <option value="<?php echo $faq[$k]["status"]; ?>"><?php echo $faq[$k]["status"]; ?></option>
                                        <option value="Approved">Accept</option>
                                        <option value="Declined">Decline</option>
                                      </select>
                                    </form>
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