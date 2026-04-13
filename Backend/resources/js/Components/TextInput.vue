<script setup lang="ts">
import { onMounted, ref } from 'vue';

const model = defineModel<string>({ required: true });

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        class="block w-full h-10 bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-[#262626] rounded-lg px-4 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all placeholder:text-gray-400 dark:placeholder:text-gray-600"
        v-model="model"
        ref="input"
    />
</template>
