@php
/*
 * Template Name: Ověření emailu
 */
@endphp

@extends('layouts.app')

@section('content')
    <h1>
        {{ $title }}
    </h1>
    <hr>
    <div id="alert" class="max-w-sm">
        <div class="p-3 {{ $verification['success'] ? 'text-green-800 bg-green-100' : 'text-red-800 bg-red-100' }}" role="alert">
            <p class="my-0 font-semibold">
                {{ $verification['message'] }}
            </p>
        </div>
    </div>
@endsection
