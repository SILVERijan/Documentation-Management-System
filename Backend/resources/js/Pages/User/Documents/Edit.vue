<script setup lang="ts">
import DocsLayout from '@/Layouts/DocsLayout.vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { cn } from '@/lib/utils'
import { 
  FileText, Save, ArrowLeft, Loader2, LayoutGrid, Users, Layers,
  Paperclip, Download, Trash2, File, AlertCircle, X
} from 'lucide-vue-next'
import RichEditor from '@/Components/RichEditor.vue'
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => (page.props.auth as any).user)

const props = defineProps<{
  document: {
    id: number;
    title: string;
    sub_title: string;
    application_id: number;
    content: string;
    status: string;
    attachments: Array<{
        id: number;
        original_name: string;
        file_size: number;
        file_type: string;
        created_at: string;
    }>;
  };
  assignableApplications: Array<{ id: number; name: string }>;
}>()

const form = useForm({
    title: props.document.title || '',
    sub_title: props.document.sub_title || '',
    application_id: props.document.application_id || '',
    status: props.document.status || 'draft',
    content: props.document.content || ''
})

const uploadForm = useForm({
    file: null as File | null,
})

const fileInput = ref<HTMLInputElement | null>(null)

const submit = () => {
    form.put(route('user.documents.update', props.document.id))
}

const onFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement
    if (target.files?.length) {
        uploadForm.file = target.files[0]
        uploadAttachments()
    }
}

const uploadAttachments = () => {
    uploadForm.post(route('user.attachments.store', props.document.id), {
        onSuccess: () => {
            uploadForm.reset()
            if (fileInput.value) fileInput.value.value = ''
        }
    })
}

const deleteAttachment = (id: number) => {
    if (confirm('Are you sure you want to remove this attachment?')) {
        router.delete(route('user.attachments.destroy', id))
    }
}

const formatSize = (bytes: number) => {
    if (bytes === 0) return '0 B'
    const k = 1024
    const sizes = ['B', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

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
</script>

<template>
  <DocsLayout wide>
    <Head title="Edit Document" />

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
            
            <div class="pt-4 mt-4 border-t border-slate-200 dark:border-[#262626]">
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
          <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mb-1">Edit Document</h1>
          <p class="text-gray-500 text-sm">Update your documentation details and content.</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] rounded-3xl overflow-hidden shadow-2xl p-8 space-y-6">
          <div class="space-y-2">
              <label class="text-sm font-bold text-slate-700 dark:text-gray-300">Title</label>
              <input 
                  v-model="form.title" 
                  type="text" 
                  required
                  class="w-full bg-slate-50 dark:bg-[#1a1a1a] border-slate-200 dark:border-[#262626] rounded-xl px-4 py-2.5 text-white placeholder-gray-600 focus:border-indigo-500 focus:ring-indigo-500 transition-all font-medium"
              >
              <div v-if="form.errors.title" class="text-red-400 text-xs mt-1">{{ form.errors.title }}</div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                  <label class="text-sm font-bold text-slate-700 dark:text-gray-300">Application</label>
                  <select 
                      v-model="form.application_id" 
                      required
                      class="w-full bg-slate-50 dark:bg-[#1a1a1a] border-slate-200 dark:border-[#262626] rounded-xl px-4 py-2.5 text-white focus:border-indigo-500 focus:ring-indigo-500 transition-all font-medium"
                  >
                      <option value="" disabled>Select an Application...</option>
                      <option v-for="app in assignableApplications" :key="app.id" :value="app.id">{{ app.name }}</option>
                  </select>
                  <div v-if="form.errors.application_id" class="text-red-400 text-xs mt-1">{{ form.errors.application_id }}</div>
              </div>
              <div class="space-y-2">
                  <label class="text-sm font-bold text-slate-700 dark:text-gray-300">Status</label>
                  <select 
                      v-model="form.status" 
                      required
                      class="w-full bg-slate-50 dark:bg-[#1a1a1a] border-slate-200 dark:border-[#262626] rounded-xl px-4 py-2.5 text-white focus:border-indigo-500 focus:ring-indigo-500 transition-all font-medium"
                  >
                      <option value="draft">Draft</option>
                      <option value="published">Published</option>
                  </select>
                  <div v-if="form.errors.status" class="text-red-400 text-xs mt-1">{{ form.errors.status }}</div>
              </div>
          </div>

          <div class="space-y-2">
              <label class="text-sm font-bold text-slate-700 dark:text-gray-300">
                  Content
              </label>
              <RichEditor 
                  v-model="form.content" 
              />
              <div v-if="form.errors.content" class="text-red-400 text-xs mt-1">{{ form.errors.content }}</div>
          </div>

          <div class="pt-4 border-t border-slate-200 dark:border-[#262626] flex items-center justify-end gap-3">
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
                  Save Changes
              </button>
          </div>
      </form>

      <!-- Attachments Section -->
      <div class="bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] rounded-3xl overflow-hidden shadow-2xl">
          <div class="p-8 border-b border-slate-200 dark:border-[#262626] flex items-center justify-between bg-slate-50 dark:bg-[#1a1a1a]/50">
              <div>
                  <h2 class="text-xl font-bold text-white mb-1">Attachments</h2>
                  <p class="text-gray-500 text-sm">Add supporting files, PDFs, or images to this document.</p>
              </div>
              <div class="relative">
                  <input 
                      type="file" 
                      ref="fileInput"
                      class="hidden" 
                      @change="onFileChange"
                  >
                  <button 
                      @click="fileInput?.click()"
                      :disabled="uploadForm.processing"
                      class="flex items-center gap-2 px-4 py-2 bg-white/5 border border-slate-200 dark:border-[#262626] text-white rounded-xl text-xs font-bold hover:bg-white/10 transition-all disabled:opacity-50"
                  >
                      <div v-if="uploadForm.processing" class="flex items-center gap-2">
                          <Loader2 class="w-3 h-3 animate-spin" />
                          Uploading...
                      </div>
                      <div v-else class="flex items-center gap-2">
                          <Paperclip class="w-3 h-3" />
                          Add File
                      </div>
                  </button>
              </div>
          </div>

          <div v-if="uploadForm.progress" class="px-8 py-4 bg-indigo-500/5 border-b border-slate-200 dark:border-[#262626]">
               <div class="flex items-center justify-between mb-2">
                  <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest">Uploading File...</span>
                  <span class="text-[10px] font-bold text-indigo-400">{{ uploadForm.progress.percentage }}%</span>
               </div>
               <div class="w-full h-1 bg-slate-200 dark:bg-[#262626] rounded-full overflow-hidden">
                  <div 
                      class="h-full bg-indigo-500 transition-all duration-300"
                      :style="`width: ${uploadForm.progress.percentage}%`"
                  />
               </div>
          </div>

          <div class="p-8">
              <div v-if="props.document.attachments.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div 
                      v-for="file in props.document.attachments" 
                      :key="file.id"
                      class="group bg-slate-50 dark:bg-[#1a1a1a] border border-slate-200 dark:border-[#262626] p-4 rounded-2xl flex items-center justify-between hover:border-indigo-500/30 transition-all"
                  >
                      <div class="flex items-center gap-4 min-w-0">
                          <div class="w-10 h-10 rounded-xl bg-[#16161a] border border-slate-200 dark:border-[#262626] flex items-center justify-center text-gray-500 group-hover:text-indigo-400 transition-colors overflow-hidden">
                              <img 
                                  v-if="['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(file.file_type.toLowerCase())" 
                                  :src="route('user.attachments.download', file.id)" 
                                  class="w-full h-full object-cover opacity-50 group-hover:opacity-100 transition-opacity"
                              />
                              <File v-else-if="file.file_type === 'pdf'" class="w-5 h-5" />
                              <File v-else class="w-5 h-5" />
                          </div>
                          <div class="flex flex-col min-w-0">
                              <span class="text-sm font-bold text-slate-900 dark:text-white truncate pr-4">{{ file.original_name }}</span>
                              <div class="flex items-center gap-2 text-[10px] font-medium text-gray-500 uppercase tracking-wider">
                                  <span>{{ formatSize(file.file_size) }}</span>
                                  <span>•</span>
                                  <span>{{ file.created_at }}</span>
                              </div>
                          </div>
                      </div>
                      <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                          <a 
                              :href="route('user.attachments.download', file.id)" 
                              target="_blank"
                              class="p-2 text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 rounded-lg transition-all"
                              title="View / Download"
                          >
                              <ImageIcon v-if="['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(file.file_type.toLowerCase())" class="w-4 h-4" />
                              <File v-else-if="file.file_type === 'pdf'" class="w-4 h-4" />
                              <Download v-else class="w-4 h-4" />
                          </a>
                          <button 
                              @click="deleteAttachment(file.id)"
                              class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all"
                              title="Remove"
                          >
                              <Trash2 class="w-4 h-4" />
                          </button>
                      </div>
                  </div>
              </div>
              <div v-else class="flex flex-col items-center justify-center py-12 text-gray-600 border-2 border-dashed border-slate-200 dark:border-[#262626] rounded-2xl">
                  <Paperclip class="w-10 h-10 mb-3 opacity-20" />
                  <p class="text-sm font-medium">No attachments yet.</p>
                  <p class="text-xs">Upload supplementary files for this documentation.</p>
              </div>
          </div>
      </div>
    </div>
  </DocsLayout>
</template>
