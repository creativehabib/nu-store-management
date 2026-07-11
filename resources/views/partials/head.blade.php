<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>
    {{ filled($title ?? null) ? $title . ' | ' : '' }}{{ setting('site_name', config('app.name', __('Store Management System'))) }} - {{ __('National University') }}
</title>
<link rel="icon" href="{{ asset(setting('site_favicon') ? 'storage/' . setting('site_favicon') : 'logo.png') }}" sizes="any">
<link rel="icon" href="{{ asset(setting('site_favicon') ? 'storage/' . setting('site_favicon') : 'logo.png') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset(setting('site_favicon') ? 'storage/' . setting('site_favicon') : 'logo.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
