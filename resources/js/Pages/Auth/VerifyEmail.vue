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
    
    <!-- Modern Gradient Background -->
    <div class="min-h-screen gradient-bg flex items-center justify-center p-4 overflow-hidden relative">
        <!-- Floating Background Elements -->
        <div class="absolute inset-0">
            <div class="absolute top-10 left-10 w-32 h-32 gradient-card rounded-full opacity-20 animate-float"></div>
            <div class="absolute top-1/4 right-20 w-24 h-24 gradient-feature rounded-full opacity-30 animate-float-reverse"></div>
            <div class="absolute bottom-20 left-1/4 w-40 h-40 gradient-cta rounded-full opacity-15 animate-morph"></div>
            <div class="absolute top-1/2 right-1/3 w-16 h-16 bg-white rounded-full opacity-10 animate-pulse-glow"></div>
        </div>
        
        <div class="w-full max-w-md relative z-10">
            <!-- Logo Section -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 gradient-card rounded-2xl flex items-center justify-center mx-auto mb-4 animate-pulse-glow">
                    <span class="text-white font-bold text-2xl neon-text">P</span>
                </div>
                <h2 class="text-4xl font-bold text-white mb-3 neon-text">Verify Your Email</h2>
                <p class="text-white/80 text-lg">Enter the verification code sent to your <span class="text-cyan-300 neon-text">email</span></p>
            </div>

            <!-- Glass Card -->
            <div class="glass p-8 rounded-3xl shadow-2xl border border-white/20 backdrop-blur-xl">
                <div class="mb-6 text-white/80 text-center">
                    Please enter the verification code sent to your email address to verify your account. If you didn't receive the code, you can request a new one.
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email Field -->
                    <div>
                        <InputLabel for="email" value="Email Address" class="text-lg font-semibold text-white mb-2" />
                        <TextInput 
                            id="email" 
                            v-model="form.email" 
                            type="email" 
                            class="block w-full px-4 py-3 text-lg rounded-xl glass border border-white/30 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400 text-white placeholder-white/60" 
                            placeholder="Your email address"
                            required 
                            autofocus
                            readonly />
                        <InputError class="mt-2 text-pink-300" :message="form.errors.email" />
                    </div>

                    <!-- Verification Code Field -->
                    <div>
                        <InputLabel for="verification_code" value="Verification Code" class="text-lg font-semibold text-white mb-2" />
                        <TextInput 
                            id="verification_code" 
                            v-model="form.verification_code" 
                            type="text" 
                            class="block w-full px-4 py-3 text-lg rounded-xl glass border border-white/30 focus:border-purple-400 focus:ring-2 focus:ring-purple-400 text-white placeholder-white/60 text-center tracking-widest" 
                            placeholder="Enter 6-digit code"
                            required 
                            maxlength="6" />
                        <InputError class="mt-2 text-pink-300" :message="form.errors.verification_code" />
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit"
                            class="w-full px-8 py-4 gradient-cta text-white text-lg font-bold rounded-2xl hover-rainbow transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-green-400 shadow-2xl disabled:opacity-50 disabled:transform-none animate-pulse-glow"
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
                            class="text-cyan-300 hover:text-cyan-100 hover:neon-text font-semibold transition-all duration-200">
                            Resend Verification Code
                        </button>
                    </div>
                    
                    <div class="flex justify-center text-sm">
                        <Link :href="route('logout')" method="post" as="button"
                            class="text-pink-300 hover:text-pink-100 hover:neon-text transition-all duration-200">
                            Log Out
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Modal -->
    <div v-if="showApprovalModal" class="fixed inset-0 flex items-center justify-center z-50 bg-black/60 backdrop-blur-sm">
        <div class="glass p-8 rounded-3xl shadow-2xl border border-white/20 max-w-sm w-full mx-4 animate-pulse-glow">
            <div class="text-center">
                <div class="w-16 h-16 gradient-card rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl">...</span>
                </div>
                <h2 class="text-2xl font-bold text-white neon-text mb-4">Waiting for Approval</h2>
                <p class="text-white/80 mb-6 leading-relaxed">
                    Your account must be approved by your institution or PESO before you can resend the verification code. Please wait for approval.
                </p>
                <button @click="showApprovalModal = false"
                    class="px-6 py-3 gradient-cta text-white font-semibold rounded-xl hover-rainbow transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-green-400">
                    OK
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Modern gradient backgrounds */
.gradient-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.gradient-card {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.gradient-feature {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.gradient-cta {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

/* Floating animations */
@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

@keyframes float-reverse {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(20px);
    }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.animate-float-reverse {
    animation: float-reverse 4s ease-in-out infinite;
}

/* Pulse glow effect */
@keyframes pulse-glow {
    0%, 100% {
        box-shadow: 0 0 20px rgba(79, 172, 254, 0.3);
    }
    50% {
        box-shadow: 0 0 40px rgba(79, 172, 254, 0.6);
    }
}

.animate-pulse-glow {
    animation: pulse-glow 2s ease-in-out infinite;
}

/* Morphing shapes */
@keyframes morph {
    0%, 100% {
        border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
    }
    50% {
        border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%;
    }
}

.animate-morph {
    animation: morph 8s ease-in-out infinite;
}

/* Colorful hover effects */
.hover-rainbow:hover {
    background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4, #feca57, #ff9ff3);
    background-size: 400% 400%;
    animation: rainbow 2s ease infinite;
}

@keyframes rainbow {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Glass morphism effect */
.glass {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Neon glow text */
.neon-text {
    text-shadow: 0 0 5px currentColor, 0 0 10px currentColor, 0 0 15px currentColor;
}

/* Smooth transitions */
* {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>