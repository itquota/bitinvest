<?php
class ReportsController extends Zend_Controller_Action{

	public function init()
	{
		
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('24',$user_id);
		if((!empty($data1->view) && ($data1->view==1)) || $authUserNamespace->user=='admin')
		{

		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}

		$reportsObj = new Gbc_Model_DbTable_FinalBalance();
			
		try{

		 $this->_helper->layout()->setLayout("admindashbord");

					
			if($this->_request->isXmlHttpRequest()){
				
				//$_POST = cleanQueryParameter($_POST);
				//$_SESSION['report'] = $_POST;
				$authUserNamespace->report=$_POST;
				/*echo "<pre>";
				print_r($_POST);exit;*/
				$data=$common_obj->getAllUserReports($_POST);
				
					
							
			}else if(!empty($_POST)){
		
				$authUserNamespace->report = $_POST;
				
				// echo urldecode($_GET);
				if(!empty($_POST) && !empty($authUserNamespace->report) && ($_POST['Export'] == "reports")){
					
					$filename = "reports.csv"; // File Name
					
					$path = FILE_UPLOAD_PATH."/res/files/reports/";
				
					$PaginateLimit="";
					$get_parameters = $authUserNamespace->report;
					// print_r($authUserNamespace->report);exit;
					$data =$common_obj->getAllUserReports($get_parameters,$filename,$path);
				}else{
					$_GET = ($_GET);
					$pairs = explode('&',$_GET['search']);
					 foreach($pairs as $pair) {
						$part = explode('=', $pair);
						$get_parameters[$part[0]] = $part[1];
					}
					// var_dump($get_parameters);
					$authUserNamespace->report = $get_parameters;
					$data = $common_obj->getAllUserReports($get_parameters,$PaginateLimit);
				
				}
				// echo $data;
				// exit;
			}
			else {
				
				$result=$reportsObj->fetchAll($reportsObj->select()
								  ->setIntegrityCheck(false)
								  ->from(array('final_balance'))
								 );
								 
				$this->view->result=$result;
				
			}
		
			

		}

		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}


	}
}