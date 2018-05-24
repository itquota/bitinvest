<?php

class EditofferController extends Zend_Controller_Action{

	public function init(){


	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Special Offers";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$specialoffer_Obj=  new Gbc_Model_DbTable_Specialoffer();
		$common_obj = new Gbc_Model_Custom_CommonFunc();

		$this->_helper->layout()->setLayout("admindashbord");


		//$id=$this->_request->getParam("id");
		$offer_id=$_POST['offerid'];
		
		
		$result=$specialoffer_Obj->fetchRow($specialoffer_Obj->select()
		->setIntegrityCheck(false)
		->from(array('special_offers'))
		->where("id= ?",$offer_id)
		);
		
		
		$this->view->result=$result;

		if($this->_request->isXmlHttpRequest())
		{
			$prize = $common_obj->cleanQueryParameter($_POST['prize']);
			$pairs = $common_obj->cleanQueryParameter($_POST['pairs']);
			$direct_pairs = $common_obj->cleanQueryParameter($_POST['direct_pairs']);
	

				
			$value=array("prize"=>$prize,"pairs"=>$pairs,"direct_pairs"=>$direct_pairs,"updated_on"=>new Zend_Db_Expr('NOW()'));

			$updatekitdata=$specialoffer_Obj->update($value,"id='".$_POST['user_id']."'");
			
			if(!empty($updatekitdata))
				{

					$arr=array('success'=>'success','failure'=>'');
					echo  json_encode($arr);exit;
				}
				else {
					$arr=array('success'=>"",'failure'=>'failure');
					echo  json_encode($arr);exit;
				}


		}


	}
	public function offereditAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$specialoffer_Obj=  new Gbc_Model_DbTable_Specialoffer();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		
				if($this->_request->isPost()){
			
			$antixss = new Gbc_Model_Custom_StringLimit();
			foreach($_POST as $key => $value)
			{		
					//if($key!="prize"){
				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						$data=array('success'=>'','failure'=>'Invalid Input.');
						echo json_encode($data);exit;

					}

				}
			//}
		}
					
			
		
				if(!empty($_POST['prize']) && $_POST['prize']!="")
				{
					$prize=$common_obj->cleanQueryParameter($_POST['prize']);
				}
				else {
						$data=array('success'=>'','failure'=>'Please enter Prize.');
						echo json_encode($data);exit;
				}
				if(!empty($_POST['pairs']) && $_POST['pairs']!="")
				{
				$pairs=$common_obj->cleanQueryParameter($_POST['pairs']);
				}
				else{
						$data=array('success'=>'','failure'=>'Please enter Pairs.');
						echo json_encode($data);exit;
				}
				
				if(!empty($_POST['direct_pairs']) && $_POST['direct_pairs']!="")
				{
					$direct_pairs=$common_obj->cleanQueryParameter($_POST['direct_pairs']); 
					
				}
				else{
					$data=array('success'=>'','failure'=>'Please enter Direct Pairs.');
						echo json_encode($data);exit;
				}

			$tokn=$_POST['tokenid'];
			
	//		if($authUserNamespace->token==$tokn){
			
			$value=array("prize"=>$prize,"pairs"=>$pairs,"direct_pairs"=>$direct_pairs,"updated_on"=>new Zend_Db_Expr('NOW()'));

			//$updatekitdata=$specialoffer_Obj->update($value,"id='".$_POST['user_id']."'");
			$updatekitdata=$specialoffer_Obj->update($value,$DB->quoteInto("id=?",$_POST['user_id']));
			
	
			
			if(!empty($updatekitdata))
				{

					$arr=array('success'=>'Data updated successfully','failure'=>'');
					echo  json_encode($arr);exit;
				}
				else {
					$arr=array('success'=>"",'failure'=>'failure');
					echo  json_encode($arr);exit;
				}
		
/*	}
	else {
		
		$data=array('success'=>'','failure'=>'Invalid Request Found.');
				  echo json_encode($data);exit;
		
	} 
*/
	}
		
	}
}

