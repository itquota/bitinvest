<?php
class ChangebannerController extends Zend_Controller_Action{
	
	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	
	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$managebanner_obj=new Gbc_Model_DbTable_Managebanner();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		
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
		
		$id=$_POST['memberid'];
		
		$status=$_POST['memberstatus'];
		$token=$_POST['token'];
		//	if($authUserNamespace->token==$token){ 
		
	if($status=='1')
		{
			$status=0;
		}
		else{

			$status=1;
		}
	
		 $result=$managebanner_obj->fetchRow($managebanner_obj->select()
		->setIntegrityCheck(false)
		->from(array('manage_banner'))
		->where("id=?",$id));
		  
	if(!empty($result) && sizeof($result)>0)
		{

			$upd_arr=array('status'=>$status);

			$upd_member=$managebanner_obj->update($upd_arr,$DB->quoteInto("id=?",$id));

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
