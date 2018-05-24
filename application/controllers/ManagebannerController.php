<?php
class ManagebannerController extends Zend_Controller_Action{
	
	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!empty($authUserNamespace->user) &&  $authUserNamespace->user=='admin')
			{
				//$loggedIn==true;
			}
			else 
			{
				$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
				$this->_redirect("/Login");
			}
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Manage Banner";
		$this->_helper->layout()->setLayout("admindashbord");
		$manage_banner_obj= new Gbc_Model_DbTable_Managebanner();
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
		$result=$manage_banner_obj->fetchAll($manage_banner_obj->select()
			->setIntegrityCheck(false)
			->from(array('manage_banner'),array('id','banner_image','description','status'))
			
			);
			
		if(empty($result) || sizeof($result)<=0)
		{
			$result=array();
		}
		$this->view->resultmanagebanner=$result;

	}
	
	
	
	
	
}
