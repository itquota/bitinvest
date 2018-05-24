<?php
class ChangepermissionController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");
			
	}

	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Change Pemission";

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('29',$user_id);
		if((!empty($data1->view) && ($data1->view)==1) || $authUserNamespace->user=='admin')
		{

		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}

		$changepermissionObj = new  Gbc_Model_DbTable_SpecialPermission();

		try {

			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();

			$this->_helper->layout()->setLayout("admindashbord");//dashboard

			$result=array();

			$result=$changepermissionObj->fetchRow($changepermissionObj->select()
			->setIntegrityCheck(false)
			->from(array('special_permissions')));

	 	//$id=$result->id;
	 	$this->view->result=$result;

	 	//$result1=sizeof($result);

		}
		catch(Exception $e)
		{
			//$db->rollBack();
			echo $e->getMessage();exit;
		}


	}
	public function updatepermissionAction()
	{
		$changepermissionObj = new  Gbc_Model_DbTable_SpecialPermission();
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		$common_obj = new Gbc_Model_Custom_CommonFunc();


		//$db = Zend_Db_Table::getDefaultAdapter();
		//$db->beginTransaction();



		$result=$changepermissionObj->fetchRow($changepermissionObj->select()
		->setIntegrityCheck(false)
		->from(array('special_permissions')));
			
		$result1=sizeof($result);


		if($this->_request->isPost())
		{

	 	$antixss = new Gbc_Model_Custom_StringLimit();
	 	foreach($_POST as $key => $value)
	 	{

	 		if(isset($value) && $value != ""){
	 			$antixss->setEncoding($value, "UTF-8");
	 			if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

	 				$data=array('success'=>'','failure'=>'Invalid Input.');
	 				echo json_encode($data);exit;

	 			}

	 		}

	 	}


	 	$seminar = $common_obj->cleanQueryParameter($_POST['seminar_popup']);
	 	$offers =  $common_obj->cleanQueryParameter($_POST['offers_popup']);
	 	$quarterly_offers = $common_obj->cleanQueryParameter($_POST['quarterly_offers']);
	 	$admin_kits = $common_obj->cleanQueryParameter($_POST['admin_kits']);
	 /*	if(!empty($_POST['seminar_popup']) &&  $_POST['seminar_popup']!="")
	 	{
	 		$seminar = $_POST['seminar_popup'];
	 	}
	 	else{
	 		$data=array('success'=>'','failure'=>'Please enter Seminar Popup');
	 		echo json_encode($data);exit;
	 	}

	 	if(!empty($_POST['offers_popup']) && $_POST['offers_popup']!="")
	 	{
	 			
	 		$offers =  $_POST['offers_popup'];
	 			
	 	}
	 	else{
	 		$data=array('success'=>'','failure'=>'Please enter Show Offers');
	 		echo json_encode($data);exit;
	 	}

	 	if(!empty($_POST['quarterly_offers']) && $_POST['quarterly_offers']!="")
	 	{
	 		$quarterly_offers = $_POST['quarterly_offers'];
	 			
	 	}else{
	 		$data=array('success'=>'','failure'=>'Please enter Quarterly Offers');
	 		echo json_encode($data);exit;
	 	}

	 	if(!empty($_POST['admin_kits']) && $_POST['admin_kits']!="")
	 	{
	 		$admin_kits = $_POST['admin_kits'];
	 	}
	 	else{
	 		$data=array('success'=>'','failure'=>'Please enter  Admin Silver Kits');
	 		echo json_encode($data);exit;
	 			
	 	}*/

	 	$token=$common_obj->cleanQueryParameter($_POST['token']);

	// 	if($authUserNamespace->token==$token){
	 			
	 		if(isset($result1) && $result1>0)
	 		{
	 			$data=array("seminar"=>$seminar,"offers"=>$offers,"quarterly_offers"=>$quarterly_offers,"admin_kits"=>$admin_kits,"updated_on"=> new Zend_Db_Expr('NOW()'));
	 			//$changepermissionObj->update($data,"id=".$_POST['userid']);
	 			$changepermissionObj->update($data,$DB->quoteInto("id=?",$_POST['userid']));

	 		}
	 		else {
	 			$data=array("seminar"=>$seminar,"offers"=>$offers,"quarterly_offers"=>$quarterly_offers,"admin_kits"=>$admin_kits);
	 			$changepermissionObj->insert($data);

	 		}
	 		//$msg="<p style='color:green'>Updated successfully</p>";
	 		//$this->view->msg=$msg;
	 		$data=array('success'=>'Updated successfully','failure'=>'');
	 		echo json_encode($data);exit;

	 		/*$result=$changepermissionObj->fetchRow($changepermissionObj->select()
	 			->setIntegrityCheck(false)
	 			->from(array('special_permissions')));

	 			$id=$result->id;
	 			$this->view->result=$result;*/
	 		//$this->_redirect('/Changepermission');
	 /*	}
	 	else{
	 			
	 		$data=array('success'=>'','failure'=>'Invalid Request Found.');
	 		echo json_encode($data);exit;
	 		//$msg="<p style='color:red;'>Invalid Request Found</p>";
	 		//$this->view->msg=$msg;
	 			
	 			
	 	}
*/
		}
	 //	$db->commit();
			
			
			

	}


}

