<?php
class UserdetailsController extends Zend_Controller_Action
{
	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");


	}

	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Users Details";
		$this->_helper->layout()->setLayout("admindashbord");
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('23',$user_id);
		if((!empty($data1->view) && ($data1->view==1)) || $authUserNamespace->user=='admin')
		{

		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}

			
		$Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
		if($this->_request->isPost()){
			$bflag = 0;
			$antixss = new Gbc_Model_Custom_StringLimit();
			foreach($_POST as $key => $value)
			{

				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						//	$data=array('success'=>'','failure'=>'Invalid Input.');
						//echo json_encode($data);exit;
						$bflag = 1;

					}

				}

			}

			if($bflag!=1)
			{
				$token=$Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST['token']);
		//		if($authUserNamespace->token==$token){
					if(!empty($_POST['username']) || !empty($_POST["startdate"]) || !empty($_POST["enddate"])){
						$startdate = (date('Y-m-d',strtotime($Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST["startdate"]))));
						$enddate = (date('Y-m-d',strtotime($Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST["enddate"]))))." 23:59:59";
						$Postusername = $_POST['username'];
						$userDetails =$Gbc_Model_Custom_func_obj-> userBusinessDetails($Postusername,$startdate,$enddate);

						if(!empty($userDetails))
						{
							$this->view->result=$userDetails;
						}
						else {
							$msg="No records found";
							$this->view->msg=$msg;
						}


						//echo "inside if";exit;
					}else{

						$msg="Please enter either Username/Date";
						$this->view->msg=$msg;

					}
		/*		}else{
					//$data=array('success'=>'','failure'=>'Invalid Request Found.');
					//echo json_encode($data);exit;
					$msg="Invalid token";
					$this->view->msg=$msg;
						
				} */
			}
			else 
			{
				$msg="Invalid Request Found";
				$this->view->msg=$msg;
			}

		}
	}
}
