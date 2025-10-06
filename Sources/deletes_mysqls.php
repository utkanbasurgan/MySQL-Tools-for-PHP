<?php
//
// Utkan Başurgan
//
//--------------------------------------------------------------------------------------------------------------------------------

function softwares_functions_mysqls_deletes($mysql_database, $mysql_table, $mysql_row_name, $mysql_row_value)
{
	require(NEPARTH_Roots_Daemons.'/04_settings_daemons/variables_settings/secrets_variables.php');
	
	$conn = new mysqli(NEPARTH_Databases[$mysql_database]['ip_address'].':'.NEPARTH_Databases[$mysql_database]['port'], NEPARTH_Databases[$mysql_database]['username'], NEPARTH_Databases[$mysql_database]['password'], NEPARTH_Databases[$mysql_database]['database_name']);
	$sql = "DELETE FROM `".$mysql_table."` WHERE `".$mysql_row_name."` = '".$mysql_row_value."'";
	
	if ($conn->query($sql) === TRUE) 
	{
	  $mysql_delete = true;
	} 
	else 
	{
	  $mysql_delete = $sql.$conn->error;
	}
	return $mysql_delete;
}
	
//--------------------------------------------------------------------------------------------------------------------------------
?>