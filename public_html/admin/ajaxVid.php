<?php
if(!empty($_POST["newstype"])){
    if($_POST['newstype'] == 'Video'){
        echo '<div class="col-md-3 form-group group group">
            <label class="control-label">Video Type</label>
            <select class="custom-select" id="videotype" name="videotype">
                <option value="">Select</option>
                <option value="1">Upload Video</option>
            	<option value="2">YouTube Link</option>
            </select>
            </div>
            <div class="col-md-3 form-group group group">
            <label class="control-label">Home Page</label>
            <select class="custom-select" id="show_home" name="show_home">
                <option>No</option>
            	<option>Yes</option>
            </select>
            </div>';
    }
}
if(!empty($_POST["videotype"])){
    if($_POST["videotype"]=='1'){
        echo '<div class="col-md-6 form-group group">
              <label class="control-label">Select Video:</label>
                <input class="form-control" type="file" name="video_file">
            </div>';
    }else{
        echo '<div class="col-md-6 form-group group">
              <label class="control-label">YouTube Link:</label>
              <input class="form-control" type="text" name="videolink">
            </div>';
    }

}
?>
<script>
$("#videotype").on('change', function(){
			$.ajax({
						type: "POST",
						url: "ajaxVid.php",
						data:{videotype:$("#videotype").val()},
						beforeSend:function(){
						    $('#vid2').html("<p>Loading....</p>");
                          },
						success: function(data){
							$('#vid2').html(data);
						}
			});
	});
</script>