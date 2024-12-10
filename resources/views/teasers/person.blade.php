<div class="person-teaser" data-aos="fade-in">
    <div class="image">
        <a href="{{ $person->link }}">
            <img src="{{ $person->teaser }}" alt="{{ $person->name }}"/>
        </a>
    </div>
    <div class="title">
        <a href="{{ $person->link }}">
            {{ $person->name_without_middle }}
        </a>
    </div>
    <div class="position">{{ $person->role ? $person->role->title : $person->position }}</div>
</div>
