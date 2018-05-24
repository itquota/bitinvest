<?php
class ChangekitstatusController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$kit_Obj = new Gbc_Model_DbTable_Kits();
		$antixss = new Gbc_Model_Custom_StringLimit();
		foreach($_POST as $key => $value)
		{
			//if($key!="user_name"){
				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						$data=array('success'=>'','failure'=>'Invalid Input.');
						echo json_encode($data);exit;

					}

				}
			//}
		}

		$username=$_POST['user_name'];

		$invoiceId=$_POST['invoiceid'];
		$kitnumber=$_POST['kit_number'];
		$kitstatus=$_POST['status'];
		$token=$_POST['token'];
			
	//	if($authUserNamespace->token==$token){

			try{
				$db = Zend_Db_Table::getDefaultAdapter();
				$db->beginTransaction();
				$this->_helper->layout()->setLayout("admindashbord");//dashboard


				$result=$kit_Obj->fetchRow($kit_Obj->select()
				->setIntegrityCheck(false)
				->from(array('k'=>'kits'))
				->where("username= ?",$username)
				->where("invoice_id= ?",$invoiceId)
				->where("kit_number= ?",$kitnumber)
				);
				$db->commit();

				$this->view->result=$result;


			}
			catch(Exception $e)
			{
				$db->rollBack();
				echo $e->getMessage();exit;
			}
	/*	}else{
			$data=array('success'=>'','failure'=>'Invalid Request Found.');
			echo json_encode($data);exit;
		}
*/

	}
	public function changekitstsAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$kit_Obj = new Gbc_Model_DbTable_Kits();
		$Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
		$antixss = new Gbc_Model_Custom_StringLimit();
		foreach($_POST as $key => $value)
		{ 
			
			//if($key!="user_name" && $key!="comment"){
				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						$data=array('success'=>'','failure'=>'Invalid Input.');
						echo json_encode($data);exit;

					}

				}
			//}
		}
			
		if(!empty($_POST['kitno']) && $_POST['kitno']!="")
		{
			
			$kitno=$_POST['kitno'];
			
		}
		else{
			$data=array('success'=>'','failure'=>'Please enter Kit Number');
						echo json_encode($data);exit;
			
		}
		if(!empty($_POST['usedby']) && $_POST['usedby']!="")
		{
			$usedby=$_POST['usedby'];
		}
		else{
			$data=array('success'=>'','failure'=>'Please enter Used By');
						echo json_encode($data);exit;
			
		}
		
		if(!empty($_POST['status']) && $_POST['status']!="")
		{
		$status=$_POST['status'];
			
		}
		else{
			$data=array('success'=>'','failure'=>'Please enter status');
						echo json_encode($data);exit;
			
		}
		$user=$_POST['user_name'];
		$comment=$_POST['comment'];
		$token=$_POST['token'];
		
		try{
		//	if($authUserNamespace->token==$token){


				$db = Zend_Db_Table::getDefaultAdapter();
				$db->beginTransaction();

				if(empty($_POST['kitno']) || empty($_POST['user_name']) || empty($_POST['status'])){
					$db->rollBack();
					echo 'false';
					exit();
				}
				else{

				/*	if($status=='Inactive')
					{
						$status='Active';
					}
					else{
						$status='Inactive';
					}*/

					$row=$kit_Obj->fetchRow($kit_Obj->select()
					->setIntegrityCheck(false)
					->from(array('k'=>'kits'))
					->where("kit_number= ?",$kitno)
					->where("username= ?",$user)
					);
					if(!empty($row) && sizeof($row)>0){
						$description = "";
						if($status != $row->status){
							$description .= "status has been changed from ".$row->status." to ".$status;
						}
					}
					$arr=array('status'=>$status,'comment'=>$comment);
					$upd_kit=$kit_Obj->update($arr,"kit_number='".$kitno."' AND username='".$user."'");

					if(!empty($upd_kit) || $upd_kit==0){

						if(!empty($description)){
							$saveUserLog = $Gbc_Model_Custom_func_obj->saveUserLog($user,"kits",$description);
						}
						///echo $saveUserLog;
						$db->commit();

						//echo 'true';exit();
						$data=array('success'=>'Data updated successfully','failure'=>'');
						echo json_encode($data);exit;
					} else {
						$db->rollBack();
						//echo 'Failed to change! Please try again.';
						$data=array('success'=>'','failure'=>'Failed to change! Please try again.');
						echo json_encode($data);exit;
					}
				}

/*		 }else{
		 	$data=array('success'=>'','failure'=>'Invalid Request Found.');
		 	echo json_encode($data);exit;
		 	 
		 } */
		}
		catch(Exception $e)
		{
			$db->rollBack();
			echo $e->getMessage();exit;
		}

	}

}
