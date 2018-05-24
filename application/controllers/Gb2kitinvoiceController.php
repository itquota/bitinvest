<?php
class Gb2kitinvoiceController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Kit Invoices";
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
		$kitupdateObj = new Gbc_Model_DbTable_Gb2Kits();
		$kitinvoicesObj = new Gbc_Model_DbTable_Gb2Kitinvoices();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
	
		try{

	 	$this->_helper->layout()->setLayout("admindashbord");//dashboard
	 	// ->joinLeft(array('kits'=>'kits'),"kits.invoice_id = kit_invoices.invoice_id",array('kits.comment as comments'))


	 	/*	 	$result=$kitinvoicesObj->fetchAll($kitinvoicesObj->select()
	 	 ->setIntegrityCheck(false)
	 	 ->from(array('kit_invoices'=>'kit_invoices'),array('kit_invoices.username','kit_invoices.invoice_id','kit_invoices.contract_rate','kit_invoices.amtPaid','kit_invoices.transactionid','kit_invoices.comment','kit_invoices.invoice_status'))
	 	 ->joinLeft(array('kits_payments'=>'kits_payments'),"kits_payments.invoice_id = kit_invoices.invoice_id",array('kits_payments.payment_mode'))
	 	 ->joinLeft(array('kits'=>'kits'),"kits.invoice_id = kit_invoices.invoice_id",array('kits.comment as comments','locked','kit_number'))
	 	 ->where("kit_invoices.contract_rate!= ?",0)
	 	 ->group("kit_invoices.invoice_id")
	 	 ->order("kit_invoices.invoice_id DESC")
	 	 );*/
	 	$MaxRowsCount=100;

			//$table = "kit_invoices";
			$data = $kitinvoicesObj->select()
			->from(array('kit_invoices'=>'gb2_kit_invoices'),array('count(invoice_id) as total_count'))
			->where("kit_invoices.contract_rate <>?", '0');
			//$whereQuery = "where kit_invoices.contract_rate <> 0";
			//$UserCountQuery = "select invoice_id from $table ";
			if(!empty($_POST['search'])){

				$searchQuery = $_POST['search'];
				//$whereQuery .= " and kit_invoices.username = '$searchQuery'";
				$data->where('kit_invoices.username = ?',$searchQuery);
			}
			//$UserCountQuery .= $whereQuery;
			//$UserCountQuery .= " limit $MaxRowsCount,1";

			//$RowCountRes=$DB->query($UserCountQuery);
			//$UserCountResult = $RowCountRes->fetchAll();

			//$UserCountQuery = "SELECT count(*) as count FROM $table ";
			//$UserCountQuery .= $whereQuery;

			/*$UserCountRes=$DB->query($UserCountQuery);
			 $UserCountResult=$UserCountRes->fetchAll();
			 $UserCount=$UserCountResult[0]['count'];*/
			//echo "inside";exit;
			$UserCountResult = $kitinvoicesObj->fetchRow($data);
			$UserCount = ($UserCountResult->total_count);
			//	print_r($UserCountResult);

			if(!empty($PaginateLimit))
			{
				$PaginateLimit=$PaginateLimit;
			}
			else
			{
				$PaginateLimit=10;
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
				$startLimit=($_GET['page']-1)*$PaginateLimit;
			}
			else
			{
				$startLimit=0;
			}
			// echo $startLimit;
			// exit;
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
					
				// $allKitInvoicesQuery = "SELECT kit_invoices.username,kit_invoices.invoice_id,kit_invoices.contract_rate,kit_invoices.amtPaid,kit_invoices.transactionid,kit_invoices.comment,kits.comment as comments,kit_invoices.invoice_status,kits_payments.payment_mode FROM kit_invoices left join kits_payments on kits_payments.invoice_id = kit_invoices.invoice_id left join kits on kits.invoice_id = kit_invoices.invoice_id where kit_invoices.username = '$searchQuery' and kit_invoices.contract_rate <> 0 group by kit_invoices.invoice_id ORDER BY kit_invoices.invoice_id DESC $Limit";
			/*
				$result = $kitinvoicesObj->select()
				->setIntegrityCheck(false)
				->from(array('kit_invoices'=>'kit_invoices'),array('kit_invoices.username','kit_invoices.invoice_id','kit_invoices.contract_rate','kit_invoices.amtPaid','kit_invoices.transactionid','kit_invoices.comment','(select comment from kits where kits.invoice_id = kit_invoices.invoice_id limit 1 ) as comments','kit_invoices.invoice_status','(select payment_mode from kits_payments where kits_payments.invoice_id = kit_invoices.invoice_id) as payment_mode'))
				->joinLeft(array('kits_payments'=>'kits_payments'),"kits_payments.invoice_id = kit_invoices.invoice_id",array('kits_payments.payment_mode'))
				->joinLeft(array('kits'=>'kits'),"kits.invoice_id = kit_invoices.invoice_id",array('kits.comment as comments','locked','kit_number'))
				->where("kit_invoices.username = ?",$searchQuery)
				->where("kit_invoices.contract_rate <> ?",'0')
				->group("kit_invoices.invoice_id")
				->order("kit_invoices.invoice_id DESC");
			*/


				$result = $kitinvoicesObj->select()
				->setIntegrityCheck(false)
				->from(array('kit_invoices'=>'gb2_kit_invoices'),array('kit_invoices.username','kit_invoices.middleAddr', 'kit_invoices.kits_qty', 'kit_invoices.creared_on as created_on', 'kit_invoices.invoice_id', 'kit_invoices.contract_rate','kit_invoices.amtPaid',  'kit_invoices.transactionid', 'kit_invoices.comment', 'kit_invoices.invoice_status'
		/*,'(select payment_mode from kits_payments where kits_payments.invoice_id = kit_invoices.invoice_id group by invoice_id) as payment_mode'*/
		))
				->joinInner(array('k1'=>'gb2_kits'),'(k1.invoice_id=kit_invoices.invoice_id)',array('k1.contract_id', 'k1.comment as comments'))
				->joinLeft(array('contracts' => 'gb2_contracts'),'(contracts.contract_id = k1.contract_id)',array('contracts.total_price'))	
				->where("kit_invoices.username = ?",$searchQuery)
				->ORwhere("kit_invoices.invoice_id = ?",$searchQuery)
				->ORwhere("kit_invoices.middleAddr LIKE ?",'%'.$searchQuery.'%')
				->ORwhere("kit_invoices.comment LIKE ?",'%'.$searchQuery.'%')
				->where("kit_invoices.contract_rate <> ?",'0')
				->group("kit_invoices.invoice_id")
				->order("kit_invoices.invoice_id DESC");
		
		
					
				if(!empty($PaginateLimit) && !empty($startLimit))
				{
					$result->limit($PaginateLimit,$startLimit);
				}
				else if(!empty($PaginateLimit)){
					$result->limit($PaginateLimit);
				}


				//$allKitInvoicesQuery = "SELECT kit_invoices.username,kit_invoices.invoice_id,kit_invoices.contract_rate,kit_invoices.amtPaid,kit_invoices.transactionid,kit_invoices.comment,(select comment from kits where kits.invoice_id = kit_invoices.invoice_id limit 1 ) as comments,kit_invoices.invoice_status,(select payment_mode from kits_payments where kits_payments.invoice_id = kit_invoices.invoice_id) as payment_mode  FROM kit_invoices where kit_invoices.username = '$searchQuery' and kit_invoices.contract_rate <> 0 group by kit_invoices.invoice_id ORDER BY kit_invoices.invoice_id DESC $Limit";
			}else{

				$result = $kitinvoicesObj->select()
				->setIntegrityCheck(false)
				->from(array('kit_invoices'=>'gb2_kit_invoices'), array('kit_invoices.username',  'kit_invoices.middleAddr', 'kit_invoices.kits_qty', 'kit_invoices.creared_on as created_on', 'kit_invoices.invoice_id', 'kit_invoices.contract_rate', 'kit_invoices.amtPaid', 'kit_invoices.transactionid', 'kit_invoices.comment', 'kit_invoices.invoice_status'))
				 ->joinInner(array('k1'=>'gb2_kits'),'(k1.invoice_id=kit_invoices.invoice_id)',array('k1.contract_id', 'k1.comment as comments'))
				->joinLeft(array('contracts' => 'contracts'),'(contracts.contract_id = k1.contract_id)',array('contracts.total_price'))
				->where("kit_invoices.contract_rate <> ?",'0')
				->group("kit_invoices.invoice_id")
				->order("kit_invoices.invoice_id DESC");
					
			
				if(!empty($PaginateLimit) && !empty($startLimit))
				{
					$result->limit($PaginateLimit,$startLimit);
				}
				else if(!empty($PaginateLimit)){
					$result->limit($PaginateLimit);
				}
				//	echo	$result->__toString();
		//exit;

				// $allKitInvoicesQuery = "SELECT kit_invoices.username,kit_invoices.invoice_id,kit_invoices.contract_rate,kit_invoices.amtPaid,kit_invoices.transactionid,kit_invoices.comment,kit_invoices.invoice_status,kits_payments.payment_mode FROM kit_invoices left join kits_payments on kits_payments.invoice_id = kit_invoices.invoice_id where kit_invoices.contract_rate <> 0 group by kit_invoices.invoice_id ORDER BY kit_invoices.invoice_id DESC $Limit";
				//$allKitInvoicesQuery = "SELECT kit_invoices.username,kit_invoices.invoice_id,kit_invoices.contract_rate,kit_invoices.amtPaid,kit_invoices.transactionid,kit_invoices.comment,kit_invoices.invoice_status FROM kit_invoices where kit_invoices.contract_rate <> 0 group by kit_invoices.invoice_id ORDER BY kit_invoices.invoice_id DESC $Limit";
			}

			$allKitInvoicesRes = $kitinvoicesObj->fetchAll($result);
					//	print_r($allKitInvoicesRes);exit;
			
			/*$allKitInvoicesRes=$DB->query($allKitInvoicesQuery);
			 $allKitInvoicesResult=$allKitInvoicesRes->fetchAll();*/
			//echo "<pre>";
			//print_r($allKitInvoicesResult);exit;
			if(isset($allKitInvoicesRes) && sizeof($allKitInvoicesRes)>0)
			{
				$allKitInvoicesResult = $allKitInvoicesRes->toArray();
			}
			else
			{
				$allKitInvoicesResult = array();
			}


	 	$this->view->result=$allKitInvoicesResult;

	 	//echo "<pre>";
	 //	print_r($allKitInvoicesResult);exit;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}


	}

}
