<script setup lang="ts">
import DocsLayout from '@/Layouts/DocsLayout.vue'
import SidebarNav from '@/Components/Docs/SidebarNav.vue'
import TOCNav from '@/Components/Docs/TOCNav.vue'
import Badge from '@/Components/ui/Badge.vue'
import { Link } from '@inertiajs/vue3'
import { cn } from '@/lib/utils'

const applications = [
  { name: 'Human Resources', color: 'bg-indigo-500', active: true },
  { name: 'Finance', color: 'bg-emerald-500' },
  { name: 'CRM', color: 'bg-orange-500' },
  { name: 'IT Support', color: 'bg-blue-500' }
]

const hrDocs = [
  { name: 'Employee Handbook' },
  { name: 'Onboarding Guide', active: true },
  { name: 'Leave Policy' },
  { name: 'Performance Review' },
  { name: 'Code of Conduct' }
]

const tocItems = [
  { id: 'getting-started', title: 'Getting started', active: true },
  { id: 'account-setup', title: 'Account setup' },
  { id: 'tools-and-access', title: 'Tools and access' },
  { id: 'first-week', title: 'Your first week' },
  { id: 'team-intros', title: 'Team introductions' },
  { id: 'policies', title: 'Policies to review' }
]
</script>

<template>
  <DocsLayout>
    <!-- Top Nav Applications (Now in Dropdown) -->
    <template #top-nav-apps>
        <div class="space-y-1">
            <Link 
              v-for="app in applications" 
              :key="app.name"
              href="#"
              :class="cn(
                'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all group',
                app.active ? 'bg-indigo-500/10 text-white' : 'text-gray-400 hover:text-slate-900 dark:text-white hover:bg-white/5'
              )"
            >
              <div :class="cn('w-2 h-2 rounded-full', app.color)" />
              {{ app.name }}
              <div v-if="app.active" class="absolute left-1 w-1 h-4 bg-indigo-500 rounded-full" />
            </Link>
        </div>
    </template>

    <!-- Left Sidebar Content -->
    <template #left-sidebar>
      <div class="p-6">
        <h3 class="text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-6 px-2">HR DOCUMENTS</h3>
        <nav class="space-y-1">
          <Link 
            v-for="doc in hrDocs" 
            :key="doc.name"
            href="#"
            :class="cn(
                'block px-3 py-1.5 rounded-lg text-sm font-medium transition-all',
                doc.active ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-slate-900 dark:text-white hover:bg-white/5'
            )"
          >
            {{ doc.name }}
          </Link>
        </nav>
      </div>
    </template>

    <!-- Main Content -->
    <div class="space-y-12">
      <!-- Doc Header -->
      <header class="space-y-6">
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight">Onboarding Guide</h1>
        
        <div class="flex items-center gap-6 text-sm">
           <div class="flex flex-col gap-1">
               <span class="text-gray-500 font-bold uppercase tracking-wider text-[10px]">Application</span>
               <span class="text-slate-900 dark:text-white font-medium">Human Resources</span>
           </div>
           <div class="h-8 w-px bg-[#262626]" />
           <div class="flex flex-col gap-1">
               <span class="text-gray-500 font-bold uppercase tracking-wider text-[10px]">Updated</span>
               <span class="text-slate-900 dark:text-white font-medium">2 days ago</span>
           </div>
           <div class="h-8 w-px bg-[#262626]" />
           <Badge variant="published">Published</Badge>
        </div>
      </header>

      <!-- Content Sections -->
      <div class="prose prose-invert max-w-none space-y-10">
        <p class="text-lg text-gray-400 leading-relaxed max-w-2xl">
          Welcome to the company. This guide walks you through everything you need to get started in your first week — from setting up your accounts to understanding our team culture.
        </p>

        <section id="getting-started" class="space-y-4 pt-10 border-t border-slate-200 dark:border-[#262626]">
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Getting started</h2>
          <p class="text-gray-400 leading-relaxed">
            Before your first day, make sure you have received your credentials via email. Your IT team will have set up your primary accounts.
          </p>
        </section>

        <section id="account-setup" class="space-y-4 pt-10 border-t border-slate-200 dark:border-[#262626]">
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Account setup</h2>
          <p class="text-gray-400 leading-relaxed">
            Log in to the company portal using the temporary password provided. You will be prompted to change it on your first login.
          </p>
          <div class="bg-indigo-500/5 border border-indigo-500/20 p-6 rounded-2xl flex items-center gap-4">
             <div class="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0">
                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
             </div>
             <p class="text-sm text-indigo-400 font-medium">Multi-factor authentication (MFA) is required for all company accounts.</p>
          </div>
        </section>
      </div>
    </div>

    <!-- Right Sidebar Content -->
    <template #right-sidebar>
      <TOCNav :items="tocItems" />
    </template>
  </DocsLayout>
</template>
