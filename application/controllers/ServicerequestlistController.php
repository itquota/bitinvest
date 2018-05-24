<?php
class ServicerequestlistController extends Zend_Controller_Action{

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
			$this->_helper->layout()->setLayout("dashbord");
			$username=$authUserNamespace->user;
			$this->view->title="Gainbitcoin - Ticket";
			$help_obj = new Gbc_Model_DbTable_Helpquery();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			//$common_obj->cleanQueryParameter(($_POST['username']));
				
			$startdate=$common_obj->cleanQueryParameter(($_POST["startdate"]));
			$enddate=$common_obj->cleanQueryParameter(($_POST["enddate"]));
				
			if($this->_request->isPost() && ($startdate!='' || $enddate!=''))
			{

				$startdate = date('Y-m-d',strtotime($startdate));
				$enddate = date('Y-m-d',strtotime($enddate))." 23:59:59";

				$result=$help_obj->fetchAll($help_obj->select()
				->setIntegrityCheck(false)
				->from(array('help_query'))
				//->where("username='$username' AND created_on between '".$startdate."' AND '".$enddate."'"));
				->where("username=?",$username)
				->where('created_on >= ?', $startdate)
				->where('created_on <=   ?', $enddate));


				$datearr=array('startdate'=>$startdate,'enddate'=>$enddate);

			}
			else
			{

				$result=$help_obj->fetchAll($help_obj->select()
				->setIntegrityCheck(false)
				->from(array('help_query'))
				//->where("username='$username'"));
				->where("username=?",$username)
				->where('created_on <= ?', '2017-07-17 15:00:00')
											
				->order('created_on DESC'));

				$datearr=array('startdate'=>'','enddate'=>'');
			}
			//echo "<pre>";
	 	//print_r($result);exit;
	 	if(empty($result) || sizeof($result)<=0)
	 	{
	 		$result=array();
	 	}
			$this->view->result=$result;
			$this->view->datearr=$datearr;

		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}

	public function requsetAction()
	{
			
		try{
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			//$common_obj->cleanQueryParameter(($_POST['username']));
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$sub_admin_obj = new Gbc_Model_DbTable_Subadminuser();
			$username=$authUserNamespace->user;

			//$id=$_POST['request'];
			$id=$common_obj->cleanQueryParameter(($_POST['request']));
				
				

			$help_obj = new Gbc_Model_DbTable_Helpquery();

			$help_data = $help_obj->fetchAll($help_obj->select()
			//->where("username='".($username)."' AND ticket_id='".$id."' "));
			->where("username=?",$username)
			->where("ticket_id=?",$id));
				
			$helpcmt=array();
			$helpquerycommentobj = new Gbc_Model_DbTable_Helpquerycomment();
				
			$helpcommentdata = $helpquerycommentobj->fetchAll($helpquerycommentobj->select()
			//->where("ticket_id='".$ticket."' "));
			->where("ticket_id=?",$id));

			if(!empty($helpcommentdata)&& sizeof($helpcommentdata)>0)
			{
				for($j=0;$j<sizeof($helpcommentdata);$j++)
				{
					$email = $helpcommentdata[$j]['comments_by'];
					$subadminuser =$sub_admin_obj->fetchRow($sub_admin_obj->select()
								->from(array('a' =>'sub_admin_users'),array('first_name'))
								->where("email = ?",$email)
								);
					if(!empty($subadminuser) && sizeof($subadminuser)>0)
					{
						$helpcommentdata[$j]['comments_by']=$subadminuser->first_name;
					}
					$subarr=array('Date'=>$helpcommentdata[$j]['comment_date'],'Comments'=>$helpcommentdata[$j]['comments'],'CommentsBy'=>$helpcommentdata[$j]['comments_by']);
					array_push($helpcmt,$subarr);
				}
					
					

			}
				
				
			$arr=array();

			//$result=sizeof($invoices_obj);
			if(isset($help_data) && sizeof($help_data)>0)
			{
			 //$datearr[] = date("jS M, Y", strtotime($day->format('d-m-Y')));



				for($j=0;$j<sizeof($help_data);$j++)
				{
					$newstatus=$help_data[$j]['status'];
					if($newstatus=='1')
					{
						$req='Open';
					}
					else if($newstatus=='2')
					{
						$req='Assigned';
					}
					else if($newstatus=='3')
					{
						$req='Resolved';
					}else if($newstatus=='4')
					{
						$req='Reopen';
					} else
					{
						$req='Closed';
					}

					$subarr=array('created_on'=>$help_data[$j]['created_on'],'ticket_id'=>$help_data[$j]['ticket_id'],'req_type'=>$help_data[$j]['req_type'],'req_category'=>$help_data[$j]['req_category'],'query'=>$help_data[$j]['query'],'status'=>$req);
					array_push($arr,$subarr);
				}
				//echo "<pre>";
				//print_r($arr);exit;
				$data1=array('success'=>'success','failure'=>'','data'=>$arr,'helpdata'=>$helpcmt);
				echo  json_encode($data1);exit;

			}


		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
			
	}
	public function changestatusAction()
	{
			
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

		$username=$authUserNamespace->user;
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$helpquerycomments=new Gbc_Model_DbTable_Helpquerycomment();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		try {
			$token=$common_obj->cleanQueryParameter(($_POST['token']));
	//		if($authUserNamespace->token==$token){
					
				$ticket=$common_obj->cleanQueryParameter(($_POST['ticket']));

				$status=$common_obj->cleanQueryParameter(($_POST['status']));

				$comment=$common_obj->cleanQueryParameter(($_POST['comment']));


				if(!empty($comment) && $comment!='')
				{
					$help_obj = new Gbc_Model_DbTable_Helpquery();
					$help_data = $help_obj->fetchRow($help_obj->select()
					//->where("ticket_id='".$ticket."' "));
					->where("ticket_id=?",$ticket));

						
				}
				if($status=='Reopen')
				{
					$status='4';
				}
				if($status=='Close')
				{
					$status='0';
				}
				if(!empty($help_data) && sizeof($help_data)>0)
				{
					//$old_comment=$help_data->query;
					//$comment=$old_comment." <br/> "."<b>".$username."</b>". ":"." ".$comment."<br/>";
					if(!empty($comment) && $comment!='')
					{
						$upd_arr=array('status'=>$status);
					}
					else
					{
						$upd_arr=array('status'=>$status);
					}
					//$upd_qry=$help_obj->update($upd_arr,"ticket_id='".$ticket."'");
					$upd_qry=$help_obj->update($upd_arr,$DB->quoteInto("ticket_id=?",$ticket));
					
						
					$insert_arr=array('ticket_id'=>$ticket,'comments'=>$comment,'comments_by'=>$username,'comment_date'=>new Zend_Db_Expr('NOW()'),'created_date'=>new Zend_Db_Expr('NOW()'));
					$insertcomment=$helpquerycomments->insert($insert_arr);

					echo "success";exit;
						
				}
/*			}else{
				//$data=array('success'=>'','failure'=>'Invalid Request Found.');
				//echo json_encode($data);exit;
				echo "failed";exit;
			} */

		}
		catch(Exception $e)
		{
			echo "failed";exit;
		}

	}

}
