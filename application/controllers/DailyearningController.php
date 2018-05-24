<?php
class DailyearningController extends Zend_Controller_Action{

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
			$username=$authUserNamespace->user;
			$this->_helper->layout()->setLayout("dashbord");//dashboard
			//if($this->_request->isPost())
			//{
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$url= BASE."/Dailyearningapi?username=".$username;
			$result=$common_obj->call_curl($url);
			$result=(array)json_decode($result,true);

			//$records_per_page = $this->_request->getParam('getPageValue',10);
			//$this->view->records_per_page = $records_per_page;

			$this->view->result=$result['data'];
			$this->view->title="Gainbitcoin - Daily Output";

		}

		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
		/* pagination code */

		//echo "<pre>";
		//print_r($result);exit;
		//$this->view->result=$result['data'];

		//$this->view->result=$result['data'];

		/*
		 $records_per_page = $this->_request->getParam('getPageValue',5);
		 $this->view->records_per_page = $records_per_page;


		 $page = $this->_request->getParam('page',1);
		 if($records_per_page=="")$records_per_page = 5;
		 //$records_count = sizeOf($result['data']);
		 $records_count = sizeOf($result['data']);

		 $paginator = Zend_Paginator::factory($result['data']);

		 $paginator->setItemCountPerPage($records_per_page);
		 $paginator->setCurrentPageNumber($page);
		 $this->view->pagination_config = array('total_items'=>$records_count,'items_per_page'=>$records_per_page,'style'=>'digg');
		 $this->view->results = $paginator;




		 $page_number  = $records_count / 1;

		 $page_number_last =  floor($page_number);
		 */
		/*$records_per_page = $this->_request->getParam('getPageValue',10);
		 $this->view->records_per_page = $records_per_page;

		 $page = $this->_request->getParam('page',1);
		  
		 if($records_per_page=="")$records_per_page = 10;
		 $record_count = sizeof($result['data']);
		 $paginator = Zend_Paginator::factory($result['data']);
		 $paginator->setItemCountPerPage($records_per_page);
		 $paginator->setCurrentPageNumber($page);
		 $this->view->pagination_config = array('total_items'=>$record_count,'items_per_page'=>$records_per_page,'style'=>'digg');
		 $this->view->result1 = $paginator;
		 $page_number  = $record_count / 1;
		 $page_number_last =  floor($page_number);*/







	}

}