<?php
class ChangecontractController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}

	public function indexAction()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$invoice_obj=new Gbc_Model_DbTable_Invoices();
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
		$invoiceid=$_POST['invoiceid'];
		$invoicestatus=$_POST['invoicestatus'];
		$token=$_POST['token'];
//		if($authUserNamespace->token==$token){
			if($invoicestatus =='1'){
				$invoicestatus="0";
			}else{
		 	$invoicestatus="1";
			}

			$updatestatusdata=array("invoice_status"=>$invoicestatus);

			$updatestatus=$invoice_obj->update($updatestatusdata,"invoice_id='".$_POST['invoiceid']."'");
			 

			$this->_redirect("/Pendcontracts");
/*		}

		else{
			$data=array('success'=>'','failure'=>'Invalid Request Found.');
			echo json_encode($data);exit;
		}
        */
		 
	}

}

