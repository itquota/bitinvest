<?php
class SendnotificationController extends Zend_Controller_Action{

	public function init()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$checkAdminAuthentication=$misc_obj->checkAdminAuthentication();
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");


			
	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Send Notification";
		/*$this->getResponse()->setHeader('Content-Type', 'application/json');
		 $this->_helper->layout->disableLayout();
		 $this->_helper->viewRenderer->setNoRender(true);*/
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$this->_helper->layout()->setLayout("admindashbord");
		$sendnotificationObj = new Gbc_Model_DbTable_Adminnotification();
		
		try {
				

				
			if($this->_request->isPost()){
				
			$antixss = new Gbc_Model_Custom_StringLimit();
		
			foreach($_POST as $key => $value)
			{
					//if($key!="text"){
				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						//$data=array('success'=>'','failure'=>'Invalid Input.');
						//echo json_encode($data);exit;
						$errormsg="Invalid Input";	
						$authUserNamespace->errormsg=$errormsg;
						$this->_redirect("/Sendnotification");
					}

				//}
			}
		}
				
				
				
				
				
				
				
				
				$token=$_POST['token'];
				//echo $token;exit;
			//	if(!empty($authUserNamespace->token) && $authUserNamespace->token==$token){

				//$text=$_POST['text'];
				if(empty($_POST['text']))
				{
					$message="<p style='color:red;'>Please enter Notification Text.</p>";
						
				}
				else {
					$updated_on=date('Y-m-d h:i:s');
					$data=array("status"=>'inactive',"flag"=>'0',"updated"=>$updated_on);
					$where=array(
				"status"=>'active',
				"flag"=>'1'
				
				);
				$upd=$sendnotificationObj->update($data,$where);
					
			 if(!empty($upd)){
			 	// printArr("<span style='margin-left: 20%;'>updated successfully</span>");
			 } else {
			 	$message="<p style='color:red;'>Error updating inactive</p>";
			 	
			 }

			 $data=array('text'=>$_POST['text']);
			 $ins=$sendnotificationObj->insert($data);
			 	
			 if (!empty($ins)) {
			 	//$row = mysql_fetch_assoc($result["dbResource"]);
			 	//$id = mysql_insert_id($conn);
			 	$id_res=$sendnotificationObj->fetchRow($sendnotificationObj->select()
			 	->setIntegrityCheck(false)
			 	->from(array('a'=>'admin_notifications'),array('a.id'))
			 	->order("a.id DESC")
			 	->limit(1)
			 	);
			 		
			 	$id=$id_res->id;
			 	//$returnArr["errCode"][-1] = -1;
			 	//$returnArr["errMsg"] = $row;
			 	// echo '<span style="color:green;font-wieght-bold;margin-left: 20%;color:green">Notification Text is submitted Successfully</span>';
			 } else {
			 	//$returnArr["errCode"][5] = 5;
			 	//$returnArr["errMsg"] = $result["errMsg"];
			 	
			$message= "<p style='color:red;'>Notification Text Not submitted..Try again</p>";
			 }
			 	
			 $updated_on=date('Y-m-d h:i:s');
			 $updatedata=array("status"=>'active',"flag"=>'1',"updated"=>$updated_on);
			 $where=array(
			 	'id'=>$id
			 );
			 $updat=$sendnotificationObj->update($updatedata,$where);
			 if(!empty($updat)){
			 	$message="<p style='color:green;'>Sent Notification successfully</p>";
					/* $query="select * from admin_notifications where id = '".$id."'";
					 $results = runQuery($query, $conn);
					 	
					 if(noError($results)){
						$row = mysql_fetch_assoc($results["dbResource"]);
						} */
			 } else {
			 	$message= "<p style='color:red;'>Notification Text not updated..Try again</p>";

			 }
			 	
			 }

			 $this->view->msg=$message;
		/*	}
			else{
					//$data=array('success'=>'','failure'=>'Invalid Request Found.');
					// echo json_encode($data);exit;
				$errormsg="Invalid Request Found";	
				$authUserNamespace->errormsg=$errormsg;
			
				}
		*/	
		}
		}
		catch(Exception $e)
		{
			$this->view->msg=$message;
		}

	}
}
