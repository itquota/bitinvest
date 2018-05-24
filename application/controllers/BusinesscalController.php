<?php

class BusinesscalController extends Zend_Controller_Action{

	public function init(){

	}

	public function indexAction(){
		try
		{

			$bin_match_obj=new Gbc_Model_DbTable_Binmatchincome();
			$fin_bin_match_obj=new Gbc_Model_DbTable_Finbinmatchincome();
			$Binaryuserreferenceobj = new Gbc_Model_DbTable_Binaryuserreferences();




			$invoices_obj = new Gbc_Model_DbTable_Invoices();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$member_obj=new Gbc_Model_DbTable_Membershiplist();

			$username=$_POST['username'];

			$membership_type_qry=$common_obj->getMembership($username);
			
		
			if(!empty($membership_type_qry) && sizeof($membership_type_qry)>0)
			{
				$membership_type=$membership_type_qry->membership_type;
			}
			else if($prev_business>10.00000000)
			{
				$membership_type='Promoter';
			}
			else
			{
				$membership_type='Customer';
			}
			
		/*	$result_contract=$invoices_obj->fetchRow($invoices_obj->select()
			->setIntegrityCheck(false)
			->from(array('i'=>"invoices"),array('sum(contract_rate) as total_own'))
			->where("username='".($username)."' AND invoice_status='1'")
			->group("username")
			);

			if(!empty($result_contract) && sizeof($result_contract)>0)
			{
				if(!empty($result_contract->total_own) && $result_contract->total_own!='')
				{
					$total_business=$result_contract->total_own;
				}
				else
				{
					$total_business=0;
				}
					

				$membership_type_qry = $member_obj->fetchRow($member_obj->select()
				->where("investment_start>=$total_business and investment_end<=$total_business"));

				if(!empty($membership_type_qry) && sizeof($membership_type_qry)>0)
				{
					$membership_type=$membership_type_qry->membership_type;
				}
				else if($prev_business>10.00000000)
				{
					$membership_type='Promoter';
				}
				else
				{
					$membership_type='Customer';
				}
			}
			else
				{
					$membership_type='Customer';
				}*/


			if(!empty($membership_type_qry) && sizeof($membership_type_qry)>0)
			{
				$binary_matching_multiple=$membership_type_qry->binary_matching_multiple;
				$binary_matching=$membership_type_qry->binary_matching;
				$binary_capping=$membership_type_qry->binary_capping;
				$membership_type=$membership_type_qry->membership_type;
				$binary_level=$membership_type_qry->level_max;
			}
		/*	else
			{
				$bin_match_mult_qry = $member_obj->fetchRow($member_obj->select()
				->where("membership_type='".($membership_type)."'"));
				$binary_matching_multiple=$membership_type_qry->binary_matching_multiple;
				$binary_matching=$membership_type_qry->binary_matching;
				$binary_capping=$membership_type_qry->binary_capping;
				$membership_type=$membership_type_qry->membership_type;
			}*/
			
				
			$childArray = array();
			$i = 0;$j=0;
			$refArray = $Binaryuserreferenceobj->fetchAll($Binaryuserreferenceobj->select()
			->where("parent_username = '".$username."' and parent_id<>0")
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
			
			if(!empty($childArray[0]) && $childArray[0]!=''){

				$common_obj->getAllChildforBinaryNew($childArray[0],$childs_first,$binary_level);
			}
		
			if(!empty($childArray[1]) && $childArray[1]!=''){
				$common_obj->getAllChildforBinaryNew($childArray[1],$childs_second,$binary_level);
			}

			$childs_first=array();
			$childs_second=array();
			if(!empty($childArray[0]) && $childArray[0]!=''){
				$childs_first=array_merge(array($childArray[0]),array_filter($childs_first));
			}
			if(!empty($childArray[1]) && $childArray[1]!=''){
				$childs_second=array_merge(array($childArray[1]),array_filter($childs_second));
			}
			
			$totalContractFirst=$common_obj->getTotlaDownlineContract($childs_first);
			$totalContractSecnd=$common_obj->getTotlaDownlineContract($childs_second);
			if(empty($totalContractFirst) || $totalContractFirst=='')
			{
				$totalContractFirst=0;
			}
			if(empty($totalContractSecnd) || $totalContractSecnd=='')
			{
				$totalContractSecnd=0;
			}

			$get_carray= $fin_bin_match_obj->fetchRow($fin_bin_match_obj->select()
			->where("username='".$username."'")
			->order("id desc")
			->limit(1)
			);
		
			if(!empty($get_carray) && sizeof($get_carray)>0)
			{
				if(!empty($get_carray->left_carry_frwd) && $get_carray->left_carry_frwd > 0)
				{
					$totalContractFirst=$totalContractFirst + ($get_carray->left_carry_frwd);
				}
				if(!empty($get_carray->right_carry_frwd) && $get_carray->right_carry_frwd > 0)
				{
					$totalContractSecnd=$totalContractSecnd + ($get_carray->right_carry_frwd);
				}
			}

			if($totalContractFirst > $totalContractSecnd)
			{
				$minimum_val=$totalContractFirst;
				$maximum_val=$totalContractSecnd;
			}
			else
			{
				$minimum_val=$totalContractSecnd;
				$maximum_val=$totalContractFirst;
			}




			$minimum_val_carry= fmod($minimum_val, $binary_matching_multiple);
			$minimum_business=$minimum_val-$carry;
			$maximum_val_carry=$maximum_val-$minimum_business;
			$final_bc==(($minimum_business/100)*$binary_matching);
			if($final_bc>$binary_capping)
			{
				$final_bc=$binary_capping;
			}


		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}

		$succ=array();
		$succ['binarydata']=$bigarr;
		$arra=array('success'=>'success','failure'=>'','data'=>$succ);
		echo json_encode($arra);exit;
	}

}