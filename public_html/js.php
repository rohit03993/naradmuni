<!-- initialize jQuery Library -->
	<script src="<?php echo $urlroot; ?>js/jquery.min.js"></script>
	<!-- navigation JS -->
	<script src="<?php echo $urlroot; ?>js/navigation.js"></script>
	<!-- Popper JS -->
	<script src="<?php echo $urlroot; ?>js/popper.min.js"></script>

	<!-- magnific popup JS -->
	<script src="<?php echo $urlroot; ?>js/jquery.magnific-popup.min.js"></script>



	<!-- Bootstrap jQuery -->
	<script src="<?php echo $urlroot; ?>js/bootstrap.min.js"></script>
	<!-- Owl Carousel -->
	<script src="<?php echo $urlroot; ?>js/owl-carousel.2.3.0.min.js"></script>
	<!-- slick -->
	<script src="<?php echo $urlroot; ?>js/slick.min.js"></script>

	<!-- smooth scroling -->
	<script src="<?php echo $urlroot; ?>js/smoothscroll.js"></script>

<script src="<?php echo $urlroot; ?>js/main.js"></script>
<script>
    var access_link='<?php echo $urlroot; ?>';
</script>
<script src="<?php echo $urlroot; ?>nm-script.js"></script>
<script>
x.style.display === "none";
function myFunction() {
  var x = document.getElementById("SearchForm");
  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
}

$('body').on('copy',function(e) {
    e.preventDefault();
    return false;
});
document.addEventListener('contextmenu', event => event.preventDefault());
document.onkeydown = function(e) {
        if (e.ctrlKey && 
            (e.keyCode === 67 || 
             e.keyCode === 86 || 
             e.keyCode === 85 || 
             e.keyCode === 117)) {
            return false;
        } else {
            return true;
        }
};
$(document).keypress("u",function(e) {
  if(e.ctrlKey)
  {
return false;
}
else
{
return true;
}
});
    
    window.oncontextmenu = function () {
            return false;
        }
        $(document).keydown(function (event) {
            if (event.keyCode == 123) {
                return false;
            }
            else if ((event.ctrlKey && event.shiftKey && event.keyCode == 73) || (event.ctrlKey && event.shiftKey && event.keyCode == 74)) {
                return false;
            }
        });
</script>