<?php
  /*  Kim Lew ART - Internal - Edit User  */
  //  Only users with appropriate permissions can edit user info.
  // Note: Do NOT display real current password. Exposing password can lead to 
  // hacking. Similarly, DO NOT (plus you CANNOT) decrypt password.

  $page_title = "Edit User";
  include("kimart_internal_header.inc.php");
  require_once('kimart_connect_vars.inc.php');
  include('kimart_internal_common_functions.inc.php');
  include('kimart_internal_validation.inc.php');
  
  $tech_diff_msg = 'There are technical difficulties.';
  $instruct_start = '* Required fields';
  $err_msg = '';
  $msg_to_user = '';
  $err_msg_array = array();
  
  $user_id = $_GET['user_id'];
  $logged_in_user_id = $logged_in_user = '';
  
  $can_delete_work_by_logged_in_user = $can_add_user_by_logged_in_user = '';
  $can_edit_user_by_logged_in_user = $can_delete_user_by_logged_in_user = '';
  
  $user_name = $email_current = $email_new = $email_confirm = '';
  $password_current = $password_new = $password_confirm = '';
  $password_display_fake = '********'; // Current password is fake field for security reasons.
  $email_update = $password_new_hashed = $password_update = '';
 
  $can_delete_work = $can_add_user = $can_edit_user = $can_delete_user = '';
  $can_delete_work_new = $can_add_user_new = $can_edit_user_new = $can_delete_user_new = '';

  // Check to see if user is logged in.
  // If no login_id, redirect the user to the Login page.
  session_start();
  if (!isset($_SESSION['user_id'])) {
    header("Location: kimart_internal_login.php");
  }
  else if (isset($_SESSION['user_id'])) {
    $logged_in_user_id = $_SESSION['user_id'];
    $logged_in_user = $_SESSION['user_name'] . " is logged in.";
  }
  
  $dbc = new mysqli(DB_HOST, DB_USER, DB_PW, DB_NAME) or die("Cannot connect to MySQL.");
  
  // Determine rights of logged-in user to see if user can edit other users. Check 
  // database for logged_in_user_id data and assign to $can_edit_user_by_logged_in_user. 
  $command = "SELECT can_delete_work, can_add_user, can_edit_user, can_delete_user
    FROM kimart_internal_login_tb WHERE user_id='$logged_in_user_id'";
  $result = mysqlQueryResult($command, $dbc);
  hasMysqlError($dbc);
  
  if ($result->num_rows > 0) {
  // Retrieve rights of logged_in_user_id.
    $data = $result->fetch_object();
    $can_delete_work_by_logged_in_user = $data->can_delete_work;
    $can_add_user_by_logged_in_user = $data->can_add_user;
    $can_edit_user_by_logged_in_user = $data->can_edit_user;
    $can_delete_user_by_logged_in_user = $data->can_delete_user;
  }
   
  // CHECK the database for user_id data for user being edited. 
  // Retrieve and display current data on web page.
  $userDataArray = getUserData($user_id, $dbc);

  $user_name = $userDataArray[KEY_USER_NAME];
  $email_current = $userDataArray[KEY_EMAIL];
  $password_current = $userDataArray[KEY_PASSWORD];

  $can_delete_work = $userDataArray[KEY_CAN_DELETE_WORK];     
  $can_add_user = $userDataArray[KEY_CAN_ADD_USER];
  $can_edit_user =  $userDataArray[KEY_CAN_EDIT_USER];
  $can_delete_user = $userDataArray[KEY_CAN_DELETE_USER];
    
  $date_added =  $userDataArray[KEY_DATE_ADDED];
  $date_left = $userDataArray[KEY_DATE_LEFT];

  if (isset($_POST['submit'])) {
    // CHECK if Submit clicked. Only process form inputs if Submit clicked.     
    if (isset($_POST['email_new'])) {
      $email_new = sanitize_input($dbc, $_POST['email_new']);
    }
    if (isset($_POST['email_confirm'])) {
      $email_confirm = sanitize_input($dbc, $_POST['email_confirm']);
    }
    if (isset($_POST['password_new'])) {
      $password_new = sanitize_input($dbc, $_POST['password_new']);

    }
    if (isset($_POST['password_confirm'])) {
      $password_confirm = sanitize_input($dbc, $_POST['password_confirm']);
    }
  
    if (isset($_POST['can_delete_work'])) {
      $can_delete_work_new = sanitize_input($dbc, $_POST['can_delete_work']);
    }
    if (isset($_POST['can_add_user'])) {
      $can_add_user_new = sanitize_input($dbc, $_POST['can_add_user']);
    }
    if (isset($_POST['can_edit_user'])) {
      $can_edit_user_new = sanitize_input($dbc, $_POST['can_edit_user']);
    }
    if (isset($_POST['can_delete_user'])) {
      $can_delete_user_new = sanitize_input($dbc, $_POST['can_delete_user']);
    }
    
    // NOTE: Must use password_verify() to compare un-hashed password_new string
    // with hashed password_current which is the 24-char hashed and salted password.    
    $same_pw_new_as_in_table = password_verify($password_new, $password_current);
    $same_pw_confirm_as_in_table = password_verify($password_confirm, $password_current);

    $all_data_the_same = (
      ( ((empty($email_new) && empty($email_confirm))) ||
        (($email_new == $email_confirm) && 
         ($email_current == $email_new)) ) &&
        
      ( (empty($password_new) && empty($password_confirm))  ||
        (($password_new == $password_confirm) && 
         ($same_pw_new_as_in_table == TRUE) &&
         ($same_pw_confirm_as_in_table== TRUE)) ) &&
       
      ($can_delete_work_new == $can_delete_work) &&
      ($can_add_user_new == $can_add_user) &&
      ($can_edit_user_new == $can_edit_user) &&
      ($can_delete_user_new == $can_delete_user));
 
    if ($all_data_the_same) {
     if (($email_new == $email_confirm) && ($email_current == $email_new)) {
       $err_msg = "This email already exists for this user. Enter a different email in New Email.";
       addToErrMsgArray($err_msg, $err_msg_array);
     }
     if (($email_new == $email_confirm) && ($email_current == $email_confirm)) {
       $err_msg = "This email already exists for this user. Enter a different email in Confirm New Email.";
       addToErrMsgArray($err_msg, $err_msg_array);
     }
     if (($password_new == $password_confirm) && ($same_pw_new_as_in_table == TRUE)) {
       $err_msg = "This password already exists for this user. Enter a different password in New Password.";
       addToErrMsgArray($err_msg, $err_msg_array);
     }
     if (($password_new == $password_confirm) && ($same_pw_confirm_as_in_table == TRUE)) {
       $err_msg = "This password already exists for this user. Enter a different password in Confirm Password.";
       addToErrMsgArray($err_msg, $err_msg_array);
     }
      $msg_to_user = "There is no new data to submit.";
    }
    else if (!($all_data_the_same)) { // At LEAST ONE field is DIFF from table values.
      // CHECK the field values against the database values. 
      // If there is an entry in either email field or both, or password field or both, 
      // do checks for correct entries and validation of these fields.
      if ($email_new || $email_confirm || $password_new || $password_confirm) {
        
        if ($email_new || $email_confirm) {     
          // Only validate and process email_new if it is different than email in database.     
          $msg_to_user = "The email must: <br> 
            - start with a letter, number, underscore, period or hyphen, <br>
            - be followed by any number of letters, numbers, underscores, periods or hyphens, <br> 
            - be followed by @, <br> 
            - then be followed by the 2-4 letter domain. <br>";
        
          // If $data has something, there is an existing user in table with name or email.
          // Give user specific message which, user_name or email, is already taken.

          if ($email_new && empty($email_confirm)) {
            $err_msg = "Enter an email in Confirm Email.";
            addToErrMsgArray($err_msg, $err_msg_array);
            echo "msg_to_user is:  <br>" . $msg_to_user . "<br>";  
          }
          else if ($email_new == $email_current) {
            $err_msg = "This email already exists for this user. Enter a different email in New Email.";
          }
          else if (empty($email_new) && $email_confirm) {
            $err_msg = "Enter an email in New Email.";
            addToErrMsgArray($err_msg, $err_msg_array);
          }       
          else if ($email_confirm == $email_current) {
            $err_msg = "This email already exists for this user. Enter a different email in Confirm New Email.";
          }
          else if ($email_new && $email_confirm && !empty($email_current)) {
            $err_msg = validate_email($email_new, $email_confirm);
            addToErrMsgArray($err_msg, $err_msg_array);
          }
        }
        
        // If there is a New Password or Confirm Password, validate. Otherwise do nothing. 
        if ($password_new || $password_confirm) {
          // Do NOT call compare_input_with_db_value() for password since security risk to show password on web page.
          $msg_to_user = "Password must have: <br>
            - 8 to 16 characters and start with a letter or number, <br>
            - 2 lowercase letters, <br> 
            - 2 UPPERCASE letters, <br> 
            - 2 numbers and <br> 
            - 2 special characters.<br>";
            
          if (empty($password_new) && $password_confirm) {
            $err_msg = "Enter a password in New Password.";
            addToErrMsgArray($err_msg, $err_msg_array);
          }
          else if ($same_pw_new_as_in_table == TRUE) {
            $err_msg = "This password already exists for this user. Enter a different password in New Password.";
            addToErrMsgArray($err_msg, $err_msg_array);
          }
          else if ($password_new && empty($password_confirm)) {
            $err_msg = "Enter a password in Confirm Password.";
            addToErrMsgArray($err_msg, $err_msg_array);
          }
          else if ($same_pw_confirm_as_in_table == TRUE) {
            $err_msg = "This password already exists for this user. Enter a different password in Confirm Password.";
            addToErrMsgArray($err_msg, $err_msg_array);
          }
          else if ($password_new && $password_confirm) {
            $err_msg = validate_password($password_new, $password_confirm);
            addToErrMsgArray($err_msg, $err_msg_array);
          }
        }    
      }

      // UPDATE since VALIDATION done and NO matching values in database.
      if ( !($err_msg_array) && 
        (($email_new == $email_confirm) && ($email_new != $email_current)) || 
        (($password_new == $password_confirm) &&  ($same_pw_new_as_in_table == FALSE))  ) {
        // Update all values. Note: They will be the correct ones from the compare function result.
        $transaction_success = TRUE; // Flag to determine success of transaction

        if (($email_current != $email_new) && ($email_current != $email_confirm) &&
          ($email_new == $email_confirm) &&
          (!empty($email_new)) && (!empty($email_confirm))) {
          
          $email_update = $email_new;
        } 
        else { // Update with same email that is in database.
          $email_update = $email_current;
        }
       
        if ( ($same_pw_new_as_in_table == FALSE) &&
          ($password_new == $password_confirm) &&
          (!empty($password_new)) && 
          (!empty($password_confirm)) ) {

          // Compare new password in POST with current password (hashed) from table.
          // If not the same (FALSE), assign &password_update as $password_new.
          // Else if the same (TRUE), assign assign &password_update as $password_current.
          // Then UPDATE database.
 
          if ($same_pw_new_as_in_table == FALSE) {
            // Need to hash. Use password_hash(), a strong one-way hashing algorithm.
            // It adds randomly-generated salt to plain text password then hashes entire result.
            $password_new_hashed = password_hash($password_new, PASSWORD_BCRYPT);
            $password_update = $password_new_hashed;

          }
          else { // if ($same_pw_in_table == TRUE) {
            // Update with same password that is in database since 
            // password_current == password_new == password_confirm
            $password_update = $password_current;
          }
        }

        /* UPDATE - Only if ANY of these conditions are met.
           - If current email is not the same as New Email or Confirm Email AND New Email is same as Confirm Email.
           - If current password is not the same as New Password or Confirm Password AND New Password is same as Confirm Password.
           - If radio button values in POST are diff. than ones in database. 
           - means that value in POST is different than value in database (a radio button changed).
        */
     
        if ( !($err_msg_array) && 
          (($email_update == $email_new) && ($email_new && $email_confirm)) || 
          (($password_update == $password_new_hashed) && ($password_new && $password_confirm)) ||
          ($can_delete_work_new != $can_delete_work) ||
          ($can_add_user_new != $can_add_user) ||
          ($can_edit_user_new != $can_edit_user) ||
          ($can_delete_user_new != $can_delete_user) ) {

          $transaction_success = TRUE; // Flag to determine transaction success.
          
          // Start transaction.
          $command = "SET AUTOCOMMIT=0";
          $result = mysqlQueryResult($command, $dbc);
          hasMysqlError($dbc);

          $command = "BEGIN";
          $result = mysqlQueryResult($command, $dbc);
          hasMysqlError($dbc);

          // UPDATE kimart_tb with ALL new form field values.
          $command = "UPDATE kimart_internal_login_tb SET
            email = '$email_update',
            password = '$password_update',
            can_delete_work = '$can_delete_work_new',
            can_add_user = '$can_add_user_new',
            can_edit_user = '$can_edit_user_new',
            can_delete_user = '$can_delete_user_new'
            WHERE user_id = '$user_id'";
          $result = mysqlQueryResult($command, $dbc);
          hasMysqlError($dbc);
          
          if ($result == FALSE) {
            $transaction_success = FALSE;
          }    
          if ($transaction_success == TRUE) {
          // $transaction_success initialized to TRUE so UPDATE went thru OK. 
          // Continue with COMMIT to add user.
            $command = "COMMIT";
            $result = $dbc->query($command);
            $msg_to_user = "The user data has been changed.";
          }      
          else { 
          // $transaction_success = FALSE so there is a problem with the UPDATE.
          // Do NOT continue with adding the user data to table.
            $command = "ROLLBACK";
            $result = mysqlQueryResult($command, $dbc);
            hasMysqlError($dbc);
            $msg_to_user = "The user data could not be changed.";
          }
          $command = "SET AUTOCOMMIT=1"; // Return to autocommit.
          $result = $dbc->query($command);     

          // CHECK the database for user_id data so web page displays current data. 
          $userDataArray = getUserData($user_id, $dbc);
          $user_name = $userDataArray[KEY_USER_NAME];
          $email_current = $userDataArray[KEY_EMAIL];
          
          $email_new = $email_confirm = '';
          $password_new = $password_confirm = '';
           
          $can_delete_work = $userDataArray[KEY_CAN_DELETE_WORK];
          $can_add_user = $userDataArray[KEY_CAN_ADD_USER];
          $can_edit_user =  $userDataArray[KEY_CAN_EDIT_USER];
          $can_delete_user = $userDataArray[KEY_CAN_DELETE_USER];
          $date_added =  $userDataArray[KEY_DATE_ADDED];
        } // Close tage for: // ONLY UPDATE if ANY of these conditions are met.
            
      }  // Close tag for: if (!($err_msg_array) && (($email_new == $email_confirm)...
      
    } //  Close tag for:  else if (!($all_data_the_same)) // At least 1 field is different than in table.  
  }  // Close tag for: (isset($_POST['submit']))
?>

<form method="POST" action="kimart_internal_edit_user.php?user_id=<?php echo $user_id; ?>">
 <div id="main">
  <p id = "instruction"> <?php echo $logged_in_user; ?> </p>
  <p class = "msg_to_user"> <?php displayMsg($msg_to_user); ?> </p>
  <p class = "err_msg"> 
  <?php
   if (count($err_msg_array) > 0) {
   // $err_msg_array appends to end but only need to display the one at [0].
     $err_msg = $err_msg_array[0];
     echo displayErrMsg($err_msg);
   }
  ?>
  </p>
  
  <?php
  /* EMAIL and PASSWORD fields - Regardless of rights, viewer sees Email and Password fields. */   
    if ( (($user_id == $logged_in_user_id) && ($can_edit_user == 'No'))  ||  ($can_edit_user_by_logged_in_user  == 'Yes') ) {
  ?>
  
  <?php generateDiv('User Name: '); ?>
      <?php echo htmlentities($user_name); ?>
    </div>
  </div>
  <?php generateDiv('Current Email: '); ?>
      <?php echo htmlentities($email_current); ?>
    </div>
  </div>
    
  <?php generateDiv('New Email: '); ?>
      <input type="email" size="40" maxlength="40" name="email_new" value="<?php echo htmlentities($email_new); ?>">
    </div>
  </div> 
  <?php generateDiv('Confirm New Email: '); ?>
      <input type="email" size="40" maxlength="40" name="email_confirm" value="<?php echo htmlentities($email_confirm); ?>">
    </div>
  </div> 
  
  <?php generateDiv('Current Password: '); ?>
      <?php echo $password_display_fake; ?>
    </div>
  </div>
  
  <?php generateDiv('New Password: '); ?>
      <input type="password" size="20" maxlength="20" name="password_new" value="<?php echo htmlentities($password_new); ?>">
    </div>
  </div> 
  <?php generateDiv('Confirm Password: '); ?>
      <input type="password" size="20" maxlength="20" name="password_confirm" value="<?php echo htmlentities($password_confirm); ?>">
    </div>
  </div>
  
  <?php
    } // EMAIL and PASSWORD close parenthesis for if statement.
  ?>
  <br>
  
  <!--  RADIO BUTTONS  -->  
  <?php
 
  // - Not all users see these radio buttons.
  // - If user having data edited is NOT the same as logged in user AND (can_edit_user = 'Yes')
  //   viewer sees all radio buttons. 
       
    if (($user_id != $logged_in_user_id) && ($can_edit_user_by_logged_in_user == 'Yes')) {
  ?>
  
  <!-- RADIO button: Can Delete Work? -->
  <?php generateDiv('Can Delete Work?: '); ?>
      <input type="radio" name="can_delete_work" value="Yes"  
      <?php
        if ($can_delete_work == "Yes") {
          echo 'checked';
        }
      ?>
      >Yes
      <input type="radio" name="can_delete_work" value="No"
      <?php 
        if ($can_delete_work == "No") {
          echo 'checked';
        }   
      ?>
      >No
    </div>
  </div>
  
  <!-- RADIO button: Can Add User? -->
  <?php generateDiv('Can Add User?: '); ?>
      <input type="radio" name="can_add_user" value="Yes"  
      <?php 
        if ($can_add_user == "Yes") {
          echo 'checked';
        }
      ?>
      >Yes  
      <input type="radio" name="can_add_user" value="No"
      <?php 
        if ($can_add_user == "No") {
          echo 'checked';
        }
      ?>
      >No
    </div>
  </div>
  
  <!-- RADIO button: Can Edit User? -->
  <?php generateDiv('Can Edit User?: '); ?>
      <input type="radio" name="can_edit_user" value="Yes"  
      <?php 
        if ($can_edit_user == "Yes") {
          echo 'checked';
        }
      ?>
      >Yes
      <input type="radio" name="can_edit_user" value="No"
      <?php 
        if ($can_edit_user == "No") {
          echo 'checked';
        }
      ?>
      >No
    </div>
  </div>
  
  <!-- RADIO button: Can Delete User? -->
  <?php generateDiv('Can Delete User?: '); ?>
      <input type="radio" name="can_delete_user" value="Yes"  
      <?php 
        if ($can_delete_user == "Yes") {
          echo 'checked';
        }
      ?>
      >Yes     
      <input type="radio" name="can_delete_user" value="No"
      <?php 
        if ($can_delete_user == "No") {
          echo 'checked';
        }
      ?>
      >No
    </div>
  </div>
 
  <?php
    } // RADIO BUTTONS close parenthesis for if statement.
  ?>
  
  <?php generateDiv(''); ?>
      <input type="submit" name="submit" value="SUBMIT">
    </div>
  </div>

 </div><!-- Close tag for: div id="main"  -->
</form>

  <br>
  <a href="kimart_internal_search_user.php"> Search User </a>
  
  <?php 
    if ($can_add_user_by_logged_in_user == 'Yes') { ?>
      <a href="kimart_internal_add_user.php"> Add User </a>
    
  <?php 
    }
    if ($can_delete_user_by_logged_in_user == 'Yes') { ?> 
      <a href="kimart_internal_delete_user.php?user_id=<?php echo $user_id; ?>"> Delete User </a>
  <?php 
    }
  ?>
  
  <a href="kimart_internal_search_art.php"> Search Artwork </a>
  <a href="kimart_internal_add_work.php"> Add Artwork </a>
  
  <?php
    if ($can_delete_work_by_logged_in_user == 'Yes') { ?> 
      <a href="kimart_internal_delete_work.php"> Delete Artwork </a>
  <?php 
    }
  ?>
  
<?php
  include "kimart_footer.inc.php";
  $dbc->close();
?>
