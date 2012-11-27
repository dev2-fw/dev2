<?php
header('Content-Type: text/html; charset=utf-8');
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); 

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />





</head>
<body>

<script type="text/javascript">
$(document).ready(function() {



  $("#searchresults a").click(function() {	
  
        $("#ajax-content").empty().append("<div id='loading' class='text-center'><img src='images/loading.gif' alt='Loading' /></div>");


			$("#searchtab").addClass("current");
			$("#hottab").removeClass("current");
			$("#celebritytab").removeClass("current");
			$("#musictab").removeClass("current");
			$("#sportstab").removeClass("current");
			
			$('#suggestions').fadeOut();
			$('#searchtab').show();
			$('#searchtab').attr("href", this.href)
		
        $.ajax({ url: this.href , success: function(html) {
            $("#ajax-content").empty().append(html);
            }
	});
	return false;
	
    });	
});	
</script>

<?php

$searchterm = $_POST["search"];
//$searchterm = $_GET["search"];

if(strlen($searchterm) < 3) die();

require('../config.inc.php');
$con = mysql_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD) or die('Could not connect to the server!');
mysql_select_db(DB_DATABASE) or die('Could not select a database!');

$searchterm = mysql_real_escape_string($searchterm);



//FANWIRES

$max_fanwire_count = 3;
$sql_fanwires = "SELECT tbl_fanwires.id as fid,name,tbl_fanwires.url as link,description,tbl_avatar_photos.url as imageurl,twitter FROM tbl_fanwires";
$sql_fanwires .= " LEFT JOIN tbl_avatar_photos ON (tbl_fanwires.id = tbl_avatar_photos.fanwire_id)";
$sql_fanwires .= " WHERE twitter!='' AND name LIKE '%".$searchterm."%'";
$sql_fanwires .= " GROUP BY tbl_fanwires.id";
$sql_fanwires .= " LIMIT 0,$max_fanwire_count";
$result_fanwires = mysql_query($sql_fanwires);
$count_fanwires = mysql_num_rows($result_fanwires);


$total_result = $count_fanwires;



if($total_result>0){
?>



<!--FANWIRES-->
<?php
	if($count_fanwires>0){
?>
	<p id="searchresults">
	<!--<span class="category">PROFILES</span>-->
		<?php
		while($row_fanwires = mysql_fetch_array($result_fanwires)){
		$description = strip_tags($row_fanwires['description']);
		if(strlen($description) > 90)
			$description  = trim(substr($description ,0,90)) . '..';
			
		if($row_fanwires['imageurl']!=''){
			$image = "/photos/".$row_fanwires['imageurl'];
		}else{
			$image = "/views/images/your_fanwire_profile_normal.png";
		}	
?>  
		
			<a href="twitter.php?page=profile&searchtwt=<?php echo strip_tags($row_fanwires['twitter'])?>">
			<img alt="<?php echo strip_tags($row_fanwires['name'])?>" src="<?php echo $image?>" <?php echo thumbnail_sizefix($image)?> />
			<span class="searchheading"><?php echo strip_tags($row_fanwires['name'])?></span>
			<span><?php echo $description?></span>
			</a>
		
<?php }?>	   
</p>
<?php 
	} //if($count_fanwires>0)
?>	
<!--FANWIRES ENDS -->	
	

	
<?php	
} //if($total_result>0)




mysql_free_result($result_fanwires);


mysql_close($con);


function fixurl($title){
    $title = str_replace("'", "", $title);
    $title = str_replace(" ", "-", $title);
    $title = str_replace(",", "-", $title);
    $title = str_replace(".", "-", $title);
    $title = str_replace("(", "-", $title);
    $title = str_replace(")", "-", $title);
    $title = str_replace('"', '-', $title);
    $title = str_replace('#', '', $title);
    $title = str_replace('&', '', $title);
return $title;
}


function thumbnail_sizefix($target){

	$img = getimagesize("http://dev2.fanwire.com/".$target);

	
	$max_width = 80;
	$max_height = 46;

	$old_width  = $img[0];
	$old_height = $img[1];


	$scale      = min($max_width/$old_width, $max_height/$old_height);


	$new_width  = ceil($scale*$old_width);
	$new_height = ceil($scale*$old_height);

return "width=\"$new_width\" height=\"$new_height\"";
}
 
?>


</body>
</html>











