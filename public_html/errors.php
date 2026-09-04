<?php  
if (count($errors) > 0) : ?>
	<div class="error">
		<?php foreach ($errors as $error) : ?>
         <div class="alert alert-danger alert-dismissible">
          <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <?php echo $error ?>
          </div>
		<?php endforeach ?>
	</div>
<?php  endif ?>
