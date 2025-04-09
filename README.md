# CoverCast

## Hardware 
* Tested in RPI 4 1GB
* P3 2121 LED 64*64 screen : https://a.aliexpress.com/_EvOfDs0
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
* apt install php, apache2

### On Home Assistant :
* create a long lived token in user menu / security tab / "Create Token" -> raspberry /CoverCast/get_image.php
* in config.yml, add a notify part : 
* back to raspberry, edit settings.php file and add API url, token and music/tv api url 

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


## API Usaege

URL : IP_RASPBERRY/CoverCast/app.php?message?=
* refreshmusic : get the image in the music url in settings and refresh the screen
* refreshtv : get the image in the tv url in settings and refresh the screen
* kill : turs off the screen

