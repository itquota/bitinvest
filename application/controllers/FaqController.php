<?php
class FaqController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		try
		{
			//echo "fsdfdfsdf";exit;
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$this->_helper->layout()->setLayout("dashbord");
			$this->view->title="Gainbitcoin - FAQs";
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
	}

}