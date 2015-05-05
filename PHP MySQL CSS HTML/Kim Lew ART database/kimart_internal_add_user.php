<?php
  /*  Kim Lew ART - Internal - Add User  */
  //  Only users with appropriate permissions (via conditions) can:  
  //  delete work, add user data, edit user data or delete user data.
  //  Rquires user to enter EVERY field.

  $page_title = "Add User";
  include("kimart_internal_header.inc.php");
  require_once('kimart_connect_vars.inc.php');
  include('kimart_internal_common_functions.inc.php');
  include('kimart_internal_validation.inc.php');
  
  $tech_diff_msg = "There are technical difficulties.";
  $instruct_start = "* Required fields";
  $err_msg = "";
  $msg_to_user = "";
  $err_msg_array = array();
   
  $user_name = $password = $password_confirm = $email = $email_confirm = ''; 
  $can_delete_work = $can_add_user = $can_edit_user = $can_delete_user = '';
  
  $password_requirements = "A password must have: <br>
                - 8 to 16 characters and start with a letter or number, <br>
                - 2 lowercase letters, <br> 
                - 2 UPPERCASE letters, <br> 
                - 2 numbers and <br> 
                - 2 special characters.<br>";
  
  session_start();
  // Check to see if user is logged in.
  // If no login_id, redirect the user to the Login page.
  if (!isset($_SESSION['user_id'])) {
    header("Location: kimart_internal_login.php");
  }
  else if (isset($_SESSION['user_id'])) {
    $logged_in_user = $_SESSION['user_name'] . " is logged in.";
  }
  $dbc = new mysqli(DB_HOST, DB_USER, DB_PW, DB_NAME) or die("Cannot connect to MySQL.");
  
  if (isset($_POST['submit'])) {
    // Check POST for field entries. If some fields entered, assign to variable names.
    // If not all fields filled in, give the user successive messages to fill in the rest
    // interspersed with validating fields.
    
    if (isset($_POST['user_name'])) {
      $user_name = sanitize_input($dbc, $_POST['user_name']);
    }
    if (isset($_POST['email'])) {
      $email = sanitize_input($dbc, $_POST['email']);
    }
    if (isset($_POST['email_confirm'])) {
      $email_confirm = sanitize_input($dbc, $_POST['email_confirm']);
    }
    if (isset($_POST['password'])) {
      $password = sanitize_input($dbc, $_POST['password']);
    }
    
    if (isset($_POST['password_confirm'])) {
      $password_confirm = sanitize_input($dbc, $_POST['password_confirm']);
    }
    if (isset($_POST['can_delete_work'])) {
      $can_delete_work = sanitize_input($dbc, $_POST['can_delete_work']);
    }
    if (isset($_POST['can_add_user'])) {
      $can_add_user = sanitize_input($dbc, $_POST['can_add_user']);
    }
    if (isset($_POST['can_edit_user'])) {
      $can_edit_user = sanitize_input($dbc, $_POST['can_edit_user']);
    }
    if (isset($_POST['can_delete_user'])) {
      $can_delete_user = sanitize_input($dbc, $_POST['can_delete_user']);
    }
    $fields_all_blank = (
      $user_name == '' && $email == '' && $email_confirm == '' && 
      $password == '' && $password_confirm == '' && $can_delete_work == '' && 
      $can_add_user == '' && $can_edit_user == '' && $can_delete_user == ''
    );
            
    if ($fields_all_blank)  {
      $msg_to_user = "There is no new data to submit."; 
    }
    else if (!($fields_all_blank)) { // There is new data in fields.
      // CHECK the database for already existing user_id based on entered user name or email.
      // If there is a user_id assoc. to the name or email, then give the user an error message
      // that it is already taken. Repeat check of name and email until it is certain there is 
      // NEW data (no match in table for either user_name or email).
      if ($user_name == '') {
        $err_msg = "Enter a name in User Name.<br>";
        addToErrMsgArray($err_msg, $err_msg_array);
      }
      if ($user_name != '') {
        $err_msg = validate_user_name($user_name);
        addToErrMsgArray($err_msg, $err_msg_array);
      }
      if ($email == '') {
        $err_msg = "Enter an email in Email.<br>";
        addToErrMsgArray($err_msg, $err_msg_array);
      }
      if ($email != '') {
        $err_msg = validate_user_name($user_name);
        addToErrMsgArray($err_msg, $err_msg_array);
      } 
      $command = "SELECT user_id, user_name, email FROM kimart_internal_login_tb 
        WHERE user_name='$user_name' OR email='$email'";     
      $result = mysqlQueryResult($command, $dbc);
      hasMysqlError($dbc);
            
      $data = $result->fetch_object();        
   
      if (!empty($data)) {
      // If $data has something, there is an existing user in table with name or email.
      // Give user specific message which, user_name or email, is already taken.         
        if ($user_name == $data->user_name) {
            $err_msg = "This user name already exists. Enter a different name.";
            addToErrMsgArray($err_msg, $err_msg_array);
            
        }         
        if ($email == $data->email) {
            $err_msg = "This email already exists. Enter a different email.";
            addToErrMsgArray($err_msg, $err_msg_array);
        }
      }

      if (empty($data)) {
      // This is a new name and email. Validate all fields.
        if ($user_name == '') {
          $err_msg = validate_user_name($user_name);
          addToErrMsgArray($err_msg, $err_msg_array);
        }
        if ($email == '' || $email_confirm == '') {
          $msg_to_user = "Email must: <br> 
          - start with a letter, number, underscore, period or hyphen, <br>
          - be followed by any number of letters, numbers, underscores, periods or hyphens, <br> 
          - be followed by @, <br> 
          - then be followed by the 2-4 letter domain. <br>";
        }
        if (($email && $email_confirm) || ($email == '' && $email_confirm) || ($email && $email_confirm == '')) {
          $err_msg = validate_email_in_add_user($email, $email_confirm);
          addToErrMsgArray($err_msg, $err_msg_array);
        } 
        if (!$err_msg_array) {
          if ((empty($password)) || (empty($password_confirm))) {
            if (empty($password)) {
              $msg_to_user = $password_requirements;
              $err_msg = "Enter a password in Password.";
              addToErrMsgArray($err_msg, $err_msg_array);
            }
            if (empty($password_confirm)) {
              $msg_to_user = $password_requirements;
              $err_msg = "Enter a password in Confirm Password.";
              addToErrMsgArray($err_msg, $err_msg_array); 
            }
          }
          if ($password && $password_confirm) {
            $err_msg = validate_password_in_add_user($password, $password_confirm);
            addToErrMsgArray($err_msg, $err_msg_array); 
          }
        }
        if (!$err_msg_array) {
            $err_msg = missed_radio_btn($can_delete_work, "delete work.");
            addToErrMsgArray($err_msg, $err_msg_array);
          }
        if (!$err_msg_array) {
          $err_msg = missed_radio_btn($can_add_user, "add a new user.");
          addToErrMsgArray($err_msg, $err_msg_array);
        }
        if (!$err_msg_array) {
          $err_msg = missed_radio_btn($can_edit_user, "edit a user.");
          addToErrMsgArray($err_msg, $err_msg_array);
        }
        if (!$err_msg_array) {
          $err_msg = missed_radio_btn($can_delete_user, "delete a user.");
          addToErrMsgArray($err_msg, $err_msg_array);
        }
        
        // After all field VALIDATION is done, perform transaction.
        if (!$err_msg_array) {
          // Need to hash. Use password_hash(), a strong one-way hashing algorithm.
          // It adds randomly-generated salt to plain text password then hashes entire result.
          $transaction_success = TRUE; // Flag to determine transaction success.
          $hashed_saltpw_for_db = password_hash($password, PASSWORD_BCRYPT);

          // This is a new user. All checks have passed. Proceed with adding user.
          // Start transaction.
          $command = "SET AUTOCOMMIT=0";
          $result = mysqlQueryResult($command, $dbc);
          hasMysqlError($dbc);
            
          $command = "BEGIN";
          $result = mysqlQueryResult($command, $dbc);
          hasMysqlError($dbc);
     
          $command = "INSERT INTO kimart_internal_login_tb 
            (user_id, user_name, password, email, can_delete_work, can_add_user, 
            can_edit_user, can_delete_user, date_added, date_left) 
            VALUES 
            ('', '$user_name', '$hashed_saltpw_for_db', '$email', '$can_delete_work', '$can_add_user', 
            '$can_edit_user', '$can_delete_user', now(), '');";
          $result = mysqlQueryResult($command, $dbc);
          hasMysqlError($dbc);
          
          if ($result == FALSE) {
            $transaction_success = FALSE;
          }    
          if ($transaction_success == TRUE) {
          // $transaction_success initialized to TRUE so INSERT INTO went thru OK. 
          // Continue with COMMIT to add user.
            $command = "COMMIT";
            $result = $dbc->query($command);
            $msg_to_user = "The new user has been added.";
          }      
          else { 
          // $transaction_success = FALSE so there is a problem with the INSERT INTO.
          // Do NOT continue with adding the user data to table.
            $command = "ROLLBACK";
            $result = mysqlQueryResult($command, $dbc);
            hasMysqlError($dbc);
            $msg_to_user = "The data could not be added.";
          }
          $command = "SET AUTOCOMMIT=1"; // Return to autocommit mode since transaction done.
          $result = $dbc->query($command);
        } // Close tag for: if (!($err_msg_array))
      } // Close tag for:   if (empty($data))

    } //   Close tag for:   else { // There is new data in fields.
  }  // Close tag for: (isset($_POST['submit']))
?>

<form method="POST" action="kimart_internal_add_user.php">
 <div id="main">
  <p id = "instruction"> <?php echo $instruct_start; ?> </p>
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
  
  <?php generateDiv('User Name: '); ?>
      <input type="text" size="16" maxlength="16" name="user_name" value="<?php echo $user_name; ?>"> *
    </div>
  </div>

  <?php generateDiv('Email: '); ?>
      <input type="email" size="40" maxlength="40" name="email" value="<?php echo htmlentities($email); ?>"> *
    </div>
  </div> 

  <?php generateDiv('Confirm Email: '); ?>
      <input type="email" size="40" maxlength="40" name="email_confirm" value="<?php echo htmlentities($email_confirm); ?>"> *
    </div>
  </div>
  
  <?php generateDiv('Password: '); ?>
      <input type="password" size="20" maxlength="20" name="password" value="<?php echo htmlentities($password); ?>"> *
    </div>
  </div> 

  <?php generateDiv('Confirm Password: '); ?>
      <input type="password" size="20" maxlength="20" name="password_confirm" value="<?php echo htmlentities($password_confirm); ?>"> *
    </div>
  </div>
  <br>
  
  <!--  Radio Buttons in Form  -->
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
      >No *
    </div>
  </div>
 
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
      >No *
    </div>
  </div>
  
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
      >No *
    </div>
  </div>
  
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
      >No *
    </div>
  </div>
  
  <?php generateDiv(''); ?>
      <input type="submit" name="submit" value="SUBMIT">
    </div>
  </div>
  
 </div><!-- Close tag for: div id="main"  -->
</form>

  <br>
  <a href="kimart_internal_search_art.php"> Search Artwork </a>
  <a href="kimart_internal_add_art.php"> Add Artwork </a>
  <a href="kimart_internal_search_user.php"> Search User </a>
  <a href="kimart_internal_add_user.php"> Add User </a>
  
<?php
  include "kimart_footer.inc.php";
  $dbc->close();
?>
