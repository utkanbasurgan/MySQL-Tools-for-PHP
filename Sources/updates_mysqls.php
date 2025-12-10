<?php
//
// Utkan Başurgan
//
//--------------------------------------------------------------------------------------------------------------------------------
ss
function softwares_functions_mysqls_updates($mysql_database, $mysql_table, $mysql_row_name, $mysql_row_value, $mysql_column, $mysql_column_value)
{
    require(NEPARTH_Roots_Daemons.'/04_settings_daemons/variables_settings/secrets_variables.php');
    
    $conn = new mysqli(NEPARTH_Databases[$mysql_database]['ip_address'].':'.NEPARTH_Databases[$mysql_database]['port'], NEPARTH_Databases[$mysql_database]['username'], NEPARTH_Databases[$mysql_database]['password'], NEPARTH_Databases[$mysql_database]['database_name']);
    
    $sql = "UPDATE `".$mysql_database."`.`".$mysql_table."` SET `".$mysql_column."` = '".$mysql_column_value."' WHERE `".$mysql_row_name."` = '".$mysql_row_value."'";
    
    if ($conn->query($sql) === TRUE) 
    {
      $softwares_functions_mysqls_updates =  true;
    } 
    else 
    {
    	softwares_functions_logs_mains("Query failed for table $mysql_table: " . $conn->error);
    	$softwares_functions_mysqls_updates =  false;
    }
    $conn->close();
    return $softwares_functions_mysqls_updates;
}

//--------------------------------------------------------------------------------------------------------------------------------

function softwares_functions_mysqls_updates_fulls($mysql_database, $mysql_table, $mysql_row_name, $mysql_row_value, $data)
{
    foreach ($data as $key => $value)
	{
    	softwares_functions_mysqls_updates($mysql_database, $mysql_table, $mysql_row_name, $mysql_row_value, $key, $value);
    }
}

//--------------------------------------------------------------------------------------------------------------------------------
?>