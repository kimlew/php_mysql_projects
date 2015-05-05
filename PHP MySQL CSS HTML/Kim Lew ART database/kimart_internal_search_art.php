<?php    
  /*  Kim Lew ART - Internal - Search Art  */
  //  Only users with appropriate permissions can search and edit art data. 
  
  $page_title = "Search Art"; // $page_title must be declared before call of header include file.
  include('kimart_internal_header.inc.php');
  require_once('kimart_connect_vars.inc.php');
  include('kimart_internal_common_functions.inc.php');
  include('kimart_internal_validation.inc.php');
  
  $art_title = $art_year = '';
    
  $tech_diff_msg = "There are technical difficulties. Please contact us.";
  $logged_in_user_id = "";
  $logged_in_user = "";
  $err_msg = "";
  $msg_to_user = "";
  $title_exists = "This is already in the database.";
  $title_new = "This is a new title. Enter other fields and click Search.";

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
  $search_term = '';
  if (isset($_POST['search_term'])) {
    $search_term = mysqli_real_escape_string($dbc, trim($_POST['search_term']));
  }
?>

<form enctype="multipart/form-data" method="POST" action="kimart_internal_search_art.php">
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
      <br>
      <br><a href="kimart_internal_add_art.php"> Add Art </a>
      <br><a href="kimart_internal_search_user.php"> Search User </a>
    </div><!-- Close tag for: div class="column1" -->
    
    <?php
      if (isset($_POST['search'])) {          
        if ((isset($_POST['deleted_users'])) && (isset($_POST['search_term']))) {       
          $command = "SELECT kimart_id, kimart_title, kimart_year FROM kimart_tb 
            WHERE date_discont > 0
            AND
            (LOWER(kimart_title) LIKE LOWER('%$search_term%') 
            OR kimart_year LIKE ('%$search_term%'))
            ORDER BY kimart_title ASC;";    
        }
        else if (isset($_POST['deleted_users']) && (!isset($_POST['search_term']))) {
          $command = "SELECT kimart_id, kimart_title, kimart_year FROM kimart_tb 
            WHERE date_discont > 0
            ORDER BY kimart_title ASC;";
        }
        else if (isset($_POST['search_term'])) {
          $command = "SELECT kimart_id, kimart_title, kimart_year FROM kimart_tb 
            WHERE date_discont = 0
            AND
            (LOWER(kimart_title) LIKE LOWER('%$search_term%')
            OR kimart_year LIKE ('%$search_term%'))
            ORDER BY kimart_title ASC;";
        }
        else { // Show all.
          $command = "SELECT kimart_id, kimart_title, kimart_year FROM kimart_tb 
            WHERE date_discont = 0
            ORDER BY kimart_title ASC;";
        }
        $hasMysqlError = hasMysqlError($dbc);
        $result = mysqlQueryResult($command, $dbc);
      ?>
    <div class = "column2">      
      <p class = "links">     
      <?php
        if ($result->num_rows <= 0) {
          echo "<br><br>No title found. <br>";
        }       
        else { //if ($result->num_rows > 0)
          while ($row = mysqli_fetch_assoc($result)) {
            // Pass kimart_id in URL with link to kimart_internal_art_details.php.
            $kimart_id = $row['kimart_id'];
            echo "<br><a href=\"kimart_internal_view_art.php?kimart_id=" . $kimart_id . "\">";
            echo $row['kimart_title'] . ", " . $row['kimart_year'];
            echo "</a>";
          }
        }
      }
      ?>
      </p>
    </div> <!-- Close tag for: div class column2 --> 
    
  </div><!-- Close tag for: div class="two_columns"  -->
  
 </div><!-- Close tag for: div id="main"  -->
</form>

<?php
  include "kimart_footer.inc.php";
  $dbc->close();
?>
