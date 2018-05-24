<?php

class AddrefundreqController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");
	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Refund Requests";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$this->_helper->layout()->setLayout("admindashbord");//dashboard
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$ref_req_obj = new Gbc_Model_DbTable_Refrequest();
		$ip_address=$misc_obj->get_client_ip();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('44',$user_id);
		if(!empty($data1->add) && ($data1->add==1) || $authUserNamespace->user=='admin')
		{
				
		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}
		$antixss = new Gbc_Model_Custom_StringLimit();


		$this->view->result=$result;

	//	$this->view->contracttype=$contracttype;
		/*echo "<pre>";
		 print_r($contracttype);exit;
		 */

						if(!empty($_POST) && !empty($_POST['username']) && !empty($_POST['email']) && !empty($_POST['kit_number']) && !empty($_POST['phone']) && !empty($_POST['reason']) )
						{

							$_POST = ($_POST);
										$ref_data=array('username'=>$_POST['username'],'full_name'=>$_POST['full_name'],'email'=>$_POST['email'],'kit_number'=>$_POST['kit_number'],'phone'=>$_POST['phone'],'txid'=>'Manual request by admin','reason_to_refund'=>$_POST['reason'],'wallet_addr'=>'Manual request by admin','ip_address'=>$ip_address,'status'=>0);
										
										$ref_qry=$ref_req_obj->insert($ref_data);
										if(!empty($ref_qry))
										{
											$msg = "Request submitted successfully.";
											$authUserNamespace->msg=$msg;
											$this->_redirect("/Refrequest");
										}
										else{
											$msg = "Something Error";
											$authUserNamespace->msg=$msg;
										}
																		
									
								
						}



	}


	public function checkkitAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$kits_obj=new Gbc_Model_DbTable_Kits();
		$kit_number = $common_obj->cleanQueryParameter($_GET['kit_number']);
		

		/*$kits_data=$kits_obj->fetchRow($kits_obj->select()
		->where("1=1 and kit_number='".$kit_number."'"));*/
		$kits_data=$kits_obj->fetchRow($kits_obj->select()
		->where("1=1 and status in ('active','used') and kit_number=?",$kit_number));
	
		
		if(!empty($kits_data) && sizeof($kits_data)>0)
		{
			echo  "true";exit;
		}
		else
		{
			echo  "false";exit;
		}
		
	}
	
	
}



