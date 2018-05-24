<?php
class AddgalleryController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="")$this->_redirect("/Login");
		//if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Manage Gallery";
		$this->_helper->layout()->setLayout("admindashbord");
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
		$managegallery_obj=new Gbc_Model_DbTable_Gallery();
		$gallery_details_obj=new Gbc_Model_DbTable_GalleryDetails();

		if($this->_request->isPost()){

			try {
				$date = new Zend_Db_Expr('NOW()');

				if(!empty($_FILES['imgs']['name'])!="")
				{
					if($_FILES['imgs']['type']=="image/jpeg" || $_FILES['imgs']['type']=="image/gif" || $_FILES['imgs']['type']=="image/png" || $_FILES['imgs']['type']=="image/jpg" )
					{

						$size=round(($_FILES['imgs']['size']/1024), 2);

						if($size > 2048.00)
						{
							$data=array('success'=>'','failure'=>'Maximum file size exceeds');
										echo json_encode($data);exit;
						}
						else{
							$imgData=(file_get_contents($_FILES['imgs']['tmp_name']));
						}
					}
					else{
						$msgErr = 'Photo only allows file types of GIF, PNG, JPG and JPEG.';
					}
				}
				else{
					 $msgErr="Please upload image";
				}
				$description=trim($_POST['title']);
				
				if(!empty($description) && $description!=""){
					
				//	$title = str_replace(" ","_",$_POST['title']);
					$description=$_POST['title'];
					
				}
				else {				
					$msgErr='Please enter title';
					
				}
			
				if(isset($imgData) && $imgData !="") {
					$insertbannerdata=array("title"=>$description,"title_image"=>$imgData,"video_links"=>$_POST['links'],"date"=>$date);
                    $bannerdata=$managegallery_obj->insert($insertbannerdata);
					$new ='images/banner_share/'.$bannerdata.'.jpg';
					$mov=move_uploaded_file($_FILES['imgs']['tmp_name'], "$new");
				}				
			//	echo $bannerdata; exit;
				if(!empty($bannerdata) && $bannerdata!="")
				{
					
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

								$insertgallerydata=array("title"=>$description,"gallery_id"=>$bannerdata,"subimage"=>$imgDataIn,"date"=>$date);
								$gallerydata=$gallery_details_obj->insert($insertgallerydata);
								$new ='images/banner_share/'.$bannerdata.'.jpg';
								$mov=move_uploaded_file($_FILES['imgs']['tmp_name'], "$new");
							}
						}
						else{
							 $msgErr="Please upload image";
						}		

					}					
					$msg = "Data inserted successfully";
					$authUserNamespace->msg=$msg;
					$this->_redirect("/Managegallery");
					
					
				}
				else{
					$authUserNamespace->msgErr=$msgErr;
					$this->_redirect("/Addgallery");
					
					
				}

			}

			catch(Exception $e)
			{
				echo $e->getMessage();exit;
			}
				
		}

	}

}
