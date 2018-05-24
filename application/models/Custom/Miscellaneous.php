<?php

class Gbc_Model_Custom_Miscellaneous{
	/**
	 * @author Parth Arora <arora.parth@gmail.com>
	 * @version 1.0
	 * @Desc
	 * The function takes an optional input specifying the length of the password to be generated, if no
	 * length is specified the default password length is taken as 7 characters long. The password generated
	 * will be alpha-numeric
	 *
	 * @link http://www.friskwave.com
	 * @param int - length of password (optional)
	 * @return randomly generated alpha-numeric password
	 */
	public function randomPassword($password_length=7){

		$chars = "abcdefghijklmnopqrstuvwxyz123456789";
		srand((double)microtime()*1000000);
		$i = 0;
		$pass = '' ;

		while($i <= $password_length){

			$num = rand() % 34;
			$tmp = substr($chars, $num, 1);
			$pass = $pass . $tmp;
			$i++;
		}

		return $pass;
	}

	/**
	 * @author Parth Arora <arora.parth@gmail.com>
	 * @version 1.0
	 * @Desc
	 * to detect the user Browser and Operating System
	 *
	 * @link http://www.friskwave.com
	 * @param string - $_SERVER user-agent string
	 * @return array - an associative Array of Client Agent Properties
	 */
	public function detectUserAgent(){

		$clientProps = array();
		$userAgent = $_SERVER['HTTP_USER_AGENT'];

		// detect Operating System
		// array of Possible Operating Systems
		$OSList = array(
			'Windows 3.11'=>'Win16','Windows 95'=>'Windows 95','Windows 95'=>'Win95','Windows 95'=>'Windows_95',
			'Windows 98'=>'Windows 98','Windows 98'=>'Win98','Windows 2000'=>'Windows NT 5.0','Windows 2000'=>'Windows 2000',
			'Windows XP'=>'Windows NT 5.1','Windows XP'=>'Windows XP','Windows Server 2003'=>'Windows NT 5.2',
			'Windows Vista'=>'Windows NT 6.0','Windows 7'=>'Windows NT 7.0','Windows NT 4.0'=>'Windows NT 4.0',
			'Windows NT 4.0'=>'WinNT4.0','Windows NT 4.0'=>'WinNT','Windows NT 4.0'=>'Windows NT','Windows ME'=>'Windows ME',
			'Open BSD'=>'OpenBSD','Sun OS'=>'SunOS','Linux'=>'Linux','Linux'=>'X11','Mac OS'=>'Mac_PowerPC','Mac OS'=>'Macintosh',
			'QNX'=>'QNX','BeOS'=>'BeOS','OS/2'=>'OS/2','Search Bot'=>'nuhk','Search Bot'=>'Googlebot','Search Bot'=>'Yammybot',
			'Search Bot'=>'Openbot','Search Bot'=>'Slurp','Search Bot'=>'MSNBot','Search Bot'=>'Ask Jeeves/Teoma','Search Bot'=>'ia_archiver'
			);

			foreach($OSList as $CurrOS=>$Match){
				if(stristr($userAgent,$Match)){
					$clientProps['platform'] = $CurrOS;
					break;
				}
			}

			// detect browser
			if(strpos($userAgent,'Gecko')){
				if(strpos($userAgent,'Netscape'))$clientProps['browser'] = 'Netscape (Gecko/Netscape)';
				elseif(strpos($userAgent,'Firefox'))$clientProps['browser'] = 'Mozilla Firefox (Gecko/Firefox)';
				else $clientProps['browser'] = 'Mozilla (Gecko/Mozilla)';
			}
			elseif(strpos($userAgent,'MSIE')){
				if(strpos($userAgent,'Opera'))$clientProps['browser'] = 'Opera (MSIE/Opera/Compatible)';
				else $clientProps['browser'] = 'Internet Explorer (MSIE/Compatible)';
			}
			else $clientProps['browser'] = 'Others browsers';

			// detect client Ip-Address
			if(!empty($_SERVER["HTTP_CLIENT_IP"]))$clientProps['ip'] = ''.$_SERVER["HTTP_CLIENT_IP"].' ';
			elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))$clientProps['ip'] = ''.$_SERVER["HTTP_X_FORWARDED_FOR"].' ';
			elseif(!empty($_SERVER["REMOTE_ADDR"]))$clientProps['ip'] = ''.$_SERVER["REMOTE_ADDR"].' ';

			return $clientProps;
	}

	/**
	 * @author Parth Arora <arora.parth@gmail.com>
	 * @version 1.0
	 * @Desc
	 * function to get the url of the page that the client is currently visiting
	 *
	 * @link http://www.friskwave.com
	 * @return string - URL of the current page along with port number and the
	 * query string
	 */
	public function getCurrentUrl(){

		$pageURL = 'http';

		if($_SERVER["HTTPS"] == "on"){
			$pageURL .= "s";
		}

		$pageURL .= "://";

		if($_SERVER["SERVER_PORT"] != "80"){
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		}
		else{
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}

		return $pageURL;
	}

	public function encryptPassword($pwd, $salt)
	{
		$hashed_password = sha1($salt . $pwd);
		return $hashed_password ;
			
	}
	function noError($errorArr) {
		$noError = false;
		if(array_key_exists(-1, $errorArr["errCode"]))
			$noError=true;
		return $noError;
	}

function generateStrongPassword($length = 9, $add_dashes = false, $available_sets = 'ludse')
	{
		$sets = array();
		if(strpos($available_sets, 'l') !== false)
			$sets[] = 'abcdefghjkmnpqrstuvwxyz';
		if(strpos($available_sets, 'u') !== false)
			$sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
		if(strpos($available_sets, 'd') !== false)
			$sets[] = '123456789';
		if(strpos($available_sets, 's') !== false)
			$sets[] = '!@';
		if(strpos($available_sets, 'e') !== false)
			$sets[] = '#';

		$all = '';
		$password = '';
		foreach($sets as $set)
		{
			$password .= $set[array_rand(str_split($set))];
			$all .= $set;
		}
		// var_dump ($password);
		$all = str_split($all);
		// var_dump($all[array_rand($all)]);
		for($i = 0; $i < $length - count($sets); $i++)
			$password .= $all[array_rand($all)];
		$password = str_shuffle($password);
		if(!$add_dashes)
			return $password;
		$dash_len = floor(sqrt($length))*2;
		$dash_str = '';
		 while(strlen($password) > $dash_len)
		{
			$dash_str .= substr($password, 0, $dash_len) . '-';
			$password = substr($password, $dash_len);
		}
		$dash_str .= $password;
		return $dash_str; 
		// return $all; 
	}




	function checkSubadminPassword($username, $password){
	//check if username already exists

		$user_info_table = new Gbc_Model_DbTable_Subadminusers();
		$userInfo = $user_info_table->fetchRow($user_info_table->select()
		->setIntegrityCheck(false)
		->from(array('u' =>'sub_admin_users'),array('u.salt','u.password' ))
		->where('u.email =?',trim($username)));

//print_r($userInfo->password);
		if($userInfo!="" && sizeof($userInfo)>0){
	
			$salt = $userInfo->salt;
			$pwd = $this->encryptPassword($password, $salt);
//		 var_dump($pwd);
			if($pwd==$userInfo->password){
				$returnArr["errCode"]=array("-1"=>-1);
				$returnArr["errMsg"]="Login Successful.";
			} else {
				$returnArr["errCode"][10]=10;
				$returnArr["errMsg"]="Incorrect Username/Password. Please try to login again.";
			}
		} else {
			//error fetching user info

			$returnArr["errCode"][5] = $userInfo["errCode"];
			$returnArr["errMsg"] = "Incorrect Username/Password. Please try to login again.";
	}

		return $returnArr;
}
	function getSubadminUserInfo($email)
	{
		$returnArr = array();

		$user_info_table = new Gbc_Model_DbTable_Subadminusers();

		$user_info = $user_info_table->fetchRow($user_info_table->select()
		->setIntegrityCheck(false)
		->from(array('u' =>'sub_admin_users'),array('u.salt','u.password' ))
		->where('u.email =?',trim($email)));

		return $user_info;
	}
	function getUserInfo($username) {

		$user_row = array();
		$user = new Gbc_Model_DbTable_Userinfo();
		$user_row = $user->fetchRow($user->select()
		->setIntegrityCheck(false)
		->from(array('u' =>'user_info'),array(  'username' ,'email_address','comm_email' ,'name' ,'country','phone' ,'salt' ,'password' ,'session_start_time' ,'session_id' ,'referrer_id' ,'otp','wallet_addr' ,'sponsor_id' ,'ref_sponsor_id' ,'lock_status' ,'user_type','isActiveId','isLevelFull' ,'login_date','assign_date','ip_address' ,'created_on' ,'updated_on' ,'isVerified' ,'payment_status_hold' ,'b2_status','comment' ,'binaryUser' ,'authentication_type','placement','secret','login_pin','last_failed_login','last_successful_login'))
		->where("username =?",$username)
		->orwhere("sponsor_id =?",$username));

		return $user_row;
	}
	function get_client_ip() {
		$ipaddress = '';
		if (getenv('HTTP_CLIENT_IP'))
		$ipaddress = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
		$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
		$ipaddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
		$ipaddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
		$ipaddress = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
		$ipaddress = getenv('REMOTE_ADDR');
		else
		$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}
	function generateSalt() {

		$salt_length = 12;

		$salt = substr(md5(uniqid()), 0, $salt_length);

		return $salt;
	}


	function encryptPassword1($pwd, $salt) {

		$hashed_password = sha1($salt . $pwd);

		return $hashed_password;

	}
	function insertUpdateBinaryNetwork($ref){
		$bin_user_ref_object=new Gbc_Model_DbTable_Binaryuserreferences();
		$chk_Parent = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
		->where("username=?" ,$ref["username"]));

		if(isset($chk_Parent) && sizeof($chk_Parent)>0)
		{
			$bin_net_details_obj=new Gbc_Model_DbTable_Binarynetworkdetail();
			$chk_depth1 = $bin_net_details_obj->fetchRow($bin_net_details_obj->select()
			->where("username=?",$chk_Parent->parent_username));
			$depth = str_replace(',',"','",$chk_depth1->depth);
			$insertDepth = $depth."','".$ref["username"];

			$checkExistUser = $bin_net_details_obj->fetchAll($bin_net_details_obj->select()
			->where("username=?" , $ref["username"]));

			if(isset($checkExistUser) && sizeof($checkExistUser)>0)
			{

			}
			else
			{
				$arr_ins=array('username'=>$ref["username"],'depth'=>$insertDepth,'status'=>'1');
				$ins_data=$bin_net_details_obj->insert($arr_ins);

			}
			$arr_upd=array('status'=>'1','updated_on'=>new Zend_Db_Expr('NOW()'));
			$upd_data=$bin_net_details_obj->update($arr_upd,"username in ('$insertDepth')");
			return "success";
		}
		return "success";

	}


	function createUserrefrences($ref) {
		$bin_user_ref_object=new Gbc_Model_DbTable_Binaryuserreferences();
		$comm_obj=new Gbc_Model_Custom_CommonFunc();
		$user = new Gbc_Model_DbTable_Userinfo();

		$checkUnique=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
		->setIntegrityCheck(false)
		->from(array('b'=>"binary_user_refences"),array('count(b.username) as uniq_child'))
		->where("username=?", trim($ref["username"])));

		if (!empty($checkUnique) && $checkUnique->uniq_child != 0) {
			return 'User already exist';
			exit();
		}


		$check_user = $user->fetchRow($user->select()
		->where("binaryUser is NOT NULL")
		->where("username=?",trim($ref["parent_username"]))
		);

		$chk_depth= $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
		->where("parent_username=?" , trim($ref["parent_username"])));

		$allRecords_row =  $bin_user_ref_object->fetchAll($bin_user_ref_object->select()
		->where("parent_username=?",trim($ref["parent_username"])));
			
		$allRecords=sizeof($allRecords_row);

		if($ref["child_position"] =='ref_page' && $ref["parent_username"]!='amitsabnetwork'){
			$comm_obj->getRight($ref["parent_username"],$ref["choice_leg"], $finalRes);
			if(!empty($finalRes)){

				$check_dw=$user->fetchRow($user->select()
				->where("binaryUser is NOT NULL")
				->where("username=?",trim($ref["finalRes"]))
				);
				if(!empty($check_dw) && (sizeof($check_dw)>0) && $check_dw->isActiveId==1){

					$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
					->where("username=?" , trim($finalRes)));

					$depth = $chk_depth1->depth . $ref["username"] . ',';
					$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
					$result=$bin_user_ref_object->insert($query);
					return "success";
					exit();
				}
				else
				{
					$chk_depth_leg=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
					->setIntegrityCheck(false)
					->from(array('b'=>"binary_user_refences"),array('count(username) as total'))
					->where("parent_username=?" , trim($finalRes)));


					if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
						if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
						$comm_obj->getRight($finalRes,$choice, $final);
						if(!empty($final)){

							$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
							->where("username=?" , trim($final)));


							$depth = $chk_depth1->depth . $ref["username"] . ',';
							$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
							$result=$bin_user_ref_object->insert($query);
							return "success";
							exit();
						}else{
							$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
							->where("username=?" , trim($ref["parent_username"])));

							$depth = $chk_depth1->depth . $ref["username"] . ',';

							$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
							$result=$bin_user_ref_object->insert($query);

							return "success";
							exit();
						}
					}else{

						$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
						->where("username='" . trim($finalRes) . "'"));
							

						$depth = $chk_depth1->depth . $ref["username"] . ',';
						$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
						$result=$bin_user_ref_object->insert($query);
							
						return "success";
						exit();
					}

				}

			}
			else
			{
				$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
				->where("username=?" , trim($ref["parent_username"])));
					

				$depth = $chk_depth1->depth . $ref["username"] . ',';
				$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
				$result=$bin_user_ref_object->insert($query);
					
			}

		}


		if($ref["parent_username"]=='amitsabnetwork' && (($allRecords)==1)){

			$comm_obj->getRight($ref["parent_username"],$ref["choice_leg"], $finalRes);
			if(!empty($finalRes)){
				$check_dw=$user->fetchRow($user->select()
				->where("binaryUser is NOT NULL")
				->where("username=?", trim($ref["finalRes"]))
				);

				if(!empty($check_dw) && (sizeof($check_dw)>0) && $check_dw->isActiveId==1){

					$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
					->where("username=?" , trim($finalRes)));

					$depth = $chk_depth1->depth . $ref["username"] . ',';
					$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
					$result=$bin_user_ref_object->insert($query);
					return "success";
					exit();
				}else{
					$chk_depth_leg=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
					->setIntegrityCheck(false)
					->from(array('b'=>"binary_user_refences"),array('count(username) as total'))
					->where("parent_username=?" , trim($finalRes)));



					if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
						if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
						$comm_obj->getRight($finalRes,$choice, $final);
						if(!empty($final)){

							$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
							->where("username=?" , trim($final)));


							$depth = $chk_depth1->depth . $ref["username"] . ',';
							$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
							$result=$bin_user_ref_object->insert($query);
							return "success";
							exit();
						}else{
							$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
							->where("username=?" , trim($ref["parent_username"])));

							$depth = $chk_depth1->depth . $ref["username"] . ',';

							$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
							$result=$bin_user_ref_object->insert($query);

							return "success";
							exit();
						}
					}
					else
					{
						$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
						->where("username=?" , trim($finalRes)));
							

						$depth = $chk_depth1->depth . $ref["username"] . ',';
						$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
						$result=$bin_user_ref_object->insert($query);
							
						return "success";
						exit();
					}

				}
			}
			else
			{
				$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
				->where("username=?" , trim($ref["parent_username"])));
					

				$depth = $chk_depth1->depth . $ref["username"] . ',';
				$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
				$result=$bin_user_ref_object->insert($query);
			}

		}


		if($ref["parent_username"]=='amitsabnetwork' && (($allRecords)==2)){
			$comm_obj->getRight($ref["parent_username"],$ref["choice_leg"], $finalRes);
			if(!empty($finalRes)){
				$check_dw=$user->fetchRow($user->select()
				->where("binaryUser is NOT NULL")
				->where("username=?", trim($ref["finalRes"]))
				);

				if(!empty($check_dw) && (sizeof($check_dw)>0) && $check_dw->isActiveId==1){

					$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
					->where("username=?" , trim($finalRes)));

					$depth = $chk_depth1->depth . $ref["username"] . ',';
					$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
					$result=$bin_user_ref_object->insert($query);
					return "success";
					exit();
				}else{
					$chk_depth_leg=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
					->setIntegrityCheck(false)
					->from(array('b'=>"binary_user_refences"),array('count(username) as total'))
					->where("parent_username=?" , trim($finalRes)));



					if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
						if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
						$comm_obj->getRight($finalRes,$choice, $final);
						if(!empty($final)){

							$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
							->where("username=?" , trim($final)));


							$depth = $chk_depth1->depth . $ref["username"] . ',';
							$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
							$result=$bin_user_ref_object->insert($query);
							return "success";
							exit();
						}else{
							$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
							->where("username=?" , trim($ref["parent_username"])));

							$depth = $chk_depth1->depth . $ref["username"] . ',';

							$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
							$result=$bin_user_ref_object->insert($query);

							return "success";
							exit();
						}
					}
					else
					{
						$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
						->where("username=?" , trim($finalRes)));
							

						$depth = $chk_depth1->depth . $ref["username"] . ',';
						$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
						$result=$bin_user_ref_object->insert($query);
							
						return "success";
						exit();
					}

				}
			}
			else
			{
				$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
				->where("username=?" , trim($ref["parent_username"])));
					

				$depth = $chk_depth1->depth . $ref["username"] . ',';
				$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
				$result=$bin_user_ref_object->insert($query);
			}

		}

			if(($allRecords == 3)) {
			$comm_obj->getRight($ref["parent_username"], $ref["choice_leg"], $finalRes);
			if(!empty($finalRes)){
				$check_dw=$user->fetchRow($user->select()
				->where("binaryUser is NOT NULL")
				->where("username=?", trim($ref["finalRes"]))
				);

				if(!empty($check_dw) && (sizeof($check_dw)>0) && $check_dw->isActiveId==1){
					$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
					->where("username=?" , trim($finalRes)));

					$depth = $chk_depth1->depth . $ref["username"] . ',';
					$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
					$result=$bin_user_ref_object->insert($query);
					return "success";
					exit();
				}
				else
				{
					$chk_depth_leg=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
					->setIntegrityCheck(false)
					->from(array('b'=>"binary_user_refences"),array('count(username) as total'))
					->where("parent_username=?" , trim($finalRes)));
					if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
						if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
						$comm_obj->getRight($finalRes,$choice, $final);
						if(!empty($final)){

							$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
							->where("username=?" , trim($final)));


							$depth = $chk_depth1->depth . $ref["username"] . ',';
							$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
							$result=$bin_user_ref_object->insert($query);
							return "success";
							exit();
						}else{
							$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
							->where("username=?" , trim($ref["parent_username"])));

							$depth = $chk_depth1->depth . $ref["username"] . ',';

							$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
							$result=$bin_user_ref_object->insert($query);

							return "success";
							exit();
						}
					}
					else{

						$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
						->where("username=?" , trim($finalRes)));
							

						$depth = $chk_depth1->depth . $ref["username"] . ',';
						$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
						$result=$bin_user_ref_object->insert($query);
							
						return "success";
						exit();
					}
				}
			}
			else
			{
				$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
				->where("username=?" , trim($ref["parent_username"])));
					

				$depth = $chk_depth1->depth . $ref["username"] . ',';
				$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
				$result=$bin_user_ref_object->insert($query);
					
			}
		}


		if ((($allRecords) == 2)){
			$comm_obj->getRight($ref["parent_username"], $ref["choice_leg"], $finalRes);
			if(!empty($finalRes)){
				$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
				->where("username=?" , trim($finalRes)));

				$depth = $chk_depth1->depth . $ref["username"] . ',';
				$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
				$result=$bin_user_ref_object->insert($query);
					
				return "success";
				exit();
			}
			else
			{
				$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
				->where("username=?" , trim($ref["parent_username"])));
					

				$depth = $chk_depth1->depth . $ref["username"] . ',';
				$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
				$result=$bin_user_ref_object->insert($query);
					
				return "success";
				exit();
			}

		}
		else
		{
			if (($allRecords) == 1) {
				$comm_obj->getRight($ref["parent_username"],$ref["choice_leg"], $finalRes);
				if(!empty($finalRes)){
					$check_dw=$user->fetchRow($user->select()
					->where("binaryUser is NOT NULL")
					->where("username=?", trim($ref["finalRes"]))
					);
					if(!empty($check_dw) && (sizeof($check_dw)>0) && $check_dw->isActiveId==1){

						$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
						->where("username=?" , trim($finalRes)));

						$depth = $chk_depth1->depth . $ref["username"] . ',';
						$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
						$result=$bin_user_ref_object->insert($query);
						return "success";
						exit();
			 	}
			 	else
			 	{
			 		$chk_depth_leg=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
			 		->setIntegrityCheck(false)
			 		->from(array('b'=>"binary_user_refences"),array('count(username) as total'))
			 		->where("parent_username=?" , trim($finalRes)));
			 		if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
			 			if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
			 			$comm_obj->getRight($finalRes,$choice, $final);
			 			if(!empty($final)){

			 				$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
			 				->where("username=?" , trim($final)));


			 				$depth = $chk_depth1->depth . $ref["username"] . ',';
			 				$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
			 				$result=$bin_user_ref_object->insert($query);
			 				return "success";
			 				exit();
			 			}else{
			 				$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
			 				->where("username=?" , trim($ref["parent_username"])));

			 				$depth = $chk_depth1->depth . $ref["username"] . ',';

			 				$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
			 				$result=$bin_user_ref_object->insert($query);

			 				return "success";
			 				exit();
			 			}
			 		}
			 		else{

			 			$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
			 			->where("username=?" , trim($finalRes)));


			 			$depth = $chk_depth1->depth . $ref["username"] . ',';
			 			$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
			 			$result=$bin_user_ref_object->insert($query);

			 			return "success";
			 			exit();
			 		}

			 	}
				}
				else
				{
					$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
					->where("username=?" , trim($ref["parent_username"])));


					$depth = $chk_depth1->depth . $ref["username"] . ',';
					$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
					$result=$bin_user_ref_object->insert($query);

				}
			}
				else if ($allRecords == 0 && $check_user['isActiveId']==1) {
				$comm_obj->getRight($ref["parent_username"],$ref["choice_leg"], $finalRes);
				if(!empty($finalRes)){
					$check_dw=$user->fetchRow($user->select()
					->where("binaryUser is NOT NULL")
					->where("username=?", trim($ref["finalRes"]))
					);
					if(!empty($check_dw) && (sizeof($check_dw)>0) && $check_dw->isActiveId==1){

						$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
						->where("username=?" , trim($finalRes)));

						$depth = $chk_depth1->depth . $ref["username"] . ',';
						$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
						$result=$bin_user_ref_object->insert($query);
						return "success";
						exit();
					}
					else
					{

						$chk_depth_leg=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
						->setIntegrityCheck(false)
						->from(array('b'=>"binary_user_refences"),array('count(username) as total'))
						->where("parent_username=?" , trim($finalRes)));


						if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
							if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
							$comm_obj->getRight($finalRes,$choice, $final);
							if(!empty($final)){

								$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
								->where("username=?" , trim($final)));


								$depth = $chk_depth1->depth . $ref["username"] . ',';
								$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
								$result=$bin_user_ref_object->insert($query);
								return "success";
								exit();
							}else{
								$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
								->where("username=?" , trim($ref["parent_username"])));

								$depth = $chk_depth1->depth . $ref["username"] . ',';

								$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
								$result=$bin_user_ref_object->insert($query);

								return "success";
								exit();
							}
						}else{

							$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
							->where("username=?" , trim($finalRes)));


							$depth = $chk_depth1->depth . $ref["username"] . ',';
							$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
							$result=$bin_user_ref_object->insert($query);

							return "success";
							exit();
						}


					}
				}
				else
				{
					$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
					->where("username=?" , trim($ref["parent_username"])));


					$depth = $chk_depth1->depth . $ref["username"] . ',';
					$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
					$result=$bin_user_ref_object->insert($query);

				}
			}
			else
			{
				if ($check_user['isActiveId'] != 1) {
					$comm_obj->getRight($ref["parent_username"],$ref["choice_leg"], $finalRes);
					if(!empty($finalRes)){

						$check_dw=$user->fetchRow($user->select()
						->where("binaryUser is NOT NULL")
						->where("username=?", trim($ref["finalRes"]))
						);
						if(!empty($check_dw) && (sizeof($check_dw)>0) && $check_dw->isActiveId==1){

							$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
							->where("username=?" , trim($finalRes)));

							$depth = $chk_depth1->depth . $ref["username"] . ',';
							$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
							$result=$bin_user_ref_object->insert($query);
							return "success";
							exit();
						}
						else
						{
							$chk_depth_leg=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
							->setIntegrityCheck(false)
							->from(array('b'=>"binary_user_refences"),array('count(username) as total'))
							->where("parent_username=?" , trim($finalRes)));

							if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
								if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
								$comm_obj->getRight($finalRes,$choice, $final);
								if(!empty($final)){

									$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
									->where("username=?" , trim($final)));


									$depth = $chk_depth1->depth . $ref["username"] . ',';
									$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
									$result=$bin_user_ref_object->insert($query);
									return "success";
									exit();
								}else{
									$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
									->where("username='" . trim($ref["parent_username"]) . "'"));

									$depth = $chk_depth1->depth . $ref["username"] . ',';

									$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
									$result=$bin_user_ref_object->insert($query);

									return "success";
									exit();
								}
							}
							else{

								$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
								->where("username=?" , trim($finalRes)));


								$depth = $chk_depth1->depth . $ref["username"] . ',';
								$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
								$result=$bin_user_ref_object->insert($query);

								return "success";
								exit();
							}
						}
					}
					else
					{
						$chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
						->where("username=?" , trim($ref["parent_username"])));


						$depth = $chk_depth1->depth . $ref["username"] . ',';
						$query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
						$result=$bin_user_ref_object->insert($query);

					}
				}
			}
		}
		return "success";
	}

	public function generateRand()
	{
		$six_digit_random_number = mt_rand(100000, 999999);
		return $six_digit_random_number;
	}
	public function GetAccessRightByUserId($module_id,$user_id)
	{
		$access_obj=new Gbc_Model_DbTable_Accessright();
		$row = $access_obj->fetchRow($access_obj->select()
		->where("module_id= ?",$module_id)
		->where("user_id= ?",$user_id));
		return $row;
	}

	public function checkAdminAuthentication(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!empty($authUserNamespace->user) &&  $authUserNamespace->user=='admin') || (!empty($authUserNamespace->subadmin)))
		{
			$loggedIn = true;
		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Login");
		}

	}
	public function generateToken()
	{
		//Generate a random string.
		$token = openssl_random_pseudo_bytes(16);

		//Convert the binary data into hexadecimal representation.
		$token = bin2hex($token);
			
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$authUserNamespace->token=$token;
		return;
	}
	
public function fetchRSSFeed($xml){
		$xmlDoc = new DOMDocument();
		$xmlDoc->load($xml);
		
	
	//get elements from "<channel>"
		$channel=$xmlDoc->getElementsByTagName('channel')->item(0);
		$channel_title = $channel->getElementsByTagName('title')->item(0)->childNodes->item(0)->nodeValue;
		
		if($channel->getElementsByTagName('link')->length > 0) {
			$channel_link = $channel->getElementsByTagName('link')->item(0)->childNodes->item(0)->nodeValue;
		}
		
		$channel_desc = $channel->getElementsByTagName('description')->item(0)->childNodes->item(0)->nodeValue;
	
	//output elements from "<channel>"
		$html = "<p ><a href='" . $channel_link . "'>" . $channel_title . "</a>";
		$html .="<br>";
		$html .= $channel_desc . "</p>";
		$html .= "<div id='content'>";
		$html .= "<ul>";
	//get and output "<item>" elements
		$x=$xmlDoc->getElementsByTagName('item');
		
		for ($i=0; $i<=2; $i++) {
			$item_title=$x->item($i)->getElementsByTagName('title')->item(0)->childNodes->item(0)->nodeValue;
			$item_link=$x->item($i)->getElementsByTagName('link')->item(0)->childNodes->item(0)->nodeValue;
			$item_desc=$x->item($i)->getElementsByTagName('description')->item(0)->childNodes->item(0)->nodeValue;
			$html .= "<p><li><a href='" . $item_link. "'>" . $item_title . "</a></li>";
			$html .= "<br>";
			$html .= $item_desc . "</p>";
		}
		
		$html .= "<ul>";
		$html .= "</div>";
		
		return $html;
	}



}
