<?php
class StatelistmobapiController extends Zend_Controller_Action{

	public function init()
	{

	}
	
	public function indexAction()
	{
		
		 $this->getResponse()->setHeader('Content-Type', 'application/json');
	 	 $this->_helper->layout->disableLayout();
	 	 $this->_helper->viewRenderer->setNoRender(true);
	 	 
	 	$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		if(isset($_GET) && isset($_GET['country_id'])&& !empty($_GET['country_id'])&& ($_GET['country_id'])!="")
		{
			$countryid=$_GET['country_id'];
			
		}
		else {
				
			$arr=array('success'=>'','failure'=>'CountryId cannot be blank');
			echo json_encode($arr);
			exit;

		}
		
		$stateobj=new Gbc_Model_DbTable_States();
			
		
			
		  $state_data=$stateobj->fetchAll($stateobj->select()
			->setIntegrityCheck(false)
			->from(array('states'),array("name as statename",'id'))
			->where("country_id=?" ,$countryid)
			->order('name ASC')
			);
			
			$stateresult=sizeof($state_data);
			$address=array();
			if(!empty($stateresult) && $stateresult>0)
			{
			
				foreach($state_data as $state_data)
				{
			$subarr=array('State'=>$state_data->statename,'Id'=>$state_data->id);
			array_push($address,$subarr);
				}
				
		       $data=array('State_list'=>$address);
				$arr=array('success'=>'success','failure'=>'','data'=>$data);
					echo json_encode($arr);
					exit;
			}
			else {
				$arr=array('success'=>'success','failure'=>'No Records Found');
					echo json_encode($arr);
					exit;
			}
			
			
	
		
		
	}
	
}