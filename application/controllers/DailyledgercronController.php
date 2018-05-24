<?php
class DailyledgercronController extends Zend_Controller_Action{

	public function init(){

	}

	public function indexAction()
	{
			
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$this->getResponse()->setHeader('Content-Type', 'application/json');
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		try
		{
print_r($common_obj); exit;
			$cronName = "dailyLedgerCron";
			$start = date('Y-m-d H:i:s');
			$common_obj->updateCronStatus($cronName,$start,'');
			$user = new Gbc_Model_DbTable_Userinfo();
			$Invoices=new Gbc_Model_DbTable_Invoices();
			$earning_obj=new Gbc_Model_DbTable_Earnings();
			$Binaryuserwelcome=new Gbc_Model_DbTable_Binaryuserwelcome();
			$Binaryuserincome=new Gbc_Model_DbTable_Binaryuserincome();
			$dailyledger_obj=new Gbc_Model_DbTable_Dailyledger();
			$dailyledgertemp_obj=new Gbc_Model_DbTable_Dailyledgertemp();
			$CurrentDate = !empty($_POST['CurrentDate'])?"".$_POST['CurrentDate']."":date('Y-m-d');
			$refundDate = !empty($_GET['refundDate'])?$_GET['refundDate']:REFUND_DATE;
			$date = date('Y-m-d');
			$currentDate = date('d');
			if($currentDate == "1" || $currentDate == "17"){

			}
			$userCount = $user->fetchRow($user->select()
			->setIntegrityCheck(false)
			->from(array('u'=>'user_info'),array('count(u.username) as countuser'))
			->where("binaryUser is NOT NULL  AND created_on <'".$date."'"));


			
			$userCounttotal=$userCount->countuser;
	
			$count = 0;
			while($count < $userCounttotal - 1){
				
				$limit = 500;
				$offset = !empty($count)?$count:'0';
				$Users =$common_obj->getBinaryUsersForCron($limit,$offset,'',$CurrentDate);
				if (($key = array_search('admin', $Users)) !== false) {
					unset($Users[$key]);
				}
				if (($key = array_search('amitsabnetwork', $Users)) !== false) {
					unset($Users[$key]);
				}
				foreach($Users as $username){
				
					$InvoicesCount = $Invoices->fetchRow($Invoices->select()
					->setIntegrityCheck(false)
					->from(array('u'=>'invoices'),array('count(invoice_id) as check_direct'))
					->where("username = ?",$username)
					->where("invoice_status = ?",'1')
					->where("created_on < '$CurrentDate'"));
					if(!empty($InvoicesCount) && sizeof($InvoicesCount)>0)
					{
						$contractDetails['isBenfit']=$InvoicesCount['check_direct'];
					}
					$daillyearningamt = $earning_obj->fetchRow($earning_obj->select()
					->setIntegrityCheck(false)
					->from(array('u'=>'earnings'),array('round(SUM(net_amt),8) as sum_net_amt'))
					->where("username = ?",$username)
					->where("isDaily = ?",'No')
					->where("temp = ?",'0')
					);

					if(!empty($daillyearningamt) && sizeof($daillyearningamt)>0)
					{
						$daily_earning_amt=number_format($daillyearningamt->sum_net_amt,8)+0;
					}
					$daillyearningtempamt = $earning_obj->fetchRow($earning_obj->select()
					->setIntegrityCheck(false)
					->from(array('u'=>'earnings'),array('round(SUM(net_amt),8) as sum_net_amt'))
					->where("username = ?",$username)
					->where("isDaily = ?",'No')
					->where("temp = ?",'1')
					);
					if(!empty($daillyearningtempamt) && sizeof($daillyearningtempamt)>0)
					{
						$temp_daily_earning_amt=number_format($daillyearningtempamt->sum_net_amt,8)+0;
					}

					$Binaryuserincomeamt = $Binaryuserincome->fetchRow($Binaryuserincome->select()
					->setIntegrityCheck(false)
					->from(array('u'=>'binaryuserincome'),array('round(SUM(amount),8) as direc_amt'))
					->where("ben_username = ?",$username)
					->where("isDaily = ?",'No')
					->where("status = ?",'1')
					->where("created_on < '$CurrentDate'")
					);
					if(!empty($Binaryuserincomeamt) && sizeof($Binaryuserincomeamt)>0)
					{
						$ref_amt=number_format($Binaryuserincomeamt->direc_amt,8)+0;
					}


					$Binaryuserwelcomeamt = $Binaryuserwelcome->fetchRow($Binaryuserwelcome->select()
					->setIntegrityCheck(false)
					->from(array('u'=>'bin_usr_wkl_income'),array('round(SUM(total_earning),8) as sum_binary_amt'))
					->where("username = ?",$username)
					->where("isDaily = ?",'No')
					);
					if(!empty($Binaryuserwelcomeamt) && sizeof($Binaryuserwelcomeamt)>0)
					{
						$binary_amt=number_format($Binaryuserwelcomeamt->sum_binary_amt,8)+0;
					}
					$total=number_format(($daily_earning_amt + $temp_daily_earning_amt + $ref_amt + $binary_amt),8)+0;
					if(isset($total) && ($total != 0)){

						if(!empty($_POST['CurrentDate']) && $_POST['CurrentDate']){
							
							$insertledger=array("username"=>$username,"ref_amt"=>$ref_amt,"binary_amt"=>$binary_amt,"daily_earning_amt"=>$daily_earning_amt,"daily_earning_amt_new"=>$temp_daily_earning_amt,"total_amt"=>$total,"created_on"=>$CurrentDate);
								  $insertearingtemp=$dailyledger_obj->insert($insertledger);

						}else{
							$insertledger=array("username"=>$username,"ref_amt"=>$ref_amt,"binary_amt"=>$binary_amt,"daily_earning_amt"=>$daily_earning_amt,"daily_earning_amt_new"=>$temp_daily_earning_amt,"total_amt"=>$total);

							    $insertearingtemp=$dailyledger_obj->insert($insertledger);


						}

						if(!empty($temp_daily_earning_amt)){
							if($_POST['CurrentDate']){
								$insertledgertemp=array("usernamesa"=>$username,"ref_amt"=>$ref_amt,"binary_amt"=>$binary_amt,"daily_earning_amt"=>$daily_earning_amt,"daily_earning_amt_new"=>$temp_daily_earning_amt,"total_amt"=>$total,"created_on"=>$CurrentDate);
								$insertearingtemp=$dailyledgertemp_obj->insert($insertledgertemp);
								$db->commit();

							}else{
								$insertledgertemp=array("usernamesa"=>$username,"ref_amt"=>$ref_amt,"binary_amt"=>$binary_amt,"daily_earning_amt"=>$daily_earning_amt,"daily_earning_amt_new"=>$temp_daily_earning_amt,"total_amt"=>$total);
								$insertearingtemp=$dailyledgertemp_obj->insert($insertledgertemp);
								$db->commit();

							}

							$earning=array("isDaily"=>"Yes");
							$updateearning=$earning_obj->update($earning,'username='.$username.' AND isDaily=No');


							$binaryuser=array("isDaily"=>"Yes");
							$updatebinaryuser=$Binaryuserincome->update($binaryuser,'ben_username='.$username.'  AND isDaily=No and created_on < '.$CurrentDate);


							$binaryuserwel=array("isDaily"=>"Yes");
							$updatebinaryuserwelcome=$Binaryuserwelcome->update($binaryuserwel,'parent_username="" AND isDaily=No');

						}

					}
					$count++;
				}
			}
			if($count >= $userCount['userCount'] - 1){
				$cronName = "dailyLedgerCron";
				// updateCronStatus($cronName);
				$end = date('Y-m-d H:i:s');
				$common_obj->updateCronStatus($cronName,'',$end);
			}
		}
		catch(Exception $e)
		{

		}

	}

}
