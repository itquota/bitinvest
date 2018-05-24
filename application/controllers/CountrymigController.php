<?php
class CountrymigController extends Zend_Controller_Action{

	public function init(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
	}

	public function indexAction()
	{
		try{
		$country_obj = new Gbc_Model_DbTable_Country();
		$countries_obj = new Gbc_Model_DbTable_Countries();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		

		$countries = $countries_obj->fetchAll($countries_obj->select()
		);
		for($i=0;$i<sizeof($countries);$i++)
		{
			$countries_name=strtolower($countries[$i]['name']);
			$countries_name = str_replace("'", "\'", $countries_name);
			//echo $countries_name;exit;

			/*$country_data = $country_obj->fetchRow($country_obj->select()
			->where("LOWER(country)='".$countries_name."'")
			);*/
			$country_data = $country_obj->fetchRow($country_obj->select()
			->where("LOWER(country)=?",$countries_name)
			);
			if(!empty($country_data) && sizeof($country_data)>0)
			{

				$country_code=$country_data->ccode;

				$upd_arr=array('ccode'=>$country_code);
				$upd_qry=$countries_obj->update($upd_arr,$DB->quoteInto("LOWER(name)= ?",$countries_name));
//$DB->quoteInto
//$DB = Zend_Db_Table_Abstract::getDefaultAdapter();

			}
		}
		}
		catch(Exception $e)
		{
			echo ($country_obj->select()
			->where("LOWER(country)='".$countries_name."'")
			);
			echo $e->getMessage();exit;
		}
		echo "success";exit;

	}

}