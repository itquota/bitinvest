<?php
//echo "Here"; exit;
class CrmdashboardController extends Zend_Controller_Action{

	public function init(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}


	public function indexAction()
	{
		
		$this->view->title="Gainbitcoin - CRM Dashboard";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$user_id=$authUserNamespace->user_id;
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$data1=$misc_obj->GetAccessRightByUserId('43',$user_id);
		
		if((!empty($data1->view) && ($data1->view==1)) || $authUserNamespace->user=='admin')
		{
	//	if(!empty($authUserNamespace->user) &&  $authUserNamespace->user=='admin')
		//{
			$loggedIn==true;
		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Login");
		}


		$result=array();
			
			$this->_helper->layout()->setLayout("admindashbord");

            $common_obj = new Gbc_Model_Custom_CommonFunc();

		$this->view->result = $result;
		try{	

			
			$todayData = $common_obj->getTodayData();
			$this->view->todaydata=$todayData;
			
			
			$startdate=$common_obj->cleanQueryParameter(($_POST["startdate"]));
			$enddate=$common_obj->cleanQueryParameter(($_POST["enddate"]));
				
			if($this->_request->isPost() && ($startdate!='' || $enddate!=''))
			{

				$startdate = date('Y-m-d',strtotime($startdate));
				$enddate = date('Y-m-d',strtotime($enddate))." 23:59:59";

				$summaryData = $common_obj->getSummaryDataRange($startdate,$enddate);
				$this->view->summarydata=$summaryData;
				
				$overalldata = $common_obj->getDataRange($startdate,$enddate);
				$overalldata["start"]=$startdate;
				$overalldata["end"]=$enddate;
				$this->view->overalldata=$overalldata;	
				
				$replycountdata = $common_obj->getRepliedDataRange($startdate,$enddate);
				$this->view->replycount=$replycountdata;



			}
			else
			{

				$overalldata = $common_obj->getOverAllData($date);
				$this->view->overalldata=$overalldata;				
				
				$summaryData = $common_obj->getSummaryData();
				$this->view->summarydata=$summaryData;
				
				$replycountdata = $common_obj->getRepliedData();
				$this->view->replycount=$replycountdata;
				
			}
			
			

		}
		catch(Exception $e)
		{
			$this->view->msg=$e->getMessage();
		}


	}


}
