<!-- Dynamic Logo -->
@php
    $logo = \App\Models\GlobalSetting::where('key', 'logo')->value('value');
    $siteName = \App\Models\GlobalSetting::where('key', 'site_name')->value('value') ?? 'ANALOGUE';
@endphp

<div class="flex items-center justify-center space-x-2" {{ $attributes }}>
    <div class="flex-shrink-0 h-full flex items-center">
        @if($logo)
            <img src="{{ asset($logo) }}" alt="{{ $siteName }}" class="h-full w-auto object-contain">
        @else
            <!-- Fallback Logo -->
            <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-blue-700 rounded-full flex items-center justify-center shadow-md">
                <span class="text-white font-bold text-sm">{{ substr($siteName, 0, 1) }}</span>
            </div>
            <div class="flex flex-col">
                <span class="text-lg font-bold text-gray-800 dark:text-gray-200 leading-none">{{ $siteName }}</span>
                <span class="text-xs text-gray-600 dark:text-gray-400 leading-none">IT Solutions</span>
            </div>
        @endif
</div>
