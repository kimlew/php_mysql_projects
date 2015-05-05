<?php
  /*  Kim Lew ART - Internal - Search User  */
  //  Only users with appropriate permissions (set conditions) can search user info. 

  $page_title = "Search User";
  include("kimart_internal_header.inc.php");
  require_once('kimart_connect_vars.inc.php');
  include('kimart_internal_common_functions.inc.php');
  include('kimart_internal_validation.inc.php');
  
  $tech_diff_msg = "There are technical difficulties.";
  $logged_in_user_id = "";
  $logged_in_user = "";
  $err_msg = "";
  $msg_to_user = "";
  
  $user_name = $can_delete_work = $can_add_user = $can_delete_user = $can_delete_user = '';
  $search_term = '';
  $can_add_user_by_logged_in_user = '';

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
  $command = "SELECT can_add_user, can_edit_user, can_delete_user
    FROM kimart_internal_login_tb WHERE user_id='$logged_in_user_id'";
  $result = mysqlQueryResult($command, $dbc);
  hasMysqlError($dbc);
  
  if ($result->num_rows > 0) {
    //  $result has something. Retrieve and display assoc. data.    
    $data = $result->fetch_object();
    $can_add_user_by_logged_in_user = $data->can_add_user;
  }  

  if (isset($_POST['search_term'])) {
    $search_term = mysqli_real_escape_string($dbc, trim($_POST['search_term']));
  }

?>

<form enctype="multipart/form-data" method="POST" action="kimart_internal_search_user.php">
 <div id="main">
  <p id = "instruction"> <?php echo $logged_in_user; ?> </p>
  <p class = "err_msg"> <?php echo displayErrMsg($err_msg); ?> </p>
	<p class = "msg_to_user"> <?php echo displayMsg($msg_to_user); ?> </p>
 
  <div class = "two_columns">
  
    <div class = "column1">
      <p><input type="checkbox" name="deleted_users" value="deleted_users_checked" 
        <?php if (isset($_POST['deleted_users']) == "deleted_users_checked") echo "checked='checked'" ?>>Deleted Users Only</p>
      
      <p class = "search_field"> 
        <input type="search" name="search_term" value="<?php if (isset($_POST['search_term'])) echo $search_term; ?>">     
        <input type="submit" name="search" value="Search" title="Search"><br>
      </p>
     
      
    <?php 
      if ($can_add_user_by_logged_in_user == 'Yes') { ?>
        <br><a href="kimart_internal_add_user.php"> Add User </a>
      
    <?php 
      }
    ?>     
      <br><a href="kimart_internal_search_art.php"> Search Artwork </a>
    </div> <!-- Close tag for: div class column1 -->
    
      <?php
        if (isset($_POST['search'])) {
          if ((isset($_POST['deleted_users'])) && (isset($_POST['search_term']))) {       
            $command = "SELECT user_id, user_name FROM kimart_internal_login_tb 
              WHERE date_left > 0
              AND
              (LOWER(user_name) LIKE LOWER('%$search_term%'))
              ORDER BY user_name ASC;";    
          }
          else if (isset($_POST['deleted_users']) && (!isset($_POST['search_term']))) {
            $command = "SELECT user_id, user_name FROM kimart_internal_login_tb
              WHERE date_left > 0
              ORDER BY user_name ASC;";
          }
          else if (isset($_POST['search_term'])) {
            $command = "SELECT user_id, user_name FROM kimart_internal_login_tb
              WHERE date_left = 0
              AND
              (LOWER(user_name) LIKE LOWER('%$search_term%'))
              ORDER BY user_name ASC;";
          }
          else { // Show all.
           $command = "SELECT user_id, user_name FROM kimart_internal_login_tb
              WHERE date_left = 0
              ORDER BY user_name ASC;";
          } 
          $hasMysqlError = hasMysqlError($dbc);
          $result = mysqlQueryResult($command, $dbc);
         ?>
        
    <div class = "column2">      
      <p class = "links">
      <?php 
          if ($result->num_rows <= 0) {
            echo "<br><br>No user found. <br>";
          }       
          else { //if ($result->num_rows > 0)
            while ($row = mysqli_fetch_assoc($result)) {
              // Pass user_id in URL with link to kimart_internal_view_user.php.
              $user_id = $row['user_id'];
              echo "<br><a href=\"kimart_internal_view_user.php?user_id=" . $user_id . "\">";
              echo $row['user_name'];
              echo "</a>";
            }
          }
        }
      ?>
      </p>
    </div><!-- Close tag for: div class="column2"  -->  
  </div><!-- Close tag for: div class="two_columns"  -->
 </div><!-- Close tag for: div id="main"  -->
</form>
  
<?php
  include "kimart_footer.inc.php";
  $dbc->close();
?>
