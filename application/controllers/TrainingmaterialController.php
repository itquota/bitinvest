<?php
class TrainingmaterialController extends Zend_Controller_Action{

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
			$this->_helper->layout()->setLayout("dashbord");
			$this->view->title="Gainbitcoin - Video Guides";
			$video_guides_obj= new Gbc_Model_DbTable_VideoGuides();	
	
			$result=$video_guides_obj->fetchAll($video_guides_obj->select()
				->setIntegrityCheck(false)
				->from(array('video_guides'))
				->order('order_id ASC'));
			
			if(empty($result) || sizeof($result)<=0)
			{
				$result=array();
			}
			//echo "<pre>";
			//print_r($result); exit;
			$this->view->result=$result;				

		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}		
		
	}

}