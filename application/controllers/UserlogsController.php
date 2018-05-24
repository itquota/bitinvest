<?php
class UserlogsController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		//echo "Here";
		$this->view->title="Gainbitcoin - User Logs";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('22',$user_id);
		
		if((!empty($data1->view) && ($data1->view==1)) || $authUserNamespace->user=='admin')
		{
		
		}
		else 
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}

		$userlogsObj= new Gbc_Model_DbTable_Userlogs();
		
		$Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
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
		try{
			$username=$Gbc_Model_Custom_func_obj->cleanQueryParameter(($_POST['username']));

			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$this->_helper->layout()->setLayout("Admindashbord");//dashboard
			$result=array();
			

			$result = $userlogsObj->fetchAll($userlogsObj->select()
			->where("username=?",$username)
			->order("created_on DESC"));

			
			$this->view->result =$result;

			$db->commit();

		}
		catch(Exception $e)
		{
			$db->rollBack();
			$e->getMessage();exit;
		}
			
			
			
			



	}
}
