<?php
class BtreetableapiController extends Zend_Controller_Action{

	public function init(){


	}

	public function indexAction(){


		//$this->_helper->layout()->setLayout("dashbord");//dashboard
		try
		{	$common_obj = new Gbc_Model_Custom_CommonFunc();
		//$common_obj->cleanQueryParameter(($_REQUEST['username']));

		$db = Zend_Db_Table::getDefaultAdapter();
		$DBS = Zend_Db_Table_Abstract::getDefaultAdapter();
		$db->beginTransaction();
		$bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
		$bin_net_details_obj=new Gbc_Model_DbTable_Binarynetworkdetail();
		$Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
		//$username=$_REQUEST['username'];
		$date = date('d');
		$time = date('H:i:s');
		$count = 0;


		//$username=$common_obj->cleanQueryParameter(($_REQUEST['username']));
		$username = $this->_request->getParam("username");
		$page = $this->_request->getParam("page")?$this->_request->getParam("page"):1;
		$child = $this->_request->getParam("child")?$this->_request->getParam("child"):1;

		if($username != ''){

			$username=$username;

		}else{
			$arr=array('Success'=>' ','Failure'=>'Username cannot be blank');
			echo json_encode($arr);
			exit;
		}


		//$user=$_REQUEST['user'];
		//$user=$common_obj->cleanQueryParameter(($_REQUEST['user']));
		$user = $this->_request->getParam("user");

		if(!empty($user)){
			$SearchParent ='';
			$Gbc_Model_Custom_func_obj->getAllChildforBinary($username,$SearchParent);
			// var_dump($SearchParent);
			if(in_array(strtolower($user),array_map('strtolower', $SearchParent))){
				$username = strtolower($user);
			}
		}

		$res=array();
		/*$res=$bin_user_ref->fetchAll($bin_user_ref->select()
		 ->setIntegrityCheck(false)
		 ->from(array('a'=>"binary_user_refences"),array('a.id','a.username','a.parent_username','a.parent_id','a.child_position'))
		 ->joinLeft(array('u'=>'user_info'),"u.username = a.username",array('u.ref_sponsor_id','u.isActiveId','u.lock_status'))
		 ->joinLeft(array('i'=>'invoices'),"i.username = a.username",array('round(sum(i.contract_rate),2) as ContractPrice','i.created_on','round(sum(i.amtPaid),2) as amtPaid'))
		 ->where("a.username= ?",$username)
		 );*/

		/*$qury="Select 
		  BinaryUserReferences.id, 
		  BinaryUserReferences.username,
		  BinaryUserReferences.parent_username,
		  BinaryUserReferences.parent_id,
		  BinaryUserReferences.child_position, 
		 Profile.full_name,  
		  UserInfo.ref_sponsor_id,
		  UserInfo.isActiveId,
		  UserInfo.lock_status, 
		  round(sum(Invoice.amtPaid),2) as amtPaid, 
		  Invoice.created_on, 
		 round(sum(Invoice.contract_rate),2) as ContractPrice 
		 from binary_user_refences as BinaryUserReferences 
		 left join `user_info` as UserInfo on UserInfo.username = BinaryUserReferences.username 
		left join `profile_contact` as Profile on Profile.username = UserInfo.username
		 left join invoices as Invoice on Invoice.username = BinaryUserReferences.username 
		 left join contracts as Contract on Contract.contract_id = Invoice.contract_id  
  where BinaryUserReferences.username='$username' ";

		$qry_res=$DBS->query($qury);
		echo "<pre>";
		print_r($qry_res);exit;
		$res = $qry_res->fetchAll();*/
		$res = $bin_user_ref->fetchAll($bin_user_ref->select()
									  ->setIntegrityCheck(false)
									  ->from(array('BinaryUserReferences'=>'binary_user_refences'),array('BinaryUserReferences.id','BinaryUserReferences.parent_username','BinaryUserReferences.username','BinaryUserReferences.parent_id','BinaryUserReferences.child_position'))
									  ->joinLeft(array('UserInfo'=>'user_info'),'(UserInfo.username = BinaryUserReferences.username)',array('UserInfo.ref_sponsor_id','UserInfo.isActiveId','UserInfo.lock_status','UserInfo.name'))
									  ->joinLeft(array('Invoice'=>'invoices'),'(Invoice.username = BinaryUserReferences.username)',array('round(sum(Invoice.amtPaid),2) as amtPaid','Invoice.created_on','round(sum(Invoice.contract_rate),2) as ContractPrice'))
									  ->joinLeft(array('Contract'=>'contracts'),'(Contract.contract_id = Invoice.contract_id )')
									  ->where("BinaryUserReferences.username=?",$username)
									  );

							 
		$limit = 1;
		if(isset($res) && sizeof($res)>0)
		{
			$res = $res->toArray();
			
		}
		
		if(isset($res) && sizeof($res)>0){
				
				if(!empty($res[0]['ContractPrice']) && $res[0]['ContractPrice']!='')
				{
					$res[0]['amtPaid']= $res[0]['ContractPrice'];
				}
				else
				{
					$res[0]['amtPaid']=0;
				}
			// var_dump($res);
			//$res[0]['amtPaid'] = !empty($res[0]['ContractPrice'])?$res[0]['ContractPrice']:'';
			$topmcode =$res[0]['id'];
			$finalRes='';

			if(!empty($topmcode) && $topmcode!=''){
			//	echo "test"; exit;
				$Gbc_Model_Custom_func_obj->getNetworkDetails($topmcode,$finalRes,$page,$child);
			//	$Gbc_Model_Custom_func_obj->getDirectChildren($topmcode,$finalRes,$limit);
				//$Gbc_Model_Custom_func_obj->getImmediateChild($topmcode,$finalRes,$limit, 'P');
			}
		}
	//	print_r($finalRes);exit;
/*
		if(!empty($finalRes))
		{
			//printArr($finalRes);


			$a=array('0'=>array('name'=>$username,'parent'=>'null','isactive' => $res[0]['isActiveId'],'lock_status' => $res[0]['lock_status'],'child_position' => '', 'amtpaid' => $res[0]['amtPaid'],'sponsor' => $res[0]['ref_sponsor_id'], 'date' => $res[0]['created_on']));
			$tree=  array_merge($a,$finalRes);
				

			//print_r($json_data);die;
			$master_arr=array();
				
			for($k=0;$k<(sizeof($tree));$k++)
			{

				array_push($master_arr,$tree[$k]['name']);
			}
			
			for($k=0;$k<(sizeof($tree));$k++)
			{
				$tree[$k]['key']=$k+1;
				$index=array_search($tree[$k]['parent'],$master_arr);
				$tree[$k]['parent_id']=$index+1;
				
			}

			//$json_data=json_encode($tree);
				
		}else{
			if(!empty($topmcode)){
				$tree=array('0'=>array('name'=>$username,'parent'=>'null','isactive' => $res[0]['isActiveId'],'full_name' => $tree['name'],'lock_status' => $res[0]['lock_status'],'child_position' => '', 'amtpaid' => $res[0]['amtPaid'],'sponsor' => $res[0]['ref_sponsor_id'], 'date' => $res[0]['created_on'],'key'=>1,'parent_id'=>1));
			}else{
				$tee = array();
			}
		}
		*/
		//echo "<pre>";
		//print_r($tree);exit;
		$users[]= $username;

		$UserData =$Gbc_Model_Custom_func_obj-> checkNetworkDetails($username,2);

		$all_user=array();
		if(isset($UserData) && sizeof($UserData)>0){
			// echo "yes";
			$all_user = $UserData;
			//$all_user = $Gbc_Model_Custom_func_obj->leftRightDetails($users,$NetworkUsers,$count);
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
				
			$all_user = $Gbc_Model_Custom_func_obj->leftRightDetails($users,$NetworkUsers,$count);
		}
		//print_r($all_user);exit;
		}
		catch(Exception $e)
		{
			$db->rollBack();
			echo $e->getMessage();exit;
		}
		$bigarr=array();
		if(!empty($all_user) && sizeof($all_user)>0)
		{
			$dataarr=array('leftContracts'=>$all_user['leftContracts'],'totalLeftUsers'=>$all_user['totalLeftUsers'],'activeLeftUsers'=>$all_user['activeLeftUsers'],'inactiveLeftUsers'=>$all_user['inactiveLeftUsers'],'rightContracts'=>$all_user['rightContracts'],'totalRightUsers'=>$all_user['totalRightUsers'],'activeRightUsers'=>$all_user['activeRightUsers'],'inactiveRightUsers'=>$all_user['inactiveRightUsers']);
		}
		else
		{
			$dataarr=array();
		}
		//for($n=0;$n<sizeof($tree);$n++)
		/*
		for($n=0;$n<sizeof($finalRes);$n++)
		{
		
			$subarr=array('name'=>$finalRes[$n]['name'],'parent'=>$finalRes[$n]['parent'],'full_name' => $finalRes['name'],'isactive'=>$finalRes[$n]['isactive'],'lock_status'=>$finalRes[$n]['lock_status'],'child_position'=>$finalRes[$n]['child_position'],'amtpaid'=>$finalRes[$n]['amtpaid'],'sponsor'=>$finalRes[$n]['sponsor'],'date'=>$finalRes[$n]['date'],'key'=>$finalRes[$n]['key'],'parent_id'=>$finalRes[$n]['parent_id'],'pos'=>$finalRes[$n]['pos']);
			array_push($bigarr,$subarr);
		}
		*/
		$db->commit();
		//print_r($bigarr);exit;
		$succ=array();
	//	$bigarr['countLeft'] = $finalRes['countLeft'];
	//	$bigarr['countRight'] = $finalRes['countRight'];
		$succ['binarydata']=$finalRes;
		$succ['userdata']=$dataarr;
		$arra=array('success'=>'success','failure'=>'','data'=>$succ);
	//	print_r($arra);exit;
	echo json_encode($arra);
		
   // echo json_last_error_msg();
		exit;
		//$this->view->result=$tree;

		}




	}