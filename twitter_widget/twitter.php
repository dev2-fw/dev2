<?php
error_reporting(E_ALL ^ E_NOTICE);
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

<?php


$page = clean($_GET['page']);
$cat  = clean($_GET['cat']);
$searchtwt = clean($_GET['searchtwt']);

if($searchtwt=='')
	$searchtwt = clean($_POST['searchtwt']);

require('../config.inc.php');
$con = mysql_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD) or die('Could not connect to the server!');
mysql_select_db(DB_DATABASE) or die('Could not select a database!');


if($page=='home'){


// Step 2 - How many tweets to you want to show? Swap 4 for how many you would like.
$numberoftweets = "1";
// Step 3 - Would you like to activate links within the tweets?
$tags = true;
// Step 4 - Would you like to activate nofollow (Best for SEO)?
$nofollow = true;
// Step 5 - Would you like links to appear in a new window/tab?]
$target = true;
// Step 6 - Would you like to show the Twitter Follow Widget button?
$widget = true;




/*
$sql_fanwires = "SELECT twitter FROM tbl_fanwires WHERE twitter!=''";
if($cat!=''){
$sql_fanwires .= " AND category1='".$cat."'";
}
$sql_fanwires .= " ORDER BY rand() LIMIT 10";
*/

$sql_fanwires = "SELECT twitter FROM tbl_fanwires";
if($cat!=''){
	$sql_fanwires .= " INNER JOIN tbl_fanwire_categories ON (tbl_fanwires.id = tbl_fanwire_categories.fanwire_id)";
	$sql_fanwires .= " WHERE  twitter!='' AND category='".$cat."'";
}else{
	$sql_fanwires .= " WHERE  twitter!=''";
}
$sql_fanwires .= " ORDER BY rand() LIMIT 10";

$result_fanwires = mysql_query($sql_fanwires);

$counter = 0;
while($row_fanwires = mysql_fetch_array($result_fanwires)){
	$twitterid = $row_fanwires['twitter'];
?>
 <img id="profile_img" src="http://api.twitter.com/1/users/profile_image/<?php echo $twitterid?>.json">
<span id="title"><strong>@<?php echo $twitterid?></strong></span><br>
<?php

	$tweetxml = "http://search.twitter.com/search.atom?q=from:" . $twitterid . "&rpp=" . $numberoftweets . "";
    getLatestTweet($tweetxml, $tags, $nofollow, $target, $widget);
	if($counter!=mysql_num_rows($result_fanwires)-1) echo "<hr>";
	$counter++;
}




} else if($page=='profile'){


$numberoftweets = "15";
$tags = true;
$nofollow = true;
$target = true;

if($searchtwt!=''){
	$twitterid = $searchtwt;
}else{
	$twitterid = 'shakira';
}
?>


<?
$tweetxml = "http://search.twitter.com/search.atom?q=from:" . $twitterid . "&rpp=" . $numberoftweets . "";
getLatestTweet_profile($tweetxml, $tags, $nofollow, $target, $widget);



}


function clean($str){
return str_replace("'","",$str);
}


 
    // Here's the Science - futher comments can be found below
    function changeLink($string, $tags=false, $nofollow, $target){
      if(!$tags){
       $string = strip_tags($string);
      } else {
       if($target){
        $string = str_replace("<a", "<a target=\"_blank\"", $string);
       }
       if($nofollow){
        $string = str_replace("<a", "<a rel=\"nofollow\"", $string);
       }
      }
      return $string;
     }
 
     function getLatestTweet($xml, $tags=false, $nofollow=true, $target=true,$widget=false){
        global $twitterid;
      $xmlDoc = new DOMDocument();
      $xmlDoc->load($xml);
 
      $x = $xmlDoc->getElementsByTagName("entry");
 
      $tweets = array();
      foreach($x as $item){
       $tweet = array();
 
       if($item->childNodes->length)
       {
        foreach($item->childNodes as $i){
         $tweet[$i->nodeName] = $i->nodeValue;
        }
       }
        $tweets[] = $tweet;
      }
 
    // Here's the opening DIV and List Tags.
	
       echo "<div id=\"latesttweet\" style=\"padding-bottom:15px;\">\n";
 
      foreach($tweets as $tweettag){
       $tweetdate = $tweettag["published"];
       $tweet = $tweettag["content"];
       $timedate = explode("T",$tweetdate);
       $date = $timedate[0];
       $time = substr($timedate[1],0, -1);
       $tweettime = (strtotime($date." ".$time))+3600; // This is the value of the time difference - UK + 1 hours (3600 seconds)
       $nowtime = time();
       $timeago = ($nowtime-$tweettime);
       $thehours = floor($timeago/3600);
       $theminutes = floor($timeago/60);
       $thedays = floor($timeago/86400);
       if($theminutes < 60){
        if($theminutes < 1){
         $timemessage =  "Less than 1 minute ago";
        } else if($theminutes == 1) {
         $timemessage = $theminutes." minute ago.";
         } else {
         $timemessage = $theminutes." minutes ago.";
         }
        } else if($theminutes > 60 && $thedays < 1){
         if($thehours == 1){
         $timemessage = $thehours." hour ago.";
         } else {
         $timemessage = $thehours." hours ago.";
         }
        } else {
         if($thedays == 1){
         $timemessage = $thedays." day ago.";
         } else {
         $timemessage = $thedays." days ago.";
         }
        }
        // Here's the list tags wrapping each tweet.
        echo "".changeLink($tweet, $tags, $nofollow, $target)."<br />\n";
        // Here's the span wrapping the time stamp.
        echo "<span><i>".$timemessage."</i></span>\n";
       }
    // Here's the closing DIV and List Tags.
        echo "</div>";
 
        // Here's the Twitter Follow Button Widget
        if($widget){
            echo "<a href=\"https://twitter.com/" .$twitterid. "\" class=\"twitter-follow-button\" data-show-count=\"false\">Follow @" .$twitterid. "</a>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=\"//platform.twitter.com/widgets.js\";fjs.parentNode.insertBefore(js,fjs);}}(document,\"script\",\"twitter-wjs\");</script>";
        }
       
 
     }

	 
	 
	 
	 
    function getLatestTweet_profile($xml, $tags=false, $nofollow=true, $target=true,$widget=false){
       global $twitterid;
      $xmlDoc = new DOMDocument();
      $xmlDoc->load($xml);
 
      $x = $xmlDoc->getElementsByTagName("entry");
 
      $tweets = array();
      foreach($x as $item){
       $tweet = array();
 
       if($item->childNodes->length)
       {
        foreach($item->childNodes as $i){
         $tweet[$i->nodeName] = $i->nodeValue;
        }
       }
        $tweets[] = $tweet;
      }
 

		$counter = 0;
      foreach($tweets as $tweettag){
	    echo "<div id=\"latesttweet\" style=\"padding-bottom:15px;\">\n";  
		echo "<img id='profile_img' src='http://api.twitter.com/1/users/profile_image/".$twitterid.".json'>";
		echo "<span id='title'><strong>@".$twitterid."</strong></span><br>";
	  
       $tweetdate = $tweettag["published"];
       $tweet = $tweettag["content"];
       $timedate = explode("T",$tweetdate);
       $date = $timedate[0];
       $time = substr($timedate[1],0, -1);
       $tweettime = (strtotime($date." ".$time))+3600; // This is the value of the time difference - UK + 1 hours (3600 seconds)
       $nowtime = time();
       $timeago = ($nowtime-$tweettime);
       $thehours = floor($timeago/3600);
       $theminutes = floor($timeago/60);
       $thedays = floor($timeago/86400);
       if($theminutes < 60){
        if($theminutes < 1){
         $timemessage =  "Less than 1 minute ago";
        } else if($theminutes == 1) {
         $timemessage = $theminutes." minute ago.";
         } else {
         $timemessage = $theminutes." minutes ago.";
         }
        } else if($theminutes > 60 && $thedays < 1){
         if($thehours == 1){
         $timemessage = $thehours." hour ago.";
         } else {
         $timemessage = $thehours." hours ago.";
         }
        } else {
         if($thedays == 1){
         $timemessage = $thedays." day ago.";
         } else {
         $timemessage = $thedays." days ago.";
         }
        }
        // Here's the list tags wrapping each tweet.
        echo "".changeLink($tweet, $tags, $nofollow, $target)."<br />\n";
        // Here's the span wrapping the time stamp.
        echo "<span><i>".$timemessage."</i></span>\n";
		if($counter!=count($tweets)-1) echo "<hr>";
		$counter++;
		echo "</div>";
       }

 

       
 
     }	 
	 

	 
	 
	 
mysql_free_result($result_fanwires);
mysql_close($con);
?>

</body>
</html>

 
 
 
 