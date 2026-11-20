@props(['appSetting' => null])

<!-- PWA Manifest & App Metadata -->
<link rel="manifest" href="/manifest.json">
<meta name="application-name" content="{{ $appSetting?->app_name ?? 'MikroTik Hotspot Manager' }}">
<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#09090b" media="(prefers-color-scheme: dark)">

<!-- Mobile & iOS Safari PWA Support -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ $appSetting?->app_name ?? 'MikroTik' }}">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
<link rel="mask-icon" href="/icons/icon.svg" color="#10b981">
