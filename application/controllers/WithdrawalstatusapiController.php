<?php
class WithdrawalstatusapiController extends Zend_Controller_Action{

	public function init()
	{

	}
	public function indexAction()
	{
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		//$common_obj->cleanQueryParameter(($_REQUEST['username']));
		$withdrawal=new Gbc_Model_DbTable_Withdrawals();
		//$username=trim($_REQUEST['username']);
		//$username=$common_obj->cleanQueryParameter(($_REQUEST['username']));
		$username=$this->_request->getParam("username");
	
		$page_no='';

		$no_of_records='';
		//echo 	$no_of_records;exit;

		if(empty($username) || !isset($username) || $username=='' || $username==null )
		{
			$data=array('Success'=>'','Failure'=>'username should not be blank');
			echo json_encode($data);exit;
		}

		else
		{


			try {

				$db = Zend_Db_Table::getDefaultAdapter(); 
				$db->beginTransaction();
				$allWithdrawals= $withdrawal->fetchAll($withdrawal->select()
				->where("username= ?",$username)
				->where("btc_amt != ?",'0')
				->where("paymentTxId != ?",'NTP')
				);
				
				$allWithdrawals=$allWithdrawals->toArray();
				$db->commit();	
				$allWithdrawals=($allWithdrawals);
				$data=array('Success'=>'success','Failure'=>'','data'=>$allWithdrawals);
				echo json_encode($data);exit;

			}
			catch (Exception $e)
			{
				$db->rollBack();	
				echo $e->getMessage();
				$data=array('Success'=>'','Failure'=>$e->getMessage());
				echo json_encode($data);exit;
			}
		}

	}
	public function status()
	{

	}


}