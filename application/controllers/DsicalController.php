<?php
class ReplyController extends Zend_Controller_Action
{
	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");
	}

	public function indexAction()
	{
		$Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
		$bin_match_obj=new Gbc_Model_DbTable_Binmatchincome();
		$fin_bin_match_obj=new Gbc_Model_DbTable_Finbinmatchincome();
		$member_obj=new Gbc_Model_DbTable_Membershiplist();
		$Direct_ref_obj=new Gbc_Model_DbTable_Directref();

		$username=trim($_POST['username']);
		$user_obj=new Gbc_Model_DbTable_Userinfo();
		$ref_user_qry = $user_obj->fetchAll($user_obj->select()
		->where("ref_sponsor_id = ?",$username)
		->order("ordering asc")
		);
		$dsi=0;
		if(!empty($ref_user_qry) && sizeof($ref_user_qry)>0)
		{
			for($i=0;$i<sizeof($ref_user_qry);$i++)
			{
				$unm=$ref_user_qry[$i]['username'];

				$final_business= $fin_bin_match_obj->fetchRow($fin_bin_match_obj->select()
				->setIntegrityCheck(false)
				->from(array('fb' =>'final_bin_match_income'),array('SUM(fb.bin_income) as bin_income'))
				->where("username=?",$unm)
				->group("username")
				);

				if(!empty($final_business) && sizeof($final_business)>0)
				{
					$prev_business=number_format($final_business->bin_income, 8);
					/*$membership_type_qry = $member_obj->fetchRow($member_obj->select()
					->where("investment_start>=$prev_business and investment_end<=$prev_business"));*/
					$membership_type_qry = $member_obj->fetchRow($member_obj->select()
					->where("investment_start>= ?",$prev_business)
					->where("investment_end<= ?",$prev_business));
					
					
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
				else {

					$membership_type='Customer';
				}


				$url= BASE."/Businesscalapi?username=".$unm."&startdate=".$startdate."&enddate=".$enddate;
				$result=$Gbc_Model_Custom_func_obj->call_curl($url);
				if(!empty($result) && sizeof($result)>0)
				{
					$result=(array)json_decode($result,true);
					$totalbusiness=$result['data']['userDetails']['totalLeftBusiness']+$result['data']['userDetails']['totalRightBusiness'];
					if(!empty($totalbusiness) && $totalbusiness!=0 && $totalbusiness!='')
					{
						if(!empty($membership_type_qry) && sizeof($membership_type_qry)>0)
						{
							$direct_sales_comission=$membership_type_qry->direct_sales_comission;
						}
						else
						{
							$membership_type_qry = $member_obj->fetchRow($member_obj->select()
							->where("membership_type=?",$membership_type));
							$direct_sales_comission=$membership_type_qry->direct_sales_comission;
						}
						$user_dsi=($totalbusiness / 100)* $direct_sales_comission;
						$dsi=$dsi + $user_dsi;
					}
				}
			}
		}
		if($dsi !=0 && $dsi!='')
		{
			$dsi_arr=array('username'=>$username,'dsi'=>$dsi,'created_on'=>new Zend_Db_Expr('NOW()'));
		}


	}
}