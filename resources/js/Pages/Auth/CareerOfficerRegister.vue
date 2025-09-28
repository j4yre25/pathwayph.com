<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';

// Multi-step form state
const currentStep = ref(1);
const totalSteps = 3;
const isPasswordFocused = ref(false);

// Step information
const stepInfo = [
    { title: 'Personal Information', icon: '👤' },
    { title: 'Contact Information', icon: '📧' },
    { title: 'Password Setup', icon: '🔒' }
];

const showModal = ref(false);

// Define the form with additional fields for user types
const form = useForm({
    institution_career_officer_first_name: '',
    institution_career_officer_last_name: '',
    gender: '',
    dob: '',
    contact_number: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'institution',
    terms: false,
    is_approved: true,
});

// Step validation
const isStep1Valid = computed(() => {
    return form.institution_career_officer_first_name && 
           form.institution_career_officer_last_name && 
           form.gender && 
           form.dob;
});

const isStep2Valid = computed(() => {
    return form.email && form.contact_number;
});

const isStep3Valid = computed(() => {
    return form.password && 
           form.password_confirmation && 
           form.password === form.password_confirmation &&
           Object.values(passwordCriteria.value).every(Boolean);
});

const canProceed = computed(() => {
    if (currentStep.value === 1) return isStep1Valid.value;
    if (currentStep.value === 2) return isStep2Valid.value;
    if (currentStep.value === 3) return isStep3Valid.value;
    return false;
});

// Navigation functions
const nextStep = () => {
    if (currentStep.value < totalSteps && canProceed.value) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const goToStep = (step) => {
    currentStep.value = step;
};


const formattedContactNumber = computed({
    get: () => {
        let rawNumber = form.contact_number.replace(/\D/g, ""); // Remove non-numeric characters

        // Ensure only the first 10 digits are considered
        if (rawNumber.length > 10) {
            rawNumber = rawNumber.slice(0, 10);
        }

        // Ensure that the displayed format is always "+63 XXX XXX XXXX"
        return `+63 ${rawNumber.replace(/(\d{3})(\d{3})(\d{4})/, "$1 $2 $3")}`.trim();
    },
    set: (value) => {
        // Remove non-numeric characters except for numbers
        let rawValue = value.replace(/\D/g, "");

        // Ensure only the last 10 digits are stored
        if (rawValue.startsWith("63")) {
            rawValue = rawValue.slice(2);
        }

        if (rawValue.startsWith("0")) {
            rawValue = rawValue.slice(1);
        }

        if (rawValue.length > 10) {
            rawValue = rawValue.slice(0, 10);
        }

        form.contact_number = rawValue;
    },
});

// Update 04/04
const passwordCriteria = computed(() => {
    const password = form.password;
    return {
        length: password.length >= 8, // Minimum 8 characters
        uppercaseLowercase: /[a-z]/.test(password) && /[A-Z]/.test(password), // Upper & lower case
        letter: /[a-zA-Z]/.test(password), // At least one letter
        number: /\d/.test(password), // At least one number
        symbol: /[~!@#$%^&*()-=+_/>.,<;'']/.test(password), // At least one special character
    };
});


// Handle form submission
const submit = () => {
    if (!canProceed.value) return;
    
    console.log('Form Data:', form);

    form.post(route('careerofficer.submit'), {
        onFinish: () => {
            console.log("Form submission finished");
            console.log(form.errors); // Log validation errors
            form.reset('password', 'password_confirmation');
        },
        onSuccess: () => {
            showModal.value = true;
            console.log("showModal:", showModal.value);
        },
    });
};

const redirectTodDashboard = () => {
    Inertia.visit(route('dashboard')); // Redirect to dashboard after successful registration
};

</script>

<template>
    <Head title="Career Officer Register" />

    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-4xl font-bold text-slate-800 mb-4">Career Officer Registration</h1>
                    <p class="text-slate-600 text-lg">Join our platform to connect with talented graduates</p>
                </div>

                <!-- Progress Bar (aligned with HResourceRegister.vue) -->
                <div class="flex justify-center items-center space-x-4 mb-8">
                    <div v-for="step in totalSteps" :key="step" class="flex items-center">
                        <div 
                            @click="goToStep(step)"
                            :class="['w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold cursor-pointer transition-all duration-300',
                            currentStep >= step ? 'bg-emerald-500 text-white shadow-lg' : 'bg-gray-200 text-gray-500 hover:bg-gray-300']">
                            {{ step }}
                        </div>
                        <div v-if="step < totalSteps" :class="['w-16 h-1 mx-2 rounded transition-all duration-300',
                        currentStep > step ? 'bg-emerald-500' : 'bg-gray-300']"></div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <form @submit.prevent="submit" class="p-8">
                        <!-- Step 1: Personal Information -->
                        <div v-show="currentStep === 1" class="space-y-6">
                            <div class="flex items-center mb-6">
                                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-slate-800">Personal Information</h2>
                                    <p class="text-slate-600">Please provide your basic personal details</p>
                                </div>
                            </div>
                            <!-- First Name -->
                            <div>
                                <div class="flex items-center gap-1 mb-2">
                                    <InputLabel for="institution_career_officer_first_name" value="First Name" class="text-sm font-medium text-gray-700" />
                                    <span class="text-red-500">*</span>
                                </div>
                                <TextInput 
                                    id="institution_career_officer_first_name" 
                                    v-model="form.institution_career_officer_first_name" 
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200" 
                                    placeholder="Enter your first name"
                                    required 
                                />
                                <InputError class="mt-1 text-sm" :message="form.errors.institution_career_officer_first_name" />
                            </div>

                            <!-- Last Name -->
                            <div>
                                <div class="flex items-center gap-1 mb-2">
                                    <InputLabel for="institution_career_officer_last_name" value="Last Name" class="text-sm font-medium text-gray-700" />
                                    <span class="text-red-500">*</span>
                                </div>
                                <TextInput 
                                    id="institution_career_officer_last_name" 
                                    v-model="form.institution_career_officer_last_name" 
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200" 
                                    placeholder="Enter your last name"
                                    required 
                                />
                                <InputError class="mt-1 text-sm" :message="form.errors.institution_career_officer_last_name" />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Gender -->
                                <div>
                                    <div class="flex items-center gap-1 mb-2">
                                        <InputLabel for="gender" value="Gender" class="text-sm font-medium text-gray-700" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <select 
                                        id="gender" 
                                        v-model="form.gender"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200" 
                                        required
                                    >
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                    <InputError class="mt-1 text-sm" :message="form.errors.gender" />
                                </div>

                                <!-- Date of Birth -->
                                <div>
                                    <div class="flex items-center gap-1 mb-2">
                                        <InputLabel for="dob" value="Date of Birth" class="text-sm font-medium text-gray-700" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <TextInput 
                                        id="dob" 
                                        v-model="form.dob" 
                                        type="date"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200" 
                                        required 
                                    />
                                    <InputError class="mt-1 text-sm" :message="form.errors.dob" />
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Contact Information -->
                        <div v-show="currentStep === 2" class="space-y-6">
                            <div class="text-center mb-8">
                                <div class="flex items-center mb-6">
                                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-2xl font-bold text-slate-800">Contact Details</h2>
                                        <p class="text-slate-600">Please provide your contact information</p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <!-- Email -->
                                <div>
                                    <div class="flex items-center gap-1 mb-2">
                                        <InputLabel for="email" value="Email Address" class="text-sm font-medium text-gray-700" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <TextInput 
                                        id="email" 
                                        v-model="form.email" 
                                        type="email" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200"
                                        placeholder="Enter your email address"
                                        required 
                                    />
                                    <InputError class="mt-1 text-sm" :message="form.errors.email" />
                                </div>

                                <!-- Contact Number -->
                                <div>
                                    <div class="flex items-center gap-1 mb-2">
                                        <InputLabel for="contact_number" value="Contact Number" class="text-sm font-medium text-gray-700" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <TextInput 
                                        id="contact_number" 
                                        v-model="formattedContactNumber" 
                                        type="text"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200" 
                                        placeholder="+63 XXX XXX XXXX"
                                        required 
                                    />
                                    <InputError class="mt-1 text-sm" :message="form.errors.contact_number" />
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Password Setup -->
                        <div v-show="currentStep === 3" class="space-y-6">
                            <div class="flex items-center mb-6">
                                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-slate-800">Password Setup</h2>
                                    <p class="text-slate-600">Create a secure password for your account</p>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <!-- Password -->
                                <div>
                                    <div class="flex items-center gap-1 mb-2">
                                        <InputLabel for="password" value="Password" class="text-sm font-medium text-gray-700" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <TextInput 
                                        id="password" 
                                        v-model="form.password" 
                                        type="password"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200" 
                                        placeholder="Enter your password"
                                        @focus="isPasswordFocused = true"
                                        @blur="isPasswordFocused = false"
                                        required 
                                    />
                                    <InputError class="mt-1 text-sm" :message="form.errors.password" />

                                    <!-- Password validation tooltip -->
                                    <div v-if="form.password" class="mt-3 p-4 bg-gray-50 rounded-lg border">
                                        <p class="font-medium text-gray-700 mb-2">Password Requirements:</p>
                                        <ul class="space-y-1 text-sm">
                                            <li class="flex items-center gap-2" :class="passwordCriteria.length ? 'text-green-600' : 'text-red-500'">
                                                <span>{{ passwordCriteria.length ? '✓' : '✗' }}</span>
                                                Minimum 8 characters
                                            </li>
                                            <li class="flex items-center gap-2" :class="passwordCriteria.uppercaseLowercase ? 'text-green-600' : 'text-red-500'">
                                                <span>{{ passwordCriteria.uppercaseLowercase ? '✓' : '✗' }}</span>
                                                Upper & lower case letters
                                            </li>
                                            <li class="flex items-center gap-2" :class="passwordCriteria.number ? 'text-green-600' : 'text-red-500'">
                                                <span>{{ passwordCriteria.number ? '✓' : '✗' }}</span>
                                                At least one number
                                            </li>
                                            <li class="flex items-center gap-2" :class="passwordCriteria.symbol ? 'text-green-600' : 'text-red-500'">
                                                <span>{{ passwordCriteria.symbol ? '✓' : '✗' }}</span>
                                                At least one special character
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <div class="flex items-center gap-1 mb-2">
                                        <InputLabel for="password_confirmation" value="Confirm Password" class="text-sm font-medium text-gray-700" />
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <TextInput 
                                        id="password_confirmation" 
                                        v-model="form.password_confirmation"
                                        type="password" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200" 
                                        placeholder="Confirm your password"
                                        required 
                                    />
                                    <InputError class="mt-1 text-sm" :message="form.errors.password_confirmation" />
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex items-center justify-between mt-8 pt-6 ">
                            <button
                                v-if="currentStep > 1"
                                type="button"
                                @click="prevStep"
                                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200"
                            >
                                Previous
                            </button>
                            <div v-else></div>

                            <button
                                v-if="currentStep < totalSteps"
                                type="button"
                                @click="nextStep"
                                :disabled="!canProceed"
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                            >
                                Next
                            </button>
                            <PrimaryButton
                                v-else
                                type="submit"
                                :disabled="!canProceed || form.processing"
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                                :class="{ 'opacity-25': form.processing }"
                            >
                                Register
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <ConfirmationModal v-if="showModal" :show="showModal" @close="redirectTodDashboard">
            <template #title>
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900">Registration Successful!</h2>
                </div>
            </template>
            <template #content>
                <p class="text-gray-600">
                    Your career officer account has been created successfully. You can now access the platform and start connecting with talented graduates.
                </p>
            </template>
            <template #footer>
                <button
                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
                    @click="redirectTodDashboard"
                >
                    Continue to Dashboard
                </button>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>