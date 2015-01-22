<?php
/*  INCLUDE file for header with Kim Art logo and navig menu. */
?>

<!doctype html>
<html lang="en">
 <head>
	<meta charset = "utf-8">
	<?php
	  echo '<title> Kim Lew ART - ' . $page_title . '</title>';
	?>

	<!-- Mobile, Tablet and Desktop layout - Resizes from multi to one column when browser resizes. -->
	<link rel="stylesheet" href="kimart_internal.css">
	
	<!-- Set viewport so Zoom works if device is rotated. -->
	<meta name="viewport" content="width=device-width, initial-scale=1" />
 </head>
 
 <body> 
  <div id ="header">
    <p id = "logo">
      <a href="kimart_internal_search_art.php">
        <img src = "images/Kim_Lew_ART.png" alt="Kim Lew ART">
      </a>
    </p>
    <ul id = "navMenu">
	    <li><a href="kimart_internal_logout.php"> Log Out </a></li>
	  </ul>
	</div>
	
  <h3 id="pageTitle">
	<?php
	  echo $page_title;
	?>
  </h3>