<div class="flex items-center gap-4 my-2">
    <div class="flex-1 border-t border-gray-200 dark:border-white/10"></div>
    <p class="text-xs font-medium tracking-wide text-gray-500 dark:text-gray-400 uppercase whitespace-nowrap">
        or continue with
    </p>
    <div class="flex-1 border-t border-gray-200 dark:border-white/10"></div>
</div>

<div class="flex justify-center w-full" x-data="{ isLoading: false }" @pageshow.window="isLoading = false">
    <a href="{{ route('auth.google') }}"
        x-on:click="isLoading = true"
        :class="{ 'opacity-80 pointer-events-none': isLoading }"
        class="relative flex items-center justify-center gap-3 w-full px-4 py-2.5
           rounded-xl
           bg-white dark:bg-gray-900
           border border-gray-300 dark:border-gray-600
           text-sm font-medium
           text-gray-700 dark:text-gray-200
           hover:bg-gray-50 dark:hover:bg-gray-800
           transition duration-150 ease-in-out">

        <!-- Spinner -->
        <svg x-show="isLoading" 
             style="display: none;" 
             class="absolute w-5 h-5 animate-spin text-gray-500 dark:text-gray-400" 
             xmlns="http://www.w3.org/2000/svg" 
             fill="none" 
             viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>

        <!-- Original Content -->
        <div class="flex items-center gap-3 transition-opacity duration-150" :class="{ 'opacity-0': isLoading }">
            <svg width="18" height="18" viewBox="0 0 48 48" style="flex-shrink: 0;">
                <path fill="#EA4335"
                    d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
                <path fill="#4285F4"
                    d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
                <path fill="#FBBC05"
                    d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24s.92 7.54 2.56 10.78l7.97-6.19z" />
                <path fill="#34A853"
                    d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
            </svg>
    
            <span>Google</span>
        </div>
    </a>
</div>
