# nowplaying

shows what's playing on supported radios

- FIP (FR)
- Radio Paradise (US)
- KCRW (US)
- KEXP (US)
- Le Bon Mix (FR)
- Radio Neptune (FR)

## Notes

- make sure the main folder can be written by httpd as the main config json needs to be written / created - or assign now.json to daemon user.
- now.php can be forced to display one of the configs: just use now.php?c=<config name> (ex: now.php?c=fip)
- now.php can also be used to provide only the cover image: just use now.php?p=true

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
