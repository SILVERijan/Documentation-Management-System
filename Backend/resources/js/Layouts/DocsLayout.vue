<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { Search, ChevronDown, Grid, Settings, LogOut } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

const page = usePage()
const isAppsOpen = ref(false)
const isUserOpen = ref(false)

const navApplications = computed(() => (page.props as any).applications ?? [])
</script>

<template>
  <div class="min-h-screen bg-[#0f0f0f] text-[#ededed] font-sans selection:bg-indigo-500/30">
    <Head title="SILVER Documentations" />

    <!-- Top Navigation Bar -->
    <header class="h-14 border-b border-[#262626] bg-[#0f0f0f]/80 backdrop-blur-md sticky top-0 z-50 px-6 flex items-center justify-between">
      <div class="flex items-center gap-10">
        <Link href="/" class="flex items-center gap-2 group shrink-0">
          <span class="text-lg font-bold tracking-tight text-white transition-colors group-hover:text-indigo-400">SILVER Documentations</span>
        </Link>

        <!-- Applications Selector (Section) -->
        <div class="relative">
          <button 
            @click="isAppsOpen = !isAppsOpen"
            class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-bold text-gray-400 hover:text-white hover:bg-white/5 transition-all"
          >
            <Grid class="w-4 h-4" />
            Applications
            <ChevronDown :class="cn('w-3 h-3 transition-transform', isAppsOpen && 'rotate-180')" />
          </button>

          <!-- Apps Dropdown -->
          <div v-if="isAppsOpen" class="absolute top-full left-0 mt-2 w-64 bg-[#161616] border border-[#262626] rounded-xl shadow-2xl p-2 z-[60] animate-in fade-in zoom-in-95 duration-200">
            <div class="px-3 py-2 text-[10px] font-bold text-gray-500 uppercase tracking-widest border-b border-[#262626] mb-2">Select Application</div>
            <div class="space-y-1">
              <Link
                v-for="app in navApplications"
                :key="app.id"
                :href="route('app.show', app.slug)"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all group"
              >
                <div class="w-2 h-2 rounded-full shrink-0 bg-indigo-400" />
                {{ app.name }}
              </Link>
              <div v-if="navApplications.length === 0" class="px-3 py-4 text-xs text-gray-500 text-center">
                No applications available.
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex-1 max-w-sm mx-10 relative group hidden sm:block">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 group-focus-within:text-indigo-400 transition-colors" />
        <input 
          type="text" 
          placeholder="Search docs..." 
          class="w-full h-9 bg-[#1a1a1a] border border-[#262626] rounded-lg pl-10 pr-4 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all placeholder:text-gray-600"
        >
      </div>

      <div class="flex items-center gap-8">
        <!-- Admin Link -->
        <Link 
            v-if="$page.props.auth.user.role === 'admin'"
            :href="route('admin.dashboard')" 
            class="text-xs font-bold uppercase tracking-wider text-gray-400 hover:text-white transition-colors border-r border-[#262626] pr-8"
        >
            Admin Portal
        </Link>
        
        <div class="relative">
          <button @click="isUserOpen = !isUserOpen" class="flex items-center gap-3 group">
              <span class="text-xs font-bold text-gray-400 group-hover:text-white transition-colors hidden lg:block">{{ $page.props.auth.user.name }}</span>
              <div class="w-8 h-8 rounded-full ring-2 ring-indigo-500/20 group-hover:ring-indigo-500/40 transition-all overflow-hidden bg-indigo-600 flex items-center justify-center text-xs font-black">
                  <img
                      v-if="$page.props.auth.user.avatar"
                      :src="'/storage/' + $page.props.auth.user.avatar"
                      class="h-full w-full object-cover"
                      :alt="$page.props.auth.user.name"
                  />
                  <span v-else>{{ $page.props.auth.user.name.charAt(0).toUpperCase() }}</span>
              </div>
          </button>

          <!-- User Dropdown -->
          <div v-if="isUserOpen" class="absolute top-full right-0 mt-2 w-48 bg-[#161616] border border-[#262626] rounded-xl shadow-2xl p-2 z-[60] animate-in fade-in zoom-in-95 duration-200">
            <Link 
                :href="route('profile.edit')"
                class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all"
            >
                <Settings class="w-4 h-4" />
                Profile
            </Link>
            <Link 
                :href="route('logout')" 
                method="post" 
                as="button"
                class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-all text-left"
            >
                <LogOut class="w-4 h-4" />
                Log Out
            </Link>
          </div>
        </div>
      </div>
    </header>

    <div class="flex h-[calc(100vh-3.5rem)]">
      <!-- Left Sidebar (Applications & Docs) -->
      <aside class="w-72 border-r border-[#262626] bg-[#0f0f0f] flex flex-col shrink-0 overflow-y-auto hidden lg:flex">
        <slot name="left-sidebar" />
      </aside>

      <!-- Main Content Area -->
      <main class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-[#0f0f0f]">
        <div class="max-w-4xl mx-auto w-full p-8 lg:p-12">
            <slot />
        </div>
      </main>

      <!-- Right Sidebar (TOC) -->
      <aside class="w-64 border-l border-[#262626] bg-[#0f0f0f] shrink-0 hidden xl:flex flex-col p-8 sticky top-14 h-full">
        <slot name="right-sidebar" />
      </aside>
    </div>
  </div>
</template>

<style>
/* Custom Scrollbar for the dark theme */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: #262626;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: #333333;
}
</style>
