<?php
//
// Copyright © 2024 by Neparth
//
//--------------------------------------------------------------------------------------------------------------------------------

function mysql_count($mysql_database, $mysql_table, $mysql_row_name, $mysql_row_value)
{
    require(WEBSITE_SERVER.'/settings_daemons/variables_settings/secrets_variables.php');

    $conn = new mysqli(NEPARTH_Databases[$mysql_database]['ip_address'].':'.NEPARTH_Databases[$mysql_database]['port'], NEPARTH_Databases[$mysql_database]['username'], NEPARTH_Databases[$mysql_database]['password'], NEPARTH_Databases[$mysql_database]['database_name']);

    // Check connection
    if ($conn->connect_error) {
        saveLog("Could not connect to the database: " . $conn->connect_error, "customs_logs/mysqls_customs.log");
        return 'NOT_FOUND'; // Could not connect to the database
    }

    $query = "SELECT COUNT(*) as count FROM `".$mysql_table."` WHERE `".$mysql_row_name."` = '".$mysql_row_value."'";
    $result = mysqli_query($conn, $query);

    // Check if query was successful
    if (!$result) {
        saveLog("Query failed for table $mysql_table: " . mysqli_error($conn), "customs_logs/mysqls_customs.log");
        return 'NOT_FOUND'; // Query failed (table or column-row doesn't exist, etc.)
    }

    $row = mysqli_fetch_assoc($result);
    $count = $row['count'];

    $conn->close();
    return $count;
}

//--------------------------------------------------------------------------------------------------------------------------------
?>