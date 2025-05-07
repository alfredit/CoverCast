# CoverCast

CoverCast enables you to display your music and tv cover from home assistant on a LED 64*64 screen. This was made with the librery rpi-rgb-led-matrix from hzeller, php and imagemagick.

## Hardware 
* Tested with RPI 4 1GB and Zero 2 w
* P3 2121 LED 64*64 screen : https://fr.aliexpress.com/item/32931309452.html
* 5v 3 amp https://fr.aliexpress.com/item/1005005763465796.html
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
* create a helper : Settings -> devices -> helpers -> type number, name "covercast-brightness" between 1-99. It will set the brightness value for day and eco setting in the automation
* back to raspberry, edit settings.php file and add API url, token and music/tv api url 
* in config.yml, add a notify part :
```
notify:
  - name: covercast
    platform: rest
    resource: http://RASPBERRY_IP/CoverCast/app.php?message={{ message }}
```
* create an automation, you may edit the day and eco value of the brihtness and the name of your media_players : 
```
alias: MGMT-COVERCAST
description: ""
triggers:
  - trigger: state
    entity_id:
      - media_player.maison
    to: playing
    enabled: true
    id: MUSIC
  - trigger: state
    entity_id:
      - media_player.sejour
    to: playing
    id: TV
  - trigger: state
    entity_id:
      - media_player.maison
    to: "off"
    enabled: true
    id: KILL
  - trigger: state
    entity_id:
      - media_player.sejour
    to: standby
    id: KILL
  - trigger: sun
    event: sunrise
    offset: 0
    alias: brightness-day
    id: brightness-day
  - alias: brightness-eco
    trigger: sun
    event: sunset
    offset: 0
    id: brightness-eco
conditions:
  - condition: state
    entity_id: input_boolean.anyone_home
    state: "on"
actions:
  - alias: MUSIC ON
    if:
      - condition: trigger
        id:
          - MUSIC
    then:
      - delay:
          hours: 0
          minutes: 0
          seconds: 5
          milliseconds: 0
      - action: notify.covercast
        metadata: {}
        data:
          message: refreshmusic-{{states.input_number.covercast_brightness.state}}
  - alias: TV ON
    if:
      - condition: trigger
        id:
          - TV
    then:
      - delay:
          hours: 0
          minutes: 0
          seconds: 5
          milliseconds: 0
      - action: notify.covercast
        metadata: {}
        data:
          message: refreshtv-{{states.input_number.covercast_brightness.state}}
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
  - if:
      - condition: trigger
        id:
          - brightness-day
    then:
      - action: input_number.set_value
        target:
          entity_id: input_number.covercast_brightness
        data:
          value: 40
    alias: Brightness-day
  - alias: Brightness-eco
    if:
      - condition: trigger
        id:
          - brightness-eco
    then:
      - action: input_number.set_value
        target:
          entity_id: input_number.covercast_brightness
        data:
          value: 15
mode: single
```

You can add those two scripts, one to force refresh the image and one to shut down the display, when leaving home or bed routine

Refresh Covercast
```
sequence:
  - action: notify.covercast
    metadata: {}
    data:
      message: refreshmusic-{{states.input_number.covercast_brightness.state}}
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

URL : http://IP_RASPBERRY/CoverCast/app.php?message?=XXXXXXXXX
* refreshmusic-xx : get the image in the music url in settings and refresh the screen with xx = brightness in 1-99 (refreshmusic-10 for example, default value is 41.)
* refreshtv-xx : get the image in the tv url in settings and refresh the screen with xx = brightness in 1-99 (refreshtv-10 for example, default value is 41.)
* kill : turs off the screen

