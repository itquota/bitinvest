<?php
class ListController extends Zend_Controller_Action{

	public function init(){
	
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || $authUserNamespace->user_type!='special')$this->_redirect("/Login");
	
	}

	public function indexAction(){
		try
		{
			$this->_helper->layout()->setLayout("dashboard");
			$user_obj=new Gbc_Model_DbTable_Userinfo();

			$id_list=$user_obj->fetchAll($user_obj->select()
			->setIntegrityCheck(false)
			->from(array('user_info'))
			->where("id_image IS NOT NULL AND id_image!=''")
			);
				


			if(empty($id_list) || sizeof($id_list)<=0)
			{
				$id_list=array();
			}

			$this->view->result=$id_list;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
		$bigarr=array();
	}

	public function getcontentAction()
	{
		$username=trim($_POST['username']);

		$user_obj=new Gbc_Model_DbTable_Userinfo();


		$id_list=$user_obj->fetchRow($user_obj->select()
		->setIntegrityCheck(false)
		->from(array('user_info'))
		->where("username='$username'")
		);

		if(empty($id_list) || sizeof($id_list)<=0)
		{
			echo "failed";exit;
		}
		else 
		{
			$img= '<img  height="300" width="300" src="data:image/jpeg;base64,'.base64_encode( $id_list->id_image ).'"/>'; 
			echo $img;exit;
		}
		
			
	}

}