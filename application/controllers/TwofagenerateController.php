<?php
class TwofagenerateController extends Zend_Controller_Action{

	public function init()
	{

	}
	public function indexAction()
	{
		require("library/PHPGangsta/GoogleAuthenticator.php");
		$ga = new PHPGangsta_GoogleAuthenticator();

		$secret=$ga->createSecret();
		$website = BASE; //Your Website
		$title= 'bitcoin';
		$qrCodeUrl = $ga->getQRCodeGoogleUrl($title, $secret,$website);
		$data=array('qrcode'=>$qrCodeUrl,'secret'=>$secret);
		$arr=array('success'=>'success','failure'=>'','data'=>$data);
		echo json_encode($arr);exit;
		
	}

}