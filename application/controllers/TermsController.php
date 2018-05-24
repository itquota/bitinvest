<?php 
class TermsController extends Zend_Controller_Action{
	
	
		public function init(){
			
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
	//if(!empty($authUserNamespace->user) && isset($authUserNamespace->user) && $authUserNamespace->user!="" && $authUserNamespace->user_type=="binary")$this->_redirect("/Dashboard");

		//$this->_helper->layout()->disableLayout();
			//}
	
		}
		public function indexAction(){
	
			//$this->_helper->layout()->setLayout("admindashbord");
			$this->_helper->layout()->setLayout("login");
		}
		
		
		
		
		
}