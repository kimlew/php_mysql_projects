<?php    
  /*  Kim Lew ART - View User Details */
  // user_id was passed in URL with link from kimart_internal_search_user.php.

  $page_title = "View User Details";
  include('kimart_internal_header.inc.php');
  require_once('kimart_connect_vars.inc.php');
  include('kimart_internal_common_functions.inc.php');
  include('kimart_internal_validation.inc.php');
  
  $user_id = $_GET['user_id'];
  $logged_in_user_id = "";
  $logged_in_user = "";
  
  $can_add_user_by_logged_in_user = $can_edit_user_by_logged_in_user = $can_delete_user_by_logged_in_user = '';

  $tech_diff_msg = "There are technical difficulties. Please contact us.";
  $instruct_start = "* Required field. To search for user, enter * field and click Submit.";
  $err_msg = "";
  $msg_to_user = "";

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
  
  // With user_id, CHECK DATABASE for a match.
  $command = "SELECT * FROM kimart_internal_login_tb WHERE user_id='$user_id'";
  $hasMysqlError = hasMysqlError($dbc);
  $result = mysqlQueryResult($command, $dbc);

  if ($result->num_rows <= 0) {
    $msg_to_user = "This is a new user. Click Add User.";
    die();
  }
  
  if ($result->num_rows > 0) {
    //  NO MATCH exists. $result has something. Retrieve and display assoc. data.    
    $data = $result->fetch_object();
    $user_name = $data->user_name;
    $email = $data->email;
    
    $can_delete_work = $data->can_delete_work;
    $can_add_user = $data->can_add_user;
    $can_edit_user = $data->can_edit_user;
    $can_delete_user = $data->can_delete_user;
    
    $date_added = $data->date_added;
    $date_left = $data->date_left; 
  } // Close tag for: if ($result->num_rows > 0)
?>

 <div id="main">
  <p id = "instruction"> <?php echo $logged_in_user; ?> </p>
  <p class = "err_msg"> <?php echo displayErrMsg($err_msg); ?> </p>
	<p class = "msg_to_user"> <?php echo displayMsg($msg_to_user); ?> </p>
  
	<div class = "two_columns">
  
    <div class = "column75">
    <br>
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
    
    <?php
      $command = "SELECT can_add_user, can_edit_user, can_delete_user
        FROM kimart_internal_login_tb WHERE user_id='$logged_in_user_id'";
      $result = mysqlQueryResult($command, $dbc);
      hasMysqlError($dbc);
        
      if ($result->num_rows > 0) {
        $data = $result->fetch_object();
        $can_add_user_by_logged_in_user = $data->can_add_user;
        $can_edit_user_by_logged_in_user = $data->can_edit_user;
        $can_delete_user_by_logged_in_user = $data->can_delete_user;
      }
    ?>
  <br>
  <?php 
    if ($can_edit_user_by_logged_in_user == 'Yes') { ?>
      <a href="kimart_internal_edit_user.php?user_id=<?php echo $user_id; ?>"> Edit User </a>   
  <?php 
    }  
    if ($can_delete_user_by_logged_in_user == 'Yes') { ?> 
      <a href="kimart_internal_delete_user.php?user_id=<?php echo $user_id; ?>"> Delete User </a>
  <?php 
    }  
    if ($can_add_user_by_logged_in_user == 'Yes') { ?>
      <a href="kimart_internal_add_user.php"> Add User </a>   
  <?php 
    } 
  ?>
  <br><br>
  <a href="kimart_internal_search_user.php"> Search User </a>
  <a href="kimart_internal_search_art.php"> Search Artwork </a>
  <a href="kimart_internal_add_work.php"> Add Artwork </a>
      </p>
    </div><!-- Close tag for: div class="column75" -->
 </div><!-- Close tag for: div id="main"  -->

<?php
  include "kimart_footer.inc.php";
  $dbc->close();
?>
