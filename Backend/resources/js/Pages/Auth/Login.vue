<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="p-3 text-sm font-medium text-green-600 bg-green-50 rounded-lg dark:bg-green-900/10 mb-4">
            {{ status }}
        </div>

        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Sign in to your account</h2>
            <p class="mt-2 text-sm text-gray-500 font-medium tracking-tight">Please enter your details to sign in</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="email" value="Email address" />
                <TextInput 
                    id="email"
                    v-model="form.email" 
                    type="email" 
                    required 
                    autofocus
                    class="mt-1 block w-full"
                    placeholder="m@example.com"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Password" />
                    <Link v-if="canResetPassword" :href="route('password.request')" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 font-bold transition-colors">
                        Forgot password?
                    </Link>
                </div>
                <TextInput 
                    id="password"
                    v-model="form.password" 
                    type="password" 
                    required 
                    class="mt-1 block w-full"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center">
                <Checkbox name="remember" v-model:checked="form.remember" class="rounded-md border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-indigo-600 focus:ring-indigo-500" />
                <span class="ms-2 text-sm font-medium text-gray-600 dark:text-gray-400">Remember me</span>
            </div>

            <PrimaryButton 
                class="w-full"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                <span v-if="form.processing">Signing in...</span>
                <span v-else>Sign in</span>
            </PrimaryButton>

            <div class="text-center mt-4">
               <p class="text-xs text-gray-500">
                   Don't have an account? 
                   <Link :href="route('register')" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline transition-all">Register</Link>
               </p>
            </div>
        </form>
    </GuestLayout>
</template>

