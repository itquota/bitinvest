<?php
class DirectupdateaftrefundController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		echo "started " . date('Y-m-d h:i:s')."\n";
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
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$ref_req_obj = new Gbc_Model_DbTable_Refrequest();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			$cronName = "directUpdateAfterRefund";
			$start = date('Y-m-d H:i:s');
			$common_obj->updateCronStatus($cronName,$start,'');
			$CurrentDate = !empty($curr_date)?"".$curr_date."":date('Y-m-d');
			$refundDate = !empty($ref_date)?$ref_date:$refundDate;

			// $Users = getBinaryUsers($conn);
			$date = date('Y-m-d');


			/*				Table Backup				*/
			$currentDate = date('d');
			// if($currentDate == "1" || $currentDate == "17"){

			$DB->query("CREATE TABLE `binaryuserincome_2` LIKE binaryuserincome");
			$DB->query("INSERT `binaryuserincome_2` SELECT * FROM binaryuserincome;");

			$DB->query("CREATE TABLE `bin_usr_wkl_income_2` LIKE bin_usr_wkl_income");
			$DB->query("INSERT `bin_usr_wkl_income_2` SELECT * FROM bin_usr_wkl_income;");

			$DB->query("CREATE TABLE `daily_ledger_2` LIKE daily_ledger");
			$DB->query("INSERT `daily_ledger_2` SELECT * FROM daily_ledger;");


			$DB->query("CREATE TABLE `earnings_2` LIKE earnings");
			$DB->query("INSERT `earnings_2` SELECT * FROM earnings;");


			// }
			/*				Table Backup				*/



			//$refund_requestsResult = mysql_query("select * from refund_requests where status = 1 and cron_status = 0");
			//$refund_requestsResult = mysql_query("select * from refund_requests where status = 1");

			$refund_requestsResult = $ref_req_obj->fetchAll($ref_req_obj->select()
			->where("status =?",'1')
			->where("cron_status = ?",'0'));
			$Counts = sizeof($refund_requestsResult);
			$count = 0;
			$userArray = array();
			if(!empty($refund_requestsResult) && sizeof($refund_requestsResult)>0)
			{
				$refund_requestsResult = $refund_requestsResult->toArray();
			}
			for($i=0;$i<sizeof($refund_requestsResult);$i++){
				$invoice_id = $refund_requestsResult[$i]['invoice_id'];
				$kit_number = $refund_requestsResult[$i]['kit_number'];
				$id = $refund_requestsResult[$i]['id'];

				$upd_arr = array('cron_status'=>'1','cron_date'=>new Zend_Db_Expr('NOW()'));
				$upd_qry = $ref_req_obj->update($upd_arr,"id = '$id'");

				echo $id;
				echo "<br>";

				$kits_obj=new Gbc_Model_DbTable_Kits();
				$earnings2_obj = new Gbc_Model_DbTable_Earnings2();
				$binary_user_inc_obj = new Gbc_Model_DbTable_Binaryuserincome2();


				$invoice_idResult=$kits_obj->fetchRow($kits_obj->select()
				->setIntegrityCheck(false)
				->from(array('kits'=>"kits"),array('k.kit_number'))
				->joinInner(array('invoices'=>'invoices'),'(invoices.use_kit_number = '$kit_number')',array('invoices.invoice_id','invoices.username','invoices.contract_rate'))
				->where("kits.kit_number =?",$kit_number)
				);


				//$invoice_idQuery = mysql_query("select invoices.invoice_id,invoices.username,invoices.contract_rate from kits left join invoices on invoices.use_kit_number = '$kit_number' where kits.kit_number = '$kit_number'");
				//$invoice_idResult = mysql_fetch_assoc($invoice_idQuery);

				$invoice_id = $invoice_idResult->invoice_id;
				$username = $invoice_idResult->username;
				$ContractRate = $invoice_idResult->contract_rate;
				if(!empty($invoice_id) && $invoice_id!=''){
					$upd_arr = array('total_amt'=>0,'net_amt'=>0,'updated_on'=>new Zend_Db_Expr('NOW()'));

					$result = $earnings2_obj->update($upd_arr,"username = '$username' and invoice_id = '$invoice_id'");
					/*$binaryuserincomequery = "update earnings_2 set total_amt = 0,net_amt =0, updated_on = now() where username = '$username' and invoice_id = '$invoice_id'";
					 $result = runQuery($binaryuserincomequery,$conn);
					 echo $binaryuserincomequery;*/
				}

				$bin_data = array('status'=>'0','updated_on'=>new Zend_Db_Expr('NOW()'));
				$bin_qry = $binary_user_inc_obj->update($bin_data,"invoice_id = '$invoice_id'");
				//$binaryuserincomequery = "update binaryuserincome_2 set status = 0, updated_on = now() where invoice_id = '$invoice_id'";
				//$result = runQuery($binaryuserincomequery,$conn);

				$directEarningUserQuery=$binary_user_inc_obj->fetchRow($binary_user_inc_obj->select()
				->setIntegrityCheck(false)
				->from(array('binaryuserincome_2'=>"binaryuserincome_2"),array('ben_username'))
				->where("invoice_id =?", $invoice_id)
				->where("status =?", '0')
				);
				if(isset($directEarningUserQuery) && sizeof($directEarningUserQuery)>0)
				{
					$ben_username = $directEarningUserResult->ben_username;
				}

				/*$directEarningUserQuery = mysql_query("select ben_username from binaryuserincome_2 where invoice_id = '$invoice_id' and status = 0 ");
				 $directEarningUserResult = mysql_fetch_assoc($directEarningUserQuery);

				 $ben_username = $directEarningUserResult['ben_username'];*/



				if(!in_array($username,$userArray)){
					$userArray[] = $username;
					$daily_ledger_obj = new Gbc_Model_DbTable_Dailyledger2();
					$daily_ledger_data = array('less_ref_amt'=>'0');
					$daily_ledger_qry = $daily_ledger_obj->update($daily_ledger_data,"username = '$ben_username' and created_on like '%$CurrentDate%'");
					//$updateDailyLedgerQuery = "update daily_ledger_2 set less_ref_amt = 0 where username = '$ben_username' and created_on like '%$CurrentDate%' ";
				}

				if ($ContractRate == 5){
					$tol_amount = 0.2;
				}else if ($ContractRate == 15){
					$tol_amount = 0.75;
				}else if ($ContractRate == 25){
					$tol_amount = 1.25;
				}else if ($ContractRate == 50){
					$tol_amount = 2.5;
				}else if ($ContractRate == 75){
					$tol_amount = 3.5;
				}else if ($ContractRate == 100){
					$tol_amount = 5;
				}else {
					$tol_amount=number_format(($ContractRate*5)/100,6);
				}

				//$upd_daily_data = array('less_ref_amt'=>round((less_ref_amt + $tol_amount),8),'total_amt'=>round(((daily_earning_amt + daily_earning_amt_new + ref_amt + binary_amt) - less_ref_amt),8),''=>,''=>,''=>,''=>,''=>);

				$updateDailyLedgerQuery = "update daily_ledger_2 set less_ref_amt = round((less_ref_amt + $tol_amount),8), total_amt = round(((daily_earning_amt + daily_earning_amt_new + ref_amt + binary_amt) - less_ref_amt),8), updated_on=now() where username = '$ben_username' and created_on like '%$CurrentDate%' ";
				$updateDailyLedgerResult=$DB->query($updateDailyLedgerQuery);

				// echo $binaryuserincomequery;
				// echo "<br>";

				// echo "select ben_username from binaryuserincome_2 where invoice_id = '$invoice_id' and status = 0 ";
				// echo "<br>";
				// echo $updateDailyLedgerQuery;
				// echo "<br>";
				//$updateDailyLedgerResult = runQuery($updateDailyLedgerQuery,$conn);
				$invoices_obj = new Gbc_Model_DbTable_Invoices();
				$activeInvoice=$invoices_obj->fetchRow($invoices_obj->select()
				->where("username =?", $username)
				->where("invoice_status =?", '1')
				);

				//$activeInvoice = mysql_query("select * from invoices where username = '$username' and invoice_status = 1");
				//$activeinvoiceCount = mysql_num_rows($activeInvoice);
				if(isset($activeInvoice) && sizeof($activeinvoiceCount) > 0){
					$updateDailyLedgerNewEarningsQuery = "update daily_ledger_2 set daily_earning_amt_new = (select round(sum(total_amt),8) from earnings_2 where username like '$username' and temp = '1' and date(timestamp) = date(daily_ledger_2.created_on)),total_amt = round((ref_amt+binary_amt+daily_earning_amt+daily_earning_amt_new-less_ref_amt),8), updated_on = now() where username like '$username' and created_on >= '2016-10-02 00:00:00'";
				}else{
					$updateDailyLedgerNewEarningsQuery = "update daily_ledger_2 set daily_earning_amt_new = (select round(sum(total_amt),8) from earnings_2 where username like '$username' and temp = '1' and date(timestamp) = date(daily_ledger_2.created_on)),binary_amt =0,total_amt =0,updated_on = now() where username like '$username' and created_on >= '2016-10-02 00:00:00'";
				}

				$result = $DB->query($updateDailyLedgerNewEarningsQuery);



				$count++;
			}
			// echo $count;
			if($count >= $Counts - 1){
				$cronName = "directUpdateAfterRefund";
				// updateCronStatus($cronName);
				$end = date('Y-m-d H:i:s');
				$common_obj->updateCronStatus($cronName,'',$end);
			}
		}

		echo "cron finished";exit;
	}

}