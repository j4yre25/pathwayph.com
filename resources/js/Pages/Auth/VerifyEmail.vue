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
    form.post(route('verification.resend'), {
        onSuccess: () => {
            alert('A new verification code has been sent to your email.');
        },
    });
};
</script>

<template>
    <Head title="Email Verification" />
    
    <!-- Modern Clean Background -->
    <div class="min-h-screen gradient-bg flex items-center justify-center p-4 relative">
        
        <div class="w-full max-w-md relative z-10">
            <!-- Logo Section -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 gradient-card rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <span class="text-white font-bold text-2xl enhanced-text">P</span>
                </div>
                <h2 class="text-4xl font-bold text-gray-800 mb-3 enhanced-text">Verify Your Email</h2>
                <p class="text-gray-600 text-lg">Enter the verification code sent to your <span class="text-blue-600 enhanced-text">email</span></p>
            </div>

            <!-- Clean Card -->
            <div class="bg-white p-8 rounded-3xl shadow-2xl border border-gray-200">
                <div class="mb-6 text-gray-600 text-center">
                    Please enter the verification code sent to your email address to verify your account. If you didn't receive the code, you can request a new one.
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email Field -->
                    <div>
                        <InputLabel for="email" value="Email Address" class="text-lg font-semibold text-gray-800 mb-2" />
                        <TextInput 
                            id="email" 
                            v-model="form.email" 
                            type="email" 
                            class="block w-full px-4 py-3 text-lg rounded-xl bg-gray-50 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-gray-800 placeholder-gray-400" 
                            placeholder="Your email address"
                            required 
                            autofocus
                            readonly />
                        <InputError class="mt-2 text-red-600" :message="form.errors.email" />
                    </div>

                    <!-- Verification Code Field -->
                    <div>
                        <InputLabel for="verification_code" value="Verification Code" class="text-lg font-semibold text-gray-800 mb-2" />
                        <TextInput 
                            id="verification_code" 
                            v-model="form.verification_code" 
                            type="text" 
                            class="block w-full px-4 py-3 text-lg rounded-xl bg-gray-50 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-gray-800 placeholder-gray-400 text-center tracking-widest" 
                            placeholder="Enter 6-digit code"
                            required 
                            maxlength="6" />
                        <InputError class="mt-2 text-red-600" :message="form.errors.verification_code" />
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit"
                            class="w-full px-8 py-4 gradient-cta text-white text-lg font-bold rounded-2xl transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-blue-400 shadow-lg disabled:opacity-50 disabled:transform-none"
                            :disabled="form.processing">
                            <span v-if="form.processing">Verifying...</span>
                            <span v-else>Verify Email</span>
                        </button>
                    </div>
                </form>

                <!-- Action Links -->
                <div class="mt-8 space-y-4">
                    <div class="text-center">
                        <button type="button" @click="(event) => { event.preventDefault(); resendCode(); }"
                            class="text-blue-600 hover:text-blue-800 font-semibold transition-all duration-200">
                            Resend Verification Code
                        </button>
                    </div>
                    
                    <div class="flex justify-center text-sm">
                        <Link :href="route('logout')" method="post" as="button"
                            class="text-blue-600 hover:text-blue-800 transition-all duration-200">
                            Log Out
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Modal -->
    <div v-if="showApprovalModal" class="fixed inset-0 flex items-center justify-center z-50 bg-black/60 backdrop-blur-sm">
        <div class="bg-white p-8 rounded-3xl shadow-2xl border border-gray-200 max-w-sm w-full mx-4">
            <div class="text-center">
                <div class="w-16 h-16 gradient-card rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-white text-2xl">...</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 enhanced-text mb-4">Waiting for Approval</h2>
                <p class="text-gray-600 mb-6 leading-relaxed">
                    Your account must be approved by your institution or PESO before you can resend the verification code. Please wait for approval.
                </p>
                <button @click="showApprovalModal = false"
                    class="px-6 py-3 gradient-cta text-white font-semibold rounded-xl transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-blue-400">
                    OK
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Background with subtle gradient matching LandingPage */
.gradient-bg {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    position: relative;
}

.gradient-card {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.gradient-feature {
    background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);
}

.gradient-cta {
    background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
}

/* Subtle text enhancement */
.enhanced-text {
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Smooth transitions */
* {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>