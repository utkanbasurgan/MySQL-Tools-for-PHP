<?php
//
// Copyright © 2024 by Neparth
//
//--------------------------------------------------------------------------------------------------------------------------------

function mysql_checkup($mysql_database)
{
    require(WEBSITE_SERVER.'/settings_daemons/variables_settings/secrets_variables.php');
    
    $servername = NEPARTH_Databases[$mysql_database]['ip_address'];
    $port = NEPARTH_Databases[$mysql_database]['port'];
    $username = NEPARTH_Databases[$mysql_database]['username'];
    $password = NEPARTH_Databases[$mysql_database]['password'];
    $dbname = NEPARTH_Databases[$mysql_database]['database_name'];

    // Create connection using mysqli with timeout
    $conn = @new mysqli();
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10); // Timeout set to 10 seconds
    
    try
    {
        $conn->real_connect($servername, $username, $password, $dbname, $port);
    }
    catch (mysqli_sql_exception $e)
    {
        saveLog("Connection failed: " . $e->getMessage(), "customs_logs/mysqls_customs.log");
        return false;
    }
    
    // Check connection
    if ($conn->connect_error) 
    {
        saveLog("Connection failed: " . $conn->connect_error, "customs_logs/mysqls_customs.log");
        return false;
    }

    // Ensure the server is active by checking if the connection is established
    if (!$conn->ping()) 
    {
        saveLog("Server is inactive: " . $conn->error, "customs_logs/mysqls_customs.log");
        $conn->close();
        return false;
    }

    $conn->close();
    return true;
}

//--------------------------------------------------------------------------------------------------------------------------------
?>
