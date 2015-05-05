<?php
// INCLUDE file with validation functions for kimart files with form input.
// Design: Separate validation functions for each field to keep it short and readable.
// Also: Might help with reusability later if there are similar functions needed, e.g., external user name.

/***  Create Login FUNCTIONS  ***/
function validate_user_name($user_name) {
  $err_msg = "";  
  
  if (!($user_name)) { // If Login User Name field is not filled in.
    $err_msg = "Enter a name in User Name.<br>";
  }
  else if ((strlen($user_name) < 8) || (strlen($user_name) > 16)) {
    $err_msg = "User name must be 8 to 16 characters.<br>";
  }
  else if (!(preg_match("/^[a-zA-Z0-9_]{8,16}$/", $user_name))) {
    $err_msg = " Login user name can only:<br>
    - have letters, numbers or underscores <br>
    - be 8 to 16 characters long.<br>";
  }
  return $err_msg;
}

/*  ------------------------- Specific functions for Add User ------------------------------- */
function validate_email_in_add_user($email, $email_confirm) {
  $err_msg = "";  
  $good_email = "/^[A-Za-z0-9._-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/";
  /* Matches almost any email address in use today.
     Matches a character in the group [a-zA-Z0-9._-] one or more times, followed by:
     - an @ sign
     - the same group again
     - a final . 
     - the top-level domain btwn 2 and 4 characters, upper case and lower case.
  */
  if ($email == '') {
    $err_msg = "Enter an email in Email.<br>";
  }
  else if ($email != '') {
    if (strlen($email) > 40) {
      $err_msg = "The email must be less than 40 characters.<br>";
    }
    else if (!preg_match($good_email, $email)) {
      $err_msg = "Email must: <br> - start with a letter, number, underscore, period or hyphen, <br>
      - be followed by any number of letters, numbers, underscores, periods or hyphens, <br> 
      - be followed by @, <br> 
      - then be followed by the 2-4 letter domain. <br>";
    }
    else if ($email_confirm == '') {
      $err_msg = "Enter an email in Confirm Email.<br>";
    }
    else if (!($email_confirm == $email)) {   
      $err_msg = "Confirm Email and Email must be typed exactly the same.<br>";
    }
    else if (!$err_msg) {  // VALIDATE domain.
      // Strip out everything BUT the domain from the email.
      $domain_only = preg_replace('/^[a-zA-Z0-9][a-zA-Z0-9\._\-&!?=#]*@/', '', $email);
        
      if (!checkdnsrr($domain_only)) {  // Check if $domain is registered. 
        $err_msg = "Your email domain is not valid.<br>";
      }
    }
  }
  return $err_msg;
}

function validate_password_in_add_user($password, $password_confirm) {
  $err_msg = "";
 
  if ($password == '') {
    $err_msg = "Enter a password in Password.<br>";
  }
  if ($password != '') {
    if ((strlen($password) < 8) || (strlen($password) > 16)) { // Check Password Strength
      $err_msg = "A password must have 8 to 16 characters.<br>";
    }
    else if (!preg_match("/[a-z].*[a-z]/", $password)) {
      $err_msg = "A password must have at least 2 lowercase letters.<br>";
    }
    else if (!preg_match("/[A-Z].*[A-Z]/", $password)) {
      $err_msg = "A password must have at least 2 UPPERCASE letters.<br>";
    }
    else if (!preg_match("/[0-9].*[0-9]/", $password)) {  
      $err_msg = "A password must have at least 2 numbers.<br>";
    }
    else if (!preg_match("/[^a-zA-Z0-9\s].*[^a-zA-Z0-9\s]/", $password)) {
    //  Note:  This is outside the charset so also add:  \s
      $err_msg = "Password must have at least 2 special characters: !@#$&%* <br>";
    }
    else if ($password_confirm == '') {
      $err_msg = "Enter a password in Confirm Password.<br>";
    }
    else if ($password != $password_confirm) {
      $err_msg = "Confirm Password and Password must be typed exactly the same.<br>";
    }
  }
  return $err_msg;
}
/*  ------------------------------- End of Specific functions for Add User -------------------------------- */


/*------------------------------ User Data Validation Functions -------------------------------*/
function validate_email($email_new, $email_confirm) {
  $err_msg = "";  
  $good_email = "/^[A-Za-z0-9._-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/";
  /* Matches almost any email address in use today.
     Matches a character in the group [a-zA-Z0-9._-] one or more times, followed by:
     - an @ sign
     - the same group again
     - a final . 
     - the top-level domain btwn 2 and 4 characters, upper case and lower case.
  */
  
  if (empty($email_new) || empty($email_confirm)) {
    if (empty($email_new) && !empty($email_confirm)) {
      $err_msg = "Enter an email in New Email.<br>";
    }
    if (!empty($email_new) && empty($email_confirm)) {
      $err_msg = "Enter an email in Confirm New Email.<br>";
    }
  }
  else if (strlen($email_new) > 40) {
    $err_msg = "The email must be less than 40 characters.<br>";
  }
  
  else if (!preg_match($good_email, $email_new)) {
    $err_msg = "The email must: <br> 
    - start with a letter, number, underscore, period or hyphen, <br>
    - be followed by any number of letters, numbers, underscores, periods or hyphens, <br> 
    - be followed by @, <br> 
    - then be followed by the 2-4 letter domain. <br>";
  }
  
  else if (!($email_confirm)) {
    $err_msg = "Enter the same email in Confirm New Email.<br>";
  }
  else if (!($email_confirm == $email_new)) {   
    $err_msg = "New Email and Confirm New Email must be typed exactly the same.<br>";
  }
  else if (!$err_msg) {  // VALIDATE domain.
    // Strip out everything BUT the domain from the email.
    $domain_only = preg_replace('/^[a-zA-Z0-9][a-zA-Z0-9\._\-&!?=#]*@/', '', $email_new);
      
    if (!checkdnsrr($domain_only)) {  // Check if $domain is registered. 
      $err_msg = "Your email domain is not valid.<br>";
    }
  }
  return $err_msg;
}

function validate_password($password_new, $password_confirm) {
  $err_msg = "";

  if ($password_new == '' ) {
     $err_msg = "Enter a password in New Password.<br>";
  }
  else if ($password_new != '') {
    if ((strlen($password_new) < 8) || (strlen($password_new) > 16)) { // Check Password Strength.        
      $err_msg = "New Password must have 8 to 16 characters.<br>";
    }
    else if (!preg_match("/[a-z].*[a-z]/", $password_new)) {
      $err_msg = "New Password must have at least 2 lowercase letters.<br>";
    }
    else if (!preg_match("/[A-Z].*[A-Z]/", $password_new)) {
      $err_msg = "New Password must have at least 2 UPPERCASE letters.<br>";
    }
    else if (!preg_match("/[0-9].*[0-9]/", $password_new)) {  
      $err_msg = "New Password must have at least 2 numbers.<br>";
    }
    else if (!preg_match("/[^a-zA-Z0-9\s].*[^a-zA-Z0-9\s]/", $password_new)) {
    //  Note:  This is outside the charset so also add:  \s
      $err_msg = "New Password must have at least 2 special characters: !@#$&%* <br>";
    }
  }   
  if ($password_confirm == '') {
    $err_msg = "Enter a-- password in Confirm Password.<br>";
  }
  else if ($password_confirm != '') {
    if ((strlen($password_confirm) < 8) || (strlen($password_confirm) > 16)) { // Check Password Strength.        
      $err_msg = "Confirm Password must have 8 to 16 characters.<br>";
    }
    else if (!preg_match("/[a-z].*[a-z]/", $password_confirm)) {
      $err_msg = "Confirm Password must have at least 2 lowercase letters.<br>";
    }
    else if (!preg_match("/[A-Z].*[A-Z]/", $password_confirm)) {
      $err_msg = "Confirm Password must have at least 2 UPPERCASE letters.<br>";
    }
    else if (!preg_match("/[0-9].*[0-9]/", $password_confirm)) {  
      $err_msg = "Confirm Password must have at least 2 numbers.<br>";
    }
    else if (!preg_match("/[^a-zA-Z0-9\s].*[^a-zA-Z0-9\s]/", $password_confirm)) {
    //  Note:  This is outside the charset so also add:  \s
      $err_msg = "Confirm Password must have at least 2 special characters: !@#$&%* <br>";
    }
  }
  if ($password_confirm != $password_new) {   
    $err_msg = "New Password and Confirm Password must be typed exactly the same.<br>";
  }
  return $err_msg;
}

function missed_radio_btn($can_do_something, $can_do_string) {
  $err_msg = "";
 
  if (!($can_do_something)) {   // Check: If radio button field is filled in.
    $err_msg = "Click Yes or No to whether this user is allowed to " . $can_do_string;
  }
  return $err_msg;
}
/*------------------------------ End of User Data Validation Functions -------------------------------*/

// CHECK $input vs. $good_input regex for correct input format.
function valid_input($good_input, $input) {
// else if (preg_match("/$bad_product_name/i", $product_name))
  if (preg_match("/$good_input/", $input)) {
    return true;
  }
  else {
    return false;
  }
}

/***  Add Artwork FUNCTIONS  ***/
function validate_art_title($art_title) {
  $err_msg = "";
  $good_title = "^[A-Za-z0-9\'\-\,\s]+$"; 
  // Need: ^ at front and $ at back but no need for / and / since they are in valid_input().
  
  if ((strlen($art_title) < 1) || (strlen($art_title) > 75)) {
    $err_msg = "Title must have at least 1 character and be 75 characters or less.<br>";
  }
  else if (!(valid_input($good_title, $art_title))) {
    $err_msg = "Title can contain ONLY letters, numbers, apostrophes, hyphens or spaces.<br>";
  }
  return $err_msg;
}

function validate_art_year($art_year) {
  $err_msg = "";
  $good_year = "^[0-9]{4}$";
  
  if (strlen($art_year) != 4) {
    $err_msg = "Year must be 4 digits long.<br>";
  }
  else if (!(valid_input($good_year, $art_year))) {
    $err_msg = "Year can contain ONLY numbers.<br>";
  }
  return $err_msg;
}

// NOTE: $art_medium has NO validation since a drop-down selection menu.

function validate_art_height($art_height) {
  $err_msg = "";
  $good_dimension = "^[0-9]+\.(00|25|50|75)$";
  // [0-9]+(\.[0-9]+)[0,1] maybe?  OR  [0-9]+(\.[0-9]{1,2}){0,1}
  // ^ (circumflex or caret) outside square brackets means look only at the beginning of the target string
  // + means 1 or more times. \d+ is 1 or more digits.
  
  if ($art_height) {
    if (!(valid_input($good_dimension, $art_height))) {
      $err_msg = "Height MUST have ONLY numbers and end in .00 .25 .50 or .75<br>";
    }
    return $err_msg;    
  }
}

function validate_art_width($art_width) {   
  $err_msg = "";
  $good_dimension = "^[0-9]+\.(00|25|50|75)$";

  if ($art_width) {
    if (!(valid_input($good_dimension, $art_width))) {
      $err_msg = "Width MUST have ONLY numbers and end in .00 .25 .50 or .75<br>";
    }
    return $err_msg;
  }
}

function validate_art_price($art_price) {
  $err_msg = "";
  $good_price = "^[0-9]+(0|5)$";  //'.*5$' matches a string end with 5
  
  if ($art_price) {
    if (!(valid_input($good_price, $art_price))) {
      $err_msg = "Price MUST have ONLY numbers that end in 0 or 5.<br>";
    }
    return $err_msg;
  }
}

// NOTE: $art_image CANNOT be validated.
// NOTE: $art_medium has NO validation since a drop-down selection menu.
// NOTE: $show_on_page has NO validation since a drop-down selection menu.

function validate_textarea($text) {
  $err_msg = "";
  if ($text) {
    if (strlen($text) > 100) {
      $err_msg = "Text must be 100 characters or less.<br>";
    }
    return $err_msg;
  }
}

function validate_comments($comments) {
  $err_msg = "";
  //$good_comments = "^[a-zA-Z0-9\-\'\s\.]+$"; //ONLY letters, numbers, hyphens, apostrophes or periods

  if ($comments) {
    if (strlen($comments) > 100) {
      $err_msg = "Comments must be 100 characters or less.<br>";
    }
    return $err_msg;
  }
}

// VALIDATE uploaded image files for image file size and file type.
function validate_image_type_err_size ($upload_image_error, $upload_image_type, $upload_image_size) {
  // Value: 0; UPLOAD_ERR_OK - There is no error, the file uploaded with success.
  // Value: 4; UPLOAD_ERR_NO_FILE - No file was uploaded (since there is no file to upload).
  // Value: 2; The file is bigger than the MAX_FILE_SIZE set in the HTML form.
  
  if ($upload_image_error == 4) {
    return "";
  }
  
  if (! (($upload_image_error == 0) || ($upload_image_error == 4))) {
  // Could have diff error msgs for diff err nos. Case-Switch?
  // OR might be function for this in PHP already.
    if ($upload_image_error == 2) {
      return "The file is too large and must be under " .(KLA_MAXFILESIZE/1024). " KB.";
    }
  }
  
  if (($upload_image_type != "image/jpg") && ($upload_image_type != "image/jpeg") && ($upload_image_type != "image/pjpeg") 
     && ($upload_image_type != "image/png") && ($upload_image_type != "image/gif")) {
    return "The uploaded image type must be a JPG, PNG or GIF.";
  }
  if ($upload_image_size > KLA_MAXFILESIZE) {
  // Allow upload of files under 1024000 bytes/1024 = 1000 KB (1 MB).
    return "The uploaded image size must be less than " . (KLA_MAXFILESIZE/1024) . " KB.";
  }
  return "";
}
