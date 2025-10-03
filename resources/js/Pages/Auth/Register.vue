<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const selectedUserLevel = ref('');
const currentStep = ref('user-level');
const schools = ref([]);
const programs = ref([]);
const terms = ref([]);
const degrees = ref([]);
const loading = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);

// Form data
const form = useForm({
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
    terms: false,
});

onMounted(() => {
    const path = window.location.pathname;
    if (path.includes('institution')) {
        form.role = 'institution';
        currentStep.value = 'institution';
    } else if (path.includes('graduate')) {
        form.role = 'graduate';
        currentStep.value = 'graduate';
    } else if (path.includes('company')) {
        form.role = 'company';
        currentStep.value = 'company';
    } else {
        currentStep.value = 'user-level';
    }
});


const selectUserLevel = (level) => {
    selectedUserLevel.value = level;

    if (level === 'graduates') {
        // Navigate to graduate registration URL
        window.location.href = '/register/graduate';
    } else if (level === 'institution') {
        // Navigate to institution registration URL
        window.location.href = '/register/institution';
    } else if (level === 'industry') {
        // Navigate to company registration URL
        window.location.href = '/register/company';
    }
}

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const toggleConfirmPasswordVisibility = () => {
    showConfirmPassword.value = !showConfirmPassword.value;
};

const submitGraduateAccount = () => {
    form.post(route('register.graduate.store'), {
        onSuccess: () => {
            window.location.href = route('graduate.information');
        }
    });
};

const submitCompanyAccount = () => {
    form.post(route('register.company.store'), {
        onSuccess: () => {
            window.location.href = route('company.information');
        }
    });
};

const submitInstitutionAccount = () => {
    form.post(route('register.institution.store'), {
        onSuccess: () => {
            window.location.href = route('institution.information');
        }
    });
};

// Password strength and requirements logic
const passwordStrength = ref(0);
const strengthText = computed(() => {
    switch (passwordStrength.value) {
        case 1: return { text: 'Very Weak', color: 'bg-red-400' };
        case 2: return { text: 'Weak', color: 'bg-orange-400' };
        case 3: return { text: 'Fair', color: 'bg-yellow-400' };
        case 4: return { text: 'Strong', color: 'bg-blue-400' };
        case 5: return { text: 'Very Strong', color: 'bg-emerald-500' };
        default: return { text: 'Enter password', color: 'bg-white/20' };
    }
});

const minLengthMet = computed(() => form.password.length >= 8);
const hasUpperCaseMet = computed(() => /[A-Z]/.test(form.password));
const hasLowerCaseMet = computed(() => /[a-z]/.test(form.password));
const hasNumberMet = computed(() => /[0-9]/.test(form.password));
const hasSpecialCharMet = computed(() => /[!@#$%^&*.]/.test(form.password));

function updatePasswordStrength() {
    let strength = 0;
    if (minLengthMet.value) strength++;
    if (hasUpperCaseMet.value) strength++;
    if (hasLowerCaseMet.value) strength++;
    if (hasNumberMet.value) strength++;
    if (hasSpecialCharMet.value) strength++;
    passwordStrength.value = strength;
}
</script>

<template>

    <Head title="Sign Up" />

    <!-- Clean Gradient Background -->
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">

        <div class="w-full max-w-lg relative z-10">
            <!-- Logo Section -->
            <div class="text-center mb-8">
                <div class="w-16 h-16  rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <img src="/images/pathway-logo.png" alt="Pathway Logo" class="w-10 h-10 rounded-xl" />
                </div>
                <h2 class="text-4xl font-bold text-slate-800 mb-3">Join Pathway</h2>
                <p class="text-slate-600 text-lg">Create your <span class="text-blue-600">Pathway</span> account</p>
            </div>

            <!-- Clean Card -->
            <div class="bg-white p-8 rounded-3xl shadow-lg border border-gray-200">
                <!-- User Level Selection -->
                <div v-if="currentStep === 'user-level'" class="space-y-6">
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-bold text-slate-800 mb-2">Select User Type</h3>
                        <p class="text-slate-600">Choose the option that best describes you</p>
                    </div>

                    <div class="space-y-4">
                        <!-- Graduate Option -->
                        <div @click="selectUserLevel('graduates')"
                            class="bg-white-50 p-6 rounded-2xl hover:bg-gray-100 cursor-pointer transition-all duration-200 border border-gray-200 hover:border-cyan-400 group">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-cyan-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-user-graduate text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-slate-800">Graduate</h4>
                                    <p class="text-slate-600 text-sm">For recent graduates seeking opportunities</p>
                                </div>
                            </div>
                        </div>

                        <!-- Institution Option -->
                        <div @click="selectUserLevel('institution')"
                            class="bg-white-50 p-6 rounded-2xl hover:bg-gray-100 cursor-pointer transition-all duration-200 border border-gray-200 hover:border-emerald-400 group">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-emerald-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-university text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-slate-800">Institution</h4>
                                    <p class="text-slate-600 text-sm">For schools, colleges and universities</p>
                                </div>
                            </div>
                        </div>

                        <!-- Company Option -->
                        <div @click="selectUserLevel('industry')"
                            class="bg-white-50 p-6 rounded-2xl hover:bg-gray-100 cursor-pointer transition-all duration-200 border border-gray-200 hover:border-amber-400 group">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-amber-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-building text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-slate-800">Company</h4>
                                    <p class="text-slate-600 text-sm">For companies seeking qualified talent</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center pt-4">
                        <p class="text-black/80">
                            Already have an account?
                            <Link href="/login"
                                class="text-blue-600 hover:text-blue-700 hover:enhanced-text font-semibold transition-all duration-200 ml-2">
                            Sign In
                            </Link>
                        </p>
                    </div>
                </div>

                <!-- Graduate Registration Form -->
                <div v-else-if="currentStep === 'graduate'" class="space-y-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-slate-800">Graduate Registration</h3>
                        <button @click="currentStep = 'user-level'"
                            class="text-slate-600 hover:text-slate-800 p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all duration-200">
                            <i class="fas fa-arrow-left text-lg"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submitGraduateAccount" class="space-y-6">
                        <div>
                            <InputLabel for="email" value="Email Address"
                                class="text-lg font-semibold text-slate-700 mb-2" />
                            <TextInput id="email" v-model="form.email" type="email"
                                class="block w-full px-4 py-3 text-lg rounded-xl bg-gray-50 border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 text-slate-800 placeholder-slate-400"
                                placeholder="Enter your email" required />
                            <InputError class="mt-2 text-red-600" :message="form.errors.email" />
                        </div>

                        <div class="relative">
                            <InputLabel for="password" value="Password"
                                class="text-lg font-semibold text-slate-700 mb-2" />
                            <div class="relative">
                                <TextInput id="password" v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="block w-full px-4 py-3 pr-12 text-lg rounded-xl bg-gray-50 border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 text-slate-800 placeholder-slate-400"
                                    placeholder="Enter your password" required @input="updatePasswordStrength" />
                                <button type="button"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition-all duration-200"
                                    @click="togglePasswordVisibility">
                                    <i :class="[showPassword ? 'fas fa-eye-slash' : 'fas fa-eye', 'text-lg']"></i>
                                </button>
                                <!-- Password strength indicator icon -->
                                <span v-if="form.password && !showConfirmPassword"
                                    class="absolute inset-y-0 right-10 flex items-center pr-3 transition-all duration-300">
                                    <i class="fas" :class="{
                                        'fa-shield-alt text-emerald-500': passwordStrength === 5,
                                        'fa-shield-alt text-blue-400': passwordStrength === 4,
                                        'fa-shield-alt text-yellow-400': passwordStrength === 3,
                                        'fa-shield-alt text-orange-400': passwordStrength === 2,
                                        'fa-shield-alt text-red-400': passwordStrength === 1
                                    }"></i>
                                </span>
                            </div>
                            <InputError class="mt-2 text-red-600" :message="form.errors.password" />

                            <!-- Password strength bar and text -->
                            <div v-if="form.password" class="mt-3 mb-2">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-medium" :class="{
                                        'text-red-400': passwordStrength === 1,
                                        'text-orange-400': passwordStrength === 2,
                                        'text-yellow-400': passwordStrength === 3,
                                        'text-blue-400': passwordStrength === 4,
                                        'text-emerald-500': passwordStrength === 5
                                    }">{{ strengthText.text }}</span>
                                    <span class="text-xs text-slate-500">{{ passwordStrength }}/5</span>
                                </div>
                                <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full transition-all duration-500 ease-out" :class="strengthText.color"
                                        :style="{ width: (passwordStrength * 20) + '%' }"></div>
                                </div>
                            </div>

                            <!-- Password requirements -->
                            <div v-if="form.password"
                                class="mt-3 p-3 bg-gray-50 rounded-md border border-gray-200 transition-all duration-300">
                                <p class="text-xs text-slate-600 mb-2 font-medium">Password Requirements:</p>
                                <ul class="text-xs text-slate-600 space-y-1 pl-1">
                                    <li class="flex items-center transition-all duration-300"
                                        :class="{ 'text-blue-600 font-medium': minLengthMet }">
                                        <i class="fas mr-1.5 text-xs transition-all duration-300"
                                            :class="[minLengthMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-slate-300']"></i>
                                        At least 8 characters
                                    </li>
                                    <li class="flex items-center transition-all duration-300"
                                        :class="{ 'text-blue-600 font-medium': hasUpperCaseMet }">
                                        <i class="fas mr-1.5 text-xs transition-all duration-300"
                                            :class="[hasUpperCaseMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-slate-300']"></i>
                                        Include uppercase letter (A-Z)
                                    </li>
                                    <li class="flex items-center transition-all duration-300"
                                        :class="{ 'text-blue-600 font-medium': hasLowerCaseMet }">
                                        <i class="fas mr-1.5 text-xs transition-all duration-300"
                                            :class="[hasLowerCaseMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-slate-300']"></i>
                                        Include lowercase letter (a-z)
                                    </li>
                                    <li class="flex items-center transition-all duration-300"
                                        :class="{ 'text-blue-600 font-medium': hasNumberMet }">
                                        <i class="fas mr-1.5 text-xs transition-all duration-300"
                                            :class="[hasNumberMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-slate-300']"></i>
                                        Include number (0-9)
                                    </li>
                                    <li class="flex items-center transition-all duration-300"
                                        :class="{ 'text-blue-600 font-medium': hasSpecialCharMet }">
                                        <i class="fas mr-1.5 text-xs transition-all duration-300"
                                            :class="[hasSpecialCharMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-slate-300']"></i>
                                        Include special character (!@#$%^&*)
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="relative">
                            <InputLabel for="password_confirmation" value="Confirm Password"
                                class="text-lg font-semibold text-slate-800 mb-2" />
                            <div class="relative">
                                <TextInput id="password_confirmation" v-model="form.password_confirmation"
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    class="block w-full px-4 py-3 pr-12 text-lg rounded-xl bg-gray-100 border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 text-slate-800 placeholder-slate-500"
                                    placeholder="Confirm your password" required />
                                <button type="button"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-500 hover:text-slate-700 focus:outline-none transition-all duration-200"
                                    @click="toggleConfirmPasswordVisibility">
                                    <i
                                        :class="[showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye', 'text-lg']"></i>
                                </button>
                            </div>
                            <InputError class="mt-2 text-red-600" :message="form.errors.password_confirmation" />
                        </div>

                        <div class="pt-4">
                            <button type="submit"
                                class="w-full px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white text-lg font-bold rounded-2xl transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-blue-400 shadow-lg disabled:opacity-50 disabled:transform-none"
                                :disabled="form.processing">
                                <span v-if="form.processing">Creating Account...</span>
                                <span v-else>Create Graduate Account</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Institution Registration Form -->
                <div v-else-if="currentStep === 'institution'" class="space-y-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-slate-800">Institution Registration</h3>
                        <button @click="currentStep = 'user-level'"
                            class="text-slate-600 hover:text-slate-800 p-2 rounded-xl bg-gray-100 border border-gray-200 transition-all duration-300 hover:scale-110">
                            <i class="fas fa-arrow-left text-lg"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submitInstitutionAccount" class="space-y-6">
                        <div>
                            <InputLabel for="email" value="Email Address"
                                class="text-lg font-semibold text-slate-800 mb-2" />
                            <TextInput id="email" v-model="form.email" type="email"
                                class="block w-full px-4 py-3 text-lg rounded-xl bg-gray-100 border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 text-slate-800 placeholder-slate-500"
                                placeholder="Enter institution email" required />
                            <InputError class="mt-2 text-red-600" :message="form.errors.email" />
                        </div>

                        <div class="relative">
                            <InputLabel for="password" value="Password"
                                class="text-lg font-semibold text-slate-800 mb-2" />
                            <div class="relative">
                                <TextInput id="password" v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="block w-full px-4 py-3 pr-12 text-lg rounded-xl bg-gray-100 border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 text-slate-800 placeholder-slate-500"
                                    placeholder="Enter your password" required @input="updatePasswordStrength" />
                                <button type="button"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-500 hover:text-slate-700 focus:outline-none transition-all duration-200"
                                    @click="togglePasswordVisibility">
                                    <i :class="[showPassword ? 'fas fa-eye-slash' : 'fas fa-eye', 'text-lg']"></i>
                                </button>
                                <!-- Password strength indicator icon -->
                                <span v-if="form.password && !showConfirmPassword"
                                    class="absolute inset-y-0 right-10 flex items-center pr-3 transition-all duration-300">
                                    <i class="fas" :class="{
                                        'fa-shield-alt text-emerald-500': passwordStrength === 5,
                                        'fa-shield-alt text-blue-400': passwordStrength === 4,
                                        'fa-shield-alt text-yellow-400': passwordStrength === 3,
                                        'fa-shield-alt text-orange-400': passwordStrength === 2,
                                        'fa-shield-alt text-red-400': passwordStrength === 1
                                    }"></i>
                                </span>
                            </div>
                            <InputError class="mt-2 text-red-600" :message="form.errors.password" />

                            <!-- Password strength bar and text -->
                            <div v-if="form.password" class="mt-3 mb-2">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-medium" :class="{
                                        'text-red-400': passwordStrength === 1,
                                        'text-orange-400': passwordStrength === 2,
                                        'text-yellow-400': passwordStrength === 3,
                                        'text-blue-400': passwordStrength === 4,
                                        'text-emerald-500': passwordStrength === 5
                                    }">{{ strengthText.text }}</span>
                                    <span class="text-xs text-slate-500">{{ passwordStrength }}/5</span>
                                </div>
                                <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full transition-all duration-500 ease-out" :class="strengthText.color"
                                        :style="{ width: (passwordStrength * 20) + '%' }"></div>
                                </div>
                            </div>

                            <!-- Password requirements -->
                            <div v-if="form.password"
                                class="mt-3 p-3 bg-gray-50 rounded-md border border-gray-200 transition-all duration-300">
                                <p class="text-xs text-slate-600 mb-2 font-medium">Password Requirements:</p>
                                <ul class="text-xs text-slate-600 space-y-1 pl-1">
                                    <li class="flex items-center transition-all duration-300"
                                        :class="{ 'text-blue-600 font-medium': minLengthMet }">
                                        <i class="fas mr-1.5 text-xs transition-all duration-300"
                                            :class="[minLengthMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-slate-300']"></i>
                                        At least 8 characters
                                    </li>
                                    <li class="flex items-center transition-all duration-300"
                                        :class="{ 'text-blue-600 font-medium': hasUpperCaseMet }">
                                        <i class="fas mr-1.5 text-xs transition-all duration-300"
                                            :class="[hasUpperCaseMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-slate-300']"></i>
                                        Include uppercase letter (A-Z)
                                    </li>
                                    <li class="flex items-center transition-all duration-300"
                                        :class="{ 'text-blue-600 font-medium': hasLowerCaseMet }">
                                        <i class="fas mr-1.5 text-xs transition-all duration-300"
                                            :class="[hasLowerCaseMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-slate-300']"></i>
                                        Include lowercase letter (a-z)
                                    </li>
                                    <li class="flex items-center transition-all duration-300"
                                        :class="{ 'text-blue-600 font-medium': hasNumberMet }">
                                        <i class="fas mr-1.5 text-xs transition-all duration-300"
                                            :class="[hasNumberMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-slate-300']"></i>
                                        Include number (0-9)
                                    </li>
                                    <li class="flex items-center transition-all duration-300"
                                        :class="{ 'text-blue-600 font-medium': hasSpecialCharMet }">
                                        <i class="fas mr-1.5 text-xs transition-all duration-300"
                                            :class="[hasSpecialCharMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-slate-300']"></i>
                                        Include special character (!@#$%^&*)
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="relative">
                            <InputLabel for="password_confirmation" value="Confirm Password"
                                class="text-lg font-semibold text-slate-800 mb-2" />
                            <div class="relative">
                                <TextInput id="password_confirmation" v-model="form.password_confirmation"
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    class="block w-full px-4 py-3 pr-12 text-lg rounded-xl bg-gray-100 border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 text-slate-800 placeholder-slate-500"
                                    placeholder="Confirm your password" required />
                                <button type="button"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-500 hover:text-slate-700 focus:outline-none transition-all duration-200"
                                    @click="toggleConfirmPasswordVisibility">
                                    <i
                                        :class="[showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye', 'text-lg']"></i>
                                </button>
                            </div>
                            <InputError class="mt-2 text-red-600" :message="form.errors.password_confirmation" />
                        </div>

                        <div class="pt-4">
                            <button type="submit"
                                class="w-full px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white text-lg font-bold rounded-2xl transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-blue-400 shadow-lg disabled:opacity-50 disabled:transform-none"
                                :disabled="form.processing">
                                <span v-if="form.processing">Creating Account...</span>
                                <span v-else>Create Institution Account</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Company Registration Form -->
                <div v-else-if="currentStep === 'company'" class="space-y-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-slate-800">Company Registration</h3>
                        <button @click="currentStep = 'user-level'"
                            class="text-slate-600 hover:text-slate-800 p-2 rounded-xl bg-gray-100 border border-gray-200 transition-all duration-300 hover:scale-110">
                            <i class="fas fa-arrow-left text-lg"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submitCompanyAccount" class="space-y-6">
                        <div>
                            <InputLabel for="email" value="Email Address"
                                class="text-lg font-semibold text-slate-800 mb-2" />
                            <TextInput id="email" v-model="form.email" type="email"
                                class="block w-full px-4 py-3 text-lg rounded-xl bg-gray-100 border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 text-slate-800 placeholder-slate-500"
                                placeholder="Enter company email" required />
                            <InputError class="mt-2 text-red-600" :message="form.errors.email" />
                        </div>

                        <div class="relative">
                            <InputLabel for="password" value="Password"
                                class="text-lg font-semibold text-slate-800 mb-2" />
                            <div class="relative">
                                <TextInput id="password" v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="block w-full px-4 py-3 pr-12 text-lg rounded-xl bg-gray-100 border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 text-slate-800 placeholder-slate-500"
                                    placeholder="Enter your password" required @input="updatePasswordStrength" />
                                <button type="button"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-500 hover:text-slate-700 focus:outline-none transition-all duration-200"
                                    @click="togglePasswordVisibility">
                                    <i :class="[showPassword ? 'fas fa-eye-slash' : 'fas fa-eye', 'text-lg']"></i>
                                </button>
                                <!-- Password strength indicator icon -->
                                <span v-if="form.password && !showConfirmPassword"
                                    class="absolute inset-y-0 right-10 flex items-center pr-3 transition-all duration-300">
                                    <i class="fas" :class="{
                                        'fa-shield-alt text-emerald-500': passwordStrength === 5,
                                        'fa-shield-alt text-blue-400': passwordStrength === 4,
                                        'fa-shield-alt text-yellow-400': passwordStrength === 3,
                                        'fa-shield-alt text-orange-400': passwordStrength === 2,
                                        'fa-shield-alt text-red-400': passwordStrength === 1
                                    }"></i>
                                </span>
                            </div>
                            <InputError class="mt-2 text-red-600" :message="form.errors.password" />

                            <!-- Password strength bar and text -->
                            <div v-if="form.password" class="mt-3 mb-2">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-medium" :class="{
                                        'text-red-400': passwordStrength === 1,
                                        'text-orange-400': passwordStrength === 2,
                                        'text-yellow-400': passwordStrength === 3,
                                        'text-blue-400': passwordStrength === 4,
                                        'text-emerald-500': passwordStrength === 5
                                    }">{{ strengthText.text }}</span>
                                    <span class="text-xs text-white/60">{{ passwordStrength }}/5</span>
                                </div>
                                <div class="w-full h-1.5 bg-white/20 rounded-full overflow-hidden">
                                    <div class="h-full transition-all duration-500 ease-out" :class="strengthText.color"
                                        :style="{ width: (passwordStrength * 20) + '%' }"></div>
                                </div>
                            </div>

                            <!-- Password requirements -->
                            <div v-if="form.password"
                                class="mt-3 p-3 bg-gray-50 rounded-md border border-gray-200 transition-all duration-300">
                                <p class="text-xs text-slate-600 mb-2 font-medium">Password Requirements:</p>
                                <ul class="text-xs text-slate-600 space-y-1 pl-1">
                                    <li class="flex items-center transition-all duration-300"
                                        :class="{ 'text-blue-600 font-medium': minLengthMet }">
                                        <i class="fas mr-1.5 text-xs transition-all duration-300"
                                            :class="[minLengthMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-slate-300']"></i>
                                        At least 8 characters
                                    </li>
                                    <li class="flex items-center transition-all duration-300"
                                        :class="{ 'text-blue-600 font-medium': hasUpperCaseMet }">
                                        <i class="fas mr-1.5 text-xs transition-all duration-300"
                                            :class="[hasUpperCaseMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-slate-300']"></i>
                                        Include uppercase letter (A-Z)
                                    </li>
                                    <li class="flex items-center transition-all duration-300"
                                        :class="{ 'text-blue-600 font-medium': hasLowerCaseMet }">
                                        <i class="fas mr-1.5 text-xs transition-all duration-300"
                                            :class="[hasLowerCaseMet ? 'fa-check-circle text-emerald-500' : 'ffa-circle text-slate-300']"></i>
                                        Include lowercase letter (a-z)
                                    </li>
                                    <li class="flex items-center transition-all duration-300"
                                        :class="{ 'text-blue-600 font-medium': hasNumberMet }">
                                        <i class="fas mr-1.5 text-xs transition-all duration-300"
                                            :class="[hasNumberMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-slate-300']"></i>
                                        Include number (0-9)
                                    </li>
                                    <li class="flex items-center transition-all duration-300"
                                        :class="{ 'text-blue-600 font-medium': hasSpecialCharMet }">
                                        <i class="fas mr-1.5 text-xs transition-all duration-300"
                                            :class="[hasSpecialCharMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-slate-300']"></i>
                                        Include special character (!@#$%^&*)
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="relative">
                            <InputLabel for="password_confirmation" value="Confirm Password"
                                class="text-lg font-semibold text-slate-800 mb-2" />
                            <div class="relative">
                                <TextInput id="password_confirmation" v-model="form.password_confirmation"
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    class="block w-full px-4 py-3 pr-12 text-lg rounded-xl bg-gray-100 border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 text-slate-800 placeholder-slate-500"
                                    placeholder="Confirm your password" required />
                                <button type="button"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-500 hover:text-slate-700 focus:outline-none transition-all duration-200"
                                    @click="toggleConfirmPasswordVisibility">
                                    <i
                                        :class="[showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye', 'text-lg']"></i>
                                </button>
                            </div>
                            <InputError class="mt-2 text-red-600" :message="form.errors.password_confirmation" />
                        </div>

                        <div class="pt-4">
                            <button type="submit"
                                class="w-full px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white text-lg font-bold rounded-2xl transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-blue-400 shadow-lg disabled:opacity-50 disabled:transform-none"
                                :disabled="form.processing">
                                <span v-if="form.processing">Creating Account...</span>
                                <span v-else>Create Company Account</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Smooth transitions */
* {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>