<?php
class DailyqueryreportsController extends Zend_Controller_Action{

	public function init()
	{
		
	}
	public function indexAction()
	{
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$help_obj = new Gbc_Model_DbTable_Helpquery();
		
				echo ($help_obj->select()
		->setIntegrityCheck(false)
		->from(array('h'=>"help_query"),array("h.assigned_to AS Executive","(SELECT COUNT(*) FROM help_query q1 WHERE q1.status NOT IN ('1','2','3','4','5','6','7','8','9','10') AND DATE(q1.created_on) = DATE(NOW()) - INTERVAL 1 DAY 
					AND q1.assigned_to=h.assigned_to ) AS Today Closed","(SELECT COUNT(*) FROM help_query q2 WHERE q2.status IN ('Resolved') AND DATE(q2.created_on) = DATE(NOW()) - INTERVAL 1 DAY AND q2.assigned_to=h.assigned_to ) AS `Today Resolved`,(SELECT COUNT(*) FROM help_query h1 WHERE  h1.status IN ('0') AND h1.assigned_to=h.assigned_to AND DATE(created_on) = DATE(NOW()) - INTERVAL 1 DAY)  AS Today Open","(SELECT COUNT(*) FROM help_query h2 WHERE  h2.status IN ('5') AND h2.assigned_to=h.assigned_to AND DATE(created_on) = DATE(NOW()) - INTERVAL 1 DAY)  AS Today Pending","(SELECT COUNT(*) FROM help_query h3 WHERE  h3.status IN ('0') AND h3.assigned_to=h.assigned_to AND DATE(created_on) <= DATE(NOW()) - INTERVAL 1 DAY)  AS Total Open","(SELECT COUNT(*) FROM help_query h4 WHERE  h4.status IN ('5') AND h4.assigned_to=h.assigned_to AND DATE(created_on) <= DATE(NOW()) - INTERVAL 1 DAY)  AS Total Pending","DATE(h.created_on) AS Created FROM help_query h  
					"))
					->where("DATE(h.created_on) = DATE(NOW()) - INTERVAL 1 DAY "));exit;
		
		$query_count=$help_obj->fetchAll($help_obj->select()
		->setIntegrityCheck(false)
		->from(array('h'=>"help_query"),array("h.assigned_to AS Executive","(SELECT COUNT(*) FROM help_query q1 WHERE q1.status NOT IN ('1','2','3','4','5','6','7','8','9','10') AND DATE(q1.created_on) = DATE(NOW()) - INTERVAL 1 DAY 
					AND q1.assigned_to=h.assigned_to ) AS Today Closed","(SELECT COUNT(*) FROM help_query q2 WHERE q2.status IN ('Resolved') AND DATE(q2.created_on) = DATE(NOW()) - INTERVAL 1 DAY AND q2.assigned_to=h.assigned_to ) AS `Today Resolved`,(SELECT COUNT(*) FROM help_query h1 WHERE  h1.status IN ('0') AND h1.assigned_to=h.assigned_to AND DATE(created_on) = DATE(NOW()) - INTERVAL 1 DAY)  AS Today Open","(SELECT COUNT(*) FROM help_query h2 WHERE  h2.status IN ('5') AND h2.assigned_to=h.assigned_to AND DATE(created_on) = DATE(NOW()) - INTERVAL 1 DAY)  AS Today Pending","(SELECT COUNT(*) FROM help_query h3 WHERE  h3.status IN ('0') AND h3.assigned_to=h.assigned_to AND DATE(created_on) <= DATE(NOW()) - INTERVAL 1 DAY)  AS Total Open","(SELECT COUNT(*) FROM help_query h4 WHERE  h4.status IN ('5') AND h4.assigned_to=h.assigned_to AND DATE(created_on) <= DATE(NOW()) - INTERVAL 1 DAY)  AS Total Pending","DATE(h.created_on) AS Created FROM help_query h  
					"))
					->where("DATE(h.created_on) = DATE(NOW()) - INTERVAL 1 DAY "));

	}

}