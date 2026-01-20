<div class="flex items-center gap-4">
    <a href="{{ route('lang.switch', 'id') }}" class="text-sm font-medium {{ app()->getLocale() === 'id' ? 'text-primary-600 font-bold' : 'text-gray-500 hover:text-gray-700' }}">
        ID
    </a>
    <span class="text-gray-300">|</span>
    <a href="{{ route('lang.switch', 'en') }}" class="text-sm font-medium {{ app()->getLocale() === 'en' ? 'text-primary-600 font-bold' : 'text-gray-500 hover:text-gray-700' }}">
        EN
    </a>
</div>