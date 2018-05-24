<?php 
class CountryController extends Zend_Controller_Action{

	public function init(){
$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
	}
	
	public function indexAction()
	{
	
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$this->_helper->layout()->setLayout("dashbord");
		
		$countryObj = new Gbc_Model_DbTable_Countries();
		
		$countrydata= $countryObj->fetchAll($countryObj->select()
									->from(array('countries'))
									);
		
			
		$country=array('countrydata'=>$countrydata);
		
		$this->view->country=$country;	
		
	}
	public function stateAction()
	{
		
				
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
	
		$username=$authUserNamespace->user;
		
		
		$stateObj=new Gbc_Model_DbTable_State();
		
		$id=$_POST['countryid'];		
			
		$statedata=$stateObj->fetchAll($stateObj->select()
									->from(array('state1'))
									->where("country_id=?",$id));
		$arr=array();

			
		
		if(isset($statedata) && sizeof($statedata)>0)
		{
			for($j=0;$j<sizeof($statedata);$j++)
			{
		$arr[]=array('stateid'=>$statedata[$j]['state_id'],'statename'=>$statedata[$j]['state_name']);
			}
			
		$data1=array('success'=>'success','failure'=>'','data'=>$arr);
			echo  json_encode($data1);exit;
			
			
		
			
			
		}	
		
	}
	public function districtAction()
	{ 
	
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$username=$authUserNamespace->user;
			
		$id=$_POST['stateid'];
		
		
		$districtObj= new Gbc_Model_DbTable_District();
		
		$districtdata=$districtObj->fetchAll($districtObj->select()
									->from(array('district1'))
									->where("state_id = ?",$id));
									
									
		if(isset($districtdata) && sizeof($districtdata)>0)
		{
			
			
			for($i=0;$i<sizeof($districtdata);$i++)
			{
				
		$arr[]=array('districtid'=>$districtdata[$i]['district_id'],'districtname'=>$districtdata[$i]['district_name']);
			}
		$data2=array('success'=>'success','failure'=>'','data'=>$arr);
			echo  json_encode($data2);exit;
			
			
		}							
									
									
									
									
									
									
									
	}
		
				
		
		
}

	