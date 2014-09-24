<?php
/*  INCLUDE file for header with Kim Art logo and navig menu.  */
?>

<!doctype html>
<html lang="en">
 <head>
    <meta charset = "utf-8">
	<?php
	  echo '<title> Kim Lew ART - ' . $page_title . '</title>';
	?>

	<!-- Mobile, Tablet and Desktop layout - Resizes from multi to one column when browser resizes. -->
	<link rel="stylesheet" href="kimart.css">
	
	<!-- Set viewport so Zoom works if device is rotated. -->
	<meta name="viewport" content="width=device-width, initial-scale=1" />
 </head>

 <body>
  <div id ="header">
	  <p id = "logo">
	    <a href="index.php">
	      <img src = "images/Kim_Lew_ART.png" alt="Kim Lew ART">
	    </a>
	  </p>
	
	  <ul id = "navMenu">
	    <li><a href="index.php"> 2012 </a></li>
	    <li><a href="index.php?page_title=2011"> 2011 </a></li>
	    <li><a href="index.php?page_title=2010"> 2010 </a></li> 
	    <li><a href="index.php?page_title=Project"> Project </a></li> 
	    <li><a href="index.php?page_title=Drawings"> Drawings </a></li> 
	  
	    <li><a href="kimart_bio_and_cv.php"> Bio and CV </a></li>  
	    <li><a href="kimart_contact.php"> Contact </a></li>  
	  </ul>
  </div>

  <h2 id="pageTitle">
	<?php
	  echo $page_title;
	?>
  </h2>
  