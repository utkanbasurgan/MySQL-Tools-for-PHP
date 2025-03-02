<?php
//
// Copyright © 2024 by Neparth
//
//--------------------------------------------------------------------------------------------------------------------------------

function mysql_select($mysql_database, $mysql_table, $mysql_row_name, $mysql_row_value, $mysql_column) {
    require(WEBSITE_SERVER.'/settings_daemons/variables_settings/secrets_variables.php');
    
    $conn = new mysqli(NEPARTH_Databases[$mysql_database]['ip_address'].':'.NEPARTH_Databases[$mysql_database]['port'], NEPARTH_Databases[$mysql_database]['username'], NEPARTH_Databases[$mysql_database]['password'], NEPARTH_Databases[$mysql_database]['database_name']);
    
    // Check connection
    if ($conn->connect_error) {
        saveLog("Could not connect to the database: " . $conn->connect_error, "customs_logs/mysqls_customs.log");
        return 'NOT_FOUND'; // Could not connect to the database
    }

    $query = "SELECT `".$mysql_column."` FROM `".$mysql_table."` WHERE `".$mysql_row_name."` = '".$mysql_row_value."'";
    $result = mysqli_query($conn, $query);

    // Check if query was successful
    if (!$result) {
        saveLog("Query failed for table $mysql_table: " . mysqli_error($conn), "customs_logs/mysqls_customs.log");
        return 'NOT_FOUND'; // Query failed (table or column-row doesn't exist, etc.)
    }

    $mysql_select = 'NOT_FOUND';
    while ($row = mysqli_fetch_assoc($result)) {
        $mysql_select = $row[$mysql_column];
        break; // Assuming you only need one match, otherwise remove this line
    }

    if ($mysql_select == 'NOT_FOUND') {
        saveLog("No matching records found in $mysql_table for $mysql_row_name = '$mysql_row_value'", "customs_logs/mysqls_customs.log");
    }

    $conn->close();
    return $mysql_select;
}

//--------------------------------------------------------------------------------------------------------------------------------

function mysql_fullSelect($mysql_database, $mysql_table, $mysql_row_name, $mysql_row_value)
{
    require(WEBSITE_SERVER.'/settings_daemons/variables_settings/secrets_variables.php');

    $conn = new mysqli(
        NEPARTH_Databases[$mysql_database]['ip_address'] . ':' . NEPARTH_Databases[$mysql_database]['port'], 
        NEPARTH_Databases[$mysql_database]['username'], 
        NEPARTH_Databases[$mysql_database]['password'], 
        NEPARTH_Databases[$mysql_database]['database_name']
    );

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $query = "SELECT * FROM `" . $mysql_table . "` WHERE `" . $mysql_row_name . "` = '" . $conn->real_escape_string($mysql_row_value) . "'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $model = new stdClass();
        if ($row = $result->fetch_assoc()) {
            foreach ($row as $key => $value) {
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

function mysql_arraySelect($mysql_database, $mysql_table, $mysql_column, $mysql_row_name = null, $mysql_row_value = null, $sort = null, $sortType = 'DESC', $limit = null, $first = 1, $last = null)
{
    require(WEBSITE_SERVER.'/settings_daemons/variables_settings/secrets_variables.php');
    
    $mysqli = new mysqli(NEPARTH_Databases[$mysql_database]['ip_address'].':'.NEPARTH_Databases[$mysql_database]['port'], NEPARTH_Databases[$mysql_database]['username'], NEPARTH_Databases[$mysql_database]['password'], NEPARTH_Databases[$mysql_database]['database_name']);
    
    if ($mysqli->connect_error) {
        saveLog("Connection failed: " . $mysqli->connect_error, "customs_logs/mysqls_customs.log");
        die("Database connection failed: " . $mysqli->connect_error);
    }
    
    if (is_array($mysql_column)) {
        $columns_string = "`" . implode("`, `", $mysql_column) . "`";
    } else {
        $columns_string = "`".$mysql_column."`";
    }
    
    $query = "SELECT $columns_string FROM `$mysql_table`";
    $params = [];
    $types = "";
    
    if (!is_null($mysql_row_name) && !is_null($mysql_row_value)) {
        if (is_array($mysql_row_value)) {
            $placeholders = implode(',', array_fill(0, count($mysql_row_value), '?'));
            $query .= " WHERE `$mysql_row_name` IN ($placeholders)";
            foreach ($mysql_row_value as $value) {
                $params[] = $value;
                $types .= "s";
            }
        } else {
            $query .= " WHERE `$mysql_row_name` = ?";
            $params[] = $mysql_row_value;
            $types .= "s";
        }
    }
    
    $sortType = strtoupper($sortType) == 'DESC' ? 'DESC' : 'ASC';
    
    if (!is_null($sort)) {
        $query .= " ORDER BY `$sort` $sortType";
    }

    // Calculate offset and pagination limit
    if (!is_null($first) && is_numeric($first) && $first > 0) {
        $offset = ($first - 1);
    } else {
        $offset = 0;
    }

    if (!is_null($last) && is_numeric($last) && $last > $first) {
        $pagination_limit = $last - $first + 1;
    } else {
        $pagination_limit = null;
    }
    
    if (!is_null($pagination_limit)) {
        $query .= " LIMIT ?, ?";
        $params[] = (int)$offset;
        $params[] = (int)$pagination_limit;
        $types .= "ii";
    } elseif (!is_null($limit) && is_numeric($limit)) {
        $query .= " LIMIT ?";
        $params[] = (int)$limit;
        $types .= "i";
    }

    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        saveLog("Prepare failed: " . $mysqli->error, "customs_logs/mysqls_customs.log");
        return 'PREPARE_FAILED';
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        saveLog("Execute failed: " . $stmt->error, "customs_logs/mysqls_customs.log");
        return 'EXECUTE_FAILED';
    }
    
    $result = $stmt->get_result();
    $mysql_arraySelect = [];
    while ($row = $result->fetch_assoc()) {
        $mysql_arraySelect[] = $row;
    }
    
    if (empty($mysql_arraySelect)) {
        saveLog("Not found: $query", "customs_logs/mysqls_customs.log");
        return 'NOT_FOUND';
    }
    
    if (!is_array($mysql_column) || count($mysql_column) === 1) {
        $single_column = is_array($mysql_column) ? $mysql_column[0] : $mysql_column;
        foreach ($mysql_arraySelect as &$row) {
            $row = $row[$single_column];
        }
        unset($row);
    }
    
    $stmt->close();
    $mysqli->close();
    return $mysql_arraySelect;
}

//--------------------------------------------------------------------------------------------------------------------------------

function mysql_arraySelectWithCount($mysql_database, $mysql_table, $mysql_column, $mysql_row_name = null, $mysql_row_value = null, $sort = null, $sortType = 'DESC', $limit = null, $first = 1, $last = null)
{
    require(WEBSITE_SERVER.'/settings_daemons/variables_settings/secrets_variables.php');
    
    $mysqli = new mysqli(NEPARTH_Databases[$mysql_database]['ip_address'].':'.NEPARTH_Databases[$mysql_database]['port'], NEPARTH_Databases[$mysql_database]['username'], NEPARTH_Databases[$mysql_database]['password'], NEPARTH_Databases[$mysql_database]['database_name']);
    
    if ($mysqli->connect_error) {
        saveLog("Connection failed: " . $mysqli->connect_error, "customs_logs/mysqls_customs.log");
        die("Database connection failed: " . $mysqli->connect_error);
    }
    
    if (is_array($mysql_column)) {
        $columns_string = "" . implode(", ", $mysql_column) . "";
    } else {
        $columns_string = "".$mysql_column."";
    }
    
    $count_query = "SELECT COUNT(*) as total FROM $mysql_table";
    $query = "SELECT $columns_string FROM $mysql_table";
    $params = [];
    $types = "";
    $count_params = [];
    $count_types = "";

    if (!is_null($mysql_row_name) && !is_null($mysql_row_value)) {
        if (is_array($mysql_row_value)) {
            $placeholders = implode(',', array_fill(0, count($mysql_row_value), '?'));
            $count_query .= " WHERE $mysql_row_name IN ($placeholders)";
            $query .= " WHERE $mysql_row_name IN ($placeholders)";
            foreach ($mysql_row_value as $value) {
                $params[] = $value;
                $types .= "s";
                $count_params[] = $value;
                $count_types .= "s";
            }
        } else {
            $count_query .= " WHERE $mysql_row_name = ?";
            $query .= " WHERE $mysql_row_name = ?";
            $params[] = $mysql_row_value;
            $types .= "s";
            $count_params[] = $mysql_row_value;
            $count_types .= "s";
        }
    }

    // Handle different sort types
    $sortType = strtoupper($sortType);
    
    if (!is_null($sort)) {
        if ($sortType == 'ASC-NUMBER' || $sortType == 'DESC-NUMBER') {
            $sortTypeSQL = $sortType == 'DESC-NUMBER' ? 'DESC' : 'ASC';
            $query .= " ORDER BY CAST($sort AS UNSIGNED) $sortTypeSQL";
        } else {
            $sortTypeSQL = $sortType == 'DESC' ? 'DESC' : 'ASC';
            $query .= " ORDER BY $sort $sortTypeSQL";
        }
    }

    if (!is_null($first) && is_numeric($first) && $first > 0) {
        $offset = ($first - 1);
    } else {
        $offset = 0;
    }

    if (!is_null($last) && is_numeric($last) && $last > $first) {
        $pagination_limit = $last - $first + 1;
    } else {
        $pagination_limit = null;
    }
    
    if (!is_null($pagination_limit)) {
        $query .= " LIMIT ?, ?";
        $params[] = (int)$offset;
        $params[] = (int)$pagination_limit;
        $types .= "ii";
    } elseif (!is_null($limit) && is_numeric($limit)) {
        $query .= " LIMIT ?";
        $params[] = (int)$limit;
        $types .= "i";
    }

    $count_stmt = $mysqli->prepare($count_query);
    if (!$count_stmt) {
        saveLog("Prepare failed for count: " . $mysqli->error, "customs_logs/mysqls_customs.log");
        return ['PREPARE_FAILED', 0];
    }
    
    if (!empty($count_params)) {
        $count_stmt->bind_param($count_types, ...$count_params);
    }

    if (!$count_stmt->execute()) {
        saveLog("Execute failed for count: " . $count_stmt->error, "customs_logs/mysqls_customs.log");
        return ['EXECUTE_FAILED', 0];
    }

    $count_result = $count_stmt->get_result();
    $total_count = $count_result->fetch_assoc()['total'];
    $count_stmt->close();

    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        saveLog("Prepare failed: " . $mysqli->error, "customs_logs/mysqls_customs.log");
        return ['PREPARE_FAILED', 0];
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        saveLog("Execute failed: " . $stmt->error, "customs_logs/mysqls_customs.log");
        return ['EXECUTE_FAILED', 0];
    }
    
    $result = $stmt->get_result();
    $mysql_arraySelect = [];
    while ($row = $result->fetch_assoc()) {
        $mysql_arraySelect[] = $row;
    }

    if (empty($mysql_arraySelect)) {
        saveLog("Not found: $query", "customs_logs/mysqls_customs.log");
        return ['NOT_FOUND', 0];
    }
    
    if (!is_array($mysql_column) || count($mysql_column) === 1) {
        $single_column = is_array($mysql_column) ? $mysql_column[0] : $mysql_column;
        foreach ($mysql_arraySelect as &$row) {
            $row = $row[$single_column];
        }
        unset($row);
    }
    
    $stmt->close();
    $mysqli->close();
    return [$mysql_arraySelect, $total_count];
}

//--------------------------------------------------------------------------------------------------------------------------------
?>