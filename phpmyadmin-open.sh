#!/bin/bash
# Open phpMyAdmin for this site's database, logged in.
#
# Committed on purpose: this file holds no secret. The credentials are read
# at run time from the server's classes/Config/Config.php over ssh, using the
# same DEST and DEST_PATH that deploy_to_dh.sh beside it has filled in (so a
# site needs no further setup for this to work). The template repo itself
# keeps DEST="example" committed and refuses to run, like deploy_to_dh.sh.
#
# The credentials pass through one temp HTML file holding an auto-submitting
# form, made by mktemp (mode 600) and removed two seconds after the browser
# has been handed it; the password never goes on the command line.
set -e

cd "$(dirname "$0")"

# Read, don't source: deploy_to_dh.sh pushes and pulls when run.
DEST=$(sed -n 's/^DEST="\([^"]*\)".*/\1/p' deploy_to_dh.sh | head -1)
DEST_PATH=$(sed -n 's/^DEST_PATH="\([^"]*\)".*/\1/p' deploy_to_dh.sh | head -1)

if [ -z "$DEST" ] || [ "$DEST" = "example" ] || [ -z "$DEST_PATH" ]; then
    echo "Fill in DEST and DEST_PATH in deploy_to_dh.sh first." >&2
    exit 1
fi

# DEST_PATH is the project root on the server, relative to the user's home.
REMOTE_CONFIG="$DEST_PATH/classes/Config/Config.php"

echo "Fetching credentials from $DEST..."

# Read Config.php once. The ssh Host already carries the username and key.
CONFIG=$(ssh "$DEST" "cat $REMOTE_CONFIG")

# Config.php may quote values with ' or ", so accept either.
field () {
    printf '%s\n' "$CONFIG" | sed -n "s/.*\\\$$1 *= *['\"]\([^'\"]*\)['\"].*/\1/p" | head -1
}

DB_USER=$(field dbUser)
DB_PASS=$(field dbPass)
SERVER_NAME=$(field dbHost)

if [ -z "$DB_USER" ] || [ -z "$DB_PASS" ] || [ -z "$SERVER_NAME" ]; then
    echo "Error: could not read dbUser, dbPass and dbHost from $DEST:$REMOTE_CONFIG" >&2
    exit 1
fi

# DreamHost's phpMyAdmin signon; pma_servername is the site's own MySQL host.
PHPMYADMIN_URL="https://west1-phpmyadmin.dreamhost.com/signon.php?lang=en"

TEMP_HTML=$(mktemp /tmp/phpmyadmin-login-XXXXXX.html)

cat > "$TEMP_HTML" << EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging into phpMyAdmin...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .container {
            text-align: center;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Logging into phpMyAdmin...</h1>
        <div class="spinner"></div>
        <p>Please wait while we redirect you...</p>
    </div>

    <form id="loginForm" method="POST" action="$PHPMYADMIN_URL">
        <input type="hidden" name="pma_username" value="$DB_USER">
        <input type="hidden" name="pma_password" value="$DB_PASS">
        <input type="hidden" name="pma_servername" value="$SERVER_NAME">
        <input type="hidden" name="server" value="1">
        <input type="hidden" name="lang" value="en">
    </form>

    <script>
        window.onload = function() {
            document.getElementById('loginForm').submit();
        };
    </script>
</body>
</html>
EOF

echo "Opening phpMyAdmin in your browser..."
xdg-open "$TEMP_HTML"

# Give the browser time to read the file, then remove it.
sleep 2
rm -f "$TEMP_HTML"

echo "Done. phpMyAdmin should be opening for $SERVER_NAME."
