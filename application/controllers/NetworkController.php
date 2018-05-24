<?php
class NetworkController extends Zend_Controller_Action{

	public function init(){

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" ){
			$this->_redirect("/Login");
		}


	}

	public function indexAction(){
		try
		{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$antixss = new Gbc_Model_Custom_StringLimit();
			if ($authUserNamespace->user_type =='binary')
			$this->_helper->layout()->setLayout("dashbord");//dashboard
			else
			$this->_helper->layout()->setLayout("admindashbord");//dashboard

			foreach($_GET as $key => $value)
			{


				//if($key!='user'){
				if(isset($value) && $value != ""){

					if($antixss->setFilter($value, "black", "string") == "invalidInput"){

						$this->_redirect("/Profileerror/errormsg");

					}
						
				}
				//}

			}

			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$username=$_GET['usr'];
			$this->view->username=$username;
			/*$url= BASE."/Networkapi?username=".$username;
			 $result=$common_obj->call_curl($url);

			 $tree=(array)json_decode($result,true);*/
				
			//$tree = $tree->toArray();
			$users[]= $username;

			$UserData =$common_obj-> checkNetworkDetails($username,2);

			$all_user=array();
			if(isset($UserData) && sizeof($UserData)>0){
				// echo "yes";
				$all_user = $UserData;
				//$all_user = $common_obj->leftRightDetails($users,$NetworkUsers,$count);
			}else{
				$UserData = $bin_net_details_obj->fetchAll($bin_net_details_obj->select()
				);
				if(!empty($UserData) && sizeof($UserData)>0)
				{
					for($i=0;$i<sizeof($UserData);$i++)
					{
						$NetworkUsers[] = $UserData[$i]['username'];
					}
				}

				$all_user = $common_obj->leftRightDetails($users,$NetworkUsers,$count);
			}
				
			if(!empty($all_user) && sizeof($all_user)>0)
			{
				$dataarr=array('leftContracts'=>$all_user['leftContracts'],'totalLeftUsers'=>$all_user['totalLeftUsers'],'activeLeftUsers'=>$all_user['activeLeftUsers'],'inactiveLeftUsers'=>$all_user['inactiveLeftUsers'],'rightContracts'=>$all_user['rightContracts'],'totalRightUsers'=>$all_user['totalRightUsers'],'activeRightUsers'=>$all_user['activeRightUsers'],'inactiveRightUsers'=>$all_user['inactiveRightUsers']);
			}
			else
			{
				$dataarr=array();
			}
			//echo "<pre>";
			//print_r($tree['data']['binarydata']);exit;
			$this->view->userdata=$dataarr;
			//$this->view->binarydata=$tree['data']['binarydata'] ;
			$this->view->title="Gainbitcoin - Binary Network";
				
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
		//echo "<pre>";
		//print_r($tree);exit;


	}
	public function searchbyuserAction()
	{

		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$common_obj=new Gbc_Model_Custom_CommonFunc();
			$usr=$common_obj->cleanQueryParameter(($_POST['usr']));
			$level=$common_obj->cleanQueryParameter(($_POST['level']));
			$token=$common_obj->cleanQueryParameter(($_POST['token']));
			/*if($authUserNamespace->token==$token)
			 {*/
			$search_by_user=$common_obj->cleanQueryParameter(($_POST['search_by_user']));

			$search_by_level=$common_obj->cleanQueryParameter(($_POST['search_by_level']));

			//$common_obj->cleanQueryParameter(($_POST['username']));

			if(!empty($usr) && !empty($level) && !empty($search_by_user)){

				$SearchUsers = $common_obj->SearchUsersByLevel((trim($usr)),(trim($level)));
				// print_r($SearchUsers);
					
				echo "<option value = ''>Search by User</option>";
				foreach($SearchUsers as $SearchUser){
					echo "<option value= '".$SearchUser['name']."'>".$SearchUser['name']."</option>";
				}
				exit;
			}

			else if(!empty($usr) && !empty($search_by_level)){
				$user = urlencode($usr);
				$user =str_replace('+', '', $user);
				//echo $user;exit;
				$bin_net_details_obj=new Gbc_Model_DbTable_Binarynetworkdetail();
				/*$CheckLevels = $bin_net_details_obj->fetchAll($bin_net_details_obj->select()
				 ->where("username = '".$user."' "));*/
				/*echo $bin_net_details_obj->select()
				 ->where("username = '".$user."' "));*/
				$CheckLevels = $bin_net_details_obj->fetchAll($bin_net_details_obj->select()
				->where("username = ?",$user));
					
				$CheckLevelsCount = sizeof($CheckLevels);
					
				if($CheckLevelsCount>0){
					$maxLevels = $CheckLevels[0]['network_levels'];
				}else{
					$levels = $common_obj->CountNetworklevel($user);
					$maxLevels = max($levels);
				}
				// var_dump($levels);
					
				echo "<option value = ''>Search by Level</option>";
				for($i=1;$i<=$maxLevels;$i++){
					echo "<option value= '$i'>".$i."</option>";
				}
					
				exit;
			}
			/*}
			 else
			 {
				$data=array('success'=>'','failure'=>'Invalid Request Found.');
				echo json_encode($data);exit;
				}*/
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}
	public function getdatafornetworkAction()
	{
		try
		{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');


			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$username = $common_obj->cleanQueryParameter($_POST['username']);
			$this->view->username=$username;
			//$url= BASE."/Binarynetworkapi?username=".$username;
			//$result=$common_obj->call_curl($url);
			$users[]= $username;

			$UserData =$common_obj-> checkNetworkDetails($username,2);

			$all_user=array();
			if(isset($UserData) && sizeof($UserData)>0){
				// echo "yes";
				$all_user = $UserData;
				//$all_user = $common_obj->leftRightDetails($users,$NetworkUsers,$count);
			}else{
				$UserData = $bin_net_details_obj->fetchAll($bin_net_details_obj->select()
				);
				if(!empty($UserData) && sizeof($UserData)>0)
				{
					for($i=0;$i<sizeof($UserData);$i++)
					{
						$NetworkUsers[] = $UserData[$i]['username'];
					}
				}

				$all_user = $common_obj->leftRightDetails($users,$NetworkUsers,$count);
			}
				
			if(!empty($all_user) && sizeof($all_user)>0)
			{
				$dataarr=array('leftContracts'=>$all_user['leftContracts'],'totalLeftUsers'=>$all_user['totalLeftUsers'],'activeLeftUsers'=>$all_user['activeLeftUsers'],'inactiveLeftUsers'=>$all_user['inactiveLeftUsers'],'rightContracts'=>$all_user['rightContracts'],'totalRightUsers'=>$all_user['totalRightUsers'],'activeRightUsers'=>$all_user['activeRightUsers'],'inactiveRightUsers'=>$all_user['inactiveRightUsers']);
			}
			else
			{
				$dataarr=array();
			}

			echo json_encode($dataarr);exit;
				
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}
	public function refreshnetworkAction()
	{

		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$bin_net_det_obj = new Gbc_Model_DbTable_Binarynetworkdetail();
		$username=$common_obj->cleanQueryParameter($_POST['user']);
		//$UserData = $bin_net_det_obj->fetchAll($bin_net_det_obj->select()
		//);
		$users[]=$username;
		$count = 0;
		/*if(!empty($UserData) && sizeof($UserData)>0)
		 {
			for($i=0;$i<$UserData;$i++)
			{
			$NetworkUsers[] = $UserData[$i]['username'];
			}
			}*/
		$users[]= $username;

		$UserData =$common_obj-> checkNetworkDetails($username,2);

		$all_user=array();
		if(isset($UserData) && sizeof($UserData)>0){
			// echo "yes";
			$all_user = $UserData;
			//$all_user = $common_obj->leftRightDetails($users,$NetworkUsers,$count);
		}
		else {
			$NetworkUsers=array();
			$all_users=$common_obj->leftRightDetails($users,$NetworkUsers,$count);
				
			if(empty($all_users) || sizeof($all_users)<=0)
			{
				$all_users=array();
			}
		}
		echo json_encode($all_users);exit;
	}

}