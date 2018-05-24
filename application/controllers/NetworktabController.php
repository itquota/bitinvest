<?php
class NetworktabController extends Zend_Controller_Action{

	public function init(){
	

	}

	public function indexAction(){
		try
		{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$this->_helper->layout()->setLayout("dashbord");//dashboard
			$common_obj=new Gbc_Model_Custom_CommonFunc();
				
			$username=$common_obj->cleanQueryParameter(($_POST['usr']));
			//$username=$_POST['usr'];
			$master=$common_obj->cleanQueryParameter(($_POST['master']));
			//$master=$_POST['master'];
			$token=$common_obj->cleanQueryParameter(($_POST['token']));
		//	if($authUserNamespace->token==$token)	{

			$url= BASE."/Btreetableapi?username=".$username;
			$result=$common_obj->call_curl($url);
				
			$url1= BASE."/Dashboard/refreshnetwork?user=".$username;
			$result1=$common_obj->call_curl($url1);
			if(isset($result) && sizeof($result)>0)
			{
				$data=(array)json_decode($result,true);
				//print_r($data);exit;
				$this->view->username=$master;
				$this->view->parent=$username;
				$this->view->binarydata=$data['data']['binarydata'];
				$this->view->userdata=$data['data']['userdata'];
			}
			else{
				//echo "fails";exit;
				$msg = 'fails';
				$authUserNamespace->errmsg=$msg;
			}
	/*	}
			else
			{
				//$data=array('success'=>'','failure'=>'Invalid Request Found.');
				//echo json_encode($data);exit;
				$msg = 'Invalid Request Found.';
				$authUserNamespace->errmsg=$msg;
				
			}  */
			//echo "hi";exit;
			/*if(!empty($_POST['user'])){
			 $SearchParent ='';
			 $Gbc_Model_Custom_func_obj->getAllChildforBinary($username,$SearchParent);
			 // var_dump($SearchParent);
			 if(in_array(strtolower($_POST['user']),array_map('strtolower', $SearchParent))){
				$username = strtolower($_POST['user']);
				}
				}

				$res=array();
				$res=$bin_user_ref->fetchAll($bin_user_ref->select()
				->setIntegrityCheck(false)
				->from(array('a'=>"binary_user_refences"),array('a.id','a.username','a.parent_username','a.parent_id','a.child_position'))
				->joinLeft(array('u'=>'user_info'),"u.username = a.username",array('u.ref_sponsor_id','u.isActiveId','u.lock_status'))
				->joinLeft(array('i'=>'invoices'),"i.username = a.username",array('round(sum(i.contract_rate),2) as ContractPrice','i.created_on','round(sum(i.amtPaid),2) as amtPaid'))
				->where("a.username='$username'")
				);

				$limit = 1;

				if(isset($res) && sizeof($res)>0){
					
				// var_dump($res);
				$res[0]['amtPaid'] = !empty($res[0]['ContractPrice'])?$res[0]['ContractPrice']:'';
				$topmcode =$res[0]['id'];
				$finalRes='';

				if(!empty($topmcode)){
				$Gbc_Model_Custom_func_obj->fetchBTree_new($topmcode,$finalRes,$limit);
				}
				}
				//print_r($finalRes);exit;

				if(!empty($finalRes))
				{
				//printArr($finalRes);


				$a=array('0'=>array('name'=>$username,'parent'=>'null','isactive' => $res[0]['isActiveId'],'lock_status' => $res[0]['lock_status'],'child_position' => '', 'amtpaid' => $res[0]['amtPaid'],'sponsor' => $res[0]['ref_sponsor_id'], 'date' => $res[0]['created_on']));
				$tree=  array_merge($a,$finalRes);
					
				//echo "<pre>";
				//print_r($tree);exit;
				//print_r($json_data);die;
				$master_arr=array();
					
				for($k=0;$k<(sizeof($tree));$k++)
				{

				array_push($master_arr,$tree[$k]['name']);
				}
				//print_r($master_arr);exit;
				//echo (array_search('ops1',$master_arr));exit;
				for($k=0;$k<(sizeof($tree));$k++)
				{
				$tree[$k]['key']=$k+1;
				$index=array_search($tree[$k]['parent'],$master_arr);
				$tree[$k]['parent_id']=$index+1;
				}
					
				//$json_data=json_encode($tree);
					
				}else{
				// $a = "";
				if(!empty($topmcode)){
				$a=array('0'=>array('name'=>$username,'parent'=>'null','isactive' => $res[0]['isActiveId'],'lock_status' => $res[0]['lock_status'],'child_position' => '', 'amtpaid' => $res[0]['amtPaid'],'sponsor' => $res[0]['ref_sponsor_id'], 'date' => $res[0]['created_on'],'key'=>1,'parent_id'=>1));
				}else{
				$a = "";
				}
				//$json_data=json_encode($a);
				//printArr($json_data);die;
				}

				$users[]= $username;

				$UserData =$Gbc_Model_Custom_func_obj-> checkNetworkDetails($username);


				if($UserData){
				// echo "yes";
				$all_user = $UserData;
				}else{
				// echo "else";
				$all_user = $Gbc_Model_Custom_func_obj->checkNetworkDetails($username,1);
				}*/

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
		$common_obj=new Gbc_Model_Custom_CommonFunc();
		if(!empty($_POST['usr']) && !empty($_POST['level']) && !empty($_POST['search_by_user'])){
			$SearchUsers = $common_obj->SearchUsersByLevel($_POST['usr'],$_POST['level']);
			// print_r($SearchUsers);

			echo "<option value = ''>Search by User</option>";
			foreach($SearchUsers as $SearchUser){
				echo "<option value= '".$SearchUser['name']."'>".$SearchUser['name']."</option>";
			}
			exit;
		}

		else if(!empty($_GET['usr']) && !empty($_GET['search_by_level'])){
			$user = ($_GET['usr']);

			$bin_net_details_obj=new Gbc_Model_DbTable_Binarynetworkdetail();
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
	}

}
