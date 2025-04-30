@foreach ($advanced_excerpts as $key => $page)
    <article class="{{ $key != 0 ? 'pt-3' : '' }} mb-2">
        <h3 class="text-2xl">
            {{ get_the_title($page['id']) }}
        </h3>
        {!! apply_filters('the_content', carbon_get_post_meta($page['id'], 'advanced_excerpt')) !!}
        <a href="{{ get_the_permalink($page['id'])  }}" class="font-semibold">
            <?= __('Chci vědět víc &raquo;', 'ilustory') ?>
        </a>
    </article>
@endforeach
