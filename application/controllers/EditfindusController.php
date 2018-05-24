<?php
class EditfindusController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");
	}
	public function indexAction()
	{
		try {
			$this->view->title="Gainbitcoin - Find Us";
		//	$db = Zend_Db_Table::getDefaultAdapter();
		//	$db->beginTransaction();
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			$user_id=$authUserNamespace->user_id;
			$data1=$misc_obj->GetAccessRightByUserId('20',$user_id);
			if(!empty($data1->edit)&&($data1->edit==1) || $authUserNamespace->user=='admin')
				{

				}
			else 
			{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
			}
			$this->_helper->layout()->setLayout("admindashbord");//dashboard

			$findusObj= new Gbc_Model_DbTable_Findus();
			$cityObj= new Gbc_Model_DbTable_City();
			
			
			
			$id=$_POST['findid'];

			$result=$findusObj->fetchRow($findusObj->select()
			->setIntegrityCheck(false)
			->from(array('find_us'))
			->where("id= ?",trim($id)));

			$this->view->result=$result;
			

			$cityresult=$cityObj->fetchAll($cityObj->select()
			->setIntegrityCheck(false)
			->from(array('city'))
			->order("city_name asc")
			);
		
//echo "<pre>";
//print_r($cityresult);exit;

		//	$db->commit();
			$this->view->cityresult=$cityresult;
			
			

		}
		catch(Exception $e)
		{
	//	$db->rollBack();
			$arr=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($arr);exit;
		}




	}
		public function editdataAction()
		{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$findusObj= new Gbc_Model_DbTable_Findus();
			$cityObj= new Gbc_Model_DbTable_City();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();

			
			
			
			if($this->_request->isPost())
			{
				
				try {
					if(!empty($_POST['id']))
					{
						
					$antixss = new Gbc_Model_Custom_StringLimit();
						
						
						foreach($_POST as $key => $value)
							{
								
							//if($key!='contact_name' && $key!='address'){
								if(isset($value) && $value != ""){
								
								if($antixss->setFilter($value, "black", "string") == "invalidInput"){
									
									$data=array('success'=>'','failure'=>'Invalid Input');
									echo json_encode($data);exit;
			
									}
			
								}
							//}			
						}	
		
						$update=date('Y-m-d h:i:s');
						if(!empty($_POST['contact_name']) && $_POST['contact_name']!=""){
						$contactname=$_POST['contact_name'];
						}
						else{
						$data=array('success'=>'','failure'=>'Please enter Contact Name.');
									echo json_encode($data);exit;
						}
						
						$address=trim($_POST['address']);
						
						if(!empty($address) && $address!=""){
							$address=$_POST['address'];
						}
						else{
							$data=array('success'=>'','failure'=>'Please enter Address');
									echo json_encode($data);exit;
						}
						if(!empty($_POST['mobile']) && $_POST['mobile']!="")
						{
							$mobileno=$_POST['mobile'];
						}
						else{
							$data=array('success'=>'','failure'=>'Please enter Mobile Number. ');
									echo json_encode($data);exit;
						}
						
						if(preg_match('/^[0-9]{10}+$/',$_POST['mobile'])) {
						
						}
						else{
							$data=array('success'=>'','failure'=>'Please enter 10 digit Mobile No ');
									echo json_encode($data);exit;
						}
						//if(!empty($_POST['city1']) && $_POST['city1']!="")
					//{
						//}
					//	else{
							
						//	$data=array('success'=>'','failure'=>'Please select City. ');
						//			echo json_encode($data);exit;
						//}
						
					//	if(!empty($_POST['status']) && $_POST['status']!="")
						//{
				//	}
					//	else{
					//	$data=array('success'=>'','failure'=>'Please select status ');
						//			echo json_encode($data);exit;
						//}
						
						
						
						//if($contactname!="" && $address!="" && $mobileno!="" && $city!="" && $status!=""){
							$city=$_POST['city1'];
							$status=$_POST['status'];
							
						
							$tokn=$_POST['token'];
							
				//		if($authUserNamespace->token==$tokn){

						
						
						$findupdatedata=array("contact_name"=>$contactname,"contact_address"=>$address,"mobile"=>$mobileno,"city"=>$city,"updated_on"=>$update,'status'=>$status);
                      //   print_r($findupdatedata);
						//$where=array("id"=>$_POST['id']);
                        
						$findupdate=$findusObj->update($findupdatedata,$DB->quoteInto("id=?",$_POST['id']));
                       //    $db->commit();
						
						if(!empty($findupdate)){
							
							
							$data=array('success'=>'Updated Successfully','failure'=>'');
							echo json_encode($data);exit;
							

						} else {
							$errormsg='Not Updated Successfully';
						$data=array('success'=>'','failure'=>$errormsg);
							echo json_encode($data);exit;

						}
						//$this->view->msg=$msg;exit;
			/*		}
					else {
						//$data=array('success'=>'','failure'=>'Invalid Request Found.');
 						//echo json_encode($data);exit;
 							$errormsg='Invalid Request Found';
							$data=array('success'=>'','failure'=>$errormsg);
							echo json_encode($data);exit;
					}
			*/		
					
					//}else{
							$errormsg='All fields are required';
							$data=array('success'=>'','failure'=>$errormsg);
							echo json_encode($data);exit;
					
					//}
					
					
					}
					else
					{
						$findinsertdata=array('contact_name'=>$contactname,'contact_address'=>$address,'mobile'=>$mobileno,'city'=>$city);
						$findinsert=$findusObj->insert($findinsertdata);

						if(!empty($findinsert)){
							
							$data=array('success'=>'Added Successfully','failure'=>'');
							echo json_encode($data);exit;
							

						} else {
							$errormsg='Not Added!';
							$data=array('success'=>'','failure'=>$errormsg);
							echo json_encode($data);exit;
							

						}


						//$this->view->msg=$msg;
					}
				}
				catch (Exception $e)
				{
				//	$db->commit();
					echo $e->getMessage();exit;
				}
			}
			
			
		}
	
	
	
}





