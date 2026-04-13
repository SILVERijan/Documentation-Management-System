<script setup lang="ts">
import DocsLayout from '@/Layouts/DocsLayout.vue'
import { Link } from '@inertiajs/vue3'
import { cn } from '@/lib/utils'
import { ref, onMounted } from 'vue'
import { Download, File, Paperclip, ArrowRight } from 'lucide-vue-next'

const props = defineProps<{
    application: { id: number; name: string; slug: string; color?: string },
    documents: Array<{ id: number; title: string; slug: string; status: string }>,
    currentDocument: {
        id: number; 
        title: string; 
        sub_title?: string;
        content: string; 
        updated_at: string; 
        status: string;
        attachments: Array<{
            id: number;
            original_name: string;
            file_size: number;
            file_type: string;
        }>;
    } | null,
    sections: Array<{ id: string; sub_title: string; content: string; level: number }>,
    toc: Array<{ id: string; title: string; level: number }>,
}>()

const activeId = ref<string | null>(null)

const formatDate = (d: string) => new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })

const formatSize = (bytes: number) => {
    if (bytes === 0) return '0 B'
    const k = 1024
    const sizes = ['B', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const scrollTo = (id: string) => {
    const el = document.getElementById(id)
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

onMounted(() => {
    if (props.toc.length > 0) activeId.value = props.toc[0].id

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) activeId.value = e.target.id })
    }, { rootMargin: '-20% 0% -60% 0%', threshold: 0 })

    props.toc.forEach(item => {
        const el = document.getElementById(item.id)
        if (el) observer.observe(el)
    })
})
</script>

<template>
  <DocsLayout>
    <!-- Left sidebar: document list -->
    <template #left-sidebar>
      <div class="p-6">
        <h3 class="text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-4 px-2">
          {{ application.name }}
        </h3>
        <nav class="space-y-0.5" v-if="documents.length > 0">
          <Link
            v-for="doc in documents"
            :key="doc.id"
            :href="route('app.show.doc', { appSlug: application.slug, docSlug: doc.slug })"
            :class="cn(
              'block px-3 py-2 rounded-lg text-sm font-medium transition-all',
              currentDocument && doc.id === currentDocument.id
                ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-l-2 border-indigo-500 font-bold'
                : 'text-slate-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5'
            )"
          >
            {{ doc.title }}
          </Link>
        </nav>
        <div v-else class="text-xs text-gray-500 px-3 italic">No published documents yet.</div>
      </div>
    </template>

    <!-- Main content -->
    <div class="space-y-10 pb-24" v-if="currentDocument">
      <!-- Document header -->
      <header class="space-y-4 pb-8 border-b border-slate-200/60 dark:border-[#262626]">
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight">
          {{ currentDocument.title }}
        </h1>
        <p v-if="currentDocument.sub_title" class="text-xl text-gray-400 font-medium leading-relaxed max-w-2xl">
          {{ currentDocument.sub_title }}
        </p>
        <div class="flex flex-wrap items-center gap-6 text-sm">
          <div class="flex flex-col gap-0.5">
            <span class="text-slate-400 dark:text-gray-500 font-bold uppercase tracking-wider text-[10px]">Application</span>
            <span class="text-slate-900 dark:text-white font-medium flex items-center gap-2">
              <div :class="cn('w-2 h-2 rounded-full', application.color || 'bg-indigo-500')" />
              {{ application.name }}
            </span>
          </div>
          <div class="h-8 w-px bg-slate-200/60 dark:bg-[#262626]" />
          <div class="flex flex-col gap-0.5">
            <span class="text-slate-400 dark:text-gray-500 font-bold uppercase tracking-wider text-[10px]">Updated</span>
            <span class="text-slate-900 dark:text-white font-medium">{{ formatDate(currentDocument.updated_at) }}</span>
          </div>
          <div class="h-8 w-px bg-slate-200/60 dark:bg-[#262626]" />
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
            <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full" />
            Published
          </span>
        </div>
      </header>

      <!-- Main intro content -->
      <div v-if="currentDocument.content" class="prose dark:prose-invert max-w-none" v-html="currentDocument.content" />

      <!-- Sections -->
      <div v-for="section in sections" :key="section.id" class="space-y-4 scroll-mt-20" >
        <div :id="section.id" :class="cn('space-y-4 pt-10 scroll-mt-24', section.level === 3 ? 'ml-0' : 'border-t border-slate-100 dark:border-[#1a1a1a]')">
          <h2 v-if="section.level === 2" class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ section.sub_title }}</h2>
          <h3 v-else class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500/50" />
            {{ section.sub_title }}
          </h3>
          <div class="prose dark:prose-invert max-w-none" v-html="section.content" />
        </div>
      </div>

      <!-- Attachments Section -->
      <div v-if="currentDocument.attachments.length > 0" class="pt-12 border-t border-gray-200 dark:border-[#262626] mt-12">
        <div class="flex items-center gap-2 text-gray-900 dark:text-white mb-6">
          <Paperclip class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
          <h3 class="text-xl font-bold">Resources & Attachments</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <a 
            v-for="file in currentDocument.attachments" 
            :key="file.id"
            :href="route('user.attachments.download', file.id)"
            target="_blank"
            class="group flex items-center justify-between p-4 bg-white dark:bg-[#16161a] border border-slate-200 dark:border-[#262626] rounded-2xl hover:border-indigo-500/50 transition-all shadow-sm shadow-slate-200/20 dark:shadow-none"
          >
            <div class="flex items-center gap-4 min-w-0">
              <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/5 flex items-center justify-center text-gray-500 group-hover:text-indigo-400 transition-colors overflow-hidden">
                <img 
                  v-if="['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(file.file_type.toLowerCase())" 
                  :src="route('user.attachments.download', file.id)" 
                  class="w-full h-full object-cover opacity-50 group-hover:opacity-100 transition-opacity"
                />
                <File v-else-if="file.file_type === 'pdf'" class="w-5 h-5" />
                <File v-else class="w-5 h-5" />
              </div>
              <div class="flex flex-col min-w-0">
                <span class="text-sm font-bold text-slate-700 dark:text-gray-200 group-hover:text-indigo-600 dark:group-hover:text-white truncate">{{ file.original_name }}</span>
                <span class="text-[10px] font-medium text-slate-400 dark:text-gray-600 uppercase tracking-widest">{{ formatSize(file.file_size) }}</span>
              </div>
            </div>
            <div class="flex items-center gap-2 text-indigo-400 font-bold text-xs opacity-0 group-hover:opacity-100 transition-all pr-2">
              {{ file.file_type === 'pdf' || ['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(file.file_type.toLowerCase()) ? 'View' : 'Download' }}
              <Download v-if="!(['pdf'].includes(file.file_type.toLowerCase()) || ['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(file.file_type.toLowerCase()))" class="w-4 h-4" />
              <ArrowRight v-else class="w-4 h-4" />
            </div>
          </a>
        </div>
      </div>

      <!-- Empty sections state -->
      <div v-if="sections.length === 0 && currentDocument.attachments.length === 0" class="py-10 text-center text-slate-400 border border-dashed border-slate-200 dark:border-[#262626] rounded-2xl">
        <p class="text-sm">No sections have been added to this document yet.</p>
      </div>
    </div>

    <!-- No document selected -->
    <div v-else class="flex flex-col items-center justify-center py-24 text-slate-400">
      <h2 class="text-xl font-medium text-slate-900 dark:text-white mb-2">No document selected</h2>
      <p>Select a document from the left navigation.</p>
    </div>

    <!-- Right sidebar: TOC -->
    <template #right-sidebar v-if="toc && toc.length > 0">
      <div class="space-y-3">
        <p class="text-[11px] font-bold text-gray-500 uppercase tracking-widest">On this page</p>
        <nav class="space-y-1">
          <button
            v-for="item in toc"
            :key="item.id"
            @click="scrollTo(item.id)"
            :class="cn(
              'block w-full text-left px-0 py-1 text-sm transition-all border-l-2',
              activeId === item.id
                ? 'text-indigo-600 dark:text-indigo-400 border-indigo-500 font-bold'
                : 'text-slate-400 hover:text-indigo-600 dark:hover:text-gray-300 border-transparent',
              item.level === 3 ? 'pl-6 text-[13px]' : 'pl-3'
            )"
          >
            {{ item.title }}
          </button>
        </nav>
      </div>
    </template>
  </DocsLayout>
</template>

<style scoped>
/* Custom scroll behavior for smooth anchors */
html {
  scroll-behavior: smooth;
}
</style>
