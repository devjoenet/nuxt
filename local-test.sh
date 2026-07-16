pkill -f '/Users/joey/sites/nuxt/node_modules/.bin/vite' || true
rm -f public/hot
npm run build
nohup npm run dev </dev/null >/tmp/nuxt-vite.log 2>&1 &
herd coverage ./vendor/bin/pest
kill $! && echo "npm run dev has been has been stopped"
