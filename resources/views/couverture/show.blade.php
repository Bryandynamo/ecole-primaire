@extends('layouts.app')
@section('content')
    <div class="table-responsive">
        @include('couverture.show_new', $__data)
    </div>
@endsection
