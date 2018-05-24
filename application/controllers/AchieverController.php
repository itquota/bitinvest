<?php
class AchieverController extends Zend_Controller_Action{

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
			if(!empty($authUserNamespace->user))
			{
				//$loggedIn==true;
			}
			else 
			{
				$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
				$this->_redirect("/Login");
			}
			
			$this->_helper->layout()->setLayout("dashbord");//dashboard
			//if($this->_request->isPost())
			//{
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			//$common_obj->cleanQueryParameter(($_POST['username']));
			$username=$authUserNamespace->user;
			$a=$common_obj->cleanQueryParameter(($_POST['a']));
			
			if(!empty($a) && isset($a) && $a!='')
			{	
				 $url= BASE."/Achieverapi?username=".$username."&a=".$a;
			}
			else
			{
				$url= BASE."/Achieverapi?username=".$username;
			}

			$result=$common_obj->call_curl($url);
			$result=(array)json_decode($result,true);
			//	print_r($result['data'][0]['username']);exit;
			$this->view->result1=$result;
			$this->view->title="Gainbitcoin - Achievers";
			//}

			//echo"<pre>";
			//print_r($result);exit;
		}

		catch(Exception $e)
		{
			echo $e->getMessage();
		}

		/*	$records_per_page = $this->_request->getParam('getPageValue',10);
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


		//$this->view->result=$result['data'];


	}
}