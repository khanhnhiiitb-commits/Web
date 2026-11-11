
#!/bin/sh
set -eu

if [ -z "${TUNNEL_SERVICE_TOKEN_ID:-}" ] ||
   [ -z "${TUNNEL_SERVICE_TOKEN_SECRET:-}" ]; then
    echo "Missing Cloudflare Service Token"
    exit 1
fi

echo "Starting Cloudflare TCP listener..."

cloudflared access tcp \
    --hostname db.giveandtake.io.vn \
    --url 127.0.0.1:3307 &

echo "Starting Apache..."
exec apache2-foreground
