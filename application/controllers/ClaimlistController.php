<?php
class ClaimlistController extends Zend_Controller_Action{

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
		$antixss = new Gbc_Model_Custom_StringLimit();
		$data1=$misc_obj->GetAccessRightByUserId('37',$user_id);
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
		 $PaginateLimit=100;
		 	
		 if(!empty($_POST)){
		 		
		 	$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		 		
		 	foreach($_POST as $key => $value)
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
		 		
		 	$_POST = $common_obj->cleanQueryParameter($_POST);
		 		
		 	$authUserNamespace->filter_by = $_POST;
		 	$token=$_POST['token'];
		// 	if($authUserNamespace->token==$token){
		 		$SearchResult = $common_obj->getAllClaimedRequests($_POST,$PaginateLimit);
		 		$AllClaimedRequests = $SearchResult['data'];
		/* 	}
		 	else{
		 		//$data=array('success'=>'','failure'=>'Invalid Request found.');
		 		//	echo json_encode($data);exit;$msg="Invalid Request found";

		 		$msg="Invalid Request found";
		 		$authUserNamespace->msg=$msg;

		 		 
		 	} 
		*/
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
				// var_dump($_GET);
				// var_dump($_SESSION);
				if(!empty($_GET) && !empty($authUserNamespace->filter_by)){
					$_GET = $common_obj->cleanQueryParameter($_GET);
					// var_dump($_GET);
					$pairs = explode('&',$_GET['search']);
					// var_dump($pairs);
				 foreach($pairs as $pair) {
				 	$part = explode('=', $pair);
				 	$get_parameters[$part[0]] = $part[1];
				 }
				 // var_dump($get_parameters);
				 $authUserNamespace->filter_by = $get_parameters;
				 $SearchResult = $common_obj->getAllClaimedRequests($get_parameters,$PaginateLimit);
				 $AllClaimedRequests = $SearchResult['data'];
				}
				// echo $data;
				// exit;
		 }else{
		 	$data['filter_by']='filter';
		 	$authUserNamespace->filter_by = $data['filter_by'];
		 	$SearchResult = $common_obj->getAllClaimedRequests($data,$PaginateLimit);
		 	// $kitDetails = getKitsData($conn,$PaginateLimit,$startLimit);
		 	$AllClaimedRequests = $SearchResult['data'];
		 }

		 $this->view->AllClaimedRequests=$AllClaimedRequests;

		 $this->view->SearchResult=$SearchResult;


		}

		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}


	}
}
