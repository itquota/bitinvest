<?php
class SpecialofferController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Special Offers";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$checkAdminAuthentication=$misc_obj->checkAdminAuthentication();
		$specialofferObj = new Gbc_Model_DbTable_Specialoffer();
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

		try{

			$this->_helper->layout()->setLayout("admindashbord");//dashboard
			
			$result=array();

			$result=$specialofferObj->fetchAll($specialofferObj->select()
			->setIntegrityCheck(false)
			->from(array('special_offers'))
			->order("id DESC"));

			/*	$result1=sizeof($result);
			 if(isset($result1) && $result1>0)
			 {
			 	
				$id=$result->id+1;
					

				}
				else
				{
				$id='1';
				}
				*/



			$this->view->result =$result;

			if($this->_request->isPost('specialoffer'))
			{
				$token=$_POST['token'];
				//echo $token;exit;
		//		if($authUserNamespace->token==$token){
				
					if(empty($_POST['prize']) || empty($_POST['pairs']))
				{

					$msg ="Please enter all required fields";
					$this->view->msg=$msg;

				}
				else
				{
					$insertoffer=array();

					$offerprize=$_POST['prize'];
					$offerpair=$_POST['pairs'];

					$insertresult=$specialofferObj->fetchRow($specialofferObj->select()
					->setIntegrityCheck(false)
					->from(array('special_offers'),array('id'))
					->order("id DESC"));

					$result1=sizeof($insertresult);
					if($result1 && $result1>0)
					{
						$id=$insertresult->id+1;
					}
					else
					{

						$id='1';
					}


					$data=array('prize'=>$offerprize,'pairs'=>$offerpair,"id"=>$id);
					$specialofferObj->insert($data);

					if(!empty($specialofferObj ))
					{
						$msg="Offer added successfully.";
						$this->view->msg=$msg;
					}


				}

				$this->_redirect('/Specialoffer');
	/*		}
			else{
				$data=array('success'=>'','failure'=>'Invalid Request Found.');
				 echo json_encode($data);exit;
				} */
			}



		}
			
		//echo "<pre>";
		///print_r($result);exit;

		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}


	}
	public function offerAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$specialoff_obj=new Gbc_Model_DbTable_Specialoffer();
		$common_obj = new Gbc_Model_Custom_CommonFunc();

		$resultaddoffer=$specialoff_obj->fetchRow($specialoff_obj->select()
		->setIntegrityCheck(false)
		->from(array('special_offers'))
		);

		/*$result1=sizeof($resultaddoffer);
		if(isset($result1) && $result1>0)
		{
			$id=$resultaddoffer->id+1;
		}
		else
		{
			$id=1;
		}
		*/
		
		if($this->_request->isPost())
		{
			$antixss = new Gbc_Model_Custom_StringLimit();
			foreach($_POST as $key => $value)
			{
				//if($key!='price')
				//{
				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						$data=array('success'=>'','failure'=>'Invalid Input.');
						echo json_encode($data);exit;

					}

					}
				
				//}
			}
			
			
			$date = new Zend_Db_Expr('NOW()');
			if(!empty($_POST['price']) && $_POST['price']!="")
			{
			$offerprice = $common_obj->cleanQueryParameter($_POST['price']);
			}
			else{
				$arr=array('success'=>'','failure'=>'Please enter Prize.');
				echo  json_encode($arr);exit;
			}
			
			if(!empty($_POST['pairs']) && $_POST['pairs']!="")
			{
			$offerpairs = $common_obj->cleanQueryParameter($_POST['pairs']);
			}
			else 
			{
				$arr=array('success'=>'','failure'=>'Please enter Pairs');
				echo  json_encode($arr);exit;
			
			}
			if(!empty($_POST['direct_pairs']) && $_POST['direct_pairs']!="")
			{
			$direct_pairs = $common_obj->cleanQueryParameter($_POST['direct_pairs']);
			}
			else
			{
				$arr=array('success'=>'','failure'=>'Please enter Direct Pairs');
				echo  json_encode($arr);exit;
			}
	
			$token=$_POST['token'];
			if($authUserNamespace->token==$token){
			
		$insertofferdata=array("prize"=>$offerprice,"pairs"=>$offerpairs,"direct_pairs"=>$direct_pairs,'created'=>$date,"updated_on"=>$date);
			//print_r($insertofferdata);exit;
		$offer_data=$specialoff_obj->insert($insertofferdata);
			
			if(!empty($offer_data))
			{
				$arr=array('success'=>'Data inserted successfully','failure'=>'');
				echo  json_encode($arr);exit;
			}
			else {
				$arr=array('success'=>'','failure'=>'failure');
				echo  json_encode($arr);exit;
			}
			
		}
		else {
			$arr=array('success'=>'','failure'=>'Invalid request found');
				echo  json_encode($arr);exit;
			}
		
		}
	}


}




