<script setup lang="ts">
import DocsLayout from '@/Layouts/DocsLayout.vue'
import { Link } from '@inertiajs/vue3'
import { cn } from '@/lib/utils'
import { 
  Users, 
  FileText, 
  Layers, 
  LayoutGrid,
  Activity, 
  ShieldCheck,
  Search,
  Plus,
  ArrowUpRight,
  Download,
  CalendarDays,
  Clock
} from 'lucide-vue-next'
import { ref } from 'vue'

const props = defineProps<{
  stats: {
    totalUsers: string;
    totalDocuments: string;
    activeApplications: number;
    uptime: string;
  };
  applications: Array<{ name: string; slug: string; color: string }>;
  docsPerApp: Array<{ label: string; count: number }>;
  recentDocuments: Array<{
    id: number;
    title: string;
    app: string;
    author: string;
    time: string;
    status: string;
  }>;
}>()

const activeTab = ref('overview')

const adminMenu = [
  { name: 'User Management', icon: Users, active: true },
  { name: 'App Settings', icon: Layers },
  { name: 'Security & RBAC', icon: ShieldCheck },
  { name: 'System Logs', icon: Activity }
]

const stats = [
  { name: 'Total Users', value: props.stats.totalUsers, change: '+12.5% from last month', icon: Users },
  { name: 'Total Documents', value: props.stats.totalDocuments, change: '+48 new this week', icon: FileText },
  { name: 'Active Applications', value: props.stats.activeApplications.toString(), change: 'Across all modules', icon: Layers },
  { name: 'System Uptime', value: props.stats.uptime, change: 'All services operational', icon: Activity },
]

// Normalize chart data for 100% scale
const maxDocs = Math.max(...props.docsPerApp.map(d => d.count), 1)
const chartData = props.docsPerApp.map(d => ({
  label: d.label,
  value: (d.count / maxDocs) * 100,
  count: d.count
}))
</script>

<template>
  <DocsLayout>
    <!-- Top Nav Applications (Now in Dropdown) -->
    <template #top-nav-apps>
        <div class="space-y-1">
            <Link 
              v-for="app in props.applications" 
              :key="app.name"
              href="#"
              class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all group"
            >
              <div :class="cn('w-2 h-2 rounded-full', app.color)" />
              {{ app.name }}
            </Link>
        </div>
    </template>

    <!-- Left Sidebar Content -->
    <template #left-sidebar>
      <!-- ... (keeping same sidebar structure but ensuring LayoutGrid is imported or using substitute) -->
      <div class="p-6 space-y-8">
        <section>
          <h3 class="text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-4 px-2">Admin Portal</h3>
          <nav class="space-y-1">
            <Link 
              v-for="item in [
                { name: 'User Management', icon: Users, active: true },
                { name: 'App Settings', icon: Layers },
                { name: 'Security & RBAC', icon: ShieldCheck },
                { name: 'System Logs', icon: Activity }
              ]" 
              :key="item.name"
              href="#"
              :class="cn(
                'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group',
                item.active ? 'bg-indigo-500/10 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5'
              )"
            >
              <component :is="item.icon" class="w-4 h-4 shrink-0" />
              {{ item.name }}
            </Link>
          </nav>
        </section>

        <section>
            <div class="bg-indigo-500/5 border border-indigo-500/10 rounded-2xl p-4 mt-12">
                <div class="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center mb-3">
                    <ShieldCheck class="w-4 h-4 text-indigo-400" />
                </div>
                <h4 class="text-xs font-bold text-white mb-1">System Health</h4>
                <p class="text-[10px] text-gray-500 leading-relaxed">All documentation services are operating at optimal performance levels.</p>
            </div>
        </section>
      </div>
    </template>

    <!-- Main Content -->
    <div class="space-y-8 max-w-6xl">
      <!-- Dashboard Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-white mb-1">Admin Dashboard</h1>
          <p class="text-gray-500 text-sm">Comprehensive overview of system users and documentations.</p>
        </div>
        <div class="flex items-center gap-2">
            <button class="flex items-center gap-2 px-3 py-1.5 h-9 bg-white text-black rounded-lg text-xs font-bold hover:bg-gray-200 transition-all shadow-lg shadow-white/5">
                <Plus class="w-3.5 h-3.5" />
                New Document
            </button>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div 
          v-for="stat in stats" 
          :key="stat.name"
          class="bg-[#161616] border border-[#262626] p-6 rounded-2xl hover:border-indigo-500/30 transition-all group relative overflow-hidden"
        >
          <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/5 rounded-full -mr-8 -mt-8 group-hover:bg-indigo-500/10 transition-colors" />
          <div class="flex items-center justify-between mb-4">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider group-hover:text-gray-300 transition-colors">{{ stat.name }}</span>
            <component :is="stat.icon" class="w-4 h-4 text-gray-600 group-hover:text-indigo-400 transition-colors" />
          </div>
          <div class="space-y-1">
            <h3 class="text-2xl font-extrabold text-white">{{ stat.value }}</h3>
            <p class="text-[10px] font-medium text-emerald-400 flex items-center gap-1">
                {{ stat.change }}
                <ArrowUpRight v-if="stat.change.includes('+')" class="w-2.5 h-2.5" />
            </p>
          </div>
        </div>
      </div>

      <!-- Main Visual Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-7 gap-6">
        <!-- Documents Per Application Chart -->
        <div class="lg:col-span-4 bg-[#161616] border border-[#262626] rounded-3xl p-6">
          <div class="flex items-center justify-between mb-8">
            <div>
                <h3 class="text-sm font-bold text-white mb-1">Documents per Application</h3>
                <p class="text-[10px] text-gray-500">Distribution across major system modules</p>
            </div>
            <Layers class="w-4 h-4 text-gray-600" />
          </div>
          
          <div class="h-[250px] w-full flex items-end justify-between gap-4 px-2">
            <div 
              v-for="item in chartData" 
              :key="item.label"
              class="flex-1 flex flex-col items-center gap-3 group"
            >
              <div class="relative w-full flex-1 flex items-end justify-center">
                  <div 
                    class="w-full max-w-[40px] bg-indigo-500/10 group-hover:bg-indigo-500 transition-all rounded-t-lg relative"
                    :style="{ height: `${item.value}%` }"
                  >
                     <div class="absolute -top-6 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-[#262626] text-white text-[9px] font-bold px-1.5 py-0.5 rounded pointer-events-none">
                         {{ item.count }}
                     </div>
                  </div>
              </div>
              <span class="text-[10px] font-bold text-gray-500 group-hover:text-white transition-colors">{{ item.label }}</span>
            </div>
          </div>
        </div>

        <!-- Recent Documents -->
        <div class="lg:col-span-3 bg-[#161616] border border-[#262626] rounded-3xl p-6">
          <div class="mb-6">
            <h3 class="text-sm font-bold text-white mb-1">Recent Documents</h3>
            <p class="text-xs text-gray-500">Latest updates across all applications.</p>
          </div>

          <div class="space-y-6">
            <div 
              v-for="doc in props.recentDocuments" 
              :key="doc.id"
              class="flex items-center justify-between group cursor-pointer"
            >
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-[#1a1a1a] border border-[#262626] flex items-center justify-center text-indigo-400 group-hover:border-indigo-500/50 transition-all">
                  <FileText class="w-4 h-4" />
                </div>
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-white group-hover:text-indigo-300 transition-colors">{{ doc.title }}</span>
                  <div class="flex items-center gap-2 text-[10px] text-gray-500">
                      <span class="font-bold text-indigo-500/70">{{ doc.app }}</span>
                      <span>•</span>
                      <span>{{ doc.author }}</span>
                  </div>
                </div>
              </div>
              <div class="flex flex-col items-end gap-1">
                  <span class="text-[10px] font-bold text-gray-400 flex items-center gap-1">
                      <Clock class="w-2.5 h-2.5" />
                      {{ doc.time }}
                  </span>
                  <div :class="cn(
                      'px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-tighter',
                      doc.status === 'Published' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-orange-500/10 text-orange-500'
                  )">
                      {{ doc.status }}
                  </div>
              </div>
            </div>
          </div>

          <button class="w-full mt-8 py-2.5 rounded-xl border border-[#262626] text-xs font-bold text-gray-400 hover:text-white hover:bg-white/5 transition-all">
            View all documents
          </button>
        </div>
      </div>
    </div>
  </DocsLayout>
</template>
