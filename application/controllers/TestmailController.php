<?php

class TestmailController extends Zend_Controller_Action{

	public function init()
	{

	}

	public function indexAction(){

		try {


			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$url='https://api.elasticemail.com/v2/email/send?apikey=10b9bb3c-47bd-454c-bc1f-0907aa4995c7&to=amolkorde123@gmail.com&subject=TestEmail&from=amol.korde@prontoinfotech.com&bodyText=Test Email';
			$bcount=$common_obj->call_curl($url);
			print_r($bcount);exit;

			}
			catch(Exception $e)
			{
				echo $e->getMessage();exit;
			}

	}


}