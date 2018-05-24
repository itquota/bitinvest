<?php

class EditcontractController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");
	}
	public function indexAction()
	{
		try {
			$this->view->title="Gainbitcoin - Contracts";
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			$user_id=$authUserNamespace->user_id;
			$data1=$misc_obj->GetAccessRightByUserId('2',$user_id);
			if(!empty($data1->edit) && ($data1->edit==1) || $authUserNamespace->user=='admin')
			{
					
			}
			else
			{
				$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
				$this->_redirect("/Admindashboard");
			}
				
			$antixss = new Gbc_Model_Custom_StringLimit();
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



			$this->_helper->layout()->setLayout("admindashbord");//dashboard




			if($this->_request->isPost())
			{
				$token=$_POST['token'];
			//	if($authUserNamespace->token==$token){
						
					if($_POST["con_type"]=='SHA')
					{
						$contractDetails["contract_name"] = $_POST["contract_name"];
						$contractDetails["old_contract_name"] = $_POST["old_contract_name"];
						$contractDetails["contract_type"] = $_POST["con_type"];
						$contractDetails["contract_qty"] = $_POST["contract_qty"];
						$contractDetails["old_contract_qty"] = $_POST["old_contract_qty"];
						$contractDetails["total_price"] = $_POST["total_price"];
						$contractDetails["old_total_price"] = $_POST["old_total_price"];
						$contractDetails["desc"] = $_POST["desc"];
						$contractDetails["old_desc"] = $_POST["old_desc"];
					}
					elseif($_POST["con_type"]=='hardware'){
						$contractDetails["contract_name"] = $_POST["contract_name"];
						$contractDetails["old_contract_name"] = $_POST["old_contract_name"];
						$contractDetails["contract_type"] = $_POST["con_type"];
						$contractDetails["contract_qty"] = $_POST["contract_qty"];
						$contractDetails["old_contract_qty"] = $_POST["old_contract_qty"];
						$contractDetails["total_price"] = $_POST["total_price"];
						$contractDetails["old_total_price"] = $_POST["old_total_price"];
						$contractDetails["desc"] = $_POST["desc"];
						$contractDetails["old_desc"] = $_POST["old_desc"];
					}
					//print_r($contractDetails);exit;
					$msg=$Gbc_Model_Custom_func_obj->EditContractHardware($contractDetails);
					$this->view->msg=$msg;
						
			/*	}else{
					$data=array('success'=>'','failure'=>'Invalid Request Found.');
					echo json_encode($data);exit;
				}
			*/		

			}

			$result=array();

			$id=$_POST['contract_id'];
				

			$editcontractObj  = new Gbc_Model_DbTable_Contracts();

			$result=$editcontractObj->fetchAll($editcontractObj->select()
			->setIntegrityCheck(false)
			->from(array('contracts'))
			//->where("contract_type= ?",SHA)
			->where("contract_id= ?",$id)
			);

			$contracttype=array();
			$contracttype=$editcontractObj->fetchAll($editcontractObj->select()
			->setIntegrityCheck(false)
			->from(array('contracts'),array("DISTINCT(contract_type) as contracttype"))
			);
				

				
			$db->commit();
			$this->view->result=$result;
				
				
				
			$this->view->contracttype=$contracttype;
				
				
		}
		catch(Exception $e)
		{
			$db->rollBack();
			$arr=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($arr);exit;
		}
	}

	public function contracteditAction()
	{

		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$editcontractObj  = new Gbc_Model_DbTable_Contracts();
			$antixss = new Gbc_Model_Custom_StringLimit();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			
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
			if(!empty($_POST['contrctname']) && $_POST['contrctname']!="")
			{
				$contractname=$_POST['contrctname'];
			}
			else{
				$msg = "Please enter Contract Name";
				$data=array('success'=>'','failure'=>$msg);
				echo json_encode($data);exit;
			}
			if(!empty($_POST['Contrctqty']) && $_POST['Contrctqty']!="")
			{
				$contrctqty=$_POST['Contrctqty'];
			}
			else{
				$msg = "Please enter Contract Qty";
				$data=array('success'=>'','failure'=>$msg);
				echo json_encode($data);exit;
			}
			if(!empty($_POST['Desc']) && $_POST['Desc']!="")
			{
				$desc=$_POST['Desc'];
			}
			else {
				$msg = "Please enter Description";
				$data=array('success'=>'','failure'=>$msg);
				echo json_encode($data);exit;
			}
				
			if(!empty($_POST['contrctdesc']) && $_POST['contrctdesc']!="")
			{
			 $contrctdes=$_POST['contrctdesc'];

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
				$msg = "Please enter Direct Sales Incentive";
				$data=array('success'=>'','failure'=>$msg);
				echo json_encode($data);exit;
			}
				
				
				
				
			$cval=$_POST['cVal'];
				
			$status=$_POST['status'];
				
		 $c_id=$_POST['cid'];
		 $tokn=$_POST['tkn'];

		 //$status=$_POST['status'];
	//	 if($authUserNamespace->token==$tokn) {
		 	 
		 	$upcontrct=array("contract_name"=>$contractname,"contract_qty"=>$contrctqty,"description"=>$desc,"total_price"=>$rate,"contract_type"=>$cval,"contract_descr"=>$contrctdes,"direct_earning"=>$dsi,"status"=>$status,"contract_ts"=>new Zend_Db_Expr('NOW()'));
		 	$upcontrctdata=$editcontractObj->update($upcontrct,$DB->quoteInto("contract_id=?",$c_id));
		 	 
	   if(!empty($upcontrctdata))
	   {
	   	$arr=array('success'=>'Data updated successfully','failure'=>'');
	   	echo json_encode($arr);exit;
	   }
	   else{
	   	$arr=array('success'=>'','failure'=>'failure');
	   	echo json_encode($arr);exit;
	   }

	   	
/*		 }
		 else {
		 	$data=array('success'=>'','failure'=>'Invalid Request Found');
		 	echo json_encode($data);exit;
		 }
*/		  
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
		 
	}


}
