<?php
class EditgalleryController extends Zend_Controller_Action{

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

			$id=$_REQUEST['id'];
		
			$managegallery_obj=new Gbc_Model_DbTable_Gallery();
			$gallery_details_obj=new Gbc_Model_DbTable_GalleryDetails();

			$resultmanagegallery=$managegallery_obj->fetchRow($managegallery_obj->select()
			->setIntegrityCheck(false)
			->from(array('gallery'))
			->where("id=?",$id));
	
			$resultmanagegallery1=$gallery_details_obj->fetchAll($gallery_details_obj->select()
			->setIntegrityCheck(false)
			->from(array('gallery_details'))
			->where("gallery_id=?",$id));
			
			$this->view->resultmanagebanner=$resultmanagegallery;
			$this->view->resultmanagebanner1=$resultmanagegallery1;
        	
	
	}
	
	public function updateimageAction()
		{	
			
		try{
				
			
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$managegallery_obj=new Gbc_Model_DbTable_Gallery();
			$gallery_details_obj=new Gbc_Model_DbTable_GalleryDetails();

			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);			
		
			if($this->_request->isPost())
			{
					
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
				
			
				if(!empty($_POST['id']))
				{
						$date = new Zend_Db_Expr('NOW()');

						if($_FILES['imgs']['name']!=""){
							
						if($_FILES['imgs']['type']=="image/jpeg" || $_FILES['imgs']['type']=="image/gif" || $_FILES['imgs']['type']=="image/png" || $_FILES['imgs']['type']=="image/jpg" ){						
								$size=round(($_FILES['imgs']['size']/1024), 2);
								if($size > 2048.00){

									//$data=array('success'=>'','failure'=>'Maximum file size exceeds');
									//echo json_encode($data);exit;
								$msgErr = "Maximum file size exceeds";
								$authUserNamespace->msgErr = $msgErr;
								$this->_redirect("/Editgallery?id=".$_POST['id']);										

								}
								else {
								$imgData=(file_get_contents($_FILES['imgs']['tmp_name']));

								}

							}
							else {
							//	$data=array('success'=>'','failure'=>'Photo only allows file types of GIF, PNG, JPG and JPEG.');
							//	echo json_encode($data);exit;
							$msgErr = "Photo only allows file types of GIF, PNG, JPG and JPEG.";
							$authUserNamespace->msgErr = $msgErr;
							$this->_redirect("/Editgallery?id=".$_POST['id']);								

							}
						
						}
						$description=trim($_POST['title']);
						$videos = $_POST['links'];
							
						if(!empty($description) && $description!="")
						{
							$description=$_POST['title'];
							
						}
						else 
						{
							$msgErr = "Title required";
							$authUserNamespace->msgErr = $msgErr;
							$this->_redirect("/Editgallery?id=".$_POST['id']);
	
						}

						for($i=0;$i<sizeof($_FILES['files']['name']);$i++){
							if(!empty($_FILES['files']['name'][$i])!="")
							{
								if($_FILES['files']['type'][$i]=="image/jpeg" || $_FILES['files']['type'][$i]=="image/gif" || $_FILES['files']['type'][$i]=="image/png" || $_FILES['files']['type'][$i]=="image/jpg" )
								{
									$size=round(($_FILES['files']['size'][$i]/1024), 2);
									if($size > 2048.00)
									{
										$msgErr = 'Maximum file size exceeds';
									}
									else{
										$imgDataIn=(file_get_contents($_FILES['files']['tmp_name'][$i]));
									}
								}
								else{
										$msgErr = 'Photo only allows file types of GIF, PNG, JPG and JPEG.';							
								}
								if(isset($imgDataIn) && $imgDataIn !="") {

									$insertgallerydata=array("title"=>$description,"gallery_id"=>$_POST['id'],"subimage"=>$imgDataIn,"date"=>$date);
									$gallerydata=$gallery_details_obj->insert($insertgallerydata);
									$new ='images/banner_share/'.$bannerdata.'.jpg';
									$mov=move_uploaded_file($_FILES['imgs']['tmp_name'], "$new");
								}
							}
							else{
								 $msgErr="Please upload image";
							}		

						}					
					
						if(isset($imgData) && $imgData !="") {
							$upbannerdata=array("title"=>$description,"title_image"=>$imgData,"video_links"=>$videos,"updated_on"=>$date);
						}else{
							$upbannerdata=array("title"=>$description,"video_links"=>$videos,"updated_on"=>$date);
						
						}
					//	$upbannerdata=array("title"=>$description,"title_image"=>$imgData,"video_links"=>$videos,"updated_on"=>$date);
						$new ='images/banner_share/'.$_POST['id'].'.jpg';
						$mov=move_uploaded_file($_FILES['imgs']['tmp_name'], "$new");

						$updatemanagebanner=$managegallery_obj->update($upbannerdata,$db->quoteInto("id=?",$_POST['id']));

						$db->commit();

					//	$arr=array('success'=>'Data updated successfully','failure'=>'');
					//	echo  json_encode($arr);exit;
						$msg = "Data updated successfully";
						$authUserNamespace->msg = $msg;
						$this->_redirect("/Managegallery");

					//	header("location:javascript://history.go(-1)");

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
	
	public function deleteimageAction(){
		try
		{
			
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$gallery_details_obj=new Gbc_Model_DbTable_GalleryDetails();
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();			

			$id=$_POST['id'];


			 $result=$gallery_details_obj->fetchRow($gallery_details_obj->select()
			->setIntegrityCheck(false)
			->from(array('gallery_details'))
			->where("id=?",$id));	

			if(!empty($result) && sizeof($result)>0)
			{
				$delete=$db->delete('gallery_details', array('id = ?' => $id));
				//$upd_arr=array('status'=>$status);

				//$upd_member=$managegallery_obj->update($upd_arr,$DB->quoteInto("id=?",$id));

				if(!empty($delete))
				{

					$data=array('success'=>$status,'failure'=>'');
					echo json_encode($data);exit;
				}
				else
				{
					$data=array('success'=>'','failure'=>'');
					echo  json_encode($data);exit;
				}


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
