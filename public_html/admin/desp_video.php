<?php
require_once("dbcontroller.php");
require_once("pagination.class.php");
$db_handle = new DBController();
$perPage = new PerPage();

    $queryCondition = "";

	if(!empty($_GET["search"])) {
		foreach($_GET["search"] as $k=>$v){
			if(!empty($v)) {

				$queryCases = array("name","email");
				if(in_array($k,$queryCases)) {
					if(!empty($queryCondition)) {
						$queryCondition .= " AND ";
					} else {
						$queryCondition .= " WHERE ";
					}
				}
				switch($k) {
					    case "name":
						$name = $v;
						$queryCondition .= "name LIKE '%" . $v . "%'";
						break;
                        case "email":
						$email = $v;
						$queryCondition .= "email LIKE '%" . $v . "%'";
                        break;
				}
			}
		}
	}
$orderby = " ORDER BY v_id desc";
$sql = "SELECT * from video" . $queryCondition;
$paginationlink = "desp_video.php?page=";	
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
                                <th>Title</th>
                                <th>Video URL</th>
                                <th>Video</th>
                                <th>Action</th>
                              </tr>
                            </thead>
                            <tbody>
                            <?php
                                $i=1;
                                foreach($faq as $k=>$v) {
                            ?>
                            <tr>
							<td><?php echo $i++;?></td>
                            <td><?php echo $faq[$k]["title"]; ?></td>
                            <td><textarea cols="50" rows="5"><iframe class="embed-responsive-item" allowfullscreen="allowfullscreen" src="https://www.joshhosh.com/videos/<?php echo $faq[$k]["video"]; ?>"></iframe></textarea>
            
                            </td>
                            <td><video width="200" controls>
                          <source src="<?php echo '../videos/'.$faq[$k]['video']; ?>" type="video/mp4">
                          Your browser does not support the video tag.
                        </video></td>
                            
                             <td>
                                <a type="button" name="delete" id="<?php echo $faq[$k]["v_id"]; ?>" class="delete fas fa-trash-alt"></a>
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