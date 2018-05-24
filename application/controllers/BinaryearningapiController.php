<?php
class BinaryearningapiController extends Zend_Controller_Action{

	public function init(){

	}
	public function indexAction()
	{
		try {
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$this->getResponse()->setHeader('Content-Type', 'application/json');
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$invoices_obj = new Gbc_Model_DbTable_Invoices();
			$Binaryuserreferenceobj = new Gbc_Model_DbTable_Binaryuserreferences();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			//$common_obj->cleanQueryParameter(($_REQUEST['username']));

			
			$Binaryuserwelcomeobj = new Gbc_Model_DbTable_Binaryuserwelcome();

			//$username=$common_obj->cleanQueryParameter(($_REQUEST['username']));
			$username = $this->_request->getParam("username");
			
		 	if($username != ''){
				
		 	$username=$username;
		 
			 }else{
		 	$arr=array('Success'=>' ','Failure'=>'Username cannot be blank');
		 	echo json_encode($arr);
		 	exit;
		 }
			//binary user reference


			$childArray = array();
			$i = 0;$j=0;
			$refArray = $Binaryuserreferenceobj->fetchAll($Binaryuserreferenceobj->select()
			//->where("parent_username = '".$username."' and parent_id<>0")
			->where("parent_username = ?",$username)
			->where("parent_id <> ?",'0')
			->order("child_position ASC")
			);

			$childArray[0]='';
			$child_position[0]='';
			$childArray[1]='';
			$child_position[1]='';

			for($i=0;$i<sizeof($refArray);$i++)
			{
				$childArray[$i]= $refArray[$i]['username'];
				$child_position[$i]=$refArray[$i]['child_position'];
			}

/*
			$childs_first='';
			if(!empty($childArray[0]) && $childArray[0]!=''){
					
				$common_obj->getAllChildforBinary($childArray[0],$childs_first);
			}

			$childs_second='';
			if(!empty($childArray[1]) && $childArray[1]!=''){
				$common_obj->getAllChildforBinary($childArray[1],$childs_second);
			}

	
			if(!empty($childArray[0]) && $childArray[0]!=''){
				$childs_first=array_merge(array($childArray[0]),array_filter($childs_first));
			}

			
			if(!empty($childArray[1]) && $childArray[1]!=''){
				$childs_second=array_merge(array($childArray[1]),array_filter($childs_second));
			}
			
		

			$totalContractFirst=$common_obj->getTotlaDownlineContract($childs_first);
			$totalContractSecnd=$common_obj->getTotlaDownlineContract($childs_second);
*/
			//	echo "here";
			
			
			if(!empty($childArray[0]) && $childArray[0]!=''){
				$total = $db->query("SELECT round(sum(B.contract_rate),2) as contracts FROM `network_details` A inner join invoices B on ((B.username = A.username) and (B.invoice_status in (1,3))) where A.root_user='$childArray[0]'   or (A.username = '$childArray[0]' and A.root_user = '$username')");
				$results = $total->fetchAll();
				$totalContractFirst=$results[0]['contracts'];
			}
		//$totalContractFirst=0;
			
			if(!empty($childArray[1]) && $childArray[1]!=''){
				$total1 = $db->query("SELECT round(sum(B.contract_rate),2)  as contracts FROM `network_details` A inner join invoices B on ((B.username = A.username) and (B.invoice_status in (1,3))) where A.root_user='$childArray[1]'  or (A.username = '$childArray[1]' and A.root_user = '$username')");
				$results1 = $total1->fetchAll();
				$totalContractSecnd=$results1[0]['contracts'];
			}
		//	$totalContractSecnd= 0;
		
	
			if($child_position[0] != "R"){
				$all_user[0]['username']=$childArray[0];
				$all_user[0]['contact']=$totalContractFirst;
				$all_user[0]['child_position']=$child_position[0];
				$all_user[1]['username']=$childArray[1];
				$all_user[1]['contact']=$totalContractSecnd;
				$all_user[1]['child_position']=$child_position[1];
			}else{
				$all_user[0]['username']='';
				$all_user[0]['contact']='';
				$all_user[0]['child_position']='';
				$all_user[1]['username']=$childArray[0];
				$all_user[1]['contact']=$totalContractFirst;
				$all_user[1]['child_position']=$child_position[0];

			}


			$result_contract=$invoices_obj->fetchRow($invoices_obj->select()
			->setIntegrityCheck(false)
			->from(array('A'=>"invoices"),array('sum(contract_rate) as total_own'))
			//->where("username='".($username)."' AND invoice_status='1' AND contract_type='SHA'")
			->where("username= ?",$username)
			->where("invoice_status= ?",'1')
			->where("contract_type in('SHA','hardware','MS','ES')")
			->group("username")
			);

			if(isset($result_contract) && sizeof($result_contract)>0)
			{
				$totalOwn_contract=$result_contract->total_own;
			}
			else
			{
				$totalOwn_contract=0;
			}

			$result_bin = $Binaryuserwelcomeobj->fetchRow($Binaryuserwelcomeobj->select()
			//->where("username = '".$username."' AND parent_username = '' AND status=1"));
			->where("username = ?",$username)
			->where("parent_username = ?",'')
			->where("status = ?",'1')
			);
				

			$child=array();
			foreach($all_user as $user){

				$refArray=array();
				$refArray = $Binaryuserwelcomeobj->fetchAll($Binaryuserwelcomeobj->select()
				//->where("parent_username = '".$username."' AND `username` = '".$user['username']."' AND status=1"));
				->where("parent_username = ?",$username)
				->where("username = ?",$user['username'])
				->where("status = ?",'1')
				);
					
				if(!empty($refArray) && sizeof($refArray)>0)
				{
					$refArray=$refArray->toArray();
				}

				for($i=0;$i<sizeof($refArray);$i++)
				{
					$child[] = $refArray[$i];
					//$sub_arr=array('username'=>$refArray[$i]['username'],'parent_username'=>$refArray[$i]['parent_username'],'last_total'=>$refArray[$i]['last_total'],'current_toal'=>$refArray[$i]['current_toal'],'less_value'=>$refArray[$i]['less_value'],'total_earning'=>$refArray[$i]['total_earning'],'old_total'=>$refArray[$i]['old_total'],'created_on'=>$refArray[$i]['created_on'],'status'=>$refArray[$i]['status']);
					//array_push($child,$sub_arr);
				}
					
			}

			$details = $Binaryuserwelcomeobj->fetchAll($Binaryuserwelcomeobj->select()
			//->where("username = '".$username."' AND parent_username = ''"));
			->where("username=?",$username)
			->where("parent_username=?",'')
			->order("created_on DESC")
			);

				
			$details=$details->toArray();

			$subarr=array();

			for($j=0;$j<sizeof($details);$j++)
			{
				$arr = array('id'=> $details[$j]['id'],'username' => $details[$j]['username'],'username' => $details[$j]['username'],'last_total' =>$details[$j]['last_total'],'current_toal' =>$details[$j]['current_toal'],'less_value' =>$details[$j]['less_value'],'total_earning' =>$details[$j]['total_earning'],'old_total' =>$details[$j]['old_total'],'created_on' =>$details[$j]['created_on'],'updated_on' =>$details[$j]['updated_on'],'status' =>$details[$j]['status'],'isDaily' =>$details[$j]['isDaily']);
				array_push($subarr,$arr);

			}
			$db->commit();
			/*$result=$Binaryuserwelcomeobj->fetchRow($Binaryuserwelcomeobj->select()
			 ->setIntegrityCheck(false)
			 ->from(array('b'=>'bin_usr_wkl_income'),array('b.total_earning','b.created_on'))
			 ->where("id=$UserId and latitude='$Latitude' and longitude='$Longitude'" ));*/

			$arr=array('success'=>'success','failure'=>'','all_user'=>$all_user,'totalOwn_contract'=>$totalOwn_contract,'childArray'=>$childArray,'details'=>$subarr,'child'=>$child);
			echo json_encode($arr);exit;

		}
		catch(Excetption $e)
		{
			$db->rollBack();
			echo $e->getMessage();
		}


	}
}