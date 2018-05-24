<?php
class KitauditController extends Zend_Controller_Action{

	public function init()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('35',$user_id);
		$antixss = new Gbc_Model_Custom_StringLimit();

		if((!empty($data1->view) && ($data1->view==1)) || $authUserNamespace->user=='admin')
		{

		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}

		$reportsObj = new Gbc_Model_DbTable_FinalBalance();
			
		try{

		 $this->_helper->layout()->setLayout("admindashbord");
		 $PaginateLimit=100;
		 $kit_status=array('active','used');
		 $kits_obj=new Gbc_Model_DbTable_Kits();
		 $Kits_data=$kits_obj->fetchRow($kits_obj->select()
		 ->setIntegrityCheck(false)
		 ->from(array('k'=>"kits"),array('count(id) as count'))
		 ->joinInner(array('k1'=>'kit_invoices'),"k.invoice_id=k1.invoice_id")
		 ->where("k.status IN (?)", $kit_status)
		 );



		 if(!empty($Kits_data) && sizeof($Kits_data)>0)
		 {
		 	$RowCount = $Kits_data->count;
		 }
		 else
		 {
		 	$RowCount=0;
		 }
			$pages = ceil($RowCount/$PaginateLimit);
			//$startLimit = !empty($_GET['page'])?$_GET['page']*$PaginateLimit:0;
			if(!empty($_GET['page']) && $_GET['page']!='')
			{
				$value = $_GET['page'];
				$antixss->setEncoding($_GET['page'], "UTF-8");
				if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
					$this->_redirect("/Profileerror/errormsg");

				}
				$startLimit = $_GET['page']*$PaginateLimit;
			}
			else
			{
				$startLimit = 0;
			}

			if(!empty($_POST)){


				foreach($_POST as $key => $value)
				{
					//if($key!='user'){
					if(isset($value) && $value != ""){
						$antixss->setEncoding($value, "UTF-8");
						if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

							//$data=array('success'=>'','failure'=>'Invalid Input.');
							//echo json_encode($data);exit;
							//$msg="Invalid Request Found.";
							//$authUserNamespace->msg=$msg;
							$this->_redirect("/Profileerror/errormsg");

						}

					}
					//}
				}

					
				$_POST = $common_obj->cleanQueryParameter($_POST);
				$authUserNamespace->filter_by = $_POST;

				$token=$_POST['token'];
		//		if($authUserNamespace->token==$token){

					$SearchResult = $common_obj->getAllUserReportsForKitAudit($_POST,$PaginateLimit);
					$kitDetails = $SearchResult['data'];
		/*		}
				else
				{
					//$data=array('success'=>'','failure'=>'Invalid Request Found.');
					//echo json_encode($data);exit;

					$msg="Invalid Request Found.";
					$authUserNamespace->msg=$msg;
				} */

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
				if(!empty($_GET) && !empty($authUserNamespace->filter_by)){
					$_GET = $common_obj->cleanQueryParameter($_GET);
					// var_dump($_GET);
					$pairs = explode('&',$_GET['search']);
					// var_dump($pairs);
				 foreach($pairs as $pair) {
				 	$part = explode('=', $pair);
				 	$get_parameters[$part[0]] = $part[1];
				 }
				 // var_dump($get_parameters);
				 $authUserNamespace->filter_by = $get_parameters;
				 $SearchResult = $common_obj->getAllUserReportsForKitAudit($get_parameters,$PaginateLimit);
				 $kitDetails = $SearchResult['data'];
				}
				// echo $data;
				// exit;
			}else{
				$data['filter_by']='filter';
				$authUserNamespace->filter_by = $data['filter_by'];
				$SearchResult = $common_obj->getAllUserReportsForKitAudit($data,$PaginateLimit);
				// $kitDetails = getKitsData($conn,$PaginateLimit,$startLimit);
				$kitDetails = $SearchResult['data'];
			}

			$this->view->kitDetails=$kitDetails;
			$this->view->SearchResult=$SearchResult;
			//echo "<pre>";
			//print_r($kitDetails);exit;

		}

		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}


	}
	public function changekitconfirmationAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$user_id = $authUserNamespace->user_id;
		$user = $authUserNamespace->user;
		$kit_conf = new Gbc_Model_DbTable_Kitconf();
		$data = $common_obj->GetAccessRightByUserId('35',$user_id);
		if((!empty($data->edit) && ($data->edit==1)) || $authUserNamespace->user=='admin')
		{
			$token=$_POST['token'];
	//  if($authUserNamespace->token==$token)  {
	  	$ip=$misc_obj->get_client_ip();
	  	$kitNo = $common_obj->cleanQueryParameter($_POST['kitNo']);
	  	$status = $common_obj->cleanQueryParameter($_POST['status']);

	  	$Kits_data=$kit_conf->fetchRow($kit_conf->select()
	  	->where("kit_no=?", $kitNo)
	  	);
	  	if(empty($Kits_data) || sizeof($Kits_data)<=0)
	  	{
	  		$insert_arr = array('kit_no'=>$kitNo,'status'=>$status,'updated_by'=>$user,'ip_address'=>$ip);
	  		$insert_data = $kit_conf->insert($insert_arr);
	  		if(!empty($insert_data)){
	  			//echo "Updated Successfully";exit;
	  			$data=array('success'=>'success','failure'=>'','data'=>'Kit status changed successfully');
	  			echo json_encode($data);exit;
	  		}else{
	  			//echo "Something error";exit;
	  			$data=array('success'=>'','failure'=>'Something error');
	  			echo json_encode($data);exit;
	  		}
	  	}
	  	else
	  	{
	  		$upd_arr = array('status'=>$status,'updated_by'=>$user,'ip_address'=>$ip);
	  		$upd_data = $kit_conf->update($upd_arr,"kit_no='".$kitNo."'");
	  		if(!empty($upd_data)){
	  			//echo "Updated Successfully";exit;
	  			$data=array('success'=>'success','failure'=>'','data'=>'Kit status changed successfully');
	  			echo json_encode($data);exit;
	  		}else{
	  			//echo "Something error";exit;
	  			$data=array('success'=>'','failure'=>'Something error');
	  			echo json_encode($data);exit;
	  		}
	  	}
	  	 

	/*  }
	  else {
				//echo "Invalid Request Found";exit;
				$data=array('success'=>'','failure'=>'Invalid Request Found');
				echo json_encode($data);exit;

			}
*/	
	}
		else
		{
			//echo "You do not have sufficient privileges to access this area.";exit;
			$data=array('success'=>'','failure'=>'You do not have sufficient privileges to access this area');
			echo json_encode($data);exit;
		}
	}


}
