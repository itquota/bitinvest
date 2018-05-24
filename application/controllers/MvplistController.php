<?php
class MvplistController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		//if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$this->_helper->layout()->setLayout("dashbord");
		$this->view->title="Gainbitcoin - MVP List";
		
		$returnArr = array();
		
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		$eventsCountQuery = "select distinct event_id,place,duration from mvpusers order by event_id DESC";
		$eventsCountData=$DB->query($eventsCountQuery);
        $eventsCountResult = $eventsCountData->fetchAll();
		$this->view->events=$eventsCountResult;		
		
	}

}