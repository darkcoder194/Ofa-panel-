@php
use DarkCoder\Ofa\Services\ThemeManager;
$manager = app(ThemeManager::class);
$palette = $manager->defaultPalette();
@endphp

@if($palette)
<style>
:root {
{!! collect($palette->colors)->map(function($v, $k){ return "--ofa-{$k}: {$v};"; })->implode("\n") !!}
}
</style>
@endif
<link rel="stylesheet" href="{{ asset('css/ofa-theme.css') }}">
