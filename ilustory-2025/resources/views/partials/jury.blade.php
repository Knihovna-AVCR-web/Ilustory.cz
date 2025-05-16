<div class="container juror">
    <div class="photo">
        @if ($photo)
            {!! wp_get_attachment_image($photo, 'medium', '', [ 'class' => 'my-0', ]) !!}
        @endif
    </div>
    <div class="desc">
    <span class="font-bold">{{ $name }}<br> </span> {{ $short_description }}  <span class="font-small">{!! \App\nonbreakingSpaces($description) !!}</span>
    </div>
</div>
