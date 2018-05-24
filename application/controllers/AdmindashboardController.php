<?php
class AdmindashboardController extends Zend_Controller_Action{

	public function init()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		//echo $authUserNamespace->user;exit;
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{

		$this->_helper->layout()->setLayout("admindashbord");//dashboard
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!empty($authUserNamespace->user) &&  $authUserNamespace->user=='admin')
		{
			//$loggedIn==true;
		}
		else if(!empty($authUserNamespace->user) &&  $authUserNamespace->user_type=='subadmin')
		{

			//$loggedIn==true;

			//$this->_redirect("/Admindashboard");
		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Login");
		}

			
		$invoices_obj  = new Gbc_Model_DbTable_Invoices();
		$userinfo_Obj= new Gbc_Model_DbTable_Userinfo();
		$this->view->title="Gainbitcoin - Admindashboard";
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();

		try
		{
				$type = array('SHA','hardware');
				$result=array();

			// Pronto - Commented the below query as it was taking lot of time for execution. 06.03.2017
			/*
				$query=$invoices_obj->fetchAll($invoices_obj->select()
				->setIntegrityCheck(false)
				->from(array('invoices'=>"invoices"),array('round(sum(invoices.contract_rate),2) as total_sales', 'count(invoices.invoice_id) as sales', 'date(invoices.created_on) as date', '(select count(user_info.username) from user_info where date(user_info.created_on) = date(invoices.created_on)) as countusers'))
				->where("invoices.contract_type IN (?)",$type)
				->where("invoices.created_on BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND NOW()")
				
				->group("date(invoices.created_on)")
				->order("invoices.created_on desc")
				);
				
		
			*/			
				
			/*$query="SELECT round(sum(invoices.contract_rate),2) as total_sales, count(invoices.invoice_id) as sales, date(invoices.created_on) as date, (select count(user_info.username) from user_info where date(user_info.created_on) = date(invoices.created_on)) as countusers FROM invoices where invoices.contract_type in('SHA','hardware') and invoices.created_on BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND NOW() group by date(invoices.created_on) order by invoices.created_on desc";
			$rows=$DB->query($query);
			//$rows->setFetchMode(Zend_Db::FETCH_NUM);
			$result = $rows->fetchAll();*/
			if(isset($query) && sizeof($query)>0)
			{
				$result = $query->toArray();
			}
			else 
			{
				$result = array();
			}

			$result1=array();
		/*	$dt = date("Y-m-d");
				$query1=$invoices_obj->fetchAll($invoices_obj->select()				
				//->where("username='".($user)."' AND contract_type='SHA' and locked = '0' AND invoice_status='1' $date"));
				->where("created_on >=?", $dt)
				->order("invoices.created_on desc")
				);
				
*/
			//$query1="SELECT  * FROM invoices  WHERE created_on >= CURDATE() order by invoices.created_on desc";
			/*$rows1=$DB->query($query1);
			//$rows->setFetchMode(Zend_Db::FETCH_NUM);
			$result1 = $rows1->fetchAll();*/
			
			if(isset($query1) && sizeof($query1)>0)
			{
				$result1 = $query1->toArray();
			}
			else 
			{
				$result1 = array();
			}

			$this->view->result=$result;
			$this->view->today=$result1;


		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}
	
	public function logoutAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$admin_obj=new Gbc_Model_DbTable_Adminsetting();
		$username=$authUserNamespace->user;
		Zend_Session::destroy(true,true);
		$upd_arr=array('session_id'=>'');
		$upd_qry=$admin_obj->update($upd_arr,"admin_user_name='".$username."'");
		if (!empty($_COOKIE['remember']) && isset($_COOKIE['remember'])){

			setcookie('remember', '', time() - 3600, '/');
			setcookie ("remember", "", time() - 3600);
		}
		//	unset($_COOKIE['remember']);
			
		$this->_redirect("/Login");
		//echo "success";exit;
	}
}