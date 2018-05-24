<?php
//get the q parameter from URL
$q=$_GET["q"];
//$xml=("https://cointelegraph.com/rss");
//$xml=("http://www.newsbtc.com/feed/");
//find out which feed was selected
if($q=="NewsBTC") {
  $xml=("http://www.newsbtc.com/feed/");
} elseif($q=="CoinT") {
  $xml=("https://cointelegraph.com/rss");
}

$xmlDoc = new DOMDocument();
$xmlDoc->load($xml);



//get elements from "<channel>"
$channel=$xmlDoc->getElementsByTagName('channel')->item(0);
$channel_title = $channel->getElementsByTagName('title')
->item(0)->childNodes->item(0)->nodeValue;
if($channel->getElementsByTagName('link')->length > 0) { 
$channel_link = $channel->getElementsByTagName('link')
->item(0)->childNodes->item(0)->nodeValue;
}
$channel_desc = $channel->getElementsByTagName('description')
->item(0)->childNodes->item(0)->nodeValue;

//output elements from "<channel>"
echo("<p ><a href='" . $channel_link
  . "'>" . $channel_title . "</a>");
echo("<br>");
echo($channel_desc . "</p>"); 
echo("<div id='content'>");
echo("<ul>");
//get and output "<item>" elements
$x=$xmlDoc->getElementsByTagName('item');
for ($i=0; $i<=2; $i++) {
  $item_title=$x->item($i)->getElementsByTagName('title')
  ->item(0)->childNodes->item(0)->nodeValue;
  $item_link=$x->item($i)->getElementsByTagName('link')
  ->item(0)->childNodes->item(0)->nodeValue;
  $item_desc=$x->item($i)->getElementsByTagName('description')
  ->item(0)->childNodes->item(0)->nodeValue;
  echo ("<p><li><a href='" . $item_link
  . "'>" . $item_title . "</a></li>");
  echo ("<br>");
  echo ($item_desc . "</p>");
}
echo("<ul>");
echo("</div>");
?> 	