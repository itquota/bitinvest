<?php
error_reporting(0);
ini_set("memory_limit","102400M");
set_time_limit(10000);
//date_default_timezone_set('Asia/Calcutta');
$companyName = "GainBitCoin";

$servername = "172.31.1.7";

  $dbname = "gain_bitcoin";


$username = "web_user";

$password = "0Onb%4#bsFD";  

 // $dbname = "gainbitco_new5";
/* $dbname = "gainbitcoin_04-02";
$username = "root";
$password = ""; */
/* $dbname = "bitcoin";
$username = "bitcoin";
$password = "bitcoinpassword"; */

/* Leadership */
$assignDateFix="31st-May-2015";
$assignLeadershipPoolFix=100;
$level_fisrt_percentage=10;
$default = "Gain@021116";
$refundDate = '2016-10-01 00:00:00';
$checkRefundDate = '2016-11-13 00:00:00';


//define('DB_SERVER', 'gbc-test.cniiibtqfavh.us-east-1.rds.amazonaws.com');
//define('DB_USERNAME', 'gbctest');
//define('DB_PASSWORD', 'gbctest#123');
//define('DB_DATABASE', 'gbc_live');

if(isset($_SERVER['APPLICATION_ENV']) && ($_SERVER['APPLICATION_ENV'] == "production")){
	define('DB_SERVER', 'gbcluster.cluster-cu5ppo026svh.us-east-1.rds.amazonaws.com');
}else{
	define('DB_SERVER', 'gbstage.cu5ppo026svh.us-east-1.rds.amazonaws.com');
}

function getDB() 
{
	$dbhost=DB_SERVER;
	$dbuser=DB_USERNAME;
	$dbpass=DB_PASSWORD;
	$dbname=DB_DATABASE;
	try {
	$dbConnection = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbConnection->exec("set names utf8");
	$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbConnection;
    }
    catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
	}

}
// Textlocal account details
// $MSGusername = '1bitcoindaily@gmail.com';
// $MSGhash = 'Test@123';
// $MSGsender = urlencode('TXTLCL');
	$MSGusername = '7827572892';
$MSGhash = 'amit123456';
$MSGsender = urlencode('GBCOIN');		
			
			

$sessionTimeout = 60;

$resultsPerPage = 3;
$folerName = "cpnew";

if (!empty($_SERVER['HTTPS']) || !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
   
   $rootURL = "https://".$_SERVER['HTTP_HOST']."/".$folerName;
   $Root = "https://" . $_SERVER['HTTP_HOST'];
}else{
	$rootURL = "http://".$_SERVER['HTTP_HOST']."/".$folerName;
	$Root = "http://" . $_SERVER['HTTP_HOST'];
}

// $rootURL = "http://".$_SERVER['HTTP_HOST']."/".$folerName;
//$rootURL = "http://www.bestcloudmine.com";
$blockchain_root = "https://blockchain.info/";
$secret = "secretbestcloudminingsecret";
$noOfConfReqd = 1;

$shaProfitabilityData = array("BTC","PPC","ABC");
$scryptProfitabilityData = array("LTC","POT","FLT");

// $minBTCWithdrawalAmt = 0.1;
$minBTCWithdrawalAmt = 0.001;
$minNICWithdrawalAmt = 10;

$captchaPublickey = "6LdbofoSAAAAAJptzuqSuoZjbfESToZaeSCWE4tM"; 
$captchaPrivatekey = "6LdbofoSAAAAALbHOIlrC1mPNzwI0ItCFUffahfn";

$salt = md5('4@(&gD8j)vf45fc$54i8745h*$#n3)kj34bbv1');
$HashedKey = sha1($salt);

$tokenExpiryTime = 60*60;

$blanks= array("", " ", '', ' ');

$fixedPoolFeesSHA = 0.00004;
$fixedPoolFeesScrypt = 0.00008;

/* Currency added in dollor*/
$current_rate=5;
$buy_rate=array('method'=>'add','val'=>3);
/* Use add less mehod for sell rate*/

$lp_reffral=1;
$lp_max_value_limit=50;/* This Valuues is cofugrable MaxBTC Limit */
$lp_min_value_limit=35;/* This value is configurable after MaxUser Limit*/
$lp_max_user_limit=100;/* This Value is congurable UP to max user for max value limit*/

$mx_binary_income=25;
$sell_rate=array('method'=>'less','val'=>2);
function printArr($arr) {
	print("<pre>");
		print_r($arr);
	print("</pre>");
}

$graphDurationsMap = array(1=>"Today's", 7=>"This week's", 30=>"This Month", 90=>"Last 3 months", 180=>"Last 6 months", 365=>"This year");

$monthsMap = array(
	1=>"January",2=>"February",3=>"March",4=>"April",5=>"May",6=>"June",7=>"July",8=>"August",9=>"September",10=>"October",11=>"November",12=>"December"
);

$httpBadResponses = array(
	400 =>"Bad Request",
	401=>"Authentication Required",
	403 =>"Forbidden: Not sufficient Access",
	404 =>"Not Found",
	500 =>"Youtube Error",
	501 =>"No such command or operation",
	503 =>"Youtube API unreachable"
);

$states_arr = array('AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'DC'=>"District Of Columbia",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa",  'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland", 'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma", 'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming", 'AS'=>'American Samoa', 'DC'=>'District of Columbia', 'GU'=>'Guam', 'MP'=>'Northern Marina Islands', 'PR'=>'Puerto Rico', 'VI'=>'U.S. Virgin Islands');


$PaginateLimit = 50;
$MaxSilverKits = 350;
$MaxRowsCount = 1000;

/*
function cleanQueryParameter
Purpose: To clean inputs to queries in order to prevent SQL Injection Attacks
Arguments: The parameter to be cleaned
Returns: The escaped parameter
*/
function cleanQueryParameter($string) {
	
	if(is_array($string)){
		foreach($string as $key => $value){
			$value = 	trim($value);	
			// prevents duplicate backslashes
			if(get_magic_quotes_gpc()) { 
				$value = stripslashes($value);
			}
				
			//escape the string with backward compatibility
			if (phpversion() >= '4.3.0'){
				$value = mysql_real_escape_string($value);
			} else{
				$value = mysql_escape_string($value);
			}
			
			$string[$key] = $value;		
		}
	}else{
		//remove extraneous spacess
		$string = trim($string);
		// prevents duplicate backslashes
		if(get_magic_quotes_gpc()) { 
			$string = stripslashes($string);
		}
			
		//escape the string with backward compatibility
		if (phpversion() >= '4.3.0'){
			$string = mysql_real_escape_string($string);
		} else{
			$string = mysql_escape_string($string);
		}
	}
	return $string;
}
/*
function cleanDisplayParameter
Purpose: To clean outputs to screen in order to prevent XSS Attacks
Arguments: The parameter to be cleaned
Returns: The escaped parameter
*/
function cleanDisplayParameter($string) {
	//strip HTML tags from input data
	$string = strip_tags($string);
	//turn all characters into their html equivalent	Â Â 
	$string = htmlentities(stripslashes($string), ENT_QUOTES);
	
	return $string;
}

/*
function noError
Purpose: To check error arrays for no error state
Arguments: The error array returned by any function
Returns: True/False
*/
function noError($errorArr) {
	$noError = false;
	if(array_key_exists(-1, $errorArr["errCode"]))
		$noError=true;
	return $noError;
}

/*
function sendMail
Purpose: To send emails
Arguments: The from and to email addresses, subject and message
Returns: error array
*/
function sendMail($to, $from, $subject, $message, $bcc='') {
	//printArr(func_get_args()); exit;
	$returnArr = array();
	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	    $headers .= "Reply-To: thegainbitcoinhelp@gmail.com\r\n";
    $headers .= "Return-Path: gainbitcoin@server1.gainbitcoin.com\r\n";
	
	// Additional headers
	// $headers .= 'To: ' .$to. "\r\n";
	$headers .= 'From: ' .$from. "\r\n";
	if($bcc){
		$headers .= 'BCC: gblinkupdate@gmail.com' . "\r\n";
	}
	// Mail it
	if(mail($to, $subject, $message, $headers)){
		$returnArr["errCode"][-1]=-1;
		$returnArr["errMsg"]="Email Sent";
	} else {
		$returnArr["errCode"][8]=8;
		$returnArr["errMsg"]="Email Send Error";
	}
	
	return $returnArr;
}

/*
function per_days_diff
Purpose: To calculate date differences
Arguments: The start and end dates
Returns: array of differences in different formats
*/
function per_days_diff($start_date, $end_date) {
	$per_days = 0;
	$noOfWeek=0;
	$noOfWeekEnd =0;
	$highSeason=array("7","8");
	
	$current_date = strtotime($start_date);
	$current_date += (24 * 3600);
	$end_date = strtotime($end_date);
	
	$seassion = (in_array(date('m', $current_date),$highSeason))?"2":"1";
	
	$noOfdays = array('');
	
	while ($current_date <= $end_date) {
		if ($current_date <= $end_date) {
			$date = date('N', $current_date); 
			array_push($noOfdays,$date);
			$current_date = strtotime('+1 day', $current_date);
		}
	}
	$finalDays = array_shift($noOfdays);
	//print_r($noOfdays);
	$weekFirst = array("week"=>array(),"weekEnd"=>array());
	for($i=0;$i<count($noOfdays);$i++)
	{
		if($noOfdays[$i]==1) {
			//echo "this is week";
			//echo "<br/>";
			if($noOfdays[$i+6]==7){
				$noOfWeek++;
				$i=$i+6;
			} else {
				$per_days++;
			}
		//array_push($weekFirst["week"],$day);
		} else if($noOfdays[$i]==5) {
			//echo "this is weekend";
			//echo "<br/>";
			if($noOfdays[$i+2] ==7) {
				$noOfWeekEnd++;
				$i = $i+2;
			} else {
				$per_days++;
			}
			//echo "after weekend value:- ".$i;
			//echo "<br/>";
		} else {
			$per_days++;
		}	
	}
	/*echo $noOfWeek;
	echo "<br/>";
	echo $noOfWeekEnd;
	echo "<br/>";
	print_r($per_days);
	echo "<br/>";
	print_r($weekFirst);
	*/	
	$duration = array("week"=>$noOfWeek,"weekEnd"=>$noOfWeekEnd,"perDay"=>$per_days,"seassion"=>$seassion);
	return $duration;
}

function getFirstTwoWords($string) {
	$allWords = explode(" ", $string);
	
	return $allWords[0]." ".$allWords[1]." ";		
}


// define percentage....
$webMasterPrntge = 75;
$adminPrntge = 5;
$distributePrntge = 20;


$fileExtensions = array(php,java,inc,js,jse,net,html,xml,pyzw,xlnk,jpg,jpeg,png,gif,in,com,org);


function ValidateData($data,$fileExtensions){
	if(is_array($data)){
		$WithoutTagData = filter_var_array($data, FILTER_SANITIZE_STRING);
		$WithoutTagData =str_replace('?','',$WithoutTagData);
		$WithoutTagData =str_replace('&','',$WithoutTagData);
	}else{
		$WithoutTagData = filter_var($data, FILTER_SANITIZE_STRING);
		$WithoutTagData =str_replace('?','',$WithoutTagData);
		$WithoutTagData =str_replace('&','',$WithoutTagData);
	}
	
	foreach($fileExtensions as $fileExtension){
		if(is_array($WithoutTagData)){
			foreach($WithoutTagData as $key => $Data){
				$WithoutTagData[$key] = str_replace('.'.$fileExtension,'',$Data);
			}
		}else{
			$WithoutTagData = str_replace('.'.$fileExtension,'',$WithoutTagData);
		}
		
		// echo $fileExtension;
	}
	
	// $WithoutTagData = highlight_string ($WithoutTagData);
	return $WithoutTagData;
}

?>
