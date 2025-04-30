@php
/*
 * Template Name: Porota
 */
@endphp

@extends('layouts.app')

@section('content')
    <div id="content-title" class="hidden">
        <h1>{{ the_title() }}</h1>
        <hr>
    </div>
    {{ the_content() }}
    <style>
    .p{
        margin-left:auto;
        margin-right:auto;
    }
    .photo{
        height: auto;
        width: 250px;
        filter: drop-shadow(8px 8px 10px gray);
    }
    .container{
        width: 100%;
        height: auto;
        display: flex;
        margin-top: 10%;
        margin-bottom: 10%;
    }
    .desc{
        margin-left: 3%;
        width: calc(100% - 250px);
    }
    .font-small{
        font-size: small;
    }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mainContentElm = document.querySelector('main > div > div');

            if (!mainContentElm || mainContentElm.querySelector('h1')) return;

            const contentTitleElm = document.getElementById('content-title');

            if (contentTitleElm) {
                contentTitleElm.remove();
                contentTitleElm.classList.remove('hidden');
                mainContentElm.prepend(contentTitleElm);
            }
        });
    </script>
@endsection