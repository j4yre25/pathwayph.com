<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref, computed, watchEffect } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { useFormattedMobileNumber } from '@/Composables/useFormattedMobileNumber';
import { usePasswordCriteria } from '@/Composables/usePasswordCriteria';
import '@fortawesome/fontawesome-free/css/all.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';

// Step-by-step functionality
const currentStep = ref(1);
const totalSteps = ref(3); // Three steps for HR registration

const isPasswordFocused = ref(false);

// Step information
const stepInfo = {
    1: { title: 'Personal Information', description: 'Basic personal details' },
    2: { title: 'Contact Details', description: 'Email and phone information' },
    3: { title: 'Password Setup', description: 'Create your secure password' }
};

// Define the form with additional fields for user types
const form = useForm({
    first_name: '',
    middle_name: '',
    last_name: '',
    gender: '',
    dob: '',
    mobile_number: null,
    email: '',
    password: '',
    password_confirmation: '',
    role: 'company',
    terms: false,
});

// Step validation
const isStep1Valid = computed(() => {
    return form.first_name && form.last_name && form.middle_name && form.gender && form.dob;
});

const isStep2Valid = computed(() => {
    return form.email && form.mobile_number;
});

const isStep3Valid = computed(() => {
    return form.password && form.password_confirmation && form.password === form.password_confirmation;
});

const canProceed = computed(() => {
    if (currentStep.value === 1) return isStep1Valid.value;
    if (currentStep.value === 2) return isStep2Valid.value;
    if (currentStep.value === 3) return isStep3Valid.value;
    return false;
});

// Navigation functions
const nextStep = () => {
    if (canProceed.value && currentStep.value < totalSteps.value) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const goToStep = (step) => {
    if (step <= currentStep.value || (step === currentStep.value + 1 && canProceed.value)) {
        currentStep.value = step;
    }
};

const { formattedMobileNumber} = useFormattedMobileNumber(form, 'mobile_number');
const { passwordCriteria } = usePasswordCriteria(form);

const maxDate = ref(new Date().toISOString().split('T')[0]);
const minDate = ref('1900-01-01');

const page = usePage();
const showModal = ref(false);
const message = ref('');

watchEffect(() => {
    if (page.props.flash?.banner) {
        showModal.value = true;
    }
});



// Handle form submission
const submit = () => {
    if (currentStep.value === totalSteps.value && canProceed.value) {
        form.post(route('hr.register'), {
            onFinish: () => {
                console.log("Form submission finished");
                console.log(form.errors);
                form.reset('password', 'password_confirmation');
            },
            onSuccess: () => {
                Inertia.reload({ only: ['hrCount'] });
            },
        });
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Human Resource Officer Register" />
        
        <!-- Clean gradient background -->
        <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 relative overflow-hidden">
            
            <div class="max-w-3xl mx-auto py-10 relative z-10">
                <!-- Modern header with neon text -->
                <div class="text-center mb-8 reveal">
                    <h1 class="text-4xl font-bold text-slate-800 mb-4">
                        Register New Human Resource Officer
                    </h1>
                    <p class="text-slate-600 text-lg mb-8">Build your HR team with us</p>
                
                    <!-- Step Progress Bar -->
                    <div class="flex justify-center items-center space-x-4 mb-8">
                        <div v-for="step in totalSteps" :key="step" class="flex items-center">
                            <div 
                                @click="goToStep(step)"
                                :class="['w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold cursor-pointer transition-all duration-300',
                                currentStep >= step ? 'bg-emerald-500 text-white shadow-lg' : 'bg-gray-200 text-gray-500 hover:bg-gray-300']">
                                {{ step }}
                            </div>
                            <div v-if="step < totalSteps" :class="['w-16 h-1 mx-2 rounded transition-all duration-300',
                            currentStep > step ? 'bg-emerald-500' : 'bg-gray-300']">
                            </div>
                        </div>
                    </div>
                </div>
                
                <form @submit.prevent="submit" class="space-y-8">
                        <!-- Step 1: Personal Information -->
                        <div v-show="currentStep === 1" class="bg-white rounded-2xl p-8 shadow-lg border border-gray-200 reveal">
                            <div class="flex items-center mb-6">
                                <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-slate-800">Personal Information</h2>
                                    <p class="text-slate-600">Please provide your basic personal details</p>
                                </div>
                            </div>
                            <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                 <div>
                                     <InputLabel for="first_name" class="text-slate-800 font-medium">
                                         First Name <span class="text-red-500">*</span>
                                     </InputLabel>
                                     <TextInput
                                         id="first_name"
                                         type="text"
                                         class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400"
                                         placeholder="Enter first name"
                                         v-model="form.first_name"
                                         required
                                         @keydown.enter.prevent
                                     />
                                     <InputError class="text-red-600" :message="form.errors.first_name" />
                                 </div>

                                  <div>
                                     <InputLabel for="middle_name" class="text-slate-800 font-medium">
                                         Middle Name 
                                     </InputLabel>
                                     <TextInput 
                                         id="middle_name" 
                                         type="text" 
                                         class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400"
                                         placeholder="Enter middle name"
                                         v-model="form.middle_name" 
                                         required
                                         @keydown.enter.prevent
                                     />
                                     <InputError class="text-red-600" :message="form.errors.middle_name" />
                                 </div>
                                    
                                <div>
                                     <InputLabel for="last_name" class="text-slate-800 font-medium">
                                         Last Name <span class="text-red-500">*</span>
                                     </InputLabel>
                                     <TextInput 
                                         id="last_name" 
                                         type="text" 
                                         class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400"  
                                         placeholder="Enter last name"
                                         v-model="form.last_name" 
                                         required
                                         @keydown.enter.prevent
                                     />
                                     <InputError class="text-red-600" :message="form.errors.last_name" />
                                 </div>
                             </div>


                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="gender" class="text-slate-800 font-medium">
                                        Gender <span class="text-red-500">*</span>
                                    </InputLabel>
                                    <select 
                                        id="gender" 
                                        v-model="form.gender" 
                                        class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 focus:border-blue-400 focus:ring-blue-400 rounded-lg" 
                                        required>
                                        <option value="" class="bg-white">Select Gender</option>
                                        <option value="Male" class="bg-white">Male</option>
                                        <option value="Female" class="bg-white">Female</option>
                                    </select>
                                    <InputError class="text-red-600" :message="form.errors.gender" />
                                </div>
                                <div>
                                    <InputLabel for="dob" class="text-slate-800 font-medium">
                                        Date of Birth <span class="text-red-500">*</span>
                                    </InputLabel>
                                    <TextInput 
                                        id="dob" 
                                        v-model="form.dob" 
                                        :max="maxDate"
                                        :min="minDate"  
                                        type="date" 
                                        class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" 
                                        required 
                                    />
                                    <InputError class="text-red-600" :message="form.errors.dob" />
                                </div>
                            </div>
                        </div>
                        </div>

                        <!-- Step 2: Contact Details -->
                        <div v-show="currentStep === 2" class="bg-white rounded-2xl p-8 shadow-lg border border-gray-200 reveal">
                            <div class="flex items-center mb-6">
                                <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-slate-800">Contact Details</h2>
                                    <p class="text-slate-600">Please provide your contact information</p>
                                </div>
                            </div>
                            <div class="space-y-6">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="email" class="text-slate-800 font-medium">
                                        Email Address <span class="text-red-500">*</span>
                                    </InputLabel>
                                    <TextInput 
                                        id="email" 
                                        type="email" 
                                        class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400"
                                        placeholder="Enter email address"
                                        v-model="form.email" 
                                        required 
                                    />
                                    <InputError class="text-red-600" :message="form.errors.email" />
                                </div>

                                <div>
                                    <InputLabel for="contact_number" class="text-slate-800 font-medium">
                                        Contact Number <span class="text-red-500">*</span>
                                    </InputLabel>
                                    <TextInput 
                                        id="contact_number"
                                        placeholder="+63 912 345 6789"
                                        type="tel"
                                        class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400"
                                        v-model="formattedMobileNumber"
                                        required 
                                    />
                                    <InputError class="text-red-600" :message="form.errors.contact_number" />
                                </div>
                            </div>
                        </div>
                        </div>

                        <!-- Step 3: Password Setup -->
                        <div v-show="currentStep === 3" class="bg-white rounded-2xl p-8 shadow-lg border border-gray-200 reveal">
                            <div class="flex items-center mb-6">
                                <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center mr-4">
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

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="password" class="text-slate-800 font-medium">
                                        Password <span class="text-red-500">*</span>
                                    </InputLabel>
                                    <TextInput 
                                        id="password" 
                                        v-model="form.password" 
                                        type="password" 
                                        class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" 
                                        placeholder="Enter your password"
                                        required
                                        @focus="isPasswordFocused = true"
                                        @blur="isPasswordFocused = false" 
                                    />
                                    <InputError class="text-red-600" :message="form.errors.password" />

                                    <!-- Password validation tooltip UPDATE 04/04-->

                                    <div v-if="isPasswordFocused && form.password" class="mt-2 p-3 bg-gray-800 text-white rounded-md w-64 text-sm shadow-lg">

                                        <p class="font-semibold text-gray-200">Password must meet the following:</p>
                                        <ul class="mt-1">
                                            <li :class="passwordCriteria.length ? 'text-green-400' : 'text-red-400'">
                                                ✔ Minimum 8 characters
                                            </li>
                                            <li
                                                :class="passwordCriteria.uppercaseLowercase ? 'text-green-400' : 'text-red-400'">
                                                ✔ Upper & lower case letters
                                            </li>
                                            <li :class="passwordCriteria.number ? 'text-green-400' : 'text-red-400'">
                                                ✔ At least one number
                                            </li>
                                            <li :class="passwordCriteria.symbol ? 'text-green-400' : 'text-red-400'">
                                                ✔ At least one special character (@$!%*?&.)
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div>
                                    <InputLabel for="password_confirmation" class="text-slate-800 font-medium">
                                        Confirm Password <span class="text-red-500">*</span>
                                    </InputLabel>
                                    <TextInput 
                                        id="password_confirmation" 
                                        v-model="form.password_confirmation"
                                        type="password" 
                                        class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" 
                                        placeholder="Confirm your password" 
                                        required 
                                    />
                                    <InputError class="text-red-600" :message="form.errors.password_confirmation" />
                                </div>
                            </div>
                        </div>
                        </div> <!-- <-- Add this closing div for step 3 -->

                        <!-- Navigation Buttons -->
                        <div class="flex items-center justify-between mt-8 pt-6">
                            <button 
                                v-if="currentStep > 1"
                                @click="prevStep"
                                type="button"
                                class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200 font-medium">
                                <i class="fas fa-arrow-left mr-2"></i>Previous
                            </button>
                            <div v-else></div>
                            
                            <button 
                                v-if="currentStep < totalSteps"
                                @click="nextStep"
                                :disabled="!canProceed"
                                type="button"
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 font-medium">
                                Next<i class="fas fa-arrow-right ml-2"></i>
                            </button>
                            
                            <PrimaryButton 
                                v-if="currentStep === totalSteps"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 rounded-lg font-medium" 
                                :class="{ 'opacity-25': form.processing || !canProceed }" 
                                :disabled="form.processing || !canProceed">
                                <i class="fas fa-user-plus mr-2"></i>Register
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
                
                <ConfirmationModal :show="showModal" @close="showModal = false">
                    <template #title>Success</template>
                    <template #content>{{ message }}</template>
                    <template #footer>
                    <button @click="showModal = false" class="bg-green-600 text-white px-4 py-2 rounded">OK</button>
                    </template>
                </ConfirmationModal>
            </div>
        </AppLayout>
</template>
