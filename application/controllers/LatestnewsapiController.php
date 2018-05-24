<?php
class LatestnewsapiController extends Zend_Controller_Action
{
	public function init(){

	}
	public function indexAction()
	{
		$this->getResponse()->setHeader('Content-Type', 'application/json');
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		$latestnewsobj = new Gbc_Model_DbTable_Latestnews();
		$result=$latestnewsobj->fetchAll($latestnewsobj->select()
		->setIntegrityCheck(false)
		->from(array('n'=>'news'),array('n.id', 'n.headline','n.news_details','n.created_on'))
		->order("id DESC"));
			
		$address=array();
		$result1=sizeOf($result);



		if(isset($result1) && $result1>0)
		{
				
			for ($i=0;$i<sizeOf($result);$i++)
			{
				$address[]=array('id' => $result[$i]['id'],'Headline'=>$result[$i]['headline'],'Newsdetails'=>$result[$i]['news_details'],'Created_on'=>$result[$i]['created_on']);
			}
			$arr=array('success'=>'','failure'=>'','data'=>$address);
			echo  json_encode($arr);
		}
		else
		{
			$arr=array('success'=>'','failure'=>'');
			echo json_encode($arr);exit;
		}
			
	}
}