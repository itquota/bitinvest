<?php

class AddeventsController extends Zend_Controller_Action{

	public function init()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$checkAdminAuthentication=$misc_obj->checkAdminAuthentication();
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");



	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Add Events";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$addeventsObj = new Gbc_Model_DbTable_Events();
		$common_obj = new Gbc_Model_Custom_CommonFunc();


		$this->_helper->layout()->setLayout("admindashbord");

		$result=array();
		$result=$addeventsObj->fetchAll($addeventsObj->select()
		->setIntegrityCheck(false)
		->from(array('events')));



		$this->view->result=$result;
		//	echo "<pre>";
		//print_r($result);exit;
		$msg= '';
		if($this->_request->isPost('add_event'))
		{
		
			$antixss = new Gbc_Model_Custom_StringLimit();
			foreach($_POST as $key => $value)
			{

				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						$msg = "Invalid Input";
						$this->view->msg=$msg;
							

					}

				}

			}
			if($msg!=''){
			}
			else{
				
				$token=$common_obj->cleanQueryParameter($_POST['token']);
			//	if(!empty($authUserNamespace->token) && $authUserNamespace->token==$token){
				
					if(empty($_POST['title']) || $_POST['title']=='')
					{
						$msg="<span style='text-align:right;margin-left: 20%;color:red'>Please enter Title.</span>";
						//$this->view->msg=$msg;
					}
					else if(empty($_POST['start']) || $_POST['start']=='')
					{
						$msg = "<span style='text-align:right;margin-left: 20%;color:red'>Please enter Start date</span>";
					}
					else if(empty($_POST['end']) || $_POST['end']=='')
					{
						$msg = "<span style='text-align:right;margin-left: 20%;color:red'>Please enter End date</span>";
					}
					else
					{
						if (!empty($_POST["start"])){
							$start = date('Y-m-d H:i:s',strtotime($_POST['start']));
						}else{
							$start = '';
						}

						if (!empty($_POST["end"])){
							$end = date('Y-m-d H:i:s',strtotime($_POST['end']));

						}else{
							$end = '';
						}
						try{

							$result=$addeventsObj->fetchRow($addeventsObj->select()
							->setIntegrityCheck(false)
							->from(array('events')));


							//->order("id DESC"));


							/*
							$result1=sizeof($result);
							if(isset($result1) && $result1>0)
							{

								$id=$result->id+1;

							}
							else
							{
								$id='1';
							}
							*/



							$eventdata=array('title'=>$_POST['title'],'start'=>$start,'end'=>$end);
							$eventinsert=$addeventsObj->insert($eventdata);
						}
						catch(Exception $e)
						{
							echo $e->getMessage();
						}

						if (!empty($eventinsert)) {
						
							/*$row = mysql_fetch_assoc($result["dbResource"]);

							$returnArr["errCode"][-1] = -1;
							$returnArr["errMsg"] = $row;*/
							$msg= '<span style="color:green;font-weight-bold;margin-left: 20%;color:green">Event is added successfully</span>';
						} else {
						
							/*$returnArr["errCode"][5] = 5;
							 $returnArr["errMsg"] = $result["errMsg"];*/
							$msg= "<span style='margin-left: 20%;color:red'>Some error..Try again</span>";

						}


					}
		/*		}
				else
				{
					$msg = "<span style='text-align:right;margin-left: 20%;color:red'>Invalid request found</span>";
				}*/
			}
		
			$this->view->msg=$msg;
		



		}


	}
	
	
	public function eventaddAction()
	{
		
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$addeventsObj = new Gbc_Model_DbTable_Events();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
//	$msg= '';
		if($this->_request->isPost())
		{
		
			$antixss = new Gbc_Model_Custom_StringLimit();
			foreach($_POST as $key => $value)
			{

				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						//$msg = "Invalid Input";
						//$this->view->msg=$msg;
						$data=array('success'=>'','failure'=>'Invalid Input');
						echo json_encode($data);exit;
						
							

					}

				}

			}
//		if($msg!=''){
	//	}
	//	else{
				
				$token=$common_obj->cleanQueryParameter($_POST['token']);
			//	if(!empty($authUserNamespace->token) && $authUserNamespace->token==$token){
				
					if(empty($_POST['title']) || $_POST['title']=='')
					{
						//$msg="<span style='text-align:right;margin-left: 20%;color:red'>Please enter Title.</span>";
						$data=array('success'=>'','failure'=>'Please enter Title.');
						echo json_encode($data);exit;
						
						//$this->view->msg=$msg;
					}
					else if(empty($_POST['start']) || $_POST['start']=='')
					{
						//$msg = "<span style='text-align:right;margin-left: 20%;color:red'>Please enter Start date</span>";
							$data=array('success'=>'','failure'=>'Please enter Start date');
						echo json_encode($data);exit;
					
						
					}
					else if(empty($_POST['end']) || $_POST['end']=='')
					{
						//$msg = "<span style='text-align:right;margin-left: 20%;color:red'>Please enter End date</span>";
						$data=array('success'=>'','failure'=>'Please enter End date');
						echo json_encode($data);exit;
					
						
					}
					else
					{
						if (!empty($_POST["start"])){
							$start = date('Y-m-d H:i:s',strtotime($_POST['start']));
						}else{
							$start = '';
						}

						if (!empty($_POST["end"])){
							$end = date('Y-m-d H:i:s',strtotime($_POST['end']));

						}else{
							$end = '';
						}
						try{

							$result=$addeventsObj->fetchRow($addeventsObj->select()
							->setIntegrityCheck(false)
							->from(array('events')));


							
							$eventdata=array('title'=>$_POST['title'],'start'=>$start,'end'=>$end,'updated_on'=>new Zend_Db_Expr('NOW()'));
							$eventinsert=$addeventsObj->insert($eventdata);
						}
						catch(Exception $e)
						{
							echo $e->getMessage();exit;
						}

						if (!empty($eventinsert)) {
						
							/*$row = mysql_fetch_assoc($result["dbResource"]);

							$returnArr["errCode"][-1] = -1;
							$returnArr["errMsg"] = $row;*/
							//$msg= '<span style="color:green;font-weight-bold;margin-left: 20%;color:green">Event is added successfully</span>';
							$data=array('success'=>'Event is added successfully','failure'=>'');
								echo json_encode($data);exit;
					
							
						} else {
						
							/*$returnArr["errCode"][5] = 5;
							 $returnArr["errMsg"] = $result["errMsg"];*/
							//$msg= "<span style='margin-left: 20%;color:red'>Some error..Try again</span>";
							$data=array('success'=>'','failure'=>'Some error..Try again');
								echo json_encode($data);exit;
					

						}


					}
	/*			}
				else
				{
					//$msg = "<span style='text-align:right;margin-left: 20%;color:red'>Invalid request found</span>";
					$data=array('success'=>'','failure'=>'Invalid request found');
								echo json_encode($data);exit;
					
				}*/
	//	}
		
			//$this->view->msg=$msg;
		



		}
		
		
			
		
	}






}
