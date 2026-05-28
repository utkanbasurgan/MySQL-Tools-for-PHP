<?php
//
// Utkan Başurgan
//
//--------------------------------------------------------------------------------------------------------------------------------

function softwares_functions_mysqls_checks($mysql_database)
{
    require(NEPARTH_Roots_Daemons.'/04_settings_daemons/variables_settings/secrets_variables.php');
    
    $servername = NEPARTH_Databases[$mysql_database]['ip_address'];
    $port = NEPARTH_Databases[$mysql_database]['port'];
    $username = NEPARTH_Databases[$mysql_database]['username'];
    $password = NEPARTH_Databases[$mysql_database]['password'];
    $dbname = NEPARTH_Databases[$mysql_database]['database_name'];
    
    $conn = @new mysqli();
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
    
    try
    {
        $conn->real_connect($servername, $username, $password, $dbname, $port);
    }
    catch (mysqli_sql_exception $e)
    {
        softwares_functions_logs_mains("Connection failed: " . $e->getMessage());
        return false;
    }
    
    if ($conn->connect_error)
    {
        softwares_functions_logs_mains("Connection failed: " . $conn->connect_error);
        return false;
    }
    
    if (!$conn->ping())
    {
        softwares_functions_logs_mains("Server is inactive: " . $conn->error);
        $conn->close();
        return false;
    }
    
    $conn->close();
    return true;
}

//--------------------------------------------------------------------------------------------------------------------------------
?>
