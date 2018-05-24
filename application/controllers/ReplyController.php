<?php
class ReplyController extends Zend_Controller_Action
{
	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");
	}

	public function indexAction()
	{
		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$db = Zend_Db_Table::getDefaultAdapter();
			
			$this->_helper->layout()->setLayout("admindashbord");
			$help_query_obj=new Gbc_Model_DbTable_Helpquery();
			$queryreply_obj=new	Gbc_Model_DbTable_Queryreply();
			$Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();

			$id=$_REQUEST['id'];
			$email=$_REQUEST['email'];
			$row = $help_query_obj->fetchRow($help_query_obj->select()
								 ->where("id= ?",$id));
								 
								 
			$this->view->result=$row;
			
			if($this->_request->isPost()){
				 $token=$_POST['token'];
		 	  //  if($authUserNamespace->token==$token){
					
				$last_row = $queryreply_obj->fetchRow($queryreply_obj->select()
											->order("id desc")
											->limit(1));
			
				if(isset($last_row) && sizeof($last_row)>0)
				{
					$last_id=($last_row->id)+1;
				}
				else
				{
					$last_id=1;
				}
					
				$arr=array('id'=>$last_id,'email'=>$_POST["email"],'subject'=>$_POST["subject"],'message'=>$_POST["message"],'query_id'=>$_POST['id']);
				$ins=$queryreply_obj->insert($arr);
				
		 	 /*   	}else{
		 			$data=array('success'=>'','failure'=>'Invalid Request Found.');
				     echo json_encode($data);exit;
		 		} */
			}
				
			if(!empty($ins))
			{
				$email_msg = "<div style='padding: 10px; margin: 10px;'><div style='padding: 0px;'>"
				. "<img src='".BASEPATH."/res/images/GainBTCLogo_large.png' style='width: 30%; min-width:300px' />"
				. "</div>"
				. "<h2>Dear ".$row->name.",</h2>"
				. "<p>".$_POST["message"]."</p>"
				. "</div>";

				//$Gbc_Model_Custom_func_obj->sendMail($_POST["email"],"admin@gainbitco.in", $_POST["subject"], $email_msg);

							$to = $_POST["email"]; 
							$from = 'admin@gainbitco.in';
							$replyTo = 'thegainbitcoinhelp@gmail.com';
							$subject = $_POST["subject"];
							$message = $email_msg;
							$htmlMessage = $email_msg;
							$sendMail = $Gbc_Model_Custom_func_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
							
							
				$upd_arr=array('reply_date'=>new Zend_Db_Expr('NOW()'),'reply_status'=>'1');
				$upd=$help_query_obj->update($upd_arr,$db->quoteInto("id=?",$_POST['id']));
				$this->view->msg="Reply Successful";
				$this->_redirect("/Queries");
			}
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}

	}
}

