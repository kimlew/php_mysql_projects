<?php    
  /*  Kim Lew ART - Add New Work */
  $page_title = "Add New Work";
  $tech_diff_msg = "There are technical difficulties. Please contact us.";
 
  $instruct_start = "* Required field.";
  $logged_in_user = "";
  $msg_to_user = "";
  $err_msg = "";
  $kimart_id = "";
  $err_msg_array = array();
  $input_array = array();
  $filled_in_input_array = array();
  
  include("kimart_internal_header.inc.php");
  require_once('kimart_connect_vars.inc.php');
  include('kimart_internal_common_functions.inc.php');
  include('kimart_internal_validation.inc.php');
  
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
  
  // Initialize variables that correspond to form fields.
  $art_title = $art_year = $art_medium = $art_height = $art_width = $art_price = $art_image = $comments = $show_on_page = '';

  // CHECK if Submit clicked. Only process form inputs if Submit clicked.
  if (isset($_POST['submit'])) {  // if (count($_POST) > 0)
    // CHECK for filled-in Title, the ONLY Required field.
    $art_title = ($_POST['art_title']);
    
    if (empty($art_title)) {
      $msg_to_user = "Enter a Title for the work.";
    }
    else { //if ($art_title) { // Title has been entered.
    // Get from $_POST, validate it and CHECK db for a match.
    // If NO match, Title is NEW. Assign value to $art_title. 
      $art_title  = sanitize_input($dbc, $_POST['art_title']);
      
      // Check if $title_in_POST (and field) is already in the db.
      $command = "SELECT * FROM kimart_tb WHERE kimart_title='$art_title'";
      $result = mysqlQueryResult($command, $dbc);
      $hasMysqlError = hasMysqlError($dbc);    

      if ($result->num_rows <= 0) {
      // mysqli_result Object([current_field] => 0 [field_count] => 10 [lengths] => [num_rows] => 0 [type] => 0)     
      // Case 2: Title is not in db so what is entered is new.
      // Get new data from any other fields entered, validate and then INSERT. 
        $msg_to_user = "This is a new title. You can enter other fields and click Submit.";

        // Check other OPTIONAL fields for data, VALIDATE and display approp. $err_msg.
        $art_year = sanitize_input($dbc, $_POST['art_year']);       
        $art_medium = sanitize_input($dbc, $_POST['art_medium']);
        $art_height = sanitize_input($dbc, $_POST['art_height']);       
        $art_width = sanitize_input($dbc, $_POST['art_width']);
        $art_price = sanitize_input($dbc, $_POST['art_price']);       
        $art_image = $_FILES['art_image']['name']; // Image is in FILES, not POST.
        $show_on_page = sanitize_input($dbc, $_POST['show_on_page']);
        $comments = sanitize_input($dbc, $_POST['comments']);
 
        $err_msg = validate_art_title($art_title);
        addToErrMsgArray($err_msg, $err_msg_array);
 
        if ($art_year) {
          $err_msg = validate_art_year($art_year);
          addToErrMsgArray($err_msg, $err_msg_array);
        }
        
        // Note: art_medium has NO validation since a drop-down selection menu.
        
        if ($art_height) {
          $err_msg = validate_art_height($art_height);
          addToErrMsgArray($err_msg, $err_msg_array);
        }        
        if ($art_width) {
          $err_msg = validate_art_width($art_width);
          addToErrMsgArray($err_msg, $err_msg_array);
        }
        if ($art_price) {
          $err_msg = validate_art_price($art_price);
          addToErrMsgArray($err_msg, $err_msg_array);
        }
       
        if ($_FILES['art_image']['error'] == 4) {
        // Value: 4; UPLOAD_ERR_NO_FILE - No file was uploaded (since there is no file to upload).
          $msg_to_user = "There is no file to upload.";
        }        

        if ($art_image) {
          $upload_image_type = sanitize_input($dbc, $_FILES['art_image']['type']);
          $upload_image_error = sanitize_input($dbc, $_FILES['art_image']['error']);
          $upload_image_size = sanitize_input($dbc, $_FILES['art_image']['size']);
          
          // Need to disallow some image types, e.g., .tif and when image file size > 1 MB.
          $err_msg = validate_image_type_err_size($upload_image_error, $upload_image_type, $upload_image_size);
          // If the type is empty or the size is empty in FILES, issue an error message and do not proceed.
          // Handled in validate_image_type_err_size().
          addToErrMsgArray($err_msg, $err_msg_array);

          if ($_FILES['art_image']['error'] == 0) {
            // Value: 0; UPLOAD_ERR_OK - There is no error, the file uploaded with success.
            // Move uploaded image file from tmp location to the permanent target upload folder, images.
            // Note: move_uploaded_file() - does security checks.
            // - Checks to ensure that the file designated by filename is a valid upload file 
            // (meaning that it was uploaded via PHP's HTTP POST upload mechanism).
            // - Important if there is any chance that anything done with uploaded files could reveal 
            //  contents to  user, or to other users on the same system.
            $target = KLA_IMAGEUPLOADPATH . $art_image;
            move_uploaded_file($_FILES['art_image']['tmp_name'], $target);
          }
        }
        // Note: show_on_page has NO validation since a drop-down selection menu.
        
        if ($comments) {        
          $err_msg = validate_comments($comments);
          addToErrMsgArray($err_msg, $err_msg_array);
        }
 
        if (count($err_msg_array) <= 0) { // If NO $err_msg after validate rest of fields.
        // Construct strings for partial MySQL INSERT statement into kimart_tb for filled-in  
        // fields with array with ALL data and array with ONLY filled-in data.
        
          // $input_array with ALL field data except $art_price, empty strings included.
          $input_array = array('kimart_title' => $art_title,
          'kimart_year' => $art_year,
          'kimart_medium' => $art_medium,
          'kimart_height' => $art_height,
          'kimart_width' => $art_width,
          'kimart_image' => $art_image,
          'show_on_page' => $show_on_page,
          'comments' => $comments);
  
          foreach($input_array as $column_name => $column_value) {
          // $filled_in_input_array with ONLY filled-in field data, so NO empty string values.
          // Loop to check for all filled-in fields and put all of the $column_name and  
          // $column_value in the $filled_in_input_array.      
            if ($column_value != '' ) {
              $filled_in_input_array[$column_name] = $column_value;
            }
          }

          // Construct partial MySQL statements for columns and for values
          // that each get comma, space and column/value.
          // Note: The last items for each do not get a comma.
          // Initialize $column_names to autoincremented first column of kimart for MySQL statement.
          // Initialize $column_values to autoincrement first column value of '' for MySQL statement.
          $column_names = "kimart_id";
          $column_values = "''";
          
          foreach($filled_in_input_array as $column_name => $column_value) {
            $column_names .= ", " . $column_name;
            $column_values .= ", '" . $column_value . "'";
          }

          // Since no kimart_id exists until After the INSERT, use transactions and INSERT first.
          // Then get kimart_id to INSERT art_price to price_listed in kimart_purch_tb.            
          // INSERT INTO kimart_tb ALL new form field values except $art_price which goes to kimart_purch_tb.
          // Use Transaction since 2-step process needed for this.      
          $is_trans_successful = TRUE; // Flag to determine transation success.
         
          // Start transaction.
          $command = "SET AUTOCOMMIT=0";
          $result = mysqlQueryResult($command, $dbc);
          hasMysqlError($dbc);
                      
          $command = "BEGIN";
          $result = mysqlQueryResult($command, $dbc);
          hasMysqlError($dbc);
          
          // INSERT INTO kimart_tb.
          $command = "INSERT INTO kimart_tb (" . $column_names . ") VALUES (" . $column_values . ")";
          $result = mysqlQueryResult($command, $dbc);
          hasMysqlError($dbc);
              
          if ($result == FALSE) {
            $is_trans_successful = FALSE;
          }
          else {
            $is_trans_successful = TRUE;
            
            if ($art_price != "") {
            // Now, get and use kimart_id for INSERT INTO kimart_purch_tb of kimart_price as price_listed.
            // insert_id returns the ID generated by a query on a table with a column having the  
            // AUTO_INCREMENT attribute. If the last query was not an INSERT or UPDATE statement or if the  
            // modified table does not have a column with the AUTO_INCREMENT attribute, it returns zero.                   
              $kimart_id = $dbc->insert_id;
              // 'price_listed' => $art_price,
              $command = "INSERT INTO kimart_purch_tb (price_listed, kimart_id) 
                VALUES ('$art_price', '$kimart_id')";
              $result = mysqlQueryResult($command, $dbc);
              hasMysqlError($dbc);
              
              if ($result == FALSE) {
                $is_trans_successful = FALSE;
              }
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

        } // Close tag for: if (count($err_msg_array) <= 0) // If NO error messages after validation.   
      } // Close tag for: if ($result->num_rows == 0) 
      else { //if ($result->num_rows > 0)
        $msg_to_user = "This work is already in the database.";
      }
    } // Close tag for: else if ($art_title)
  } // Close tag for: if (isset($_POST['submit']))
?>

<form enctype="multipart/form-data" method="POST" action="kimart_internal_add_work.php">
  <input type="hidden" name="MAX_FILE_SIZE" value="1024000">
  <!--
  - enctype - form attribute that tells form to use special type of encoding for file upload 
  - enctype - affects how the POST data is bundled and sent when the form is submitted.
  - input value - sets maximum file upload size for 1 MB (1,000,000 bytes).
  -->

 <div id="main">
  <p id = "instruction"> <?php echo $instruct_start; ?> </p>
  <p id = "instruction"> <?php echo $logged_in_user; ?> </p>
  <p class = "msg_to_user"> <?php displayMsg($msg_to_user); ?> </p>

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
      <input type="text" size="25" name="art_year" placeholder="YYYY" value="<?php echo $art_year; ?>">
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

  <?php  generateDiv('Height: '); ?>
      <input type="text" step="0.25" size="8" name="art_height" placeholder="0.00"
        value=
          "<?php 
          if ((!empty($_POST['art_height'])) && (($_POST['art_height']) > 0)) {
            echo number_format(floatval($art_height), 2);
          }
          ?>">
    </div>
  </div>

  <?php  generateDiv('Width: '); ?>
      <input type="text" step="0.25" size="8" name="art_width" placeholder="0.00" 
        value=
          "<?php 
          if ((!empty($_POST['art_width'])) && (($_POST['art_height']) > 0)) {
            echo number_format(floatval($art_width), 2);
          }
          ?>">
    </div>
  </div>

  <?php  generateDiv('Price (USD): '); ?>
      <input type="text" step="5" size="8" name="art_price" placeholder="0" value="<?php echo $art_price; ?>">
    </div>
  </div>

  <?php generateDiv('Image (in database): '); ?>
  <?php echo $art_image; ?>
    </div>
  </div>
  
  <?php generateDiv('Image: '); ?>
      <input type="file" id="art_image" name="art_image">
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
      <input type="submit" name="submit" value="Add">
    </div>
  </div>

  <!-- Pass art_title and art_year to add.php, edit.php or delete.php -->
  <br><br>
   
  <?php
    if (!empty($kimart_id)) {
  ?>
   <a href="kimart_internal_view_details.php?kimart_id=<?php echo $kimart_id; ?>"> View Details </a>
   <a href="kimart_internal_edit_details.php?kimart_id=<?php echo $kimart_id; ?>"> Edit Details </a>
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
