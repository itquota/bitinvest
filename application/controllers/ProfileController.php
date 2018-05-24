<?php
class ProfileController extends Zend_Controller_Action{

    public function init()
    {
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");
    }
    public function indexAction()
    {
        require("library/PHPGangsta/GoogleAuthenticator.php");
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $username=$authUserNamespace->user;
        $ga = new PHPGangsta_GoogleAuthenticator();
		$binary_usr_ref = new Gbc_Model_DbTable_Binaryuserreferences();
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $result=array();
        $url= BASE."/Userinfoapi?username=".$username;
        $result=$common_obj->call_curl($url);
        $userInfo=(array)json_decode($result,true);
        $this->view->title="Gainbitcoin - Profile";
        //echo "<pre>";
        //print_r($userInfo);exit;
        if($this->_request->isXmlHttpRequest())
        {

        }
        else
        {

            $this->_helper->layout()->setLayout("dashbord");
            $this->view->userInfo=$userInfo;
        }
        //$secret = 'DZPVUXRVSX33NZFF';
        $secret=$ga->createSecret();
        //echo $secret;exit;
        $authUserNamespace ->secret=$secret;


        $website = "Gainbitcoin"; //Your Website
        $title= $username;
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($title, $secret,$website);
        $this->view->qrCodeUrl=$qrCodeUrl;
        //$authUserNamespace ->secret=$secret;

        $user_category_obj = new Gbc_Model_DbTable_Usercategory();
        $lov_obj = new Gbc_Model_DbTable_Lov();
        $user = new Gbc_Model_DbTable_Userinfo();

        $cat_data= $lov_obj->fetchAll($lov_obj->select()
        ->from(array('lov'),array('value','id'))
        ->where("name='category' and status='1'"));

        /*$user_category=array();
         $user_category_data=$user_category_obj->fetchAll($user_category_obj->select()
         ->setIntegrityCheck(false)
         ->from(array('u'=>"user_category"),array('u.id'))
         ->joinLeft(array('l'=>'lov'),"l.id = u.category_id",array('l.value'))
         ->where("u.username='".$authUserNamespace->user."' and l.name='category' and l.status='1'")
         );
         $this->view->user_category_data=$user_category_data;

         if(!empty($user_category_data) && sizeof($user_category_data)>0)
         {
            for($cd=0;$cd<sizeof($user_category_data);$cd++)
            {
            array_push($user_category,$user_category_data[$cd]['value']);
            }
            }*/

        $userobj = new Gbc_Model_DbTable_Userinfo();
        /*$user_info=$userobj->fetchRow($userobj->select()
        ->setIntegrityCheck(false)
        ->from(array('u'=>"user_info"))
        ->where("u.username='".$authUserNamespace->user."' and referral_flag='1'"));*/


        $user_info=$userobj->fetchRow($userobj->select()
        ->setIntegrityCheck(false)
        ->from(array('u'=>"user_info"))
        ->where("u.username=?",$authUserNamespace->user)
        ->where("referral_flag = ?",'1'));


        $this->view->user_info=$user_info;


        $country_obj=new Gbc_Model_DbTable_Countries();
        $country_data=$country_obj->fetchAll($country_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('countries'))
        ->order('ccode ASC')
        );


        $profile_obj=new Gbc_Model_DbTable_Userinfo();
        /*$profile_image=$profile_obj->fetchAll($profile_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('user_info'))
        ->where("username='$username'"));*/

        $profile_image=$profile_obj->fetchAll($profile_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('user_info'))
        ->where("username=?",$username));




        //echo "<pre>";
        //print_r($profile_image);exit;
        //$this->view->country_data=$country_data;

        $user_cat=array('category'=>$cat_data,'user_category'=>$user_category,'country_data'=>$country_data,'profile_image'=>$profile_image);

        $this->view->user_cat=$user_cat;
		
		$checkleg = $binary_usr_ref->fetchAll($binary_usr_ref->select()
		->setIntegrityCheck(false)
		->from(array('binary_user_refences'))		
		->where("parent_username=?",$username));
		
		$totalchild = sizeof($checkleg);
		$this->view->totalchild=$totalchild;

    }
    public function updateprofileAction()
    {
        try {

            $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
            $common_obj = new Gbc_Model_Custom_CommonFunc();
            $result=array();
            $url= BASE."/Userinfoapi?username=".$authUserNamespace->user;
            $result=$common_obj->call_curl($url);
            $userInfo=(array)json_decode($result,true);

            $userInfo['data']['email_address']=$_POST['email_address'];
            $userInfo['data']['name']=$_POST['name'];
            $updateUserAccount=$common_obj->updateUserAccount($userInfo);
            $data=array('success'=>'success','failure'=>'');
            echo json_encode($data);exit;
        }
        catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }

    }
    /****** user profile update ******/
    public function newupdateprofileAction()
    {
        try {

            $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
            $common_obj = new Gbc_Model_Custom_CommonFunc();
            $user = new Gbc_Model_DbTable_Userinfo();
            $antixss = new Gbc_Model_Custom_StringLimit();
            $username=$authUserNamespace->user;
            foreach($_POST as $key => $value)
            {
                    if($key!="name")
                    {
                if(isset($value) && $value != ""){
                    $antixss->setEncoding($value, "UTF-8");
                    if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

                        $data=array('success'=>'','failure'=>'Invalid Input.');
                        echo json_encode($data);exit;

                    }

                }
                }

            }
            $token=$_POST['token'];
          //  if(!empty($authUserNamespace->user) && $authUserNamespace->user!='' && $authUserNamespace->token==$token)    {
                //$_FILES['imgs']['tmp_name'];
                $result=array();
                $url= BASE."/Userinfoapi?username=".$username;
                $result=$common_obj->call_curl($url);
                $userInfo=(array)json_decode($result,true);
                //echo "<pre>";
                //print_r($userInfo);exit;

              /*  $userInfo['data']['username']=$username;
                $userInfo['data']['comm_email']=$_POST['email_address'];
                $userInfo['data']['name']=$_POST['name'];
                $userInfo['data']['country']=$_POST['country'];

                $userInfo['data']['phone']=$_POST['contact_phone'];

                $userInfo['data']["imgs"]=file_get_contents($_FILES['imgs']['tmp_name']); */



                $profile_ver_code= md5(rand(100000, 999999));
                $upd_arr = array('profile_ver_code'=>$profile_ver_code);
                $upd_qry = $user->update($upd_arr,"username='".$username."'");
                $encodeusername=base64_encode($username);
                if(!empty($_POST['email_address']) && $_POST['email_address']!="")
                {
                $encodeEmail=base64_encode($_POST['email_address']);
                }
                else {
                    $msg="Please provide Email";
                    $data=array('success'=>'','failure'=>$msg);
                        echo json_encode($data);exit;
                }

            if (!filter_var($_POST['email_address'], FILTER_VALIDATE_EMAIL)) {
                     $msg="Please provide valid email.";
                    $data=array('success'=>'','failure'=>$msg);
                        echo json_encode($data);exit;
            }


                if(!empty($_POST['name']) && $_POST['name']!="")
                {
                $encodedName=base64_encode($_POST['name']);
                }else {
                    $msg="Please provide Name";
                    $data=array('success'=>'','failure'=>$msg);
                        echo json_encode($data);exit;
                }

                if(!empty($_POST['country']) && $_POST['country']!="")
                {
                $encodeCountry=base64_encode($_POST['country']);

                }
                else{
                    $msg="Please provide country";
                    $data=array('success'=>'','failure'=>$msg);
                        echo json_encode($data);exit;
                }



                if(!empty($_POST['contact_phone']) && $_POST['contact_phone']!="")
                {
                $encodePhone=base64_encode($_POST['contact_phone']);
                }
                else{
                    $msg="Please provide phone";
                    $data=array('success'=>'','failure'=>$msg);
                        echo json_encode($data);exit;
                }

                 if (!preg_match('/^[0-9]*$/', $_POST['contact_phone'])) {
                    $msg="Please provide number only.";
                    $data=array('success'=>'','failure'=>$msg);
                        echo json_encode($data);exit;
                        } else {
                    // Continue
                        }

                   if($_FILES['imgs']['name']!=""){

                       if($_FILES['imgs']['type']=="image/jpeg" || $_FILES['imgs']['type']=="image/gif" || $_FILES['imgs']['type']=="image/png" || $_FILES['imgs']['type']=="image/jpg" )
                           {
                               $size=round(($_FILES['imgs']['size']/1024), 2);

                           if($size > 2048.00){
                                   $data=array('success'=>'','failure'=>'Maximum file size exceeds');
                                    echo json_encode($data);exit;

                           }
                           else{
                        $encodeImage="";

                        if (isset($_FILES["imgs"]["name"])){
                            $image_content=$_FILES['imgs']['tmp_name'];
                            $encodeImage="true";
                                $datatemp = file_get_contents($image_content);

                                $tdate = strtotime(date("Y-m-d H:i:s"));
                                 $new = 'images/updateprofile/'.$username.'.jpg';
                                 $mov=move_uploaded_file($image_content, "$new");

                /*    $datatemp = file_get_contents($image_content);

                    $tdate = strtotime(date("Y-m-d H:i:s"));
                     $new =BASEPATH.'images/updateprofile/'.$tdate.'.jpg';
                    $encodeImage = base64_encode('http://'.BASE.'/images/updateprofile/'.$tdate.'.jpg');
                     // $domain_path   =$imagename.'_'.$tdate.'.jpg';
                    file_put_contents($new, $datatemp);*/
                            }
                           }
               }
               else{
                   $data=array('success'=>'','failure'=>'Invalid file type uploaded');
                            echo json_encode($data);exit;

               }

                   }

                //$userInfo['data']["imgs"]=$_POST['imgs'];
                //echo $imgdata;exit;
                //$data = file_get_contents($_FILES['photo']['tmp_name']);
            //    $updateUserAccount=$common_obj->updateUserAccount($userInfo);
                 $time = new Zend_Db_Expr('NOW()');
                 $confirmation_url = BASE."/Confirmchanges?t=$time&u=$encodeusername&n=$encodedName&e=$encodeEmail&c=$encodeCountry&p=$encodePhone&cd=$profile_ver_code&imgs=$encodeImage";


                $email = "<div style='padding: 10px; border: solid thin black'><div style='padding: 0px;'>
                    <img src='".BASE."/images/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div>
                Profile  Updation</h2><br/>Dear ".$username.", <br/><p>A request for profile update has been initiated for the account related to this email address. If it wasn't you who initiated this request, please report us to gbspamreport@gmail.com or send us query through contact form on website. If it was you, please follow the link below to confirm your profile details updations:</p><p><a href = '".$confirmation_url."'>".$confirmation_url."</a></p></div>";

                          if(isset($userInfo['data']["comm_email"]) && $userInfo['data']["comm_email"]!='')
                          {
                             $to = $userInfo['data']["comm_email"];
                          }
                          else
                          {
                            $to = $userInfo['data']["email_address"];
                          }
                            $to = $to;
                            $from = 'support@gainbitcoin.com';
                            $replyTo = 'thegainbitcoinhelp@gmail.com';
                            $subject = 'Profile Updation Request !';
                            $message = $email;
                            $htmlMessage = $email;
                            $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);


                $data=array('success'=>'success','failure'=>'');
                echo json_encode($data);exit;
          /*  }
            else
            {
                $data=array('success'=>'','failure'=>'Invalid Request Found.');
                echo json_encode($data);exit;
            } */
        }
        catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }

    }
    /****** user profile update ******/

    public function updatepasswordAction()
    {
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $misc_obj=new Gbc_Model_Custom_Miscellaneous;
        $user_obj = new Gbc_Model_DbTable_Userinfo();
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $antixss = new Gbc_Model_Custom_StringLimit();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
    //    print_r($_POST);
        /*foreach($_POST as $key => $value)
        {

            if(isset($value) && $value != ""){
                $antixss->setEncoding($value, "UTF-8");
                if ($antixss->setFilter($value, "black", "pwd") == "invalidInput"){

                    $data=array('success'=>'','failure'=>'Invalid Input.');
                    echo json_encode($data);exit;

                }

            }

        } */
        $token=$_POST['token'];
 //       if(!empty($authUserNamespace->user) && $authUserNamespace->user!='' && $authUserNamespace->token==$token)    {

            $result=array();
            $url= BASE."/Userinfoapi?username=".$authUserNamespace->user;
            $result=$common_obj->call_curl($url);
            $userInfo=(array)json_decode($result,true);

            $salt = $userInfo['data']["salt"];
            $username=$authUserNamespace->user;
            $pwd =$misc_obj->encryptPassword($_POST['old_pass'], $salt);
            $newPass=$misc_obj->encryptPassword($_POST['new_pass'], $salt);
    //        echo $pwd;
    //        echo " ".$userInfo['data']["password"];
    //        echo " ".$_POST['old_pass'];
            if($_POST['new_pass']!=$_POST['repeat_pass'])
            {
                $data=array('success'=>'','failure'=>'New Password and Repeat password not Match');
                echo json_encode($data);exit;
            }


            else if($pwd===$userInfo['data']["password"])
            {
                $description = "password has been changed from ".$pwd." to ".$newPass;
                $update_arr=array('password'=>$newPass,'salt'=>$salt,'updated_on'=>new Zend_Db_Expr('NOW()'));
                $update_data=$user_obj->update($update_arr,$DB->quoteInto("username = ?",$username));
                $saveUserLog =$common_obj-> saveUserLog($username,"user_info",$description);
                $data=array('success'=>'success','failure'=>'');
                echo json_encode($data);exit;
            }
            else
            {
                $data=array('success'=>'','failure'=>'Old password does not match.');
                echo json_encode($data);exit;
            }
  /*      }
        else
        {
            $data=array('success'=>'','failure'=>'Invalid Request Found.');
            echo json_encode($data);exit;
        }
*/
    }
    public function updatecontactAction()
    {
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        require("library/PHPGangsta/GoogleAuthenticator.php");
        try {
            $misc_obj=new Gbc_Model_Custom_Miscellaneous;
            $user_obj = new Gbc_Model_DbTable_Userinfo();
            $profile_cont_obj=new Gbc_Model_DbTable_Profilecontact();
            $common_obj=new Gbc_Model_Custom_CommonFunc;
            $antixss = new Gbc_Model_Custom_StringLimit();
            $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

            foreach($_POST as $key => $value)
            {

                if(isset($value) && $value != ""){
                    $antixss->setEncoding($value, "UTF-8");
                    if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

                        $data=array('success'=>'','failure'=>'Invalid Input.');
                        echo json_encode($data);exit;

                    }

                }

            }

            if(!empty($_POST["otp"]) && $_POST["otp"]!=''){
                $result=array();
                $url= BASE."/Userinfoapi?username=".$authUserNamespace->user;

                $result=$common_obj->call_curl($url);
                $userInfo=(array)json_decode($result,true);
                $otp = $userInfo['data']["otp"];
                $username=$userInfo['data']["username"];
                $otpSent = $_POST["otp"];
                if($otp!=$otpSent){
                    $data=array('success'=>'','failure'=>'Incorrect One Time Password. Please generate a new OTP.');
                    echo json_encode($data);exit;
                }
                $update_arr=array('otp'=>'');
                $update_data=$user_obj->update($update_arr,$DB->quoteInto("username = ?",$username));
            }
            /*else if(!empty($_POST["authcode"]) && $_POST["authcode"]!='')
             {
             $ga = new PHPGangsta_GoogleAuthenticator();
             $secret= $authUserNamespace ->secret;
             //echo $secret;
             $checkResult = $ga->verifyCode($secret, $_POST['authcode'], 2);    // 2 = 2*30sec clock tolerance
             if ($checkResult) {

             } else {
             $data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP.');
             echo json_encode($data);exit;
             }
             }*/
            else
            {

                $data=array('success'=>'','failure'=>'One Time Password not found. Please click the back button and generate a new OTP.');
                echo json_encode($data);exit;
            }
            if(!empty($_POST['full_name'])){ $full_name=(strip_tags(trim($_POST['full_name'])));}else{$full_name="";}
            if(!empty($_POST['contact_email'])){$contact_email=(strip_tags(trim($_POST['contact_email'])));}else{$contact_email="";}
            if(!empty($_POST['country'])){$country=(strip_tags(trim($_POST['country'])));}else{$country="";}
            if(!empty($_POST['contact_phone'])){$contact_phone=(strip_tags(trim($_POST['contact_phone'])));}else{$contact_phone="";}

            //$user_row = $profile_cont_obj->fetchRow($profile_cont_obj->select()
            //->where("username='".$username."'"));

            if(isset($userInfo) && sizeof($userInfo)>0)
            {
                $description = "";
                $email = "Dear ".$username.",<br/>";
                if($full_name != $userInfo['data']['name']){
                    $description .= "full_name has been changed from".$userInfo['data']['name']." to ".$full_name;
                }
                if($country != $userInfo['data']['country']){
                    $description .= "country has been changed from".$userInfo['data']['country']." to ".$country;
                }
                if($contact_phone != $userInfo['data']['phone']){
                    $description .= "contact_phone has been changed from".$userInfo['data']['phone']." to ".$contact_phone;
                    //$savePhone = savePhone($username,$country,$contact_email, $contact_phone);
                    $email .= "<div>Your Contact No. has been changed from ".$userInfo['data']['phone']." to ".$contact_phone." </div>";
                }

                if($contact_email != $userInfo['data']['comm_email']){
                    $description .= "contact_email has been changed from".$userInfo['data']['email_address']." to ".$contact_email;
                    $email .= "<div><br/>Your e-mail id has been changed from ".$userInfo['data']['email_address']." to ".$contact_email." </div>";
                }


                $update_arr=array('name'=>$full_name,'email_address'=>$contact_email,'country'=>$country,'phone'=>$contact_phone);
                $update_data=$user_obj->update($update_arr,$DB->quoteInto("username=?",$username));



                if(!empty($description)){
                    //echo $description;
                    $saveUserLog = $common_obj->saveUserLog($username,"user_info",$description);

                }
                //echo $saveUserLog;
                if(!empty($email)){

                    //echo $email; exit;
                    /*                     $mail_to = array($row['contact_email'], $contact_email);
                     sendMail(implode(',' , $mail_to),"admin@gainbitco.in", "Contact Details Updated", $email); */
                //$common_obj->sendMail($userInfo['data']['comm_email'], "admin@gainbitco.in", "Contact Details Updated", $email);
                //$common_obj->sendMail($contact_email, "admin@gainbitco.in", "Contact Details Updated", $email);

                $to = $userInfo['data']['comm_email'];
                $from = 'admin@gainbitco.in';
                $replyTo = 'thegainbitcoinhelp@gmail.com';
                $subject = 'Contact Details Updated';
                $message = $email;
                $htmlMessage = $email;
                $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);

                $to = $contact_email;
                $from = 'admin@gainbitco.in';
                $replyTo = 'thegainbitcoinhelp@gmail.com';
                $subject = 'Contact Details Updated';
                $message = $email;
                $htmlMessage = $email;
                $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);

                }

            }
            else
            {
                //$insert_arr=array('username'=>$username,'full_name'=>$full_name,'country'=>$country,'contact_email'=>$contact_email,'contact_phone'=>$contact_phone);
                //$insert_data=$profile_cont_obj->insert(insert_arr,"username='$username'");
            }
            $data=array('success'=>'Contact updated successfully.','failure'=>'');
            echo json_encode($data);exit;
        }
        catch(Exception $e)
        {
            $data=array('success'=>'','failure'=>$e->getMessage());
            echo json_encode($data);exit;
        }

    }

    public function sendotpAction()
    {

        try {
            $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
            $user_obj = new Gbc_Model_DbTable_Userinfo();
            $common_obj=new Gbc_Model_Custom_CommonFunc();
            $sms_log_obj=new Gbc_Model_DbTable_Smslog();
            $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

            $username=$authUserNamespace->user;
            $result=array();
            $url= BASE."/Userinfoapi?username=".$username;

            $result=$common_obj->call_curl($url);
            $userInfo=(array)json_decode($result,true);
            $username=$userInfo['data']['username'];
            $pass= rand(100000, 999999);

            $update_arr=array('otp'=>$pass);
            $update_data=$user_obj->update($update_arr,$DB->quoteInto("username=?",$username));
            $tomail = $userInfo['data']["comm_email"];
            if(!isset($userInfo['data']["comm_email"]) || $userInfo['data']["comm_email"]=='')
            {
                $tomail = $userInfo['data']["email_address"];
            }

            if(!empty($_POST['withdrawal_type']) && $_POST['withdrawal_type'] == 2){
                $withdrawal_type = " for transfer fund";
                $email = "<div style='padding: 10px; border: solid thin black'><div style='padding: 0px;'>
                    <img src='".BASE."/images/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div>
                    <p>Hello ".$username.",</p>
                    <p>A request for one time password for transfer fund is initiated.</p>
                    <p>Your OTP is: ".$pass."</p></div>";
                    $subject="Your One Time Password for Fund Transfer";

                // sendMail($userInfo["email_address"], "admin@gainbitco.in", "Your One Time Password for Withdrawals", $email);
                //$common_obj->sendMail($userInfo['data']["comm_email"], "admin@gainbitco.in", "Your One Time Password for Fund Transfer", $email);
            }else if(!empty($_POST['withdrawal_type']) && $_POST['withdrawal_type'] == 3){
                $withdrawal_type = " for withdrawals";
                $email = "<div style='padding: 10px; border: solid thin black'><div style='padding: 0px;'>
                    <img src='".BASE."/images/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div>
                    <p>Hello ".$username.",</p>
                    <p>A request for one time password for withdrawal has been initiated.</p>
                    <p>Your OTP is: ".$pass."</p></div>";
                    $subject="Your One Time Password for Withdrawals";
                // sendMail($userInfo["email_address"], "admin@gainbitco.in", "Your One Time Password for Withdrawals", $email);
                //$common_obj->sendMail($userInfo['data']["comm_email"], "admin@gainbitco.in", "Your One Time Password for Withdrawals", $email);

            }else{
                $withdrawal_type = " ";
                $email = "<div style='padding: 10px; border: solid thin black'><div style='padding: 0px;'>
                    <img src='".BASE."/images/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div>
                    <p>Hello ".$username.",</p>
                    <p>A request for one time password has been initiated.</p>
                    <p>Your OTP is: ".$pass."</p></div>";
                        $subject="One Time Password";
                // sendMail($userInfo["email_address"], "admin@gainbitco.in", "Your One Time Password for Withdrawals", $email);
                //$common_obj->sendMail($userInfo['data']["comm_email"], "admin@gainbitco.in", "One Time Password", $email);
            }
                    $to = $tomail;
                    $from = 'support@gainbitcoin.com';
                    $replyTo = 'thegainbitcoinhelp@gmail.com';
                    $subject = subject;
                    $message = $email;
                    $htmlMessage = $email;
                    $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);

            $numbers = array($userInfo['data']["phone"]);
            $contact_phone=$userInfo['data']["phone"];
            if(!empty($contact_phone)){
                $message = rawurlencode(addslashes("Hello ".$username.", Greetings from Gainbitcoin. Your One Time Password ".$withdrawal_type." is ".$pass.". Pls don't share with anyone. Visit gainbitcoin.com"));
                // $message = "Greetings from Gainbitcoin. Your One Time Password ".$withdrawal_type." is ".$pass.". Pls don't share with anyone. Visit gainbitcoin.com";
                $numbers = implode(',', $numbers);

                // Prepare data for POST request
                // $data = array('username' => $MSGusername, 'hash' => $MSGhash, 'numbers' => $numbers, "sender" => $MSGsender, "message" => $message);
                $data = 'login='.MSGusername.'&pword='.MSGhash.'&mobnum='.$numbers."&senderid=".MSGsender."&msg=".$message;

                //}
                // var_dump($userInfo);
                // var_dump($contactMail);

                $MsgResponse = $common_obj->sendMSG($data);

                // $MsgResponse = 1;
                if(!empty($MsgResponse)){
                    $authUserNamespace->button_locked = time();
                    //echo "fdsxsfsddf";exit;
                    //$insert_arr=array('username'=>$username,'mobile'=>$numbers,'msg'=>$message,'response_code'=>$MsgResponse);
                    //$insert_data=$sms_log_obj->insert($insert_arr,"username='$username'");

                }

            }

            $data=array('success'=>'success','failure'=>'','data'=>$pass);
            echo json_encode($data);exit;
        }
        catch(Excetption $e)
        {
            $data=array('success'=>'','failure'=>'Something went wrong. Please try again.');
            echo json_encode($data);exit;
        }
    }
    public function enableauthAction()
    {
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        if(empty($authUserNamespace->user) || $authUserNamespace->user=='')
        {
            echo "You do not have access to this area";exit;
        }
        require("library/PHPGangsta/GoogleAuthenticator.php");
        $user_obj = new Gbc_Model_DbTable_Userinfo();
        $code=trim($_POST['code']);
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $ga = new PHPGangsta_GoogleAuthenticator();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        $secret= $authUserNamespace ->secret;
        $username=$authUserNamespace->user;


        $checkResult = $ga->verifyCode($secret,$code, 2);    // 2 = 2*30sec clock tolerance
		
        if ($checkResult) {
            $common_obj = new Gbc_Model_Custom_CommonFunc();
            $update_arr=array('authentication_type'=>2,'secret'=>$secret,'updated_on'=>new Zend_Db_Expr('NOW()'));
            $upd=$user_obj->update($update_arr,$DB->quoteInto("username=?",$username));
            $authUserNamespace->gacode="success";
            $data=array('success'=>'success','failure'=>'');
            echo json_encode($data);exit;

        } else {
            $data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP');
            echo json_encode($data);exit;
            //$data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP.');
            //echo "Incorrect One Time Password for 2FA Authentication. Please generate a new OTP"; exit;
        }
    }

    public function disableauthAction()
    {
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        if(empty($authUserNamespace->user) || $authUserNamespace->user=='')
        {
            echo "You do not have access to this area";exit;
        }
        require("library/PHPGangsta/GoogleAuthenticator.php");
        $user_obj = new Gbc_Model_DbTable_Userinfo();
        $code=trim($_POST['code']);
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $user = new Gbc_Model_DbTable_Userinfo();
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $ga = new PHPGangsta_GoogleAuthenticator();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        //$secret= $authUserNamespace ->secret;
        $username=$authUserNamespace->user;

        $userInfo = $common_obj->getUserInfo($username);
        $secret= $userInfo ->secret;

        //echo $secret;
        $checkResult = $ga->verifyCode($secret,$code, 2);    // 2 = 2*30sec clock tolerance
        if ($checkResult) {
            $common_obj = new Gbc_Model_Custom_CommonFunc();
            $update_arr=array('authentication_type'=>1,'updated_on'=>new Zend_Db_Expr('NOW()'));
            $upd=$user_obj->update($update_arr,$DB->quoteInto("username=?",$username));
            $authUserNamespace->gacode="success";
            $data=array('success'=>'success','failure'=>'');
            echo json_encode($data);exit;
            //echo "success";exit;
        } else {
            $data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP');
            echo json_encode($data);exit;
            //$data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP.');
            //echo "Incorrect One Time Password for 2FA Authentication. Please generate a new OTP"; exit;
        }
    }

    public function verifyauthAction()
    {
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        if(empty($authUserNamespace->user) || $authUserNamespace->user=='')
        {
            echo "You do not have access to this area";exit;
        }
        require("library/PHPGangsta/GoogleAuthenticator.php");
        $user_obj = new Gbc_Model_DbTable_Userinfo();
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $code=trim($_POST['code']);
        $ga = new PHPGangsta_GoogleAuthenticator();

        $username=$authUserNamespace->user;
        $userInfo = $common_obj->getUserInfo($username);
        $secret= $userInfo ->secret;
        //echo $secret;exit;
        $checkResult = $ga->verifyCode($secret,$code, 2);    // 2 = 2*30sec clock tolerance
        if ($checkResult) {
            $data=array('success'=>'success','failure'=>'');
            echo json_encode($data);exit;
        } else {
            $data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP');
            echo json_encode($data);exit;
        }
    }
    public function enablecategotyAction()
    {
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $antixss = new Gbc_Model_Custom_StringLimit();
        foreach($_POST as $key => $value)
        {

            if(isset($value) && $value != ""){
                $antixss->setEncoding($value, "UTF-8");
                if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

                    echo "failed";exit;

                }

            }

        }
        $token=$_POST['token'];
      //  if(!empty($authUserNamespace->user) && $authUserNamespace->user!='' && $authUserNamespace->token==$token)  {
            $cat_id=$_POST['cat_id'];
            $user_category_obj = new Gbc_Model_DbTable_Usercategory();
            $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
            $username=$authUserNamespace->user;
            $ins_arr=array('username'=>$username,'category_id'=>$cat_id,'status'=>'1');
            $ins_query=$user_category_obj->insert($ins_arr);
            echo "Category added successfully";exit;
      /*  }
        else
        {
            echo "Invalid Request Found."; exit;
        } */
    }
    public function chooseplacementAction()
    {
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $user_obj=new Gbc_Model_DbTable_Userinfo();
        $antixss = new Gbc_Model_Custom_StringLimit();

        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        foreach($_POST as $key => $value)
        {

            if(isset($value) && $value != ""){
                $antixss->setEncoding($value, "UTF-8");
                if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

                    echo "failed";exit;

                }

            }

        }
        $token=$_POST['token'];
//        if(!empty($authUserNamespace->user) && $authUserNamespace->user!='' && $authUserNamespace->token==$token)  {
            $place=$_POST['place'];
            $username=$authUserNamespace->user;

            $upd_data=array('placement'=>$place,'updated_on'=>new Zend_Db_Expr('NOW()'));
            $upd_qry=$user_obj->update($upd_data,$DB->quoteInto("username=?",$username));
            if(!empty($upd_qry))
            {
                echo "success";exit;
            }
            else
            {
                echo "failed";exit;
            }
  /*      }else
        {
            echo "failed";exit;
        } */
    }

    public function setpinAction()
    {
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $user_obj=new Gbc_Model_DbTable_Userinfo();
        $antixss = new Gbc_Model_Custom_StringLimit();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        if($_POST['pin']=='')
        {
            $arr=array('success'=>'','failure'=>'Please enter Login Pin.');
            echo json_encode($arr);exit;
        }
        if(strlen($_POST['pin'])!=4)
        {
            $arr=array('success'=>'','failure'=>'Please enter 4 digit Login Pin.');
            echo json_encode($arr);exit;
        }

        foreach($_POST as $key => $value)
        {

            if(isset($value) && $value != ""){
                $antixss->setEncoding($value, "UTF-8");
                if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

                    $arr=array('success'=>'','failure'=>'Invalid Input.');
                    echo json_encode($arr);exit;

                }

            }

        }

        $token=$_POST['token'];
      //  if(!empty($authUserNamespace->user) && $authUserNamespace->user!='' && $authUserNamespace->token==$token)  {
            $pin=$_POST['pin'];
            $username=$authUserNamespace->user;

            $upd_data=array('login_pin'=>$pin,'updated_on'=>new Zend_Db_Expr('NOW()'));
            $upd_qry=$user_obj->update($upd_data,$DB->quoteInto("username=?",$username));
            if(!empty($upd_qry))
            {
                $arr=array('success'=>'success','failure'=>'','data'=>'Login Pin set successully.');
                echo json_encode($arr);exit;
            }
            else
            {
                $arr=array('success'=>'','failure'=>'Failed to set Login Pin. Please try again.');
                echo json_encode($arr);exit;
            }
       /* }else
        {
            $arr=array('success'=>'','failure'=>'Invalid Token.');
            echo json_encode($arr);exit;
        } */
    }




}
