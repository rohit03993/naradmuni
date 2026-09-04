<?php
class PerPage {
	public $perpage;
	
	function __construct() {
		$this->perpage = 5;
	}
	
	function getAllPageLinks($count,$href) {
		$output = '';
		if(!isset($_GET["page"])) $_GET["page"] = 1;
		if($this->perpage != 0)
			$pages  = ceil($count/$this->perpage);
		if($pages>1) {
			if($_GET["page"] == 1) 
				$output = $output . '<li><span class="link first disabled">&#8810;</span><span class="link disabled">&#60;</span></li>';
			else	
				$output = $output . '<li><a class="link first" onclick="getresult(\'' . $href . (1) . '\')" >&#8810;</a></li><li><a class="link" onclick="getresult(\'' . $href . ($_GET["page"]-1) . '\')" >&#60;</a></li>';
			
			
			if(($_GET["page"]-3)>0) {
				if($_GET["page"] == 1)
					$output = $output . '<li class="active"><a href="#"><span id=1 class="link current">1</span></a></li>';
				else				
					$output = $output . '<li><a class="link" onclick="getresult(\'' . $href . '1\')" >1</a></li>';
			}
			if(($_GET["page"]-3)>1) {
					$output = $output . '<span class="dot">...</span>';
			}
			
			for($i=($_GET["page"]-2); $i<=($_GET["page"]+2); $i++)	{
				if($i<1) continue;
				if($i>$pages) break;
				if($_GET["page"] == $i)
					$output = $output . '<li class="active"><a href="#"><span id='.$i.' class="link current">'.$i.'</span></a></li>';
				else				
					$output = $output . '<li style="background-color:lightgray;border-radius:50%;color:#000;margin:0 1%"><a class="link" onclick="getresult(\'' . $href . $i . '\')" >'.$i.'</a></li>';
			}
			
			if(($pages-($_GET["page"]+2))>1) {
				$output = $output . '<li><span class="dot">...</span></li>';
			}
			if(($pages-($_GET["page"]+2))>0) {
				if($_GET["page"] == $pages)
					$output = $output . '<li class="active"><span id=' . ($pages) .' class="link current">' . ($pages) .'</span></li>';
				else				
					$output = $output . '<li><a class="link" onclick="getresult(\'' . $href .  ($pages) .'\')" >' . ($pages) .'</a></li> ';
			}
			
			if($_GET["page"] < $pages)
				$output = $output . '<li><a  class="link" onclick="getresult(\'' . $href . ($_GET["page"]+1) . '\')" >></a></li><li><a  class="link" onclick="getresult(\'' . $href . ($pages) . '\')" >&#8811;</a> </li>';
			else				
				$output = $output . ' <li><span class="link disabled">></span><span class="link disabled">&#8811;</span></li> ';
			
			
		}
		return $output;
	}
	function getPrevNext($count,$href) {
		$output = '';
		if(!isset($_GET["page"])) $_GET["page"] = 1;
		if($this->perpage != 0)
			$pages  = ceil($count/$this->perpage);
		if($pages>1) {
			if($_GET["page"] == 1) 
				$output = $output . ' <li><span class="link disabled first">Prev</span></li> ';
			else	
				$output = $output . '<li> <a class="link first" onclick="getresult(\'' . $href . ($_GET["page"]-1) . '\')" >Prev</a></li> ';			
			
			if($_GET["page"] < $pages)
				$output = $output . '<li> <a  class="link" onclick="getresult(\'' . $href . ($_GET["page"]+1) . '\')" >Next</a></li>';
			else				
				$output = $output . '<li> <span class="link disabled">Next</span></li>';
			
			
		}
		return $output;
	}
}
?>