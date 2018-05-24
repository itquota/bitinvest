<?php
class ContractdetailsController extends Zend_Controller_Action{

	public function init()
	{

	}
	public function indexAction()
	{
		try {
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			//$common_obj->cleanQueryParameter(($_POST['username']));

			
			//$username=$_POST['username'];
			$username=$common_obj->cleanQueryParameter(($_REQUEST['username']));
			$invoices_obj = new Gbc_Model_DbTable_Invoices();
			$final_balance_obj = new Gbc_Model_DbTable_Userinfo();
			$bin_net_det_obj = new Gbc_Model_DbTable_Binarynetworkdetail();
			/*$result = $invoices_obj->fetchRow($invoices_obj->select()
			->setIntegrityCheck(false)
			->from(array('i' =>'invoices'),array('round(sum(contract_rate),2) as total_contract'))
			->where("contract_type in('SHA','hardware') AND username='".$username."'")
			);*/
			
			$result = $invoices_obj->fetchRow($invoices_obj->select()
			->setIntegrityCheck(false)
			->from(array('i' =>'invoices'),array('round(sum(contract_rate),5) as total_contract'))
			->where("contract_type in('SHA','hardware','MS','ES')")
			->where("invoice_status=?",'1')								  					  
			->where("username=?",$username)
			
			);
		
			$cont= "";
			if(isset($result) && sizeof($result)>0)
			{
				$cont = $result->total_contract;
			}
			else
			{
				$cont=0;
			}
			/*$totalEarnings= $final_balance_obj->fetchRow($final_balance_obj->select()
			->setIntegrityCheck(false)
			->from(array('f' =>'final_balance'),array('total_amt', 'total_admin_fees', 'total_withdrawal','bal_amt'))
			->where("username='".$username."'")
			);*/
			$totalEarnings= $final_balance_obj->fetchRow($final_balance_obj->select()
			->setIntegrityCheck(false)
			->from(array('f' =>'final_balance'),array('total_amt', 'total_admin_fees', 'total_withdrawal','bal_amt'))
			->where("username= ?",$username)
			);
			
			
			
			$total_admin_fees='';
			$totalAmount='';
			if(isset($totalEarnings) & sizeof($totalEarnings)>0)
			{
				$total_admin_fees = $totalEarnings->total_admin_fees;
				$totalAmount = ($totalEarnings->total_amt) - $total_admin_fees;
				$totalAmount=number_format($totalAmount,8);
			}

			/*$all_user= $bin_net_det_obj->fetchRow($bin_net_det_obj->select()
			->setIntegrityCheck(false)
			->from(array('b' =>'binary_network_details'),array('left_contracts','right_contracts','left_users','right_users','left_active_users','right_active_users',
		'left_inactive_users', 'right_inactive_users'))
			->where("username='".$username."' and status = 0 ")
			);*/

			$all_user= $bin_net_det_obj->fetchRow($bin_net_det_obj->select()
			->setIntegrityCheck(false)
			->from(array('b' =>'binary_network_details'),array('left_contracts','right_contracts','left_users','right_users','left_active_users','right_active_users',
		'left_inactive_users', 'right_inactive_users'))
			->where("username = ?",$username)
			->where("status= ?",0)
			);
			
			
			$db->commit();

			$data_arr=array('myContracts'=>$cont,'totalOutputs'=>$totalAmount." BTC",'left'=>number_format($all_user->left_contracts,2)." BTC",'right'=>number_format($all_user->right_contracts,2)." BTC",'totalLeftUsers'=>$all_user->left_users,'leftActiveUsers'=>$all_user->left_active_users,'leftInactiveUsers'=>$all_user->left_inactive_users,'totalRightUsers'=>$all_user->right_users,'rightActiveUsers'=>$all_user->right_active_users,'rightInactiveUsers'=>$all_user->right_inactive_users);
		 $data=array('success'=>'success','failure'=>'','data'=>$data_arr);
		 echo json_encode($data);exit;
		}
		catch(Exception $e)
		{
			$db->rollBack();
			echo $e->getMessage();exit;
		}
	}
}