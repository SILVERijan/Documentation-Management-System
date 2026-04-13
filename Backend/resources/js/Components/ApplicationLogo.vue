<template>
    <div :class="['brand-logo-wrapper', size]">
        <div class="brand-icon overflow-hidden">
            <img src="/images/logo.jpg" alt="SILVER Docs Logo" class="w-full h-full object-cover">
        </div>
        <span v-if="showName" class="brand-name text-slate-900 dark:text-white">SILVER <span class="brand-accent text-indigo-600 dark:text-indigo-400">Docs</span></span>
    </div>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
    showName?: boolean;
    size?: 'sm' | 'md' | 'lg';
}>(), {
    showName: false,
    size: 'md'
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

.brand-logo-wrapper {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    font-family: 'Inter', sans-serif;
}

.brand-icon {
  background: white; /* Matches the white background of the provided artwork */
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* ── Sizes ── */
.sm .brand-icon {
    width: 32px; height: 32px;
    border-radius: 6px;
}
.sm .brand-name {
    font-size: 16px;
}

.md .brand-icon {
    width: 48px; height: 48px;
    border-radius: 10px;
}
.md .brand-name {
    font-size: 20px;
}

.lg {
    flex-direction: column;
    gap: 20px;
}
.lg .brand-icon {
    width: 120px; height: 120px;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}
.lg .brand-name {
    font-size: 32px;
    letter-spacing: -2px;
}

/* ── Colors ── */

.brand-name {
  font-weight: 900;
  letter-spacing: -0.3px;
}
</style>
