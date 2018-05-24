<?php
class FinledgcronController extends Zend_Controller_Action{

	public function init(){

	}

	public function indexAction(){
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
			try {
				$common_obj = new Gbc_Model_Custom_CommonFunc();
				$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
				$user = new Gbc_Model_DbTable_Userinfo();
				$daily_ledger = new Gbc_Model_DbTable_Dailyledger();
				$final_ledger = new Gbc_Model_DbTable_FinalLedger();
				$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
				$cronName = "finalLedgerCron";
				$start = date('Y-m-d H:i:s');
				$common_obj->updateCronStatus($cronName,$start,'');
				$date = date('Y-m-d');

				/*				Table Backup				*/


			if(!empty($curr_date) && $curr_date!=''){
			$DB->query("CREATE TABLE `final_ledger_2_$date` LIKE final_ledger_2"); 
			$DB->query("INSERT `final_ledger_2_$date` SELECT * FROM final_ledger_2");
		}else{
			/*
			$DB->query("CREATE TABLE `final_balance_$date` LIKE final_balance"); 
			$DB->query("INSERT `final_balance_$date` SELECT * FROM final_balance");
			
			$DB->query("CREATE TABLE `final_ledger_$date` LIKE final_ledger"); 
			$DB->query("INSERT `final_ledger_$date` SELECT * FROM final_ledger");
			
			$DB->query("CREATE TABLE `daily_ledger_$date(1)` LIKE daily_ledger"); 
			$DB->query("INSERT `daily_ledger_$date(1)` SELECT * FROM daily_ledger");
			*/
		}
		/*	

				/*				Table Backup				*/
echo "after backup " . date('Y-m-d h:i:s')."\n";	
				$refundDate = !empty($ref_date)?$ref_date:REFUND_DATE;
				$checkRefundDate = !empty($chk_ref_date)?$chk_ref_date:REFUND_DATE;
				$CurrentDate = !empty($curr_date)?"".$curr_date."":date('Y-m-d');


				$userCount = $user->fetchRow($user->select()
				->setIntegrityCheck(false)
				->from(array('u' =>'user_info'),array('count(username) as userCount'))
				->where("binaryUser IS NOT NULL")
				->where("created_on < ?",$date));

				$count = 0;
echo "after user count " . date('Y-m-d h:i:s')."\n";	
				while($count < (100)){
					$limit = 500;
					$offset = !empty($count)?$count:'0';
					// echo $offset;
					$Users = $common_obj-> getBinaryUsersForCron($limit,$offset);
					if (($key = array_search('admin', $Users)) !== false) {
						unset($Users[$key]);
					}
					if (($key = array_search('amitsabnetwork', $Users)) !== false) {
						unset($Users[$key]);
					}
					foreach($Users as $username){
						if(!empty($curr_date) && $curr_date){
							$month = date('m',strtotime($curr_date));
						}else{
							$month = date('m');
						}
						if($month>1){
							$LastMonth = $month-1;
						}else{
							$LastMonth = "12";
						}
						$date = date('d');
						if(!empty($curr_date) && $curr_date){
							$date = date('d',strtotime($curr_date));
						}else{
							$date = date('d');
						}
						if($date>1 && $date<=16){
							$startDate = '2';
							$lastDate = '16';
							$startDailyLedgerDate = date('Y')."-".$LastMonth."-".$startDate." 00:00:00";
							$lastDailyLedgerDate = date('Y')."-".$LastMonth."-".$lastDate." 23:59:59";
						}else{
							$startDate = '17';
							$lastDate = '1';
							$startDailyLedgerDate = date('Y')."-".$LastMonth."-".$startDate." 00:00:00";
							$lastDailyLedgerDate = date('Y')."-".$month."-1"." 23:59:59";
						}
						$result = $daily_ledger->fetchRow($daily_ledger->select()
						->setIntegrityCheck(false)
						->from(array('d' =>'daily_ledger'),array('round(SUM(daily_earning_amt),8) as daily_earning_amt','round(SUM(ref_amt),8) as ref_amt','round(SUM(binary_amt),8) as binary_amt','round(SUM(daily_earning_amt_new),8) as daily_earning_amt_new','round(SUM(total_amt),8) as total_amt'))
						->where("username='$username' AND status='1'"));

						if(!empty($result) && sizeof($result)>0)
						{
							$ref_amt=$result->ref_amt;
							$binary_amt=$result->binary_amt;
							$daily_earning_amt=$result->daily_earning_amt;
							$daily_earning_amt_new=$result->daily_earning_amt_new;
							$total_amt=$result->total_amt;
								
							if(!empty($total_amt) || !empty($ref_amt) || !empty($binary_amt) || !empty($daily_earning_amt)|| !empty($daily_earning_amt_new)){
								// $adminRef=  number_format($ref_amt*(10/100),8);
								$adminRef= 0;
								$ref_amt=number_format(($ref_amt-$adminRef),8,'.','');

								// $adminBinary=  number_format($binary_amt*(10/100),8);
								$adminBinary= 0;
								$binary_amt=number_format(($binary_amt-$adminBinary),8,'.','');

								// $adminDaily=  number_format($daily_earning_amt*(10/100),8);
								$adminDaily= 0;
								$daily_earning_amt=number_format(($daily_earning_amt-$adminDaily),8,'.','');

								$daily_earning_amt_new=number_format(($daily_earning_amt_new),8,'.','');

								$date = date('Y-m-d');
							 // $date = date('2016-09-01');

								$invoices_obj = new Gbc_Model_DbTable_Invoices();

								$res_chk =$invoices_obj->fetchRow($invoices_obj->select()
								->setIntegrityCheck(false)
								->from(array('A'=>"invoices"),array('count(invoice_id) as check_direct','round(SUM(contract_rate),8) as contracts'))
								->where("username=?",$username)
								->where("invoice_status=?",1)
								->where("created_on < ?",$date)
								);
								if(!empty($res_chk ) && sizeof($res_chk)>0)
								{
									$contractDetails['isBenfit']=$res_chk->contracts;
								}
								$total_amt=number_format(($daily_earning_amt),8,'.','');
								if(!empty($total_amt)){
									$insert_arr = array('username'=>$username,'ref_amt'=>$ref_amt,'binary_amt'=>$binary_amt,'daily_earning_amt'=>$daily_earning_amt,'daily_earning_amt_new'=>$daily_earning_amt_new,'total_amt'=>$total_amt,'adm_roi_payout'=>$adminDaily,'adm_bin_payout'=>$adminBinary,'adm_ref_payout'=>$adminRef);
									$insert_qry=$final_ledger->insert($insert_arr);

									$upd_arr=array('status'=>0);
									//$upd_qry=$daily_ledger->update($upd_arr,"username='$username' AND status='1'");
									$upd_qry=$daily_ledger->update($upd_arr,$DB->quoteInto("username=?",$username) . ' AND ' . $DB->quoteInto("status=?",'1'));
									
									

								}

							}
								
						}
						echo "after insert and update " . date('Y-m-d h:i:s')."\n";	
						$withdrawalDetails = array();
						$count++;
						$UpdateFinalLedger = $common_obj->UpdateFinalLedger($withdrawalDetails, $username);
					}

				}
				if($count >= $userCount->userCount - 1){
					echo "cron finished " . date('Y-m-d h:i:s')."\n";	
					$cronName = "finalLedgerCron";
					// updateCronStatus($cronName);
					$end = date('Y-m-d H:i:s');
					$common_obj->updateCronStatus($cronName,'',$end);exit;
				}
			}
			catch(Exception $e)
			{
				echo $e->getMessage();exit;
			}
		}
echo "finished";exit;
	}
}