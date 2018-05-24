
<?php
class ViewreportController extends Zend_Controller_Action{

	public function init()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Reports";
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$user_id=$authUserNamespace->user_id;
		$antixss = new Gbc_Model_Custom_StringLimit();
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

			$PaginateLimit=500;
		 $this->_helper->layout()->setLayout("admindashbord");
		 if(!empty($_POST)){

		 	foreach($_POST as $key => $value)
		 	{
		 		//if($key!='user'){
		 		if(isset($value) && $value != ""){

		 			if($antixss->setFilter($value, "black", "string") == "invalidInput"){
		 					
		 				//$data=array('success'=>'','failure'=>'Invalid Input');
		 				//echo json_encode($data);exit;
		 				$msg="Invalid Input";
		 				$authUserNamespace->msg=$msg;
		 					
		 			}
		 				
		 		}
		 		//}

		 	}


		 	$_POST = $common_obj->cleanQueryParameter($_POST);
		 	$authUserNamespace->report = $_POST;
		 	$token=$_POST['token'];

		// 	if($authUserNamespace->token==$token){
		 		$data = $common_obj->getAllUserReports($_POST,$PaginateLimit);
		 /*	}
		 	else {
		 		$msg="Invalid Request found";
		 		$authUserNamespace->msg=$msg;
		 	} */
		 	// echo $data;
		 	// exit;
		 }else if(!empty($_GET)){
		   	foreach($_GET as $key => $value)
		 	{
		 			
		 		//if($key!='user'){
					if(isset($value) && $value != ""){
						$antixss->setEncoding($value, "UTF-8");
						if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

							//$data=array('success'=>'','failure'=>'Invalid Input.');
							//echo json_encode($data);exit;
							//$msg="Invalid Input";
							//$authUserNamespace->msg=$msg;
							$this->_redirect("/Profileerror/errormsg");

						}

					}
					//}
		 	}
		 	// echo urldecode($_GET);
		 	if(!empty($_GET) && !empty($authUserNamespace->report) && ($_GET['export'] == "report")){
		 		//echo "in";exit;
		 		$filename = "reports.csv"; // File Name
		 		//$path = "../../reports/";
		 		$path = FILE_UPLOAD_PATH."/res/files/reports/";
		 		$get_parameters = $authUserNamespace->report;
		 		//echo $get_parameters;exit;
		 		$data = $common_obj->getAllUserReports($get_parameters,$PaginateLimit,$filename,$path);
		 	}else{

		 		$_GET = $common_obj->cleanQueryParameter($_GET);
		 	/*	$pairs = explode('&',$_GET['search']);
		 		foreach($pairs as $pair) {
		 			$part = explode('=', $pair);
		 			$get_parameters[$part[0]] = $part[1];
		 		}
				*/
				$get_parameters = $_GET;
		 		// var_dump($_GET);
		 		$authUserNamespace->report = $get_parameters;
		 		$data = $common_obj->getAllUserReports($get_parameters,$PaginateLimit);
		 	}
		 	 
		 }
		 else
		 {

		 	$table['reports'] = "6";
		 	$authUserNamespace->report = $table['reports'];
		 	$data = $common_obj->getAllUserReports($table,$PaginateLimit);
		 }


		 $this->view->data=$data;
		 	


		}

		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}


	}
}
