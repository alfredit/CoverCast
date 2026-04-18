<?php

// Configuration is read from Apache environment variables (SetEnv in VirtualHost)
// Fallback to empty strings if not set

// WEBHOOK SECRET (required for webhook authentication)
$webhook_secret = $_SERVER['COVERCAST_WEBHOOK_SECRET'] ?? '';

// FOLDER TO ACCESS THE COVERCAST INSTALL
$folder = $_SERVER['COVERCAST_FOLDER'] ?? '';
