<script setup lang="ts">
import DocsLayout from '@/Layouts/DocsLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { cn } from '@/lib/utils'
import { 
  FileText, Plus, Edit2, Trash2, CheckCircle2, Clock, Eye, Activity, LayoutList, LayoutGrid, Users, Layers
} from 'lucide-vue-next'
import { ref, watch, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => (page.props.auth as any).user)

const props = defineProps<{
  documents: {
    data: Array<any>;
    links: Array<any>;
    current_page: number;
    last_page: number;
    total: number;
  };
  stats: {
    total: number;
    published: number;
    draft: number;
  };
  filters: {
    search?: string;
  };
}>()

const userMenu = computed(() => {
  if (user.value?.role === 'admin') {
    return [
      { name: 'Dashboard', icon: LayoutGrid, active: false, href: route('admin.dashboard') },
      { name: 'User Management', icon: Users, active: false, href: route('admin.users.index') },
      { name: 'Application Management', icon: Layers, active: false, href: route('admin.applications.index') },
      { name: 'Documentation Management', icon: FileText, active: false, href: route('admin.documents.index') },
      { name: 'My Documents', icon: FileText, active: true, href: route('user.documents.index') }
    ]
  }

  return [
    { name: 'My Documents', icon: FileText, active: true, href: route('user.documents.index') }
  ]
})

const search = ref(props.filters.search || '')

watch(search, (value) => {
    router.get(route('user.documents.index'), { search: value }, {
        preserveState: true,
        replace: true
    })
})

const deleteDocument = (doc: any) => {
    if (confirm(`Are you sure you want to delete "${doc.title}"? This cannot be undone.`)) {
        router.delete(route('user.documents.destroy', doc.id), {
            preserveScroll: true,
        })
    }
}

const statsItems = [
    { name: 'My Documents', value: props.stats.total, icon: FileText, color: 'text-indigo-400', bg: 'bg-indigo-500/10' },
    { name: 'Published', value: props.stats.published, icon: CheckCircle2, color: 'text-emerald-400', bg: 'bg-emerald-500/10' },
    { name: 'Drafts', value: props.stats.draft, icon: Clock, color: 'text-orange-400', bg: 'bg-orange-500/10' },
]
</script>

<template>
  <DocsLayout wide>
    <Head title="My Documents - Portal" />

    <template #left-sidebar>
      <div class="p-6 space-y-8">
        <section>
          <h3 class="text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-4 px-2">
              {{ user?.role === 'admin' ? 'Admin Portal' : 'User Portal' }}
          </h3>
          <nav class="space-y-1">
            <Link 
              v-for="item in userMenu" 
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

    <div class="space-y-8 max-w-6xl">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mb-1">My Documents</h1>
          <p class="text-gray-500 text-sm">Manage the documentations for your assigned applications.</p>
        </div>
        <div class="flex items-center gap-2">
            <Link :href="route('user.documents.create')" class="flex items-center gap-2 px-3 py-1.5 h-9 bg-white text-black rounded-lg text-xs font-bold hover:bg-gray-200 transition-all shadow-lg shadow-white/5">
                <Plus class="w-3.5 h-3.5" />
                New Document
            </Link>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div 
          v-for="stat in statsItems" 
          :key="stat.name"
          class="bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] p-5 rounded-2xl hover:border-indigo-500/30 transition-all group"
        >
          <div class="flex items-center justify-between mb-3">
            <div :class="cn('w-8 h-8 rounded-lg flex items-center justify-center', stat.bg)">
                <component :is="stat.icon" :class="cn('w-4 h-4', stat.color)" />
            </div>
            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">{{ stat.name }}</span>
          </div>
          <div class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ stat.value }}</div>
        </div>
      </div>

      <!-- Filters & Search -->
      <div class="flex items-center justify-between gap-4">
          <div class="relative flex-1 max-w-sm">
            <Activity class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
            <input 
                v-model="search"
                type="text" 
                placeholder="Search documents by title..." 
                class="w-full bg-white dark:bg-[#161616] border-slate-200 dark:border-[#262626] rounded-xl pl-10 pr-4 py-2 text-sm text-white placeholder-gray-600 focus:border-indigo-500 focus:ring-indigo-500 transition-all"
            >
          </div>
      </div>

      <!-- Documents Table -->
      <div class="bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] rounded-3xl overflow-hidden shadow-2xl">
         <div class="overflow-x-auto">
             <table class="w-full text-left text-sm text-gray-400">
                 <thead class="bg-slate-50 dark:bg-[#1a1a1a] text-xs uppercase text-gray-500 border-b border-slate-200 dark:border-[#262626]">
                     <tr>
                         <th class="px-6 py-4 font-bold tracking-wider">Document Title</th>
                         <th class="px-6 py-4 font-bold tracking-wider">Application</th>
                         <th class="px-6 py-4 font-bold tracking-wider">Status</th>
                         <th class="px-6 py-4 font-bold tracking-wider text-right">Actions</th>
                     </tr>
                 </thead>
                 <tbody class="divide-y divide-slate-200 dark:divide-[#262626]">
                     <tr v-for="doc in props.documents.data" :key="doc.id" class="hover:bg-slate-100 dark:hover:bg-[#1f1f1f] transition-colors group">
                         <td class="px-6 py-4">
                             <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-slate-50 dark:bg-[#1a1a1a] border border-slate-200 dark:border-[#262626] flex flex-shrink-0 items-center justify-center text-indigo-400 group-hover:border-indigo-500/50 transition-all shadow-inner">
                                    <FileText class="w-4 h-4" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-slate-900 dark:text-white font-bold group-hover:text-indigo-300 transition-colors">{{ doc.title }}</span>
                                    <span class="text-[10px] text-gray-500 font-mono">{{ doc.slug }}</span>
                                </div>
                             </div>
                         </td>
                         <td class="px-6 py-4">
                             <div v-if="doc.application" class="flex items-center gap-2">
                                 <div :class="cn('w-2 h-2 rounded-full', doc.application.color)" />
                                 <span class="text-xs font-bold text-slate-700 dark:text-gray-300">{{ doc.application.name }}</span>
                             </div>
                             <span v-else class="text-xs text-gray-600 italic">No Application</span>
                         </td>
                         <td class="px-6 py-4">
                             <div :class="cn(
                                 'inline-flex items-center gap-1.5 px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-widest w-fit',
                                 doc.status === 'published' ? 'bg-emerald-500/10 text-emerald-400' : 
                                 doc.status === 'draft' ? 'bg-orange-500/10 text-orange-400' : 'bg-gray-800 text-gray-400'
                             )">
                                 <CheckCircle2 v-if="doc.status === 'published'" class="w-2.5 h-2.5" />
                                 <Clock v-if="doc.status === 'draft'" class="w-2.5 h-2.5" />
                                 {{ doc.status }}
                             </div>
                         </td>
                         <td class="px-6 py-4 text-right">
                             <div class="flex items-center justify-end gap-1">
                                 <Link :href="route('app.show.doc', { appSlug: doc.application ? doc.application.slug : 'unassigned', docSlug: doc.slug })" class="p-2 text-gray-500 hover:text-white hover:bg-white/5 rounded-lg transition-all" title="View Document">
                                     <Eye class="w-4 h-4" />
                                 </Link>
                                 <Link :href="route('user.sections.index', doc.id)" class="p-2 text-gray-500 hover:text-emerald-400 hover:bg-emerald-500/10 rounded-lg transition-all" title="Manage Sections">
                                     <LayoutList class="w-4 h-4" />
                                 </Link>
                                 <Link :href="route('user.documents.edit', doc.id)" class="p-2 text-gray-500 hover:text-indigo-400 hover:bg-indigo-500/10 rounded-lg transition-all" title="Edit Document">
                                     <Edit2 class="w-4 h-4" />
                                 </Link>
                                 <!-- Only show delete if they have permission via Policy / owned the doc. We assume owned for now. -->
                                 <button @click="deleteDocument(doc)" class="p-2 text-gray-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all" title="Delete Document">
                                     <Trash2 class="w-4 h-4" />
                                 </button>
                             </div>
                         </td>
                     </tr>
                     <tr v-if="props.documents.data.length === 0">
                         <td colspan="4" class="px-6 py-12 text-center">
                             <div class="flex flex-col items-center justify-center text-gray-500">
                                 <FileText class="w-12 h-12 mb-3 text-gray-700" />
                                 <p class="text-sm font-medium">No documents found.</p>
                                 <p class="text-xs">Create your first document to get started.</p>
                             </div>
                         </td>
                     </tr>
                 </tbody>
             </table>
         </div>
      </div>
    </div>
  </DocsLayout>
</template>
