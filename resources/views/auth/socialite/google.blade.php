<x-filament::button :href="route('auth.google')" tag="a" color="info" href="{{ route('auth.google') }}"
    class="inline-flex items-center justify-center gap-2 rounded px-6 py-2.5 font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 hover:shadow-md transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
    onclick="window.location.href=this.href; return false;">
    Sign in with Google
</x-filament::button>
