@props(['avg'])
<span class="inline-flex gap-0.5">
    @for ($i = 1; $i <= 5; $i++)
        @if ($avg >= $i)
            <i class="bi bi-star-fill text-amber-400"></i>
        @elseif ($avg >= $i - 0.5)
            <i class="bi bi-star-half text-amber-400"></i>
        @else
            <i class="bi bi-star text-gray-300 dark:text-gray-600"></i>
        @endif
    @endfor
</span>
