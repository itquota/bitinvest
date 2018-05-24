<?php
class CitylistmobapiController extends Zend_Controller_Action{

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
		$cityobj=new Gbc_Model_DbTable_Cities();
		
		if(isset($_GET) && isset($_GET['stateid'])&& !empty($_GET['stateid'])&& ($_GET['stateid'])!="")
		{
			$state_id=$_GET['stateid'];

			
		}
		else {
				
			$arr=array('success'=>'','failure'=>'State cannot be blank');
			echo json_encode($arr);
			exit;

		}
			
		$city_data=$cityobj->fetchAll($cityobj->select()
						 ->setIntegrityCheck(false)
						 ->from(array('cities'),array('name'))
						 ->where("state_id='$state_id'")
						 ->order('name ASC')
						 );
		
			$cityresult=sizeof($city_data);	
			$address=array();
			if(!empty($cityresult) && $cityresult>0)
			{
				foreach($city_data as $city_data)
				{
		$subarr=array('Cityname'=>$city_data->name);
		array_push($address,$subarr);
		
					
				}
				$data=array('city_list'=>$address);
				$arr=array('success'=>'success','failure'=>'','data'=>$data);
			  		echo  json_encode($arr);	
			} 
			else {
				$arr=array('success'=>'','failure'=>'No Records Found');
			  		echo  json_encode($arr);	
			}
						 
						
		
		
		
	}
}