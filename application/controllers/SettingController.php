<?php

class SettingController extends Zend_Controller_Action{

	public function init(){

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}


	public function indexAction()
	{
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		try
		{
			$this->view->title="Gainbitcoin - Setting";
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			if(!empty($authUserNamespace->user) &&  $authUserNamespace->user=='admin')
			{
				$loggedIn==true;
			}
			else
			{
				$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
				$this->_redirect("/Login");
			}

			$this->_helper->layout()->setLayout("admindashbord");//dashboard
			//if($this->_request->isPost())
			//{
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			if($this->_request->isXmlHttpRequest())
			{

					
				$glob_obj=new Gbc_Model_DbTable_Globalvariables();
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
				if(!empty($_POST["inputHashRate"]) && ($_POST["inputHashRate"])!="")
				{
					$globalVariables["hash_rate"] = $common_obj->cleanQueryParameter(($_POST["inputHashRate"]));
				}
				else{
					$msg = "Please enter Hash Rate";
					$data=array('success'=>'','failure'=>$msg);
					echo json_encode($data);exit;
				}
				if(!empty($_POST["inputHashRateMhz"]) && ($_POST["inputHashRateMhz"])!="")
				{
					$globalVariables["hash_rate_hardware"] = $common_obj->cleanQueryParameter(($_POST["inputHashRateMhz"]));
				}
				else{
					$msg = "Please enter Hardware Hash Rate";
					$data=array('success'=>'','failure'=>$msg);
					echo json_encode($data);exit;
				}
				if(!empty($_POST["inputTarget"]) && ($_POST["inputTarget"])!="")
				{
					$globalVariables["target"] = $common_obj->cleanQueryParameter(($_POST["inputTarget"]));
				}
				else{
					$msg = "Please enter Target";
					$data=array('success'=>'','failure'=>$msg);
					echo json_encode($data);exit;
				}
				if(!empty($_POST["inputProgress"]) && ($_POST["inputProgress"])!="")
				{
					$globalVariables["progress"] =  $common_obj->cleanQueryParameter(($_POST["inputProgress"]));
				}
				else{
					$msg = "Please enter Progress";
					$data=array('success'=>'','failure'=>$msg);
					echo json_encode($data);exit;
				}
				if(!empty($_POST["inputPayoutGhz"]) && ($_POST["inputPayoutGhz"])!="")
				{
					$globalVariables["payout_ghz"] = $common_obj->cleanQueryParameter(($_POST["inputPayoutGhz"]));
				}
				else{
					$msg = "Please enter Payout";
					$data=array('success'=>'','failure'=>$msg);
					echo json_encode($data);exit;
				}
				if(!empty($_POST["inputPayoutMhz"]) && ($_POST["inputPayoutMhz"])!="")
				{
					$globalVariables["payout_hardware"] = $common_obj->cleanQueryParameter(($_POST["inputPayoutMhz"]));
				}
				else{
					$msg = "Please enter Hardware Payout";
					$data=array('success'=>'','failure'=>$msg);
					echo json_encode($data);exit;
				}
				if(!empty($_POST["inputPayoutNic"]) && ($_POST["inputPayoutNic"])!="")
				{
					$globalVariables["payout_inc_nic"] = $common_obj->cleanQueryParameter(($_POST["inputPayoutNic"]));
				}
				else{
					$msg = "Please enter Percent Increase";
					$data=array('success'=>'','failure'=>$msg);
					echo json_encode($data);exit;
				}
				if(!empty($_POST["inputAllowedKits"]) && ($_POST["inputAllowedKits"])!="")
				{
					$globalVariables["allowed_kits"] = $common_obj->cleanQueryParameter(($_POST["inputAllowedKits"]));
				}
				else{
					$msg = "Please enter Allowed Kits";
					$data=array('success'=>'','failure'=>$msg);
					echo json_encode($data);exit;
				}
				if(!empty($_POST["inputPerKitCost"]) && ($_POST["inputPerKitCost"])!="")
				{
					$globalVariables["per_kit_cost"] = $common_obj->cleanQueryParameter(($_POST["inputPerKitCost"]));
				}
				else{
					$msg = "Please enter Input Cost";
					$data=array('success'=>'','failure'=>$msg);
					echo json_encode($data);exit;
				}
				if(!empty($_POST["inputShippingCost"]) && ($_POST["inputShippingCost"])!="")
				{
					$globalVariables["shipping_cost"] =$common_obj->cleanQueryParameter(($_POST["inputShippingCost"]));
				}
				else{
					$msg = "Please enter Shipping Cost";
					$data=array('success'=>'','failure'=>$msg);
					echo json_encode($data);exit;
				}


				$globalVariables["token"] = $common_obj->cleanQueryParameter(($_POST["inputtoken"]));

		//		if($authUserNamespace->token==$globalVariables["token"]){
					$arr=array('hash_rate'=>$globalVariables["hash_rate"],'hash_rate_hardware'=>$globalVariables["hash_rate_hardware"],'target'=>$globalVariables["target"],'progress'=>$globalVariables["progress"],'payout_ghz'=>$globalVariables["payout_ghz"],'payout_hardware'=>$globalVariables["payout_hardware"],'payout_inc_nic'=>$globalVariables["payout_inc_nic"],'timestamp'=> new Zend_Db_Expr('NOW()'),'allowed_kits'=>$globalVariables["allowed_kits"],'per_kit_cost'=>$globalVariables["per_kit_cost"],'shipping_cost'=>$globalVariables["shipping_cost"]);
					$upd_data=$glob_obj->insert($arr);

					$msg="Settings have been changed Succesfully";
					$this->view->msg=$msg;
					$result=$common_obj->getGlobalVar('','');
					//$this->view->msg='';
					$this->view->result=$result;
					$data=array('success'=>'success','failure'=>'');
					echo json_encode($data);exit;
		/*		}else{
					$data=array('success'=>'','failure'=>'failure');
					echo json_encode($data);exit;
				}
		*/			
			}
			else
			{
				$result=$common_obj->getGlobalVar('','');
				$this->view->msg='';
				$this->view->result=$result;
			}


		}

		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}

	public function submitAction()
	{
		try
		{

			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$this->_helper->layout()->setLayout("admindashbord");//dashboard
			//if($this->_request->isPost())
			//{
			//$common_obj = new Gbc_Model_Custom_CommonFunc();
			$url= BASE."/Submitsettingapi";
			$result=$common_obj->call_curl($url);
			$result=(array)json_decode($result,true);
			$this->view->result=$result;
			//	$records_per_page = $this->_request->getParam('getPageValue',10);
			//	$this->view->records_per_page = $records_per_page;


		}

		catch(Exception $e)
		{
			echo $e->getMessage();
		}
	}
}
