# CoverCast

## Hardware 

* Tested in RPI 4 1GB
* P3 2121 LED screen : https://a.aliexpress.com/_EvOfDs0
* Matrix Panel Connector : https://www.electrodragon.com/product/rgb-matrix-panel-drive-board-for-raspberry-pi-v2/
* Home Assistant ! 

## Install : 

### On Raspberry : 
* clone this repo in /CoverCast
* visudo, add those lines under the root:ALL : 
```
www-data ALL=(ALL) NOPASSWD: /CoverCast/music.sh
www-data ALL=(ALL) NOPASSWD: /CoverCast/led-image-viewer
```
* apt install php, apache2
* cp /CoverCast/listen.php /var/www/html/

### On Home Assistant :
* create a long lived token in user menu / security tab / "Create Token"
* in config.yml, add a notify part : 
```
notify:
  - name: musicdisplay
    platform: rest
    resource: http://192.168.31.190/listen.php
```
* create an automation : 
```
alias: MGMT-MUSIC-DISPLAY
description: ""
triggers:
  - trigger: state
    entity_id:
      - media_player.maison
    attribute: media_title
  - trigger: state
    entity_id:
      - media_player.spotify_alfredit
    to: null
    enabled: true
conditions: []
actions:
  - action: notify.musicdisplay
    data:
      message:
mode: single
```


