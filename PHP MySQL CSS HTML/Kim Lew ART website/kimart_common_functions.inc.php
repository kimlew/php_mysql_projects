<?php
/* INCLUDE file for common functions.  */

// Function for checking query from database.
function checkQuery($result, $dbc, $err_msg) {  
  if (empty($result)) { 
    // Need to send these database errors to a error log file so user does not see.
    /* Check connection to kimlewart database.
    mysqli_select_db($dbc, "kimlewartdb") or die ("You did not connect to the database '" . "kimlewartdb" . "'.");
    echo "Success! Connected to database: " . "kimlewartdb <br><br>"; // Result: Success! Connected to database: kimlewartdb 
    */
    $db_error =  mysqli_error($dbc);
    // $err_msg = $err_msg."SOME string"
    // Send msg to log file which is configured in php.ini file, probably the Apache error log.
    // Will append to the end of this file.
    error_log($db_error);
    echo $err_msg;
    return false;
  }
  else {
    return true;
  }
}

// Function for generating a thumbnail.
function createThumb($kimart_id, $kimart_title, $kimart_year, $kimart_image) {
  // $outFile - Initialize a variable that holds thumbnail image.
  // Note: Generates thumbnail files on the server but temporary till PHP script done running.

  // $haveThumbnail == "TRUE" if statement with create thumbnails ImageMagick code block.
  // Note: if ($haveThumbnail == FALSE) statement NOT needed since I have the Catch.
  // However, set $haveThumbnail = FALSE; within the Catch so a user msg displays about
  // missing image THEN display the rest of the thumbnails after the user msg.
		 
  $outFile = dirname($kimart_image) . '/TH_' . basename($kimart_image);
  $haveThumbnail = TRUE; // Flag initialized to TRUE to determine if there is already a thumbnail.
	
  // Determine if thumbnail exists.
  // Put ImageMagic stuff inside an if Boolean !file_exists(string $filename) statement 
  // to correct missing thumbnails issue.
  if (!(file_exists($outFile))) {
    try {
      $imagick = new Imagick($kimart_image);
      //$imagick->readImage($kimart_image); // Only for image uploads.
      $imagick->thumbnailImage(150, 150, true);
      $imagick->writeImage($outFile);
      $imagick->clear();
    }
    catch(Exception $e) {
      // Do not have die() so program execution continues.
      echo("ERROR: " . basename($kimart_image) . " could not be displayed.");
	    // $e->getMessage()); //missing due to an error
      $haveThumbnail = FALSE;
    }
  }
  // If thumbnail exists, display it.
  if ($haveThumbnail == TRUE) {
    ?>
    <span id = "imageAndDetails">
      <a href="kimart_view.php?kimart_id=<?php echo $kimart_id; ?>&kimart_title=<?php echo $kimart_title; ?>">
        <div id = "image">	
          <img src="<?php echo $outFile; ?>" alt="<?php echo $kimart_image; ?>">
          <!-- This $outFile which is the thumnail name is passed as URL to browser to interpret. -->
        </div>		  
        <div id = "imageDetails">
          <?php echo $kimart_title . ", " . $kimart_year; ?>
        </div>	  	
      </a>
    </span>
    <?php
  }
}
?>
