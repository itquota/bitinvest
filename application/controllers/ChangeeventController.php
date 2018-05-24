<?php
class ChangeeventController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");
		
	}


	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$addeventsObj = new Gbc_Model_DbTable_Events();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		
		$antixss = new Gbc_Model_Custom_StringLimit();
			foreach($_POST as $key => $value)
			{

				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						$data=array('success'=>'','failure'=>'Invalid Input.');
						echo json_encode($data);exit;

					}

				}

			}
		$token= $common_obj->cleanQueryParameter($_POST["token"]);
	//	if($authUserNamespace->token==$token){
			
		if(!empty($_POST['event'])){
			$id = str_replace("show_pop","",($_POST['event']));
			$checked = $common_obj->cleanQueryParameter($_POST['checked']);
			// echo "checked ".$checked;
			// $checked = !empty($checked)?0:1;
		






			$updata=array("pop_flag"=>$checked);
			$upevents=$addeventsObj->update($updata,$DB->quoteInto("id=?",$id));
			if(!empty($upevents))
			{echo "true";exit;}
			else{
				echo "Failed to change! Please try again";exit;
			}



		}

		$eventDetails=array();

		$eventDetails["id"] = $common_obj->cleanQueryParameter($_POST["eventId"]);
		$eventDetails["status"]= $common_obj->cleanQueryParameter($_POST["status_value"]);
		
		$eventStatus=0;

		if($eventDetails["status"]==0){
			$eventStatus =1;
		}else{
			$eventStatus=0;
		}

		$dataevents=array("status"=>$eventStatus,"updated_on"=>new Zend_Db_Expr('NOW()'));
		$event=$addeventsObj->update($dataevents,$DB->quoteInto("id=?",$eventDetails["id"]));

		if(!empty($event)){
			//account created. Commit info
			//$commit=commitTransaction($conn);
			//Show success and navigate user to Settings page
			$msg = " Status Of the Event has been changed  succesfully<br/>You will now be navigated back to Special offers page.";


			echo "true";exit;
			//$alertMessage = createAlertMsgBox($msg);
			//printArr($alertMessage);
			//$redirectURL = "../view/admin/add_events.php";
				
			//print("<script data-cfasync='false'>");
			//	print("var t = setTimeout(\"window.location='".$redirectURL."';\", 3000);");
			//print("</script>");
		} else {
			//printArr($updater);
			//Error creating account show error and die
			//$rollback = rollbackTransaction($conn);
			$msg = "Error: Status Of the Event could Not be  changed  Please try again later or contact us if the problem persists.";

			echo $msg;exit;
			//$alertMessage = createAlertMsgBox($msg);
			//printArr($alertMessage);
			//$redirectURL = "../view/admin/add_events.php";
				
			//print("<script data-cfasync='false'>");
			//	print("var t = setTimeout(\"window.location='".$redirectURL."';\", 3000);");
			//print("</script>");

			//print("<a href=".$redirectURL.">Click here if you are not redirected automatically by your browser within 3 seconds</a>");

		}
		/*
	}else{
			$data=array('success'=>'','failure'=>'Invalid Request Found.');
			 echo json_encode($data);exit;
				}
	*/	
		/*}
	else{
			$data=array('success'=>'','failure'=>'Invalid Request Found.');
			 echo json_encode($data);exit;
				}*/


	}




}
