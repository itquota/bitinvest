<?php
class SetpermissionController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");
	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Subadmin";

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$loggedIn="";
		if(!empty($authUserNamespace->user) &&  $authUserNamespace->user=='admin')
		{
			$loggedIn==true;
		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Login");
		}
		$subadmin_Obj = new Gbc_Model_DbTable_Subadminuser();
		$Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();

		try{

			$this->_helper->layout()->setLayout("admindashbord");//dashboard

			$result=array();

			$user_id=$Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST['setpermission_id']);
			
			$row = $subadmin_Obj->fetchRow($subadmin_Obj->select()
			->where("id= ?",$user_id));
			
			
			$this->view->firstname=$row->first_name;
			 
			$Modules=$Gbc_Model_Custom_func_obj->getModules();



	 	$this->view->result=$Modules;

		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
			
			
			
			



	}

	public function changeaccessrightAction()
	{
		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$db = Zend_Db_Table::getDefaultAdapter();
			
			$token=$_POST['token'];
			
	//		if($authUserNamespace->token==$token){
					

				if($_POST['mode']=='add' || $_POST['mode']=='edit' || $_POST['mode']=='view' || $_POST['mode']=='delete')
				{
					$Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
					$access_obj=new Gbc_Model_DbTable_Accessright();
					$get_access=$Gbc_Model_Custom_func_obj->GetAccessRightByUserId($_POST['module_id'],$_POST['user_type_id']);
					$mode=$_POST['change_val'];
					$user_id=$_POST['user_type_id'];
					
					$module_id=$_POST['module_id'];
				
					
					$orig_mode=$_POST['mode'];
					if(!empty($get_access) && sizeof($get_access)>0)
					{
						try{
							$query=array($orig_mode=>$mode);

								
							$where=array('user_id'=>$user_id);

							//$result=$access_obj->update($query,"user_id='".$user_id."' AND module_id='".$module_id."'");
							$result=$access_obj->update($query,$db->quoteInto("user_id=?",$user_id) . ' AND ' . $db->quoteInto("module_id=?",$module_id));
							
							
							//$db->quoteInto("sponsor_id=?",trim($_REQUEST["ref_sponser_id"])) . ' OR ' . $db->quoteInto("username=?",trim($_REQUEST["ref_sponser_id"]))
						}
						catch(Exception $e)
						{
							echo $e->getMessage();exit;
						}

					}
					else
					{
						$query=array('user_id'=>$user_id,'module_id'=>$module_id,$orig_mode=>$mode);
						$result = $access_obj->insert($query);
					}
					//$result='';
					/*if(!empty($result ))
					 {
					 echo "Changed successfully";exit;
					 }
					 else
					 {
					 echo "Error while changing! Please try again";exit;
					 }*/
					if(!empty($result))
					{
						$data=array('success'=>'success','failure'=>'');
						echo json_encode($data);exit;
					}
					else
					{
						$data=array('success'=>'','failure'=>'Error while changing! Please try again');
						echo json_encode($data);exit;
					}
						
				}
	/*		}else{
				$data=array('success'=>'','failure'=>'failure');
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
