# nowplaying

shows what's playing on supported radios

- FIP (FR)
- Radio Paradise (US) - in progress

## Notes

- make sure the main folder can be written by httpd as the main config json needs to be written / created - or assign now.json to daemon user.
- now.php can be forced to display one of the configs: just use now.php?c=<config name> (ex: now.php?c=fip)
