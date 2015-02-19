<?php
  /*  Kim Lew ART - Internal - Add New User  */
  //  Only users with appropriate permissions (set conditions) can add user info. 

  $page_title = "Add New User";
  include("kimart_internal_header.inc.php");
  require_once('kimart_connect_vars.inc.php');
  include('kimart_internal_common_functions.inc.php');
  include('kimart_internal_validation.inc.php');
  
  $tech_diff_msg = "There are technical difficulties.";
  $instruct_start = "* Required fields";
  $err_msg = "";
  $msg_to_user = "";
  
  $user_name = $password1 = $password2 = $email1 = $email2 = $can_delete_work = $can_add_user = '';
  
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
  
  // CHECK if Submit clicked. Only process form inputs if Submit clicked.
  if (isset($_POST['submit'])) {
    $user_name = sanitize_input($dbc, $_POST['user_name']);
    $email1 = sanitize_input($dbc, $_POST['email1']);
    $email2 = sanitize_input($dbc, $_POST['email2']);
    $password1 = sanitize_input($dbc, $_POST['password1']);
    $password2 = sanitize_input($dbc, $_POST['password2']);
    
    if (isset($_POST['can_delete_work'])) {
      $can_delete_work = sanitize_input($dbc, $_POST['can_delete_work']);
    }
    if (isset($_POST['can_add_user'])) {
      $can_add_user = sanitize_input($dbc, $_POST['can_add_user']);
    }

    // VALIDATE login user name, email and domain and password.
    $err_msg = validate_user_name($user_name);
    if (!$err_msg) {
      $err_msg = validate_email($email1, $email2);
    } 
    if (!$err_msg) {
      $err_msg = validate_password($password1, $password2);
    }
    if (!$err_msg) {
      $err_msg = validate_can_delete_work($can_delete_work);
    }
    if (!$err_msg) {
      $err_msg = validate_can_add_user($can_add_user);
    }
   
    // After all field VALIDATION is done, CHECK the database for an existing 
    // login user name and email.
    if (!($err_msg)) {    
      // To create a new user password hash using a strong one-way hashing algorithm, 
      // put the plain text password into password_hash()
      // password_hash() adds randomly-generated salt to password then hashes entire thing.
      $hashed_saltpw_for_db = password_hash($password1, PASSWORD_BCRYPT);

      // CHECK the database for already-taken login user name or email.
      $command_u = "SELECT user_id FROM kimart_internal_login_tb 
        WHERE (user_name='$user_name' OR email='$email1')";
      $result_u = mysqlQueryResult($command_u, $dbc);
      hasMysqlError($dbc);
      
      if ($result_u->num_rows > 0) {
        while ($row = $result_u->fetch_assoc()) {
        //  $data = $result_u->fetch_object();
          if (($row->user_name) == $user_name) {
            $msg_to_user = "This login user name already exists. Enter a different login user name.";
          }
          if (($row->email) == $email1) {
            $msg_to_user = "This email already exists. Enter a different email.";
          }
        }
      }
      else if ($result_u->num_rows <= 0) {
        // All checks have passed. Create the login user.
        $transaction_success = TRUE; // Flag to determine transaction success.
          
        // Start transaction.
        $command = "SET AUTOCOMMIT=0";
        $result = mysqlQueryResult($command, $dbc);
        hasMysqlError($dbc);
          
        $command = "BEGIN";
        $result = mysqlQueryResult($command, $dbc);
        hasMysqlError($dbc);
   
        // Ready for INSERT INTO kimart_internal_login_tb.
        $command = "INSERT INTO kimart_internal_login_tb 
          (user_id, user_name, password, email, can_delete_work, can_add_user, date_added) 
          VALUES 
          ('', '$user_name', '$hashed_saltpw_for_db', '$email1', '$can_delete_work', '$can_add_user', now());";
        $result = mysqlQueryResult($command, $dbc);
        hasMysqlError($dbc);
        
        if ($result == FALSE) {
          $transaction_success = FALSE;
        }
      }
        if ($transaction_success == TRUE) {
        // Was initialized to TRUE. INSERT INTO went thru OK. Continue with COMMIT.
          $command = "COMMIT";
          $result = $dbc->query($command);
        }      
        else { // $transaction_success = FALSE
          $command = "ROLLBACK";
          $result = mysqlQueryResult($command, $dbc);
          hasMysqlError($dbc);
        } 

        $command = "SET AUTOCOMMIT=1"; // Return to autocommit.
        $result = $dbc->query($command);
        $msg_to_user = "The new user has been added.";

    }  // Close tag for: if (!($err_msg))
  }  // Close tag for: (isset($_POST['submit']))
?>

<form method="POST" action="kimart_internal_add_user.php">
 <div id="main">
  <p id = "instruction"> <?php echo $instruct_start; ?> </p>
  <p class = "msg_to_user"> <?php displayMsg($msg_to_user); ?> </p>
  <p class = "err_msg"> <?php displayErrMsg($err_msg); ?> </p>
  
  <?php generateDiv('User Name: '); ?>
      <input type="text" size="16" maxlength="16" name="user_name" value="<?php echo $user_name; ?>"> *
    </div>
  </div>

  <?php generateDiv('Email: '); ?>
      <input type="email" size="40" maxlength="40" name="email1" value="<?php echo htmlentities($email1); ?>"> *
    </div>
  </div> 

  <?php generateDiv('Confirm Email: '); ?>
      <input type="email" size="40" maxlength="40" name="email2" value="<?php echo htmlentities($email2); ?>"> *
    </div>
  </div>
  
  <?php generateDiv('Password: '); ?>
      <input type="password" size="16" maxlength="16" name="password1" value=""> *
    </div>
  </div> 

  <?php generateDiv('Confirm Password: '); ?>
      <input type="password" size="16" maxlength="16" name="password2" value=""> *
    </div>
  </div>
  
  <?php generateDiv('Can Delete Work?: '); 

  ?>
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
  
  <?php generateDiv(''); ?>
      <input type="submit" name="submit" value="SUBMIT">
    </div>
  </div>
  
 </div><!-- Close tag for: div id="main"  -->
</form>

  <br>
  <a href="kimart_internal_search_art.php"> Search for Artwork </a>
  <a href="kimart_internal_add_work.php"> Add New Work </a>
  
<?php
  include "kimart_footer.inc.php";
  $dbc->close();
?>
