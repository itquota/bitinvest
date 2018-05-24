<?php
class AddbannerController extends Zend_Controller_Action{

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
		$managebanner_obj=new Gbc_Model_DbTable_Managebanner();
		
	
		try{
			/*
			 $resultmanagebanner=$managebanner_obj->fetchRow($managebanner_obj->select()
			 ->setIntegrityCheck(false)
			 ->from(array('manage_banner'),array('id','banner_image'))
			 ->where("id='44'")
			 );
			 */ //header("Content-type: image/jpeg");
			// echo $resultmanagebanner->banner_image;exit;

		}

		catch(Exception $e){

			echo $e->getMessage();exit;
		}



		if($this->_request->isPost()){
			
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
			//}
		}
			
			
			
			
			
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
					$data=array('success'=>'','failure'=>'Photo only allows file types of GIF, PNG, JPG and JPEG.');
							echo json_encode($data);exit;
	
				}
				}
				else{
						$msg="Please upload image";
					$data=array('success'=>'','failure'=>$msg);
				     echo json_encode($data);exit;
				}
				$description=trim($_POST['description']);
				
				if(!empty($description) && $description!=""){
					
				
				$description=$_POST['description'];
					
				}
				else {
					//echo "inside";exit;
				
				$msg='Please enter Description';
				$data=array('success'=>'','failure'=>$msg);
				     echo json_encode($data);exit;
					
				}
			 	$imageProperties = getimageSize($_FILES['imgs']['tmp_name']);
				
				$token=$_POST['inputtoken'];
				//if($authUserNamespace->token==$token){
				if(isset($imgData) && $imgData !="") {
					$insertbannerdata=array("description"=>$description,"banner_image"=>$imgData,"date"=>$date);
                    $bannerdata=$managebanner_obj->insert($insertbannerdata);
					$new ='images/banner_share/'.$bannerdata.'.jpg';
					     $mov=move_uploaded_file($_FILES['imgs']['tmp_name'], "$new");
				}else{
					$insertbannerdata=array("description"=>$description,"date"=>$date);
					$bannerdata=$managebanner_obj->insert($insertbannerdata);
				}
				
				
				if(!empty($bannerdata) && $bannerdata!="")
				{
						$arr=array('success'=>'Data inserted successfully','failure'=>'');
							echo  json_encode($arr);exit;
				}
				else{
					$arr=array('success'=>'','failure'=>'failure');
							echo  json_encode($arr);exit;
				}
			/*	
					
				}
				else{
					$data=array('success'=>'','failure'=>'Invalid Request Found.');
				     echo json_encode($data);exit;
				}*/
			}




			// $imgData =addslashes (file_get_contents($_FILES['userImage']['tmp_name']));
			//$imageProperties = getimageSize($_FILES['userImage']['tmp_name']);




			catch(Exception $e)
			{
				echo $e->getMessage();exit;
			}
				
		



		}

	}

}
