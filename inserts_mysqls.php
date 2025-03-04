<?php
//
// Copyright © 2024 by Neparth
//
//--------------------------------------------------------------------------------------------------------------------------------

function mysql_insert($mysql_database, $mysql_table, $mysql_column, $mysql_column_value)
{
    require(WEBSITE_SERVER.'/settings_daemons/variables_settings/secrets_variables.php');
    
    $conn = new mysqli(NEPARTH_Databases[$mysql_database]['ip_address'].':'.NEPARTH_Databases[$mysql_database]['port'], NEPARTH_Databases[$mysql_database]['username'], NEPARTH_Databases[$mysql_database]['password'], NEPARTH_Databases[$mysql_database]['database_name']);
    
    if ($conn->connect_error) {
      saveLog("Connection failed for database $mysql_database: " . $conn->connect_error, "customs_logs/mysqls_customs.log");
        return false; // or handle the error as you see fit
    }

    $sql = "INSERT IGNORE INTO `".$mysql_table."` (`".$mysql_column."`) VALUES ('".$mysql_column_value."')";
    if ($conn->query($sql) === TRUE) {
      $mysql_insert = true;
    } else {
      $mysql_insert = false; // Indicate failure
      saveLog("Query failed for table $mysql_table: " . $conn->error, "customs_logs/mysqls_customs.log");
    }

    $conn->close(); // Close the database connection
    return $mysql_insert;
}

//--------------------------------------------------------------------------------------------------------------------------------

function mysql_multipleInsert($mysql_database, $mysql_table, $data)
{
    require(WEBSITE_SERVER.'/settings_daemons/variables_settings/secrets_variables.php');

    // Create a new connection to the database
    $conn = new mysqli(
        NEPARTH_Databases[$mysql_database]['ip_address'].':'.NEPARTH_Databases[$mysql_database]['port'], 
        NEPARTH_Databases[$mysql_database]['username'], 
        NEPARTH_Databases[$mysql_database]['password'], 
        NEPARTH_Databases[$mysql_database]['database_name']
    );

    // Check if the connection was successful
    if ($conn->connect_error)
    {
        saveLog("Connection failed for database $mysql_database: " . $conn->connect_error, "customs_logs/mysqls_customs.log");
        return false; // Return false if connection fails
    }

    // Prepare the columns and values for the SQL query
    $columns = array_keys($data);
    $values = array_map([$conn, 'real_escape_string'], array_values($data));

    $columns_sql = "`" . implode("`, `", $columns) . "`";
    $values_sql = "'" . implode("', '", $values) . "'";

    // Construct the full SQL query
    $sql = "INSERT IGNORE INTO `$mysql_table` ($columns_sql) VALUES ($values_sql)";

    // Execute the query and check for success
    if ($conn->query($sql) === TRUE)
    {
        $mysql_insert = true;
    }
    else
    {
        $mysql_insert = false; // Indicate failure
        saveLog("Query failed for table $mysql_table: " . $conn->error, "customs_logs/mysqls_customs.log");
    }

    // Close the database connection
    $conn->close();
    return $mysql_insert;
}

//--------------------------------------------------------------------------------------------------------------------------------

function mysql_multipleInsertAndUpdate($mysql_database, $mysql_table, $data)
{
    require(WEBSITE_SERVER.'/settings_daemons/variables_settings/secrets_variables.php');

    // Create a new connection to the database
    $conn = new mysqli(
        NEPARTH_Databases[$mysql_database]['ip_address'].':'.NEPARTH_Databases[$mysql_database]['port'], 
        NEPARTH_Databases[$mysql_database]['username'], 
        NEPARTH_Databases[$mysql_database]['password'], 
        NEPARTH_Databases[$mysql_database]['database_name']
    );

    // Check if the connection was successful
    if ($conn->connect_error)
    {
        saveLog("Connection failed for database $mysql_database: " . $conn->connect_error, "customs_logs/mysqls_customs.log");
        return false; // Return false if connection fails
    }

    // Prepare the columns and values for the SQL query
    $columns = array_keys($data);
    $values = array_map([$conn, 'real_escape_string'], array_values($data));

    $columns_sql = "`" . implode("`, `", $columns) . "`";
    $values_sql = "'" . implode("', '", $values) . "'";

    // Construct the full SQL query with ON DUPLICATE KEY UPDATE
    $updates = [];
    foreach ($data as $key => $value) {
        if ($key !== 'coin_address' && $key !== 'address') {
            $updates[] = "`$key` = '$value'";
        }
    }
    $updates_sql = implode(", ", $updates);
    $sql = "INSERT INTO `$mysql_table` ($columns_sql) VALUES ($values_sql) 
            ON DUPLICATE KEY UPDATE $updates_sql";

    // Execute the query and check for success
    if ($conn->query($sql) === TRUE)
    {
        $mysql_insert = true;
    }
    else
    {
        $mysql_insert = false; // Indicate failure
        saveLog("Query failed for table $mysql_table: " . $conn->error, "customs_logs/mysqls_customs.log");
    }

    // Close the database connection
    $conn->close();
    return $mysql_insert;
}

//--------------------------------------------------------------------------------------------------------------------------------
?>