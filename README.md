# nowplaying

Shows what's playing on supported radios

- FIP (FR)
- Radio Paradise (US)
- KCRW (US)
- KEXP (US)
- Le Bon Mix (FR)
- Radio Neptune (FR)
- TSF (FR)
- Classic Vinyl (US)

3 main URLs can be used:

- /path/ or /path/index.php: display the web version
- /path/data.php is just JSON (used internaly for XMR refresh but can be used by other UIs)
- /path/pict.php is just the cover
- /default.php *sets* the default radio - can be used with Home Assistant to tell the system which radio is playing so that various displays just knows which radio to show

## Notes

- make sure the main folder can be written by httpd as the main config json needs to be written / created - or assign now.json to daemon user.
- all URLs can be forced to display one of the configs: just use index.php?c=<config name> (ex: index.php?c=fip)
- when using the web view, there's a special CSS mode for Google Hub display
- beyons storing the default radio, now.json can store a notification URL, which is called when the current playing title changes (again, great combo with Home Assistant)

## Other tools used

- if no cover is provided, the cover.php uses musicbrainz and cover archive to try and find a good cover
- to show the now playing UI on Google Hub, DashCast can be recommended and works great

## Example configurations

### basic

```
{
    "name": "Radio Paradise",
    "country": "US",
    "logo": "http://www8.radioparadise.com/graphics/logo_flat_350x103.png",
    "nowURL": "https://www8.radioparadise.com/ajax_mx_nowplaying.php",
    "mappings": {
        "nowTitle": "title", // note the simple mapping from the returned object
        "nowArtist": "artist",
        "nowPictURL": "cover"
    }
}
```

### complex mapping (nested attributes)

```
{
    "name": "FIP",
    "country": "FR",
    "logo": "https://s3-eu-west-1.amazonaws.com/static.media.info/l/o/6/6820.1503524658.png",
    "nowURL": "https://api.radiofrance.fr/livemeta/live/7/webrf_fip_player?preset=800x800",
    "mappings": {
        "nowTitle": "now.firstLine", // in this case, the return object has nested attributes - just put in the path.to.the.attribute
        "nowArtist": "now.secondLine",
        "nowPictURL": "now.cover"
    }
}
```

### transformations (search and replace)

```
{
    "name": "KCRW",
    "country": "US",
    "logo": "https://www.kcrw.com/music/shows/eclectic24/@@images/square_image/listing-square",
    "nowURL": "https://tracklist-api.kcrw.com/Music",
    "mappings": {
        "nowTitle": "title",
        "nowArtist": "artist",
        "nowPictURL": "albumImageLarge"
    },
    "transform": {
        "nowPictURL": { // note the block here which instructs the page to search "from" in "nowPictURL" results and replace is by "to"
            "from": "100x100",
            "to": "400x400"
        }
    }
}
```
