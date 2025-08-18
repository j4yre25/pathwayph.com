<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';

const { props } = usePage(); // Access the page props
const email = props.auth.user.email || '';

// Create an array of 6 refs for the 6 input fields
const codeInputs = ref(['', '', '', '', '', '']);
const inputRefs = ref([]);

// Create a form with the email and verification code
const form = useForm({
    email: email,
    verification_code: '',
});

// Watch for changes in the code inputs and update the form
watch(codeInputs, (newValues) => {
    form.verification_code = newValues.join('');
}, { deep: true });

// Handle input in each digit field
const handleInput = (index, event) => {
    const value = event.target.value;
    
    // Only allow numbers
    if (!/^\d*$/.test(value)) {
        codeInputs.value[index] = '';
        return;
    }
    
    // If the user enters multiple digits, only take the last one
    if (value.length > 1) {
        codeInputs.value[index] = value.slice(-1);
    }
    
    // Move to the next input if this one is filled
    if (value && index < 5) {
        inputRefs.value[index + 1].focus();
    }
};

// Handle backspace key
const handleKeydown = (index, event) => {
    if (event.key === 'Backspace' && !codeInputs.value[index] && index > 0) {
        // Move to the previous input if current is empty and backspace is pressed
        inputRefs.value[index - 1].focus();
    }
};

// Handle paste event
const handlePaste = (event) => {
    event.preventDefault();
    const pastedData = event.clipboardData.getData('text');
    const digits = pastedData.replace(/\D/g, '').slice(0, 6).split('');
    
    digits.forEach((digit, index) => {
        if (index < 6) {
            codeInputs.value[index] = digit;
        }
    });
    
    // Focus the next empty input or the last one if all are filled
    const nextEmptyIndex = codeInputs.value.findIndex(val => !val);
    if (nextEmptyIndex !== -1) {
        inputRefs.value[nextEmptyIndex].focus();
    } else if (inputRefs.value[5]) {
        inputRefs.value[5].focus();
    }
};

// Submit the form
const submit = () => {
    form.post(route('verify.code'), {
        onSuccess: () => {
            // Reset the form after successful submission
            codeInputs.value = ['', '', '', '', '', ''];
            form.reset('verification_code');
        },
    });
};

// Resend verification code
const resendCode = () => {
    form.post(route('verification.resend'), {
        onSuccess: () => {
            // Show success message
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

        <template #loginForm>
            <div class="text-center mb-6">
                <h1 class="text-xl font-semibold mb-2">Check your email for a code</h1>
                <p class="text-sm text-gray-600">
                    Enter the 6-digit code sent to<br>
                    <span class="font-medium">{{ email }}</span>
                </p>
            </div>

            <form @submit.prevent="submit">
                <!-- Verification Code Input -->
                <div class="flex justify-center space-x-2 mb-6">
                    <template v-for="(digit, index) in codeInputs" :key="index">
                        <input 
                            type="text"
                            maxlength="1"
                            class="w-12 h-12 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-center text-xl"
                            v-model="codeInputs[index]"
                            @input="handleInput(index, $event)"
                            @keydown="handleKeydown(index, $event)"
                            @paste="handlePaste"
                            :ref="el => { if(el) inputRefs[index] = el }"
                            :tabindex="index + 1"
                        >
                        <!-- Add a separator dot after the third digit -->
                        <span v-if="index === 2" class="flex items-center justify-center text-gray-400 text-xl">·</span>
                    </template>
                </div>

                <InputError :message="form.errors.verification_code" class="mt-2 text-center" />

                <!-- Submit Button -->
                <div class="mt-6">
                    <PrimaryButton 
                        type="submit" 
                        class="w-full justify-center py-3"
                        :class="{ 'opacity-25': form.processing }" 
                        :disabled="form.processing || form.verification_code.length !== 6"
                    >
                        Sign in
                    </PrimaryButton>
                </div>
            </form>

            <div class="mt-4 text-center">
                <p class="text-sm text-gray-600 mb-2">
                    Can't find it? It may take a minute.<br>
                    Check your spam or
                    <button 
                        type="button" 
                        @click="resendCode"
                        class="text-indigo-600 hover:text-indigo-800 font-medium"
                        :disabled="form.processing"
                    >
                        resend the code
                    </button>
                </p>
                <Link 
                    :href="route('login')" 
                    class="text-sm text-gray-600 hover:text-gray-900 font-medium"
                >
                    Back to sign in options
                </Link>
            </div>
        </template>
    </AuthenticationCard>
</template>