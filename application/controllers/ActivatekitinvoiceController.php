<?php

class ActivatekitinvoiceController extends Zend_Controller_Action{

	public function init(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");
	}

	public function indexAction(){
		try {

			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$this->_helper->layout()->setLayout("admindashbord");
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$kits_object=new Gbc_Model_DbTable_Kits();
			$this->_helper->layout()->setLayout("login");
			$username=$_POST['username'];
			$invoiceId=$_POST['invoiceId'];

			/*$kits_data=$kits_object->fetchAll($kits_object->select()
			->where("username='".$username."' AND invoice_id='".$invoiceId."'")
			);
			*/
			
			$kits_data=$kits_object->fetchAll($kits_object->select()
			->where("username=?",$username)
			->where("invoice_id=?",$invoiceId)
			);
			
			if(!empty($kits_data) && sizeof($kits_data)>0)
			{
				$db->commit();
				$this->view->result=$kits_data;
			}
			else
			{
				$db->commit();
				$kits_data=array();
				$this->view->result=$kits_data;
			}
		}
		catch(Exception $e)
		{
			$db->rollBack();
			$arr=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($arr);exit;
		}

	}
	public function kitupdateAction()
	{
		try {
			$db = Zend_Db_Table::getDefaultAdapter();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			
			$db->beginTransaction();
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$user=$authUserNamespace->user;
			$invoiceId=$_POST['invoiceId'];
			if(empty($_POST['comment']) || $_POST['comment']=='')
			{
				$db->rollBack();	
				$arr=array('success'=>'','failure'=>'failure','msg'=>'Comment should not be blank');
				echo json_encode($arr);exit;
			}

			$kitinvoices_obj = new Gbc_Model_DbTable_Kitinvoices();
			$kits_object=new Gbc_Model_DbTable_Kits();
			$commcon_obj=new Gbc_Model_Custom_CommonFunc();
			/*$kits_inv_data=$kits_object->fetchAll($kits_object->select()
			->where("invoice_id='".$invoiceId."'")
			);*/

			
			$kits_inv_data=$kits_object->fetchAll($kits_object->select()
			->where("invoice_id=?",$invoiceId)
			);

			if(!empty($kits_inv_data) && sizeof($kits_inv_data))
			{
				$description = "";
				if($kits_inv_data[0]['invoice_status'] != 1){
					$description .= "invoice_status for Kit invoice '".$kits_inv_data[0]['invoice_id']."' has been changed from ".$kits_inv_data[0]['invoice_status']." to 1";
				}
			}
			$upd_arr=array('invoice_status'=>'1','comment'=>$_POST["comment"],'update_by'=>$user,'updated_on'=>new Zend_Db_Expr('NOW()'));
			$upd_data= $kitinvoices_obj->update($upd_arr,$DB->quoteInto("invoice_id=?",$invoiceId));

			if(!empty($upd_data))
			{
				if(!empty($description)){
					$saveUserLog = $commcon_obj-> saveUserLog($user,"kit_invoices",$description);
				}
				$kitInvUpdated = true;
				$update=date('Y-m-d h:i:s');

				/*$kits_data=$kits_object->fetchRow($kits_object->select()
				->where("status='Inactive' || status='Pending'  || status='Partial Payment')AND invoice_id='".$invoiceId."'")
				);*/
				
				$kits_data=$kits_object->fetchRow($kits_object->select()
				->where("status='Inactive' || status='Pending'  || status='Partial Payment'")
				->where("invoice_id = ?",$invoiceId)
				
				);

				if(!empty($kits_data)){

					if($kits_data['status'] != "Active"){
						$description .= "status for kit '".$kits_data['kit_number']."' has been changed from ".$kits_data['status']." to Active";
					}
					$KitType = $kits_data['kit_type'];

				}

				$kit_upd_arr=array('status'=>'Active','updated_on'=>new Zend_Db_Expr('NOW()'));
				//$kit_upd_qry=$kits_object->update($kit_upd_arr,"(status='Inactive' || status='Pending'  || status='Partial Payment')AND invoice_id='".$invoiceId."'");
				
				$kit_upd_qry=$kits_object->update($kit_upd_arr,$DB->quoteInto("status='Inactive' || status='Pending'  || status='Partial Payment'") . ' AND ' . $db->quoteInto("invoice_id=?",$invoiceId));
				
				if(!empty($description)){
					$saveUserLog =$commcon_obj-> saveUserLog($user,"kits",$description);
				}
			}
			$db->commit();
			$arr=array('success'=>'success','failure'=>'','msg'=>'Kit updated successfully');
			echo json_encode($arr);exit;
		}
		catch(Exception $e)
		{
			$db->rollBack();
			$arr=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($arr);exit;
		}
	}

}