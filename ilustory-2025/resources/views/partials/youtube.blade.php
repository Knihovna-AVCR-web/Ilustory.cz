<figure class="mb-4">
    <lite-youtube videoid="{{ $videoId }}"
        params="modestbranding=2&noCookie=true&rel=0&showinfo=0&iv_load_policy=3&cc_load_policy=1&vq=hd1080">
    </lite-youtube>
    @if ($description)
        <figcaption class="pb-1 mt-1">
            {{ $description }}
        </figcaption>
    @endif
</figure>
