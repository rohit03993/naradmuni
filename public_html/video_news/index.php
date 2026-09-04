<?php include"../admin/config.php"; 
header( 'Content-Type: text/html; charset=utf-8' );
$url='video';
$qry="select * from categories where cat_url='$url'";
$ex=mysqli_query($con,$qry);
$rs=mysqli_fetch_array($ex);
?>
<!doctype html>
<html lang="en">
<head>
   <!-- Basic Page Needs =====================================-->
   <meta charset="utf-8">

   <!-- Mobile Specific Metas ================================-->
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

   <!-- Site Title- -->
   <title>Videos | Thenaradmuni</title>

   <!-- CSS
   ==================================================== -->
   <?php include"../css.php"; ?>
</head>

<body class="body-color">
   <div class="body-inner-content category-layout-4">
      <!-- header nav end-->

      <!-- block post area start-->
      <section class="block-wrapper mt-15">
         <div class="container">
		   <div class="row">
        <select style="display:none;" onChange="changePagination(this.value);" id="pagination-setting">
          <option value="all-links">Display All Page Link</option>
          <option value="prev-next">Display Prev Next Only</option>
        </select>
		
		<div class="col-lg-12" id="pagination-result">
            
            
               
        </div>
		
            </div>
            <!-- row end-->
         </div>
         <!-- container end-->
      </section>
      <!-- block area end-->

      <!-- footer social list start-->
      <!-- footer end -->


   </div>


   <!-- javaScript Files
	=============================================================================-->
<?php include"../js.php"; ?>
        <script>
$(document).on('click', '.reset', function(){  
        $('#SearchForm')[0].reset();
        getresult("desp_categories.php");
        });
getresult("desp_categories.php");
function getresult(url) {
	$.ajax({
		url: url,
		type: "GET",
		data:  {rowcount:$("#rowcount").val(),
                "pagination_setting":$("#pagination-setting").val(),
                "actions":("<?php echo $rs['id']; ?>")},
		beforeSend: function(){$("#overlay").show();},
		success: function(data){
		$("#pagination-result").html(data);
		$("#overlay").hide();
		},
		error: function() 
		{} 	        
   });
} 
function changePagination(option) {
	if(option!= "") {
		getresult("desp_categories.php");
	}
}
</script>
<script>
getresult("desp_categories.php");
</script>
</body>
</html>