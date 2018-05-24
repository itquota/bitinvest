<?php
class RefrequestController extends Zend_Controller_Action{

	public function init()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$antixss = new Gbc_Model_Custom_StringLimit();
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			$user_id=$authUserNamespace->user_id;
			$data40=$misc_obj->GetAccessRightByUserId('40',$user_id);
			if((!empty($data40->view) && ($data40->view==1)) || $authUserNamespace->user=='admin')
			{
					
			}
			else
			{

				$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
				$this->_redirect("/Admindashboard");
			}

		 $this->_helper->layout()->setLayout("admindashbord");
		 $this->view->data40=$data40;
		 if($this->_request->isXmlHttpRequest()){
		 	$_POST = ($_POST);
		 	$authUserNamespace->filter_by = $_POST;
		 	$SearchResult = $common_obj->getAllRefundRequests($_POST,$PaginateLimit);
		 	$AllRefundRequests = $SearchResult['data'];
		 	// echo $data;
		 	// exit;
		 }else if(!empty($_GET)){
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
		 	// echo urldecode($_GET);
		 	// var_dump($_GET);
		 	// var_dump($_SESSION);
		 	if(!empty($_GET) && !empty($authUserNamespace->filter_by)){
		 		$_GET = ($_GET);
		 		// var_dump($_GET);
		 		$pairs = explode('&',$_GET['search']);
		 		// var_dump($pairs);
		 		foreach($pairs as $pair) {
		 			$part = explode('=', $pair);
		 			$get_parameters[$part[0]] = $part[1];
		 		}
		 		// var_dump($get_parameters);
		 		$authUserNamespace->filter_by = $get_parameters;
		 		$SearchResult = $common_obj->getAllRefundRequests($get_parameters,$PaginateLimit);
		 		$AllRefundRequests = $SearchResult['data'];
		 	}
		 	// echo $data;
		 	// exit;
		 }else{

		 	$data['filter_by']='filter';
		 	$_SESSION['filter_by'] = $data['filter_by'];
		 	$SearchResult = $common_obj->getAllRefundRequests($data,$PaginateLimit);
		 	// $kitDetails = getKitsData($conn,$PaginateLimit,$startLimit);
		 	$AllRefundRequests = $SearchResult['data'];
		 }
		 $this->view->SearchResult=$SearchResult;
		 $this->view->AllRefundRequests=$AllRefundRequests;

		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}


	}

	public function confirmrequestAction()
	{
		try {
				
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			
			$user_id=$authUserNamespace->user_id;
			$data37 = $common_obj->GetAccessRightByUserId('37',$user_id);
			$data40 = $common_obj->GetAccessRightByUserId('40',$user_id);
			if((!empty($data37->edit) && ($data37->edit==1)) || (!empty($data40->edit) && ($data40->edit==1)) || $authUserNamespace->user=='admin')
			{
					
				$claimed_offers_obj = new Gbc_Model_DbTable_Claimedoffers();
				$user = $authUserNamespace->user;
				//$msg="";
				$m='08';
				$months = array (1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December');
				//if(!empty($_GET['claimed_request']) ){
				if(!empty($_POST['claimed_request']) && ((!empty($data37->edit) && ($data37->edit==1)) || $authUserNamespace->user=='admin')){
					
					$_POST = ($_POST);
					$offer_id = $_POST['claimed_request'];
					$OfferId = substr($offer_id,6,-6);
					$ip=$misc_obj->get_client_ip();
					if($_POST['status'] == 2){
						$upd_arr=array('status'=>2,'ip_address2'=>$ip,'updated_by'=>$user,'updated_on'=>new Zend_Db_Expr('NOW()'));
						$upd_qry=$claimed_offers_obj->update($upd_arr,$DB->quoteInto("id = ?",$OfferId));
						if(!empty($upd_qry))
						{
							$msg=array('success'=>'success','Failure'=>'');
							echo  json_encode($msg);exit;
						}
						else
						{
							
							$msg=array('success'=>'','Failure'=>'Failure');
							echo  json_encode($msg);exit;
						}

					}
					else {
						$achiever_obj=new Gbc_Model_DbTable_Achiever();
						$upd_arr=array('status'=>1,'ip_address2'=>$ip,'updated_by'=>$user,'updated_on'=>new Zend_Db_Expr('NOW()'));
						$upd_qry=$claimed_offers_obj->update($upd_arr,$DB->quoteInto("id = ?",$OfferId));
						$OfferDetails=$claimed_offers_obj->fetchRow($claimed_offers_obj->select()
						->setIntegrityCheck(false)
						->from(array('claimed_offers'=>"claimed_offers"),array('claimed_offers.username'))
						->joinLeft(array('special_offers'=>'special_offers'),"special_offers.id = claimed_offers.offer_id",array('special_offers.pairs', 'special_offers.prize'))
						//->where("claimed_offers.id = '$OfferId'")
						->where("claimed_offers.id = ?",$OfferId)
						
						);

						$paircount = $OfferDetails->pairs;
						$User = $OfferDetails->username;
						$prize = $OfferDetails->prize;
						$date = date('Y-m-d');
						$pieces = explode("-",$date);
						$index = $pieces[1];
						$mdate=($months[$index]);

						$insert_arr=array('username'=>$User,'pairs'=>$paircount,'prize'=>$prize,'month'=>$mdate,'created'=>new Zend_Db_Expr('NOW()'),'updated'=>new Zend_Db_Expr('NOW()'));
						$insert_qry=$achiever_obj->insert($insert_arr);

						if(!empty($insert_qry))
						{
							$msg=array('success'=>'success','Failure'=>'');
							echo  json_encode($msg);exit;
						}
						else
						{
							$msg=array('success'=>'','Failure'=>'Failure');
							echo  json_encode($msg);exit;
						}
					}
				}
				else if(!empty($_POST['refund_request']) && ((!empty($data40->edit) && ($data40->edit==1)) || $authUserNamespace->user=='admin')){
					//else if(!empty($_GET['refund_request'])&& ((!empty($data40->edit) && ($data40->edit==1)) || $authUserNamespace->user=='admin')){
				 
					$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
					//$_POST = ($_POST);
					//print_r($_POST);
					$refund_id = $_POST['refund_request'];
					$status = $_POST['status'];
					$comment = $_POST['comment'];
					$RefundId = substr($refund_id,6,-6);

					$ip=$misc_obj->get_client_ip();

					if($status == 2){
						$RefundRequestStatusquery = "update refund_requests set refund_requests.status = 2, refund_requests.ip_address2= '$ip', refund_requests.updated_by = '$user', refund_requests.comment = '$comment', refund_requests.updated_on = now() where refund_requests.id = '$RefundId' ";
					}else{
						$RefundRequestStatusquery = "update refund_requests left join invoices on (invoices.use_kit_number = refund_requests.kit_number or invoices.invoice_id = refund_requests.invoice_id) left join kits on (kits.kit_number = refund_requests.kit_number or kits.kit_number = invoices.use_kit_number) set refund_requests.status = 3, refund_requests.ip_address2= '$ip', refund_requests.updated_by = '$user', refund_requests.updated_on = now(), invoices.invoice_status = 2, kits.status = 'Refund in Progress' where refund_requests.id = '$RefundId' ";
					}
					$AllUserData = $DB->query($RefundRequestStatusquery);
					// echo $RefundRequestStatusquery;
					$msg=array('success'=>'success','Failure'=>'');
							echo  json_encode($msg);exit;
				 
				}
			}
			else
			{

				$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
				$this->_redirect("/Admindashboard");
			}
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}
}