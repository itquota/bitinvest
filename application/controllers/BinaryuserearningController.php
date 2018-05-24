<?php
class BinaryuserearningController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");
	}
	public function indexAction()
	{
		try{
			
			$this->view->title="Gainbitcoin - Users";
			$this->_helper->layout()->setLayout("admindashbord");//dashboard
			$userObj = new Gbc_Model_DbTable_Userinfo();
			$misc_obj = new Gbc_Model_Custom_Miscellaneous;
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$daily_ledger_obj = new Gbc_Model_DbTable_Dailyledger();
			$final_ledger_obj = new  Gbc_Model_DbTable_FinalLedger();
			if($this->_request->getParam("username")){
			$user = $this->_request->getParam("username");
			//		$user = "virender1122";

			$totalDailyEarned = $common_obj->getSumOfDailyEarnings($user);
			
			$totalBinaryEarned = $common_obj->getSumOfBinaryEarnings($user);

			$totalDirectEarned = $common_obj->getSumOfDirectEarnings($user);
			
			$totalEarnings = $common_obj->gettotalEarnings($user);

			$ledgerDetails =  $daily_ledger_obj->fetchAll($daily_ledger_obj->select()
								->setIntegrityCheck(false)
								->from(array('l'=>"daily_ledger"),array('round(SUM(ref_amt),8) as ref_amt','round(SUM(binary_amt),8) as binary_amt','round(SUM(daily_earning_amt),8) as daily_earning_amt','round(SUM(daily_earning_amt_new),8) as daily_earning_amt_new','round(SUM(total_amt),8) as total_amt'))
								->where("username = ?",$user)
								->where("status= ?",1 )									
								);
			$this->view->ledgerDetails = $ledgerDetails;

			$adminPercentage = $final_ledger_obj->fetchAll($final_ledger_obj->select()
								->setIntegrityCheck(false)
								->from(array('l'=>"final_ledger"),array('SUM(adm_roi_payout) as admin_daily','SUM(adm_bin_payout) as admin_binary','SUM(adm_ref_payout) as admin_direct'))
								->where("username = ?",$user)
								->where("status= ?",1 )									
								);
//			$this->view->adminPercentage = $adminPercentage;
//			echo "---".$totalDailyEarned["sum_net_amt"];
//			echo "<pre>";
//			print_r($totalEarnings);exit;
//			echo "---".$adminPercentage[0]['admin_daily'];exit;
			$totalDailyEarned = $totalDailyEarned["sum_net_amt"] + $adminPercentage[0]['admin_daily'];
			$totalDailyBalance = $totalDailyEarned - $adminPercentage[0]['admin_daily'];
			$this->view->totalDailyBalance = $totalDailyBalance;
			
			$totalBinaryEarned = $totalBinaryEarned["sum_bin_amt"] + $adminPercentage[0]['admin_binary'];
			$totalBinaryBalance = $totalBinaryEarned - $adminPercentage[0]['admin_binary'];
			$this->view->totalBinaryBalance = $totalBinaryBalance;
			
			$totalDirectEarned = $totalDirectEarned["sum_direct_amt"] + $adminPercentage[0]['admin_direct'];
			$totalDirectBalance = $totalDirectEarned - $adminPercentage[0]['admin_direct'];
			$this->view->totalDirectBalance = $totalDirectBalance;			
			
			$total_admin_fees = $totalEarnings["total_admin_fees"];
			$totalAmount = $totalEarnings["total_amt"] - $total_admin_fees;
			$this->view->totalAmount = $totalAmount;
			$total_withdrawal = $totalEarnings["total_withdrawal"];
			$this->view->total_withdrawal = $total_withdrawal;
			$bal_amt = $totalEarnings["bal_amt"];
			$this->view->bal_amt = $bal_amt;
			}else{
					$this->_redirect("/Binaryuser");
			}
			
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}

}