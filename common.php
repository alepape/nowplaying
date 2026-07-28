<?php

function arrayLocator($array, $locator, $dictindex = 0, $dictkey = "", $dictvalue = "", $parent = FALSE) {
	$last_segment = "";
	$tmp = $array;

	if ($locator !== "") {
		$addr = explode('.', $locator);

		foreach ($addr as $segment) {
			$last_segment = $segment;
			if (!isset($tmp[$segment])) {
				return null;
			}
			$tmp = $tmp[$segment];
		}
	}

	if (is_array($tmp)) {
		if ($dictkey != "") {
			foreach ($tmp as $stream) {
				if (!is_array($stream)) {
					continue;
				}
				if (isset($stream[$dictkey]) && $stream[$dictkey] == $dictvalue) {
					if ($parent) {
						return $stream;
					}
					if ($last_segment !== "" && isset($stream[$last_segment])) {
						return $stream[$last_segment];
					}
					return $stream;
				}
			}
		} else {
			if (isset($tmp[$dictindex])) {
				$entry = $tmp[$dictindex];
				if ($last_segment !== "" && is_array($entry) && isset($entry[$last_segment])) {
					return $entry[$last_segment];
				}
				return $entry;
			}
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