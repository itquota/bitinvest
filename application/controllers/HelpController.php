<?php
class HelpController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		try
		{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

			if($this->_request->isXmlHttpRequest())
			{
				if(empty($_POST['fullName']) || empty($_POST['email']) || empty($_POST['mob'])|| empty($_POST['subject'])|| empty($_POST['message'])){
						
					echo 'false';
					exit();
				}
				else
				{
					$username=$authUserNamespace->user;
					$help_obj = new Gbc_Model_DbTable_Helpquery();
					$common_obj = new Gbc_Model_Custom_CommonFunc();
					$result=array();
					$url= BASE."/UserinfoApi?username=".$username;
					$result=$common_obj->call_curl($url);
					$userInfo=(array)json_decode($result,true);
						
					$ticket_id=$common_obj->get_rand_alphanumeric(5).$common_obj->get_rand_numbers(4);
					$insert_arr=array('name'=>$_POST['fullName'],'email'=>$_POST['email'],'mob'=>$_POST['mob'],'subject'=>$_POST['subject'],'query'=>$_POST['message'],'ticket_id'=>$ticket_id,'username'=>$_POST['username']);
					$insert_data=$help_obj->insert($insert_arr);
					echo $ticket_id;die;
				}
			}
			else
			{
				$username=$authUserNamespace->user;
					
				$common_obj = new Gbc_Model_Custom_CommonFunc();
				$result=array();
				$url= BASE."/UserinfoApi?username=".$username;
				$result=$common_obj->call_curl($url);
				$userInfo=(array)json_decode($result,true);
				$this->view->result=$userInfo['data'];
					

				$this->_helper->layout()->setLayout("dashbord");
					
			}
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}

}