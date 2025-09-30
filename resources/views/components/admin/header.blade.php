@props(['title' => 'Dashboard'])
<div class="hidden md:flex justify-between items-center p-4 mt-3 mx-3 bg-white dark:!bg-gray-800 shadow-lg sticky top-0 rounded-lg z-30">
  <h4 class="text-xl font-semibold text-gray-900 dark:text-white mb-0">{{ $title ?? 'Dashboard' }}</h4>

  {{-- RIGHT: theme switch + user dropdown (inline) --}}
  <div x-data="{ open: false }" class="relative flex items-center gap-3">

    {{-- Switch Tema --}}
    <div class="flex items-center">
      <!-- Dark Mode Toggle Button -->
      <button type="button" x-data="darkModeToggle()" @click="toggle()"
          class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:focus-visible:ring-indigo-400 transition-colors duration-200"
          :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
          title="Toggle dark/light mode">
          <!-- Sun icon (light mode) -->
          <svg x-show="!isDark" class="h-5 w-5 text-yellow-500" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z" />
          </svg>
          <!-- Moon icon (dark mode) -->
          <svg x-show="isDark" x-cloak class="h-5 w-5 text-blue-400" viewBox="0 0 24 24" fill="currentColor">
              <path fill-rule="evenodd" d="M9.528 1.718a.75.75 0 01.162.819A8.97 8.97 0 009 6a9 9 0 009 9 8.97 8.97 0 003.463-.69.75.75 0 01.981.98 10.503 10.503 0 01-9.694 6.46c-5.799 0-10.5-4.701-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 01.818.162z" clip-rule="evenodd" />
          </svg>
      </button>
    </div>

    {{-- Tombol Akun - buka dropdown --}}
    <button
      type="button"
      class="flex items-center justify-center p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
      @click="open = !open"
      aria-label="User Menu"
    >
      <i class="bi bi-person-circle text-xl"></i>
    </button>
    
    {{-- Dropdown user menu --}}
    <div
      x-show="open"
      x-transition:enter="transition ease-out duration-100"
      x-transition:enter-start="transform opacity-0 scale-95"
      x-transition:enter-end="transform opacity-100 scale-100"
      x-transition:leave="transition ease-in duration-75"
      x-transition:leave-start="transform opacity-100 scale-100"
      x-transition:leave-end="transform opacity-0 scale-95"
      @click.outside="open = false"
      class="absolute right-0 top-full mt-2 w-56 bg-white dark:!bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-600 z-50 opacity-100"
    >
      <div class="py-1">
        {{-- User Profile Option --}}
        <livewire:admin.userprofile />

        
        {{-- Divider --}}
        <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
        
        {{-- Logout --}}
        <form action="{{ route('logout') }}" method="POST" class="block">
          @csrf
          <button 
            type="submit" 
            class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center transition-colors"
          >
            <i class="bi bi-box-arrow-right mr-2"></i>Log Out
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

