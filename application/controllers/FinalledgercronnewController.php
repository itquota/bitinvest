<?php
class FinalledgercronnewController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
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

		if (PHP_SAPI === 'cli' && isset($argv[4])) {
			list($key, $val) = explode("=", $argv[4]);
			$ref_date= $val;
		} else {
			if(isset($_REQUEST['checkRefundDate']))
			{
				$chk_ref_date = $_REQUEST['checkRefundDate'];
			}

		}
		if(isset($key) && ($key == HASHKEY)){
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$user = new Gbc_Model_DbTable_Userinfo();
			$cronName = "finalLedgerCron_new";
			$start = date('Y-m-d H:i:s');
			$common_obj->updateCronStatus($cronName,$start,'');
			$date = date('Y-m-d');
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			/*				Table Backup				*/


			$DB->query("CREATE TABLE `final_balance_2` LIKE final_balance");
			$DB->query("INSERT `final_balance_2` SELECT * FROM final_balance");

			$DB->query("CREATE TABLE `final_ledger_2` LIKE final_ledger");
			$DB->query("INSERT `final_ledger_2` SELECT * FROM final_ledger");


			/*				Table Backup				*/

			if(!empty($curr_date) && $curr_date!=''){
				$date = date('d',strtotime($_REQUEST['CurrentDate']));
				$month = date('m',strtotime($_REQUEST['CurrentDate']));
				$year = date('Y',strtotime($_REQUEST['CurrentDate']));

				//$LastMonth = $month;

				if($date==16){

					$LastMonth = $month;
					$startDate = '02';
					$lastDate = '16';
					$startDailyLedgerDate = $year."-".$LastMonth."-".$startDate." 00:00:00";
					$lastDailyLedgerDate = $year."-".$LastMonth."-".$lastDate." 23:59:59";


				}else{
					if($month>1){
						$LastMonth = $month-1;
						if($LastMonth <10){
							$LastMonth = "0".$LastMonth;
						}
					}else{
						$LastMonth = "12";
					}

					//$month_end = new DateTime("last day of last month");
					// $LastYear = $month_end->format('Y');

					$prev_month_ts = strtotime($_REQUEST['CurrentDate'].' -1 month');
					$LastYear = date('Y', $prev_month_ts);

					$startDate = '17';
					$lastDate = '01';
					$startDailyLedgerDate = $LastYear."-".$LastMonth."-".$startDate." 00:00:00";
					$lastDailyLedgerDate = $year."-".$month."-".$lastDate." 23:59:59";
				}

			}


			$refundDate = !empty($_REQUEST['refundDate'])?$_REQUEST['refundDate']:$refundDate;
			$checkRefundDate = !empty($_REQUEST['checkRefundDate'])?$_REQUEST['checkRefundDate']:$checkRefundDate;
			$CurrentDate = !empty($_REQUEST['CurrentDate'])?"".$_REQUEST['CurrentDate']."":date('Y-m-d');

			$userCount = $user->fetchRow($user->select()
			->setIntegrityCheck(false)
			->from(array('u' =>'user_info'),array('count(username) as userCount'))
			->where("binaryUser"));

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
				if(empty($Users)){
					$count = $userCount->userCount ;
				}
				foreach($Users as $username){
					// echo "username ".$username;
					//	echo "<br> ";

					//$ActivationTime =  UserActivationTime($username);
					// $ActivationTime =  '2016-08-24';


					//$diff = floor((strtotime($CurrentDate)-strtotime($ActivationTime))/(3600*24));

					// if(!empty($ActivationTime) && ((($diff>30) && ($ActivationTime > $refundDate)) || ($ActivationTime < $refundDate))){

				 // $date = date('2016-09-02');

				 // echo $_GET['CurrentDate'];
					/* if(($CurrentDate < $checkRefundDate) && ($ActivationTime < $refundDate)){
						$query = "SELECT round(SUM(daily_earning_amt),8) as daily_earning_amt FROM daily_ledger WHERE username='$username' AND status='1'";
						}else{ */

					// echo $month;
					// echo "<br>";
					// echo $date;
					// echo "<br>";





					// if(($diff>30) && ($ActivationTime > $refundDate)){
					$daily_ledger_obj = new Gbc_Model_DbTable_Dailyledger2();
					$result=$daily_ledger_obj->fetchRow($daily_ledger_obj->select()
					->setIntegrityCheck(false)
					->from(array('daily_ledger_2'=>"daily_ledger_2"),array('round(SUM(ref_amt),8) as ref_amt','round(SUM(less_ref_amt),8) as less_ref_amt', 'round(SUM(binary_amt),8) as binary_amt', 'round(SUM(daily_earning_amt),8) as daily_earning_amt', 'round(SUM(daily_earning_amt_new),8) as daily_earning_amt_new', 'round(SUM(total_amt),8) as total_amt'))
					->where("username =?",$username)
					->where("created_on >= '$startDailyLedgerDate'")
					->where("created_on <= '$lastDailyLedgerDate'")
					);
					//$query = "SELECT round(SUM(ref_amt),8) as ref_amt,round(SUM(less_ref_amt),8) as less_ref_amt, round(SUM(binary_amt),8) as binary_amt, round(SUM(daily_earning_amt),8) as daily_earning_amt, round(SUM(daily_earning_amt_new),8) as daily_earning_amt_new, round(SUM(total_amt),8) as total_amt FROM daily_ledger_2 WHERE username='$username'  and created_on >= '$startDailyLedgerDate' and  created_on <= '$lastDailyLedgerDate'";
					//echo $query;
					//echo "<br>";
					//exit;
					// }
					// }


					//$result = runQuery($query, $conn);

					if (isset($result) && sizeof($result)>0) {
							

					 $ref_amt=$result->ref_amt;
					 $less_ref_amt=$result->less_ref_amt;
					 $binary_amt=$result->binary_amt;
					 $daily_earning_amt=$result->daily_earning_amt;
					 $daily_earning_amt_new=$result->daily_earning_amt_new;
					 $total_amt=$$result->total_amt;
					 //	if(!empty($total_amt) || !empty($ref_amt) || !empty($binary_amt) || !empty($daily_earning_amt) || !empty($daily_earning_amt_new)){
					 	
						// $adminRef=  number_format($ref_amt*(10/100),8);
					 $adminRef= 0;
						$ref_amt=number_format(($ref_amt-$adminRef-$less_ref_amt),8,'.','');

						// $adminBinary=  number_format($binary_amt*(10/100),8);
						$adminBinary= 0;
						$binary_amt=number_format(($binary_amt-$adminBinary),8,'.','');

						// $adminDaily=  number_format($daily_earning_amt*(10/100),8);
						$adminDaily= 0;
						$daily_earning_amt=number_format(($daily_earning_amt-$adminDaily),8,'.','');
						$daily_earning_amt_new=number_format(($daily_earning_amt_new-$adminDaily),8,'.','');

						// $date = date('Y-m-d');
						// $date = date('2016-09-01');

						// $sql="SELECT count(invoice_id) as check_direct from invoices where contract_rate='1' AND username='".$username."' AND invoice_status=1";

						$Invoices_obj=new Gbc_Model_DbTable_Invoices();
						$res_chk = $Invoices_obj->fetchRow($Invoices_obj->select()
						->from(array('invoices'=>"invoices"),array('count(invoice_id) as check_direct', 'round(SUM(contract_rate),8) as contracts'))
						->where("username = ?",$username)
						->where("invoice_status = ?",'1')
						->where("created_on < '$CurrentDate'")
						);

						//$sql="SELECT count(invoice_id) as check_direct, round(SUM(contract_rate),8) as contracts from invoices where username='".$username."' AND invoice_status=1 and created_on < '$CurrentDate' ";
						//$res_chk = runQuery($sql, $conn);
						if (isset($res_chk) && sizeof($res_chk)>0) {
							//$rw = mysql_fetch_assoc($res_chk["dbResource"]);
							$contractDetails['isBenfit']=$res_chk->contracts;
						}
						/*
						 if($contractDetails['isBenfit']>=0.5){
						 $total_amt=number_format(($daily_earning_amt + $daily_earning_amt_new + $ref_amt + $binary_amt),8,'.','');
						 }else{*/

						$total_amt=number_format(($daily_earning_amt),8,'.','');
							
							
						//}


						/* if(($diff>30) && ($ActivationTime > $refundDate)){
						 $total_amt=number_format(($daily_earning_amt + $ref_amt + $binary_amt),8,'.','');
							} */
							
						//if(!empty($total_amt)){
						// $insert="INSERT INTO final_ledger(username,ref_amt,binary_amt,daily_earning_amt,total_amt,adm_roi_payout,adm_bin_payout,adm_ref_payout) VALUES('$username','$ref_amt','$binary_amt','$daily_earning_amt','$total_amt','$adminDaily','$adminBinary','$adminRef')";
						// runQuery($insert, $conn);
						if(!empty($_REQUEST['CurrentDate']) && $_REQUEST['CurrentDate']!=''){

							$update1= "UPDATE final_ledger_2 SET ref_amt = '$ref_amt',binary_amt = '$binary_amt',daily_earning_amt = '$daily_earning_amt',daily_earning_amt_new = '$daily_earning_amt_new', total_amt = round((ref_amt + binary_amt + daily_earning_amt+daily_earning_amt_new),8), updated_on = now() where username = '".$username."' and created_on like '%$CurrentDate%'";
							//$update1= "UPDATE final_ledger_2 SET ref_amt = '$ref_amt',binary_amt = '$binary_amt',daily_earning_amt = '$daily_earning_amt',daily_earning_amt_new = '$daily_earning_amt_new', total_amt = round(($total_amt),8), updated_on = now() where username = '".$username."' and created_on like '%$CurrentDate%'";
						}else{
							$update1= "UPDATE final_ledger_2 SET ref_amt = '$ref_amt',binary_amt = '$binary_amt',daily_earning_amt = '$daily_earning_amt',daily_earning_amt_new = '$daily_earning_amt_new', total_amt = round(($total_amt),8), updated_on = now() where username = '".$username."' and created_on like '%$CronDate%'";
						}
						// echo  $update1;
						result = $DB->query($update1);
						//exit;
						// $update1= "UPDATE daily_ledger SET status='0' WHERE username='$username' AND status='1' and created_on >= '$startDailyLedgerDate' and  created_on <= '$lastDailyLedgerDate'";
						// runQuery($update1, $conn);
						//}
						// $UpdateFinalLedger = UpdateFinalLedger($withdrawalDetails, $username,$conn);
						//}
					}
					$count++;
					//ob_flush();
					//flush();
					// }
					$UpdateFinalLedger = UpdateFinalLedger_2($withdrawalDetails, $username,$conn);
				}

			}


		}// end of key

	}

}