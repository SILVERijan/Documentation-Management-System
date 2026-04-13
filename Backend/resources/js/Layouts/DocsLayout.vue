<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { Search, ChevronDown, Grid, Settings, LogOut, FileText, Loader2, CornerDownLeft } from 'lucide-vue-next'
import { cn } from '@/lib/utils'
import axios from 'axios'
import ToastHandler from '@/Components/Common/ToastHandler.vue'
import ThemeSwitcher from '@/Components/Common/ThemeSwitcher.vue'

const page = usePage()
const isAppsOpen = ref(false)
const isUserOpen = ref(false)

const searchQuery = ref('')
const searchResults = ref<any[]>([])
const isSearching = ref(false)
const isSearchOpen = ref(false)
let searchTimeout: any = null

watch(searchQuery, (query) => {
    if (searchTimeout) clearTimeout(searchTimeout)
    
    if (query.length < 2) {
        searchResults.value = []
        isSearching.value = false
        isSearchOpen.value = false
        return
    }

    isSearching.value = true
    isSearchOpen.value = true

    searchTimeout = setTimeout(async () => {
        try {
            const response = await axios.get(route('docs.search'), {
                params: { query }
            })
            searchResults.value = response.data
        } catch (error) {
            console.error('Search failed:', error)
        } finally {
            isSearching.value = false
        }
    }, 300)
})

const onBlur = () => {
    setTimeout(() => {
        isSearchOpen.value = false
    }, 200)
}

const props = withDefaults(defineProps<{
  wide?: boolean
}>(), {
  wide: false
})

const navApplications = computed(() => (page.props as any).applications ?? [])
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-[#0a0a0f] text-slate-900 dark:text-[#ededed] font-['Inter',_sans-serif] selection:bg-indigo-500/30 transition-colors duration-300">
    <Head title="SILVER Documentations" />
    <ToastHandler />

    <!-- Top Navigation Bar -->
    <header class="h-14 border-b border-slate-200/60 dark:border-[#262626] bg-white/90 dark:bg-[#0a0a0f]/80 backdrop-blur-md sticky top-0 z-50 px-6 flex items-center justify-between transition-colors duration-300 shadow-sm shadow-slate-200/40 dark:shadow-none">
      <div class="flex items-center gap-10">
        <Link href="/" class="flex items-center gap-2.5 group shrink-0">
          <ApplicationLogo size="sm" :show-name="true" />
          <span v-if="$page.props.auth?.user?.role === 'admin'" class="ml-2 px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-[10px] font-black uppercase tracking-widest border border-indigo-500/20">Admin</span>
        </Link>

        <!-- Applications Selector (Section) -->
        <div class="relative">
          <button 
            @click="isAppsOpen = !isAppsOpen"
            class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-bold text-slate-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5 transition-all"
          >
            <Grid class="w-4 h-4" />
            Applications
            <ChevronDown :class="cn('w-3 h-3 transition-transform', isAppsOpen && 'rotate-180')" />
          </button>

          <!-- Apps Dropdown -->
          <div v-if="isAppsOpen" class="absolute top-full left-0 mt-2 w-64 bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] rounded-xl shadow-2xl p-2 z-[60] animate-in fade-in zoom-in-95 duration-200">
            <div class="px-3 py-2 text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest border-b border-slate-100 dark:border-[#262626] mb-2">Select Application</div>
            <div class="space-y-1">
              <div
                v-for="app in navApplications"
                :key="app.id"
              >
                <Link
                  v-if="app.slug"
                  :href="app.slug ? route('app.show.doc', { appSlug: app.slug }) : '#'"
                  class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5 transition-all group"
                >
                  <div :class="cn('w-2 h-2 rounded-full shrink-0', app.color || 'bg-indigo-400')" />
                  <div class="flex flex-col gap-0.5 min-w-0 flex-1">
                      <span class="truncate">{{ app.name }}</span>
                      <span v-if="app.status !== 'active' && $page.props.auth?.user?.role === 'admin'" class="text-[9px] uppercase tracking-tighter text-gray-500 font-bold">
                          {{ app.status }}
                      </span>
                  </div>
                </Link>
                <div 
                  v-else
                  class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 cursor-not-allowed"
                >
                  <div class="w-2 h-2 rounded-full shrink-0 bg-gray-600" />
                  <div class="flex flex-col gap-0.5 min-w-0 flex-1">
                      <span class="truncate">{{ app.name }}</span>
                      <span class="text-[9px] uppercase tracking-tighter text-red-500 font-bold">
                          NO SLUG
                      </span>
                  </div>
                </div>
              </div>
              <div v-if="navApplications.length === 0" class="px-3 py-4 text-xs text-gray-500 text-center">
                No applications available.
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex-1 max-w-sm mx-10 relative group hidden sm:block">
        <div class="relative">
            <template v-if="isSearching">
                <Loader2 class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-indigo-400 animate-spin" />
            </template>
            <template v-else>
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 group-focus-within:text-indigo-400 transition-colors" />
            </template>
                <input 
                    v-model="searchQuery"
                    type="text" 
                    @focus="isSearchOpen = searchQuery.length >= 2"
                    @blur="onBlur"
                    placeholder="Search docs..." 
                    class="w-full h-9 bg-slate-100 dark:bg-[#1a1a1a] border border-transparent dark:border-[#262626] rounded-lg pl-10 pr-4 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all placeholder:text-slate-400 dark:placeholder:text-gray-600 text-slate-900 dark:text-white"
                >
        </div>

        <!-- Search Results Dropdown -->
        <div v-if="isSearchOpen" class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] rounded-xl shadow-2xl overflow-hidden z-[60] animate-in fade-in slide-in-from-top-2 duration-200">
            <div class="p-2 max-h-[400px] overflow-y-auto">
                <div v-if="searchResults.length > 0" class="space-y-1">
                    <div class="px-3 py-1.5 text-[10px] font-bold text-gray-500 uppercase tracking-widest flex items-center justify-between">
                        Results
                        <span class="text-[9px] lowercase opacity-50 font-normal">Found {{ searchResults.length }}</span>
                    </div>
                    <Link
                        v-for="result in searchResults"
                        :key="result.id"
                        :href="route('app.show.doc', { appSlug: result.app_slug, docSlug: result.slug })"
                        class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-all group"
                        @click="isSearchOpen = false; searchQuery = ''"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div :class="cn('w-8 h-8 rounded-lg flex items-center justify-center bg-white/5 text-gray-400 group-hover:text-indigo-400 transition-colors shrink-0')">
                                <FileText class="w-4 h-4" />
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-200 group-hover:text-indigo-600 dark:group-hover:text-white truncate">{{ result.title }}</span>
                                <div class="flex items-center gap-2">
                                    <div :class="cn('w-1.5 h-1.5 rounded-full', result.app_color || 'bg-indigo-500')" />
                                    <span class="text-[10px] text-gray-500 font-medium">{{ result.app_name }}</span>
                                </div>
                            </div>
                        </div>
                        <CornerDownLeft class="w-3 h-3 text-gray-700 opacity-0 group-hover:opacity-100 transition-all" />
                    </Link>
                </div>
                <div v-else-if="!isSearching" class="py-12 flex flex-col items-center justify-center text-gray-600 gap-3">
                    <Search class="w-8 h-8 opacity-20" />
                    <p class="text-xs font-medium">No documents matching your search.</p>
                </div>
            </div>
            <div class="bg-slate-50 dark:bg-[#1a1a1a] px-4 py-2 border-t border-slate-100 dark:border-[#262626] flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-[9px] text-gray-500 flex items-center gap-1"><kbd class="bg-[#262626] px-1 rounded text-gray-400 border border-white/5">ESC</kbd> to close</span>
                </div>
                <ApplicationLogo size="sm" :show-name="false" class="opacity-20" />
            </div>
        </div>
      </div>

      <div class="flex items-center gap-8">
        <!-- Authorized User Links -->
        <template v-if="$page.props.auth && $page.props.auth.user">
            <!-- Admin Link -->
                <Link 
                    v-if="$page.props.auth.user.role === 'admin' && !route().current('admin.*') && !route().current('profile.edit')"
                    :href="route('admin.dashboard')" 
                    class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-white transition-colors border-r border-gray-200 dark:border-[#262626] pr-8"
                >
                    Admin Portal
                </Link>

                <Link 
                    v-if="$page.props.auth.user.role === 'admin' && (route().current('admin.*') || route().current('profile.edit'))"
                    :href="route('dashboard')" 
                    class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-white transition-colors border-r border-gray-200 dark:border-[#262626] pr-8"
                >
                    Admin Portal
                </Link>
            
            <!-- User Link -->
            <Link 
                v-if="$page.props.auth.user.role === 'user'"
                :href="route('user.documents.index')" 
                class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-white transition-colors border-r border-slate-200 dark:border-[#262626] pr-8"
            >
                User Portal
            </Link>
            
            <div class="relative flex items-center gap-4">
              <ThemeSwitcher />
              
              <button @click="isUserOpen = !isUserOpen" class="flex items-center gap-3 group">
                  <span class="text-xs font-bold text-gray-500 dark:text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-white transition-colors hidden lg:block">{{ $page.props.auth.user.name }}</span>
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
              <div v-if="isUserOpen" class="absolute top-full right-0 mt-2 w-48 bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] rounded-xl shadow-2xl p-2 z-[60] animate-in fade-in zoom-in-95 duration-200">
                <Link 
                    :href="route('user.documents.index')"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                >
                    <FileText class="w-4 h-4" />
                    My Documents
                </Link>
                <Link 
                    :href="route('profile.edit')"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
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
        </template>
        
        <!-- Guest Login Button -->
        <template v-else>
            <Link 
                :href="route('login')" 
                class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-white hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors bg-indigo-50 dark:bg-white/5 hover:bg-indigo-100 dark:hover:bg-white/10 px-4 py-2 rounded-lg flex items-center gap-4"
            >
                Log In
                <ThemeSwitcher />
            </Link>
        </template>
      </div>
    </header>

    <div class="flex h-[calc(100vh-3.5rem)]">
      <!-- Left Sidebar (Applications & Docs) -->
      <aside v-if="$slots['left-sidebar']" class="w-72 border-r border-slate-200/60 dark:border-[#262626] bg-slate-50/50 dark:bg-[#0a0a0f] flex flex-col shrink-0 overflow-y-auto hidden lg:flex transition-colors duration-300">
        <slot name="left-sidebar" />
      </aside>

      <!-- Main Content Area -->
      <main class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-white dark:bg-[#0a0a0f] transition-colors duration-300">
        <div :class="cn('w-full p-8 lg:p-12', !wide && 'max-w-4xl mr-auto ml-0 border-r border-slate-100 dark:border-none')">
            <slot />
        </div>
      </main>

      <!-- Right Sidebar (TOC) -->
      <aside v-if="!wide && $slots['right-sidebar']" class="w-64 border-l border-slate-200/40 dark:border-[#262626] bg-slate-50/50 dark:bg-[#0a0a0f] shrink-0 hidden xl:flex flex-col p-8 sticky top-14 h-full overflow-y-auto max-h-full transition-colors duration-300">
        <slot name="right-sidebar" />
      </aside>
    </div>
  </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

/* Custom Scrollbar for the dark theme */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: #e5e7eb;
  border-radius: 10px;
}

.dark ::-webkit-scrollbar-thumb {
  background: #262626;
}

::-webkit-scrollbar-thumb:hover {
  background: #d1d5db;
}

.dark ::-webkit-scrollbar-thumb:hover {
  background: #333333;
}
</style>
