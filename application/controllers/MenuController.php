<?php
class MenuController extends Zend_Controller_Action{

	public function init()
	{

	}

	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Manage Navigation";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!empty($authUserNamespace->user) &&  $authUserNamespace->user=='admin')
			{
				//$loggedIn==true;
			}
			else 
			{
				$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
				$this->_redirect("/Login");
			}
		$manage_link_obj = new Gbc_Model_DbTable_Managelinks();
		$country_obj= new Gbc_Model_DbTable_Countries();
		$nav_obj = new Gbc_Model_DbTable_Navigationmaster();
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
		$country=$_POST['country'];
		$this->_helper->layout()->setLayout("admindashbord");
		$country_name=$country_obj->fetchRow($country_obj->select()	
		->from(array('countries'),array('countries.country'))
		->where("ccode=?",$country));
		$this->view->country_name=$country_name;
		
		
		$link_data=$manage_link_obj->fetchAll($manage_link_obj->select()
	//	->where("country_id='".$country."' and status='1'"));
		->where("country_id=?",$country)
		->where("status=?",'1'));

		if(!empty($link_data) && sizeof($link_data)>0)
		{

		}
		else
		{
			$link_data=array();
		}
		$this->view->selectlink=$link_data;


		$nav_data=$nav_obj->fetchAll($nav_obj->select()
		//->where("status='1'")
		->where("status=?",'1')
		->order("position asc")
		);
			
		
		//print_r(sizeof($nav_data));exit;
		$this->view->masterlink=$nav_data;

		/*echo "<pre>";
		 print_r($nav_data);exit;*/

	}

	public function changestatusAction()
	{
		try {

			$nav_id=$_POST['id'];

			$nav_link=$_POST['navlink'];

			$parent=$_POST['parent'];

			$ccode=$_POST['countryid'];

			$action=$_POST['action'];
			$token=$_POST['token'];
	//		if($authUserNamespace->token==$token){

			$master_link_obj= new Gbc_Model_DbTable_Navigationmaster();
			$manage_link_obj = new Gbc_Model_DbTable_Managelinks();
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
			
			if($action=='checked')
			{
				$status=1;
			}
			else
			{
				$status=0;
			}

			$link_data=$manage_link_obj->fetchRow($manage_link_obj->select()
			//->where("country_id='".$ccode."' and nav_id='".$nav_id."'"));
			->where("country_id=?",$ccode)
			->where("nav_id=?",$nav_id));
			
			
			if($action=='checked')
			{

				if(!empty($link_data) && sizeof($link_data))
				{

					$upd_arr=array('status'=>$status,'parent'=>$parent);

					$upd_data=$manage_link_obj->update($upd_arr,"country_id='".$ccode."' and nav_id='".$nav_id."'");
				}
				else
				{
						
					$insert_arr=array('country_id'=>$ccode,'nav_id'=>$nav_id,'status'=>$status,'parent'=>$parent);
					$insert_data=$manage_link_obj->insert($insert_arr);
						
				}
					
					
				$parent_data=$manage_link_obj->fetchRow($manage_link_obj->select()
			//->where("country_id='".$ccode."' and nav_id='".$parent."'"));
				->where("country_id=?",$ccode)
				->where("nav_id=?",$parent));

			
				if(!empty($parent_data) && sizeof($parent_data))
				{

					$upd_arr=array('status'=>$status,'parent'=>$parent);

					$upd_data=$manage_link_obj->update($upd_arr,"country_id='".$ccode."' and nav_id='".$parent."'");
				}
				else
				{
						
					$insert_arr=array('country_id'=>$ccode,'nav_id'=>$parent,'status'=>$status,'parent'=>$parent);
					$insert_data=$manage_link_obj->insert($insert_arr);
						
				}
					
			}
			else
			{
				if(!empty($link_data) && sizeof($link_data))
				{

					$upd_arr=array('status'=>$status,'parent'=>$parent);

					$upd_data=$manage_link_obj->update($upd_arr,"country_id='".$ccode."' and nav_id='".$nav_id."'");
				}
				else
				{
						
					$insert_arr=array('country_id'=>$ccode,'nav_id'=>$nav_id,'status'=>$status,'parent'=>$parent);
					$insert_data=$manage_link_obj->insert($insert_arr);
						
				}
					
					
				$ckh_parent_data=$manage_link_obj->fetchRow($manage_link_obj->select()
				//->where("country_id='".$ccode."' and parent='".$parent."' and nav_id!='".$parent."' and status=1"));
				->where("country_id=?",$ccode)
				->where("parent=?",$parent)
				->where("status=?",'1'));
				
				
				if(empty($ckh_parent_data) && sizeof($ckh_parent_data)<=0)
				{
			
					$parent_data=$manage_link_obj->fetchRow($manage_link_obj->select()
					->where("country_id= ?",$ccode)
					->where("nav_id= ?",$parent)
					->where("status= ?",1));
				
					if(!empty($parent_data) && sizeof($parent_data))
					{
	
						$upd_arr=array('status'=>$status,'parent'=>$parent);
	
						$upd_data=$manage_link_obj->update($upd_arr,"country_id='".$ccode."' and nav_id='".$parent."'");
					}
		
				}
			}
				

			/*$link_data=$manage_link_obj->fetchRow($manage_link_obj->select()
			 	
			->where("country_id='".$ccode."' and nav_id='".$nav_id."'"));*/



			/*	$masterdata=$master_link_obj->fetchRow($master_link_obj->select('parent','id')
			 ->where("parent='".$parent."' and nav_controller=''"));

			 if(!empty($masterdata) && sizeof($masterdata)>0)
			 {
				$nav_id1=$masterdata->id;

				$parent=$masterdata->parent;


				$masterdata1=$manage_link_obj->fetchRow($manage_link_obj->select()
				->from(array('manage_links'),array('count(id) as count'))
				->where("country_id='".$ccode."' and parent='".$parent."' and status='1' and nav_id!='".$nav_id1."'"));
					
					
				$statusscount=$masterdata1->count;
					

				if($statusscount >= 1)
				{

				$status=1;
				}
				else
				{

				$status=0;
				}

					
				$link_data=$manage_link_obj->fetchRow($manage_link_obj->select()
				->where("country_id='".$ccode."' and nav_id='".$nav_id1."'"));


				if(!empty($link_data) && sizeof($link_data))
				{

				$upd_arr=array('status'=>$status,'parent'=>$parent);

				$upd_data=$manage_link_obj->update($upd_arr,"country_id='".$ccode."' and nav_id='".$nav_id1."'");
				}
				else
				{

				$insert_arr=array('country_id'=>$ccode,'nav_id'=>$nav_id1,'status'=>$status,'parent'=>$parent);
				$insert_data=$manage_link_obj->insert($insert_arr);

				}
					

					
					
				}*/








			echo "Updated successfully";exit;
	/*
		}
		else{
			$data=array('success'=>'','failure'=>'Invalid Request Found.');
			 echo json_encode($data);exit;
				}
	*/	
	}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}

	}
	public function allchangestatusAction()
	{
		try {
			$nav_link_obj= new Gbc_Model_DbTable_Navigationmaster();
			$manage_link_obj = new Gbc_Model_DbTable_Managelinks();
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
			$token=$_POST['token'];
		//	if($authUserNamespace->token==$token){
			$ccode=$_POST['ccode'];

			$action=$_POST['action'];

			if($action=='checked')
			{
				$status=1;
					
			}
			else
			{
				$status=0;
			}

			$nav_list=$nav_link_obj->fetchAll($nav_link_obj->select()
			);

			if(!empty($nav_list) && sizeof($nav_list)>0)
			{
				for($i=0;$i<sizeof($nav_list);$i++)
				{
					$row_data=array();
						
					$row_data=$manage_link_obj->fetchRow($manage_link_obj->select()
					->where("country_id= ?",$ccode)
					->where("nav_id= ?",$nav_list[$i]['id'])
					);
						
					if(!empty($row_data) && sizeof($row_data)>0)
					{
						$upd_arr=array();
						$upd_arr=array('country_id'=>$ccode,'nav_id'=>$nav_list[$i]['id'],'parent'=>$nav_list[$i]['parent'],'status'=>$status);
						$upd_data=$manage_link_obj->update($upd_arr,"id='".$row_data->id."'");
					}
					else
					{
						$ins_arr=array();
						$ins_arr=array('country_id'=>$ccode,'nav_id'=>$nav_list[$i]['id'],'parent'=>$nav_list[$i]['parent'],'status'=>$status);
						$upd_data=$manage_link_obj->insert($ins_arr);
					}
				}
			}


				

			echo "updated successfully";exit;
	/*	}
	else{
			$data=array('success'=>'','failure'=>'Invalid Request Found.');
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
