<?php

function arrayLocator($array, $locator, $dictindex = 0, $dictkey = "", $dictvalue = "") {
	$addr = explode('.', $locator);

	foreach($addr as $i){
		if(!isset($tmp)){
	        $tmp = &$array[$i];
	    } else if (isset($tmp[$i])) {
	        $tmp = $tmp[$i];
	    }
	}
	//echo gettype($tmp);
	if (gettype($tmp) == "array") {
		// check if using key or index
		if ($dictkey != "") {
			foreach($tmp as $stream) {
				if ($stream[$dictkey] == $dictvalue) {
					$tmp = $stream[$i];
				}
			}
		} else {
			$tmp = $tmp[$dictindex][$i];
		}
	}
	return $tmp;
}

function cmp($a, $b) {
    $date_a = substr($a["date"], 0, 4);
    $date_b = substr($b["date"], 0, 4);

    return strcmp($date_a, $date_b);
}

function endsWith( $haystack, $needle ) {
    $length = strlen( $needle );
    if( !$length ) {
        return true;
    }
    return substr( $haystack, -$length ) === $needle;
}
  

?>