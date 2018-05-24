<?php
class ReviewController extends Zend_Controller_Action{

	public function init(){

	//	$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		
	//	if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");
	}

	public function indexAction()
	{
		
		$this->view->title="Gainbitcoin - Review";
		$this->_helper->layout()->setLayout("dashbord");
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();

		$username=$authUserNamespace->user;
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$userinfo = $common_obj->getUserInfo($username);
		$this->view->userinfo=$userinfo;
		
		$reviewObj= new Gbc_Model_DbTable_Reviews();
			$result=$reviewObj->fetchAll($reviewObj->select()
			->setIntegrityCheck(false)
			->from(array('reviews'))
			->where("status=?",1));	
			if(empty($result) || sizeof($result)<=0)
			{
				$result=array();
			}
			$this->view->reviews=$result;	
		
		$review_rating = $reviewObj->fetchRow($reviewObj->select()
		->setIntegrityCheck(false)
		->from(array('m'=>"reviews"),array('round(sum(rating),1) as count','count(*) as records'))
		->where("status= ?",1)
		);
		$this->view->review_count=$review_rating;	

		
		
		$review_count_status = $reviewObj->fetchAll($reviewObj->select()
		->setIntegrityCheck(false)
		->from(array('m'=>"reviews"),array('rating','count(*) as records'))
		->where("status= ?",1)										
		->group("rating")
		);
		$this->view->review_count_status=$review_count_status;			
	//	print_r($review_count_status->count);
	//	echo "<pre>";var_dump($review_count_status);
	//	exit;
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
			
		
			try {
				$adddata=array();
				if($_POST['rating']!="" && $_POST['email']!="" && $_POST['comment']!="" ) {
					$adddata=array("rating"=>$_POST['rating'],"email"=>$_POST['email'],"review"=>$_POST['comment'],"name"=>$_POST['name'],"reviewed_on"=>new Zend_Db_Expr('NOW()'));
					$insertdata=$reviewObj->insert($adddata); 
				if(!empty($insertdata))
				{
					$data=array('success'=>'Review submitted successfully','failure'=>'');
					echo json_encode($data);exit;
				}
				else{
					$data=array('success'=>'','failure'=>'Failed to add! Please try again');
					echo json_encode($data);exit;	
					 
				}
			}else{
			
					$data=array('success'=>'','failure'=>'Fields cannot be blank');
					echo json_encode($data);exit;
			}
			}
			catch(Exception $e)
			{
			echo $e->getMessage();exit;
				$this->view->msg=$e->getMessage();
			}
		}

	}
	
	public function savereviewAction()
	{
		
		$reviewObj= new Gbc_Model_DbTable_Reviews();

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
			
		
			try {
				$adddata=array();
				if($_POST['rating']!="" && $_POST['email']!="" && $_POST['comment']!="" ) {
					$adddata=array("rating"=>$_POST['rating'],"email"=>$_POST['email'],"review"=>$_POST['comment'],"name"=>$_POST['name'],"reviewed_on"=>new Zend_Db_Expr('NOW()'));
					$insertdata=$reviewObj->insert($adddata); 
				if(!empty($insertdata))
				{
					$data=array('success'=>'Review submitted successfully','failure'=>'');
					echo json_encode($data);exit;
				}
				else{
					$data=array('success'=>'','failure'=>'Failed to add! Please try again');
					echo json_encode($data);exit;	
					 
				}
			}else{
			
					$data=array('success'=>'','failure'=>'Fields cannot be blank');
					echo json_encode($data);exit;
			}
			}
			catch(Exception $e)
			{
			echo $e->getMessage();exit;
				$this->view->msg=$e->getMessage();
			}
		}

	}	

}
