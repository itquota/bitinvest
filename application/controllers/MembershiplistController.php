<?php
class MembershiplistController extends Zend_Controller_Action{

	public function init()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Manage Membership";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!empty($authUserNamespace->user) &&  $authUserNamespace->user=='admin')
			{
				//$loggedIn==true;
			}
			else 
			{
				$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
				$this->_redirect("/Login");
			}
		
		$membershiplistObj = new Gbc_Model_DbTable_Membershiplist();
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

		try{
				
	 	$this->_helper->layout()->setLayout("admindashbord");//dashboard
	 	// ->joinLeft(array('kits'=>'kits'),"kits.invoice_id = kit_invoices.invoice_id",array('kits.comment as comments'))
	 	 

	 	$result=$membershiplistObj->fetchAll($membershiplistObj->select()
	 	->setIntegrityCheck(false)
	 	->from(array('membership_master')));
	 		
	 		

	 	$this->view->result=$result;

	 	//echo "<pre>";
	 	//print_r($result);exit;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}


	}
	public function changestatusAction()
	{
	
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$antixss = new Gbc_Model_Custom_StringLimit();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
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
		$username=$authUserNamespace->user;
		$id=$_POST['memberid'];
		$status=$_POST['memberstatus'];
		$token=$_POST['token'];
		//echo $token;exit;
//		if($authUserNamespace->token==$token){
		if($status=='1')
		{
			$status=0;
		}
		else{

			$status=1;
		}
			
			
		$membershiplistObj = new Gbc_Model_DbTable_Membershiplist();
			
		$result=$membershiplistObj->fetchRow($membershiplistObj->select()
		->setIntegrityCheck(false)
		->from(array('membership_master'))
		//->where("id='$id'"));
		->where("id=?",$id));
		
		
		if(!empty($result) && sizeof($result))
		{

			$upd_arr=array('status'=>$status);

			//$upd_member=$membershiplistObj->update($upd_arr,"id='".$id."'");
			$upd_member=$membershiplistObj->update($upd_arr,$DB->quoteInto("id=?",$id));
			
			
			if(!empty($upd_member))
			{
				/*$address[]=array('Status'=>$result->status);

				$arr=array('success'=>'','failure'=>'','data'=>$address);
				echo  json_encode($arr);*/
					
				$data=array('success'=>$status,'failure'=>'');
				echo json_encode($data);exit;
			}
			else
			{
				$data=array('success'=>'','failure'=>'');
				echo  json_encode($data);exit;
			}
			$this->_redirect("/Membershiplist");


		}
/*			
		}
		
	else{
			$data=array('success'=>'','failure'=>'Invalid Request Found.');
			 echo json_encode($data);exit;
				}
*/			
			
			
			
	}


}
