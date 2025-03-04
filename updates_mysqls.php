<?php
//
// Copyright © 2024 by Neparth
//
//--------------------------------------------------------------------------------------------------------------------------------

function mysql_update($mysql_database, $mysql_table, $mysql_row_name, $mysql_row_value, $mysql_column, $mysql_column_value)
{
    require(WEBSITE_SERVER.'/settings_daemons/variables_settings/secrets_variables.php');
    
    $conn = new mysqli(NEPARTH_Databases[$mysql_database]['ip_address'].':'.NEPARTH_Databases[$mysql_database]['port'], NEPARTH_Databases[$mysql_database]['username'], NEPARTH_Databases[$mysql_database]['password'], NEPARTH_Databases[$mysql_database]['database_name']);
    
    $sql = "UPDATE `".$mysql_database."`.`".$mysql_table."` SET `".$mysql_column."` = '".$mysql_column_value."' WHERE `".$mysql_row_name."` = '".$mysql_row_value."'";
    
    if ($conn->query($sql) === TRUE) {
      $mysql_update =  true;
    } else {
      saveLog("Query failed for table $mysql_table: " . $conn->error, "customs_logs/mysqls_customs.log");
      $mysql_update =  false;
    }
    $conn->close();
    return $mysql_update;
}

//--------------------------------------------------------------------------------------------------------------------------------

function mysql_fullUpdate($mysql_database, $mysql_table, $mysql_row_name, $mysql_row_value, $data)
{
    foreach ($data as $key => $value)
	{
        mysql_update($mysql_database, $mysql_table, $mysql_row_name, $mysql_row_value, $key, $value);
    }
}

//--------------------------------------------------------------------------------------------------------------------------------
?>