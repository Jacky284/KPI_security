<!DOCTYPE html>
<html 
    lang="en"
    data-theme-mode="{{ request()->cookie('theme_mode', 'light') }}"
    data-theme-preset="{{ request()->cookie('theme_preset', 'security') }}"
    data-sidebar-variant="{{ request()->cookie('sidebar_variant', 'floating') }}"
    data-sidebar-collapsible="{{ request()->cookie('sidebar_collapsible', 'icon') }}"
    data-font="{{ request()->cookie('font', 'geist') }}"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ config('app.name', 'Studio Admin') }}</title>

        <!-- Barlow & Oswald fonts for a sturdy, flat, and military-like design (no pointy elegant terminals) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Barlow:ital,wght@0,100..0,900;1,100..0,900&family=Oswald:wght@400;700&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx'])
        @inertiaHead
    </head>
    <body class="min-h-screen antialiased">
        @inertia
    </body>
</html>
