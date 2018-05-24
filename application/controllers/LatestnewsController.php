<?php
class LatestnewsController extends Zend_Controller_Action{

	public function init()
	{
		
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		try
		{
			
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$this->_helper->layout()->setLayout("dashbord");//dashboard

			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$url= BASE."/Latestnewsapi";
			$result=$common_obj->call_curl($url);
			$result=(array)json_decode($result,true);
			$this->view->result=$result;
			$this->view->title="Gainbitcoin - News";
				

		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
	}
	
	public function detailAction(){
		$this->_helper->layout()->setLayout("dashbord");
		$latestnewsobj = new Gbc_Model_DbTable_Latestnews();
		$url = $this->_request->getRequestUri();
 
		//Get the product-numbers-category.html part of the URL
		//$param = substr($url,(strrpos($url,'details/') + 1));
		$param = strstr($url,'detail');
		$param = explode('/',str_replace('detail/','',$param));
		$id = $param[0];

		$result=$latestnewsobj->fetchRow($latestnewsobj->select()
			->setIntegrityCheck(false)
			->from(array('n'=>'news'),array('n.id', 'n.headline','n.news_details','n.created_on'))
			->where('id =?',$id)
		);
			
		if(!empty($result)){
			$result =  $result->toArray();
		}
		
		$this->view->result=$result;
		$this->view->title="Gainbitcoin - News:".$result['headline'];
	}

}