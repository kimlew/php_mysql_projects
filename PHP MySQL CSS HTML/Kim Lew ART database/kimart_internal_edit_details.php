<?php    
  /*  Kim Lew ART - Edit Details  */
  // kimart_id was passed in URL with link to kimart_internal_edit_details.php.    

  $page_title = "Edit Details";
  include("kimart_internal_header.inc.php");
  require_once('kimart_connect_vars.inc.php');
  include('kimart_internal_common_functions.inc.php');
  include('kimart_internal_validation.inc.php');
  
  $kimart_id = '';
  if(isset($_GET['kimart_id'])) {
    $kimart_id = $_GET['kimart_id'];
  }

  $logged_in_user = "";
  $err_msg = "";
  $msg_to_user = "";
  $err_msg_array = array();
  
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
  
  // CHECK kimart_tb for a match using kimart_id.
  $command = "SELECT * FROM kimart_tb WHERE kimart_id='$kimart_id'";
  $result = mysqlQueryResult($command, $dbc);
  $hasMysqlError = hasMysqlError($dbc);
  
  // When there is NO match for $kimart_id in the database, display a user message.
  if ($result->num_rows <= 0) {
    $msg_to_user = "This is a new title. Click Add New Work.";
  }
  // When there IS a match for $kimart_id in the kimart_tb, CHECK kimart_tb for art details, 
  // retrieve and display.   
  else { // if ($result->num_rows > 0)
    $data = $result->fetch_object();
    $art_title = $data->kimart_title;
    $art_year = $data->kimart_year;
    
    $art_medium = $data->kimart_medium;
    $art_height = $data->kimart_height;
    $art_width = $data->kimart_width;
    $art_price = '';
       
    $art_image = $data->kimart_image;
    $show_on_page = $data->show_on_page;
    $comments = $data->comments;
  
    // CHECK kimart_purch_tb for PRICE associated to kimart_id.
    // IF record exists (created recently), retrieve $art_price in kimart_purch_tb and display it. 
    // Covers cases where:
    // $art_price = 0 and 6000 since price_listed column value is 0 or 6000.
    $result_p = getPriceInTable($dbc, $kimart_id);
  
    // If there a price in the database, $0 or more, retrieve it.
    if ($result_p->num_rows > 0) {
      $data_p = $result_p->fetch_object();
      $art_price = $data_p->price_listed;
    }

    if (isset($_POST['submit'])) {
      $title_in_POST = sanitize_input($dbc, $_POST['art_title']);
      $year_in_POST = sanitize_input($dbc, $_POST['art_year']);
      $medium_in_POST = sanitize_input($dbc, $_POST['art_medium']);
      $height_in_POST = sanitize_input($dbc, $_POST['art_height']);
      $width_in_POST = sanitize_input ($dbc,$_POST['art_width']);
      $price_in_POST = sanitize_input($dbc, $_POST['art_price']);
      $image_name_in_FILES = sanitize_input($dbc, $_FILES['art_image']['name']); // Note: Image is in FILES.
      $show_on_page_in_POST = sanitize_input($dbc, $_POST['show_on_page']);
      $comments_in_POST = sanitize_input($dbc,$_POST['comments']);
                  
      $are_all_the_same = (($art_title == $title_in_POST) && 
       ($art_year == $year_in_POST) && 
       ($art_medium == $medium_in_POST) && 
       ($art_height == $height_in_POST) &&
       ($art_width == $width_in_POST) && 
       ($art_price == $price_in_POST) &&
       (($_FILES['art_image']['error'] == 4) || ($art_image == $image_name_in_FILES)) &&
        // Value: 4; UPLOAD_ERR_NO_FILE - No file was uploaded (since there is no file to upload).
       ($show_on_page == $show_on_page_in_POST) &&
       ($comments == $comments_in_POST));  
     
      if ($are_all_the_same) {
        $msg_to_user = "There is no new data to submit.";
      }
     
      else { // If ANY fields are different.
      // Submit clicked. Call compare_input_with_db_value() to compare table data to clean POST data.
      // If POST data is different than table data, assign POST data value as variable 
      // representing field. Validate POST if not Medium or Show on Page drop-down menus.
      // Example: Assign a NEW $art_title value if art_title from kimart_tb is NOT the same.
      
      // Note: Do not push err_msg into array if $err_msg = '';
      // LOOP to add stuff variable to end of an array OR array_length.

        $art_title = compare_input_with_db_value($title_in_POST, $dbc, $art_title);
        $err_msg = validate_art_title($art_title);
        addToErrMsgArray($err_msg, $err_msg_array);

        $art_year = compare_input_with_db_value($year_in_POST, $dbc, $art_year);
        $err_msg = validate_art_year($art_year);
        addToErrMsgArray($err_msg, $err_msg_array);

        $art_medium = compare_input_with_db_value($medium_in_POST, $dbc, $art_medium);
        // Note: art_medium has NO validation since a drop-down selection menu.
        
        $art_height = compare_input_with_db_value($height_in_POST, $dbc, $art_height);
        $err_msg = validate_art_height($art_height);
        addToErrMsgArray($err_msg, $err_msg_array);
 
        $art_width = compare_input_with_db_value($width_in_POST, $dbc, $art_width);
        $err_msg = validate_art_width($art_width);
        addToErrMsgArray($err_msg, $err_msg_array);

        $art_price = compare_input_with_db_value($price_in_POST, $dbc, $art_price);
        $err_msg = validate_art_price($art_price);
        addToErrMsgArray($err_msg, $err_msg_array);
              
        /* At this point, we know the image file:
           - IS an image file, 
           - IS an ok size and 
           - if there is an upload error type 4 or 1.
        */
        $upload_image_type = sanitize_input($dbc, $_FILES['art_image']['type']);
        $upload_image_error = sanitize_input($dbc, $_FILES['art_image']['error']);
        $upload_image_size = sanitize_input($dbc, $_FILES['art_image']['size']);
        
        // Need to disallow image type like .tif and when image file > 1 MB.
        $err_msg = validate_image_type_err_size($upload_image_error, $upload_image_type, $upload_image_size);
        // If the type is empty or the size is empty in FILES, issue an error message and do not proceed.
        // Handled in validate_image_type_err_size().
        addToErrMsgArray($err_msg, $err_msg_array);

        if (!($err_msg) && $_FILES['art_image']['error'] == 0) {
          // Value: 0; UPLOAD_ERR_OK - There is no error, the file uploaded with success.
          // Move uploaded image file from tmp location to the permanent target upload folder, images.
          // Note: move_uploaded_file() - does security checks.
          // - Checks to ensure that the file designated by filename is a valid upload file 
          // (meaning that it was uploaded via PHP's HTTP POST upload mechanism).
          // - Important if there is any chance that anything done with uploaded files could reveal 
          //  contents to  user, or to other users on the same system.
          // Note: $art_image = $data->kimart_image;
          $art_image = compare_input_with_db_value($image_name_in_FILES, $dbc, $art_image);
          $target = KLA_IMAGEUPLOADPATH . $art_image;
          move_uploaded_file($_FILES['art_image']['tmp_name'], $target);
        }
       
        $show_on_page = compare_input_with_db_value($show_on_page_in_POST, $dbc, $show_on_page);
        // Note: show_on_page has NO validation since a drop-down selection menu.
        
        $comments = compare_input_with_db_value($comments_in_POST, $dbc, $comments);
        $err_msg = validate_comments($comments);
        addToErrMsgArray($err_msg, $err_msg_array);
   
        /* At this point, fields have been checked for new data (is diff. than in database).
        UPDATE the database with the new data. Then rename the current variables with the new value 
        in table, so new data displays in fields. Display a message that data has been updated. */
        if (count($err_msg_array) <= 0) {// If NO $err_msg.
          $is_trans_successful = TRUE; // Flag to determine transation success.
         
          // Start transaction.
          $command = "SET AUTOCOMMIT=0";
          $result = mysqlQueryResult($command, $dbc);
          hasMysqlError($dbc);
                      
          $command = "BEGIN";
          $result = mysqlQueryResult($command, $dbc);
          hasMysqlError($dbc);
          
        // UPDATE kimart_tb with ALL new form field values except Price.
          $command = "UPDATE kimart_tb SET
            kimart_title = '$art_title',
            kimart_year = '$art_year',
            kimart_medium = '$art_medium',
            kimart_height = '$art_height',
            kimart_width = '$art_width',
            kimart_image = '$art_image',
            show_on_page = '$show_on_page',
            comments = '$comments'
            WHERE kimart_id = '$kimart_id'";
          $result = mysqlQueryResult($command, $dbc);
          hasMysqlError($dbc);
          
          // Then get updated variables from kimart_tb.
          $result = getTableValues($dbc, $kimart_id);
          $data = $result->fetch_object();
          
          if ($result == FALSE) {
            $is_trans_successful = FALSE;
          }
          else {
            $is_trans_successful = TRUE;              
            if ($art_price) { // is diff than one in db != "")
              // UPDATE kimart_purch_tb with new Price.
              $command = "UPDATE kimart_purch_tb SET price_listed = '$art_price' WHERE kimart_id = '$kimart_id'";
              
              $result_p = mysqlQueryResult($command, $dbc);
              hasMysqlError($dbc);
                    
              if ($result_p == FALSE) {
                $is_trans_successful = FALSE;
              }
              
              // Then get updated variables from kimart_purch_tb.
              $result_p = getPriceInTable($dbc, $kimart_id);
              $data_p = $result_p->fetch_object();
            }
          }
          if ($is_trans_successful == TRUE) {
            $command = "COMMIT";
            $result = mysqlQueryResult($command, $dbc);
            hasMysqlError($dbc);
            $msg_to_user = "The work has been added.";             
          }
          else {
            $command = "ROLLBACK";
            $result = mysqlQueryResult($command, $dbc);
            hasMysqlError($dbc);
            $msg_to_user = "Please contact technical support.";  
          }
          
          // Set autocommit back to 1.
          $command = "SET AUTOCOMMIT = 1";
          $result = mysqlQueryResult($command, $dbc);
          hasMysqlError($dbc);
          
          // Display values from tables.
          if ($data || $data_p) {      
            $art_title = $data->kimart_title;
            $art_year = $data->kimart_year;
            $art_medium = $data->kimart_medium;
        
            $art_height = $data->kimart_height;
            $art_width = $data->kimart_width;
           
            $art_image = $data->kimart_image;
            $show_on_page = $data->show_on_page;
            $comments = $data->comments;
            
            if ($data_p) {
              $art_price =  $data_p->price_listed;
            }
            $msg_to_user = "The data has been updated.<br>"; 
          }
          
        } // Close tag for: if (count($err_msg_array) <= 0) 
      }  // Close tag for: else { //if (!($are_all_the_same))
    } // Close tag for: if (isset($_POST['submit']))
  } // Close tag for: if ($result->num_rows > 0)

?>

<form enctype="multipart/form-data" method="POST" action="kimart_internal_edit_details.php?kimart_id=<?php echo $kimart_id; ?>">
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
      <input type="text" size="25" name="art_title" value="<?php echo $art_title; ?>"> *
    </div>
  </div>

  <?php  generateDiv('Year: '); ?>
      <input type="text" size="25" name="art_year" placeholder="YYYY" value="<?php echo $art_year; ?>"> *
    </div>
  </div>

  <?php  generateDiv('Medium: '); ?>
      <select name="art_medium">
        <option value="<?php echo $art_medium; ?>"> Select the medium.    
        <?php
         $medium_options_array = array(
           "acrylic on canvas", 
           "acrylic on board",
           "oil on canvas",
           "oil on board",
           "ink on paper",
           "mixed media",
           "charcoal on paper"
           );  
           $db_value = $art_medium;
           generateOptionsInArray($db_value, $medium_options_array);
        ?>
      </select>
    </div>
  </div>
      
  <?php  generateDiv('Height (inches): '); ?>
      <input type="text" step="0.25" size="8" name="art_height" placeholder="0.00" 
        value="<?php echo number_format(floatval($art_height), 2); ?>">
    </div>
  </div>

  <?php  generateDiv('Width (inches): '); ?>
      <input type="text" step="0.25" size="8" name="art_width" placeholder="0.00" 
        value="<?php echo number_format(floatval($art_width), 2); ?>">
    </div>
  </div>

  <?php  generateDiv('Price (USD): '); ?>
      <input type="text" step="5" size="8" name="art_price" placeholder="0" value="<?php echo $art_price; ?>">
    </div>
  </div>

  <?php generateDiv('Image: '); ?>
  <?php echo $art_image; ?>
    </div>
  </div>
  
  <?php generateDiv('Image (new): '); ?>
      <input type="file" name="art_image">
    </div>
  </div>

   <?php generateDiv('Show on Page: '); ?>	
     <select name = "show_on_page">
       <option value = "<?php echo $show_on_page; ?>"> Select the page.
       <?php
         $page_options_array = array(
           "None", 
           "2012",
           "2011",
           "2010",
           "Drawings",
           "Project",
           );  
           $db_value = $show_on_page;
           generateOptionsInArray($db_value, $page_options_array);
       ?>
	   </select>
   </div>
</div>

   <?php generateDiv('Comments: '); ?>
      <textarea name="comments" cols="60" rows="3"><?php echo $comments; ?></textarea>
    </div>
  </div>
  
   <?php generateDiv(''); ?>
      <input type="submit" name="submit" value="Submit">
    </div>
  </div>

  <br>
  <!-- Pass kimart_id along with links for View Work and Delete Work. -->
  <a href="kimart_internal_view_details.php?kimart_id=<?php echo $kimart_id; ?>"> View Details </a>
  <a href="kimart_internal_search_art.php"> Search for Art </a>
  <a href="kimart_internal_add_work.php"> Add New Work </a>
  
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
