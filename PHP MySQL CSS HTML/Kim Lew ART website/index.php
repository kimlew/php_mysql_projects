<?php
  $page_title = "2012";
  // If $page_title has been set in $_GET to something else, e.g., 2010, then use that as $page_title.
  if (isset($_GET['page_title'])) {
    $page_title = $_GET['page_title'];
  }
  include("kimart_header.inc.php");
  require_once("kimart_connect_vars.inc.php");
  include('kimart_common_functions.inc.php');
?>

  <div id = "main">
	<?php
    // Retrieve art images, titles and years for $page_title from database.
    $dbc = new mysqli(DB_HOST, DB_USER, DB_PW, DB_NAME) or die ("Cannot connect to MySQL.");
    $command = "SELECT kimart_id, kimart_title, kimart_year, kimart_image FROM kimart_tb WHERE show_on_page='$page_title'";
    $result = $dbc->query($command);
	
	  $err_msg = "There are technical difficulties. Please contact us.";
    $query_success = checkQuery($result, $dbc, $err_msg);
	
    if ($query_success) { // The query was sucessful and returned TRUE.
       while ($data = $result->fetch_object()) {
         $kimart_id = $data->kimart_id;
         $kimart_title = $data->kimart_title;
         $kimart_year = $data->kimart_year;
         $kimart_image = $data->kimart_image; 
	   
         if ($kimart_image != "") { // Need to skip ones with no image yet.
           createThumb($kimart_id, $kimart_title, $kimart_year, $kimart_image);   
         }
       }	
    }	
	?>	    
  </div>
	
<?php
  include("kimart_footer.inc.php");
  $dbc->close();
?>