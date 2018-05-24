<?php

require_once("Rest.inc.php");
require_once("config.php");

class API extends REST {

	public $data = "";
	/*  const DB_SERVER = "localhost";
	 const DB_USER = "bitcoin";
	 // const DB_PASSWORD = "}GP{H^%hj75#Aa";
	 // const DB = "gainbitc_maindb_live";
	 const DB = "bitcoin";
	 const DB_PASSWORD = "bitcoinpassword"; */
	// const DB_SERVER = "localhost";
	// const DB_USER = "root";
	// const DB_PASSWORD = "}GP{H^%hj75#Aa";
	// const DB = "gainbitc_maindb_live";
//	const DB_SERVER = "localhost";
//	 const DB = "gbc_live";
//	const DB_USER = "root";
//	 const DB_PASSWORD = "111";
	
	const DB_SERVER = "gbcluster.cluster-cu5ppo026svh.us-east-1.rds.amazonaws.com";
	const DB_USER = "webapp";
	const DB = "gain_bitcoin";
	const DB_PASSWORD = "qDtearL9Oar1";

	private $db = NULL;

	public function __construct() {
		parent::__construct(); // Init parent contructor
		$this->dbConnect();// Initiate Database connection
	}

	//Database connection
	/*    private function dbConnect() {
	$this->db = mysql_connect(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD);
	if ($this->db)
	mysql_select_db(self::DB);
	}*/
	private function dbConnect() {
		$this->db = mysqli_connect(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD,self::DB);
		if (mysqli_connect_errno())
		{
			echo "Failed to connect to MySQLI: " . mysqli_connect_error();exit;
		}
	}

	//Public method for access api.
	//This method dynmically call the method based on the query string
	public function processApi() {
		$func = strtolower(trim(str_replace("/", "", $_REQUEST['rquest'])));
		if ((int) method_exists($this, $func) > 0)
		$this->$func();
		else
		$this->response('', 404);
		// If the method not exist with in this class, response would be "Page not found".
	}

	private function currency() {

		global $current_rate;
		global $buy_rate;
		global $sell_rate;

		if ($this->get_request_method() == "POST") {
			$resultArray = array();

			$sql = mysqli_query($this->db,"SELECT * from api_unit_price WHERE status=1");
			if(mysqli_num_rows($sql) > 0)
			{
				$data = mysqli_fetch_array($sql,MYSQLI_ASSOC);
				$buy=array('0'=>$data['bitpay_rate'],'1'=>$data['unocoin_buy'],'2'=>$data['igot_buy']);
				$buy=array_filter($buy);

				$sell=array('0'=>$data['bitpay_rate'],'1'=>$data['unocoin_sell'],'2'=>$data['igot_sell']);
				$sell=array_filter($sell);

				if(empty($buy) || empty($sell))
				{
					$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'No Record Found');
					$this->response($this->json($resultArray), 200);
					die;
				}

				if (!empty($buy_rate))
				{

					if ($buy_rate['method'] == 'less') {printArr($buy_rate);die;
					$buying_rate=(array_sum($buy)/count($buy)- $buy_rate['val']);
					}elseif ($buy_rate['method'] == 'add') {
						$buying_rate=(array_sum($buy)/count($buy) + $buy_rate['val']);
					}
				}
				else {
					$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'No Record Found');
					$this->response($this->json($resultArray), 200);
					die;
				}
				 
				if (!empty($sell_rate))
				{
					if ($sell_rate['method'] == 'less') {
						$selling_rate=(array_sum($sell)/count($sell)) - $sell_rate['val'];
					}elseif ($sell_rate['method'] == 'add') {
						$selling_rate=(array_sum($sell)/count($sell)) + $sell_rate['val'];

					}
				} else {
					$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'No Record Found');
					$this->response($this->json($resultArray), 200);
					die;
				}

				if (!empty($this->_request['exc'])) {
					$exc = trim($this->_request['exc']);
				}

				if (isset($exc) && !empty($exc)) {
					$exc = strtoupper($exc);
						
					 
					$amount = 1;
					$from_Currency = "BTC";
					$to_Currency = $exc;
					$get = file_get_contents("https://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency");
					$get = explode("<span class=bld>",$get);
					$get = explode("</span>",$get[1]);
					$converted_amount = preg_replace("/[^0-9\.]/", null, $get[0]);
					// echo $converted_amount;
					// exit;
						
					//admin charges
					// $amount = 500;
					// $amount = 2000;
					$amount = 750;
					if($exc != "INR"){
						$from_Currency = "INR";
						$to_Currency = $exc;
						$get = file_get_contents("https://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency");
						$get = explode("<span class=bld>",$get);
						$get = explode("</span>",$get[1]);
						$admin_charges = preg_replace("/[^0-9\.]/", null, $get[0]);
					}else{
						$admin_charges = $amount;
					}
						
					/*  $buying_rate_js = file_get_contents("http://devel.farebookings.com/api/curconversor/USD/$exc/$buying_rate/");
					 $buying_rate = explode(' ', $buying_rate_js, 2);
					 $buying_rate = array("0"=>number_format($buying_rate[1],2),"1"=>$buying_rate[0]);
					 $buying_rate = join(' ', $buying_rate);

					 $sell_rate_js = file_get_contents("http://devel.farebookings.com/api/curconversor/USD/$exc/$selling_rate/");
					 $selling_rate = explode(' ', $sell_rate_js, 2);
					 $selling_rate = array("0"=>number_format($selling_rate[1],2),"1"=>$selling_rate[0]);
					 $selling_rate = join(' ', $selling_rate); */
						
					if(!empty($converted_amount)){
						$current_rate = number_format($converted_amount,2)." ".$exc;
						$buying_rate = number_format($converted_amount + $admin_charges,2)." ".$exc;
						// $buying_rate = number_format($converted_amount ,2)." ".$exc;
						$selling_rate = number_format($converted_amount - $admin_charges, 2)." ".$exc;
						// $selling_rate = number_format($converted_amount - ($admin_charges * 3),2)." ".$exc;

						$resultArray = array('current' => $buying_rate, 'buy' => $buying_rate, 'sell' => $selling_rate);
					}else{
						$resultArray = array('current' => $buying_rate, 'buy' => $buying_rate, 'sell' => $selling_rate);
					}
					$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $resultArray);
					$this->response($this->json($resultArray), 200);
					die;
						
				} else {

					$resultArray = array('current' => number_format($buying_rate,2) . ' USD', 'buy' => number_format($buying_rate,2) . ' USD', 'sell' => number_format($selling_rate,2) . ' USD');
					$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $resultArray);
					$this->response($this->json($resultArray), 200);
					die;
				}

			}else {
				$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'No Record Found');
				$this->response($this->json($resultArray), 200);
				die;
			}
		}/* end POST METHOD */
		elseif ($this->get_request_method() == "GET") {
			$resultArray = array();

			$sql = mysqli_query($this->db,"SELECT * from api_unit_price WHERE status=1");
			if(mysqli_num_rows($sql) > 0)
			{
				$data = mysqli_fetch_array($sql,MYSQLI_ASSOC);
				$buy=array('0'=>$data['bitpay_rate'],'1'=>$data['unocoin_buy'],'2'=>$data['igot_buy']);
				$buy=array_filter($buy);

				$sell=array('0'=>$data['bitpay_rate'],'1'=>$data['unocoin_sell'],'2'=>$data['igot_sell']);
				$sell=array_filter($sell);

				if(empty($buy) || empty($sell))
				{
					$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'No Record Found');
					$this->response($this->json($resultArray), 200);
					die;
				}

				if (!empty($buy_rate))
				{

					if ($buy_rate['method'] == 'less') {printArr($buy_rate);die;
					$buying_rate=(array_sum($buy)/count($buy)- $buy_rate['val']);
					}elseif ($buy_rate['method'] == 'add') {
						$buying_rate=(array_sum($buy)/count($buy) + $buy_rate['val']);
					}
				}
				else {
					$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'No Record Found');
					$this->response($this->json($resultArray), 200);
					die;
				}
				 
				if (!empty($sell_rate))
				{
					if ($sell_rate['method'] == 'less') {
						$selling_rate=(array_sum($sell)/count($sell)) - $sell_rate['val'];
					}elseif ($sell_rate['method'] == 'add') {
						$selling_rate=(array_sum($sell)/count($sell)) + $sell_rate['val'];

					}
				} else {
					$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'No Record Found');
					$this->response($this->json($resultArray), 200);
					die;
				}

				if (!empty($this->_request['exc'])) {
					$exc = trim($this->_request['exc']);
				}

				if (isset($exc) && !empty($exc)) {
					$exc = strtoupper($exc);
						
					// $amount = urlencode($amount);
					// $from_Currency = urlencode($from_Currency);
					// $to_Currency = urlencode($to_Currency);
					$amount = 1;
					$from_Currency = "BTC";
					$to_Currency = $exc;
					$get = file_get_contents("https://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency");
					$get = explode("<span class=bld>",$get);
					$get = explode("</span>",$get[1]);
					$converted_amount = preg_replace("/[^0-9\.]/", null, $get[0]);
					// echo $converted_amount;
					// exit;
						
					//admin charges
					// $amount = 2000;
					$amount = 750;
					if($exc != "INR"){
						$from_Currency = "INR";
						$to_Currency = $exc;
						$get = file_get_contents("https://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency");
						$get = explode("<span class=bld>",$get);
						$get = explode("</span>",$get[1]);
						$admin_charges = preg_replace("/[^0-9\.]/", null, $get[0]);
					}else{
						$admin_charges = $amount;
					}
					/*
					 $buying_rate_js = file_get_contents("http://devel.farebookings.com/api/curconversor/USD/$exc/$buying_rate/");
					 $buying_rate = explode(' ', $buying_rate_js, 2);
					 $buying_rate = array("0"=>number_format($buying_rate[1],2),"1"=>$buying_rate[0]);
					 $buying_rate = join(' ', $buying_rate);

					 $sell_rate_js = file_get_contents("http://devel.farebookings.com/api/curconversor/USD/$exc/$selling_rate/");
					 $selling_rate = explode(' ', $sell_rate_js, 2);
					 $selling_rate = array("0"=>number_format($selling_rate[1],2),"1"=>$selling_rate[0]);
					 $selling_rate = join(' ', $selling_rate); */
						
					if(!empty($converted_amount)){
						/*
						 $current_rate = number_format($converted_amount,2)." ".$exc;
						 $buying_rate = number_format($converted_amount + $admin_charges,2)." ".$exc;
						 $selling_rate = number_format($converted_amount - ($admin_charges * 3),2)." ".$exc;
						 */
						$current_rate = number_format($converted_amount,2)." ".$exc;
						$buying_rate = number_format($converted_amount + $admin_charges,2)." ".$exc;
						// $buying_rate = number_format($converted_amount ,2)." ".$exc;
						$selling_rate = number_format($converted_amount - $admin_charges, 2)." ".$exc;

						$resultArray = array('current' => $buying_rate, 'buy' => $buying_rate, 'sell' => $selling_rate);
					}else{
						$resultArray = array('current' => $buying_rate, 'buy' => $buying_rate, 'sell' => $selling_rate);
					}
					$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $resultArray);
					$this->response($this->json($resultArray), 200);
					die;
				} else {

					$resultArray = array('current' => number_format($buying_rate,2) . ' USD', 'buy' => number_format($buying_rate,2) . ' USD', 'sell' => number_format($selling_rate,2) . ' USD');
					$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $resultArray);
					$this->response($this->json($resultArray), 200);
					die;
				}

			}else {
				$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'No Record Found');
				$this->response($this->json($resultArray), 200);
				die;
			}
		}/* end POST GET */


	}

	private function curr_opt() {
		if ($this->get_request_method() == "POST") {

			$page = file_get_contents('https://bitpay.com/api/rates');
			$my_array = json_decode($page, true);
			//printArr($my_array);
			$returnArr = array();
			$j = 0;
			for ($i = 1; $i <= count($my_array) - 1; $i++) {

				$returnArr[$j]['id'] = $i;
				$returnArr[$j]['name'] = $my_array[$i]['code'];
				$j++;
			}

			$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $returnArr);
			$this->response($this->json($resultArray), 200);
			die;
		} else if ($this->get_request_method() == "GET") {
			$page = file_get_contents('https://bitpay.com/api/rates');
			$my_array = json_decode($page, true);
			$returnArr = array();
			$j = 0;
			for ($i = 1; $i <= count($my_array) - 1; $i++) {

				$returnArr[$j]['id'] = $i;
				$returnArr[$j]['name'] = $my_array[$i]['code'];
				$j++;
			}

			$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $returnArr);
			$this->response($this->json($resultArray), 200);
			die;
		} else {
			$resultArray = array('errCode' => '1', 'response' => 'failed');
			$this->response($this->json($resultArray), 406);
			die;
		}
	}

	private function json($data) {
		if (is_array($data)) {
			return json_encode($data);
		}
	}

	private function find_us()
	{

		if ($this->get_request_method() == "POST") {

			if (!empty($this->_request['city'])) {
				$city = trim($this->_request['city']);
			}
			else{
				$city='325';
			}

			$sql = mysqli_query($this->db,"SELECT A.contact_name, A.contact_address, A.mobile, B.city_name FROM find_us A, city B WHERE A.city ='$city' AND A.city=B.city_id");
			if(mysqli_num_rows($sql) > 0){
				while ($row = mysqli_fetch_array($sql,MYSQLI_ASSOC)) {
					$retArr[]=$row;
				}
			}
			 
			if(!empty($retArr))
			{

				//echo "not here"; die;
				$j = 0;
				for ($i =0; $i <=count($retArr)-1; $i++) {

					$returnArr[$j]['name'] = $retArr[$i]['contact_name'];
					$returnArr[$j]['address'] = $retArr[$i]['contact_address'];
					$returnArr[$j]['mobile'] = $retArr[$i]['mobile'];
					$returnArr[$j]['city'] = $retArr[$i]['city_name'];
					$j++;
				}

				$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $returnArr);
				$this->response($this->json($resultArray), 200);
				die;
			}
			else{ //echo "here"; die;

				$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'No Record Found');
				$this->response($this->json($resultArray), 200);
				die;
			}
		}
		elseif ($this->get_request_method() == "GET") {

			if (!empty($this->_request['city'])) {
				$city = trim($this->_request['city']);
			}
			else{
				$city='325';
			}


			$sql = mysqli_query($this->db,"SELECT A.contact_name, A.contact_address, A.mobile, B.city_name FROM find_us A, city B WHERE A.city ='$city' AND A.city=B.city_id");
			if(mysqli_num_rows($sql) > 0){
				while ($row = mysqli_fetch_array($sql,MYSQLI_ASSOC)) {
					$retArr[]=$row;
				}
			}
			if(!empty($retArr))
			{
				$j = 0;
				for ($i =0; $i <=count($retArr)-1; $i++) {

					$returnArr[$j]['name'] = $retArr[$i]['contact_name'];
					$returnArr[$j]['address'] = $retArr[$i]['contact_address'];
					$returnArr[$j]['mobile'] = $retArr[$i]['mobile'];
					$returnArr[$j]['city'] = $retArr[$i]['city_name'];
					$j++;
				}

				$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $returnArr);
				$this->response($this->json($resultArray), 200);
				die;
			}
			else{
				$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'No Record Found');
				$this->response($this->json($resultArray), 406);
				die;
			}
		}
		else {
			$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'No Record Found');
			$this->response($this->json($resultArray), 406);
			die;
		}
		 
	}

	private function city_opt()
	{
	
		if ($this->get_request_method() == "POST") {
			$sql = mysqli_query($this->db,"SELECT A.* from city A,find_us B WHERE B.city=A.city_id GROUP BY A.city_id ");
			if(mysqli_num_rows($sql) > 0){
				while ($row = mysqli_fetch_array($sql,MYSQLI_ASSOC)) {
					$retArr[]=$row;
				}
			}
			if(!empty($retArr))
			{
				$j = 0;
				for ($i =0; $i <=count($retArr)-1; $i++) {

					$returnArr[$j]['id'] = $retArr[$i]['city_id'];
					$returnArr[$j]['name'] = $retArr[$i]['city_name'];
					$j++;
				}

				$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $returnArr);
				$this->response($this->json($resultArray), 200);
				die;
			}
			else{
				$resultArray = array('errCode' => '1', 'response' => 'failed');
				$this->response($this->json($resultArray), 406);
				die;
			}
		}
		elseif ($this->get_request_method() == "GET") {
			$sql = mysqli_query($this->db,"SELECT A.* from city A,find_us B WHERE B.city=A.city_id GROUP BY A.city_id");
			if(mysqli_num_rows($sql) > 0){
				while ($row = mysqli_fetch_array($sql,MYSQLI_ASSOC)) {
					$retArr[]=$row;
				}
			}
			if(!empty($retArr))
			{
				$j = 0;
				for ($i =0; $i <=count($retArr)-1; $i++) {

					$returnArr[$j]['id'] = $retArr[$i]['city_id'];
					$returnArr[$j]['name'] = $retArr[$i]['city_name'];
					$j++;
				}

				$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $returnArr);
				$this->response($this->json($resultArray), 200);
				die;
			}
			else{
				$resultArray = array('errCode' => '1', 'response' => 'failed');
				$this->response($this->json($resultArray), 406);
				die;
			}
		}
		else{
			$resultArray = array('errCode' => '1', 'response' => 'failed');
			$this->response($this->json($resultArray), 406);
			die;
		}
	}
	 
	private function login()
	{
		 
		if($this->get_request_method()=="POST")
		{

			$returnArr=array();
			$username=$_POST['user'];
			$password=$_POST['password'];
			if( (!isset($_POST['user'])) || $username=='' || (!isset($_POST['password'])) || $password=='')
			{
				$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'Username or password wrong.!');
				$this->response($this->json($resultArray), 200);
				die;
			}
			$sql =mysqli_query($this->db,"SELECT * FROM user_info u WHERE u.binaryUser IS NOT NULL AND u.username='$username'");
			if(mysqli_num_rows($sql) > 0){
				$row = mysqli_fetch_array($sql,MYSQLI_ASSOC);
				$salt = $row["salt"];
				$pwd = $this->encryptPassword($password, $salt);
			}
			if ($pwd==$row["password"])
			{
				$sql_query=mysqli_query($this->db,"SELECT SUM(daily_earning_amt) as sum_net_amt, SUM(binary_amt) as sum_bin_amt,SUM(ref_amt) as sum_direct_amt FROM final_ledger WHERE username = '".cleanQueryParameter($username)."'");
				if(mysqli_num_rows($sql_query) > 0){
					$earning = mysqli_fetch_array($sql_query,MYSQLI_ASSOC);
				}
				if(!empty($earning['sum_net_amt']))
				{ $daily_tot=$earning['sum_net_amt'];}
				else{ $daily_tot=0;}
				$daily_wid=$this->getTotalWithdrawn($username,'daily_earn');
				$daily_bal=$daily_tot-$daily_wid['sum_net_withdrawn'];
				if(!empty($earning['sum_bin_amt']))
				{
					$bin_tot=$earning['sum_bin_amt'];
				}
				else{
					$bin_tot=0;
				}
				$bin_wd=$this->getTotalWithdrawn($username,'cp_binary');
				$bin_bal=$bin_tot-$bin_wd['sum_net_withdrawn'];

				if(!empty($earning['sum_direct_amt'])){
					$direct_tot=$earning['sum_direct_amt'];
				}else { $direct_tot=0;}
				$direct_wid=$this->getTotalWithdrawn($username,'direct');
				$direct_bal=$direct_tot-$direct_wid['sum_net_withdrawn'];
				 
				 
				$returnArr['name'] =$username;
				$returnArr['ref_id'] =$row['ref_sponsor_id'];

				$page = file_get_contents('https://bitpay.com/api/rates');
				$my_array = json_decode($page, true);
				 
				$key = array_search('INR', array_column($my_array, 'code'));
					
				$amount = 1;
				$from_Currency = "BTC";
				$to_Currency = "INR";
				$get = file_get_contents("https://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency");
				$get = explode("<span class=bld>",$get);
				$get = explode("</span>",$get[1]);
				$converted_amount = preg_replace("/[^0-9\.]/", null, $get[0]);
					
				if(!empty($converted_amount)){
					$exchange_rate = $converted_amount;
				}else{
					$exchange_rate = $my_array[$key]["rate"];
				}
				 

				$returnArr['daily_tot']= number_format(($exchange_rate*$daily_tot), 2)." INR";
				$returnArr['daily_balance']= number_format(($exchange_rate*$daily_bal),2).' INR';

				$returnArr['bin_tot']=  number_format(($exchange_rate*$bin_tot),2).' INR';
				$returnArr['bin_balance']= number_format(($exchange_rate*$bin_bal),2).' INR';

				$returnArr['direct_tot']= number_format(($exchange_rate*$direct_tot),2).' INR';
				$returnArr['direct_balance']= number_format(($exchange_rate*$direct_bal),2).' INR';

				$query =mysqli_query($this->db,"SELECT SUM(ref_amt) as ref_amt, SUM(binary_amt) as binary_amt, SUM(daily_earning_amt) as daily_earning_amt FROM daily_ledger WHERE username='$username' AND status='1'");
				if(mysqli_num_rows($query) > 0){
					$row =mysqli_fetch_array($query,MYSQLI_ASSOC);
					$ref_amt=$row['ref_amt'];
					$binary_amt=$row['binary_amt'];
					$daily_earning_amt=$row['daily_earning_amt'];
				}
				$returnArr['curr_daily']= number_format(($exchange_rate*$daily_earning_amt), 2)." INR";
				$returnArr['curr_binary']= number_format(($exchange_rate*$binary_amt), 2)." INR";
				$returnArr['curr_direct']= number_format(($exchange_rate*$ref_amt), 2)." INR";

				$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $returnArr);
				$this->response($this->json($resultArray), 200);
				die;
			}
			else{
				$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'Login failed try again!');
				$this->response($this->json($resultArray), 200);
				die;
			}

		}
		if ($this->get_request_method() == "GET")
		{
			$returnArr=array();
			$username=$_GET['user'];
			$password=$_GET['password'];
			// echo $username;
			// echo $password;
				
			if( (!isset($_GET['user'])) || $username=='' || (!isset($_GET['password'])) || $password=='')
			{
				$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'Username or password wrong.!');
				$this->response($this->json($resultArray), 200);
				die;
			}
			$sql =mysqli_query($this->db,"SELECT * FROM user_info u WHERE u.binaryUser IS NOT NULL AND u.username='$username'");
			if(mysqli_num_rows($sql) > 0){
				$row = mysqli_fetch_array($sql,MYSQLI_ASSOC);
				$salt = $row["salt"];
				$pwd = $this->encryptPassword($password, $salt);
			}
			if ($pwd==$row["password"])
			{
				$sql_query=mysqli_query($this->db,"SELECT SUM(daily_earning_amt) as sum_net_amt, SUM(binary_amt) as sum_bin_amt,SUM(ref_amt) as sum_direct_amt FROM final_ledger WHERE username = '".cleanQueryParameter($username)."'");
				if(mysqli_num_rows($sql_query) > 0){
					$earning = mysqli_fetch_array($sql_query,MYSQLI_ASSOC);
				}
				if(!empty($earning['sum_net_amt']))
				{ $daily_tot=$earning['sum_net_amt'];}
				else{ $daily_tot=0;}
				$daily_wid=$this->getTotalWithdrawn($username,'daily_earn');
				$daily_bal=$daily_tot-$daily_wid['sum_net_withdrawn'];
				if(!empty($earning['sum_bin_amt']))
				{
					$bin_tot=$earning['sum_bin_amt'];
				}
				else{
					$bin_tot=0;
				}
				$bin_wd=$this->getTotalWithdrawn($username,'cp_binary');
				$bin_bal=$bin_tot-$bin_wd['sum_net_withdrawn'];

				if(!empty($earning['sum_direct_amt'])){
					$direct_tot=$earning['sum_direct_amt'];
				}else { $direct_tot=0;}
				$direct_wid=$this->getTotalWithdrawn($username,'direct');
				$direct_bal=$direct_tot-$direct_wid['sum_net_withdrawn'];
				 
				 
				$returnArr['name'] =$username;
				$returnArr['ref_id'] =$row['ref_sponsor_id'];

				$page = file_get_contents('https://bitpay.com/api/rates');
				$my_array = json_decode($page, true);
					
				$key = array_search('INR', array_column($my_array, 'code'));
					
				$amount = 1;
				$from_Currency = "BTC";
				$to_Currency = "INR";
				$get = file_get_contents("https://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency");
				$get = explode("<span class=bld>",$get);
				$get = explode("</span>",$get[1]);
				$converted_amount = preg_replace("/[^0-9\.]/", null, $get[0]);
					
				if(!empty($converted_amount)){
					$exchange_rate = $converted_amount;
				}else{
					$exchange_rate = $my_array[$key]["rate"];
				}
				 
				$returnArr['daily_tot']= number_format(($exchange_rate*$daily_tot), 2)." INR";
				$returnArr['daily_balance']= number_format(($exchange_rate*$daily_bal),2).' INR';

				$returnArr['bin_tot']=  number_format(($exchange_rate*$bin_tot),2).' INR';
				$returnArr['bin_balance']= number_format(($exchange_rate*$bin_bal),2).' INR';

				$returnArr['direct_tot']= number_format(($exchange_rate*$direct_tot),2).' INR';
				$returnArr['direct_balance']= number_format(($exchange_rate*$direct_bal),2).' INR';

				$query =mysqli_query($this->db,"SELECT SUM(ref_amt) as ref_amt, SUM(binary_amt) as binary_amt, SUM(daily_earning_amt) as daily_earning_amt FROM daily_ledger WHERE username='$username' AND status='1'");
				if(mysqli_num_rows($query) > 0){
					$row =mysqli_fetch_array($query,MYSQLI_ASSOC);
					$ref_amt=!empty($row['ref_amt'])?$row['ref_amt']:0;
					$binary_amt=!empty($row['binary_amt'])?$row['binary_amt']:0;
					$daily_earning_amt=!empty($row['daily_earning_amt'])?$row['daily_earning_amt']:0;
				}

				$returnArr['curr_daily']= number_format(($exchange_rate*$daily_earning_amt), 2)." INR";
				$returnArr['curr_binary']= number_format(($exchange_rate*$binary_amt), 2)." INR";
				$returnArr['curr_direct']= number_format(($exchange_rate*$ref_amt), 2)." INR";

				$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $returnArr);
				$this->response($this->json($resultArray), 200);
				die;
			}
			else{
				$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'Login failed try again!');
				$this->response($this->json($resultArray), 200);
				die;
			}

		}
	}

	private function about_bitcoin()
	{
		if ($this->get_request_method() == "POST")
		{
			$sql =mysqli_query($this->db,"SELECT * FROM cms where handler='about_bitcoin'");
			if(mysqli_num_rows($sql) > 0){
				$row = mysqli_fetch_array($sql,MYSQLI_ASSOC);

				$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $row['description']);
				$this->response($this->json($resultArray), 200);
				die;
			}
			else{
				$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'Login failed try again!');
				$this->response($this->json($resultArray), 406);
				die;
			}
		}
		if ($this->get_request_method() == "GET")
		{
			$sql =mysqli_query($this->db,"SELECT * FROM cms where handler='about_bitcoin'");
			if(mysqli_num_rows($sql) > 0){
				$row = mysqli_fetch_array($sql,MYSQLI_ASSOC);

				$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $row['description']);
				$this->response($this->json($resultArray), 200);
				die;
			}
			else{
				$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'Login failed try again!');
				$this->response($this->json($resultArray), 406);
				die;
			}
		}

	}

	private function about_gainbitco()
	{
		if ($this->get_request_method() == "POST")
		{
			$sql =mysqli_query($this->db,"SELECT * FROM cms where handler='about_gainbitco'");
			if(mysqli_num_rows($sql) > 0){
				$row = mysqli_fetch_array($sql,MYSQLI_ASSOC);

				$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $row['description']);
				$this->response($this->json($resultArray), 200);
				die;
			}
			else{
				$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'Login failed try again!');
				$this->response($this->json($resultArray), 406);
				die;
			}
		}
		if ($this->get_request_method() == "GET")
		{
			$sql =mysqli_query($this->db,"SELECT * FROM cms where handler='about_gainbitco'");
			if(mysqli_num_rows($sql) > 0){
				$row = mysqli_fetch_array($sql,MYSQLI_ASSOC);

				$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $row['description']);
				$this->response($this->json($resultArray), 200);
				die;
			}
			else{
				$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'Login failed try again!');
				$this->response($this->json($resultArray), 406);
				die;
			}
		}

	}

	private function earn_bitcoin()
	{
		if ($this->get_request_method() == "POST")
		{
			$sql =mysqli_query($this->db,"SELECT * FROM cms where handler='earn_bitcoin'");
			if(mysqli_num_rows($sql) > 0){
				$row = mysqli_fetch_array($sql,MYSQLI_ASSOC);

				$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $row['description']);
				$this->response($this->json($resultArray), 200);
				die;
			}
			else{
				$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'Login failed try again!');
				$this->response($this->json($resultArray), 406);
				die;
			}
		}
		if ($this->get_request_method() == "GET")
		{
			$sql =mysqli_query($this->db,"SELECT * FROM cms where handler='earn_bitcoin'");
			if(mysqli_num_rows($sql) > 0){
				$row = mysqli_fetch_array($sql,MYSQLI_ASSOC);

				$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $row['description']);
				$this->response($this->json($resultArray), 200);
				die;
			}
			else{
				$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'Login failed try again!');
				$this->response($this->json($resultArray), 406);
				die;
			}
		}

	}

	function encryptPassword($pwd, $salt)
	{
		$hashed_password = sha1($salt . $pwd);
		return $hashed_password;
	}

	function getTotalWithdrawn($username,$withdrawtype)
	{
		$query = mysqli_query($this->db,"SELECT SUM(btc_amt) as sum_net_withdrawn FROM withdrawals WHERE `status`<>2 AND withdrawal_type= '" .$withdrawtype. "' AND username = '".cleanQueryParameter($username) . "' ");
		if(mysqli_num_rows($query) > 0){
			$row = mysqli_fetch_array($query,MYSQLI_ASSOC);
		}
		return $row;
	}
	/* rss feed and news function */
	function InsertRss()
	{
		//$sql = mysqli_query($this->db,"truncate table rss_feed");
		$this->insetdata('http://feeds.feedburner.com/BitcoinMagazine','');
		$this->insetdata('http://feeds.feedburner.com/CoinDesk?format=xml','coin');
	}

	function insetdata($feed,$img)
	{
		global $rootURL;
		$img_url=$rootURL.'/res/images';
		$rss = simplexml_load_file($feed);

		$i=0;
		/*   if($img=='coin')
		 {
		 $path=$img_url.'/coin.png';
		 }
		 else{
		 $path='';
		 } */

		foreach ($rss->channel->item as $item){
			// var_dump($item);
			// echo "<br>".$i."<br>";
			if($i<5){

				$title = (string) $item->title; // Title
				$link   = (string) $item->link; // Url Link

				$namespaces = $item->getNameSpaces(true);
				// $content = $item->children($namespaces['content']);
				$content = $item->children($namespaces['content']);
				$html_string = $content->encoded;
					
				$dom = new DOMDocument();
				libxml_use_internal_errors(true);
				$dom->loadHTML($html_string);
				libxml_clear_errors();
					
				// var_dump($dom->getElementsByTagName('img')->item(0));
				if($dom->getElementsByTagName('img')->item(0)){
					// echo "here";
					$path = $dom->getElementsByTagName('img')->item(0)->getAttribute('src');
				}else if($img=='coin'){
					$path=$img_url.'/coin.png';
				}
				else{
					$path='';
				}
				// var_dump($dom);
				$sql = mysqli_query($this->db,"INSERT INTO rss_feed(title, link,img_path) VALUES('" .$title. "', '".$link."','".$path."') ");
			}
			// echo $path."<br><br>";
			// echo $i;
			$i++;
		}
	}

	private function rss()
	{
		if ($this->get_request_method() == "POST") {
			$sql = mysqli_query($this->db,"SELECT * from rss_feed where status=1 ORDER BY created_on DESC LIMIT 10");
			if(mysqli_num_rows($sql) > 0){
				while ($row = mysqli_fetch_array($sql,MYSQLI_ASSOC)) {
					$retArr[]=$row;
				}
			}
			if(!empty($retArr))
			{
				$j = 0;
				for ($i =0; $i <=count($retArr)-1; $i++) {

					$returnArr[$j]['title'] = $retArr[$i]['title'];
					$returnArr[$j]['link'] = $retArr[$i]['link'];
					$returnArr[$j]['img'] = $retArr[$i]['img_path'];
					$j++;
				}

				$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $returnArr);
				$this->response($this->json($resultArray), 200);
				die;
			}
			else{
				$resultArray = array('errCode' => '1', 'response' => 'failed');
				$this->response($this->json($resultArray), 406);
				die;
			}
		}
		elseif ($this->get_request_method() == "GET") {
			$sql = mysqli_query($this->db,"SELECT * from rss_feed where status=1 ORDER BY created_on DESC LIMIT 10");
			if(mysqli_num_rows($sql) > 0){
				while ($row = mysqli_fetch_array($sql,MYSQLI_ASSOC)) {
					$retArr[]=$row;
				}
			}
			if(!empty($retArr))
			{
				$j = 0;
				for ($i =0; $i <=count($retArr)-1; $i++) {

					$returnArr[$j]['title'] = $retArr[$i]['title'];
					$returnArr[$j]['link'] = $retArr[$i]['link'];
					$returnArr[$j]['img'] = $retArr[$i]['img_path'];
					$j++;
				}

				$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $returnArr);
				$this->response($this->json($resultArray), 200);
				die;
			}
			else{
				$resultArray = array('errCode' => '1', 'response' => 'failed');
				$this->response($this->json($resultArray), 406);
				die;
			}
		}
		else{
			$resultArray = array('errCode' => '1', 'response' => 'failed');
			$this->response($this->json($resultArray), 406);
			die;
		}
	}

	private function rss_old()
	{
		if ($this->get_request_method() == "POST") {
			$sql = mysqli_query($this->db,"SELECT * from rss_feed where status=1 ORDER BY created_on ASC");
			if(mysqli_num_rows($sql) > 0){
				while ($row = mysqli_fetch_array($sql,MYSQLI_ASSOC)) {
					$retArr[]=$row;
				}
			}
			if(!empty($retArr))
			{
				$j = 0;
				for ($i =0; $i <=count($retArr)-1; $i++) {

					$returnArr[$j]['title'] = $retArr[$i]['title'];
					$returnArr[$j]['link'] = $retArr[$i]['link'];
					$returnArr[$j]['img'] = $retArr[$i]['img_path'];
					$j++;
				}

				$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $returnArr);
				$this->response($this->json($resultArray), 200);
				die;
			}
			else{
				$resultArray = array('errCode' => '1', 'response' => 'failed');
				$this->response($this->json($resultArray), 406);
				die;
			}
		}
		elseif ($this->get_request_method() == "GET") {
			$sql = mysqli_query($this->db,"SELECT * from rss_feed where status=1 ORDER BY created_on ASC");
			if(mysqli_num_rows($sql) > 0){
				while ($row = mysqli_fetch_array($sql,MYSQLI_ASSOC)) {
					$retArr[]=$row;
				}
			}
			if(!empty($retArr))
			{
				$j = 0;
				for ($i =0; $i <=count($retArr)-1; $i++) {

					$returnArr[$j]['title'] = $retArr[$i]['title'];
					$returnArr[$j]['link'] = $retArr[$i]['link'];
					$returnArr[$j]['img'] = $retArr[$i]['img_path'];
					$j++;
				}

				$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $returnArr);
				$this->response($this->json($resultArray), 200);
				die;
			}
			else{
				$resultArray = array('errCode' => '1', 'response' => 'failed');
				$this->response($this->json($resultArray), 406);
				die;
			}
		}
		else{
			$resultArray = array('errCode' => '1', 'response' => 'failed');
			$this->response($this->json($resultArray), 406);
			die;
		}
	}

	private function GBcalculator()
	{
	
		$investment = trim($this->_request['investment']);
		if($investment=='')
		{
			$resultArray = array('errCode' => '1', 'response' => 'failed','message'=>'No Record Found');
			$this->response($this->json($resultArray), 200);
			die;
		}
		$query =mysqli_query($this->db,"SELECT value FROM featured_permissions where name like 'mcap_roi_percentage' ");
		
		if(mysqli_num_rows($query) > 0){
			$row = mysqli_fetch_array($query,MYSQLI_ASSOC);
			
		}
		
		$McapRoiPercentage = $row['value']?$row['value']:10;
		
		if($this->get_request_method() == "POST")
		{
		
			/*
			$query =mysqli_query($this->db,"SELECT payout_ghz FROM global_variables ORDER BY timestamp DESC LIMIT 1");
			if(mysqli_num_rows($query) > 0){
				$row = mysqli_fetch_array($query,MYSQLI_ASSOC);
				
			}
			
			// $tot_GB=number_format(($investment*400*$row['payout_ghz']),8).' BTC';
			$dailyEarning = number_format(($investment*400*$row['payout_ghz']),8);
			$monthlyEarning = number_format(($investment*400*30*$row['payout_ghz']),8);
			$yearlyEarning = number_format(($investment*400*365*$row['payout_ghz']),8);
			*/
			$dailyEarning = number_format(($investment*$McapRoiPercentage)/(100*30),8);
			$monthlyEarning = number_format(($investment*$McapRoiPercentage)/100,8);
			$yearlyEarning = number_format(($investment*$McapRoiPercentage*12)/100,8);
			
			$tot_GB['daily_earning'] = $dailyEarning;
			$tot_GB['monthly_earning'] = $monthlyEarning;
			$tot_GB['yearly_earning'] = $yearlyEarning;
			$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $tot_GB);
			$this->response($this->json($resultArray), 200);
			die;
		}
		if($this->get_request_method() == "GET") {
			/*
			$query =mysqli_query($this->db,"SELECT payout_ghz FROM global_variables ORDER BY timestamp DESC LIMIT 1");
			if(mysqli_num_rows($query) > 0){
				$row = mysqli_fetch_array($query,MYSQLI_ASSOC);
			}
			// $tot_GB=number_format(($investment*400*$row['payout_ghz']),8).' BTC';
			$dailyEarning = number_format(($investment*400*$row['payout_ghz']),8);
			$monthlyEarning = number_format(($investment*400*30*$row['payout_ghz']),8);
			$yearlyEarning = number_format(($investment*400*365*$row['payout_ghz']),8);
			*/
			$dailyEarning = number_format(($investment*$McapRoiPercentage)/(100*30),8);
			$monthlyEarning = number_format(($investment*$McapRoiPercentage)/100,8);
			$yearlyEarning = number_format(($investment*$McapRoiPercentage*12)/100,8);
			
			$tot_GB['daily_earning'] = $dailyEarning;
			$tot_GB['monthly_earning'] = $monthlyEarning;
			$tot_GB['yearly_earning'] = $yearlyEarning;

			$resultArray = array('errCode' => '-1', 'response' => 'success', 'client_data' => $tot_GB);
			$this->response($this->json($resultArray), 200);
			die;
		}
	}

	/* function get_client_ip() {
	 $ipaddress = '';
	 if (getenv('HTTP_CLIENT_IP'))
	 $ipaddress = getenv('HTTP_CLIENT_IP');
	 else if(getenv('HTTP_X_FORWARDED_FOR'))
	 $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	 else if(getenv('HTTP_X_FORWARDED'))
	 $ipaddress = getenv('HTTP_X_FORWARDED');
	 else if(getenv('HTTP_FORWARDED_FOR'))
	 $ipaddress = getenv('HTTP_FORWARDED_FOR');
	 else if(getenv('HTTP_FORWARDED'))
	 $ipaddress = getenv('HTTP_FORWARDED');
	 else if(getenv('REMOTE_ADDR'))
	 $ipaddress = getenv('REMOTE_ADDR');
	 else
	 $ipaddress = 'UNKNOWN';
	 return $ipaddress;
	 }
	 */


	private function send_notification(){
		session_start();
		$sql = mysqli_query($this->db,"select * from admin_notifications where status = 'active' and flag = '1'");
		/*  if($_REQUEST){
			// var_dump($_SERVER['REQUEST_METHOD']);
			$ip = $this->get_client_ip();
			$user = $_SESSION['user'];
			$method = $_SERVER['REQUEST_METHOD'];
			$data = json_encode($_REQUEST);
				
			// $last_query = mysql_info();
			// var_dump(mysql_info($this->db));
			// echo $user;
			$insertNotification = mysqli_query($this->db,"insert into requests(username,method,data,ip_address) values('$user','$method','$data','$ip')");
			} */

		if(mysqli_num_rows($sql) > 0){
			$row = mysqli_fetch_array($sql,MYSQLI_ASSOC);
			// var_dump($row);
			$this->response($this->json($row),200);
		}else{
			echo "Notification Not Found";
		}
	}


	private function acknowledgement($id = NULL){
		$id = $this->_request['id'];
		if(!empty($id)){
			$returnArr = array();
			$updated_on=date('Y-m-d h:i:s');
			$result = mysqli_query($this->db,"update admin_notifications set flag = '0', updated = '".$updated_on."' where id = '".$id."'");
				
			// var_dump($result);
			// exit;
			if (noError($result)) {
				$returnArr["errCode"][-1] = -1;
				$returnArr["errMsg"] = "updated";
			} else {
				$returnArr["errCode"][5] = 5;
				$returnArr["errMsg"] = "Not updated";
			}
			return $returnArr;
		}
	}
}

// Initiiate Library
$api = new API;
$api->processApi();
?>
