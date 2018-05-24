<?php
class EditmembershipmasterController extends Zend_Controller_Action{

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
		
		$id=$_POST['edit'];
		

		$membershiplistObj=new Gbc_Model_DbTable_Membershiplist();
		
		$result=$membershiplistObj->fetchRow($membershiplistObj->select()
				->setIntegrityCheck(false)
				->from(array('membership_master'))
				->where("id=?",$id));
			
		
		
		$this->view->result=$result;


	
	}
		public function updatemembershipmasterAction()
		{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$membershiplistObj=new Gbc_Model_DbTable_Membershiplist();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			
					
		if($this->_request->isXmlHttpRequest())
		{
				
			$antixss = new Gbc_Model_Custom_StringLimit();
			foreach($_POST as $key => $value)
			{
				//if($key!='membershiptype'){
					
				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						$data=array('success'=>'','failure'=>'Invalid Input.');
						echo json_encode($data);exit;

					}
					

				}
			//}

			}
			
			if(!empty($_POST['uid']))
			{
				if(!empty($_POST['membershiptype']) && $_POST['membershiptype']!="")
				{
				$membershiptype=$_POST['membershiptype'];
				}
				else
				{
					$msg="Please enter Membership Type";
					$data=array('success'=>'','failure'=>$msg);
				  		echo json_encode($data);exit;
				}
				if(!empty($_POST['investmentstart']) && $_POST['investmentstart']!="")
				{
				$investmentstart=$_POST['investmentstart'];
				}
				else 
				{
					$msg="Please enter  Investment Start";
					$data=array('success'=>'','failure'=>$msg);
				  		echo json_encode($data);exit;
				}
				
				if(!empty($_POST['investmentend']) && $_POST['investmentend']!="")
				{
				$investmentend=$_POST['investmentend'];
				}
				else
				{
					$msg="Please enter Investment End";
					$data=array('success'=>'','failure'=>$msg);
				  		echo json_encode($data);exit;
				}
				
				if(!empty($_POST['levelmax']) && $_POST['levelmax']!="")
				{
				$levelmax=$_POST['levelmax'];
				}
				else
				{
					$msg="Please enter Level Max";
					$data=array('success'=>'','failure'=>$msg);
				  		echo json_encode($data);exit;
				}
				
				if(!empty($_POST['binarymatching']) && $_POST['binarymatching']!="")
				{
				$binarymatching=$_POST['binarymatching'];
				}
				else
				{
					$msg="Please enter Binary Matching";
					$data=array('success'=>'','failure'=>$msg);
				  		echo json_encode($data);exit;
				}
				
				if(!empty($_POST['binarymatchingmultiple']) && $_POST['binarymatchingmultiple']!="")
				{
				$binarymatchingmultiple=$_POST['binarymatchingmultiple'];
				}
				else 
				{
					$msg="Please enter Binary Matching Multiple";
					$data=array('success'=>'','failure'=>$msg);
				  		echo json_encode($data);exit;
				}
				
				if(!empty($_POST['binarycapping']) && $_POST['binarycapping']!="")
				{
				$binarycapping=$_POST['binarycapping'];
				}
				else
				{
					$msg="Please enter Binary Capping";
					$data=array('success'=>'','failure'=>$msg);
				  		echo json_encode($data);exit;
				}
				
				if(!empty($_POST['trimming']) && $_POST['trimming']!="")
				{
				$Trimming=$_POST['trimming'];
				}
				else 
				{
					$msg="Please enter Trimming";
					$data=array('success'=>'','failure'=>$msg);
				  		echo json_encode($data);exit;
				}
				
				if(!empty($_POST['miningreturns']) && $_POST['miningreturns']!="")
				{
				$miningreturns=$_POST['miningreturns'];
				}
				else
				{
					$msg="Please enter Mining Returns";
					$data=array('success'=>'','failure'=>$msg);
				  		echo json_encode($data);exit;
				}
				
				if(!empty($_POST['Directsalescomission']) && $_POST['Directsalescomission']!="")
				{
				$Directsalescomission=$_POST['Directsalescomission'];
				}
				else
				{
					$msg="Please enter Direct Sales Comission";
					$data=array('success'=>'','failure'=>$msg);
				  		echo json_encode($data);exit;
				}
					$token=$_POST['token'];
		//echo $token;exit;
	//	if($authUserNamespace->token==$token){
				$updata=array("membership_type"=>$membershiptype,"investment_start"=>$investmentstart,"investment_end"=>$investmentend,"level_max"=>$levelmax,"binary_matching"=>$binarymatching,"binary_matching_multiple"=>$binarymatchingmultiple,"binary_capping"=>$binarycapping,"trimming"=>$Trimming,"mining_returns"=>$miningreturns,"direct_sales_comission"=>$Directsalescomission);
				$membershipdata=$membershiplistObj->update($updata,$DB->quoteInto("id=?",$_POST['uid']));
					
				if(!empty($membershipdata) || $membershipdata==0)
				{

					$arr=array('success'=>'Data updated successfully','failure'=>'');
					echo  json_encode($arr);exit;
				}
				else {
					$arr=array('success'=>"",'failure'=>'failure');
					echo  json_encode($arr);exit;
				}
				 
		/*	}
			else{
			$data=array('success'=>'','failure'=>'Invalid Request Found.');
				  echo json_encode($data);exit;
				} */
			/*else{
					
				$insertdata=array("membership_type"=>$membershiptype,"investment_start"=>$investmentstart,"investment_end"=>$investmentend,"level_max"=>$levelmax,"binary_matching"=>$binarymatching,"binary_matching_multiple"=>$binarymatchingmultiple,"binary_capping"=>$binarycapping,"trimming"=>$Trimming,"mining_returns"=>$miningreturns);
				$insertmembershipdata=$membershiplistObj->insert($insertdata);
					
				$msg="Inserted Successfully";
				$this->view->msg=$msg;exit;

			}
				*/
				
		}
			
		/*echo "<pre>";
		 print_r($result);exit;*/
		}
			
				
			
			
		}
	
	
}
