<?php
class ChangelockstatusController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$invoices_obj=new Gbc_Model_DbTable_Invoices();
			$finalledger_obj=new Gbc_Model_DbTable_FinalLedger();
			$kits_obj=new Gbc_Model_DbTable_Kits();
			$antixss = new Gbc_Model_Custom_StringLimit();
			$user=$authUserNamespace->user;

			$FinalLedgerDatesArray=array();

			if(!empty($_GET) && !empty($_GET['kitno'])){
				foreach($_GET as $key => $value)
		 	{

		 		//if($key!='user'){
					if(isset($value) && $value != ""){
						$antixss->setEncoding($value, "UTF-8");
						if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

							//$data=array('success'=>'','failure'=>'Invalid Input.');
							//echo json_encode($data);exit;
							//$msg="Invalid Input";
							//$authUserNamespace->msg=$msg;
							$this->_redirect("/Profileerror/errormsg");

						}

					}
					//}
		 	}
				//$changeLock = $_GET['lock_value'] == 1?0:1;
				$changeLock = $_GET['lock'];

				if($changeLock==1)
				{
					$changeLock=0;
				}
				else {
					$changeLock=1;
				}


				$kit_number = $_GET['kitno'];
					

				//$invoice_id = mysql_query("select invoice_id from invoices where use_kit_number = '$kit_number' ");
				$invoiceresult=$invoices_obj->fetchRow($invoices_obj->select()
				->setIntegrityCheck(false)
				->from(array('i'=>"invoices"),array('invoice_id'))
				->where("use_kit_number= ?",$kit_number));

				//$invoice_id = mysql_result($invoice_id,0);
				if(!empty($invoiceresult) && sizeof($invoiceresult)>0)
				{

					$invoice_id =$invoiceresult->invoice_id;

				}
				//$invoice_id ='1';

				if(!empty($invoiceresult) && isset($_POST['lock']))
				{

					//$InvoiceData = mysql_query("select invoices.username,  invoices.contract_rate, invoices.created_on, invoices.updated_on, binary_network_details.depth from invoices left join binary_network_details on binary_network_details.username = invoices.username where invoices.invoice_id = '$invoice_id'");
					$InvoiceData=$invoices_obj->fetchRow($invoices_obj->select()
					->setIntegrityCheck(false)
					->from(array('i'=>"invoices"),array('i.username','i.contract_rate','i.created_on','i.updated_on'))
					->joinLeft(array('b'=>"binary_network_details"),"b.username = i.username",array('b.depth'))
					->where("i.invoice_id= ?",$invoice_id));

					if(!empty($InvoiceData) && sizeof($InvoiceData)>0)
					{
						$InvoiceUser = $InvoiceData->username;
						$start = $InvoiceData->created_on;

						$depth = $InvoiceData->depth;

						$depth = str_replace("'","",$depth);

						$contract_rate = $InvoiceData->contract_rate;

						//$FinalLedgerDates = mysql_query("SELECT created_on FROM `final_ledger` group by date(created_on) order by created_on desc limit 2");

						$FinalLedgerDates=$finalledger_obj->fetchAll($finalledger_obj->select()
						->from(array('f'=>"final_ledger"),array('created_on'))
						->group("date(created_on)")
						->order("created_on DESC")
						->limit(2));

						for($i=0;$i<sizeof($FinalLedgerDates);$i++)
						{

							$FinalLedgerDatesArray[] = $FinalLedgerDates[$i]['created_on'];

						}
							
						/*echo "<pre>";
						 print_r($FinalLedgerDatesArray);exit;*/
							
						if($start < $FinalLedgerDatesArray[1]){

							$msg = "Invoice Can't be locked";
							$this->view->msg=$msg;

						}else{

							//$changeLockStatus = changeInvoiceLock($user,$InvoiceUser,$kit_number,$changeLock,$_GET['lock_value'],$invoice_id,$contract_rate,$depth,$start,$FinalLedgerDatesArray[0],$conn);

							$common_obj = new Gbc_Model_Custom_CommonFunc();
							$changeLockStatus=$common_obj->changeInvoiceLock($user,$InvoiceUser,$kit_number,$changeLock,$_POST['lock'],$invoice_id,$contract_rate,$depth,$start,$FinalLedgerDatesArray[0]);

						}

					}
				}
				else
				{
					$upd_arr=array('locked'=>$changeLock,'updated_on'=>new Zend_Db_Expr('NOW()'));
					$upd_qry=$kits_obj->update($upd_arr,"kit_number = '$kit_number'");
				}
				$this->_redirect("/Kitinvoice");

			}
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}

	}


}