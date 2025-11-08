<?php
//
// Utkan Başurgan
//
//--------------------------------------------------------------------------------------------------------------------------------
sss
function softwares_functions_mysqls_inserts($mysql_database, $mysql_table, $mysql_column, $mysql_column_value)
{
    require(NEPARTH_Roots_Daemons.'/04_settings_daemons/variables_settings/secrets_variables.php');
    $conn = new mysqli(NEPARTH_Databases[$mysql_database]['ip_address'].':'.NEPARTH_Databases[$mysql_database]['port'], NEPARTH_Databases[$mysql_database]['username'], NEPARTH_Databases[$mysql_database]['password'], NEPARTH_Databases[$mysql_database]['database_name']);
    if ($conn->connect_error)
    {
        softwares_functions_logs_mains("Connection failed for database $mysql_database: " . $conn->connect_error);
        return false;
    }
    $sql = "INSERT IGNORE INTO `".$mysql_table."` (`".$mysql_column."`) VALUES ('".$mysql_column_value."')";
    if ($conn->query($sql) === TRUE)
    {
        $softwares_functions_mysqls_inserts = true;
    }
    else
    {
        $softwares_functions_mysqls_inserts = false;
        softwares_functions_logs_mains("Query failed for table $mysql_table: " . $conn->error);
    }
    $conn->close();
    return $softwares_functions_mysqls_inserts;
}

//--------------------------------------------------------------------------------------------------------------------------------

function softwares_functions_mysqls_inserts_multiples($mysql_database, $mysql_table, $data)
{
    require(NEPARTH_Roots_Daemons.'/04_settings_daemons/variables_settings/secrets_variables.php');
    $conn = new mysqli(
        NEPARTH_Databases[$mysql_database]['ip_address'].':'.NEPARTH_Databases[$mysql_database]['port'],
        NEPARTH_Databases[$mysql_database]['username'],
        NEPARTH_Databases[$mysql_database]['password'],
        NEPARTH_Databases[$mysql_database]['database_name']
    );
    if ($conn->connect_error)
    {
        softwares_functions_logs_mains("Connection failed for database $mysql_database: " . $conn->connect_error);
        return false;
    }
    $columns = array_keys($data);
    $values = array_map([$conn, 'real_escape_string'], array_values($data));
    $columns_sql = "`" . implode("`, `", $columns) . "`";
    $values_sql = "'" . implode("', '", $values) . "'";
    $sql = "INSERT IGNORE INTO `$mysql_table` ($columns_sql) VALUES ($values_sql)";
    if ($conn->query($sql) === TRUE)
    {
        $softwares_functions_mysqls_inserts = true;
    }
    else
    {
        $softwares_functions_mysqls_inserts = false;
        softwares_functions_logs_mains("Query failed for table $mysql_table: " . $conn->error);
    }
    $conn->close();
    return $softwares_functions_mysqls_inserts;
}

//--------------------------------------------------------------------------------------------------------------------------------
?>
