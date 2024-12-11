<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

// BOOTSTRAP
include 'config.php';

?>
<header>
<!-- <meta http-equiv="refresh" content="30" /> -->
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="style.css">
<script src="logic.js"></script>
<link rel="icon" href="picts/favicon.ico" />

</header>
<body>
<div id="container">
  <div id="logo">
    <img src="<?=$configobj['logo']?>" align="center">
  </div>
  <div id="now_playing" style="display: block;">
    <div id="coverdiv">
      <!-- add <a href="https://open.spotify.com/search/nowTitle." ".nowArtist" target="_blank"> -->
      <img src="" id="cover" class="image-styled">
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