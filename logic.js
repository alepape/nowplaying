console.log("js loaded");

function displayJson(json) {
    //console.log(json);

    var title = document.getElementById("title");
    var artist = document.getElementById("artist");
    var cover = document.getElementById("cover");
    var radiologo = document.getElementById("radiologo");

    if (!json.title && !json.artist) {
      // null results...
      title.innerHTML = "";
      artist.innerHTML = "";
      cover.style.backgroundImage = "url('picts/logo.png')";
    } else {
      title.innerHTML = json.title;    
      artist.innerHTML = "by: " + json.artist;
      cover.style.backgroundImage = "url("+json.pict+")";
      // cover.src = json.pict;  
    }

    radiologo.src = json.radiologo;
}

function notifyHA() {
    var xhr = new XMLHttpRequest();
    
    xhr.addEventListener("readystatechange", function() {
        if(this.readyState === 4) {
            console.log("HA notified");
        }
    });
    
    xhr.open("GET", "notif.php");
    xhr.send();
}

function checkJson() {
  // WARNING: For GET requests, body is set to null by browsers.

  var xhr = new XMLHttpRequest();
  xhr.withCredentials = true;

  xhr.addEventListener("readystatechange", function() {
    if(this.readyState === 4) {
      json = JSON.parse(this.responseText);
      if (window.currentJson == JSON.stringify(json)) { // TODO: use a hash?
        console.log("no change");
      } else {
        window.currentJson = JSON.stringify(json);
        displayJson(json);
        notifyHA();
      }
    }
  });

  xhr.open("GET", "data.php"+window.location.search);
  xhr.send();
}

checkJson();
setInterval(checkJson, 30000);