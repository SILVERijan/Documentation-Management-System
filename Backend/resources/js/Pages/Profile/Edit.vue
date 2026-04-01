<script setup lang="ts">
import DocsLayout from '@/Layouts/DocsLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';
import { 
  Users, Layers, LayoutGrid, FileText, Settings
} from 'lucide-vue-next';

import { ref, computed } from 'vue';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const user = computed(() => usePage().props.auth?.user);

const adminMenu = [
  { name: 'Dashboard', icon: LayoutGrid, active: false, href: route('admin.dashboard') },
  { name: 'User Management', icon: Users, active: false, href: route('admin.users.index') },
  { name: 'Application Management', icon: Layers, active: false, href: route('admin.applications.index') },
  { name: 'Documentation Management', icon: FileText, active: false, href: route('admin.documents.index') }
];
</script>

<template>
    <Head title="Profile Settings" />

    <DocsLayout :wide="true">
        <template v-if="user?.role === 'admin'" #left-sidebar>
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
                <div class="pt-4 mt-4 border-t border-[#262626]">
                    <Link 
                      :href="route('profile.edit')"
                      :class="cn(
                        'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group bg-indigo-500/10 text-white'
                      )"
                    >
                      <Settings class="w-4 h-4 shrink-0" />
                      Profile Settings
                    </Link>
                </div>
              </nav>
            </section>
          </div>
        </template>

        <div class="max-w-4xl mx-auto space-y-8">
          <!-- Header -->
          <div class="flex flex-col gap-1">
            <h1 class="text-3xl font-bold tracking-tight text-white">Profile Settings</h1>
            <p class="text-gray-500 text-sm">Update your account information, password, and security settings.</p>
          </div>

          <div class="space-y-6">
            <div
                class="bg-[#161616] border border-[#262626] p-6 shadow-2xl rounded-3xl sm:p-8"
            >
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-2xl"
                />
            </div>

            <div
                class="bg-[#161616] border border-[#262626] p-6 shadow-2xl rounded-3xl sm:p-8"
            >
                <UpdatePasswordForm class="max-w-2xl" />
            </div>

            <div
                class="bg-[#161616] border border-[#262626] p-6 shadow-2xl rounded-3xl sm:p-8"
            >
                <DeleteUserForm class="max-w-2xl" />
            </div>
          </div>
        </div>
    </DocsLayout>
</template>
