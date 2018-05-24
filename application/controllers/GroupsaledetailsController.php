<?php
class GroupsaledetailsController extends Zend_Controller_Action{

    public function init(){


    }

    public function indexAction(){
		//$this->_helper->layout()->setLayout("dashbord");
      	
		$group_sales_obj=new Gbc_Model_DbTable_Groupsaledetails();
		$rows = $group_sales_obj->fetchAll($group_sales_obj->select()
                )->toArray();
		
		foreach ($rows as $row){
			$date = $row['date'];
			$user = $row['username'];
			$details[$date][$user] = $row['sale'];
		}
	//	print_r($details);
		
		echo '<style>
		table{
			border:1px solid #000;
			width:100%;
			table-layout:fixed;
			border-spacing:0;
		}
		tr td{
			border:1px solid #000;
			padding:10px;
		}
		
		th{
			border:1px solid #000;
			padding:10px;
		}
		</style>';
		
		
		echo '<h3 style ="text-align:center;">Daily Sale Details</h3><table>';
		echo '<tr>
		<th></th>
		<th>4</th>
		<th>5</th>
		<th>6</th>
		<th>7</th>
		</tr>';
		
		foreach($details as $date => $detail){
			echo '<tr>';
			echo '<td style = "text-align:left;">'.$date.'</td>';
			echo '<td style = "text-align:center;" >'.$detail['sevenstar04'].'</td>';
			echo '<td style = "text-align:center;">'.$detail['sevenstar05'].'</td>';
			echo '<td style = "text-align:center;">'.$detail['sevenstar06'].'</td>';
			echo '<td style = "text-align:center;">'.$detail['sevenstar07'].'</td>';
			
			
			echo '</tr>';
		}
		
		echo '</table>';
		
		$group_payouts_obj=new Gbc_Model_DbTable_Grouppayoutdetails();
		$rows = $group_payouts_obj->fetchAll($group_payouts_obj->select()
                )->toArray();
		
		foreach ($rows as $row){
			$date = $row['date'];
			$user = $row['username'];
			$details[$date][$user] = $row['payout'];
		}
		//print_r($details);
		
		echo "<br><br>";
		
		
		echo '<h3 style ="text-align:center;">Daily Payout Details</h3><table>';
		echo '<tr>
		<th></th>
		<th>4</th>
		<th>5</th>
		<th>6</th>
		<th>7</th>
		</tr>';
		
		foreach($details as $date => $detail){
			echo '<tr>';
			echo '<td style = "text-align:left;">'.$date.'</td>';
			echo '<td style = "text-align:center;" >'.$detail['sevenstar04'].'</td>';
			echo '<td style = "text-align:center;">'.$detail['sevenstar05'].'</td>';
			echo '<td style = "text-align:center;">'.$detail['sevenstar06'].'</td>';
			echo '<td style = "text-align:center;">'.$detail['sevenstar07'].'</td>';
			
			
			echo '</tr>';
		}
		
		echo '</table>';
		
		
		exit;
    }
}
