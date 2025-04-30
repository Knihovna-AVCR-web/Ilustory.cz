</main>
<footer class="flex flex-col px-6 py-5 mt-auto text-white bg-gray-900 xl:py-12">
    <ul class="flex flex-wrap items-center justify-between mb-6 gap-x-8 gap-y-4">
        <li>
            <a href="https://www.avcr.cz/" target="_blank" title="{{ __('Akademie věd ČR', 'ilustory') }}">
                <img src="{{ \App\imageUrl('logo/avcr.png') }}" loading="lazy"
                    alt="{{ __('Akademie věd ČR', 'ilustory') }}" class="h-16 sm:h-28">
            </a>
        </li>
        <li>
            <a href="https://knav.cz/" target="_blank" title="{{ __('Knihovna AV ČR, v. v. i.', 'ilustory') }}">
                <img src="{{ \App\imageUrl('logo/knav.svg') }}" loading="lazy"
                    alt="{{ __('Knihovna AV ČR, v. v. i.', 'ilustory') }}" class="h-16 sm:h-28">
            </a>
        </li>
        <li>
            <p class="font-semibold uppercase">
                {{ __('Kontakt', 'ilustory') }}
            </p>
            <ul>
                <li>
                    {{ __('Knihovna AV ČR, v. v. i.', 'ilustory') }}
                </li>
                <li>
                    {{ __('Národní 3, 110 00 Praha 1', 'ilustory') }}
                </li>
                <li>
                    <a href="https://knav.cz" target="_blank">
                        https://knav.cz
                    </a>
                </li>
                <li class="mt-3 font-semibold">
                    E-mail: {!! \App\mailtoLink('pr@knav.cz', '') !!}
                </li>
            </ul>
        </li>
    </ul>
    <div class="flex flex-wrap items-baseline justify-between">
        <p>
            &copy; {{ date('Y') }}, <a href="https://knav.cz" target="_blank"
                class="mt-4 mb-2 mr-4">{{ __('Knihovna AV ČR, v. v. i.', 'ilustory') }}</a>
        </p>
    </div>
</footer>
