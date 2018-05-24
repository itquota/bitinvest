<?php
class GeneratereqController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		try
		{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$this->_helper->layout()->setLayout("dashbord");
			
			$username=$authUserNamespace->user;
			//$common_obj = new Gbc_Model_Custom_CommonFunc();
			
			

			$permissions_obj=new Gbc_Model_DbTable_FeaturedPermission();
			$permissions_data=$permissions_obj->fetchRow($permissions_obj->select()
			->setIntegrityCheck(false)
			->from(array('featured_permissions'),array('name','value','start','end'))
			->where("name =?",'refund_disable')
			->where("value =?",'1'))->toArray();

			$this->view->permissions_data=$permissions_data;
			//print_r($permissions_data);
			//exit;
			
			
			if($this->_request->isPost())
			{
				$common_obj = new Gbc_Model_Custom_CommonFunc();
				$misc_obj = new Gbc_Model_Custom_Miscellaneous();
				$ip_address=$misc_obj->get_client_ip();
				$ref_req_obj = new Gbc_Model_DbTable_Refrequest();
				$antixss = new Gbc_Model_Custom_StringLimit();
				$flag=0;
				$this->view->title="Gainbitcoin - Refund Request";
				
				

		//		if(isset($authUserNamespace->token) && $authUserNamespace->token==$_POST['token']) {
						foreach($_POST as $key => $value)
						{

							if(isset($value) && $value != ""){
								$antixss->setEncoding($value, "UTF-8");
								if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
									$flag=1;
								}

								//echo $key . " - " . $value . " - " . $antixss->setFilter($value, "black", "string")."\n";
							}
							
						}	
							
					if($flag!=1)
					{	
						if(!empty($this->permissions_data) && ($CurrentDate >= $this->permissions_data['start']) && ($CurrentDate <= $this->permissions_data['end'])){
							$msg = 'Refund requests are disabled till '.date('d F, Y',strtotime($this->permissions_data['end']));
							$authUserNamespace->msg=$msg;
						}else if(!empty($_POST) && !empty($_POST['full_name']) && !empty($_POST['email']) && !empty($_POST['kit_number']) && !empty($_POST['phone'])  && !empty($_POST['reason']) && !empty($_POST['wallet_address']))
						{
							
							$_POST = ($_POST);
							$_POST['username'] = $username;
							$kit_number = $_POST['kit_number'];
							$ref_request=$ref_req_obj->fetchRow($ref_req_obj->select()
								 ->from(array('refund_requests'=>"refund_requests"),array('id'))
								->where("1=1 and username = '$username' and status != '2' and kit_number=?",$kit_number));
						//	print_r($ref_request);
							//exit;
							$msg =$common_obj-> checkInvoice($_POST);
							$CheckTransaction =$common_obj-> CheckTransactionforrefund($_POST['txid']);
							if(!empty($ref_request)){
								
								$msg = "You have already generated refund request for this kit.";
								$authUserNamespace->msg=$msg;
								//echo $msg;
								//exit;
								
							}else{
								if($CheckTransaction == "success"){
									if($msg == "success"){
										$ref_data=array('username'=>$username,'full_name'=>$_POST['full_name'],'email'=>$_POST['email'],'kit_number'=>$_POST['kit_number'],'phone'=>$_POST['phone'],'txid'=>$_POST['txid'],'reason_to_refund'=>$_POST['reason'],'wallet_addr'=>$_POST['wallet_address'],'ip_address'=>$ip_address,'status'=>0);
										
										$ref_qry=$ref_req_obj->insert($ref_data);
										if(!empty($ref_qry))
										{
											$msg = "Request submitted successfully. Refund request will be cleared in 30 working days!";
											$authUserNamespace->msg=$msg;
											$this->_redirect("/Reqrefund");
										}
										else{
											$msg = "Something Error";
											$authUserNamespace->msg=$msg;
										}
																		
									}else{
										
											$msg = "Please Enter correct Kit Number";
											$authUserNamespace->msg=$msg;
									}
								}
							}
						}
					}
					else
					{
						$msg = "Invalid Input";
						$authUserNamespace->msg=$msg;
					}					
			/*		
				}
				else
				{
					$msg = "Invalid Token";
					$authUserNamespace->msg=$msg;
				}
			*/	
				
			}
			

		}
		catch(Exception $e)
		{

			$msg= $e->getMessage();
			$authUserNamespace->msg=$msg;
		}
	}
	
	
	public function checkkitstatusAction()
	{
		
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$kits_obj=new Gbc_Model_DbTable_Kits();
		$kit_number = $common_obj->cleanQueryParameter($_GET['kit_number']);
		

		/*$kits_data=$kits_obj->fetchRow($kits_obj->select()
		->where("1=1 and kit_number='".$kit_number."'"));*/
		$kits_data=$kits_obj->fetchRow($kits_obj->select()
		->where("1=1 and status in ('active','used') and kit_number=?",$kit_number));
	
		
		if(!empty($kits_data) && sizeof($kits_data)>0)
		{
			echo  "true";exit;
		}
		else
		{
			echo  "false";exit;
		}
		
	}
	
	
	
	public function checkkitpurchasedAction()
	{
		try{
			
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$kits_obj=new Gbc_Model_DbTable_Kits();
		$kit_number = $common_obj->cleanQueryParameter($_GET['kit_number']);
		$username=$authUserNamespace->user;

		/*$kits_data=$kits_obj->fetchRow($kits_obj->select()
		->where("1=1 and kit_number='".$kit_number."'"));*/
		$kits_data=$kits_obj->fetchRow($kits_obj->select()
		->where("1=1 and username like '$username' and status in ('active','used') and kit_number=?",$kit_number));
	
		
		if(!empty($kits_data) && sizeof($kits_data)>0)
		{
			echo  "true";exit;
		}
		else
		{
			echo  "false";exit;
		}
		}
		catch(Exception $e){
			echo $e->getMessage();
		}
		
	}
	
	
	
	
	public function checkkitrefundperiodAction()
	{
		try{
			
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$kits_obj=new Gbc_Model_DbTable_Kits();
		$kit_number = $common_obj->cleanQueryParameter($_GET['kit_number']);
		$username=$authUserNamespace->user;

		/*$kits_data=$kits_obj->fetchRow($kits_obj->select()
		->where("1=1 and kit_number='".$kit_number."'"));*/
		$kits_data=$kits_obj->fetchRow($kits_obj->select()
		->where("1=1 and username like '$username' and (kits.created_on BETWEEN DATE_SUB(NOW(), INTERVAL 29 DAY) AND NOW()) and status in ('active','used') and kit_number=?",$kit_number));
	//print_r($kits_data);
		
		if(!empty($kits_data) && sizeof($kits_data)>0)
		{
			echo  "true";exit;
		}
		else
		{
			echo  "false";exit;
		}
		}
		catch(Exception $e){
			echo $e->getMessage();
		}
		
	}
	
	
	

}
