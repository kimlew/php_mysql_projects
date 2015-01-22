<?php    
  /*  Kim Lew ART - Delete Work 
      Removes work from use and adds now() as a date_discont column 
      but keeps record in table.
  */
  $page_title = "Delete Work";
  include("kimart_internal_header.inc.php");
  require_once('kimart_connect_vars.inc.php');
  include('kimart_internal_common_functions.inc.php');
  include('kimart_internal_validation.inc.php');
  
  $tech_diff_msg = "There are technical difficulties. Please contact us.";
  $logged_in_user = "";
  $msg_to_user = "";
  $err_msg = "";
  $err_msg_array = array();
  
  $kimart_id = '';
  if(isset($_GET['kimart_id'])) {
    $kimart_id = $_GET['kimart_id'];
  }
  
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
  $command = "SELECT * FROM kimart_tb WHERE kimart_id='$kimart_id'"; // AND date_discont = 0
  $hasMysqlError = hasMysqlError($dbc);
  $result = mysqlQueryResult($command, $dbc);
 
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
  $reason = "";
    
  // Retrieve $art_price from kimart_purch_tb using $kimart_id.
  $command = "SELECT price_listed FROM kimart_purch_tb WHERE kimart_id='$kimart_id'";
  $hasMysqlError = hasMysqlError($dbc);
  $result = mysqlQueryResult($command, $dbc);
  
  // For SELECT command
  if ($result->num_rows > 0) {
    $data = $result->fetch_object();
    $art_price = $data->price_listed;
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
        $command = "UPDATE kimart_tb SET date_discont=now(), reason='$reason' WHERE kimart_id = $kimart_id";
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

<form enctype="multipart/form-data" method="POST" action="kimart_internal_delete_work.php?kimart_id=<?php echo $kimart_id; ?>">
  <input type="hidden" name="MAX_FILE_SIZE" value="1024000">
  <!--
  - enctype - form attribute that tells form to use special type of encoding for file upload 
  - enctype - affects how the POST data is bundled and sent when the form is submitted.
  - input value - sets maximum file upload size for 1 MB (1,000,000 bytes).
  -->

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
  ?> </p>
  
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

  <?php  generateDiv('Height: '); ?>
      <?php echo $art_height; ?>
    </div>
  </div>

  <?php  generateDiv('Width: '); ?>
      <?php echo $art_width; ?>
    </div>
  </div>

  <?php  generateDiv('Price (USD): '); ?>
      <?php echo $art_price; ?>
    </div>
  </div>

  <?php generateDiv('Image: '); ?>
      <?php echo $art_image; ?>
    </div>
  </div>

   <?php generateDiv('Show on Page: '); ?>
      <?php echo $show_on_page; ?>
    </div>	
  </div>

   <?php generateDiv('Comments: '); ?>
      <?php echo $comments; ?>
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

  <!-- Pass art_title and art_year to add.php, edit.php or delete.php -->
  <br><br>
   
  <?php
    if (!empty($kimart_id)) {
  ?>
   <a href="kimart_internal_view_details.php?kimart_id=<?php echo $kimart_id; ?>"> View Details </a>
  <?php
    }
  ?>
  
   <a href="kimart_internal_search_art.php"> Search Art </a>
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
