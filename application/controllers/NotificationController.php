<?php
class NotificationController extends Zend_Controller_Action{

	public function init(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");

	}

	public function indexAction(){
		try{
			$this->_helper->layout()->setLayout("dashbord");//dashboard
			$common_obj=new Gbc_Model_Custom_CommonFunc();
			$admin_not_obj=new Gbc_Model_DbTable_Adminnotification();
			$res = $admin_not_obj->fetchAll($admin_not_obj->select()
			->where("status=?",'active')
			->order("created desc")
			);
			$date=date('Y-m-d h:i:s');
			$arr=array();

			for($i=0;$i<sizeof($res);$i++)
			{
					
			// $diff = abs(strtotime($date) - strtotime($res[$i]['created']));
			 if(!empty($res[$i]['created']))
			 {
				 $time_elapsed =  $common_obj->time_elapsed_string($res[$i]['created']);	
			 }
			 else
			 {
				$time_elapsed='';
			 }
			 
			/* $years = floor($diff / (365*60*60*24));
			 $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
			 $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));*/

			 $subarr=array('id'=>$res[$i]['id'],'text'=>$res[$i]['text'],'created'=>$res[$i]['created'],'status'=>$res[$i]['status'],'time_elapsed'=>$time_elapsed);
			 array_push($arr,$subarr);
			}
				
			$this->view->result=$arr;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}


	}


}