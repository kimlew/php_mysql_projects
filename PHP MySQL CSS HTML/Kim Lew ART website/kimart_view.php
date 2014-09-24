<?php
/*  Script to display larger image of thumbnail that was clicked in index.php
    kimart_id passed in via URL as paramter, kimart_id.  */
    
  $page_title = $_GET['kimart_title'];
  include "kimart_header.inc.php";
  require_once("kimart_connect_vars.inc.php");
  include('kimart_common_functions.inc.php');
?>
    <div id = "main">
	<?php
    // Display and retrieved from database art image and details that correspond to kimart_id in URL.
    $dbc = new mysqli(DB_HOST, DB_USER, DB_PW, DB_NAME) or die ("Cannot connect to MySQL.");
		
    // echo "<br>Database connection error: ". mysqli_error($dbc) . "<br>";
		
    $kimart_id = $_GET[kimart_id]; 	
	
    $command = "SELECT kimart_title, kimart_year, kimart_medium, kimart_height, kimart_width, kimart_image
	    FROM kimart_tb 
	    WHERE kimart_id = $kimart_id
	    AND show_on_page != 'None'";
    $result = $dbc->query($command);
	
	$err_msg = "There are technical difficulties. Please contact us.";
    $query_success = checkQuery($result, $dbc, $err_msg);

    if ($data = $result->fetch_object()) {
      $kimart_title = $data->kimart_title;
      $kimart_year = $data->kimart_year;
      $kimart_medium = $data->kimart_medium;
	   
      $kimart_height = $data->kimart_height;
      $kimart_width = $data->kimart_width;  
      $kimart_image = $data->kimart_image;
	  ?>
	  
	  <span id = "largeImageAndDetails">
	    <div id = "image">
        <img src="<?php echo $kimart_image; ?>" alt="<?php echo $kimart_image; ?>">
	    </div>	
	  
	    <div id = "imageDetails">
      <?php
	      echo $kimart_title . ", " . $kimart_year . ", " . $kimart_medium . ", "
	      . $kimart_height . " x " . $kimart_width . " inches ";
	  }
	    ?>
	    </div>	  
	  </span>
  </div>	

<?php
include "kimart_footer.inc.php";
?>
