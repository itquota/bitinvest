<?php
class DailyledgerafterrefundController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{

		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		if (PHP_SAPI === 'cli' && isset($argv[1])) {
			list($key, $val) = explode("=", $argv[1]);
			$key = $val;
		} else {
			$key = $_REQUEST['key'];
		}

		if (PHP_SAPI === 'cli' && isset($argv[2])) {
			list($key, $val) = explode("=", $argv[2]);
			$curr_date= $val;
		} else {
			if(isset($_REQUEST['CurrentDate']))
			{
				$curr_date = $_REQUEST['CurrentDate'];
			}

		}

		if (PHP_SAPI === 'cli' && isset($argv[3])) {
			list($key, $val) = explode("=", $argv[3]);
			$ref_date= $val;
		} else {
			if(isset($_REQUEST['refundDate']))
			{
				$ref_date = $_REQUEST['refundDate'];
			}

		}
		if(isset($key) && ($key == HASHKEY)){
			$cronName = "dailyLedgerCronAfterRefund";
			$start = date('Y-m-d H:i:s');
			$common_obj->updateCronStatus($cronName,$start,'');
			$user = new Gbc_Model_DbTable_Userinfo();
			$CurrentDate = !empty($curr_date)?"".$curr_date."":date('Y-m-d');
			$refundDate = !empty($ref_date)?$ref_date:$refundDate;

			// $Users = getBinaryUsers($conn);
			$date = date('Y-m-d');


			/*				Table Backup				*/
			$currentDate = date('d');
			//if($currentDate == "1" || $currentDate == "17"){
			// mysql_query("CREATE TABLE `earnings_2` LIKE earnings");
			//mysql_query("INSERT `earnings_2` SELECT * FROM earnings;");

			// mysql_query("CREATE TABLE `daily_ledger_2_$date` LIKE daily_ledger_2");
			// mysql_query("INSERT `daily_ledger_2_$date` SELECT * FROM daily_ledger_2;");
			//}
			/*				Table Backup				*/


			$month_end = new DateTime("last day of last month");
			$LastMonthDate = $month_end->format('Y-m-d');

			if(!empty($curr_date) && $curr_date!=''){
				$month = date('m',strtotime($curr_date));
				$LastMonth = $month;
			}else{
				$month = date('m');
				if($month>1){
					$LastMonth = $month-1;
				}else{
					$LastMonth = "12";
				}
			}


			$date = date('d');
			if(!empty($curr_date) && $curr_date!=''){
				$date = date('d',strtotime($curr_date));
			}else{
				$date = date('d');
			}
			$ref_date = $date-1;
			//echo $date;
			if($date ==1){
				$direct_ref_date = $LastMonthDate;
			}else if($ref_date < 10){
				$direct_ref_date = date('Y')."-".$LastMonth."-0".$ref_date;
			}else{
				$direct_ref_date = date('Y')."-".$LastMonth."-".$ref_date;

			}
			// echo $month;
			// echo "<br>";
			// echo $date;
			// echo "<br>";
			if($date>1 && $date<=16){
				$startDate = '2';
				$lastDate = '16';
				$startDailyLedgerDate = date('Y')."-".$LastMonth."-".$startDate." 00:00:00";
				$lastDailyLedgerDate = date('Y')."-".$LastMonth."-".$lastDate." 23:59:59";

			}else{
				$startDate = '17';
				$lastDate = '1';
				$startDailyLedgerDate = date('Y')."-".$LastMonth."-".$startDate." 00:00:00";
				$lastDailyLedgerDate = date('Y')."-".$month."-01"." 23:59:59";
			}
			$userCount = $user->fetchRow($user->select()
			->setIntegrityCheck(false)
			->from(array('u' =>'user_info'),array('count(username) as userCount'))
			->where("binaryUser IS NOT NULL and created_on < '$date'"));

			$count = 0;
			while($count < ($userCount->userCount - 1)){
				$limit = 500;
				$offset = !empty($count)?$count:'0';
				$Users = $common_obj->getBinaryUsersForCron($limit,$offset);
				if (($key = array_search('admin', $Users)) !== false) {
					unset($Users[$key]);
				}
				if (($key = array_search('amitsabnetwork', $Users)) !== false) {
					unset($Users[$key]);
				}

				// $Users = array("atulanand");
				// var_dump($Users);

				if(empty($Users) || $Users==''){
					$count = $userCount->userCount ;
				}

				foreach($Users as $username)
				{
					// echo $username;
					// $sql="SELECT count(invoice_id) as check_direct from invoices where contract_rate='1' AND username='".$username."' AND invoice_status=1";


					// $ActivationTime =  UserActivationTime($username);
					// $ActivationTime =  '2016-08-24';

					// $CurrentDate = date('Y-m-d');

					// $diff = floor((strtotime($CurrentDate)-strtotime($ActivationTime))/(3600*24));


					// $date = date('Y-m-d');

					$earnings2_obj = new Gbc_Model_DbTable_Earnings2();

					$query=$earnings2_obj->fetchRow($earnings2_obj->select()
					->setIntegrityCheck(false)
					->from(array('earnings_2'=>"earnings_2"),array('round(SUM(net_amt),8) as sum_net_amt','temp'))
					->where("username =?",$username)
					->where("timestamp like '%$CurrentDate%'")
					->where("temp =?",'0')
					);

					//$query = "SELECT round(SUM(net_amt),8) as sum_net_amt,temp FROM earnings_2 WHERE username='$username' and timestamp like '%$CurrentDate%'  and temp = 0";
					//$query = "SELECT round(SUM(net_amt),8) as sum_net_amt,temp FROM earnings WHERE username='$username' and timestamp like '%$CurrentDate%'  and temp = 0";

					/*	$result = runQuery($query, $conn);
					 if (noError($result)) {
						$row = mysql_fetch_assoc($result["dbResource"]);*/
					//if($row['temp'] == 0){
					if (isset($query) && sizeof($query)>0) {
						$daily_earning_amt=number_format($query->sum_net_amt,8)+0;
					}
					else
					{
						$daily_earning_amt = 0;
					}
					//}
					/*
					 if($row['temp'] == 1){
					 $temp_daily_earning_amt=number_format($row['sum_net_amt'],8)+0;
					 }
					 	

					 }*/
					$query2=$earnings2_obj->fetchRow($earnings2_obj->select()
					->setIntegrityCheck(false)
					->from(array('earnings_2'=>"earnings_2"),array('round(SUM(net_amt),8) as sum_net_amt','temp'))
					->where("username =?",$username)
					->where("timestamp like '%$CurrentDate%'")
					->where("temp =?",'1')
					);

					//	$query = "SELECT round(SUM(net_amt),8) as sum_net_amt FROM earnings_2 WHERE username='$username' and  timestamp like '%$CurrentDate%' and temp = 1 ";
					//$query = "SELECT round(SUM(net_amt),8) as sum_net_amt FROM earnings WHERE username='$username' and  timestamp like '%$CurrentDate%' and temp = 1 ";

					//$result = runQuery($query, $conn);
					if (isset($query2) && sizeof($query2)>0) {
						//$row = mysql_fetch_assoc($result["dbResource"]);
						$temp_daily_earning_amt=number_format($query2->sum_net_amt,8)+0;
					}
					else
					{
						$temp_daily_earning_amt = 0;
					}

					$binary_user_inc_obj = new Gbc_Model_DbTable_Binaryuserincome2();
					//$query1 = "SELECT ref_amt,less_ref_amt FROM daily_ledger_2 WHERE username='$username' and created_on like '%$CurrentDate%'";
					$query1=$binary_user_inc_obj->fetchRow($binary_user_inc_obj->select()
					->setIntegrityCheck(false)
					->from(array('binaryuserincome_2'=>"binaryuserincome_2"),array('round(SUM(amount),8) as direc_amt'))
					->where("username =?",$username)
					->where("created_on like '%$direct_ref_date%'")
					->where("status =?",'1')
					);

					//	$query1 = "SELECT round(SUM(amount),8) as direc_amt FROM binaryuserincome_2 WHERE ben_username='$username' AND status = '1' and created_on like '%$direct_ref_date%' ";
					//echo $query1;
					//exit;
					//$result1 = runQuery($query1, $conn);
					if (isset($query1) && sizeof($query1)>0) {
						//$row1 = mysql_fetch_assoc($result1["dbResource"]);
						$ref_amt=number_format($query1['direc_amt'],8)+0;
							
					}
					else
					{
						$ref_amt = 0;
					}

					$daily_ledger_obj = new Gbc_Model_DbTable_Dailyledger2();
					$query3=$daily_ledger_obj->fetchRow($daily_ledger_obj->select()
					->setIntegrityCheck(false)
					->from(array('daily_ledger_2'=>"daily_ledger_2"),array('less_ref_amt'))
					->where("username =?",$username)
					->where("created_on like '%$CurrentDate%'")
					);
					//$query3 = "SELECT less_ref_amt FROM daily_ledger_2 WHERE username='$username' and created_on like '%$CurrentDate%'";
					//echo $query1;
					//exit;
					//$result3 = runQuery($query3, $conn);

					if (isset($query3) && sizeof($query3)>0) {
						//$row3 = mysql_fetch_assoc($result3["dbResource"]);
						$less_ref_amt=number_format($query3->less_ref_amt,8)+0;
					}
					else
					{
						$less_ref_amt = 0;
					}

					$Binaryuserwelcome2 = new Gbc_Model_DbTable_Binaryuserwelcome2();
					$query4=$Binaryuserwelcome2->fetchRow($Binaryuserwelcome2->select()
					->setIntegrityCheck(false)
					->from(array('bin_usr_wkl_income_2'=>"bin_usr_wkl_income_2"),array('round(SUM(total_earning),8) as sum_binary_amt'))
					->where("parent_username =?",'')
					->where("username =?",$username)
					->where("created_on like '%$CurrentDate%'")
					);

		  	// $query2 = "SELECT round(SUM(total_earning),8) as sum_binary_amt FROM bin_usr_wkl_income WHERE  parent_username = '' AND username='$username' and created_on >= '$startDailyLedgerDate' and created_on <= '$lastDailyLedgerDate'";
		  	//$query2 = "SELECT round(SUM(total_earning),8) as sum_binary_amt FROM bin_usr_wkl_income_2 WHERE  parent_username = '' AND username='$username' and created_on like '%$CurrentDate%'";
		  	//$result2 = runQuery($query2, $conn);
					if (isset($query4) && sizeof($query4)>0) {
		  		//$row2 = mysql_fetch_assoc($result2["dbResource"]);
		  		$binary_amt=  number_format($query4->sum_binary_amt,8)+0;
		  	}
		  	else
		  	{
		  		$binary_amt = 0;
		  	}

		  	$total=number_format((($daily_earning_amt + $temp_daily_earning_amt + $ref_amt + $binary_amt) - $less_ref_amt),8)+0;

		  	// echo "daily_earning_amt ".$daily_earning_amt;
		  	// echo "ref_amt ".$ref_amt;
		  	// echo "binary_amt ".$binary_amt;

		   // if(isset($total) && (($daily_earning_amt != 0) || ($temp_daily_earning_amt != 0))){

		  	/* if($_REQUEST['CurrentDate']){
		  	 $insert="INSERT INTO daily_ledger(username,ref_amt,binary_amt,daily_earning_amt,daily_earning_amt_new,total_amt,created_on) VALUES('$username','$ref_amt','$binary_amt','$daily_earning_amt','$temp_daily_earning_amt','$total','".$CurrentDate." 00:00:00')";
		  	 }else{
		  	 $insert="INSERT INTO daily_ledger(username,ref_amt,binary_amt,daily_earning_amt,daily_earning_amt_new,total_amt) VALUES('$username','$ref_amt','$binary_amt','$daily_earning_amt','$temp_daily_earning_amt','$total')";
		  	 } */
		  	$upd_arr = array('ref_amt'=>$ref_amt,'less_ref_amt'=>$less_ref_amt,'binary_amt'=>$binary_amt,'daily_earning_amt'=>$daily_earning_amt,'daily_earning_amt_new'=>$temp_daily_earning_amt,'total_amt'=>$total,'updated_on'=>new Zend_Db_Expr('NOW()'));
		  	//$res = daily_ledger_2->update($upd_arr,"username = '$username' and created_on like '%$CurrentDate%'");
		 	$res = daily_ledger_2->update($upd_arr,$DB->quoteInto("username=?",$username) . ' AND ' . $DB->quoteInto("created_on like '%$CurrentDate%'"));
		  	
		  	//->where($db->quoteInto("sponsor_id=?",trim($_REQUEST["ref_sponser_id"])) . ' OR ' . $db->quoteInto("username=?",trim($_REQUEST["ref_sponser_id"]))));
		  	
		  	//	$update = "update daily_ledger_2 set ref_amt = '$ref_amt',less_ref_amt = '$less_ref_amt', binary_amt = '$binary_amt',daily_earning_amt = '$daily_earning_amt', daily_earning_amt_new = '$temp_daily_earning_amt', total_amt = '$total', updated_on = now() where username = '$username' and created_on like '%$CurrentDate%' ";
		  	// echo $insert;
		  	//runQuery($update, $conn);

		  	// if(!empty($ActivationTime) &&(($diff<=30) && ($ActivationTime > date('Y-m-d',strtotime($refundDate))))){


		  	// }
		  	$count++;

				}
					
			}
				
			if($count >= $userCount->userCount - 1){
				$cronName = "dailyLedgerCronAfterRefund";
				// updateCronStatus($cronName);
				$end = date('Y-m-d H:i:s');
				$common_obj->updateCronStatus($cronName,'',$end);
			}

		}
		echo "cron finished";exit;
	}

}