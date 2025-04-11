# CoverCast

CoverCast enables you to display your music and tv cover from home assistant on a LED 64*64 screen. This was made with the librery rpi-rgb-led-matrix from hzeller, php and imagemagick.

## Hardware 
* Tested with RPI 4 1GB and Zero 2 w
* P3 2121 LED 64*64 screen : https://fr.aliexpress.com/item/32931309452.html
* 5v 3 amp https://fr.aliexpress.com/item/1005005763465796.html
* Matrix Panel Connector : https://www.electrodragon.com/product/rgb-matrix-panel-drive-board-for-raspberry-pi-v2/
* Home Assistant ! 

## Install : 

### On Raspberry : 
* clone this repo in /var/www/html/CoverCast
* cp settings.php.BLANK settings.php
* visudo, add those lines under the root:ALL : 
```
www-data ALL=(ALL) NOPASSWD: /var/www/html/CoverCast/led-image-viewer
```
* apt install php, apache2, imagemagick

### On Home Assistant :
* create a long lived token in user menu / security tab / "Create Token" -> raspberry /CoverCast/get_image.php
* back to raspberry, edit settings.php file and add API url, token and music/tv api url 
* in config.yml, add a notify part :
```
notify:
  - name: covercast
    platform: rest
    resource: http://RASPBERRY_IP/CoverCast/app.php?message={{ message }}
```
* create an automation : 
```
alias: MGMT-COVERCAST
description: ""
triggers:
  - trigger: state
    entity_id:
      - media_player.music
    to: playing
    enabled: true
    id: MUSIC
  - trigger: state
    entity_id:
      - media_player.tv
    to: playing
    id: TV
  - trigger: state
    entity_id:
      - media_player.music
    to: "off"
    enabled: true
    id: KILL
  - trigger: state
    entity_id:
      - media_player.tv
    to: standby
    id: KILL
conditions:
actions:
  - if:
      - condition: trigger
        id:
          - MUSIC
    then:
      - action: notify.covercast
        metadata: {}
        data:
          message: refreshmusic
    alias: MUSIC ON
  - if:
      - condition: trigger
        id:
          - TV
    then:
      - action: notify.covercast
        metadata: {}
        data:
          message: refreshtv
    alias: TV ON
  - alias: KILL
    if:
      - condition: trigger
        id:
          - KILL
    then:
      - action: notify.covercast
        metadata: {}
        data:
          message: kill
mode: single
```

You can add those two scripts, one to force refresh the image and one to shut down the display, when leaving home or bed routine

Refresh Covercast
```
sequence:
  - action: notify.covercast
    metadata: {}
    data:
      message: refreshmusic
alias: REFRESH-COVERCAST
description: ""
```
Kill Covercast
```
sequence:
  - action: notify.covercast
    metadata: {}
    data:
      message: kill
alias: KILL-COVERCAST
description: ""
```

## API Usaege

URL : IP_RASPBERRY/CoverCast/app.php?message?=
* refreshmusic-xx : get the image in the music url in settings and refresh the screen with xx = brightness in 1-99 (refreshmusic-10 for example, default value is 41.)
* refreshtv-xx : get the image in the tv url in settings and refresh the screen with xx = brightness in 1-99 (refreshtv-10 for example, default value is 41.)
* kill : turs off the screen

