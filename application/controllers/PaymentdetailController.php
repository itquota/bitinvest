<?php
class PaymentdetailController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Paid After Timeout";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('32',$user_id);
		
		if((!empty($data1->view) && ($data1->view==1)) || $authUserNamespace->user=='admin')
		{
		
		}
		else 
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}


		$paymentdetailsObj = new  Gbc_Model_DbTable_Paymentresponose();
		$kitinvoicesObj= new Gbc_Model_DbTable_Kitinvoices();
		$Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
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
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$this->_helper->layout()->setLayout("Admindashbord");//dashboard
			$result=array();


			$result=$paymentdetailsObj->fetchAll($paymentdetailsObj->select()
			->setIntegrityCheck(false)
			->from(array("p"=>'payment_response'))
			->joinLeft(array("k"=>'kit_invoices'),'(p.invoice_id=k.invoice_id)',array('k.contract_rate'))
			->where("p.status= ?",'paid_after_timeout')
			->where("k.invoice_status != ?",1));
			
			$this->view->result =$result;
			if(!empty($_POST['invoiceid']) && $_POST['invoiceid']!='')
			{	
				
				$invoice_id=$_POST['invoiceid'];
				
				 $token=$_POST['token'];
		//		 if($authUserNamespace->token==$token)	 {
				
		 	  		$payments_obj=new Gbc_Model_DbTable_Payaftertimeout();
					$payments_data = $payments_obj->fetchAll($payments_obj->select()
					->where("invoice_id= ?",$invoice_id)
					);
					
					$paymentdata=sizeof($payments_data);
						
					if(empty($payments_data) || sizeof($payments_data)<=0)
					{
						
						$MarkResponse =$Gbc_Model_Custom_func_obj->KitActivationAfterPayment($invoice_id);
						//echo $MarkResponse;exit;
						$authUserNamespace->msg = $MarkResponse;
						$this->view->msg=$MarkResponse;
					}
		/*		}
				else{
					$message = 'Invalid Request Found';
					$authUserNamespace->message=$message;
					
					//$data=array('success'=>'','failure'=>'Invalid Request Found.');
				     //echo json_encode($data);exit;
				}
		 */	    
			}
			$db->commit();

		}
		catch(Exception $e)
		{
			$db->rollBack();
			$e->getMessage();exit;
		}
			
			
			
			



	}
}
