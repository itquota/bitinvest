<?php
class ChangeofferController extends Zend_Controller_Action
{
	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");
		
	}

	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$specialofferObj = new Gbc_Model_DbTable_Specialoffer();
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

		$offerDetails=array();

		try{

			$offerDetails["id"] = $_POST["offerId"];
			$offerDetails["status"]= $_POST["status_value"];
			$offerDetails["token"]= $_POST["token"];
	//		if($authUserNamespace->token==$offerDetails["token"]){
			//$desiredStatusValue = 0;
			$offerstatus=0;
			if($offerDetails["status"]==0){

				//$desiredStatusValue =1;
				$offerstatus=1;

			}else{

				//$desiredStatusValue  = 0;
				$offerstatus=0;
			}

			$offerdata=array("status"=>$offerstatus,"updated_on"=>new Zend_Db_Expr('NOW()'));

			//$where=array("id"=>$offerid);
			//$where = array("id = " . $offerid);

			$offer=$specialofferObj->update($offerdata,"id=".$_POST["offerId"]);
				
			if(!empty($offer))
			{
				$msg = "true";


			}
			else
			{
				$msg = "Error: Status Of the Offer could Not be  changed  Please try again later or contact us if the problem persists.";

			}

			echo 	$msg ;exit;
	/*	}
		else{
			$data=array('success'=>'','failure'=>'Invalid Request Found.');
			 echo json_encode($data);exit;
				}
		*/
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}
}

