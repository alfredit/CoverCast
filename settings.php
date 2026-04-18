<?php

// Configuration is read from Apache environment variables (SetEnv in VirtualHost)
// Fallback to empty strings if not set

// API URL OF THE MUSIC MEDIA PLAYER
$ha_url_music = $_SERVER['COVERCAST_HA_URL_MUSIC'] ?? '';

// API URL OF THE TV MEDIA PLAYER IF EXIST
$ha_url_tv = $_SERVER['COVERCAST_HA_URL_TV'] ?? '';

// TOKEN TO GET IN HOME ASSISTANT CF README
$long_lived_access_token = $_SERVER['COVERCAST_TOKEN'] ?? '';

// FOLDER TO ACCESS THE COVERCAST INSTALL
$folder = $_SERVER['COVERCAST_FOLDER'] ?? '';
