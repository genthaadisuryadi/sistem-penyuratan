@props(['title' => null, 'noindex' => false])
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="color-scheme" content="light">
<title>{{ $title ? $title.' - ' : '' }}{{ config('penyuratan.instansi.singkatan') }}</title>
@if ($noindex)
    <meta name="robots" content="noindex, nofollow">
@endif
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
@vite(['resources/css/app.css', 'resources/js/app.js'])
