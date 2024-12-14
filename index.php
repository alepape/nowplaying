<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

// BOOTSTRAP
include 'config.php';

?>
<header>
<link rel="apple-touch-icon" sizes="180x180" href="picts/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="picts/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="picts/favicon-16x16.png">
<link rel="manifest" href="site.webmanifest">

<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="style.css">
<script src="logic.js"></script>

</header>
<body>
<div id="container">
  <div id="logo">
    <img id="radiologo" src="<?=$configobj['logo']?>" align="center">
  </div>
  <div id="now_playing" style="display: block;">
    <div id="coverdiv">
      <!-- add <a href="https://open.spotify.com/search/nowTitle." ".nowArtist" target="_blank"> -->
      <img src="picts/1x1.png" id="cover" class="image-styled">
    </div>
    <div id="infodiv">
		<div id="infobox">
	    	<div id="title"></div>
    		<div id="artist"></div>
		</div>
	</div>
  </div>
</div>
</body>