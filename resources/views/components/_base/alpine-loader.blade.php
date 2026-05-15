{{-- Alpine.js loader. See src/Blade/Components/AlpineLoader.php and
     docs/tools/alpine-loader.md for the contract. --}}
<script>
    (function () {
        if (typeof window.Alpine !== 'undefined') {
            return; // Already loaded — let the consumer's Alpine win.
        }
        var script = document.createElement('script');
        script.defer = true;
@if ($cdn)
        script.src = {!! json_encode($cdnUrl, JSON_UNESCAPED_SLASHES) !!};
@if ($integrity !== '')
        script.integrity = {!! json_encode($integrity, JSON_UNESCAPED_SLASHES) !!};
        script.crossOrigin = 'anonymous';
@endif
        script.onerror = function () {
            var fallback = document.createElement('script');
            fallback.defer = true;
            fallback.src = {!! json_encode($localUrl, JSON_UNESCAPED_SLASHES) !!};
            document.head.appendChild(fallback);
        };
@else
        script.src = {!! json_encode($localUrl, JSON_UNESCAPED_SLASHES) !!};
@endif
        document.head.appendChild(script);
    })();
</script>
