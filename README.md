# CoverCast

CoverCast enables you to display your music and TV cover art from Home Assistant on a LED 64×64 screen. This project uses the rpi-rgb-led-matrix library from hzeller, PHP, and ImageMagick.

## Hardware
* Tested with Raspberry Pi 4 (1GB) and Zero 2 W
* P3 2121 LED 64×64 screen: https://fr.aliexpress.com/item/32931309452.html
* 5V 3A power supply: https://fr.aliexpress.com/item/1005005763465796.html
* Home Assistant!

## Installation

### On Raspberry Pi

1. Clone this repository:
```bash
cd /var/www/html
git clone https://github.com/alfredit/CoverCast.git
git checkout webhook
```

2. Install dependencies:
```bash
sudo apt update
sudo apt install php apache2 imagemagick
```

3. Configure sudo permissions for the LED viewer:
```bash
sudo visudo
```
Add this line under `root ALL=(ALL:ALL) ALL`:
```
www-data ALL=(ALL) NOPASSWD: /var/www/html/CoverCast/led-image-viewer
```

4. Configure Apache VirtualHost with environment variables:

Edit your VirtualHost configuration (e.g., `/etc/apache2/sites-available/000-default.conf`):

```apache
<VirtualHost *:80>
    DocumentRoot /var/www/html/CoverCast
    
    # Webhook secret for authentication (generate a random string)
    SetEnv COVERCAST_WEBHOOK_SECRET "your-random-secret-key-here"
    
    # Installation folder
    SetEnv COVERCAST_FOLDER "/var/www/html/CoverCast"
</VirtualHost>
```

5. Reload Apache:
```bash
sudo systemctl reload apache2
```

### Home Assistant Configuration

#### 1. Create a RESTful Command

Add to your `configuration.yaml`:

```yaml
rest_command:
  covercast_display:
    url: "http://RASPBERRY_IP/CoverCast/webhook.php"
    method: POST
    headers:
      X-Webhook-Secret: "your-random-secret-key-here"
      Content-Type: "application/json"
    payload: >
      {
        "action": "display",
        "brightness": {{ brightness | default(31) }},
        "image": "{{ image_base64 }}"
      }
  covercast_kill:
    url: "http://RASPBERRY_IP/CoverCast/webhook.php"
    method: POST
    headers:
      X-Webhook-Secret: "your-random-secret-key-here"
      Content-Type: "application/json"
    payload: '{"action": "kill"}'
```

#### 2. Create a Helper for Brightness

Settings → Devices & Services → Helpers → Create Helper
- Type: Number
- Name: `covercast_brightness`
- Min: 1, Max: 99, Step: 1

#### 3. Create Automations

**Automation 1: Display cover art**

```yaml
alias: CoverCast - Display Cover
mode: single
trigger:
  - platform: state
    entity_id:
      - media_player.YOUR_MEDIA_PLAYER
    to: playing
action:
  - delay:
      hours: 0
      minutes: 0
      seconds: 3
      milliseconds: 0
  - service: rest_command.covercast_display
    data:
      brightness: "{{ states('input_number.covercast_brightness') | int }}"
      image_base64: "{{ state_attr('media_player.YOUR_MEDIA_PLAYER', 'entity_picture_local') | regex_replace('^data:image/[^;]+;base64,', '') }}"
```

**Note:** If `entity_picture_local` is not available, you may need to download the image and encode it as base64 using a shell command or template sensor.

**Alternative using shell command:**

```yaml
shell_command:
  covercast_send_cover: >
    curl -X POST http://RASPBERRY_IP/CoverCast/webhook.php
    -H "X-Webhook-Secret: your-random-secret-key-here"
    -H "Content-Type: application/json"
    -d '{"action":"display","brightness":{{ brightness }},"image":"'$(curl -s http://YOUR_HA_IP:8123{{ state_attr("media_player.YOUR_MEDIA_PLAYER", "entity_picture") }} | base64 -w 0)'"}'
```

**Automation 2: Turn off display**

```yaml
alias: CoverCast - Turn Off
mode: single
trigger:
  - platform: state
    entity_id:
      - media_player.YOUR_MEDIA_PLAYER
    to: "off"
  - platform: state
    entity_id:
      - media_player.YOUR_MEDIA_PLAYER
    to: "idle"
action:
  - service: rest_command.covercast_kill
```

**Automation 3: Brightness schedule**

```yaml
alias: CoverCast - Set Day Brightness
mode: single
trigger:
  - platform: sun
    event: sunrise
action:
  - service: input_number.set_value
    target:
      entity_id: input_number.covercast_brightness
    data:
      value: 40
```

```yaml
alias: CoverCast - Set Night Brightness
mode: single
trigger:
  - platform: sun
    event: sunset
action:
  - service: input_number.set_value
    target:
      entity_id: input_number.covercast_brightness
    data:
      value: 15
```

## Webhook API

### Endpoint
`POST http://RASPBERRY_IP/CoverCast/webhook.php`

### Headers
| Header | Value |
|--------|-------|
| `X-Webhook-Secret` | Your configured secret |
| `Content-Type` | `application/json` |

### Actions

#### Display Image
```json
{
  "action": "display",
  "brightness": 40,
  "image": "base64encodedimagedata..."
}
```

- `action` (required): `"display"`
- `brightness` (optional): Integer 1-99 (default: 31)
- `image` (required): Base64-encoded image data (JPEG/PNG)

#### Kill Display
```json
{
  "action": "kill"
}
```

- `action` (required): `"kill"`

### Responses

**Success:**
```json
{
  "success": true,
  "action": "display",
  "brightness": 40,
  "image_size": 45230
}
```

**Error:**
```json
{
  "error": "Unauthorized",
  "message": "Invalid or missing webhook secret"
}
```

### Response Codes
- `200`: Success
- `400`: Bad Request (invalid JSON, missing parameters)
- `401`: Unauthorized (invalid secret)
- `500`: Internal Server Error

## Testing

Test the webhook with curl:

```bash
# Display test (replace with actual base64 image)
curl -X POST http://RASPBERRY_IP/CoverCast/webhook.php \
  -H "X-Webhook-Secret: your-secret" \
  -H "Content-Type: application/json" \
  -d '{"action":"display","brightness":40,"image":"base64data..."}'

# Kill display
curl -X POST http://RASPBERRY_IP/CoverCast/webhook.php \
  -H "X-Webhook-Secret: your-secret" \
  -H "Content-Type: application/json" \
  -d '{"action":"kill"}'
```

## Troubleshooting

1. **Images not displaying:**
   - Check Apache error logs: `sudo tail -f /var/log/apache2/error.log`
   - Verify webhook secret matches between Apache config and Home Assistant
   - Ensure `www-data` user has sudo access to `led-image-viewer`

2. **Permission errors:**
   - Verify sudoers entry is correct
   - Check file permissions in `/var/www/html/CoverCast`

3. **Home Assistant not triggering:**
   - Test webhook with curl first
   - Check Home Assistant logs
   - Verify REST commands are properly formatted
