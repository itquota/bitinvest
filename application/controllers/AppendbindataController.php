<?php
class AppendbindataController extends Zend_Controller_Action{

	public function init(){

		//$this->_helper->layout()->disableLayout();
	}
	public function indexAction(){
		$bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
		$Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();

		$username=$_POST['user'];
		$prekey=$_POST['key'];
		$lenght=$_POST['length'];
		if(!empty($_POST['user'])){
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
		->where("a.username=?",$username)
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
			//$tree=  array_merge($finalRes);
			$tree=  ($finalRes);

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
				$tree[$k]['key']=$k+1+$lenght;
				$index=array_search($tree[$k]['parent'],$master_arr);
				if(isset($index)&& $index!=false)
				{
					$tree[$k]['parent_id']=$index+1+$prekey;
				}
				else
				{
					$tree[$k]['parent_id']=$prekey;
				}

			}

			$json_data=json_encode($tree);
			echo $json_data; exit;

		}else{
				
			$tree=array();
			$json_data=json_encode($tree);
			echo $json_data; exit;
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
		}
	}
}