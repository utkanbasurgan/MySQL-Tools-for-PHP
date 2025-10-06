<?php
//
// Utkan Başurgan
//
//--------------------------------------------------------------------------------------------------------------------------------

function softwares_functions_mysqls_selects($mysql_database, $mysql_table, $mysql_row_name, $mysql_row_value, $mysql_column)
{
    require(NEPARTH_Roots_Daemons.'/04_settings_daemons/variables_settings/secrets_variables.php');
    
    $conn = new mysqli(NEPARTH_Databases[$mysql_database]['ip_address'].':'.NEPARTH_Databases[$mysql_database]['port'], NEPARTH_Databases[$mysql_database]['username'], NEPARTH_Databases[$mysql_database]['password'], NEPARTH_Databases[$mysql_database]['database_name']);
    
    if ($conn->connect_error)
    {
        softwares_functions_logs_mains("Could not connect to the database: " . $conn->connect_error);
        return 'NOT_FOUND';
    }

    $query = "SELECT `".$mysql_column."` FROM `".$mysql_table."` WHERE `".$mysql_row_name."` = '".$mysql_row_value."'";
    $result = mysqli_query($conn, $query);

    if (!$result)
    {
        softwares_functions_logs_mains("Query failed for table $mysql_table: " . mysqli_error($conn));
        return 'NOT_FOUND';
    }

    $softwares_functions_mysqls_selects = 'NOT_FOUND';
    while ($row = mysqli_fetch_assoc($result))
    {
        $softwares_functions_mysqls_selects = $row[$mysql_column];
        break;
    }

    if ($softwares_functions_mysqls_selects == 'NOT_FOUND')
    {
        softwares_functions_logs_mains("No matching records found in $mysql_table for $mysql_row_name = '$mysql_row_value'");
    }

    $conn->close();
    return $softwares_functions_mysqls_selects;
}

//--------------------------------------------------------------------------------------------------------------------------------

function softwares_functions_mysqls_selects_fulls($mysql_database, $mysql_table, $mysql_row_name, $mysql_row_value)
{
    require(NEPARTH_Roots_Daemons.'/04_settings_daemons/variables_settings/secrets_variables.php');

    $conn = new mysqli(
        NEPARTH_Databases[$mysql_database]['ip_address'] . ':' . NEPARTH_Databases[$mysql_database]['port'], 
        NEPARTH_Databases[$mysql_database]['username'], 
        NEPARTH_Databases[$mysql_database]['password'], 
        NEPARTH_Databases[$mysql_database]['database_name']
    );

    if ($conn->connect_error)
    {
        die("Connection failed: " . $conn->connect_error);
    }

    $query = "SELECT * FROM `" . $mysql_table . "` WHERE `" . $mysql_row_name . "` = '" . $conn->real_escape_string($mysql_row_value) . "'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0)
    {
        $model = new stdClass();
        if ($row = $result->fetch_assoc())
        {
            foreach ($row as $key => $value)
            {
                $model->$key = $value;
            }
        }
        $conn->close();
        return $model;
    }

    $conn->close();
    return null;
}

//--------------------------------------------------------------------------------------------------------------------------------

function softwares_functions_mysqls_selects_arrays($mysql_database, $mysql_table, $mysql_column, $mysql_row_name = null, $mysql_row_value = null, $sort = null, $sortType = 'DESC', $limit = null, $first = 1, $last = null)
{
    require(NEPARTH_Roots_Daemons.'/04_settings_daemons/variables_settings/secrets_variables.php');
    
    $mysqli = new mysqli(NEPARTH_Databases[$mysql_database]['ip_address'].':'.NEPARTH_Databases[$mysql_database]['port'], NEPARTH_Databases[$mysql_database]['username'], NEPARTH_Databases[$mysql_database]['password'], NEPARTH_Databases[$mysql_database]['database_name']);
    
    if ($mysqli->connect_error)
    {
        softwares_functions_logs_mains("Connection failed: " . $mysqli->connect_error);
        die("Database connection failed: " . $mysqli->connect_error);
    }
    
    if (is_array($mysql_column))
    {
        $columns_string = "`" . implode("`, `", $mysql_column) . "`";
    }
    else
    {
        $columns_string = "`".$mysql_column."`";
    }
    
    $query = "SELECT $columns_string FROM `$mysql_table`";
    $params = [];
    $types = "";
    
    if (!is_null($mysql_row_name) && !is_null($mysql_row_value))
    {
        if (is_array($mysql_row_value))
        {
            $placeholders = implode(',', array_fill(0, count($mysql_row_value), '?'));
            $query .= " WHERE `$mysql_row_name` IN ($placeholders)";
            foreach ($mysql_row_value as $value)
            {
                $params[] = $value;
                $types .= "s";
            }
        }
        else
        {
            $query .= " WHERE `$mysql_row_name` = ?";
            $params[] = $mysql_row_value;
            $types .= "s";
        }
    }
    
    $sortType = strtoupper($sortType) == 'DESC' ? 'DESC' : 'ASC';
    
    if (!is_null($sort))
    {
        $query .= " ORDER BY `$sort` $sortType";
    }

    if (!is_null($first) && is_numeric($first) && $first > 0)
    {
        $offset = ($first - 1);
    }
    else
    {
        $offset = 0;
    }

    if (!is_null($last) && is_numeric($last) && $last > $first)
    {
        $pagination_limit = $last - $first + 1;
    }
    else
    {
        $pagination_limit = null;
    }
    
    if (!is_null($pagination_limit))
    {
        $query .= " LIMIT ?, ?";
        $params[] = (int)$offset;
        $params[] = (int)$pagination_limit;
        $types .= "ii";
    }
    elseif (!is_null($limit) && is_numeric($limit))
    {
        $query .= " LIMIT ?";
        $params[] = (int)$limit;
        $types .= "i";
    }

    $stmt = $mysqli->prepare($query);
    if (!$stmt)
    {
        softwares_functions_logs_mains("Prepare failed: " . $mysqli->error);
        return 'PREPARE_FAILED';
    }
    
    if (!empty($params))
    {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute())
    {
        softwares_functions_logs_mains("Execute failed: " . $stmt->error);
        return 'EXECUTE_FAILED';
    }
    
    $result = $stmt->get_result();
    $softwares_functions_mysqls_selects_arrays = [];
    while ($row = $result->fetch_assoc())
    {
        $softwares_functions_mysqls_selects_arrays[] = $row;
    }
    
    if (empty($softwares_functions_mysqls_selects_arrays))
    {
        softwares_functions_logs_mains("Not found: $query");
        return 'NOT_FOUND';
    }
    
    if (!is_array($mysql_column) || count($mysql_column) === 1)
    {
        $single_column = is_array($mysql_column) ? $mysql_column[0] : $mysql_column;
        foreach ($softwares_functions_mysqls_selects_arrays as &$row)
        {
            $row = $row[$single_column];
        }
        unset($row);
    }
    
    $stmt->close();
    $mysqli->close();
    return $softwares_functions_mysqls_selects_arrays;
}

//--------------------------------------------------------------------------------------------------------------------------------
?>