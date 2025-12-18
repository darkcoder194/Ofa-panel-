@extends('layouts.app')

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div id="ofa-theme-app">
    <!-- Vue app will mount here -->
</div>

<div class="ofa-help">
  <p><strong>Tip:</strong> Use <em>Preview</em> to apply a palette to your session without making it default. Click <em>Clear Preview</em> to return to the default palette.</p>
</div>
@endsection

@push('scripts')
<script>
// Try loading the Vite dev module when available (dev) then fallback to compiled asset
(function(){
    const tryDev = () => {
        const devUrl = `${location.origin}/resources/js/admin/app.js`;
        const s = document.createElement('script');
        s.type = 'module';
        s.src = devUrl;
        s.onload = () => console.log('Loaded OFA admin from Vite dev server');
        s.onerror = () => {
            // fallback to compiled asset
            const f = document.createElement('script');
            f.src = '{{ asset('js/ofa-admin.js') }}';
            f.defer = true;
            document.body.appendChild(f);
        };
        document.body.appendChild(s);
    };

    tryDev();
})();
</script>
@endpush
