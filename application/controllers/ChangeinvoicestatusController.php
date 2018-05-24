<?php
class ChangeinvoicestatusController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");
		
		$authUserNamespace="kit updated successfully";
		$this->view->msg=$authUserNamespace;
	
	}
	public function indexAction()
	{
		try {
			
			$this->view->title="Gainbitcoin - Kit Invoices";
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$this->_helper->layout()->setLayout("admindashbord");
			$adminsetting_obj=new Gbc_Model_DbTable_Adminsetting();
			$kitinvoice_obj=new Gbc_Model_DbTable_Kitinvoices();
			$kit_obj=new Gbc_Model_DbTable_Kits();
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			$antixss = new Gbc_Model_Custom_StringLimit();
			$invoiceId=$common_obj->cleanQueryParameter($_POST['invoiceid']);
			
			if(!isset($authUserNamespace->user) && ($authUserNamespace->user_type!='subadmin' || $authUserNamespace->user_type!='admin')){
				$this->_redirect("/Dashboard/logout");
			}
			
						//echo $invoiceId;exit;
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
			
				$kits_obj=new Gbc_Model_DbTable_Kits();

				$username=$common_obj->cleanQueryParameter($_POST['user_name']);
				$invoiceId=$common_obj->cleanQueryParameter($_POST['invoiceid']);

				// 	$sql="SELECT * from kits where username='".$username."' AND invoice_id='".$invoiceId."' ";
				/*$resultkit=$kits_obj->fetchAll($kits_obj->select()
				->setIntegrityCheck(false)
				->from(array('k'=>'kits'))
				->where("username='".$username."' AND invoice_id='".$invoiceId."'")
				);*/

				$resultkit=$kits_obj->fetchAll($kits_obj->select()
				->setIntegrityCheck(false)
				->from(array('k'=>'kits'))
				->where("username=?",$username)
				->where("invoice_id=?",$invoiceId)
				
				);
				
				
			   
				$this->view->resultkit=$resultkit;
			
			$this->view->invoiceid=$invoiceId;
			$this->view->username=$username;
			
			
			
			
			
			
			
/*else {


				$kits_obj=new Gbc_Model_DbTable_Kits();

				$username=$common_obj->cleanQueryParameter($_REQUEST['user_name']);
				$invoiceId=$common_obj->cleanQueryParameter($_REQUEST['invoiceid']);

				// 	$sql="SELECT * from kits where username='".$username."' AND invoice_id='".$invoiceId."' ";
				$resultkit=$kits_obj->fetchAll($kits_obj->select()
				->setIntegrityCheck(false)
				->from(array('k'=>'kits'))
				->where("username='".$username."' AND invoice_id='".$invoiceId."'")
				);

				$this->view->resultkit=$resultkit;
				//$this->_redirect("/Kitinvoice");
			}*/
		
			
		}
	
		catch(Exception $e)
		{
			$db->rollBack();	
			$arr=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($arr);exit;
		}


	}
	
	
	public function gb2kitsAction()
	{
		try {
			
			$this->view->title="Gainbitcoin - Kit Invoices";
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$this->_helper->layout()->setLayout("admindashbord");
			$adminsetting_obj=new Gbc_Model_DbTable_Adminsetting();
			$kitinvoice_obj=new Gbc_Model_DbTable_Gb2Kitinvoices();
			$kit_obj=new Gbc_Model_DbTable_Gb2Kits();
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			$antixss = new Gbc_Model_Custom_StringLimit();
			$invoiceId=$common_obj->cleanQueryParameter($_POST['invoiceid']);
			
			if(!isset($authUserNamespace->user) && ($authUserNamespace->user_type!='subadmin' || $authUserNamespace->user_type!='admin')){
				$this->_redirect("/Dashboard/logout");
			}
			
						//echo $invoiceId;exit;
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
			
				$kits_obj=new Gbc_Model_DbTable_Kits();

				$username=$common_obj->cleanQueryParameter($_POST['user_name']);
				$invoiceId=$common_obj->cleanQueryParameter($_POST['invoiceid']);

				$resultkit=$kits_obj->fetchAll($kits_obj->select()
				->setIntegrityCheck(false)
				->from(array('k'=>'gb2_kits'))
				->where("username=?",$username)
				->where("invoice_id=?",$invoiceId)
				
				);
				
				
			   
				$this->view->resultkit=$resultkit;
			
			$this->view->invoiceid=$invoiceId;
			$this->view->username=$username;
			
		}
	
		catch(Exception $e)
		{
			$db->rollBack();	
			$arr=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($arr);exit;
		}


	}
	
	
	public function updateinvoiceAction(){
		
		try{
	$db = Zend_Db_Table::getDefaultAdapter();
	$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
	
	$db->beginTransaction();	
	$common_obj = new Gbc_Model_Custom_CommonFunc();
	$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
	$adminsetting_obj=new Gbc_Model_DbTable_Adminsetting();
	$kitinvoice_obj=new Gbc_Model_DbTable_Kitinvoices();
	$kit_obj=new Gbc_Model_DbTable_Kits();
	$misc_obj=new Gbc_Model_Custom_Miscellaneous();
	if(!isset($authUserNamespace->user) && ($authUserNamespace->user_type!='subadmin' || $authUserNamespace->user_type!='admin')){
		$this->_redirect("/Dashboard/logout");
	}
	
			if($this->_request->isPost())
			{
				$antixss = new Gbc_Model_Custom_StringLimit();
				
				foreach($_POST as $key => $value)
				{
					//	if($key!='comment'){
					if(isset($value) && $value != ""){
						$antixss->setEncoding($value, "UTF-8");
						if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
	
							$data=array('success'=>'','failure'=>'Invalid Input.');
							echo json_encode($data);exit;
	
						}
	
					}
				//}
			}
				
				
				
				
		
				 $token=$_POST['token'];
				
		 	   // if($authUserNamespace->token==$token){
					
		
				$show=false;
				if(isset($_POST['id'])&& !empty($_POST['id'])&& ($_POST['id'])!="" && isset($_POST['address'])&& !empty($_POST['address'])&& ($_POST['address'])!="" && isset($_POST['comment'])&& !empty($_POST['comment'])&& ($_POST['comment'])!="")
				{
					
				
					$show=true;
					$update=date('Y-m-d h:i:s');
					$update_by = $authUserNamespace->user;

					$pass=(strip_tags($_POST['address']));

					// $Sql_adm="SELECT * from admin_setting";
					$resultadminsetting=$adminsetting_obj->fetchRow($adminsetting_obj->select()
					->setIntegrityCheck(false)
					->from(array('admin_setting'))
					);

					if(!empty($resultadminsetting) && sizeof($resultadminsetting)>0)
					{
						$static=$resultadminsetting->static_salt;
							//echo "in";exit;
					}


					$stcPss = $misc_obj->encryptPassword($pass,$static);

					if($stcPss!=$resultadminsetting->static_pwd)
				//	if(1==2)
					{	
						//$invoiceId=$_POST['id'];
						//echo $invoiceId;exit;
						//echo "in if";exit;
						$kitUpdated = false;
						//$msg="Password Not matched! Kits updation Failed!";
						//$authUserNamespace->msg=$msg;
						$msg="Password Not matched! Kits updation Failed!";
						$data=array('success'=>'','failure'=>$msg);
						echo  json_encode($data);exit;
						
						
						
						$invoice_id=$_POST['id'];
						$user=$_POST['user'];
						$this->_redirect("/Changeinvoicestatus?invoiceid=".$invoice_id."&user_name=".$user);
						
					}
					else{
						//echo "in else";exit;
						//$query="SELECT * from kit_invoices WHERE invoice_id='".cleanQueryParameter($_POST["id"])."' ";
						$resultkitinvoices=$kitinvoice_obj->fetchRow($kitinvoice_obj->select()
						->setIntegrityCheck(false)
						->from(array('kit_invoices'))
						->where("invoice_id=?",$_POST['id']));

						if(!empty($resultkitinvoices))
						{
							$description="";
							if($resultkitinvoices->invoice_status !='1')
							{
								$description .= "invoice_status for Kit invoice '".$resultkitinvoices->invoice_id."' has been changed from ".$resultkitinvoices->invoice_status." to 1";
									
							}

						}

						//$query = "UPDATE kit_invoices SET invoice_status=1,comment='".$_POST["comment"]."', update_by='".$update_by."',updated_on=now() WHERE invoice_id='".cleanQueryParameter($_POST["id"])."'";
						$updateinvoicedata=array("invoice_status"=>'1',"comment"=>$_POST['comment'],"update_by"=>$update_by,"updated_on"=>new Zend_Db_Expr('NOW()'));
						$updatedata=$kitinvoice_obj->update($updateinvoicedata,$DB->quoteInto("invoice_id=?",$_POST['id']));

						if(!empty($updatedata)){

							if(!empty($description) && $description!=''){

								//$saveUserLog = saveUserLog($update_by,"kit_invoices",$description);


								$common_obj = new Gbc_Model_Custom_CommonFunc();
								$saveUserLog=$common_obj->saveUserLog($update_by,'kit_invoices',$description);

							}


						}
						if(!empty($updatedata))
						{
							$kitInvUpdated = true;
							$update=date('Y-m-d h:i:s');
							//$query="SELECT * from kits WHERE (status='Inactive' || status='Pending'  || status='Partial Payment')AND invoice_id='".cleanQueryParameter($_POST["id"])."' ";
						/*	$resultkit=$kit_obj->fetchRow($kit_obj->select()
							->setIntegrityCheck(false)
							->from(array('kits'))
							->where("(status='Inactive' || status='Pending'  || status='Partial Payment') AND invoice_id='".$_POST['id']."'"));*/
							
							$resultkit=$kit_obj->fetchRow($kit_obj->select()
							->setIntegrityCheck(false)
							->from(array('kits'))
							->where("status='Inactive' || status='Pending'  || status='Partial Payment'")
							->where("invoice_id=?",$_POST['id'])
							);
							

							$description = "";
							if(!empty($resultkit) && sizeof($resultkit)>0)
							{
								if($resultkit->status != "Active")
								{
									$description .= "status for kit '".$resultkit->kit_number."' has been changed from ".$resultkit->status." to Active";

								}
								$KitType = $resultkit->kit_type;
								$KitPrice = $resultkit->kit_price;

							}
							//$query = "UPDATE kits SET status='Active',count='".$_POST["counter"]."', updated_on=now() WHERE (status='Inactive' || status='Pending'  || status='Partial Payment') AND invoice_id='".cleanQueryParameter($_POST["id"])."'";
							$updatekits=array("status"=>'Active',"updated_on"=>new Zend_Db_Expr('NOW()'));
							$updatekitsdata=$kit_obj->update($updatekits,"(status='Inactive' || status='Pending'  || status='Partial Payment') AND invoice_id='".$_POST['id']."'");


							if(!empty($updatekitsdata))
							{

								//$calculateKits = CalculateAvailableKits($KitPrice);
								$common_obj = new Gbc_Model_Custom_CommonFunc();
								$calculateKits=$common_obj->CalculateAvailableKits($KitPrice);

								if(!empty($description)){


									//$saveUserLog = saveUserLog($update_by,"kits",$description);
									$common_obj = new Gbc_Model_Custom_CommonFunc();
									$saveUserLog=$common_obj->saveUserLog($update_by,"kits",$description);
								}
								$kitUpdated=true;

							}
							else {
								$kitUpdated = false;
							}


						}
						else {

							$kitInvUpdated = false;
						}
					}

				}
				else{
					$kitUpdated = false;
					//$result["errMsg"]="All Field required";
					//$msg="All Field required";
					
					$msg="All Field required";
					$data=array('success'=>'','failure'=>$msg);
						echo  json_encode($data);exit;

				}

		//$this->_redirect("/Kitinvoice");
		 
		/*		  
		 	    }else{
		 			
				     $msg="Invalid Request Found";
				     // $authUserNamespace->errormsg=$msg;
				      
				    $data=array('success'=>'','failure'=>$msg);
				     echo json_encode($data);exit;
				      
		 		} */
		 		 //$this->view->msg=$msg;
		 		
			}
			
			
				$db->commit();
			if(!empty($kitInvUpdated))
			{
				//$this->_redirect("/Kitinvoice");
				//$msg="Updated successfully";
				//$authUserNamespace->msg=$msg;
				
				   $data=array('success'=>'Updated successfully','failure'=>'');
				     echo json_encode($data);exit;
			}
			
	}
	catch(Exception $e)
	{
		echo $e->getMessage();exit;
	}
		
	}
	
	
	
	
	public function updategb2invoiceAction(){
		
		try{
			$db = Zend_Db_Table::getDefaultAdapter();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();

			$db->beginTransaction();	
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$adminsetting_obj=new Gbc_Model_DbTable_Adminsetting();
			$kitinvoice_obj=new Gbc_Model_DbTable_Gb2Kitinvoices();
			$kit_obj=new Gbc_Model_DbTable_Gb2Kits();
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			if(!isset($authUserNamespace->user) && ($authUserNamespace->user_type!='subadmin' || $authUserNamespace->user_type!='admin')){
				$this->_redirect("/Dashboard/logout");
			}

					if($this->_request->isPost())
					{
						$antixss = new Gbc_Model_Custom_StringLimit();

						foreach($_POST as $key => $value)
						{
							//	if($key!='comment'){
							if(isset($value) && $value != ""){
								$antixss->setEncoding($value, "UTF-8");
								if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

									$data=array('success'=>'','failure'=>'Invalid Input.');
									echo json_encode($data);exit;

								}

							}
						//}
					}
						 $token=$_POST['token'];
					   // if($authUserNamespace->token==$token){

						$show=false;
						if(isset($_POST['id'])&& !empty($_POST['id'])&& ($_POST['id'])!="" && isset($_POST['address'])&& !empty($_POST['address'])&& ($_POST['address'])!="" && isset($_POST['comment'])&& !empty($_POST['comment'])&& ($_POST['comment'])!="")
						{

							$show=true;
							$update=date('Y-m-d h:i:s');
							$update_by = $authUserNamespace->user;

							$pass=(strip_tags($_POST['address']));

							// $Sql_adm="SELECT * from admin_setting";
							$resultadminsetting=$adminsetting_obj->fetchRow($adminsetting_obj->select()
							->setIntegrityCheck(false)
							->from(array('admin_setting'))
							);

							if(!empty($resultadminsetting) && sizeof($resultadminsetting)>0)
							{
								$static=$resultadminsetting->static_salt;
									//echo "in";exit;
							}


							$stcPss = $misc_obj->encryptPassword($pass,$static);

							if($stcPss!=$resultadminsetting->static_pwd)
						//	if(1==2)
							{	
								//$invoiceId=$_POST['id'];
								//echo $invoiceId;exit;
								//echo "in if";exit;
								$kitUpdated = false;
								//$msg="Password Not matched! Kits updation Failed!";
								//$authUserNamespace->msg=$msg;
								$msg="Password Not matched! Kits updation Failed!";
								$data=array('success'=>'','failure'=>$msg);
								echo  json_encode($data);exit;

								$invoice_id=$_POST['id'];
								$user=$_POST['user'];
								$this->_redirect("/Changeinvoicestatus/gb2kits?invoiceid=".$invoice_id."&user_name=".$user);

							}
							else{
								//echo "in else";exit;
								//$query="SELECT * from kit_invoices WHERE invoice_id='".cleanQueryParameter($_POST["id"])."' ";
								$resultkitinvoices=$kitinvoice_obj->fetchRow($kitinvoice_obj->select()
								->setIntegrityCheck(false)
								->from(array('gb2_kit_invoices'))
								->where("invoice_id=?",$_POST['id']));

								if(!empty($resultkitinvoices))
								{
									$description="";
									if($resultkitinvoices->invoice_status !='1')
									{
										$description .= "GB2 invoice_status for Kit invoice '".$resultkitinvoices->invoice_id."' has been changed from ".$resultkitinvoices->invoice_status." to 1";

									}

								}
							$updateinvoicedata=array("invoice_status"=>'1',"comment"=>$_POST['comment'],"update_by"=>$update_by,"updated_on"=>new Zend_Db_Expr('NOW()'));
								$updatedata=$kitinvoice_obj->update($updateinvoicedata,$DB->quoteInto("invoice_id=?",$_POST['id']));

								if(!empty($updatedata)){

									if(!empty($description) && $description!=''){

										//$saveUserLog = saveUserLog($update_by,"kit_invoices",$description);


										$common_obj = new Gbc_Model_Custom_CommonFunc();
										$saveUserLog=$common_obj->saveUserLog($update_by,'kit_invoices',$description);

									}


								}
								if(!empty($updatedata))
								{
									$kitInvUpdated = true;
									$update=date('Y-m-d h:i:s');
								
									$resultkit=$kit_obj->fetchRow($kit_obj->select()
									->setIntegrityCheck(false)
									->from(array('gb2_kits'))
									->where("status='Inactive' || status='Pending'  || status='Partial Payment'")
									->where("invoice_id=?",$_POST['id'])
									);


									$description = "";
									if(!empty($resultkit) && sizeof($resultkit)>0)
									{
										if($resultkit->status != "Active")
										{
											$description .= "GB2 status for kit '".$resultkit->kit_number."' has been changed from ".$resultkit->status." to Active";

										}
										$KitType = $resultkit->kit_type;
										$KitPrice = $resultkit->kit_price;

									}
									
									$updatekits=array("status"=>'Active',"updated_on"=>new Zend_Db_Expr('NOW()'));
									$updatekitsdata=$kit_obj->update($updatekits,"(status='Inactive' || status='Pending'  || status='Partial Payment') AND invoice_id='".$_POST['id']."'");
									if(!empty($updatekitsdata))
									{
										//$calculateKits = CalculateAvailableKits($KitPrice);
										$common_obj = new Gbc_Model_Custom_CommonFunc();
								//		$calculateKits=$common_obj->CalculateAvailableKits($KitPrice);

										if(!empty($description)){
											//$saveUserLog = saveUserLog($update_by,"kits",$description);
											$common_obj = new Gbc_Model_Custom_CommonFunc();
											$saveUserLog=$common_obj->saveUserLog($update_by,"kits",$description);
										}
										$kitUpdated=true;

									}
									else {
										$kitUpdated = false;
									}
								}
								else {

									$kitInvUpdated = false;
								}
							}

						}
						else{
							$kitUpdated = false;
							//$result["errMsg"]="All Field required";
							//$msg="All Field required";

							$msg="All Field required";
							$data=array('success'=>'','failure'=>$msg);
								echo  json_encode($data);exit;

						}
					}
					$db->commit();
					if(!empty($kitInvUpdated))
					{
					
						   $data=array('success'=>'Updated successfully','failure'=>'');
							 echo json_encode($data);exit;
					}

			}
			catch(Exception $e)
			{
				echo $e->getMessage();exit;
			}
		
	}
	
	public function updatestatusAction(){
		try{
			$db = Zend_Db_Table::getDefaultAdapter();
			
			$db->beginTransaction();	
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$kit_invoices_obj=new Gbc_Model_DbTable_Kitinvoices();
			$kits_obj=new Gbc_Model_DbTable_Kits();
			$username = $authUserNamespace->user;

		//	var_dump($_POST);
		//	exit;
	
			if($this->_request->isPost()){
				$antixss = new Gbc_Model_Custom_StringLimit();
				foreach($_POST as $key => $value){
					if(isset($value) && $value != ""){
						$antixss->setEncoding($value, "UTF-8");
						if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
	
							$data=array('success'=>'','failure'=>'Invalid Input.');
							echo json_encode($data);exit;
	
						}
	
					}
				}
				
				if(!empty($_POST['address']) && !empty($_POST['amount'])){
					$data = array('address' => $_POST['address'], 'amount' => $_POST['amount'] );
			
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, "https://mcapwallet.io/api/getdepstatus");
					curl_setopt($ch, CURLOPT_POST, TRUE);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
					$Response = curl_exec($ch);
					curl_close($ch);
					
					$Res = json_decode($Response);
					$result = $Res->message;

				
					if($result == true){
						 $rowInvoice = $kit_invoices_obj->fetchRow($kit_invoices_obj->select()
							->where("middleAddr = ?",$_POST['address']));

						$invoiceId = $rowInvoice->invoice_id;
						$user  = $rowInvoice->username;
							
						$query2=array('status'=> 'Active','updated_on'=>new Zend_Db_Expr('NOW()'));
						$query2_data=$kits_obj->update($query2," invoice_id = '" . $invoiceId . "' AND (status='Pending' or status='Inactive' or status='Partial Payment')");
						
						$query["invoice_status"] = "1";
						 $que_data=$kit_invoices_obj->update($query,$db->quoteInto("invoice_id= ?",$invoiceId));
						
						$db->commit();

						
							$common_obj = new Gbc_Model_Custom_CommonFunc();
							$description = "kits activated with invoice_id $invoiceId by $username";
							$saveUserLog=$common_obj->saveUserLog($user,'kit_invoices,kits',$description);
							
						echo "success";
						//$arr=array('success'=>'success','failure'=>'');
						 //echo json_encode($arr);

						
					}
					

				}

				
			}
			exit;
		
		}
		
		catch(Exception $e){
			echo $e->getMessage();exit;
		}
		
		
	}
	
	
	
	public function updategb2statusAction(){
		try{
			$db = Zend_Db_Table::getDefaultAdapter();
			
			$db->beginTransaction();	
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$kit_invoices_obj=new Gbc_Model_DbTable_Gb2Kitinvoices();
			$kits_obj=new Gbc_Model_DbTable_Gb2Kits();
		   $Gb2Binaryuserincome_obj=new Gbc_Model_DbTable_Gb2Binaryuserincome();
			$Gb2Contracts_obj=new Gbc_Model_DbTable_Gb2Contracts();
			$username = $authUserNamespace->user;

		//	var_dump($_POST);
		//	exit;
	
			if($this->_request->isPost()){
				$antixss = new Gbc_Model_Custom_StringLimit();
				foreach($_POST as $key => $value){
					if(isset($value) && $value != ""){
						$antixss->setEncoding($value, "UTF-8");
						if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
	
							$data=array('success'=>'','failure'=>'Invalid Input.');
							echo json_encode($data);exit;
	
						}
	
					}
				}
				
				if(!empty($_POST['address']) && !empty($_POST['amount'])){
					$data = array('address' => $_POST['address'], 'amount' => $_POST['amount'] );
			
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, "https://mcapwallet.io/api/getdepstatus");
					curl_setopt($ch, CURLOPT_POST, TRUE);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
					$Response = curl_exec($ch);
					curl_close($ch);
				//	$Response = '{"message":true}';
					$Res = json_decode($Response);
					$result = $Res->message;

				
					if($result == true){
						 $rowInvoice = $kit_invoices_obj->fetchRow($kit_invoices_obj->select()
							->where("middleAddr = ?",$_POST['address']));

						$invoiceId = $rowInvoice->invoice_id;
						$kits_qty = $rowInvoice->kits_qty;
						$user  = $rowInvoice->username;
						$referer_user = isset($rowInvoice->referer_user)?$rowInvoice->referer_user:'';
							
						$query2=array('status'=> 'Active','updated_on'=>new Zend_Db_Expr('NOW()'));
						$query2_data=$kits_obj->update($query2," invoice_id = '" . $invoiceId . "' AND (status='Pending' or status='Inactive' or status='Partial Payment')");
						
						$query["invoice_status"] = "1";
						 $que_data=$kit_invoices_obj->update($query,$db->quoteInto("invoice_id= ?",$invoiceId));
						
						
						
						 $row1 = $kits_obj->fetchRow($kits_obj->select()
              			  ->where("invoice_id = ?",$invoiceId));
						
						$contract_id = $row1->contract_id;
						$contract_query["available_limit"] = new Zend_Db_Expr("available_limit - $kits_qty");
						$Gb2Contracts_obj=$Gb2Contracts_obj->update($contract_query,$db->quoteInto("contract_id= ?",$contract_id));
						
					//	print_r($contract_id);
					//	print_r($Gb2Contracts_obj);
						
						
						if(!empty($referer_user)){
							$amount = round(($_POST['amount'] * 2)/100,4);
							$insert = array('ben_username' => $referer_user, 'from_username'=> $username, 'amount'=> $amount, 'invoice_id' => $invoiceId, 'status' => '1', 'percentage'=> '2');
							$insert1 = $Gb2Binaryuserincome_obj->insert($insert);
							
						}
						
						$db->commit();

						
							$common_obj = new Gbc_Model_Custom_CommonFunc();
							$description = "GB2 kits activated with invoice_id $invoiceId by $username";
							$saveUserLog=$common_obj->saveUserLog($user,'kit_invoices,kits',$description);
							
						echo "success";
						//$arr=array('success'=>'success','failure'=>'');
						 //echo json_encode($arr);

						
					}
					

				}

				
			}
			exit;
		
		}
		
		catch(Exception $e){
			echo $e->getMessage();exit;
		}
		
		
	}
				
				
	
	
	
	
	
}
