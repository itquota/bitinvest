<?php
class OrderhistoryController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$this->_helper->layout()->setLayout("dashbord");//dashboard
		$username=$authUserNamespace->user;
			
		/*kits detail */
		$kits_obj=new Gbc_Model_DbTable_Kits();
		$kit_invoices_obj=new Gbc_Model_DbTable_Kitinvoices();
			

		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$url= BASE."/Kitsdetailapi?username=".$username;

		$result2=$common_obj->call_curl($url);
		$result=(array)json_decode($result2,true);


		//$common_obj->cleanQueryParameter(($_POST['username']));

		$this->view->result=$result;

		//echo "<pre>";
		//print_r($result);exit;

		$url= BASE."/Usercontract?username=".$username;
		$result_contract=$common_obj->call_curl($url);
		$result1=(array)json_decode($result_contract,true);



		$new_arr=array();

		//$result14=sizeof($result1['data']);


		for($k=0;$k<sizeof($result1['data']);$k++)
		{

			array_push($new_arr,$result1['data'][$k]['use_kit_number']);

		}

		//print_r($new_arr);exit;
		$this->view->new_arr=$new_arr;
		$this->view->title="Gainbitcoin - Order History";
		/*echo "<pre>";
		 print_r($new_arr)exit;*/
			
	}
	public function sharekitAction()
	{
		try {
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
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
			$token=$common_obj->cleanQueryParameter(($_POST['token']));
	//		if(!empty($authUserNamespace->token) && $authUserNamespace->token==$token){
				//$kitnum=trim($_POST['kitnum']);
				$kitnum=$common_obj->cleanQueryParameter(($_POST['kitnum']));
				//$sharewith=trim($_POST['sharewith']);
				$sharewith=$common_obj->cleanQueryParameter(($_POST['sharewith']));
				//$kit_pin=trim($_POST['kit_pin']);
				$kit_pin=$common_obj->cleanQueryParameter(($_POST['kit_pin']));
				$username=$authUserNamespace->user;
				if(empty($kitnum))
				{
					$msg="Kit Number should not be empty.";
				}
				else if(empty($sharewith))
				{
					$msg="Username should not be blank.";
				}
				$this->view->msg=$msg;
				$kits_obj=new Gbc_Model_DbTable_Kits();
				$common_obj = new Gbc_Model_Custom_CommonFunc();

				/*			$kit_result=$kits_obj->fetchRow($kits_obj->select()
				 ->setIntegrityCheck(false)
				 ->from(array('k'=>"kits"),array('k.id','k.username','k.kit_number','k.created_on','k.status','k.invoice_id','k.kit_price','k.kit_used_by','k.kit_used_date','k.kit_type'))
				 ->joinInner(array('k1'=>'kit_invoices'),"k.invoice_id = k1.invoice_id",array('k1.kits_qty'))
				 ->where("k.username= ?",$username)
				 ->where("k.kit_number= ?",$kitnum)
				 ->order("k.invoice_id DESC"));*/

				/*$kit_result = $kits_obj->fetchRow($kits_obj->select()
				->setIntegrityCheck(false)
				->from(array('k' =>'kits'),array('kit_number'))
				->where("kit_number='".$kitnum."'"));*/

				$kit_result = $kits_obj->fetchRow($kits_obj->select()
				->setIntegrityCheck(false)
				->from(array('k' =>'kits'),array('kit_number'))
				->where("kit_number=?",$kitnum));


				if(empty($kit_result) || sizeof($kit_result)<=0)
				{
					$arr=array('success'=>'','failure'=>'Invalid Kit Number');
					echo json_encode($arr);exit;
				}
				$userinfo=$common_obj->getUserInfo($sharewith);
				if(!empty($userinfo) && sizeof($userinfo)>0)
				{
					$upd_kit_data=array('kit_share_status'=>'1','kit_pin'=>$kit_pin,'kit_shared_with'=>$sharewith);
					//$kit_upd_qry=$kits_obj->update($upd_kit_data,"username='$username' and kit_number='$kitnum'");
					$kit_upd_qry=$kits_obj->update($upd_kit_data,($DB->quoteInto("username=?",$username). ' AND '. $DB->quoteInto("kit_number=?",$kitnum)));
					
					//->where($db->quoteInto("binaryUser is NOT NULL") . ' AND ' . $db->quoteInto("sponsor_id=?",$ref_id))
					

				}
				else {
					$arr=array('success'=>'','failure'=>'Invalid User to share the kit.');
					echo json_encode($arr);exit;
				}
				if(!empty($userinfo->comm_email) && $userinfo->comm_email!='')
				{
					$email = "<div>Below kit has been shared with : ".$sharewith.". by ".$username." <br>Kit Number = ".$kitnum."</div>";
					//$common_obj->sendMail($userinfo->comm_email, "admin@gainbitco.in", "Buy Now Clicked", $email);
					$to = $userinfo->comm_email;
					$from = 'admin@gainbitco.in';
					$replyTo = 'thegainbitcoinhelp@gmail.com';
					$subject = 'Kit Shared';
					$message = $email;
					$htmlMessage = $email;
					$sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
				}
				else{
					$email = "<div>Below kit has been shared with : ".$sharewith.". by ".$username." <br>Kit Number = ".$kitnum."</div>";
					//$common_obj->sendMail($userinfo->email_address, "admin@gainbitco.in", "Buy Now Clicked", $email);
					$to = $userinfo->comm_email;
					$from = 'admin@gainbitco.in';
					$replyTo = 'thegainbitcoinhelp@gmail.com';
					$subject = 'Kit Shared';
					$message = $email;
					$htmlMessage = $email;
					$sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
				}
				$arr=array('success'=>'success','failure'=>'','data'=>'Kit has been shared successfully.');
				echo json_encode($arr);exit;
	/*		}
			else{
				$data=array('success'=>'','failure'=>'Invalid Request Found.');
				echo json_encode($data);exit;

					
			}
	*/
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}
	public function generaterandAction()
	{
		$misc_obj = new Gbc_Model_Custom_Miscellaneous();
		$rand = $misc_obj->generateRand();
		if(!empty($rand) && $rand!='')
		{
			echo $rand;exit;
		}
		else
		{
			echo '';exit;
		}

	}

	public function acceptkitAction()
	{
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$antixss = new Gbc_Model_Custom_StringLimit();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		foreach($_POST as $key => $value)
		{

			if(isset($value) && $value != ""){
				$antixss->setEncoding($value, "UTF-8");
				if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
					$arr=array('success'=>'','failure'=>'failure','data'=>'Invalid Input.');
					echo json_encode($arr);exit;

				}

			}

		}
		if(empty($authUserNamespace->user) || $authUserNamespace->user=='')
		{
			$arr=array('success'=>'','failure'=>'failure','data'=>'Session expired. Please login again.');
			echo json_encode($arr);exit;
		}
		$token=$common_obj->cleanQueryParameter(($_POST['token']));
	//	if(!empty($authUserNamespace->token) && $authUserNamespace->token==$token){
			//$kitnum=trim($_POST['kitnum']);
			$kitnum=$common_obj->cleanQueryParameter(($_POST['kitnum']));

			//$kit_pin=trim($_POST['kit_pin']);
			$kit_pin=$common_obj->cleanQueryParameter(($_POST['kit_pin']));
			$username=$authUserNamespace->user;

			$kits_obj=new Gbc_Model_DbTable_Kits();
			
			/*$pin_data = $kits_obj->fetchRow($kits_obj->select()
			->where("kit_shared_with='$username' and kit_number='$kitnum'")
			);*/
			$pin_data = $kits_obj->fetchRow($kits_obj->select()
			->where("kit_shared_with=?",$username)
			->where("kit_number=?",$kitnum)
			);
			
			$pin='';
			if(!empty($pin_data) && sizeof($pin_data)>0)
			{
				$pin = $pin_data->kit_pin;
			}
			else
			{
				$arr=array('success'=>'','failure'=>'failure','data'=>'Invalid Kit Number.');
				echo json_encode($arr);exit;
			}

			if(isset($pin) && $pin !='' && $pin != $kit_pin)
			{
				$arr=array('success'=>'','failure'=>'failure','data'=>'Invalid Pin Number.');
				echo json_encode($arr);exit;
			}

			$upd_kit_share_status_data=array('kit_share_status'=>'2');
			//$upd_qry=$kits_obj->update($upd_kit_share_status_data,"kit_shared_with='$username' and kit_number='$kitnum'");
			$upd_qry=$kits_obj->update($upd_kit_share_status_data,($DB->quoteInto("kit_shared_with=?",$username). ' AND '. $DB->quoteInto("kit_number=?",$kitnum)));
			
			$arr=array('success'=>'success','failure'=>'','data'=>'Kit has been accepted');
			echo json_encode($arr);exit;
/*		}
		else{
			$arr=array('success'=>'','failure'=>'failure','data'=>'Invalid Request Found');
			echo json_encode($arr);exit;
		}
*/
	}
	public function rejectkitAction()
	{
		try {


			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$antixss = new Gbc_Model_Custom_StringLimit();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			
			foreach($_POST as $key => $value)
			{

				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
						$arr=array('success'=>'','failure'=>'failure','data'=>'Invalid Input.');
						echo json_encode($arr);exit;

					}

				}

			}
			if(empty($authUserNamespace->user) || $authUserNamespace->user=='')
			{
				$arr=array('success'=>'','failure'=>'failure','data'=>'Session expired. Please login again.');
				echo json_encode($arr);exit;
			}
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$token=$common_obj->cleanQueryParameter(($_POST['token']));
			
		//	if(!empty($authUserNamespace->token) && $authUserNamespace->token==$token){
					
				$username=$authUserNamespace->user;
				//$kitnum=trim($_POST['kitnum']);
				$kitnum=$common_obj->cleanQueryParameter(($_POST['kitnum']));
				$kits_obj=new Gbc_Model_DbTable_Kits();
				$upd_arr=array('kit_share_status'=>'0','kit_shared_with'=>'');
				//$upd_qry=$kits_obj->update($upd_arr,"kit_number='".$kitnum."' AND kit_shared_with='".$username."'");
				$upd_qry=$kits_obj->update($upd_arr,($DB->quoteInto("kit_number=?",$kitnum).' AND '.$DB->quoteInto("kit_shared_with=?",$username)));
				
				$arr=array('success'=>'','failure'=>'failure','data'=>'Kit rejected.');
				echo json_encode($arr);exit;
		/*	}else{
				$arr=array('success'=>'','failure'=>'failure','data'=>'Invalid Request Found');
				echo json_encode($arr);exit;
			}
		*/		
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}

}
