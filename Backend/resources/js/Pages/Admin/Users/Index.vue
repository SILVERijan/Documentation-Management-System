<script setup lang="ts">
import DocsLayout from '@/Layouts/DocsLayout.vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { cn } from '@/lib/utils'
import { 
  Users, Layers, LayoutGrid, ShieldCheck, Activity, FileText,
  Plus, Edit2, Trash2, ShieldAlert, CheckCircle2, XCircle
} from 'lucide-vue-next'
import { ref } from 'vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps<{
  users: Array<any>;
  assignableApplications: Array<{ id: number; name: string; }>;
}>()

const adminMenu = [
  { name: 'Dashboard', icon: LayoutGrid, active: false, href: route('admin.dashboard') },
  { name: 'User Management', icon: Users, active: true, href: route('admin.users.index') },
  { name: 'Application Management', icon: Layers, active: false, href: route('admin.applications.index') },
  { name: 'Documentation Management', icon: FileText, active: false, href: route('admin.documents.index') },
  { name: 'Activity Log', icon: Activity, active: false, href: route('admin.activity') },
  { name: 'My Documents', icon: FileText, active: false, href: route('user.documents.index') }
]

const showModal = ref(false)
const editingUser = ref<any>(null)

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user',
    status: 'active',
    applications: [] as number[],
})

const openCreateModal = () => {
    editingUser.value = null
    form.reset()
    form.clearErrors()
    showModal.value = true
}

const openEditModal = (user: any) => {
    editingUser.value = user
    form.reset()
    form.clearErrors()
    form.name = user.name
    form.email = user.email
    form.role = user.role
    form.status = user.status
    form.applications = user.applications.map((app: any) => app.id)
    showModal.value = true
}

const submitForm = () => {
    if (editingUser.value) {
        form.put(route('admin.users.update', editingUser.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false
            }
        })
    } else {
        form.post(route('admin.users.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false
                form.reset()
            }
        })
    }
}

const deleteUser = (user: any) => {
    if (confirm(`Are you sure you want to delete ${user.name}? This action cannot be undone.`)) {
        router.delete(route('admin.users.destroy', user.id), {
            preserveScroll: true,
        })
    }
}
</script>

<template>
  <DocsLayout wide>
    <Head title="User Management - Admin Portal" />

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

    <div class="space-y-8 max-w-6xl">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mb-1">User Management</h1>
          <p class="text-gray-500 text-sm">Control user access, roles, and assigned application permissions.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="openCreateModal" class="flex items-center gap-2 px-3 py-1.5 h-9 bg-indigo-500 text-white rounded-lg text-xs font-bold hover:bg-indigo-600 transition-all shadow-lg shadow-indigo-500/20">
                <Plus class="w-3.5 h-3.5" />
                Add New User
            </button>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] rounded-3xl overflow-hidden shadow-2xl">
         <div class="overflow-x-auto">
             <table class="w-full text-left text-sm text-gray-400">
                 <thead class="bg-slate-50 dark:bg-[#1a1a1a] text-xs uppercase text-gray-500 border-b border-slate-200 dark:border-[#262626]">
                     <tr>
                         <th class="px-6 py-4 font-bold tracking-wider">Name / Email</th>
                         <th class="px-6 py-4 font-bold tracking-wider">Role</th>
                         <th class="px-6 py-4 font-bold tracking-wider">Status</th>
                         <th class="px-6 py-4 font-bold tracking-wider">Assigned Apps</th>
                         <th class="px-6 py-4 font-bold tracking-wider text-right">Actions</th>
                     </tr>
                 </thead>
                 <tbody class="divide-y divide-slate-200 dark:divide-[#262626]">
                     <tr v-for="user in props.users" :key="user.id" class="hover:bg-slate-100 dark:hover:bg-[#1f1f1f] transition-colors group">
                         <td class="px-6 py-4">
                             <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex flex-shrink-0 items-center justify-center text-indigo-400 font-bold shadow-inner">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-slate-900 dark:text-white font-bold group-hover:text-indigo-300 transition-colors">{{ user.name }}</span>
                                    <span class="text-[11px] text-gray-500">{{ user.email }}</span>
                                </div>
                             </div>
                         </td>
                         <td class="px-6 py-4">
                             <div :class="cn(
                                 'inline-flex items-center gap-1.5 px-2 py-1 rounded text-[10px] font-black uppercase tracking-widest',
                                 user.role === 'admin' ? 'bg-purple-500/10 text-purple-400' : 'bg-gray-800 text-gray-400'
                             )">
                                 <ShieldAlert v-if="user.role === 'admin'" class="w-3 h-3" />
                                 <Users v-else class="w-3 h-3" />
                                 {{ user.role }}
                             </div>
                         </td>
                         <td class="px-6 py-4">
                             <div :class="cn(
                                 'inline-flex items-center gap-1.5 px-2 py-1 rounded text-[10px] font-black uppercase tracking-widest',
                                 user.status === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400'
                             )">
                                 <CheckCircle2 v-if="user.status === 'active'" class="w-3 h-3" />
                                 <XCircle v-else class="w-3 h-3" />
                                 {{ user.status }}
                             </div>
                         </td>
                         <td class="px-6 py-4">
                             <div class="flex flex-wrap gap-1.5 max-w-[200px]">
                                 <span v-if="user.applications.length === 0" class="text-[10px] italic text-gray-600">None</span>
                                 <span 
                                     v-for="app in user.applications" 
                                     :key="app.id"
                                     class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-[#262626] border border-slate-200 dark:border-[#333] text-[10px] font-bold text-slate-700 dark:text-gray-300"
                                  >
                                     {{ app.name }}
                                 </span>
                             </div>
                         </td>
                         <td class="px-6 py-4 text-right">
                             <div class="flex items-center justify-end gap-2">
                                 <button @click="openEditModal(user)" class="p-2 text-gray-500 hover:text-indigo-400 hover:bg-indigo-500/10 rounded-lg transition-all" title="Edit User">
                                     <Edit2 class="w-4 h-4" />
                                 </button>
                                 <button @click="deleteUser(user)" class="p-2 text-gray-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all" title="Delete User">
                                     <Trash2 class="w-4 h-4" />
                                 </button>
                             </div>
                         </td>
                     </tr>
                     <tr v-if="props.users.length === 0">
                         <td colspan="5" class="px-6 py-12 text-center">
                             <div class="flex flex-col items-center justify-center text-gray-500">
                                 <Users class="w-12 h-12 mb-3 text-gray-700" />
                                 <p class="text-sm font-medium">No users found.</p>
                                 <p class="text-xs">Click "Add New User" to get started.</p>
                             </div>
                         </td>
                     </tr>
                 </tbody>
             </table>
         </div>
      </div>
    </div>

    <!-- User Form Modal -->
    <Modal :show="showModal" @close="showModal = false" maxWidth="md">
        <div class="p-6 bg-white dark:bg-[#161616] border border-slate-200 dark:border-[#262626] overflow-hidden">
            <h2 class="text-xl font-bold text-white mb-6">
                {{ editingUser ? 'Edit User' : 'Create New User' }}
            </h2>

            <form @submit.prevent="submitForm" class="space-y-5">
                <!-- Name -->
                <div>
                    <InputLabel for="name" value="Full Name" class="text-slate-700 dark:text-gray-300 mb-1" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full bg-slate-50 dark:bg-[#1a1a1a] border-slate-200 dark:border-[#333] text-white focus:border-indigo-500 focus:ring-indigo-500 placeholder-gray-600"
                        v-model="form.name"
                        required
                        placeholder="e.g. Jane Doe"
                    />
                    <InputError class="mt-2 text-red-400" :message="form.errors.name" />
                </div>

                <!-- Email -->
                <div>
                    <InputLabel for="email" value="Email Address" class="text-slate-700 dark:text-gray-300 mb-1" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full bg-slate-50 dark:bg-[#1a1a1a] border-slate-200 dark:border-[#333] text-white focus:border-indigo-500 focus:ring-indigo-500 placeholder-gray-600"
                        v-model="form.email"
                        required
                        placeholder="e.g. jane@example.com"
                    />
                    <InputError class="mt-2 text-red-400" :message="form.errors.email" />
                </div>

                <!-- Password -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="password" :value="editingUser ? 'New Password' : 'Password'" class="text-slate-700 dark:text-gray-300 mb-1" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full bg-slate-50 dark:bg-[#1a1a1a] border-slate-200 dark:border-[#333] text-white focus:border-indigo-500 focus:ring-indigo-500 placeholder-gray-600"
                            v-model="form.password"
                            :required="!editingUser"
                            placeholder="••••••••"
                        />
                        <InputError class="mt-2 text-red-400" :message="form.errors.password" />
                        <p v-if="editingUser" class="text-[10px] text-gray-500 mt-1">Leave blank to keep current password.</p>
                    </div>
                    <div>
                        <InputLabel for="password_confirmation" value="Confirm Password" class="text-slate-700 dark:text-gray-300 mb-1" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="mt-1 block w-full bg-slate-50 dark:bg-[#1a1a1a] border-slate-200 dark:border-[#333] text-white focus:border-indigo-500 focus:ring-indigo-500 placeholder-gray-600"
                            v-model="form.password_confirmation"
                            :required="!editingUser && form.password.length > 0"
                            placeholder="••••••••"
                        />
                    </div>
                </div>

                <!-- Role & Status -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="role" value="User Role" class="text-slate-700 dark:text-gray-300 mb-1" />
                        <select 
                            id="role" 
                            v-model="form.role" 
                            class="mt-1 block w-full bg-slate-50 dark:bg-[#1a1a1a] border-slate-200 dark:border-[#333] text-white rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm h-10 px-3"
                        >
                            <option value="user">Normal User</option>
                            <option value="admin">Administrator</option>
                        </select>
                        <InputError class="mt-2 text-red-400" :message="form.errors.role" />
                    </div>
                    <div>
                        <InputLabel for="status" value="Account Status" class="text-slate-700 dark:text-gray-300 mb-1" />
                        <select 
                            id="status" 
                            v-model="form.status" 
                            class="mt-1 block w-full bg-slate-50 dark:bg-[#1a1a1a] border-slate-200 dark:border-[#333] text-white rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm h-10 px-3"
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <InputError class="mt-2 text-red-400" :message="form.errors.status" />
                    </div>
                </div>

                <!-- Assigned Applications -->
                <div class="pt-4 border-t border-slate-200 dark:border-[#262626]">
                    <InputLabel value="Assigned Applications" class="text-slate-700 dark:text-gray-300 mb-3 font-bold" />
                    <div class="grid grid-cols-2 gap-3 max-h-[160px] overflow-y-auto pr-2 custom-scrollbar">
                        <label 
                            v-for="app in props.assignableApplications" 
                            :key="app.id"
                            class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-[#333] bg-slate-50 dark:bg-[#1a1a1a] cursor-pointer hover:border-indigo-500/50 hover:bg-slate-100 dark:hover:bg-[#1f1f1f] transition-all group"
                        >
                            <input 
                                type="checkbox" 
                                :value="app.id" 
                                v-model="form.applications"
                                class="rounded border-gray-600 bg-gray-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-[#1a1a1a] w-4 h-4 transition-all"
                            >
                            <span class="text-sm font-medium text-slate-700 dark:text-gray-300 group-hover:text-slate-900 dark:text-white transition-colors">{{ app.name }}</span>
                        </label>
                    </div>
                    <p v-if="props.assignableApplications.length === 0" class="text-xs text-gray-500 mt-2 p-3 bg-slate-50 dark:bg-[#1a1a1a] rounded-xl border border-slate-200 dark:border-[#333]">No applications exist in the system yet.</p>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-slate-200 dark:border-[#262626]">
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
                        {{ editingUser ? 'Update User' : 'Create User' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
  </DocsLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #333;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #444;
}
</style>
