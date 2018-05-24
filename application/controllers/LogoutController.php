<?php

class LogoutController extends Zend_Controller_Action{

	public function init(){
		//$this->_helper->layout()->disableLayout();
		
	}

	public function indexAction(){

        $this->_helper->layout()->setLayout("login");
		  $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $user_obj = new Gbc_Model_DbTable_Userinfo();
        $username=$authUserNamespace->user;
        Zend_Session::destroy(true,true);
        $upd_arr=array('session_id'=>'');
        $upd_qry=$user_obj->update($upd_arr,$DB->quoteInto("username=?",$username));
        if (!empty($_COOKIE['remember']) && isset($_COOKIE['remember'])){

            setcookie('remember', '', time() - 3600, '/');
            setcookie ("remember", "", time() - 3600);
        }
        //    unset($_COOKIE['remember']);
           
        $this->_redirect("/Login");
        //echo "success";exit;
		//echo "success";exit;


		//$authUserNamespace->user_id=
	}
}

