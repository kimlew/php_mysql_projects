<?php    
  /*  Kim Lew ART - Delete User 
      Checks that logged_in_user_id has rights to removed a user.
      Then removes user by adding the now() date to date_discont column.
      This keeps record in table.
  */
  $page_title = "Delete User";
  include("kimart_internal_header.inc.php");
  require_once('kimart_connect_vars.inc.php');
  include('kimart_internal_common_functions.inc.php');
  include('kimart_internal_validation.inc.php');
  
  $tech_diff_msg = "There are technical difficulties. Please contact us.";
  $msg_to_user = "";
  $err_msg = "";
  $err_msg_array = array();
  
  $user_id = '';
  if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
  }
  
  $logged_in_user_id = $logged_in_user = '';
  $user_name = $email = $can_delete_work = $can_add_user = $can_edit_user = $can_delete_user = '';
  $date_added = $date_left = $reason = '';

  $can_add_work_by_logged_in_user = $can_add_user_by_logged_in_user = '';
  $can_edit_user_by_logged_in_user = $can_delete_user_by_logged_in_user = '';  
    
  // Check to see if user is logged in.
  session_start();
  // If no login_id, redirect the user to the Login page.
  
  if (!isset($_SESSION['user_id'])) {
    header("Location: kimart_internal_login.php");
  }
  else if (isset($_SESSION['user_id'])) {
    $logged_in_user_id = $_SESSION['user_id'];
    $logged_in_user = $_SESSION['user_name'] . " is logged in.";
  }

  $dbc = new mysqli(DB_HOST, DB_USER, DB_PW, DB_NAME) or die("Cannot connect to MySQL.");
  
  // Determine $can_edit_user_by_logged_in_user from the database using logged_in_user_id. 
  $command = "SELECT can_add_user, can_edit_user FROM kimart_internal_login_tb WHERE user_id='$logged_in_user_id'";
  $result = mysqlQueryResult($command, $dbc);
  hasMysqlError($dbc);
  
  if ($result->num_rows > 0) {
    //  $result has something. Retrieve and display assoc. data.    
    $data = $result->fetch_object();
    $can_add_user_by_logged_in_user = $data->can_add_user;
    $can_edit_user_by_logged_in_user = $data->can_edit_user;
  }
  
  // CHECK the database for user_id data. 
  $command = "SELECT * FROM kimart_internal_login_tb WHERE user_id='$user_id'"; // AND date_discont = 0
  $hasMysqlError = hasMysqlError($dbc);
  $result = mysqlQueryResult($command, $dbc);
 
  if ($result->num_rows > 0) { 
  // MATCH exists. $result has something. Retrieve and display assoc. data.    
    $data = $result->fetch_object();
    $user_name = $data->user_name;
    $email = $data->email;
    
    $can_delete_work = $data->can_delete_work;
    $can_add_user = $data->can_add_user;
    $can_edit_user = $data->can_edit_user;
    $can_delete_user = $data->can_delete_user;
    
    $date_added = $data->date_added;
    $date_left = $data->date_left;
  }    

  if (isset($_POST['submit'])) {
    $reason = sanitize_input($dbc, $_POST['reason']);   
    if (empty($reason)) {
      $err_msg = "Enter a reason for the deletion.";
    }
    else { //if ($reason) {        
      $err_msg = validate_textarea($reason);
         
      if (!$err_msg) { // If NO $err_msg.               
      // UPDATE kimart_tb with date_discont = now() and resson column in kimart_tb.
        $command = "UPDATE kimart_internal_login_tb SET date_discont=now(), reason='$reason' WHERE user_id = $user_id";
        $result = mysqlQueryResult($command, $dbc);
        $hasMysqlError = hasMysqlError($dbc);

        if ($hasMysqlError == TRUE) { // There IS a db error.
          $err_msg = $tech_diff_msg;
        }
        else if ($hasMysqlError == FALSE) {  // There is NO db error.
          $msg_to_user = "The work has been deleted. ";
        }
      }
    }
  }  // if (isset($_POST['submit']))

?>

<form enctype="multipart/form-data" method="POST" action="kimart_internal_delete_work.php?user_id=<?php echo $user_id; ?>">
 <div id="main">
  <p id = "instruction"> <?php echo $logged_in_user; ?> </p>
  <p class = "msg_to_user"> <?php echo displayMsg($msg_to_user); ?> </p>
  <p class = "err_msg"> 
  <?php
   if (count($err_msg_array) > 0) {
   // $err_msg_array appends to end but only need to display the one at [0].
     $err_msg = $err_msg_array[0];
     echo displayErrMsg($err_msg);
   }
  ?></p>
    
  <?php generateDiv('Name: '); ?>
      <?php echo htmlentities($user_name); ?>
    </div>
  </div>
  
  <?php generateDiv('Password: '); ?>
      <?php echo "*******"; ?>
    </div>
  </div>
  
  <?php generateDiv('Email: '); ?>
      <?php echo htmlentities($email); ?>
    </div>
  </div>
  
  <br>
  <?php generateDiv('Can Delete Work: '); ?>
      <?php echo htmlentities($can_delete_work); ?>
    </div>
  </div>
  
  <?php generateDiv('Can Add User: '); ?>
      <?php echo htmlentities($can_add_user); ?>
    </div>
  </div>
  
  <?php generateDiv('Can Edit User: '); ?>
      <?php echo htmlentities($can_edit_user); ?>
    </div>
  </div>

  <?php generateDiv('Can Delete User: '); ?>
      <?php echo htmlentities($can_delete_user); ?>
    </div>
  </div>
  <br>
  
  <?php generateDiv('Date Added: '); ?>
      <?php echo htmlentities($date_added); ?>
    </div>
  </div>
  
  <?php generateDiv('Date Left: '); ?>
      <?php echo htmlentities($date_left); ?>
    </div>
  </div>

  <?php generateDiv('Reason for Deletion: '); ?>
      <textarea name="reason" cols="60" rows="3"><?php echo $reason; ?></textarea>
    </div>
  </div>

   <?php generateDiv(''); ?>
      <input type="submit" name="submit" value="Delete">
    </div>
  </div>
  <br>  

  <?php
    if ($can_edit_user_by_logged_in_user == 'Yes') { ?>
   <a href="kimart_internal_edit_user.php?user_id=<?php echo $user_id; ?>"> Edit User </a>
  <?php
    }
  ?>
  
  <?php
    if ($can_add_user_by_logged_in_user == 'Yes') { ?> 
      <a href="kimart_internal_add_user.php"> Add User </a>
  <?php 
    }
  ?>
  
  <a href="kimart_internal_search_user.php"> Search User </a>
  <a href="kimart_internal_search_art.php"> Search Art </a>
  <a href="kimart_internal_add_work.php"> Add Artwork </a>
  <br>
 </div><!-- Close tag for div id="main"  -->
</form>

<?php
  include "kimart_footer.inc.php";
  $dbc->close();
  @unlink($_FILES['art_image']['tmp_name']);
  /* Delete the temporary image file that was uploaded from the web server with unlink(). 
  The @ preceding unlink(), or preceding any PHP function, suppresses its error reporting.
  Suppress this error in case there is no temporary image file to upload (upload failed)
  so program does not try to upload non-existant file.
  */
?>
