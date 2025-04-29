<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import LoginCard from '@/Components/LoginCard.vue';
import { Inertia } from '@inertiajs/inertia';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';


defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});


const showModal = ref(false); 
const modalTitle = ref(''); 
const modalMessage = ref('');


const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
        onError: (errors) => {
            console.log('Errors:', errors); // Debugging
            if (errors.password) {
                modalTitle.value = 'Login Error';
                modalMessage.value = errors.password; // Set the password error message
                showModal.value = true; 
            } else if (errors.email) {
                modalTitle.value = 'Login Error';
                modalMessage.value = errors.email; // Set the password error message
                showModal.value = true
            }
        },
        onSuccess: () => {

            Inertia.visit(route('dashboard'));
        },
    });
};

</script>

<template>

    <Head title="Log in" />

    <!-- UPDATE 04/04 Changes from AuthenticationCard to LoginCard. Putting form inside a template -->
    <LoginCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>


        <template #loginForm>
            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="mt-1 block w-full"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="mt-4">
                    <InputLabel for="password" value="Password" />
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        required
                        autocomplete="current-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="block mt-4">
                    <label class="flex items-center">
                        <Checkbox v-model:checked="form.remember" name="remember" />
                        <span class="ms-2 text-sm text-gray-600">Remember me</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <Link v-if="canResetPassword" :href="route('password.request')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Forgot your password?
                    </Link>

                    <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Log in
                    </PrimaryButton>
                </div>
            </form>
        </template>
    </LoginCard>
    <Modal v-if="showModal" :show="showModal" @close="showModal = false">
        <template #title>
            {{ modalTitle }}
        </template>
        <template #content>
            <p>{{ modalMessage }}</p>
        </template>
        <template #footer>
            <PrimaryButton @click="showModal = false">Close</PrimaryButton>
        </template>
    </Modal>
</template>
