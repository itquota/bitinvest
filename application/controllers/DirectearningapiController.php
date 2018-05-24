<?php

class DirectearningapiController extends Zend_Controller_Action{

	public function init(){

	}

	public function indexAction()
	{
		try
		{
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
		 $common_obj = new Gbc_Model_Custom_CommonFunc();
		 //$common_obj->cleanQueryParameter(($_REQUEST['username']));
		 $this->getResponse()->setHeader('Content-Type', 'application/json');
		 $this->_helper->layout->disableLayout();
		 $this->_helper->viewRenderer->setNoRender(true);

		 /*if(empty($_REQUEST['username']) || $_REQUEST['username']=='')
		  {
		 	$db->rollBack();
			 $arr=array('success'=>'','failure'=>'Please provide username');exit;
			 echo json_encode($arr);exit;
			 }
			 $username=$_REQUEST['username'];*/
		 //$username=$common_obj->cleanQueryParameter(($_REQUEST['username']));
		 $username = $this->_request->getParam("username");;
		 $type = $this->_request->getParam("type");

		 if($username != ''){

		 	$username=$username;

			}else{
				$arr=array('Success'=>' ','Failure'=>'Username cannot be blank');
				echo json_encode($arr);
				exit;
			}

			if($type == "gb2"){
				$directearningobj = new Gbc_Model_DbTable_Gb2Binaryuserincome();

				 $result=$directearningobj->fetchAll($directearningobj->select()
				 ->setIntegrityCheck(false)
				 ->from(array('gb2_binaryuserincome'),array('created_on','from_username','amount'))
				 // ->where("ben_username='$username'")
				 ->where("ben_username=?",$username)
				 ->order("created_on DESC")
				// ->limit(15)
				 );

			}else{
				 $directearningobj = new Gbc_Model_DbTable_Binaryuserincome();

				 $result=$directearningobj->fetchAll($directearningobj->select()
				 ->setIntegrityCheck(false)
				 ->from(array('binaryuserincome'),array('created_on','from_username','amount'))
				 // ->where("ben_username='$username'")
				 ->where("ben_username=?",$username)
				 ->order("created_on DESC")
				// ->limit(15)
				 );

			}
		//	print_r($result);
			$master=array();
			$result1=sizeOf($result);
			if(isset($result1) && $result1>0)
			{
				foreach($result as $result)
				{
					$sub_arr=array('created_on'=>$result->created_on,'from_username'=>$result->from_username,'amount'=>$result->amount);
					array_push($master,$sub_arr);
				}
					
			}
			$db->commit();
			$arr=array('success'=>'success','failure'=>'','data'=>$master);
			echo  json_encode($arr);exit;



		}
		catch(Exception $e)
		{
			$db->rollBack();
			$arr=array('success'=>'','failure'=>$e->getMessage);
			echo json_encode($arr);exit;
		}

			
			


	}
}
