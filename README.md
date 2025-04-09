# CoverCast

## Hardware 

* Tested in RPI 4 1GB
* P3 2121 LED 64*64 screen : https://a.aliexpress.com/_EvOfDs0
* Matrix Panel Connector : https://www.electrodragon.com/product/rgb-matrix-panel-drive-board-for-raspberry-pi-v2/
* Home Assistant ! 

## Install : 

### On Raspberry : 
* clone this repo in /var/www/html/CoverCast
* visudo, add those lines under the root:ALL : 
```
www-data ALL=(ALL) NOPASSWD: /var/www/html/CoverCast/led-image-viewer

```
* apt install php, apache2

### On Home Assistant :
* create a long lived token in user menu / security tab / "Create Token" -> raspberry /CoverCast/get_image.php
* in config.yml, add a notify part : 
```
notify:
  - name: covercast
    platform: rest
    resource: http://192.168.31.190/CoverCast/app.php
```
* create an automation : 
```
alias: MGMT-MUSIC-DISPLAY
description: ""
triggers:
  - trigger: state
    entity_id:
      - media_player.home
    attribute: media_title
  - trigger: state
    entity_id:
      - media_player.spotify
    to: null
    enabled: true
conditions: []
actions:
  - action: notify.covercast
    data:
      message:
mode: single
```


