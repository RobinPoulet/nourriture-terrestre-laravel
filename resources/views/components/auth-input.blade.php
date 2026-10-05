@props(['name', 'label', 'type' => 'text'])
<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        {{ $label }}
    </label>
    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" required
           {{ $attributes->class([
               'w-full rounded-lg border border-gray-300 dark:border-gray-600
                bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white text-sm
                px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400',
           ]) }}>
</div>
