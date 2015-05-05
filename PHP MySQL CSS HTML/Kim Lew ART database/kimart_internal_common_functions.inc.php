<?php
/* INCLUDE file for common functions.  */

function mysqlQueryResult($command, $dbc) {
// Note: If !($result), i.e., has nothing, it's not nec. a technical error but 
// that the requested data is not in the database table.
  $result = $dbc->query($command);

  if (!$result) {
  echo "Command is: " . $command . "<br>";
    echo "<br>Query database error. Contact the technical team. <br>";
//    die();
  }
  else {
    return $result;
  }
}

// CHECK mysql (SELECT or INSERT INTO) query statement for a problem.
function hasMysqlError($dbc) {
  $mysql_error = mysqli_error($dbc);
  
  // If SELECT or INSERT INTO query has a problem, send the MySQL error descriptions to 
  // a PHP error log file so user does not see. They append to the end of the log file.
  // PHP error log file is configured in php.ini file, most likely the Apache error log.
  if (!empty($mysql_error)) {
    error_log($mysql_error);
    // Note: This echo statement will NOT be in the CSS style for err_msg.
    echo "Technical errors. Ask the technical team to look at error log.";
    die();
  }
}

function addToErrMsgArray($err_msg, &$err_msg_array) {
// Pushes an err_msg to the end of the $err_msg_array.
// NOTE: Need to indicate a reference to an array when it is passed parameter using &.
// int array_push (array &$array , mixed $value1 [, mixed $... ])
  if (($err_msg) && ($err_msg != "")) {
   array_push($err_msg_array, $err_msg); 
  }
}

function displayTechErrMsg($tech_err_msg) {
  echo $tech_err_msg;
  die();
}
function displayErrMsg($err_msg) {
  if ($err_msg != "") {
    $err_msg = "<b>Error: </b>" . $err_msg;
    echo $err_msg;
  }
}
function displayMsg($msg_to_user) {
  if ($msg_to_user != "") {
    echo $msg_to_user;
  }
}
/*
function compare_input_with_db_value($string_in_superglobal, $dbc, $string_in_db) {
// Compare value in table to value in $_POST to see if new data was entered. If diff,
// then there was so assign new value as return variable and return to calling script. 
// Note: Ideal to return $variable and $err_msg BUT not allowed to return 2 variables.
    
  if ($string_in_superglobal != $string_in_db) {
  // Case 1: Field has data and database has data and values are diff.
    return $string_in_superglobal;
  }
  else {
  // Case 2: Field has data and database has data, and values are same.
    return $string_in_db;
  }
}
*/
function sanitize_input($dbc, $unclean_string) {
// Sanitize an input string.
  $clean_string = mysqli_real_escape_string($dbc, (trim($unclean_string)));
  return $clean_string;
}

// Get table values from kimart_tb except price_listed which is in kimart_purch_tb.
function getTableValues($dbc, $kimart_id) {
  $command = "SELECT * FROM kimart_tb WHERE kimart_id='$kimart_id'";
  $hasMysqlError = hasMysqlError($dbc);
  $result = mysqlQueryResult($command, $dbc);
  return $result;
}

// Get price_listed from kimart_purch_tb
function getPriceInTable($dbc, $kimart_id) {
  $command_p = "SELECT price_listed FROM kimart_purch_tb WHERE kimart_id='$kimart_id'";
  $hasMysqlError = hasMysqlError($dbc);
  $result_p = mysqlQueryResult($command_p, $dbc);
  return $result_p;
}

function generateDiv($div_name) {
  echo '<div class = "row">';
  echo '<div class = "field_name_column"><label>' . $div_name . '</label></div> ';
  echo '<div class = "input_column">';
}

function generateSelectOption($db_value, $option_value) {
  echo '<option value = "' . $option_value . '" ';
  if ($db_value == $option_value) {
    echo 'selected';
  }
  echo '>' . $option_value;
}

function generateOptionsInArray($db_value, $options_array) {
  foreach($options_array as $index) {
    generateSelectOption($db_value, $index);
  }
}

// KEY constants for getUserData() with global scope.
define('KEY_USER_NAME', 'user_name');
define('KEY_EMAIL', 'email');
define('KEY_PASSWORD', 'password');
   
define('KEY_CAN_DELETE_WORK', 'can_delete_work');
define('KEY_CAN_ADD_USER', 'can_add_user');
define('KEY_CAN_EDIT_USER', 'can_edit_user');
define('KEY_CAN_DELETE_USER', 'can_delete_user');
   
define('KEY_DATE_ADDED', 'date_added');
define('KEY_DATE_LEFT', 'date_left');
   
function getUserData($user_id, $dbc) {
  // CHECK the database for user_id data. 
  $command = "SELECT * FROM kimart_internal_login_tb WHERE user_id='$user_id'";
  $result = mysqlQueryResult($command, $dbc);
  hasMysqlError($dbc);
    
  if ($result->num_rows <= 0) {
    $msg_to_user = "This is a new user. Click Add User.";
    die();
  }
  
  if ($result->num_rows > 0) {
    // Retrieve data for user you are editing and add to array with string constant as key.
    // Since adding data to array this other way, do not need array_push($userDataArray, $user_name).
    
    $data = $result->fetch_object();
    $userDataArray[KEY_USER_NAME] = $data->user_name;
    $userDataArray[KEY_EMAIL] = $data->email;
    $userDataArray[KEY_PASSWORD] = $data->password;

    $userDataArray[KEY_CAN_DELETE_WORK] = $data->can_delete_work;
    $userDataArray[KEY_CAN_ADD_USER] = $data->can_add_user;
    $userDataArray[KEY_CAN_EDIT_USER] = $data->can_edit_user;
    $userDataArray[KEY_CAN_DELETE_USER] = $data->can_delete_user;

    $userDataArray[KEY_DATE_ADDED] = $data->date_added;
    $userDataArray[KEY_DATE_LEFT] = $data->date_left;
  }
  return $userDataArray;
}

?>
