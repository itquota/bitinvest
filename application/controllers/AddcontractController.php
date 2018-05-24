<?php

class AddcontractController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");
	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Contracts";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$this->_helper->layout()->setLayout("admindashbord");//dashboard
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('2',$user_id);
		if(!empty($data1->add) && ($data1->add==1) || $authUserNamespace->user=='admin')
		{
				
		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}
		$antixss = new Gbc_Model_Custom_StringLimit();

		$editcontractObj  = new Gbc_Model_DbTable_Contracts();

			
		$result=$editcontractObj->fetchAll($editcontractObj->select()
		->setIntegrityCheck(false)
		->from(array('contracts'))
		->where("contract_type in ('SHA','hardware')")
		);

		$contracttype=array();
		$contracttype=$editcontractObj->fetchAll($editcontractObj->select()
		->setIntegrityCheck(false)
		->from(array('contracts'),array("DISTINCT(contract_type) as contracttype"))
		);


		$this->view->result=$result;

		$this->view->contracttype=$contracttype;
		/*echo "<pre>";
		 print_r($contracttype);exit;
		 */





	}
	public function dataeditAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$antixss = new Gbc_Model_Custom_StringLimit();
		$editcontractObj  = new Gbc_Model_DbTable_Contracts();
		foreach($_POST as $key => $value)
		{
			//if($key!='Desc' && $key!='contrctdesc' && $key!='contrctname'){
			if(isset($value) && $value != ""){
				$antixss->setEncoding($value, "UTF-8");
				if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

					$data=array('success'=>'','failure'=>'Invalid Input.');
					echo json_encode($data);exit;

				}

			}
			//}
		}
		try{
			/*$result=$editcontractObj->fetchRow($editcontractObj->select()
			 ->setIntegrityCheck(false)
			 ->from(array('contracts'),array("contract_id"))
			 ->order("contract_id DESC"));


			 $result1=sizeof($result);

			 if(isset($result1) && $result1>0)
			 {
				$id=$result->contract_id+1;
				}
				else
				{
				$id=1;
				}
				*/
			if(!empty($_POST['contrctname']) && $_POST['contrctname']!="" )
			{
			 $contractname=$_POST['contrctname'];

			}else{
				$msg = "Please enter Contract Name";
				$data=array('success'=>'','failure'=>$msg);
				echo json_encode($data);exit;
			}
				
			if(!empty($_POST['Contrctqty']) && $_POST['Contrctqty']!="" )
			{
				$contrctqty=$_POST['Contrctqty'];
			}else{
				$msg = "Please enter Contract Qty";
				$data=array('success'=>'','failure'=>$msg);
				echo json_encode($data);exit;
			}
				
			if(!empty($_POST['Desc']) && $_POST['Desc']!="")
			{
				$desc=$_POST['Desc'];
			}else{
				$msg = "Please enter Description";
				$data=array('success'=>'','failure'=>$msg);
				echo json_encode($data);exit;
			}
				
			if(!empty($_POST['contrctdesc']) && $_POST['contrctdesc']!="")
			{
				$contractdesc=$_POST['contrctdesc'];
			}
			else{
				$msg = "Please enter Contrct Description";
				$data=array('success'=>'','failure'=>$msg);
				echo json_encode($data);exit;
			}
				
			if(!empty($_POST['rte']) && $_POST['rte']!="")
			{
				$rate=$_POST['rte'];
			}
			else{
				$msg = "Please enter Total Rate";
				$data=array('success'=>'','failure'=>$msg);
				echo json_encode($data);exit;
			}
				
				
			if(!empty($_POST['dsi']) && $_POST['dsi']!="")
			{
				$dsi=$_POST['dsi'];
			}
			else{
				$msg = "Please enter Direct sales incentive";
				$data=array('success'=>'','failure'=>$msg);
				echo json_encode($data);exit;
			}
				
			if(!empty($_POST['cVal']) && $_POST['cVal']!="")
			{
				$cval=$_POST['cVal'];
			}
			else {
				$msg = "Please select Contract Type";
				$data=array('success'=>'','failure'=>$msg);
				echo json_encode($data);exit;
			}
				
				
			if($_POST['contractstatus']!="select" || $_POST['contractstatus']==1 || $_POST['contractstatus']==0)
			{
					
				$status=$_POST['contractstatus'];
			}
			else{
				$msg = "Please select status";
				$data=array('success'=>'','failure'=>$msg);
				echo json_encode($data);exit;
			}
				

				
			$tokn=$_POST['tkn'];
	 // if($authUserNamespace->token==$tokn) {
	  	$data=array("contract_name"=>$contractname,"contract_qty"=>$contrctqty,"description"=>$desc,"total_price"=>$rate,"contract_type"=>$cval,"contract_descr"=>$contractdesc,"direct_earning"=>$dsi,"status"=>$status,"contract_ts"=>new Zend_Db_Expr('NOW()'));

	  	$insetadddata=$editcontractObj->insert($data);

	  	if(!empty($insetadddata))
	  	{
	  		$arr=array('success'=>'Data inserted successfully','failure'=>'');
					echo  json_encode($arr);exit;
	  	}
	  	else {
	  		$arr=array('success'=>'','failure'=>'failure');
					echo  json_encode($arr);exit;
	  	}
		/*	}
			else {
				$data=array('success'=>'','failure'=>'Invalid Request Found');
				echo json_encode($data);exit;
			}*/
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
			
			



			
			
			
			
	}

}
