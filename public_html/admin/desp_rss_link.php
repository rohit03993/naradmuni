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
$orderby = " ORDER BY id desc";
$sql = "SELECT * from rss_feed" . $queryCondition;
$paginationlink = "desp_rss_link.php?page=";	
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
							<th>Date</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Url</th>
						    <th>Small Description</th>
						    <th>Link</th>
						   <th>Sequence</th>
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
                            <td><?php echo $faq[$k]["date"]; ?></td>
							 <td><img src="../images/gallery/<?php echo $faq[$k]["image_name"]; ?>" width="100" class="img-thumbnail"></td>
            				 <td><?php echo $faq[$k]["title"]; ?></td>
            				 <td><?php echo $faq[$k]["url"]; ?></td>
              <td><?php echo $faq[$k]["small_description"]; ?></td>
             
                            <td><a href="<?php echo $faq[$k]["link"]; ?>" target="_blank">Rss Link</a></td>
                             <td><?php echo $faq[$k]["sequence"]; ?></td>
                             <td width="15%">
                                 
                                <a class="btn btn-warning text-white" href="edit_rss_link.php?eid=<?php echo $faq[$k]["id"]; ?>"><i class="fas fa-edit"></i></a>
                                <a type="button" class="delete btn btn-danger text-white" name="delete" id="<?php echo $faq[$k]["id"]; ?>"><i class="delete fas fa-trash-alt"></i></a>
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