<script setup lang="ts">
import DocsLayout from '@/Layouts/DocsLayout.vue'
import { usePage, Link } from '@inertiajs/vue3'
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
import AdminChart from '@/Components/Admin/AdminChart.vue'
import { ref, computed } from 'vue'

const page = usePage()
const sharedApplications = computed(() => (page.props.applications as any[]) || [])

const props = defineProps<{
  stats: {
    totalUsers: string;
    totalDocuments: string;
    activeApplications: number;
    uptime: string;
  };
  docsPerApp: Array<{ label: string; count: number; color?: string }>;
  docStatusDistribution: Record<string, number>;
  recentDocuments: Array<{
    id: number;
    title: string;
    app: string;
    appColor: string;
    author: string;
    time: string;
    status: string;
  }>;
}>()

const activeTab = ref('overview')

const adminMenu = [
  { name: 'Dashboard', icon: LayoutGrid, active: true, href: route('admin.dashboard') },
  { name: 'User Management', icon: Users, active: false, href: route('admin.users.index') },
  { name: 'Application Management', icon: Layers, active: false, href: route('admin.applications.index') },
  { name: 'Documentation Management', icon: FileText, active: false, href: route('admin.documents.index') },
  { name: 'Activity Log', icon: Activity, active: false, href: route('admin.activity') },
  { name: 'My Documents', icon: FileText, active: false, href: route('user.documents.index') }
]

const stats = [
  { name: 'Total Users', value: props.stats.totalUsers, change: '+12.5% from last month', icon: Users },
  { name: 'Total Documents', value: props.stats.totalDocuments, change: '+48 new this week', icon: FileText },
  { name: 'Active Applications', value: props.stats.activeApplications.toString(), change: 'Across all modules', icon: Layers },
  { name: 'System Uptime', value: props.stats.uptime, change: 'All services operational', icon: Activity },
]

// Bar Chart Data (Documents per Application)
const barChartData = computed(() => ({
    labels: props.docsPerApp.map(d => d.label),
    datasets: [{
        label: 'Documents',
        data: props.docsPerApp.map(d => d.count),
        backgroundColor: props.docsPerApp.map(d => {
            const colorMap: Record<string, string> = {
                indigo: '#6366f1',
                emerald: '#10b981',
                rose: '#f43f5e',
                amber: '#f59e0b',
                sky: '#0ea5e9',
                violet: '#8b5cf6',
                fuchsia: '#d946ef',
                // Legacy or fallback colors
                red: '#ef4444',
                orange: '#f59e0b',
                blue: '#3b82f6',
            };
            return colorMap[d.color || 'indigo'] || '#6366f1';
        }),
        borderRadius: 8,
        borderSkipped: false,
    }]
}));

// Doughnut Chart Data (Document Status)
const doughnutChartData = computed(() => {
    const labels = Object.keys(props.docStatusDistribution);
    const colorMap: Record<string, string> = {
        'Published': '#10b981', // Green (Emerald)
        'Draft': '#f59e0b',     // Yellow (Orange)
        'Archived': '#374151',  // Gray
    };

    return {
        labels,
        datasets: [{
            data: Object.values(props.docStatusDistribution),
            backgroundColor: labels.map(label => colorMap[label] || '#374151'),
            borderWidth: 0,
            hoverOffset: 15,
            cutout: '70%',
        }]
    };
});

const barChartOptions = {
    scales: {
        y: {
            beginAtZero: true,
            grid: { color: '#262626', drawBorder: false },
            ticks: { color: '#6b7280', font: { size: 10, weight: 'bold' } }
        },
        x: {
            grid: { display: false },
            ticks: { color: '#6b7280', font: { size: 10, weight: 'bold' } }
        }
    }
};

const doughnutChartOptions = {
    plugins: {
        legend: { display: false },
    }
};
</script>

<template>
  <DocsLayout wide>
    <!-- Top Nav Applications (Now in Dropdown) -->
    <template #top-nav-apps>
        <div class="space-y-1">
            <Link 
              v-for="app in sharedApplications" 
              :key="app.name"
              :href="app.slug ? route('app.show.doc', { appSlug: app.slug }) : '#'"
              class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 hover:text-slate-900 dark:text-white hover:bg-white/5 transition-all group"
            >
              <div :class="cn('w-2 h-2 rounded-full transition-all group-hover:scale-125', `bg-${app.color || 'indigo'}-500`)" />
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
              v-for="item in adminMenu" 
              :key="item.name"
              :href="item.href || '#'"
              :class="cn(
                'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group',
                item.active ? 'bg-indigo-500/10 text-white' : 'text-gray-400 hover:text-slate-900 dark:text-white hover:bg-white/5'
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
                <h4 class="text-xs font-bold text-slate-900 dark:text-white mb-1">System Health</h4>
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
          <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mb-1">Admin Dashboard</h1>
          <p class="text-gray-500 text-sm">Comprehensive overview of system users and documentations.</p>
        </div>
        
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div 
          v-for="stat in stats" 
          :key="stat.name"
          class="bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] p-6 rounded-2xl hover:border-indigo-500/30 transition-all group relative overflow-hidden"
        >
          <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/5 rounded-full -mr-8 -mt-8 group-hover:bg-indigo-500/10 transition-colors" />
          <div class="flex items-center justify-between mb-4">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider group-hover:text-gray-300 transition-colors">{{ stat.name }}</span>
            <component :is="stat.icon" class="w-4 h-4 text-gray-600 group-hover:text-indigo-400 transition-colors" />
          </div>
          <div class="space-y-1">
            <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ stat.value }}</h3>
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
        <div class="lg:col-span-4 bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] rounded-3xl p-6 flex flex-col">
          <div class="flex items-center justify-between mb-8">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-1">Documents per Application</h3>
                <p class="text-[10px] text-gray-500">Distribution across major system modules</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1.5 px-2 py-1 rounded-md bg-indigo-500/10 text-[9px] font-bold text-indigo-400 border border-indigo-500/20">
                    <Activity class="w-3 h-3" />
                    Live Data
                </div>
            </div>
          </div>
          
          <div class="flex-1 min-h-[300px]">
             <AdminChart 
               type="bar" 
               :data="barChartData" 
               :options="barChartOptions" 
             />
          </div>
        </div>

        <!-- Document Status Distribution -->
        <div class="lg:col-span-3 bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] rounded-3xl p-6 flex flex-col">
          <div class="mb-8">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-1">Document Status</h3>
            <p class="text-[10px] text-gray-500">Current lifecycle distribution</p>
          </div>

          <div class="flex-1 flex flex-col items-center justify-center relative min-h-[220px]">
             <div class="absolute inset-0 flex items-center justify-center flex-col pointer-events-none">
                <span class="text-2xl font-black text-slate-900 dark:text-white line-height-none">{{ props.stats.totalDocuments }}</span>
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Total</span>
             </div>
             <AdminChart 
               type="doughnut" 
               :data="doughnutChartData" 
               :options="doughnutChartOptions" 
             />
          </div>

          <div class="mt-6 grid grid-cols-3 gap-2">
              <div v-for="(count, label) in props.docStatusDistribution" :key="label" class="bg-slate-50 dark:bg-[#1a1a1a] border border-slate-200 dark:border-[#262626] p-2 rounded-xl flex flex-col items-center">
                  <span class="text-[9px] font-bold text-gray-500 uppercase mb-1">{{ label }}</span>
                  <span class="text-xs font-black text-slate-900 dark:text-white">{{ count }}</span>
              </div>
          </div>
        </div>
      </div>

      <!-- Recent Documents Section -->
      <div class="bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] rounded-3xl p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-8">
          <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Recent Document Activity</h3>
            <p class="text-sm text-gray-500">The latest documentation updates from your team.</p>
          </div>
          <Link :href="route('admin.activity')" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-[#262626] text-xs font-bold text-slate-600 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition-all">
            View All Activity
          </Link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="doc in props.recentDocuments" 
            :key="doc.id"
            class="bg-slate-50 dark:bg-[#1a1a1a] border border-slate-200 dark:border-[#262626] p-5 rounded-2xl group hover:border-indigo-500/30 transition-all cursor-pointer relative overflow-hidden"
          >
            <div class="flex items-start justify-between mb-4">
              <div 
                  class="w-10 h-10 rounded-xl bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] flex items-center justify-center transition-all group-hover:scale-110 shadow-lg"
                  :class="`text-${doc.appColor}-400 group-hover:border-${doc.appColor}-500/50`"
              >
                <FileText class="w-5 h-5" />
              </div>
              <div :class="cn(
                  'px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-tighter shadow-sm',
                  doc.status.toLowerCase() === 'published' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-orange-500/10 text-orange-500'
              )">
                  {{ doc.status }}
              </div>
            </div>
            
            <div class="space-y-4">
              <div>
                <h4 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-indigo-300 transition-colors line-clamp-1 mb-1">{{ doc.title }}</h4>
                <div class="flex items-center gap-2 text-[10px] font-medium text-gray-500 uppercase tracking-wider">
                    <span :class="cn(`text-${doc.appColor}-500`, 'font-bold text-[10px]')">{{ doc.app }}</span>
                    <span>•</span>
                    <span>{{ doc.author }}</span>
                </div>
              </div>
              
              <div class="pt-4 border-t border-slate-200 dark:border-[#262626] flex items-center justify-between">
                  <span class="text-[10px] font-bold text-gray-600 flex items-center gap-1.5 uppercase tracking-widest">
                      <Clock class="w-3 h-3" />
                      {{ doc.time }}
                  </span>
                  <ArrowUpRight class="w-3.5 h-3.5 text-gray-700 group-hover:text-indigo-400 transition-colors" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DocsLayout>
</template>
