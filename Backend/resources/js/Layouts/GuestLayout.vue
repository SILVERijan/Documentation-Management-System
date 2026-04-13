<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import ToastHandler from '@/Components/Common/ToastHandler.vue';
import ThemeSwitcher from '@/Components/Common/ThemeSwitcher.vue';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-slate-50 dark:bg-[#0a0a0f] pt-6 sm:justify-center sm:pt-0 relative overflow-hidden transition-colors duration-300"
    >
        <div class="absolute top-8 right-8 z-50">
            <ThemeSwitcher />
        </div>
        <ToastHandler />
        <!-- Background Glows -->
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-indigo-500/5 rounded-full filter blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-violet-500/5 rounded-full filter blur-[120px] pointer-events-none"></div>

        <div class="relative z-10">
            <Link href="/">
                <ApplicationLogo size="lg" show-name />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white dark:bg-[#16161a] px-6 py-4 border border-slate-200 dark:border-white/5 shadow-xl shadow-slate-200/50 dark:shadow-none sm:max-w-md sm:rounded-2xl relative z-10 transition-all duration-300"
        >
            <slot />
        </div>
    </div>
</template>
