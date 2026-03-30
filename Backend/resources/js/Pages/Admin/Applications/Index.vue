<script setup lang="ts">
import DocsLayout from '@/Layouts/DocsLayout.vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { cn } from '@/lib/utils'
import { 
  Users, Layers, LayoutGrid, ShieldCheck, Activity,
  Plus, Edit2, Trash2, CheckCircle2, XCircle, Wrench
} from 'lucide-vue-next'
import { ref } from 'vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps<{
  applications: Array<any>;
}>()

const adminMenu = [
  { name: 'Dashboard', icon: LayoutGrid, active: false, href: route('admin.dashboard') },
  { name: 'User Management', icon: Users, active: false, href: route('admin.users.index') },
  { name: 'Application Management', icon: Layers, active: true, href: route('admin.applications.index') },
  { name: 'System Logs', icon: Activity }
]

const showModal = ref(false)
const editingApp = ref<any>(null)

const form = useForm({
    name: '',
    description: '',
    status: 'active',
})

const openCreateModal = () => {
    editingApp.value = null
    form.reset()
    form.clearErrors()
    showModal.value = true
}

const openEditModal = (app: any) => {
    editingApp.value = app
    form.reset()
    form.clearErrors()
    form.name = app.name
    form.description = app.description
    form.status = app.status
    showModal.value = true
}

const submitForm = () => {
    if (editingApp.value) {
        form.put(route('admin.applications.update', editingApp.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false
            }
        })
    } else {
        form.post(route('admin.applications.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false
                form.reset()
            }
        })
    }
}

const deleteApp = (app: any) => {
    if (confirm(`Are you sure you want to delete the application "${app.name}"? This action cannot be undone.`)) {
        router.delete(route('admin.applications.destroy', app.id), {
            preserveScroll: true,
        })
    }
}
</script>

<template>
  <DocsLayout>
    <Head title="Application Management - Admin Portal" />

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
                item.active ? 'bg-indigo-500/10 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5'
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
          <h1 class="text-3xl font-bold tracking-tight text-white mb-1">Application Management</h1>
          <p class="text-gray-500 text-sm">Manage system modules, statuses, and core configuration.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="openCreateModal" class="flex items-center gap-2 px-3 py-1.5 h-9 bg-indigo-500 text-white rounded-lg text-xs font-bold hover:bg-indigo-600 transition-all shadow-lg shadow-indigo-500/20">
                <Plus class="w-3.5 h-3.5" />
                New Application
            </button>
        </div>
      </div>

      <!-- Applications Table -->
      <div class="bg-[#161616] border border-[#262626] rounded-3xl overflow-hidden shadow-2xl">
         <div class="overflow-x-auto">
             <table class="w-full text-left text-sm text-gray-400">
                 <thead class="bg-[#1a1a1a] text-xs uppercase text-gray-500 border-b border-[#262626]">
                     <tr>
                         <th class="px-6 py-4 font-bold tracking-wider">Application Details</th>
                         <th class="px-6 py-4 font-bold tracking-wider">Slug URL</th>
                         <th class="px-6 py-4 font-bold tracking-wider">Status</th>
                         <th class="px-6 py-4 font-bold tracking-wider border-l border-[#262626] text-center">Documents</th>
                         <th class="px-6 py-4 font-bold tracking-wider text-right">Actions</th>
                     </tr>
                 </thead>
                 <tbody class="divide-y divide-[#262626]">
                     <tr v-for="app in props.applications" :key="app.id" class="hover:bg-[#1f1f1f] transition-colors group">
                         <td class="px-6 py-4 w-96">
                             <div class="flex items-start gap-4">
                                <div class="w-10 h-10 mt-0.5 rounded-xl border border-[#262626] flex flex-shrink-0 items-center justify-center text-white font-bold shadow-inner" :class="app.color ? app.color + '/10' : 'bg-[#262626]'">
                                    <Layers class="w-4 h-4" :class="app.color ? `text-${app.color.replace('bg-','')}` : 'text-gray-400'" />
                                </div>
                                <div class="flex flex-col pr-4">
                                    <span class="text-white font-bold group-hover:text-indigo-300 transition-colors">{{ app.name }}</span>
                                    <span class="text-[11px] text-gray-500 mt-1 line-clamp-2 leading-relaxed">{{ app.description }}</span>
                                </div>
                             </div>
                         </td>
                         <td class="px-6 py-4">
                             <span class="px-2.5 py-1 rounded-lg bg-[#262626] text-gray-400 font-mono text-[10px]">/docs/{{ app.slug }}</span>
                         </td>
                         <td class="px-6 py-4">
                             <div :class="cn(
                                 'inline-flex items-center gap-1.5 px-2 py-1 rounded text-[10px] font-black uppercase tracking-widest flex-shrink-0',
                                 app.status === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 
                                 app.status === 'maintenance' ? 'bg-orange-500/10 text-orange-400' : 'bg-red-500/10 text-red-400'
                             )">
                                 <CheckCircle2 v-if="app.status === 'active'" class="w-3 h-3" />
                                 <Wrench v-else-if="app.status === 'maintenance'" class="w-3 h-3" />
                                 <XCircle v-else class="w-3 h-3" />
                                 {{ app.status }}
                             </div>
                         </td>
                         <td class="px-6 py-4 border-l border-[#262626] text-center">
                             <span class="text-xl font-black text-white group-hover:text-indigo-400 transition-colors">{{ app.documents_count }}</span>
                         </td>
                         <td class="px-6 py-4 text-right">
                             <div class="flex items-center justify-end gap-2">
                                 <button @click="openEditModal(app)" class="p-2 text-gray-500 hover:text-indigo-400 hover:bg-indigo-500/10 rounded-lg transition-all" title="Edit App">
                                     <Edit2 class="w-4 h-4" />
                                 </button>
                                 <button @click="deleteApp(app)" class="p-2 text-gray-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all" title="Delete App">
                                     <Trash2 class="w-4 h-4" />
                                 </button>
                             </div>
                         </td>
                     </tr>
                     <tr v-if="props.applications.length === 0">
                         <td colspan="5" class="px-6 py-12 text-center">
                             <div class="flex flex-col items-center justify-center text-gray-500">
                                 <Layers class="w-12 h-12 mb-3 text-gray-700" />
                                 <p class="text-sm font-medium">No applications found.</p>
                                 <p class="text-xs">Click "New Application" to get started.</p>
                             </div>
                         </td>
                     </tr>
                 </tbody>
             </table>
         </div>
      </div>
    </div>

    <!-- Application Form Modal -->
    <Modal :show="showModal" @close="showModal = false" maxWidth="md">
        <div class="p-6 bg-[#161616] border border-[#262626] overflow-hidden">
            <h2 class="text-xl font-bold text-white mb-6">
                {{ editingApp ? 'Edit Application' : 'Create New Application' }}
            </h2>

            <form @submit.prevent="submitForm" class="space-y-5">
                <!-- Name -->
                <div>
                    <InputLabel for="name" value="Application Name" class="text-gray-300 mb-1" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full bg-[#1a1a1a] border-[#333] text-white focus:border-indigo-500 focus:ring-indigo-500 placeholder-gray-600"
                        v-model="form.name"
                        required
                        placeholder="e.g. Finance Hub"
                    />
                    <InputError class="mt-2 text-red-400" :message="form.errors.name" />
                    <p class="text-[10px] text-gray-500 mt-1">A unique URL slug will be automatically generated from the name.</p>
                </div>

                <!-- Description -->
                <div>
                    <InputLabel for="description" value="Module Description" class="text-gray-300 mb-1" />
                    <textarea
                        id="description"
                        class="mt-1 block w-full rounded-lg bg-[#1a1a1a] border-[#333] text-white focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm p-3 min-h-[100px] placeholder-gray-600"
                        v-model="form.description"
                        required
                        placeholder="Brief overview of what this application handles..."
                    ></textarea>
                    <InputError class="mt-2 text-red-400" :message="form.errors.description" />
                </div>

                <!-- Status -->
                <div>
                    <InputLabel for="status" value="Operational Status" class="text-gray-300 mb-1" />
                    <select 
                        id="status" 
                        v-model="form.status" 
                        class="mt-1 block w-full bg-[#1a1a1a] border-[#333] text-white rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm h-10 px-3"
                    >
                        <option value="active">Active (Operational)</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <InputError class="mt-2 text-red-400" :message="form.errors.status" />
                </div>

                <!-- Actions -->
                <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-[#262626]">
                    <button 
                        type="button" 
                        @click="showModal = false"
                        class="px-4 py-2 text-sm font-bold text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-all"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="flex items-center gap-2 px-5 py-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-bold rounded-lg transition-all shadow-lg shadow-indigo-500/20 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ editingApp ? 'Update Application' : 'Create Application' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
  </DocsLayout>
</template>
