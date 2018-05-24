<?php

class UpdatewalletaddrController extends Zend_Controller_Action{

	public function init()
	{
//		echo "Here"; exit;

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");


	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Change Wallet Address";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$daily_payout_data_Obj=  new Gbc_Model_DbTable_Dailypayout();
		$gbc_wallet_data_Obj=  new Gbc_Model_DbTable_Gbcwalletdata();
		$withdrawals_Obj=  new Gbc_Model_DbTable_Withdrawals();
		$Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
		$misc_obj=new Gbc_Model_Custom_Miscellaneous;
		$antixss = new Gbc_Model_Custom_StringLimit();
		$username=$authUserNamespace->user;
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('47',$user_id);
		if((!empty($data1->view) && ($data1->view)==1) || $authUserNamespace->user=='admin')
		{

		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}	
		
		
			foreach($_POST as $key => $value)
			{

				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

							$messag =  'Invalid Input';
				 				$this->view->messag=$messag;

					}

				}

			}
		try{
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$this->_helper->layout()->setLayout("admindashbord");

			if($this->_request->isPost()){
				$oldwallet=$Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST['old_wallet']);
				$newwallet=$Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST['new_wallet']);
				$comment=$Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST['comment']);
				if(empty($oldwallet))
				{
					$messag = "<p style='color:red;'>Old Wallet required</p>";
				}

				else if( empty($newwallet))
				{
					$messag = "<p style='color:red;'>New Wallet required</p>";
				}

				else if(empty($comment))
				{
					$messag = "<p style='color:red;'>Comment required</p>";
				}
				else
				{
				 $walletdetails=$daily_payout_data_Obj->fetchRow($daily_payout_data_Obj->select()
				 ->setIntegrityCheck(false)
				 ->from(array('daily_payout_data'))
				 ->where('wallet_addr=?',$oldwallet));
					if(!empty($walletdetails)){
						$update_arr1=array('wallet_addr'=>$newwallet,'payout_option'=>1,'updated_on'=>new Zend_Db_Expr('NOW()'));
						$update_data1=$daily_payout_data_Obj->update($update_arr1,$db->quoteInto("wallet_addr=?",$oldwallet));	
						
						if(!empty($update_data1)){
							
							$update_arr2=array('total_btc_amt'=>0,'total_btc_withdrawal'=>0,'balance_btc'=>0,'updated_on'=>new Zend_Db_Expr('NOW()'));
							$update_data2=$gbc_wallet_data_Obj->update($update_arr2,$db->quoteInto("wallet_addr=?",$oldwallet));							
							
							if(!empty($update_data2)){
							//	$type = array('daily_payout_btc','daily_payout_mcap');
								$update_arr3=array('addr'=>$newwallet,'withdrawal_type'=>'daily_payout_btc','updated_on'=>new Zend_Db_Expr('NOW()'));
								//$update_data3=$withdrawals_Obj->update($update_arr3,$db->quoteInto("addr=$oldwallet AND withdrawal_type IN ('daily_payout_btc','daily_payout_mcap')"));
								$where = array();
								$where[] = $db->quoteInto('addr = ?', $oldwallet);
								$where[] = $db->quoteInto('withdrawal_type IN("daily_payout_btc","daily_payout_mcap")');
								$update_data3=$withdrawals_Obj->update($update_arr3,$where);						
								if(!empty($update_data3)){			
								$description = "From MCAP to COINBANK :: Wallet Address updated from '".$oldwallet."' to '".$newwallet."' by '".$username."' comment mentioned - '".$comment."'." ;
									if(!empty($description)){
										$saveUserLog = $common_obj->saveUserLog($username,"daily_payout_data",$description);	
										$msg = "Wallet Updated Successfully";
									}								
								}
							}
						} 
					}

				}
				
				$this->view->msg=$msg;
				$this->view->messag=$messag;				
			}
			$db->commit();	

		}
		catch(Exception $e)
		{
			$db->rollBack();
			echo $e->getMessage();exit;
		}
	}

}
