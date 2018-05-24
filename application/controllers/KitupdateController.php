<?php
class KitupdateController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Kit Update";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('6',$user_id);
		$kits_obj=new Gbc_Model_DbTable_Kits();
		$antixss = new Gbc_Model_Custom_StringLimit();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		if((!empty($data1->view) && ($data1->view==1)) || $authUserNamespace->user=='admin')
		{

		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}
		$kitupdateObj = new Gbc_Model_DbTable_Kits();
		$kitinvoicesObj = new Gbc_Model_DbTable_Kitinvoices();

		try{

	 	$this->_helper->layout()->setLayout("admindashbord");//dashboard


	 	/*	 	$result=$kitupdateObj->fetchAll($kitupdateObj->select()
	 	 ->setIntegrityCheck(false)
	 	 ->from(array('k'=>'kits'))
	 	 ->joinInner(array('k1'=>'kit_invoices'),'(k.invoice_id=k1.invoice_id)',array('k1.invoice_status'))
	 	 );*/
	 	$PaginateLimit=100;



	 	$query=$kits_obj->fetchAll($kits_obj->select()
	 	->setIntegrityCheck(false)
	 	->from(array('k'=>"kits"),array('k.username'))
	 	->joinInner(array('k1'=>'kit_invoices'),'(k.invoice_id=k1.invoice_id)',array('k1.invoice_status','k1.comment'))
	 	);

	 	$RowCount = sizeof($query);

	 	/*	$RowCountRes = ("SELECT count(id) as count FROM kits k INNER JOIN kit_invoices k1 ON k.invoice_id=k1.invoice_id ");
	 	 $RowCountResult=$DB->query($RowCountRes);
	 	 $result = $RowCountResult->fetchAll();

	 	 $RowCount = $result[0]['count'];*/
	 	$pages = ceil($RowCount/$PaginateLimit);
	 	if(!empty($_GET))
	 	{
	 		$value = $_GET['page'];
	 		$antixss->setEncoding($_GET['page'], "UTF-8");
	 		if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
				$this->_redirect("/Profileerror/errormsg");
	 			//$data=array('success'=>'','failure'=>'Invalid Input.');
	 			//echo json_encode($data);exit;

	 		}
	 		$startLimit = ($_GET['page']-1)*$PaginateLimit;
			$searchQuery = $_GET['search'];
	 	}
	 	else
	 	{
	 		$startLimit=0;
	 	}
	 	//echo "pages= ".$pages . " RowCount= ".$RowCount." startLimit= ".$startLimit;exit;
	 	$this->view->pages=$pages;

	 	 
	 	foreach($_GET as $key => $value)
	 	{
	 			
	 		if(isset($value) && $value != ""){
	 			$antixss->setEncoding($value, "UTF-8");
	 			if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

	 				$data=array('success'=>'','failure'=>'Invalid Input.');
	 				echo json_encode($data);exit;

	 			}

	 		}
	 			
	 	}

	 	 
	 	 
	 	 
	 	 
	 	 
	 	 
	 	if(!empty($_POST)){
	 		$bflag = 0;
	 		//$antixss = new Gbc_Model_Custom_StringLimit();
	 		foreach($_POST as $key => $value)
	 		{

	 			if(isset($value) && $value != ""){
	 				$antixss->setEncoding($value, "UTF-8");
	 				if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

	 					$bflag = 1;


	 				}

	 			}

	 		}
	 		if($bflag !=1)
	 		{

	 			$token=$_POST['token'];
	 		//	if($authUserNamespace->token==$token)	{
	 				$searchQuery = $common_obj-> cleanQueryParameter($_POST['search']);

	 				if(!empty($searchQuery)){
	 					$kitDetails = $common_obj->getKitsData($PaginateLimit,$startLimit,$searchQuery);
	 				}
	 		/*	}else{
	 				//$data=array('success'=>'','failure'=>'Invalid Request Found.');
	 				//echo json_encode($data);exit;
	 				$msg = 'Invalid Request Found.';
	 				$authUserNamespace->msg=$msg;
	 			} */
	 		}
	 		else
	 		{
	 			$msg = 'Invalid token';
	 			$authUserNamespace->msg=$msg;
	 		}
	 	}
	 	else{

	 		$kitDetails = $common_obj->getKitsData($PaginateLimit,$startLimit,$searchQuery);

	 	}

	 	$this->view->result=$kitDetails;

		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}


	}
}
