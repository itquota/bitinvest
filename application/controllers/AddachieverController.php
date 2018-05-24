<?php

class AddachieverController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Add Achievers";

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$achieversObj = new Gbc_Model_DbTable_Achiever();
					
		try{

	 	$this->_helper->layout()->setLayout("admindashbord");//dashboard

	 	if($this->_request->isPost()){
	 		
	 			
	 	
	 		
	 	$antixss = new Gbc_Model_Custom_StringLimit();
		foreach($_POST as $key => $value)
		{
			//if($key!="username" && $key!="prize"){
			if(isset($value) && $value != ""){
				$antixss->setEncoding($value, "UTF-8");
				if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

					//$data=array('success'=>'','failure'=>'Invalid Input.');
					//echo json_encode($data);exit;
					$msg="Invalid Input";
					$data=array('success'=>'','failure'=>$msg);
				 		echo json_encode($data);exit;
				
				}

			}
			
		//}
	}
	 		//echo $token;exit;
	 		if(!empty($_POST['username']) && $_POST['username']!="")
	 		{
			$username=$_POST['username'];
	 		}
	 		else{
	 			$msg="Please enter Username.";
	 			$data=array('success'=>'','failure'=>$msg);
				 		echo json_encode($data);exit;
				
	 		}
	 		if(!empty($_POST['pairs']) && $_POST['pairs']!=""){
	 		$pairs=$_POST['pairs'];
	 			
	 		}
	 		else{
	 			$msg="Please enter Pairs.";
	 			$data=array('success'=>'','failure'=>$msg);
				 		echo json_encode($data);exit;
				
	 		}
	 		if(!empty($_POST['prize']) && $_POST['prize']!="")
	 		{
	 		$prizes=$_POST['prize'];
	 		}
	 		else{
	 			$msg="Please enter Prize.";
	 			$data=array('success'=>'','failure'=>$msg);
				 		echo json_encode($data);exit;
			
	 		}
	 		$token=$_POST['token'];
	 		
		//	if($username!="" && $pairs!="" && $prizes!="" ){
//	 		if($authUserNamespace->token==$token){
	 				
	 			$address=array('username'=>$username,'pairs'=>$pairs,'prize'=>$prizes);
	 				
	 			$achieverdata=$achieversObj->insert($address);
	 				
	 				
	 			if(!empty($achieverdata)){
	 				//$msg='Achiever Added Successfully';
	 				$data=array('success'=>'Achiever Added Successfully','failure'=>'');
				 		echo json_encode($data);exit;
				
	 				//print("<script data-cfasync='false'>");
	 				//print("var t = setTimeout(\"window.location='add_achievers.php';\", 3000);");
	 				//print("</script>");
	 			} else {
	 			//$this->view->msg= 'Please try again!!';
	 			$msg='Please try again!!';
	 			$data=array('success'=>'','failure'=>$msg);
				 		echo json_encode($data);exit;
				

	 			}

	 				
	 			//$this->view->result=$result;
	 				
/*	 		}
	 		else{
	 			
	 			$data=array('success'=>'','failure'=>'Invalid Request Found.');
	 			echo json_encode($data);exit;
	 			
	 			
				}
				
*/				
				
	 //	}else{
	 //		$data=array('success'=>'','failure'=>'All fields are required.');
	 //		echo json_encode($data);exit;
	 		
	//	}
	 	}
		}
		catch(Exception $e)
		{
			$this->view->msg= $e->getMessage();
		}


	}
}
