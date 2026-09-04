<?php  
if (count($sucs) > 0) : ?>
	<div class="success">
		<?php foreach ($sucs as $sucsess) : ?>
         <div class="alert alert-success alert-dismissible">
          <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <?php echo $sucsess ?>
          </div>
		<?php endforeach ?>
	</div>
<?php  endif ?>