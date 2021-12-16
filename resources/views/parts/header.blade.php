@if (Auth::check())
    @include('parts.header_user')
@else
    @include('parts.header_guest')
@endif

@push('js')
    @include('parts.js_for_header')
@endpush
