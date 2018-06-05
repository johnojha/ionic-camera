<?php

ini_set('display_errors', 0);

include('simple_html_dom.php');

session_start();

$_SESSION['testemail']="";
//$url = "https://www.googleapis.com/customsearch/v1?key=AIzaSyBd420O3u-g4Ne1-8iaghzyR3xSGty2o3c&cx=007419303966640618058:fumimds_2f0&q=".$query;

static $totalemails='dd';


function strip_tags_content($text, $tags = '', $invert = FALSE) {
	/*
	This function removes all html tags and the contents within them
	unlike strip_tags which only removes the tags themselves.
	*/
	//removes <br> often found in google result text, which is not handled below
	$text = str_ireplace('<br>', '', $text);
 
	preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
	$tags = array_unique($tags[1]);
 
	if(is_array($tags) AND count($tags) > 0) {
		//if invert is false, it will remove all tags except those passed a
		if($invert == FALSE) {
			return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
		//if invert is true, it will remove only the tags passed to this function
		} else {
			return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
		}
	//if no tags were passed to this function, simply remove all the tags
	} elseif($invert == FALSE) {
		return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
	}
 
	return $text;
}


function file_get_contents_curl($url) {
	/*
	This is a file_get_contents replacement function using cURL
	One slight difference is that it uses your browser's idenity
	as it's own when contacting google. 
	*/
	$ch = curl_init();
 
	curl_setopt($ch, CURLOPT_USERAGENT,	$_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
 
	$data = curl_exec($ch);
	curl_close($ch);
 
	return $data;
}


$domain=$_REQUEST["domain"];




if(!isset($_REQUEST["domain"]))
$domain="diversitech.com";
else
$domain=$_REQUEST["domain"];

$domain=str_replace("http://","",$domain);
$domain=str_replace("https://","",$domain);



 $domain="http://" . $domain;


//echo "domain" . $domain; 
$parse=parse_url($domain);



$domain=$parse['host'];

//echo "domain" . $domain;

$domain=str_replace("www.","",$domain);
$domain=str_replace("http://","",$domain);
$domain=str_replace("https://","",$domain);

$domain=str_replace("/","",$domain);

//echo "domain " . $domain;

//$ext=explode(".",$domain);

$ext=strpos($domain,'.');

//print_r($ext);

$len=count($domain);

$mainext=substr($domain,$ext+1);



//echo   "<br /> main ext" .  $mainext;


$url='https://www.google.com/search?q=email+"' . "@" . $domain . '"';  //"%40diversitech.com"';

//echo  $url;

$html = file_get_html($url);






$result = array();
 
foreach($html->find('div.g') as $g)
{
	/*
	each search results are in a list item with a class name 'g'
	we are seperating each of the elements within, into an array
 
	Titles are stored within <h3><a...>{title}</a></h3>
	Links are in the href of the anchor contained in the <h3>...</h3>
	Summaries are stored in a div with a classname of 's'
	*/
 
	$h3 = $g->find('h3.r', 0);
	$s = $g->find('div.s', 0);
	$a = $h3->find('a', 0);
	$result[] = array('title' => strip_tags($a->innertext), 
		'link' => $a->href, 
		'description' => strip_tags_content($s->innertext));
}

//echo "<pre>";
//print_r($result);

$i=0;
$testemail='';

foreach($result as $t)
{
	$mainurl=str_replace('/url?q=',"",$t['link']);

	$content = file_get_html($mainurl);

	//print_r($html);
	
    $_SESSION['testemail']=$_SESSION['testemail'] . extract_email_address($content);

	//echo "testingin  " . $testemail;
	
	$i=$i+1;

	if($i==9)
		break;
   
}

function extract_email_address ($string) {
	$matches = array();
	$pattern = '/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}/i';

	//$pattern='/^[a-zA-Z0-9]+@[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]/';


	preg_match_all($pattern,$string,$matches);

	//echo "<pre>";
	//print_r($matches[0]);
	
	if(count($matches[0])>1)
	{
		
		foreach($matches[0] as $email) 
		{
			$totalemails=$totalemails . $email . "~";
		}
	
	}
	//print_r($matches[0]);
	return $totalemails;
}



$allemails=explode("~",$_SESSION['testemail']);

//echo "<pre>";
//print_r($allemails);

$finalemail="";
foreach($allemails as $email) 
{
			$mainemail=explode("@",$email);
			$mainemail1=$mainemail[1];
			//echo $mainemail1  . "---" . $domain;
			if($mainemail1==$domain)
			$finalemail=$finalemail . $email . ",";
}

//echo $finalemail;



//$fakeemails=array("John_Smith@");


//echo $finalemail;

$checkemail=explode(",",$finalemail);

//echo count($checkemail);
$parsedemail="";
foreach($checkemail as $email1)
{
	
	$parseEmail=explode("@",$email1);

	$parseEmail=$parseEmail[0] . "@";

	//echo $parseEmail . "in  loop";

	
	
		$parsedemail=$parsedemail . $email1  . ",";
	

}



$testemail=explode(",",$parsedemail);

$parsedemail1="";
foreach(array_unique($testemail) as $email)
{
  $parsedemail1=$parsedemail1 . $email  . "<br />";
}

echo $parsedemail1;
//echo $parsedemail;



?>



