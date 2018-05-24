<?php
class NewmembershipmasterController extends Zend_Controller_Action{

	public function init()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Manage Membership";
		$this->_helper->layout()->setLayout("admindashbord");
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
		$addmembershipobj=new Gbc_Model_DbTable_Membershiplist();
		
	
		$resultaddmember=$addmembershipobj->fetchAll($addmembershipobj->select()
		->setIntegrityCheck(false)
		->from(array('membership_master'))
		);
		
		/*
		$result1=sizeof($resultaddmember);
		if(isset($result1) && $result1>0)
		{
			$id=$resultaddmember->id+1;
		}
		else
		{
			$id=1;
		}*/

		if($this->_request->isPost())
		{
			$antixss = new Gbc_Model_Custom_StringLimit();
			foreach($_POST as $key => $value)
			{
				///if($key!="membershiptype"){
				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						$data=array('success'=>'','failure'=>'Invalid Input.');
						echo json_encode($data);exit;

					}

					}
				//}
			}
			
			
			

			if(!empty($_POST['membershiptype']) && $_POST['membershiptype']!=""){
					
				$membershiptype=$_POST['membershiptype'];
				
				}
				else{
					$msg="Please enter Membership Type";
					$data=array('success'=>'','failure'=>$msg);
				 		echo json_encode($data);exit;
				 
				}
				if(!empty($_POST['investmentstart']) && $_POST['investmentstart']!="")
				{
				$investmentstart=$_POST['investmentstart'];
				}
				else{
					$msg="Please enter Investment Start";
					$data=array('success'=>'','failure'=>$msg);
				 		echo json_encode($data);exit;
					
				}
				if(!empty($_POST['investmentend']) && $_POST['investmentend']!="")
				{
				$investmentend=$_POST['investmentend'];
				}
				else{
					$msg="Please enter Investment End";
					$data=array('success'=>'','failure'=>$msg);
				 		echo json_encode($data);exit;
				}
				if(!empty($_POST['levelmax']) && $_POST['levelmax']!="")
				{
					$levelmax=$_POST['levelmax'];
				}
				else{
					$msg="Please enter Level Max";
					$data=array('success'=>'','failure'=>$msg);
				 		echo json_encode($data);exit;
				}
				
				if(!empty($_POST['binarymatching']) && $_POST['binarymatching'])
				{
				$binarymatching=$_POST['binarymatching'];
				}
				else{
					$msg="Please enter  Binary Matching";
					$data=array('success'=>'','failure'=>$msg);
				 		echo json_encode($data);exit;
				}
				if(!empty($_POST['binarymatchingmultiple']) && $_POST['binarymatchingmultiple']!="")
				{
				$binarymatchingmultiple=$_POST['binarymatchingmultiple'];
				}
				else{
					$msg="Please enter  Binary Matching Multiple";
					$data=array('success'=>'','failure'=>$msg);
				 		echo json_encode($data);exit;
				}
				if(!empty($_POST['binarycapping']) && $_POST['binarycapping']!="")
				{
				$binarycapping=$_POST['binarycapping'];
				}
				else{
					$msg="Please enter  Binary Capping";
					$data=array('success'=>'','failure'=>$msg);
				 		echo json_encode($data);exit;
				}
				if(!empty($_POST['trimming']) && $_POST['trimming']!="")
				{
					$trimming=$_POST['trimming'];
				}
				else{
					$msg="Please enter Trimming";
					$data=array('success'=>'','failure'=>$msg);
				 		echo json_encode($data);exit;
				}
				if(!empty($_POST['miningreturns']) && $_POST['miningreturns']!="")
				{
				$miningreturns=$_POST['miningreturns'];
				}
				else{
					$msg="Please enter Mining Returns";
					$data=array('success'=>'','failure'=>$msg);
				 		echo json_encode($data);exit;
				}
				
				if(!empty($_POST['directsalescomission']) && $_POST['directsalescomission']!="")
				{
				$directsalescomission=$_POST['directsalescomission'];
				}
				else {
						$msg="Please enter Direct Sales Commission";
					$data=array('success'=>'','failure'=>$msg);
				 		echo json_encode($data);exit;
				}
				
			$token=$_POST['token'];
	//		if($authUserNamespace->token==$token){
			
			$insertmembershipdata=array("membership_type"=>$membershiptype,"investment_start"=>$investmentstart,"investment_end"=>$investmentend,"level_max"=>$levelmax,"binary_matching"=>$binarymatching,"binary_matching_multiple"=>$binarymatchingmultiple,"binary_capping"=>$binarycapping,"trimming"=>$trimming,"mining_returns"=>$miningreturns,"direct_sales_comission"=>$directsalescomission);
			$membershipdata=$addmembershipobj->insert($insertmembershipdata);

			if(!empty($membershipdata))
			{
				$arr=array('success'=>'Data inserted successfully','failure'=>'');
				echo  json_encode($arr);exit;
			}
			else {
				$arr=array('success'=>'','failure'=>'failure');
				echo  json_encode($arr);exit;
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

}
