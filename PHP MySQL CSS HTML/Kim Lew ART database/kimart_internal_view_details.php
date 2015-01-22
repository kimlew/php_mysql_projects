<?php    
  /*  Kim Lew ART - View Art  */
  // kimart_id was passed in URL with link to kimart_internal_view_details.php.

  $page_title = "View Details";
  include("kimart_internal_header.inc.php");
  require_once('kimart_connect_vars.inc.php');
  include('kimart_internal_common_functions.inc.php');
  include('kimart_internal_validation.inc.php');
  
  $kimart_id = $_GET['kimart_id'];
  
  $tech_diff_msg = "There are technical difficulties. Please contact us.";
  $instruct_start = "* Required field. To search for artwork, enter * fields and click Submit.";
  $logged_in_user = "";
  $err_msg = "";
  $msg_to_user = "";

  // Check to see if user is logged in.
  session_start();
  // If no login_id, redirect the user to the Login page.
  if (!isset($_SESSION['user_id'])) {
    header("Location: kimart_internal_login.php");
  }
  else if (isset($_SESSION['user_id'])) {
    $logged_in_user = $_SESSION['user_name'] . " is logged in.";
  }

  $dbc = new mysqli(DB_HOST, DB_USER, DB_PW, DB_NAME) or die("Cannot connect to MySQL.");
  
  // With kimart_id, CHECK DATABASE for a match.
  $command = "SELECT * FROM kimart_tb WHERE kimart_id='$kimart_id'";
  $hasMysqlError = hasMysqlError($dbc);
  $result = mysqlQueryResult($command, $dbc);

  if ($result->num_rows <= 0) {
    $msg_to_user = "This is a new title. Click Add New Work.";
    die();
  }
  
  if ($result->num_rows > 0) {
    //  NO MATCH exists. $result has something. Retrieve and display assoc. data.    
    $data = $result->fetch_object();

    $art_title = $data->kimart_title;
    $art_year = $data->kimart_year;
    $art_medium = $data->kimart_medium;
    
    $art_height = $data->kimart_height;
    $art_width = $data->kimart_width;
    
    $art_image = $data->kimart_image;
    $show_on_page = $data->show_on_page;
    $comments = $data->comments;
      
    // Retrieve $art_price from kimart_purch_tb using $kimart_id.
    $command = "SELECT price_listed FROM kimart_purch_tb WHERE kimart_id='$kimart_id'";
    $hasMysqlError = hasMysqlError($dbc);
    $result = mysqlQueryResult($command, $dbc);
    
    $art_price = '';
    // For SELECT command
    if ($result->num_rows > 0) {
      $data = $result->fetch_object();
      $art_price = $data->price_listed;
    }    
  } // Close tag for: if ($result->num_rows > 0)
?>

 <div id="main">
  <p id = "instruction"> <?php echo $logged_in_user; ?> </p>
  <p class = "err_msg"> <?php echo displayErrMsg($err_msg); ?> </p>
	<p class = "msg_to_user"> <?php echo displayMsg($msg_to_user); ?> </p>
  
  <?php  generateDiv('Title: '); ?>
      <?php echo $art_title; ?>
    </div>
  </div>
  
  <?php  generateDiv('Year: '); ?>
      <?php echo $art_year; ?>
    </div>
  </div>
  
  <?php  generateDiv('Medium: '); ?>
      <?php echo $art_medium; ?>
    </div>
  </div>
 
  <?php  generateDiv('Height (inches): '); ?>
      <?php echo $art_height; ?>
    </div>
  </div>
  
  <?php  generateDiv('Width (inches): '); ?>
      <?php echo $art_width; ?>
    </div>
  </div>
  
  <?php  generateDiv('Price (USD): '); ?>
      <?php echo $art_price; ?>
    </div>
  </div>
  
  <?php  generateDiv('Image: '); ?>
      <?php echo $art_image; ?>
    </div>
  </div>
  
  <?php  generateDiv('Show on Page: '); ?>
      <?php echo $show_on_page; ?>
    </div>
  </div>
  
  <?php  generateDiv('Comments: '); ?>
      <?php echo $comments; ?>
    </div>
  </div> 
  
  <?php
  // Display Date Deleted and Reason ONLY if they are not 0 or '' in table.
    $command = "SELECT * FROM kimart_tb WHERE kimart_id='$kimart_id' AND date_discont > 0 AND reason != ''";
    $result = mysqlQueryResult($command, $dbc);
    $hasMysqlError = hasMysqlError($dbc);

    if ($hasMysqlError == TRUE) { // There IS a db error.
      $err_msg = $tech_diff_msg;
    }

    if ($result->num_rows > 0) {
      $data = $result->fetch_object();
      $date_discont = $data->date_discont;
      $reason = $data->reason;      
    ?>
    <?php  generateDiv('Date Deleted: '); ?>
      <?php echo $date_discont; ?>
    </div>
  </div>
  
  <?php  generateDiv('Reason: '); ?>
      <?php echo $reason; ?>
    </div>
  </div> 
  <?php 
    }
  ?>
  
  <!-- Pass art_title and art_year to add.php, edit.php or delete.php -->
  <br>
  <a href="kimart_internal_search_art.php"> Search for Artwork </a>
  <a href="kimart_internal_edit_details.php?kimart_id=<?php echo $kimart_id; ?>"> Edit Details </a>
  <a href="kimart_internal_add_work.php"> Add New Work </a>
  <a href="kimart_internal_delete_work.php?kimart_id=<?php echo $kimart_id; ?>"> Delete Work </a>
  
 </div><!-- Close tag for: div id="main"  -->

<?php
  include "kimart_footer.inc.php";
  $dbc->close();
?>
