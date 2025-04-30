@extends('layouts.app')

@section('content')
    <h1>
        {{ the_title() }}
    </h1>
    <hr>
    {{ the_content() }}
@endsection
