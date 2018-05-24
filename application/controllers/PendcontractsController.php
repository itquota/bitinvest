<?php
class PendcontractsController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		/*$this->view->title="Gainbitcoin - Contract Invoices";
		 $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		 $misc_obj=new Gbc_Model_Custom_Miscellaneous();
		 $user_id=$authUserNamespace->user_id;
		 $data1=$misc_obj->GetAccessRightByUserId('16',$user_id);
		 if(!empty($data1['view']) && $data1['view']==1 || (($authUserNamespace->user=="admin")))
		 {

		 }
		 else
		 {
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
			}

			$pendcontractsObj = new Gbc_Model_DbTable_Invoices();

			try{

			//dashboard
			$this->_helper->layout()->setLayout("admindashbord");



	 	$result=$pendcontractsObj->fetchAll($pendcontractsObj->select()
	 	->setIntegrityCheck(false)
	 	->from(array('i'=>'invoices'))
	 	->where("1= ?",1 and invoice_status!=2)
	 	->where("invoice_status!= ?",2));

	 	$this->view->result=$result;

	 	}
	 	catch(Exception $e)
	 	{
			echo $e->getMessage();exit;
			}





			$this->view->title="Gainbitcoin - Contract Invoices";
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			$user_id=$authUserNamespace->user_id;
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
			$kitupdateObj = new Gbc_Model_DbTable_Kits();
			$kitinvoicesObj = new Gbc_Model_DbTable_Kitinvoices();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			try{

	 	$this->_helper->layout()->setLayout("admindashbord");//dashboard
	 	// ->joinLeft(array('kits'=>'kits'),"kits.invoice_id = kit_invoices.invoice_id",array('kits.comment as comments'))


	 	$result=$kitinvoicesObj->fetchAll($kitinvoicesObj->select()
	 	->setIntegrityCheck(false)
	 	->from(array('kit_invoices'=>'kit_invoices'),array('kit_invoices.username','kit_invoices.invoice_id','kit_invoices.contract_rate','kit_invoices.amtPaid','kit_invoices.transactionid','kit_invoices.comment','kit_invoices.invoice_status'))
	 	->joinLeft(array('kits_payments'=>'kits_payments'),"kits_payments.invoice_id = kit_invoices.invoice_id",array('kits_payments.payment_mode'))
	 	->joinLeft(array('kits'=>'kits'),"kits.invoice_id = kit_invoices.invoice_id",array('kits.comment as comments','locked','kit_number'))
	 	->where("kit_invoices.contract_rate!= ?",0)
	 	->group("kit_invoices.invoice_id")
	 	->order("kit_invoices.invoice_id DESC")
	 	);*/
		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			$user_id=$authUserNamespace->user_id;
			$data1=$misc_obj->GetAccessRightByUserId('16',$user_id);
			$antixss = new Gbc_Model_Custom_StringLimit();
			$this->_helper->layout()->setLayout("admindashbord");
			if(!empty($data1['view']) && $data1['view']==1 || (($authUserNamespace->user=="admin")))
			{

			}
			else
			{
				$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
				$this->_redirect("/Admindashboard");
			}
			$MaxRowsCount=100;

			$table = "invoices";
			$whereQuery = "where invoice_status=1";
			$UserCountQuery = "select * from $table ";
			if(!empty($_POST['search'])){
				$searchQuery = $_POST['search'];
				$whereQuery .= " and invoices.username = '$searchQuery'";
			}
			$UserCountQuery .= $whereQuery;
			$UserCountQuery .= " limit $MaxRowsCount,1";

			$RowCountRes=$DB->query($UserCountQuery);
			$UserCountResult = $RowCountRes->fetchAll();

			$UserCountQuery = "SELECT count(*) as count FROM $table ";
			$UserCountQuery .= $whereQuery;

			$UserCountRes=$DB->query($UserCountQuery);
			$UserCountResult=$UserCountRes->fetchAll();
			$UserCount=$UserCountResult[0]['count'];


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
			// echo $pages;

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
			// echo $startLimit;
			// exit;
			if(!empty($PaginateLimit) && !empty($startLimit)){
				$Limit = "limit ".$startLimit.", ".$PaginateLimit;
			}else if(!empty($PaginateLimit)){
				$Limit = "limit ".$PaginateLimit;
			}else{
				$Limit ="";
			}

			if(!empty($_POST['search'])){
				foreach($_POST as $key => $value)
				{

					if(isset($value) && $value != ""){
						$antixss->setEncoding($value, "UTF-8");
						if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

							$this->_redirect("/Profileerror/errormsg");



						}

					}

				}
				$searchQuery = $_POST['search'];
					
				// $allKitInvoicesQuery = "SELECT kit_invoices.username,kit_invoices.invoice_id,kit_invoices.contract_rate,kit_invoices.amtPaid,kit_invoices.transactionid,kit_invoices.comment,kits.comment as comments,kit_invoices.invoice_status,kits_payments.payment_mode FROM kit_invoices left join kits_payments on kits_payments.invoice_id = kit_invoices.invoice_id left join kits on kits.invoice_id = kit_invoices.invoice_id where kit_invoices.username = '$searchQuery' and kit_invoices.contract_rate <> 0 group by kit_invoices.invoice_id ORDER BY kit_invoices.invoice_id DESC $Limit";
					
				$allInvoicesQuery = "SELECT * from invoices where 1 = 1 AND invoice_status = 1  $Limit";
			}else{
				// $allKitInvoicesQuery = "SELECT kit_invoices.username,kit_invoices.invoice_id,kit_invoices.contract_rate,kit_invoices.amtPaid,kit_invoices.transactionid,kit_invoices.comment,kit_invoices.invoice_status,kits_payments.payment_mode FROM kit_invoices left join kits_payments on kits_payments.invoice_id = kit_invoices.invoice_id where kit_invoices.contract_rate <> 0 group by kit_invoices.invoice_id ORDER BY kit_invoices.invoice_id DESC $Limit";
				$allInvoicesQuery = "SELECT * from invoices where 1 = 1 AND invoice_status = 1  $Limit";
			}
			$allInvoicesRes=$DB->query($allInvoicesQuery);
			$allInvoicesResult=$allInvoicesRes->fetchAll();
			//echo "<pre>";
			//print_r($allKitInvoicesResult);exit;
			;

			$this->view->result=$allInvoicesResult;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}

	}

	public function changecontractAction()
	{
			
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$invoice_obj=new Gbc_Model_DbTable_Invoices();
		$antixss = new Gbc_Model_Custom_StringLimit();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();


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
		$invoiceid=$_POST['invoice_id'];
		$invoicestatus=$_POST['invoice_status'];
		$token=$_POST['token'];

	//	if($authUserNamespace->token==$token){
			if($invoicestatus =='1'){
				$invoicestatus="0";
			}else{
		 	$invoicestatus="1";
			}

			$updatestatusdata=array("invoice_status"=>$invoicestatus);

			$updatestatus=$invoice_obj->update($updatestatusdata,$DB->quoteInto("invoice_id=?",$_POST['invoice_id']));
				
			if(!empty($updatestatus)){

				$this->_redirect("/Pendcontracts");
			}
	/*	}

		else{
			//$data=array('success'=>'','failure'=>'Invalid Request Found.');
			//echo json_encode($data);exit;
			$msg="Invalid Request Found";
			$authUserNamespace->msg=$msg;
			$this->_redirect("/Pendcontracts");
		}
	*/		
			
	}

}
