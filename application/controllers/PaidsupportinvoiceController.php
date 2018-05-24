<?php
class PaidsupportinvoiceController extends Zend_Controller_Action{
	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Paid Support Invoices";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$user_id=$authUserNamespace->user_id;
		$antixss = new Gbc_Model_Custom_StringLimit();
		$data1=$misc_obj->GetAccessRightByUserId('5',$user_id);
		if((!empty($data1->view) && ($data1->view==1)) || $authUserNamespace->user=='admin')
		{
			$user = $authUserNamespace->user;
		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}
		$payment_obj = new Gbc_Model_DbTable_Querypayments();
		
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		try{

	 	$this->_helper->layout()->setLayout("admindashbord");//dashboard
	 	$MaxRowsCount=100;

			$data = $payment_obj->select()
			->from(array('query_payments'=>'query_payments'),array('count(invoice_id) as total_count'));
			//->where("query_payments. <>?", '0');

			if(!empty($_POST['search'])){

				$searchQuery = $_POST['search'];

				$data->where('query_payments.username = ?',$searchQuery);
			}
			
			$UserCountResult = $payment_obj->fetchRow($data);
			$UserCount = ($UserCountResult->total_count);

			if(!empty($PaginateLimit))
			{
				$PaginateLimit=$PaginateLimit;
			}
			else
			{
				$PaginateLimit=100;
			}
			$pages = ceil($UserCount/$PaginateLimit);

			$this->view->pages=$pages;

			if(!empty($_GET['page']))
			{
				$value = $_GET['page'];
				$antixss->setEncoding($_GET['page'], "UTF-8");
				if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
					$this->_redirect("/Profileerror/errormsg");

				}
				$startLimit=$_GET['page']*$PaginateLimit;
			}
			else
			{
				$startLimit=0;
			}
			if(!empty($PaginateLimit) && !empty($startLimit)){
				$Limit = $startLimit.", ".$PaginateLimit;
			}else if(!empty($PaginateLimit)){
				$Limit = $PaginateLimit;
			}else{
				$Limit ="";
			}

			if(!empty($_POST['search'])){
				$searchQuery = $common_obj->cleanQueryParameter($_POST['search']);

				foreach($_POST as $key => $value)
				{

					if(isset($value) && $value != ""){
						$antixss->setEncoding($value, "UTF-8");
						if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

							$this->_redirect("/Profileerror/errormsg");



						}

					}

				}

				
				$result = $payment_obj->select()
				->setIntegrityCheck(false)
				->from(array('query_payments'=>'query_payments'))
				->where("query_payments.username = ?",$searchQuery)
				->ORwhere("query_payments.invoice_id = ?",$searchQuery)
				->ORwhere("query_payments.wallet_addr LIKE ?",'%'.$searchQuery.'%')
				->order("query_payments.created_on DESC");
		
		
					
				if(!empty($PaginateLimit) && !empty($startLimit))
				{
					$result->limit($PaginateLimit,$startLimit);
				}
				else if(!empty($PaginateLimit)){
					$result->limit($PaginateLimit);
				}

			}else{

				$result = $payment_obj->select()
				->setIntegrityCheck(false)
				->from(array('query_payments'=>'query_payments'))
				->order("query_payments.created_on DESC");
			
				if(!empty($PaginateLimit) && !empty($startLimit))
				{
					$result->limit($PaginateLimit,$startLimit);
				}
				else if(!empty($PaginateLimit)){
					$result->limit($PaginateLimit);
				}
			}

			$allPaidInvoicesRes = $payment_obj->fetchAll($result);

			if(isset($allPaidInvoicesRes) && sizeof($allPaidInvoicesRes)>0)
			{
				$allPaidInvoicesRes = $allPaidInvoicesRes->toArray();
			}
			else
			{
				$allPaidInvoicesRes = array();
			}


	 	$this->view->result=$allPaidInvoicesRes;

		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}


	}
	
	public function changestatusAction(){
		try{
			$this->_helper->layout()->setLayout("admindashbord");
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$payment_obj = new Gbc_Model_DbTable_Querypayments();
			$username=$authUserNamespace->user;
			
			$invoice_id=$_POST['invoiceid'];

			$user=$_POST['user_name'];

			 $result=$payment_obj->fetchAll($payment_obj->select()
			->setIntegrityCheck(false)
			->from(array('query_payments'))
			->where("invoice_id=?",$invoice_id)
			->where("username=?",$user)
			->where("status=?",'Inactive'));		

			if(!empty($result) && sizeof($result)>0)
			{
				$this->view->resultinvoice=$result;
				$this->view->invoiceid=$invoice_id;
				$this->view->username=$user;			
			}			
			
		}catch(Exception $e)
		{
			$db->rollBack();
			$arr=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($arr);exit;
		}			
	}
	
	
	public function updatestatusAction(){
		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$payment_obj = new Gbc_Model_DbTable_Querypayments();
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$payment_obj = new Gbc_Model_DbTable_Querypayments();
			$username=$authUserNamespace->user;
			$adminsetting_obj=new Gbc_Model_DbTable_Adminsetting();
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
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
				if(isset($_POST['id'])&& !empty($_POST['id'])&& ($_POST['id'])!="" && isset($_POST['password'])&& !empty($_POST['password'])&& ($_POST['password'])!="" && isset($_POST['comment'])&& !empty($_POST['comment'])&& ($_POST['comment'])!="")
				{
					$show=true;
					$update=date('Y-m-d h:i:s');
					$update_by = $authUserNamespace->user;
					$pass=(strip_tags($_POST['password']));
					$invoice_id=$_POST['id'];
					$user=$_POST['user'];	
					$comment=$_POST['comment'];
					$resultadminsetting=$adminsetting_obj->fetchRow($adminsetting_obj->select()
					->setIntegrityCheck(false)
					->from(array('admin_setting'))
					);

					if(!empty($resultadminsetting) && sizeof($resultadminsetting)>0)
					{
						$static=$resultadminsetting->static_salt;
					}

					$stcPss = $misc_obj->encryptPassword($pass,$static);
					
					if($stcPss!=$resultadminsetting->static_pwd)
					{	
						$msg="Password Not matched.Invoice status updation Failed !";
						$data=array('success'=>'','failure'=>$msg);
						echo  json_encode($data);exit;
						$this->_redirect("/Paidsupportinvoice/changestatus?invoiceid=".$invoice_id);
						
					}else{
						$result=$payment_obj->fetchRow($payment_obj->select()
						->setIntegrityCheck(false)
						->from(array('query_payments'))
						->where("invoice_id=?",$invoice_id)
						->where("username=?",$user)
						->where("status=?",'Inactive'));				

						if(!empty($result) && sizeof($result)>0)
						{		
							$update_arr=array('status'=>'Active','updated_by'=>$update_by,'comment'=>$comment,'updated_on'=>new Zend_Db_Expr('NOW()'));
							$where = array();
							$where[] = $db->quoteInto('invoice_id = ?',$invoice_id);
							$where[] = $db->quoteInto("status='Inactive' || status='Pending'  || status='Partial Payment' || status='Used'");
							$where[] = $db->quoteInto('username = ?',$user);
							$update_data=$payment_obj->update($update_arr,$where);
							if(!empty($update_data))
							{
								  $description = "Paid support invoice :: $invoice_id for username :: $user Manual Activated by $update_by on $update comment mention :: $comment" ;
								if(!empty($description)){
									$saveUserLog = $common_obj->saveUserLog($username,"query_payments",$description);						
								}
								$data=array('success'=>"Status updated successfully !",'failure'=>'',);
								echo json_encode($data);exit;
							}
							else
							{
								$data=array('success'=>'','failure'=>'Some error occured');
								echo  json_encode($data);exit;
							}	
						  }		
					}					
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
