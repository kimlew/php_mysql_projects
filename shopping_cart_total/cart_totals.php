<?php
/* Script that efficiently takes an array of numbers as an input. Calculates; 
   - sum of entire cart - all values in array
   - sum of the 2 highest values in the array
   - sum of the 2 lowest values in the array
   - Assumes: Whatever admin input form attaches to this only allows positive price entries.
*/

function determine_high_start_values ($an_array) {
  // At start, when there are only 2 values to consider:
  // Compare and assign which is highest and which is 2nd highest.
  if (count($an_array) == 0) {
	$temp_highest = 0;
	$temp_2nd_highest = 0;
  }
  else if (count($an_array) == 1) {
	$temp_highest = $an_array[0];
	$temp_2nd_highest = 0;
  }
  else {
	if ($an_array[0] <= $an_array[1]) {
		$temp_highest = $an_array[1];
		$temp_2nd_highest = $an_array[0];
	}
	else { 
		// if ($an_array[0]  > $an_array[1])
		$temp_highest = $an_array[0];
		$temp_2nd_highest = $an_array[1];
	}
  }
  $high_start_values = array($temp_highest, $temp_2nd_highest);
  return $high_start_values;
}

function get_two_highest ($an_array) {
  $high_start_values = determine_high_start_values ($an_array);
  
  $temp_highest = $high_start_values[0];
  $temp_2nd_highest = $high_start_values[1];
  
  for ($i = 2; $i <= count($an_array) - 1; $i++)  {
    // Every other time, when there are 3 values to consider:
    // - the new value, e.g., $an_array[$i] with 
    // - the 2 previous values of $temp_highest and $temp_2nd_highest, and so on.
    // I do not need to consider values at $an_array[0] and $an_array[1] since
    // they have already been considered with initial comparison and assignment.
    // Start $i = 2.
    // Compare value at $an_array[$i] with highest.
    // Returns array with 2 highest values.
		
    if ($an_array[$i] > $temp_highest) { 
      $temp_2nd_highest = $temp_highest;
      $temp_highest = $an_array[$i];    
    }
    else { 
      // if ($an_array[$i] <= $temp_highest)
      // Compare $an_array[$i] with $temp_2nd_highest
      if ($an_array[$i] > $temp_2nd_highest) {      
        $temp_2nd_highest = $an_array[$i];
      }
    }
  }
  $two_highest = array($temp_highest, $temp_2nd_highest);
  return $two_highest;
}

function determine_low_start_values ($an_array) {
  // At start, when there are only 2 values to consider:
  // Compare and assign which is lowest and which is 2nd lowest.
  if (count($an_array) == 0) {
	$temp_lowest = 0;
	$temp_2nd_lowest = 0;
  }
  else if (count($an_array) == 1) {
	$temp_lowest = $an_array[0];
	$temp_2nd_lowest = 0;
  }	
  else {
	if ($an_array[0] <= $an_array[1]) {
		$temp_lowest = $an_array[0];
		$temp_2nd_lowest = $an_array[1];
	}
	else { 
		// if ($an_array[0]  > $an_array[1])
		$temp_lowest = $an_array[1];
		$temp_2nd_lowest = $an_array[0];
	}
  }
  $low_start_values = array($temp_lowest, $temp_2nd_lowest);
  return $low_start_values;
}

function get_two_lowest ($an_array) {
  $low_start_values = determine_low_start_values ($an_array);
  $temp_lowest = $low_start_values[0];
  $temp_2nd_lowest = $low_start_values[1];
  
  for ($i = 2; $i <= count($an_array) - 1; $i++)  {
    // Every other time, when there are 3 values to consider:
    // - the new value, e.g., $an_array[$i] with 
    // - the 2 previous values of $temp_lowest and $temp_2nd_lowest, and so on.
    // I do not need to consider values at $an_array[0] and $an_array[1] since
    // they have already been considered with initial comparison and assignment.
    // Start $i = 2.
    // Compare value at $an_array[$i] with lowest.
		
    if ($an_array[$i] < $temp_lowest) { 
      $temp_2nd_lowest = $temp_lowest;
      $temp_lowest = $an_array[$i];    
    }
    else { 
      // if ($an_array[$i] >= $temp_lowest)
      // Compare $an_array[$i] with $temp_2nd_lowest
      if ($an_array[$i]  < $temp_2nd_lowest) {      
        $temp_2nd_lowest = $an_array[$i];
      }
    }
  }
  $two_lowest = array($temp_lowest, $temp_2nd_lowest);
  return $two_lowest;
}

function total_an_array ($an_array) {
  $array_total = 0;
  
  for ($i = 0; $i < count($an_array) ; $i++)  {
     $array_total = $array_total + $an_array[$i];
  }
  return $array_total;
}

function display_total_highs_lows ($an_array) {
	$cart_total = total_an_array($an_array)  ;
	
	echo "\n";
	echo "-------------------------------------------------\n";
	echo "CART TOTAL:  $" . number_format((float)$cart_total, 2, '.', '')  . "\n";
	echo "-------------------------------------------------\n";
	
	$two_highest = get_two_highest ($an_array) ;
	echo "2 high-priced items: $" .  number_format((float)$two_highest [0] , 2, '.', '')  .
	   " + $" .   number_format((float)$two_highest [1] , 2, '.', '') ;
	$sum_two_highest = total_an_array($two_highest) ;
	echo "  =  $" . number_format((float)$sum_two_highest, 2, '.', '')  . "\n";
	
	$two_lowest = get_two_lowest ($an_array) ;
	echo "2 low-priced items:  $" . number_format((float)$two_lowest [0] , 2, '.', '')  .
	   " + $" .   number_format((float)$two_lowest [1] , 2, '.', '') ;
	$sum_two_lowest = total_an_array($two_lowest) ;
	echo "  =  $" . number_format((float)$sum_two_lowest, 2, '.', '')  . "\n\n";
}

$num_array_1 = array(3, 17, 8, 20, 0);  	// Expected:  total: 48	high two: 37  	low two: 3 		
$num_array_2 = array(3, 8, 8, 2, 0); 		// Expected:  total: 21	high two: 16	low two: 2  		
$num_array_3 = array(3, 17, 8, 2, 1); 		// Expected:  total: 31	high two: 25 	low two: 3

$num_array_4 = array(); 							// Expected:  total: 0		high two: 0		low two: 0, 		
$num_array_5 = array(5); 							// Expected:  total: 5		high two: 5, 	low two: 5
$num_array_6 = array(3.15, 17, 8.7, 20.25, 0); // Expected:  total: 48.10		high two: 37.25		low: 3.15

display_total_highs_lows ($num_array_1);
display_total_highs_lows ($num_array_2);
display_total_highs_lows ($num_array_3);

display_total_highs_lows ($num_array_4);
display_total_highs_lows ($num_array_5);
display_total_highs_lows ($num_array_6);

?>

