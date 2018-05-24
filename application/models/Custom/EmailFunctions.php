<?php

class Bankoffercms_Model_Custom_EmailFunctions{
	
	/**
	 * @author Parth Arora <arora.parth@gmail.com>
	 * @version 1.0
	 * @Desc
	 * A custom function to send Mail built on Zend_Mail using parameters from application.ini file
	 *
	 * @link http://www.friskwave.com
	 * @param string - subject of the email
	 * @param string(or associative array) - consisting of content of Email or an array
	 * 		  consisting template name to be used for email and any additional parameters if required
	 * @param string - associative array containing recipients name and email
	 * 		  (format of array is $to['name']='recipeints name', $to['email']='recipeints email')
	 * @return boolean - returns false in case of error
	 */
	public function sendMail($subject,$content,$to){
		
		require_once 'Zend/Mail.php';
		
		$tr = new Zend_Mail_Transport_Smtp("localhost");
		Zend_Mail::setDefaultTransport($tr);
		
		$mail = new Zend_Mail();
	  	
		$mail->setSubject($subject);
		$mail->setFrom('no-reply@standardchartered.co.in','Standard Chartered Bank');
		$mail->addTo($to['email'],$to['name']);
		
		if(is_array($content)){
			
			// create zend view object for using phtml files as templates
			$view = new Zend_View();
			
			// set path of the directory where all the email templates are stored
			$view->setScriptPath(dirname(__FILE__).'/EmailTemplates/');
			
			// set the email template to use
			$emailTemplate = $content['emailTemplate'];
			
			// set variable values to include the dynamic content in the phtml file
			foreach($content as $key=>$value){
				if($key!="emailTemplate")$view->assign($key,$value);
			}
			
			$mail->setBodyHtml($view->render("$emailTemplate.phtml"));
		}
		
		else{
			$mail->setBodyHtml($content);
		}
		
		$mail->send($tr);
		
		return true;
	}
}