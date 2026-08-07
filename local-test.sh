pkill -f '/Users/joey/sites/nuxt/node_modules/.bin/vite' || true
rm -f public/hot
yarn build
nohup yarn dev </dev/null >/tmp/nuxt-vite.log 2>&1 &
herd coverage ./vendor/bin/pest
kill $! && echo "yarn dev has been stopped"
