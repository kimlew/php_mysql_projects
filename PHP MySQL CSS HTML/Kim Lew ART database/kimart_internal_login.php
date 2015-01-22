<?php
  $page_title = "Log In";
  include("kimart_internal_header.inc.php");
  require_once('kimart_connect_vars.inc.php');
  include('kimart_internal_common_functions.inc.php');
  include('kimart_internal_validation.inc.php');
  
  $tech_diff_msg = "There are technical difficulties. Please contact us.";
  $instruct_start = "* Required fields.";
  $err_msg = "";
  $msg_to_user = "";
  $user_name = $password = '';

  session_start();  
  $dbc = new mysqli(DB_HOST, DB_USER, DB_PW, DB_NAME) or die("Cannot connect to MySQL.");
  
  // CHECK if Submit clicked. Only process form inputs if Submit clicked.
  if (isset($_POST['submit'])) {  // if (count($_POST) > 0)  
   // if (!isset($_SESSION['user_id'])) {  // If the user isn't logged in, try to log them in
  // isset()-see if a variable exists and is set VS empty()-see if variable has any data
    $user_name = mysqli_real_escape_string($dbc, trim($_POST['user_name']));
    $password = mysqli_real_escape_string($dbc, trim($_POST['password']));
    
    if (!($user_name) || !($password)) {
      $err_msg = "The login user name or password you entered is missing.";
    }
    
    else if ($user_name && $password) {
      // Use user name entered, $user_name, and see if this is in database.
      // If it is, the SELECT returns associated data, user_id, user_name and password from database. 
      $command = "SELECT user_id, user_name, password FROM kimart_internal_login_tb WHERE user_name = '$user_name'";
      $result = mysqlQueryResult($command, $dbc);
      $hasMysqlError = hasMysqlError($dbc);
    
      if ($result) { // For SELECT: NO technical database error.
        // Compare user_name entered with user_name retrieved from SELECT FROM database.
        // If there are rows, a match exists. $result has something.
        
        if ($result->num_rows > 0) {
          $msg_to_user = "The user was found.";
           
          $data = $result->fetch_object();
          $user_id_in_db = $data->user_id;
          $user_name_in_db = $data->user_name;
          $hashed_saltpw_from_db = $data->password;
        
          // Check if the hash of the entered login password, matches the stored hash.
          // The salt and the cost factor will be extracted from $hashed_saltpw_from_db.        
          if (password_verify($password, $hashed_saltpw_from_db)) {  
          // password_verify(pword just entered, hashed pword from db) - bcrypt function
            $msg_to_user = 'Password is correct for this user!';
           
            // Create 2 session variables user_id and user_name.
            // Store Log-in data using a session.
            $_SESSION['user_id'] = $user_id_in_db;
            $session_user_id = $_SESSION['user_id'];
            
            $_SESSION['user_name'] = $user_name_in_db;
            $session_user_name = $_SESSION['user_name'];
  
            // Redirect user to kimart_internal_search_art.php.
            header("Location: kimart_internal_search_art.php");
          } 
          else {
            $err_msg = 'Password is NOT correct for this user. Try again.';
          }    
        }
        else if ($result->num_rows <= 0)  {
          /*  NO match exists. $result has nothing.  same as if ($result->num_rows <= 0) */
          $msg_to_user = "The user was NOT found. Enter a different login user name.";
        }
      }
    }
  }

?>

<form method="POST" action="kimart_internal_login.php">

 <div id="main">
  <p id = "instruction"> <?php echo $instruct_start; ?> </p>
  <p class = "msg_to_user"> <?php echo displayMsg($msg_to_user); ?> </p>
  <p class = "err_msg"> <?php echo displayErrMsg($err_msg); ?> </p>
	  
  <div class = "row">
    <div class = "field_name_column"> Login User Name: </div>
    <div class = "input_column">
      <input type="text" size="16" maxlength="16" name="user_name" value="<?php echo $user_name; ?>"> *
    </div>
  </div>
  
  <div class = "row">
    <div class = "field_name_column"> Password: </div>
    <div class = "input_column">
      <input type="password" size="16" maxlength="16" name="password" value=""> *
    </div>
  </div>
  
  <div class = "row">
    <div class = "field_name_column"></div>
    <div class = "input_column"> 
      <input type="submit" name="submit" value="Login">
    </div>
  </div>
  
 </div><!-- Close tag for: div id="main"  -->
</form>

<?php
  include "kimart_footer.inc.php";
  $dbc->close();
?>