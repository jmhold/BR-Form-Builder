<?php
sleep(2);
include("../config/config.php");
$tb_=$prefix_table."reload";
$datenow=date('Y-m-d H:i:s');
$column_=array(1,2,3,4,5,6);
$values_=
array(
	  $_REQUEST['titlename'],
	  $_REQUEST['name'],
	  $_REQUEST['lastname'],
	  $_REQUEST['email'],
	  $_REQUEST['gender'],
	  $datenow
	  ); 
$result=q(add_db($column_,$values_,$tb_));
			if($result){ 			 
				$check="1";  // complete return 1
			}else{
				$check="0"; // uncomplete return 0
			}
$return_arr["check"] = $check;
echo json_encode($return_arr);
?>