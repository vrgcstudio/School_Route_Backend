<?php
/**
 * Return parameter type for sql statement parameter binding
 * @param mixed $var
 * @return string
 */
function db_param_type($var)
{
    if (mb_strlen($var) > 1 && substr($var, 0, 1) == '0') {
        return 's';
    }

    if (is_int($var)) {
        return 'i';
    }

    if (is_numeric($var)) {
        return 'd';
    }

    return 's';
}

/**
 * Execute query to database
 * @param string $sql
 * @param string $parameterBinding
 * @param array $parameters
 * @return bool|mysqli_stmt
 */
function db_query($sql, $parameterBinding = '', $parameters = array())
{
    global $CONNECTION;

    if (strlen($parameterBinding) != count($parameters)) {
        return false;
    }

    if ($parameterBinding == '') {
        $statement = $CONNECTION->prepare($sql);
        $statement->execute();

        return $statement;
    }

    $parametersRefs = array();
    foreach ($parameters as $key => $value) {
        $parametersRefs[$key] = &$parameters[$key];
    }

    $statement = $CONNECTION->prepare($sql);
    if (!$statement) {
        return false;
    }
    call_user_func_array(array($statement, 'bind_param'), array_merge(array($parameterBinding), $parametersRefs));
    $statement->execute();

    return $statement;
}

/**
 * Get mysqli statement object from SQL
 * @param string $sql
 * @param array $parameters
 * @return bool|mysqli_stmt
 */
function db_sql($sql, $parameters = array())
{
    $bindings = '';
    foreach ($parameters as $parameter) {
        $bindings .= db_param_type($parameter);
    }

    $stmt = db_query($sql, $bindings, $parameters);
    if (!$stmt) {
        return false;
    }

    return $stmt;
}

/**
 * Get mysqli result from SQL
 * @param $sql
 * @param array $parameters
 * @return array
 */
function db_sql_get_result($sql, $parameters = array())
{
    $stmt = db_sql($sql, $parameters);
    if (!$stmt) {
        return [];
    }

    $result = array();
    $stmt->store_result();
    for ($i = 0; $i < $stmt->num_rows; $i++) {
        $metaData = $stmt->result_metadata();
        $fieldNames = [];
        while ($field = $metaData->fetch_field()) {
            $fieldNames[] = &$result[$i][$field->name];
        }
        call_user_func_array(array($stmt, 'bind_result'), $fieldNames);
        $stmt->fetch();
    }

    return $result;
}

/**
 * Get object array from mysqli result
 * @param array
 * @return stdClass[]|bool
 */
function db_result_get_objects($stmtResult)
{
    if (!is_array($stmtResult)) {
        return false;
    }

    $objects = array();

    while ($object = array_shift($stmtResult)) {
        array_push($objects, (object)$object);
    }

    return $objects;
}

/**
 * Get object from SQL
 * @param string $sql
 * @param array $conditions
 * @return bool|stdClass
 */
function db_get_record_sql($sql, $conditions = array())
{
    $mysqliResult = db_sql_get_result($sql, $conditions);
    if (!is_array($mysqliResult) || count($mysqliResult) == 0) {
        return false;
    }

    return (object)$mysqliResult[0];
}

/**
 * Get object array from SQL
 * @param string $sql
 * @param array $conditions
 * @return bool|stdClass[]
 */
function db_get_records_sql($sql, $conditions = array())
{
    $mysqliResult = db_sql_get_result($sql, $conditions);
    if (!is_array($mysqliResult)) {
        return false;
    }

    return db_result_get_objects($mysqliResult);
}

/**
 * Get object from conditions
 * @param string $table
 * @param array $conditions
 * @return bool|stdClass
 */
function db_get_record($table, $conditions)
{
    $fields = array();
    $parameters = array();

    foreach ($conditions as $property => $value) {
        if (is_null($value)) {
            array_push($fields, "{$property} IS NULL");
        } else {
            array_push($fields, "{$property} = ?");
            array_push($parameters, $value);
        }
    }

    $fieldsSQL = implode($fields, ' AND ');

    $sql = "SELECT * FROM {$table} WHERE {$fieldsSQL}";
    return db_get_record_sql($sql, $parameters);
}

/**
 * Get object array from conditions
 * @param string $table
 * @param array $conditions
 * @param string $sort
 * @return bool|stdClass[]
 */
function db_get_records_array($table, $conditions = array(), $sort = '')
{
    $parameters = array();
    if (count($conditions) == 0) {
        $sql = "SELECT * FROM {$table}";
    } else {
        $fields = array();

        foreach ($conditions as $property => $value) {
            if (is_null($value)) {
                array_push($fields, "{$property} IS NULL");
            } else {
                array_push($fields, "{$property} = ?");
                array_push($parameters, $value);
            }
        }

        $fieldsSQL = implode($fields, ' AND ');

        $sql = "SELECT * FROM {$table} WHERE {$fieldsSQL}";
    }
    if ($sort != '') {
        $sql .= " ORDER BY {$sort}";
    }
    return db_get_records_sql($sql, $parameters);
}

/**
 * Insert record to database
 * @param string $table
 * @param stdClass $object
 * @return bool|int
 */
function db_insert_record($table, $object)
{
    if (!is_object($object)) {
        return false;
    }

    $paramBindings = '';

    $fields = array();
    $values = array();
    $bindings = array();
    foreach ($object as $property => $value) {
        array_push($fields, $property);
        array_push($values, $value);
        array_push($bindings, '?');
        $paramBindings .= db_param_type($value);
    }

    $fieldsQueryStr = implode(', ', $fields);
    $bindingsStr = implode(', ', $bindings);

    $sql = "INSERT INTO {$table} ({$fieldsQueryStr}) VALUES ({$bindingsStr})";

    $queryResult = db_query($sql, $paramBindings, $values);
    if (!$queryResult) {
        return false;
    }

    if ($queryResult->errno) {
        return false;
    }

    if ($queryResult->insert_id) {
        return $queryResult->insert_id;
    }

    return true;
}

/**
 * Insert records array to database
 * @param string $table
 * @param stdClass[] $objects
 * @return bool
 */
function db_insert_records($table, $objects)
{
    if (!is_array($objects)) {
        return false;
    }

    foreach ($objects as $object) {
        if (!is_object($object)) {
            continue;
        }

        if (!db_insert_record($table, $object)) {
            return false;
        }
    }

    return true;
}

/**
 * Update records
 * @param string $table
 * @param stdClass[]|stdClass $objects
 * @return bool
 */
function db_update_records($table, $objects)
{
    $keyFieldResult = db_sql_get_result("SHOW KEYS FROM {$table} WHERE Key_name = 'PRIMARY'");
    $keyField = $keyFieldResult[0]['Column_name'];

    if (is_object($objects)) {
        $objects = array($objects);
    }

    foreach ($objects as $object) {
        $updateSQLs = array();
        $parameters = array();

        foreach ($object as $property => $value) {
            if ($property == $keyField) {
                continue;
            }

            if (is_object($value)) {
                continue;
            }

            array_push($updateSQLs, "{$property} = ?");
            array_push($parameters, $value);
        }

        array_push($parameters, $object->{$keyField});

        $setSQL = implode($updateSQLs, ', ');

        $sql = "UPDATE {$table} SET {$setSQL} WHERE {$keyField} = ?";
        if (!db_sql($sql, $parameters)) {
            return false;
        }
    }

    return true;
}

/**
 * Update primary key
 * @param string $table
 * @param string $field_name
 * @param mixed $old_value
 * @param mixed $new_value
 * @return bool|mysqli_stmt
 */
function db_update_pk($table, $field_name, $old_value, $new_value)
{
    return db_sql("UPDATE {$table} SET {$field_name} = ? WHERE {$field_name} = ?", [$new_value, $old_value]);
}

/**
 * Delete records
 * @param string $table
 * @param array $conditions
 * @return bool
 */
function db_delete_record($table, $conditions = array())
{
    $parameters = array();

    $condition_query_string = '';
    if (count($conditions) > 0) {
        $condition_strings = array();

        foreach ($conditions as $field => $value) {
            if (is_null($value)) {
                array_push($condition_strings, "{$field} IS NULL");
            } else {
                array_push($condition_strings, "{$field} = ?");
                array_push($parameters, $value);
            }
        }

        $condition_query_string = ' WHERE ' . implode(' AND ', $condition_strings);
    }

    $sql = "DELETE FROM {$table}{$condition_query_string}";
    if (!db_sql($sql, $parameters)) {
        return false;
    }

    return true;
}

/**
 * Num rows
 * @param string $table
 * @param array $conditions
 * @return mixed
 */
function db_num_rows($table, $conditions = array())
{
    $parameters = array();

    $condition_query_string = '';
    if (count($conditions) > 0) {
        $condition_strings = array();

        foreach ($conditions as $field => $value) {
            if (is_null($value)) {
                array_push($condition_strings, "{$field} IS NULL");
            } else {
                array_push($condition_strings, "{$field} = ?");
                array_push($parameters, $value);
            }
        }

        $condition_query_string = ' WHERE ' . implode(' AND ', $condition_strings);
    }

    $sql = "SELECT COUNT(*) AS num_rows FROM {$table}{$condition_query_string}";
    $result = db_sql_get_result($sql, $parameters);
    return $result[0]['num_rows'];
}

/**
 * Create query of IN statement
 * @param array $array
 * @return string
 */
function db_create_in_query($array)
{
    foreach ($array as &$item) {
        $item = '?';
    }

    return '(' . implode(', ', $array) . ')';
}
