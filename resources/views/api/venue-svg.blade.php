<svg class="venue-svg"
     ref="venue"
     id="venueSvg"
     style="width: {{ $venueScheme->width }}px; height: {{ $venueScheme->height }}px;"
     viewBox="`0 0 {{$venueScheme->width}} {{$venueScheme->height}}`">
    @foreach($venueScheme->sections as $section)
        <g class="section">
            @if(($section->svg['custom'] ?? null))
                <g>{!! $section->svg->custom !!}</g>
            @else
                <path
                        stroke="black"
                        stroke-width="0"
                        fill="{{ $section->svg['color'] }}"
                        d="{{ transformSVGPoints($section->svg['points']) }}"></path>
                @if($section->show_title && $section->title)
                    <text text-anchor="middle"
                          dominant-baseline="central"
                          transform="rotate({{ $section->svg['text'][2] ?? 0 }}, {{ $section->svg['text'][0] }}, {{ $section->svg['text'][1] }})"
                          style="fill: {{ $section->title_color ?: '#000' }}"
                          x="{{ $section->svg['text'][0] }}"
                          y="{{ $section->svg['text'][1] }}">
                        {{ $section->title }}
                    </text>
                @endif
            @endif
        </g>
    @endforeach
</svg>
