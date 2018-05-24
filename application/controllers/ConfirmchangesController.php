<?php

class ConfirmchangesController extends Zend_Controller_Action{

	public function init(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		//if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");
		//$this->_helper->layout()->disableLayout();
	}

	public function indexAction(){
		try{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$user_obj = new Gbc_Model_DbTable_Userinfo();
		$common_obj=new Gbc_Model_Custom_CommonFunc();
		$com_code = $_GET['cd'];
	    $username=base64_decode($_GET['u']);
		$name=base64_decode($_GET['n']);
		$email_new=base64_decode($_GET['e']);
		$country=base64_decode($_GET['c']);
		$phone=base64_decode($_GET['p']);
       $image=base64_decode($_GET['imgs']);
	    $time=$_GET['t'];
       $userInfo = $common_obj->getUserInfo($username); 
	   $com_codeinfo=$userInfo->profile_ver_code;
	   	if($com_codeinfo == $com_code){
	   	$description = "";
	   		$email = "<div style='padding: 10px; border: solid thin black'><div style='padding: 0px;'>
					<img src='".BASE."/images/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div>";
	     	$email .= "Dear ".$username.",<br/>";

		   	if($name != $userInfo->name){
						$description .= "full_name has been changed from ".$userInfo->name." to ".$name;
						$email .= "<div><br/>Your Profile name has been changed from ".$userInfo->name." to ".$name." </div>";

					}
					if($country != $userInfo->country){
						$description .= "country has been changed from ".$userInfo->country." to ".$country;
						$email .= "<div><br/>Your country has been changed from ".$userInfo->country." to ".$country." </div>";
					}
					if($phone != $userInfo->phone){
						$description .= "contact_phone has been changed from ".$userInfo->phone." to ".$phone;
			
						$email .= "<div><br/>Your Contact No. has been changed from ".$userInfo->phone." to ".$phone." </div>";
					}
					
					if($email_new != $userInfo->comm_email){
						$description .= "contact_email has been changed from ".$userInfo->comm_email." to ".$email_new;
						$email .= "<div><br/>Your e-mail id has been changed from ".$userInfo->comm_email." to ".$email_new." </div>";
					}
				   if(isset($image) && $image != ''){
							
						$description .= "KYC - ID PROOF image has been changed ";
						$email .= "<div><br/>Your KYC - ID PROOF image has been changed ";
						}
						
					if(!empty($description)){
					
						$saveUserLog = $common_obj->saveUserLog($username,"user_info",$description);

					}
			   	
				   	  if(isset($userInfo->comm_email) && $userInfo->comm_email!='')
						  {
							 $to = $userInfo->comm_email;
						  }
						  else
						  {
							$to = $userInfo->email_address;
						  }
					if(!empty($email)){
						if($email_new != $userInfo->comm_email){
							// $sendMail=sendMail($row['contact_email'], "admin@gainbitco.in", "Contact Details Updated", $email);
							// $sendMail=sendMail($email_new, "admin@gainbitco.in", "Contact Details Updated", $email);		
							
				
							$to = $to;
							$from = 'support@gainbitcoin.com';
							$replyTo = 'thegainbitcoinhelp@gmail.com';
							$subject = 'Contact Details Updated';
							$message = $email;
							$htmlMessage = $email;
								if(!empty($description)){
					     	$sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
								}
						//	$sendMails = sendMail($row["contact_email"], "gainbitcoin@server1.gainbitcoin.com", "Contact Details Updated", $email,true);
							
							$to = $email_new;
							if(!empty($description)){
							$sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
							   }
							//$sendMails = sendMail($email_new, "gainbitcoin@server1.gainbitcoin.com", "Contact Details Updated", $email,true);
							
						}
						else{
							// $sendMail=sendMail($row['contact_email'], "admin@gainbitco.in", "Contact Details Updated", $email);
							$to = $userInfo->comm_email;
							$from = 'support@gainbitcoin.com';
							$replyTo = 'thegainbitcoinhelp@gmail.com';
							$subject = 'Contact Details Updated';
							$message = $email;
							$htmlMessage = $email;
							if(!empty($description)){
							$sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
								}
							//$sendMails = sendMail($row["contact_email"], "gainbitcoin@server1.gainbitcoin.com", "Contact Details Updated", $email,true);
							
						}	
							
					}	
	   
	
				if(isset($image) && $image != ''){
						$img = file_get_contents("images/updateprofile/".$username.".jpg");
						/*$source = "path_to_file/image.jpg";
						$dest = "new_path_to_file/image.jpg";
						copy($source, $dest);
						unlink($source);*/
						$update_arr=array('comm_email'=>$email_new,'name'=>$name,'country'=>$country,'phone'=>$phone,'id_image'=>$img,'updated_on'=>$time);
						
					}else{
						
						$update_arr=array('comm_email'=>$email_new,'name'=>$name,'country'=>$country,'phone'=>$phone,'updated_on'=>$time);
					}
						$update_data=$user_obj->update($update_arr,"username='$username'");
				         if($authUserNamespace->user == $username){
						   		$this->_redirect("Profile");
						}else{
							Zend_Session::destroy(true,true);
					           $this->_redirect("/Login");
                        }						
							
						$data=array('success'=>'success','failure'=>'');
						echo json_encode($data);exit;
					}
            	else 
			{     
				$this->_redirect("/Profileerror");
				$msg = "That verification link is invalid, has expired or has been used.";
				$data=array('success'=>'','failure'=>$msg);
				echo json_encode($data);exit;
				exit;
			}
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;;
		}
	   	
	}

	
}