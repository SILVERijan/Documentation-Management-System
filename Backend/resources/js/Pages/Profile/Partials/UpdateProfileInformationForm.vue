<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps<{
    mustVerifyEmail?: Boolean;
    status?: String;
}>();

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    avatar: null as File | null,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                Update your account's profile information and email address.
            </p>
        </header>

        <form
            @submit.prevent="form.transform((data) => ({ ...data, _method: 'patch' })).post(route('profile.update'), { forceFormData: true })"
            class="mt-6 space-y-6"
        >
            <!-- Avatar Section -->
            <div class="flex items-center gap-6">
                <div class="relative">
                    <img
                        v-if="user.avatar"
                        :src="'/storage/' + user.avatar"
                        class="h-24 w-24 rounded-full object-cover ring-2 ring-indigo-500 shadow-lg"
                        alt="Profile Avatar"
                    />
                    <div
                        v-else
                        class="flex h-24 w-24 items-center justify-center rounded-full bg-indigo-100 ring-2 ring-indigo-500 shadow-lg dark:bg-indigo-900"
                    >
                        <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-300">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </span>
                    </div>
                </div>

                <div>
                    <InputLabel for="avatar" value="Profile Photo" />
                    <input
                        id="avatar"
                        type="file"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300"
                        @input="(e) => {
                            const target = e.target as HTMLInputElement;
                            if (target.files) form.avatar = target.files[0];
                        }"
                    />
                    <InputError class="mt-2" :message="form.errors.avatar" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Max size: 2MB (JPG, PNG)
                    </p>
                </div>
            </div>

            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800 dark:text-gray-200">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600 dark:text-green-400"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save Changes</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600 dark:text-gray-400"
                    >
                        Changes saved successfully.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
