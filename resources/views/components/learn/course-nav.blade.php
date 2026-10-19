{{-- Course contents navigation for the lesson player (used in the desktop sidebar and mobile drawer). --}}
@props(['course', 'module', 'lesson', 'slug'])
<nav aria-label="Course contents" class="space-y-5">
    @foreach ($course['modules'] as $m)
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide {{ $m->id === $module->id ? 'text-accent' : 'text-subtle' }}">{{ $m->position }}. {{ $m->title }}</p>
            <ul class="mt-2 space-y-0.5">
                @foreach ($m->lessons as $l)
                    <li><a href="{{ route('learn.lesson', [$slug, $l->id]) }}" @if ($l->id === $lesson->id) aria-current="page" @endif
                        class="flex items-start gap-2 rounded-md px-2 py-1.5 text-sm {{ $l->id === $lesson->id ? 'bg-elevated-2 text-text' : 'text-muted hover:bg-elevated hover:text-text' }}">
                        <x-icon :name="$l->done ? 'check-circle-2' : 'circle'" :label="$l->done ? 'Completed' : 'Not completed'" class="mt-0.5 size-3.5 shrink-0 {{ $l->done ? 'text-success' : 'text-subtle' }}" />{{ $l->title }}
                    </a></li>
                @endforeach
                @foreach ($m->labs as $l)
                    <li><a href="{{ route('learn.lab', [$slug, $l->slug]) }}" class="flex items-start gap-2 rounded-md px-2 py-1.5 text-sm text-muted hover:bg-elevated hover:text-text">
                        <x-icon name="flask-conical" class="mt-0.5 size-3.5 shrink-0 {{ $l->passed ? 'text-success' : 'text-accent' }}" />Lab: {{ $l->title }}
                    </a></li>
                @endforeach
            </ul>
        </div>
    @endforeach
</nav>
