<script setup lang="ts">
import DocsLayout from '@/Layouts/DocsLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { cn } from '@/lib/utils'
import { 
  Users, 
  Layers, 
  LayoutGrid, 
  Activity, 
  FileText,
  Clock,
  ArrowUpRight,
  User as UserIcon,
  Filter,
  RefreshCcw,
  Search
} from 'lucide-vue-next'
import { ref, computed } from 'vue'

const props = defineProps<{
  activities: {
    data: Array<{
      id: number;
      type: string;
      action: 'created' | 'updated';
      title: string;
      document_slug: string;
      app: string;
      app_slug: string;
      appColor: string;
      user: string;
      time: string;
      timestamp: string;
      status: string;
    }>;
    links: Array<any>;
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
  };
}>()

const adminMenu = [
  { name: 'Dashboard', icon: LayoutGrid, active: false, href: route('admin.dashboard') },
  { name: 'User Management', icon: Users, active: false, href: route('admin.users.index') },
  { name: 'Application Management', icon: Layers, active: false, href: route('admin.applications.index') },
  { name: 'Documentation Management', icon: FileText, active: false, href: route('admin.documents.index') },
  { name: 'Activity Log', icon: Activity, active: true, href: route('admin.activity') },
  { name: 'My Documents', icon: FileText, active: false, href: route('user.documents.index') }
]

const searchQuery = ref('')
const filteredActivities = computed(() => {
    if (!searchQuery.value) return props.activities.data
    const query = searchQuery.value.toLowerCase()
    return props.activities.data.filter(act => 
        act.title.toLowerCase().includes(query) || 
        act.user.toLowerCase().includes(query) ||
        act.app.toLowerCase().includes(query)
    )
})

const getActionVerb = (action: string) => {
    return action === 'created' ? 'created a new document' : 'updated the document';
}

const getActionColor = (action: string) => {
    return action === 'created' ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' : 'text-indigo-400 bg-indigo-500/10 border-indigo-500/20';
}
</script>

<template>
  <DocsLayout wide>
    <Head title="System Activity Log - Admin Portal" />

    <template #left-sidebar>
      <div class="p-6 space-y-8">
        <section>
          <h3 class="text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-4 px-2">Admin Portal</h3>
          <nav class="space-y-1">
            <Link 
              v-for="item in adminMenu" 
              :key="item.name"
              :href="item.href || '#'"
              :class="cn(
                'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group',
                item.active ? 'bg-indigo-500/10 text-slate-900 dark:text-white' : 'text-slate-500 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5'
              )"
            >
              <component :is="item.icon" class="w-4 h-4 shrink-0" />
              {{ item.name }}
            </Link>
          </nav>
        </section>
      </div>
    </template>

    <div class="space-y-8 max-w-5xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mb-1">System Activity Log</h1>
          <p class="text-gray-500 text-sm">Comprehensive audit trail of all documentation changes and system events.</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="px-3 py-1.5 rounded-lg bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] text-[11px] font-bold text-gray-400 flex items-center gap-2">
                <Activity class="w-3.5 h-3.5 text-indigo-400" />
                Live Audit Stream
            </div>
        </div>
      </div>

      <!-- Filters Row -->
      <div class="flex items-center justify-between gap-4">
          <div class="relative flex-1 max-w-sm">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
            <input 
                v-model="searchQuery"
                type="text" 
                placeholder="Search activity by title, user, or app..." 
                class="w-full bg-white dark:bg-[#161616] border-slate-200 dark:border-[#262626] rounded-xl pl-10 pr-4 py-2 text-sm text-white placeholder-gray-600 focus:border-indigo-500 focus:ring-indigo-500 transition-all font-medium"
            >
          </div>
          
          <div class="flex items-center gap-3">
              <button class="p-2 rounded-lg bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] text-gray-500 hover:text-white transition-all">
                  <Filter class="w-4 h-4" />
              </button>
              <button class="p-2 rounded-lg bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] text-gray-500 hover:text-white transition-all" @click="$inertia.reload()">
                  <RefreshCcw class="w-4 h-4" />
              </button>
          </div>
      </div>

      <!-- Activity Timeline -->
      <div class="relative space-y-4">
          <!-- Timeline Line -->
          <div class="absolute left-6 top-0 bottom-0 w-px bg-gradient-to-b from-indigo-500/50 via-[#262626] to-transparent pointer-events-none" />

          <div 
            v-for="activity in filteredActivities" 
            :key="activity.id"
            class="relative pl-12 group"
          >
            <!-- Timeline Node -->
            <div class="absolute left-[21px] top-6 w-2.5 h-2.5 rounded-full bg-white dark:bg-[#161616] border-2 border-indigo-500 z-10 group-hover:scale-125 transition-transform shadow-[0_0_8px_rgba(99,102,241,0.5)]" />

            <div class="bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] rounded-2xl p-5 hover:border-indigo-500/30 transition-all shadow-lg hover:shadow-indigo-500/5">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500/20 to-indigo-500/5 border border-indigo-500/10 flex items-center justify-center shrink-0">
                            <UserIcon class="w-5 h-5 text-indigo-400" />
                        </div>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ activity.user }}</span>
                                <span class="text-xs text-gray-500 font-medium">{{ getActionVerb(activity.action) }}</span>
                                <Link 
                                    :href="route('app.show.doc', { appSlug: activity.app_slug, docSlug: activity.document_slug })"
                                    class="text-sm font-black text-indigo-300 hover:text-indigo-200 transition-colors flex items-center gap-1 group/link"
                                >
                                    {{ activity.title }}
                                    <ArrowUpRight class="w-3 h-3 group-hover/link:translate-x-0.5 group-hover/link:-translate-y-0.5 transition-transform" />
                                </Link>
                            </div>
                            <div class="flex items-center gap-3">
                                <div :class="cn('px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-tighter border', getActionColor(activity.action))">
                                    {{ activity.action }}
                                </div>
                                <div class="flex items-center gap-1.5 text-[10px] font-bold text-gray-600 uppercase tracking-widest">
                                    <Layers class="w-3 h-3" />
                                    {{ activity.app }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col items-end shrink-0">
                        <div class="flex items-center gap-2 text-xs font-bold text-gray-400 bg-white/5 px-3 py-1.5 rounded-lg border border-white/5">
                            <Clock class="w-3.5 h-3.5 text-gray-500" />
                            {{ activity.time }}
                        </div>
                        <span class="text-[9px] font-mono text-gray-700 mt-2 uppercase tracking-tighter">{{ activity.timestamp }}</span>
                    </div>
                </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="filteredActivities.length === 0" class="flex flex-col items-center justify-center py-20 bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] border-dashed rounded-3xl">
              <Activity class="w-12 h-12 text-gray-800 mb-4" />
              <h3 class="text-lg font-bold text-gray-400">No activity recorded</h3>
              <p class="text-sm text-gray-600">We couldn't find any system activity matching your criteria.</p>
          </div>
      </div>

      <!-- Pagination -->
      <div v-if="props.activities.last_page > 1" class="flex items-center justify-between bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] p-4 rounded-2xl shadow-xl">
          <div class="text-[11px] font-bold text-gray-500 uppercase tracking-widest px-2">
              Showing {{ props.activities.from }}-{{ props.activities.to }} of {{ props.activities.total }} logs
          </div>
          <div class="flex items-center gap-1">
              <Link 
                  v-for="link in props.activities.links" 
                  :key="link.label"
                  :href="link.url || '#'"
                  v-html="link.label"
                  :class="cn(
                      'px-3.5 py-1.5 rounded-lg text-xs font-black transition-all border',
                      link.active 
                        ? 'bg-indigo-500 text-white border-indigo-400 shadow-[0_0_15px_rgba(99,102,241,0.3)]' 
                        : !link.url 
                            ? 'text-gray-700 border-transparent cursor-not-allowed' 
                            : 'text-gray-500 border-transparent hover:text-white hover:bg-white/5 hover:border-white/10'
                  )"
              />
          </div>
      </div>
    </div>
  </DocsLayout>
</template>
