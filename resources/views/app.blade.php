<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-appearance="{{ $appearance ?? 'system' }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="/theme-bootstrap.js"></script>

    <title inertia>{{ config('app.name', 'Southeast Code') }}</title>

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=open-sans:300,400,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
    @inertiaHead
  </head>
  <body>
    <div class="isolate" data-argon-app>
      @inertia
    </div>
  </body>
</html>
