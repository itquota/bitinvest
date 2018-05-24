<?php
class WithdrawalapiController extends Zend_Controller_Action{

	public function init()
	{
		//Zend_Loader::loadClass('nonexistantclass');
	}
	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$final_ledger=new Gbc_Model_DbTable_FinalLedger();
		$daily_ledger = new Gbc_Model_DbTable_Dailyledger();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		//$common_obj->cleanQueryParameter(($_REQUEST['username']));
		
		$user = new Gbc_Model_DbTable_Userinfo();
		$fund_transfers = new Gbc_Model_DbTable_FundTransfers();
		$user_withdrawal = new Gbc_Model_DbTable_UserWithdrawalTypes();
		//$username=$common_obj->cleanQueryParameter(($_REQUEST['username']));
		$username=$this->_request->getParam("username");
		
	 if($username != ''){

		 $username=$username;
		
		}else{
			$arr=array('Success'=>' ','Failure'=>'Username cannot be blank');
			echo json_encode($arr);
			exit;
		}

		try {
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
		/*	$ledgerDetails=$daily_ledger->fetchRow($daily_ledger->select()
			->setIntegrityCheck(false)
			->from(array('dl' =>'daily_ledger'),array('round(SUM(dl.ref_amt),8) as ref_amt','round(SUM(dl.binary_amt),8) as binary_amt','round(SUM(dl.daily_earning_amt),8) as daily_earning_amt','round(SUM(dl.total_amt),8) as total_amt'))
			->where("username='$username' AND status='1'"));*/
			$ledgerDetails=$daily_ledger->fetchRow($daily_ledger->select()
			->setIntegrityCheck(false)
			->from(array('dl' =>'daily_ledger'),array('round(SUM(dl.ref_amt),8) as ref_amt','round(SUM(dl.binary_amt),8) as binary_amt','round(SUM(dl.daily_earning_amt),8) as daily_earning_amt','round(SUM(dl.daily_earning_amt_new),8) as daily_earning_amt_new','round(SUM(dl.daily_earning_amt_new1),8) as daily_earning_amt_new1','round(SUM(dl.total_amt),8) as total_amt'))
			->where("username=?",$username)
			->where("status=?",'1'));
			
			
			//}
			/*catch (Zend_Exception $e)
			 {
			 $data=array('Success'=>'','Failure'=>$e->getMessage());
			 echo json_encode($data);exit;
			 }*/
			$ledgerDetails=$ledgerDetails->toArray();

		/*	$adminPercentage=$final_ledger->fetchRow($final_ledger->select()
			->setIntegrityCheck(false)
			->from(array('fl' =>'final_ledger'),array('SUM(fl.adm_roi_payout) as admin_daily', 'SUM(fl.adm_bin_payout) as admin_binary', 'SUM(fl.adm_ref_payout) as admin_direct'))
			->where("username='$username' AND status='1'"));*/
			
	$adminPercentage=$final_ledger->fetchRow($final_ledger->select()
			->setIntegrityCheck(false)
			->from(array('fl' =>'final_ledger'),array('SUM(fl.adm_roi_payout) as admin_daily', 'SUM(fl.adm_bin_payout) as admin_binary', 'SUM(fl.adm_ref_payout) as admin_direct'))
			->where("username=?",$username)
			->where("status=?",'1'));
			
			$AvailableKits=	$common_obj->AvailableSilverKits('');

			$totalDailyEarned=$common_obj->getSumOfDailyEarnings($username);
			//$totalDailyEarned=$totalDailyEarned->toArray();
			//	print_r($totalDailyEarned);exit;

			$totalDlyEarned=($totalDailyEarned->sum_net_amt) + ($totalDailyEarned->daily_earning_amt_new) + ($totalDailyEarned->daily_earning_amt_new1) + ($adminPercentage->admin_daily);
			$totalDailyBalance = ($totalDlyEarned) - ($adminPercentage->admin_daily);


			$totalBinaryEarned = $common_obj->getSumOfBinaryEarnings($username);
			//$totalBinaryEarned=$totalBinaryEarned->toArray();
			$totalBnryEarned = ($totalBinaryEarned->sum_bin_amt) + ($adminPercentage->admin_binary);
			$totalBinaryBalance = $totalBnryEarned - $adminPercentage->admin_binary;



			$totalDirectEarned = $common_obj->getSumOfDirectEarnings($username);
			//$totalDirectEarned=$totalDirectEarned->toArray();
			$totalDrctEarned=($totalDirectEarned->sum_direct_amt) + ($adminPercentage->admin_direct);
			$totalDirectBalance = $totalDrctEarned - ($adminPercentage->admin_direct);


			$totalEarnings = $common_obj->getTotalEarnings($username);
			//$totalEarnings=$totalEarnings->toArray();
			$total_admin_fees = $totalEarnings->total_admin_fees;
			$totalAmount = ($totalEarnings->total_amt) - $total_admin_fees;
			$total_withdrawal = $totalEarnings->total_withdrawal;
			$bal_amt = $totalEarnings->bal_amt;
			$new_token_amt = $totalEarnings->new_token_amt;
			

			$walletaddress=$user->fetchRow($user->select()
			->setIntegrityCheck(false)
			->from(array('u' =>'user_info'),array('u.wallet_addr'))
			->where("username=?",$username));
			

			$fund_trans=$fund_transfers->fetchRow($fund_transfers->select()
			->setIntegrityCheck(false)
			->from(array('f' =>'fund_transfers'),array('f.user_to'))
			->where("f.user_from=?",$username)
			->order("f.id desc")
			->Limit(1));
			
			/*$ExistingWithdrawalType=$fund_transfers->fetchRow($fund_transfers->select()
			 ->setIntegrityCheck(false)
			 ->from(array('f' =>'fund_transfers'),array('f.user_to'))
			 ->where("f.user_from='$username'")
			 ->order("f.id desc")
			 ->Limit(1));*/

			$db->commit();
			$data=array('Success'=>'success','Failure'=>'','totalDailyEarned'=>$totalDlyEarned,'totalDailyBalance'=>$totalDailyBalance,'totalBinaryEarned'=>$totalBnryEarned,'totalBinaryBalance'=>$totalBinaryBalance,'totalDirectEarned'=>$totalDrctEarned,'totalDirectBalance'=>$totalDirectBalance,'totalEarnings'=>$totalEarnings,'totalAmount'=>$totalAmount,'total_withdrawal'=>$total_withdrawal,'bal_amt'=>$bal_amt, 'new_token_amt' => $new_token_amt, 'ledgerDetails'=>$ledgerDetails,'walletaddress'=>$walletaddress->wallet_addr);
			echo  json_encode($data);	exit;
		}
		catch (Exception $e)
		{
			$db->rollBack();
			$data=array('Success'=>'','Failure'=>$e->getMessage());
			echo json_encode($data);exit;
		}



	}

}