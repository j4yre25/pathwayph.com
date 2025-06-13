<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const { props } = usePage(); // Access the page props
const form = useForm({
    email: props.auth.user.email || '',
    verification_code: '',
});


console.log(props.auth.user.is_approved)
const showApprovalModal = ref(false);
const isApproved = props.auth.user.is_approved ?? false; // Pass this from backend

const submit = () => {
    form.post(route('verify.code'), {
        onSuccess: () => {
            form.reset('verification_code');
        },
    });
};

const resendCode = () => {
    if (!isApproved) {
        showApprovalModal.value = true;
        return;
    }
    form.post(route('verification.resend'), {
        onSuccess: () => {
            alert('A new verification code has been sent to your email.');
        },
    });
};
</script>

<template>
    <Head title="Email Verification" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <template #registerForm>
            <div class="mb-4 text-sm text-gray-600">
                Please enter the verification code sent to your email address to verify your account. If you didn't
                receive the code, you can request a new one.
            </div>

            <form @submit.prevent="submit">
                <!-- Email Field -->
                <div>
                    <InputLabel for="email" value="Email Address" />
                    <TextInput id="email" type="email" v-model="form.email" class="mt-1 block w-full" required autofocus />
                    <InputError :message="form.errors.email" class="mt-2" />
                </div>

                <!-- Verification Code Field -->
                <div class="mt-4">
                    <InputLabel for="verification_code" value="Verification Code" />
                    <TextInput id="verification_code" type="text" v-model="form.verification_code"
                        class="mt-1 block w-full" required />
                    <InputError :message="form.errors.verification_code" class="mt-2" />
                </div>

                <!-- Submit Button -->
                <div class="mt-4 flex items-center justify-between">
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Verify Email
                    </PrimaryButton>

                    <button type="button" @click="(event) => { event.preventDefault(); resendCode(); }"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Resend Verification Code
                    </button>
                </div>
            </form>

            <div class="mt-4">
                <Link :href="route('logout')" method="post" as="button"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Log Out
                </Link>
            </div>
        </template>
    </AuthenticationCard>

    <!-- Approval Modal -->
    <div v-if="showApprovalModal" class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full">
            <h2 class="text-lg font-semibold mb-2">Waiting for Approval</h2>
            <p class="mb-4">Your account must be approved by your institution or PESO before you can resend the verification code. Please wait for approval.</p>
            <button @click="showApprovalModal = false"
                class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">OK</button>
        </div>
    </div>
</template>