<div class="relative rounded-2xl overflow-hidden shadow-xl bg-gray-100 dark:bg-gray-700">
    <img
            src="{{ asset('assets/IMG/'.$menu->img_src) }}"
            alt="Photo du menu"
            class="{{ $imageClass }}"
            @if ($imageStyle) style="{{ $imageStyle }}" @endif
    >
    @if (!empty($menu->img_figcaption))
        <div class="absolute bottom-0 left-0 right-0
                    bg-gradient-to-t from-black/70 to-transparent px-5 py-4">
            <p class="text-white text-sm italic opacity-90">
                {{ $menu->img_figcaption }}
            </p>
        </div>
    @endif
</div>
