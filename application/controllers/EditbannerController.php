<?php
class EditbannerController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="")$this->_redirect("/Login");
		//if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Manage Banner";
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

		$id=$_POST['id'];
		
		
			$managebanner_obj=new Gbc_Model_DbTable_Managebanner();

			$resultmanagebanner=$managebanner_obj->fetchRow($managebanner_obj->select()
			->setIntegrityCheck(false)
			->from(array('manage_banner'))
			->where("id=?",$id));

			$this->view->resultmanagebanner=$resultmanagebanner;
        	
	
	}
		public function updateimageAction()
		{
			try{
				
			
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$managebanner_obj=new Gbc_Model_DbTable_Managebanner();
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
			
		
			if($this->_request->isPost())
			{
					
				$antixss = new Gbc_Model_Custom_StringLimit();
				foreach($_POST as $key => $value)
				{
					//if($key!="description"){
					if(isset($value) && $value != ""){
						$antixss->setEncoding($value, "UTF-8");
						if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
	
							$data=array('success'=>'','failure'=>'Invalid Input.');
							echo json_encode($data);exit;
	
						}
	
					}
			//	}
				}
				
			
				if(!empty($_POST['id']))
				{
						if($_FILES['imgs']['name']!=""){
							
						if($_FILES['imgs']['type']=="image/jpeg" || $_FILES['imgs']['type']=="image/gif" || $_FILES['imgs']['type']=="image/png" || $_FILES['imgs']['type']=="image/jpg" ){
							
							//$size=$_FILES['imgs']['size']/1024;
							$size=round(($_FILES['imgs']['size']/1024), 2);
							if($size > 2048.00){
								
								$data=array('success'=>'','failure'=>'Maximum file size exceeds');
									echo json_encode($data);exit;
		
							}
							else {
							$imgData=(file_get_contents($_FILES['imgs']['tmp_name']));
								
							}
						
						}
						else {
							$data=array('success'=>'','failure'=>'Photo only allows file types of GIF, PNG, JPG and JPEG.');
							echo json_encode($data);exit;
	
						}
						
						}
						
						$description=trim($_POST['description']);
							
						
						if(!empty($description) && $description!="")
						{
							$description=$_POST['description'];
							
						}
						else 
						{
							$data=array('success'=>'','failure'=>'Please enter Description');
							echo json_encode($data);exit;
	
						}

					//	$imageProperties = getimageSize($_FILES['imgs']['tmp_name']);

					$token=$_POST['inputtoken'];
					$date = new Zend_Db_Expr('NOW()');
			//		if($authUserNamespace->token==$token){
					if(isset($imgData) && $imgData !="") {
							
						$upbannerdata=array("description"=>$description,"banner_image"=>$imgData,"date"=>$date);
						  $new ='images/banner_share/'.$_POST['id'].'.jpg';
					       $mov=move_uploaded_file($_FILES['imgs']['tmp_name'], "$new");
					}else{
						$upbannerdata=array("description"=>$description,"date"=>$date);
					}

					//$updatemanagebanner=$managebanner_obj->update($upbannerdata,"id='".$_POST['id']."'");
					$updatemanagebanner=$managebanner_obj->update($upbannerdata,$db->quoteInto("id=?",$_POST['id']));
					
					$db->commit();

					$arr=array('success'=>'Data updated successfully','failure'=>'');
					echo  json_encode($arr);exit;

		/*		}
				else{
					$data=array('success'=>'','failure'=>'Invalid Request Found.');
				     echo json_encode($data);exit;
				}
*/

			}
			$db->commit();

		}
		
			}
			catch(Exception $e)
			{
				$db->rollBack();
			$arr=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($arr);exit;
			}
			
			
		}

}
