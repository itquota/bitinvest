<?php
//error_reporting(1);
ini_set("memory_limit","102400M");
set_time_limit(10000);
//date_default_timezone_set('Asia/Calcutta');
$companyName = "GainBitCoin";

/*
  $dbname = "gainbitc_gainbitcoin";


$username = "gainbitc_gainbit";

$password = "L3@{srTu_E&Q";  
*/
 // $dbname = "gainbitco_new5";

//$servername = "localhost";
//$dbname = "gainbitc_local";
// $dbname = "gbc_live";
//$username = "root";
//$password = "111"; 


//$servername = "gb-stage-db-cluster.cluster-cu5ppo026svh.us-east-1.rds.amazonaws.com";
//$dbname = "gain_bitcoin";
//$username = "webapp";
//$password = "s714YNrX01vw"; 

$servername = "gbcluster.cluster-cu5ppo026svh.us-east-1.rds.amazonaws.com";
$dbname = "gain_bitcoin";
$username = "webapp";
$password = "qDtearL9Oar1";


function createDbConnection($servername, $username, $password, $dbname) {
    $returnArr = array();

    $conn = mysql_connect($servername, $username, $password);
	
    if (!$conn) {
        $returnArr["errCode"][5] = 5;
        $returnArr["errMsg"] = "Could not connect to DB: " . mysql_error();
    } else {
        if (!mysql_select_db($dbname, $conn)) {
            $returnArr["errCode"][6] = 6;
            $returnArr["errMsg"] = "Could not select DB: " . mysql_error();
        } else {
            $returnArr["errCode"][-1] = -1;
            $returnArr["errMsg"] = $conn;
        }
    }
	// var_dump($returnArr);
    return $returnArr;
}


function noError($errorArr) {
	$noError = false;
	if(array_key_exists(-1, $errorArr["errCode"]))
		$noError=true;
	return $noError;
}


?>
