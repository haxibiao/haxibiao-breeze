@extends('layouts.blank')
@section('title')写文章 - @stop
@section('content')
    <write></write>
@stop

@push('js')
    <script>
        $(function() {
            $('.dropdown-toggle').dropdown();
            $('[data-toggle="tooltip"]').tooltip();
            $('[data-toggle="popover"]').popover();
        })
    </script>
@endpush
