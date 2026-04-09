<script setup lang="ts">
import DocsLayout from '@/Layouts/DocsLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { FileText, Save, ArrowLeft, Loader2, LayoutGrid, Users, Layers, X, CheckCircle2, FileCode } from 'lucide-vue-next'
import RichEditor from '@/Components/RichEditor.vue'
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { cn } from '@/lib/utils'
import { importDocument } from '@/lib/doc-importer'

const page = usePage()
const user = computed(() => (page.props.auth as any).user)

const props = defineProps<{
  assignableApplications: Array<{ id: number; name: string }>
}>()

const form = useForm({
  title: '',
  application_id: '',
  status: 'draft',
  content: '',
  sections: [] as Array<{ sub_title: string; content: string }>,
})

// Markdown import state
const isDragging       = ref(false)
const isImporting      = ref(false)
const importError      = ref('')
const importedSections = ref<Array<{ sub_title: string; content: string }>>([])
const fileInputRef     = ref<HTMLInputElement | null>(null)

const userMenu = computed(() => {
  if (user.value?.role === 'admin') {
    return [
      { name: 'Dashboard',                icon: LayoutGrid, active: false, href: route('admin.dashboard') },
      { name: 'User Management',          icon: Users,      active: false, href: route('admin.users.index') },
      { name: 'Application Management',   icon: Layers,     active: false, href: route('admin.applications.index') },
      { name: 'Documentation Management', icon: FileText,   active: false, href: route('admin.documents.index') },
      { name: 'My Documents',             icon: FileText,   active: true,  href: route('user.documents.index') },
    ]
  }
  return [
    { name: 'My Documents', icon: FileText, active: true, href: route('user.documents.index') },
  ]
})

async function handleFile(file: File) {
  const ext = file.name.split('.').pop()?.toLowerCase()
  if (ext !== 'md' && ext !== 'txt' && ext !== 'docx') {
    importError.value = 'Please upload a .md, .txt, or .docx file.'
    return
  }
  importError.value = ''
  isImporting.value = true
  try {
    const data = await importDocument(file)

    form.title   = data.title   || form.title
    form.content = data.intro   || form.content
    if (data.sections && data.sections.length > 0) {
      importedSections.value = data.sections
      form.sections = data.sections
    }
  } catch (e: any) {
    importError.value = e?.message ?? 'Failed to parse the file. Please check the format.'
  } finally {
    isImporting.value = false
  }
}

function onFileInput(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (file) handleFile(file)
}

function onDrop(e: DragEvent) {
  isDragging.value = false
  const file = e.dataTransfer?.files?.[0]
  if (file) handleFile(file)
}

function clearImport() {
  importedSections.value = []
  form.sections  = []
  form.title     = ''
  form.content   = ''
  importError.value = ''
  if (fileInputRef.value) fileInputRef.value.value = ''
}

const submit = () => {
  form.post(route('user.documents.store'))
}
</script>

<template>
  <DocsLayout wide>
    <Head title="Create Document" />

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
                item.active ? 'bg-indigo-500/10 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5'
              )"
            >
              <component :is="item.icon" class="w-4 h-4 shrink-0" />
              {{ item.name }}
            </Link>
            <div class="pt-4 mt-4 border-t border-[#262626]">
              <Link
                :href="route('user.documents.index')"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all group"
              >
                <ArrowLeft class="w-4 h-4 shrink-0" />
                Back to Documents
              </Link>
            </div>
          </nav>
        </section>
      </div>
    </template>

    <div class="space-y-8 max-w-4xl mx-auto">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-white mb-1">Create Document</h1>
          <p class="text-gray-500 text-sm">Create a new documentation article or import a document file (.md, .docx).</p>
        </div>
      </div>

      <!-- Markdown Import Drop Zone -->
      <div
        v-if="importedSections.length === 0"
        :class="cn(
          'relative border-2 border-dashed rounded-2xl p-8 transition-all cursor-pointer text-center',
          isDragging
            ? 'border-indigo-500 bg-indigo-500/5'
            : 'border-[#2e2e2e] hover:border-indigo-500/50 bg-[#111111]'
        )"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="onDrop"
        @click="fileInputRef?.click()"
      >
        <input ref="fileInputRef" type="file" accept=".md,.txt,.docx" class="hidden" @change="onFileInput" />

        <div v-if="isImporting" class="flex flex-col items-center gap-3 text-indigo-400">
          <Loader2 class="w-8 h-8 animate-spin" />
          <p class="text-sm font-medium">Parsing your document file…</p>
        </div>

        <div v-else class="flex flex-col items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center">
            <FileCode class="w-6 h-6 text-indigo-400" />
          </div>
          <div>
            <p class="text-white font-semibold text-sm">Drop a <code class="text-indigo-400">.md</code> or <code class="text-indigo-400">.docx</code> file here to auto-import</p>
            <p class="text-gray-500 text-xs mt-1">Or click to browse — title, intro and sections will be filled automatically</p>
          </div>
          <div class="flex items-center gap-2 mt-1">
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/5 text-gray-400 border border-white/10"># H1 → Title</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/5 text-gray-400 border border-white/10">## H2 → Sections</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/5 text-gray-400 border border-white/10">Intro → Overview</span>
          </div>
        </div>

        <p v-if="importError" class="mt-3 text-red-400 text-xs font-medium">{{ importError }}</p>
      </div>

      <!-- Import Success Banner -->
      <div
        v-if="importedSections.length > 0"
        class="flex items-center justify-between gap-4 px-5 py-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20"
      >
        <div class="flex items-center gap-3">
          <CheckCircle2 class="w-5 h-5 text-emerald-400 shrink-0" />
          <div>
            <p class="text-sm font-bold text-white">Markdown imported successfully</p>
            <p class="text-xs text-gray-400">{{ importedSections.length }} section{{ importedSections.length !== 1 ? 's' : '' }} detected and auto-filled below</p>
          </div>
        </div>
        <button type="button" @click="clearImport" class="flex items-center gap-1.5 text-xs text-gray-400 hover:text-white transition-colors font-medium">
          <X class="w-4 h-4" /> Clear
        </button>
      </div>

      <!-- Main Form -->
      <form @submit.prevent="submit" class="bg-[#161616] border border-[#262626] rounded-3xl overflow-hidden shadow-2xl p-8 space-y-6">
        <!-- Title -->
        <div class="space-y-2">
          <label class="text-sm font-bold text-gray-300">Title</label>
          <input
            v-model="form.title"
            type="text"
            required
            class="w-full bg-[#1a1a1a] border-[#262626] rounded-xl px-4 py-2.5 text-white placeholder-gray-600 focus:border-indigo-500 focus:ring-indigo-500 transition-all font-medium"
            placeholder="e.g. Onboarding Guide"
          />
          <div v-if="form.errors.title" class="text-red-400 text-xs mt-1">{{ form.errors.title }}</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Application -->
          <div class="space-y-2">
            <label class="text-sm font-bold text-gray-300">Application</label>
            <select
              v-model="form.application_id"
              required
              class="w-full bg-[#1a1a1a] border-[#262626] rounded-xl px-4 py-2.5 text-white focus:border-indigo-500 focus:ring-indigo-500 transition-all font-medium"
            >
              <option value="" disabled>Select an Application...</option>
              <option v-for="app in assignableApplications" :key="app.id" :value="app.id">{{ app.name }}</option>
            </select>
            <div v-if="form.errors.application_id" class="text-red-400 text-xs mt-1">{{ form.errors.application_id }}</div>
          </div>

          <!-- Status -->
          <div class="space-y-2">
            <label class="text-sm font-bold text-gray-300">Status</label>
            <select
              v-model="form.status"
              required
              class="w-full bg-[#1a1a1a] border-[#262626] rounded-xl px-4 py-2.5 text-white focus:border-indigo-500 focus:ring-indigo-500 transition-all font-medium"
            >
              <option value="draft">Draft</option>
              <option value="published">Published</option>
            </select>
            <div v-if="form.errors.status" class="text-red-400 text-xs mt-1">{{ form.errors.status }}</div>
          </div>
        </div>

        <!-- Content / Intro -->
        <div class="space-y-2">
          <label class="text-sm font-bold text-gray-300">Introduction / Overview</label>
          <RichEditor v-model="form.content" placeholder="Write a brief introduction for this document…" />
          <div v-if="form.errors.content" class="text-red-400 text-xs mt-1">{{ form.errors.content }}</div>
        </div>

        <!-- Imported Sections Preview -->
        <div v-if="importedSections.length > 0" class="space-y-4 pt-2 border-t border-[#262626]">
          <div class="flex items-center gap-2">
            <FileCode class="w-4 h-4 text-indigo-400" />
            <p class="text-sm font-bold text-gray-300">Sections from Markdown</p>
            <span class="ml-auto text-[10px] text-gray-500 font-medium uppercase tracking-widest">{{ importedSections.length }} section{{ importedSections.length !== 1 ? 's' : '' }}</span>
          </div>

          <div class="space-y-3">
            <div
              v-for="(section, i) in importedSections"
              :key="i"
              class="p-4 bg-[#111] border border-[#1e1e1e] rounded-xl space-y-1"
            >
              <p class="text-xs font-bold text-indigo-400 uppercase tracking-widest">Section {{ i + 1 }}</p>
              <p class="text-sm font-semibold text-white">{{ section.sub_title }}</p>
              <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">
                {{ section.content.slice(0, 160) }}{{ section.content.length > 160 ? '…' : '' }}
              </p>
            </div>
          </div>

          <p class="text-xs text-gray-500 italic">These sections will be saved automatically when you create the document.</p>
        </div>

        <!-- Footer -->
        <div class="pt-4 border-t border-[#262626] flex items-center justify-between gap-3">
          <p class="text-xs text-gray-500">
            {{ importedSections.length > 0
              ? `${importedSections.length} section${importedSections.length !== 1 ? 's' : ''} will be created automatically.`
              : 'After creating, you can add sections with sub-titles from the document management page.'
            }}
          </p>
          <div class="flex items-center gap-3">
            <Link :href="route('user.documents.index')" class="px-4 py-2 text-sm font-bold text-gray-400 hover:text-white transition-colors">
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="flex items-center gap-2 px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-indigo-500/20 disabled:opacity-50"
            >
              <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
              <Save v-else class="w-4 h-4" />
              Create Document
            </button>
          </div>
        </div>
      </form>
    </div>
  </DocsLayout>
</template>