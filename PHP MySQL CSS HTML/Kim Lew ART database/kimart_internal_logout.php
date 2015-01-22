<?php
  // Log out and end session.
  $page_title = "Log Out";
  include('kimart_internal_header.inc.php');
  include('kimart_internal_common_functions.inc.php');

  $msg_to_user = "";
  
  session_start();
    
  // CHECK the user_id in the session array to see if the user is logged in.
  // If NOT logged in, redirect to the Log In page.
  // If the user is logged in, clear(delete) the session variables by clearing 
  // the $_SESSION array to log them out and redirect to the Log In page.

  if (!(isset($_SESSION['user_id']))) {
    header("Location: kimart_internal_login.php");
  }
  else if (isset($_SESSION['user_id'])) {
    $msg_to_user = $_SESSION['user_name'] . " has been logged out.";
    // Delete the session vars by clearing the $_SESSION array.
    $_SESSION = array(); // This code removes all of the session variables in the current session.
    session_unset(); // Deletes the session variables.
    session_destroy(); // Destroys (ends) the session preventing it from being used in another page.
    header("Location: kimart_internal_login.php");
  }
  
?>
 <div id="main">
  <p class = "msg_to_user"> <?php echo displayMsg($msg_to_user); ?> </p>
 </div>
 
<?php
  include "kimart_footer.inc.php";
?>
