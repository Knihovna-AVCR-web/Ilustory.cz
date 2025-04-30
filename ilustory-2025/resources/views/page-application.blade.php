@php
/*
 * Template Name: Přihláška
 */
@endphp

@extends('layouts.app')

@section('content')
    <h1>
        {{ $title }}
    </h1>
    <hr>
    {{ the_content() }}
    @if ($active)
        <div id="alert" class="max-w-sm">
            @if ((isset($_SESSION['ilustory-2025']['subscribed']) && $_SESSION['ilustory-2025']['subscribed'] != '1') || (isset($_GET['subscribed']) && $_GET['subscribed'] != 'true'))
                <div class="p-3 mb-3 text-red-800 bg-red-100" role="alert">
                    @if (isset($_SESSION['ilustory-2025']['subscribed']) && is_array($_SESSION['ilustory-2025']['subscribed']))
                        <p class="my-0 font-semibold">
                            {{ __('Formulář nebyl odeslán:', 'ilustory') }}
                        </p>
                        <ul class="my-1 text-sm">
                            @foreach ($_SESSION['ilustory-2025']['subscribed'] as $key => $errors)
                                @foreach ($errors as $error)
                                    <li>
                                        @if (empty($key))
                                            {{ $error }}
                                        @else
                                            <a href="#<?= $key ?>"><?= $error ?></a>
                                        @endif
                                    </li>
                                @endforeach
                            @endforeach
                        </ul>
                    @elseif (isset($_SESSION['ilustory-2025']['subscribed']))
                        <p class="my-0 font-semibold">
                            {{ $_SESSION['ilustory-2025']['subscribed'] }}
                        </p>
                    @elseif (isset($_GET['subscribed']))
                        <p class="my-0 font-semibold">
                            {{ $_GET['subscribed'] }}
                        </p>
                    @endif
                </div>
            @endif
            @if ((isset($_SESSION['ilustory-2025']['subscribed']) && $_SESSION['ilustory-2025']['subscribed'] == '1') || (isset($_GET['subscribed']) && $_GET['subscribed'] == 'true'))
                <div class="p-3 mb-3 text-green-800 bg-green-100" role="alert">
                    <p class="my-0 font-semibold">
                        {{ __('Na uvedený e-mail byl odeslán ověřovací odkaz. Po ověření bude dokončeno přihlášení Vaší soutěžní povídky. Pokud e-mail neobdržíte do 24 hodin, zkuste se podívat do odfiltrovaných e-mailových zpráv. Pokud ani tam e-mail nenajdete, obraťte se na nás: pr@knav.cz','ilustory') }}
                    </p>
                </div>
                <style>
                    #submission-form {
                        display: none;
                    }
                </style>
            @endif
            @php
                unset($_SESSION['ilustory-2025']['subscribed']);
            @endphp
        </div>
        <form class="max-w-sm space-y-4" action="{{ admin_url('admin-post.php') }}" method="post"
            enctype="multipart/form-data" id="submission-form">
            <div id="fullname">
                <label>
                    <span class="text-sm font-semibold">{{ __('Jméno a příjmení', 'ilustory') }}</span>
                    <input type="text" name="fullname" class="block w-full px-2 py-1 mt-1 border border-gray-300"
                        value="{{ \App\old('fullname') }}" required>
                </label>
            </div>
            <div id="email">
                <label>
                    <span class="text-sm font-semibold">{{ __('E-mailová adresa', 'ilustory') }}</span>
                    <input type="email" name="email" class="block w-full px-2 py-1 mt-1 border border-gray-300"
                        value="{{ \App\old('email') }}" required>
                </label>
                <p class="my-1 text-sm text-gray-600 ">
                    {{ __('Jeden přihlášený může soutěžit pouze s jednou povídkou, a tedy jedna e-mailová adresa umožňuje odeslání jedné povídky. Proto musíme ověřit, že Váš e-mail existuje, Vy jste jeho oprávněný majitel/ka a dáváte souhlas se soutěžními pravidly.','ilustory') }}
                </p>
            </div>
            <div id="phone">
                <label>
                    <span class="text-sm font-semibold">{{ __('Telefonní číslo', 'ilustory') }}</span>
                    <input type="tel" name="phone" minlength="9" pattern="[0-9]+" title="vložte pouze čísla"
                        class="block w-full px-2 py-1 mt-1 border border-gray-300" value="{{ \App\old('phone') }}" required>
                </label>
            </div>
            <div id="age">
                <label>
                    <span class="text-sm font-semibold">{{ __('Věk', 'ilustory') }}</span>
                    <input type="number" name="age" min="3" max="120"
                        class="block w-full px-2 py-1 mt-1 border border-gray-300" value="{{ \App\old('age') }}"
                        required>
                </label>
            </div>
            <div id="story">
                <label>
                    <span class="text-sm font-semibold">{{ __('Název povídky', 'ilustory') }}</span>
                    <input type="text" name="story" class="block w-full px-2 py-1 mt-1 border border-gray-300"
                        value="{{ \App\old('story') }}" required>
                </label>
            </div>
            <div id="story-file">
                <label>
                    <span
                        class="text-sm font-semibold">{{ __('Nahrajte povídku ve formátu doc, docx, pdf, txt nebo odt o maximální velikosti 2 MB.', 'ilustory') }}</span>
                    <input type="file" name="story-file"
                        accept="application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword,application/pdf,text/plain,application/vnd.oasis.opendocument.text"
                        required>
                </label>
            </div>
            <div id="note">
                <label>
                    <span class="text-sm font-semibold">{{ __('Místo pro Váš komentář (nepovinné)', 'ilustory') }}</span>
                    <textarea name="note" class="block w-full px-2 py-1 mt-1 border border-gray-300">{{ \App\old('note') }}</textarea>
                    <p class="my-1 text-sm text-gray-600 ">
                        {{ __('Chtěli byste k povídce připojit ještě nějaký komentář? Nebo nám potřebujete něco vzkázat?', 'ilustory') }}
                    </p>
                </label>
            </div>
            <div id="consent">
                <label class="inline-flex items-center">
                    <input name="consent" type="checkbox" class="border-gray-300" required>
                    <span class="ml-2 text-sm font-semibold">
                        {!! sprintf(__('Seznámil/a jsem se s %s pravidly soutěže %sa souhlasím se zpracováním osobních údajů.', 'ilustory'), '<a target="_blank" href="https://ilustory.cz/2025/pravidla-souteze">', '</a>') !!}
                    </span>
                </label>
            </div>
            <input type="hidden" name="action" value="submit_story">
            <input type="hidden" name="current_url" value="{{ $currentUrl }}">
            <input type="checkbox" name="fax" type="checkbox" tabindex="-1" autocomplete="off" value="1"
                style="display: none;">
            <button class="submit-story">
                {{ __('Odeslat přihlášku', 'ilustory') }}
            </button>
        </form>
    @endif
@endsection


 <style>
.submit-story{
    background-color: #BF6D65; 
    color: white; 
    font-size: 16px; 
    font-weight: bold; 
    padding: 12px 24px; 
    border: none; 
    border-radius: 50px; 
    cursor: pointer; 
    display: inline-block;
    text-decoration: underline; 
    transition: all 0.3s ease-in-out;
}
.submit-story:hover{
    color: black;
}
 </style>
