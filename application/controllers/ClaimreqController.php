<?php
class ClaimreqController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		try
		{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$username=$authUserNamespace->user;
			$this->_helper->layout()->setLayout("dashbord");
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$userInfo = $common_obj->getUserInfo($username);
			$special_off_obj=new Gbc_Model_DbTable_Specialoffer();
			$antixss = new Gbc_Model_Custom_StringLimit();
			
			if(empty($userInfo) || sizeof($userInfo)<=0)
			{
				
			}
				if(!empty($_GET['o'])){
					
					
					$value = $_GET['o'];
				$antixss->setEncoding($_GET['o'], "UTF-8");
				if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
					$this->_redirect("/Profileerror/errormsg");

				}
					
					
					$offer_id = $common_obj->cleanQueryParameter($_GET['o']);
					
					$OfferId=substr($offer_id,6,2);
					
					$this->view->OfferId = $OfferId;
					// echo $OfferId;
					/*	$SpecialOffers =$special_off_obj->fetchRow($special_off_obj->select()
							->where("id = '$OfferId'")
							);*/
					
						$SpecialOffers =$special_off_obj->fetchRow($special_off_obj->select()
							->where("id = ?",$OfferId)
							);
							
					if(empty($SpecialOffers) || sizeof($SpecialOffers)<=0)
					{
						
					}
					
				}
			$this->view->offerDetail=$SpecialOffers;
	

		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}
	public function generateclaimAction()
	{
		try {
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$username = $authUserNamespace->user;
			$antixss = new Gbc_Model_Custom_StringLimit();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$msg='';
			foreach($_POST as $key => $value)
			{

				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						$data=array('success'=>'','failure'=>'Invalid Input.','data'=>'Invalid Input.');
						echo json_encode($data);exit;

					}

					//echo $key . " - " . $value . " - " . $antixss->setFilter($value, "black", "string")."\n";
				}

			}
			if(empty($username) || $username=='')
			{
				//echo "Session timeout. Please login again and try.";exit;
				$arr=array('success'=>'','failure'=>'failure','data'=>'Session timeout. Please login again and try.');
				echo json_encode($arr);exit;
			}
			$token=$_POST['token'];
		//	if(!empty($authUserNamespace->user) && $authUserNamespace->user!='' && $authUserNamespace->token==$token){
				$choice=$common_obj->cleanQueryParameter($_POST['choice']);
				$mobile=$common_obj->cleanQueryParameter($_POST['mob']);
				$comment=$common_obj->cleanQueryParameter($_POST['comment']);
				$email=$common_obj->cleanQueryParameter($_POST['email']);
				$username=$authUserNamespace->user;
				if(empty($comment) || $comment=='')
				{
					$msg="Please add Description";
				}
				else if(empty($email) || $email=='')
				{
					$msg="Please select email";
				}
				else if(empty($mobile) || $mobile=='')
				{
					$msg="Please select mobile";
				}
				else if(empty($choice) || $choice=='')
				{
					$msg="Please select choice";
				}

				if(isset($msg) && $msg!='')
				{
					$arr=array('success'=>'','failure'=>'failure','data'=>$msg);
					echo json_encode($arr);exit;
				}

				$common_obj = new Gbc_Model_Custom_CommonFunc();
				$misc_obj = new Gbc_Model_Custom_Miscellaneous();
				$claim_offers = new Gbc_Model_DbTable_Claimedoffers();
				$daily_pairs = $common_obj->dailyPairs();
	
				//$offer_completion_time='';
				$OfferId=$common_obj->cleanQueryParameter($_POST['OfferId']);
				$offer_completion_time=$daily_pairs['CompletionOffersArray'];
				$offer_time= ($offer_completion_time[$OfferId]['offer_completion_time']);
				/*foreach($daily_pairs['CompletionOffersArray'] as $key => $daily_pair){
					if($key == $OfferId){
						$offer_completion_time = $daily_pairs['CompletionOffersArray'][$key]['completionTime'];
					}
					
				}*/
				$ip_address= $misc_obj->get_client_ip();
				$insert_arr=array('username'=>$username,'offer_id'=>$OfferId,'preferred_option'=>$choice,'email'=>$email,'mobile'=>$mobile,'comment1'=>$comment,'ip_address1'=>$ip_address,'offer_completion_time'=>$offer_time);
				$insert_qry=$claim_offers->insert($insert_arr);
				
				$arr=array('success'=>'success','failure'=>'','data'=>'Offer claim request successfully submitted');
				echo json_encode($arr);exit;	
	/*		}
			else 
			{
				echo "Session timeout. Please login again and try.";exit;
				$arr=array('success'=>'','failure'=>'failure','data'=>'Invalide Token.');
				echo json_encode($arr);exit;
			} */

		}catch(Exception $e)
		{
			echo $e->getMessage();exit;
			$arr=array('success'=>'','failure'=>'failure','data'=>'Error while creating claim offer request. Please try again.');
			echo json_encode($arr);exit;
		}
	}


}
