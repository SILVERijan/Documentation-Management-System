<script setup lang="ts">
import { useTheme } from '@/lib/useTheme'
import { Sun, Moon } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

const { theme, setTheme } = useTheme()

const toggleTheme = () => {
    setTheme(theme.value === 'dark' ? 'light' : 'dark')
}
</script>

<template>
    <button
        @click="toggleTheme"
        class="group relative flex h-10 w-10 items-center justify-center rounded-xl bg-white/80 border border-slate-200 shadow-sm backdrop-blur-sm transition-all hover:bg-white hover:border-slate-300 dark:bg-white/5 dark:border-white/10 dark:hover:bg-white/10 dark:hover:border-white/20"
        :title="`Switch to ${theme === 'dark' ? 'light' : 'dark'} mode`"
    >
        <div class="relative h-5 w-5 overflow-hidden">
            <!-- Sun Icon for Light Mode -->
            <Sun 
                :class="cn(
                    'absolute inset-0 h-5 w-5 text-amber-500 transition-all duration-500',
                    theme === 'dark' ? 'translate-y-10 opacity-0' : 'translate-y-0 opacity-100'
                )" 
            />
            <!-- Moon Icon for Dark Mode -->
            <Moon 
                :class="cn(
                    'absolute inset-0 h-5 w-5 text-indigo-400 transition-all duration-500',
                    theme === 'dark' ? 'translate-y-0 opacity-100' : '-translate-y-10 opacity-0'
                )" 
            />
        </div>
        
        <!-- Subtle Glow Effect -->
        <span 
            :class="cn(
                'absolute inset-0 rounded-xl opacity-0 transition-opacity group-hover:opacity-10 dark:bg-indigo-400 bg-amber-400',
            )"
        />
    </button>
</template>
