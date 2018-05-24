<?php
class UpdatenavigationController extends Zend_Controller_Action{

	public function init()
	{

	}

	public function indexAction()
	{
		try{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');		
		
		$country_obj= new Gbc_Model_DbTable_Countries();
		$nav_link_obj= new Gbc_Model_DbTable_Navigationmaster();	
		$manage_link_obj = new Gbc_Model_DbTable_Managelinks();	
		
		$country_list=$country_obj->fetchAll($country_obj->select()
		->from(array('countries'))
//		->order(array('ccode ASC'))
		);
		 $country_list = $country_list->toArray();

		 
		for($j=0;$j<sizeof($country_list);$j++)
		{
			$ccode = $country_list[$j]['ccode'];
//			$ccode = '60';
							
			$nav_list=$nav_link_obj->fetchAll($nav_link_obj->select());
		
				if(!empty($nav_list) && sizeof($nav_list)>0)
				{
					for($i=0;$i<sizeof($nav_list);$i++)
					{
						if($nav_list[$i]['nav_link']!="Buy Ledger Wallet"){
							$row_data=array();
	
							$row_data=$manage_link_obj->fetchRow($manage_link_obj->select()
							->where("country_id= ?",$ccode)
							->where("nav_id= ?",$nav_list[$i]['id'])
							);
	
							if(!empty($row_data) && sizeof($row_data)>0)
							{
								
								$upd_arr=array();
								$upd_arr=array('country_id'=>$ccode,'nav_id'=>$nav_list[$i]['id'],'parent'=>$nav_list[$i]['parent'],'status'=>"1");
								$upd_data=$manage_link_obj->update($upd_arr,"id='".$row_data->id."'");
							}
							else
							{
								
								$ins_arr=array();
								$ins_arr=array('country_id'=>$ccode,'nav_id'=>$nav_list[$i]['id'],'parent'=>$nav_list[$i]['parent'],'status'=>"1");
								$upd_data=$manage_link_obj->insert($ins_arr);
							}
						}
					}
				}	
			
		}
		exit;
	
	}
	catch(Exception $e)
	{
		echo $e->getMessage();exit;
	}
	

	}
}