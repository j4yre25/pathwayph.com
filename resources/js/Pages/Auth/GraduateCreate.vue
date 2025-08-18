<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, computed } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
    degrees: {
        type: Array,
        default: () => ([]),
    },
    programs: {
        type: Array,
        default: () => ([]),
    },
    school_years: {
        type: Array,
        default: () => ([]),
    },
});

const form = ref({
    first_name: '',
    middle_name: '',
    last_name: '',
    email: '',
    contact_number: '',
    address: '',
    gender: '',
    birth_date: '',
    degree_id: '',
    program_id: '',
    school_year_id: '',
    employment_status: 'Unemployed',
    company_name: '',
    position: '',
    date_hired: '',
});

// Step process variables
const showSuccess = ref(false);
const currentStep = ref(1);
const totalSteps = 3;

const stepTitles = [
  'Personal Information',
  'Academic Information',
  'Employment Information'
];

const stepIcons = [
  'fas fa-user',
  'fas fa-graduation-cap',
  'fas fa-briefcase'
];

const stepColors = [
  'blue',
  'green',
  'purple'
];

function nextStep() {
  if (currentStep.value < totalSteps) {
    currentStep.value++;
  }
}

function prevStep() {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
}

function goToStep(step) {
  if (step >= 1 && step <= totalSteps) {
    currentStep.value = step;
  }
}

const handleEmploymentStatusChange = () => {
    if (form.value.employment_status === 'Unemployed') {
        form.value.company_name = '';
        form.value.position = '';
        form.value.date_hired = '';
    }
};

const filteredPrograms = computed(() => {
    if (!form.value.degree_id) return [];
    return props.programs.filter(program => program.degree_id === form.value.degree_id);
});

const createGraduate = () => {
    router.post(route('graduates.store'), form.value, {
        onSuccess: () => {
            showSuccess.value = true;
            form.value = {
                first_name: '',
                middle_name: '',
                last_name: '',
                email: '',
                contact_number: '',
                address: '',
                gender: '',
                birth_date: '',
                degree_id: '',
                program_id: '',
                school_year_id: '',
                employment_status: 'Unemployed',
                company_name: '',
                position: '',
                date_hired: '',
            };
            currentStep.value = 1;
        },
    });
};

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <AppLayout title="Graduate Registration">
        <template #header>
            <div>
                <div class="flex items-center">
                    <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <i class="fas fa-user-graduate text-blue-500 text-xl mr-2"></i>
                    <h2 class="text-2xl font-bold text-gray-800">Graduate Registration</h2>
                </div>
                <p class="text-sm text-gray-500 mb-1">Register a new graduate in the system.</p>
            </div>
        </template>
        
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-user-graduate text-blue-500 mr-2"></i>
                                <h3 class="text-lg font-medium text-gray-900">New Graduate Registration</h3>
                            </div>
                        </div>
                <div class="mb-8">
                    <div class="flex justify-between mb-2">
                        <div v-for="(title, index) in stepTitles" :key="index" 
                            class="flex flex-col items-center cursor-pointer" 
                            @click="goToStep(index + 1)">
                            <div :class="[`text-${stepColors[index]}-500 bg-${stepColors[index]}-100`, 
                                        currentStep > index + 1 ? `bg-${stepColors[index]}-500 text-white` : '',
                                        currentStep === index + 1 ? `ring-2 ring-${stepColors[index]}-500` : '']"
                                class="w-10 h-10 rounded-full flex items-center justify-center mb-1 transition-all duration-200">
                                <i v-if="currentStep > index + 1" class="fas fa-check"></i>
                                <i v-else :class="stepIcons[index]"></i>
                            </div>
                            <span :class="[currentStep === index + 1 ? `text-${stepColors[index]}-600 font-medium` : 'text-gray-500']"
                                class="text-xs text-center hidden sm:block">
                                {{ title }}
                            </span>
                        </div>
                    </div>
                    <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="absolute top-0 left-0 h-full transition-all duration-300 ease-in-out"
                            :class="`bg-${stepColors[currentStep-1]}-500`"
                            :style="{width: `${(currentStep / totalSteps) * 100}%`}"></div>
                    </div>
                </div>
                
                <form @submit.prevent="createGraduate" class="space-y-6">
                    <!-- Step 1: Personal Information -->
                    <div v-show="currentStep === 1" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-user text-blue-500 mr-2"></i>
                            Personal Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="flex flex-col">
                                <label for="first_name" class="text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input v-model="form.first_name" type="text" id="first_name" required
                                        class="pl-10 w-full py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <label for="middle_name" class="text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input v-model="form.middle_name" type="text" id="middle_name"
                                        class="pl-10 w-full py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <label for="last_name" class="text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input v-model="form.last_name" type="text" id="last_name" required
                                        class="pl-10 w-full py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <label for="email" class="text-sm font-medium text-gray-700 mb-1">Email</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                    </div>
                                    <input v-model="form.email" type="email" id="email" required
                                        class="pl-10 w-full py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <label for="contact_number" class="text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-phone text-gray-400"></i>
                                    </div>
                                    <input v-model="form.contact_number" type="text" id="contact_number" required
                                        class="pl-10 w-full py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <label for="gender" class="text-sm font-medium text-gray-700 mb-1">Gender</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-venus-mars text-gray-400"></i>
                                    </div>
                                    <select v-model="form.gender" id="gender" required
                                        class="pl-10 w-full py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none appearance-none transition">
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <label for="birth_date" class="text-sm font-medium text-gray-700 mb-1">Birth Date</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-calendar-alt text-gray-400"></i>
                                    </div>
                                    <input v-model="form.birth_date" type="date" id="birth_date" required
                                        class="pl-10 w-full py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                </div>
                            </div>

                            <div class="flex flex-col md:col-span-3">
                                <label for="address" class="text-sm font-medium text-gray-700 mb-1">Address</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-map-marker-alt text-gray-400"></i>
                                    </div>
                                    <input v-model="form.address" type="text" id="address" required
                                        class="pl-10 w-full py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Academic Information -->
                    <div v-show="currentStep === 2" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-graduation-cap text-green-500 mr-2"></i>
                            Academic Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="flex flex-col">
                                <label for="degree_id" class="text-sm font-medium text-gray-700 mb-1">Degree</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-scroll text-gray-400"></i>
                                    </div>
                                    <select v-model="form.degree_id" id="degree_id" required
                                        class="pl-10 w-full py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none appearance-none transition">
                                        <option value="">Select Degree</option>
                                        <option v-for="degree in degrees" :key="degree.id" :value="degree.id">
                                            {{ degree.type }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <label for="program_id" class="text-sm font-medium text-gray-700 mb-1">Program</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-book text-gray-400"></i>
                                    </div>
                                    <select v-model="form.program_id" id="program_id" required
                                        class="pl-10 w-full py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none appearance-none transition"
                                        :disabled="!form.degree_id">
                                        <option value="">Select Program</option>
                                        <option v-for="program in filteredPrograms" :key="program.id" :value="program.id">
                                            {{ program.name }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <label for="school_year_id" class="text-sm font-medium text-gray-700 mb-1">School Year</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-calendar-check text-gray-400"></i>
                                    </div>
                                    <select v-model="form.school_year_id" id="school_year_id" required
                                        class="pl-10 w-full py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none appearance-none transition">
                                        <option value="">Select School Year</option>
                                        <option v-for="school_year in school_years" :key="school_year.id"
                                            :value="school_year.id">
                                            {{ school_year.school_year_range }} - {{ school_year.term }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Employment Information -->
                    <div v-show="currentStep === 3" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-briefcase text-purple-500 mr-2"></i>
                            Employment Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="flex flex-col">
                                <label for="employment_status" class="text-sm font-medium text-gray-700 mb-1">Employment Status</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user-tie text-gray-400"></i>
                                    </div>
                                    <select v-model="form.employment_status" id="employment_status" required
                                        @change="handleEmploymentStatusChange"
                                        class="pl-10 w-full py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none appearance-none transition">
                                        <option value="">Select Employment Status</option>
                                        <option value="Employed">Employed</option>
                                        <option value="Unemployed">Unemployed</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col" v-if="form.employment_status === 'Employed'">
                                <label for="company_name" class="text-sm font-medium text-gray-700 mb-1">Company Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-building text-gray-400"></i>
                                    </div>
                                    <input v-model="form.company_name" type="text" id="company_name" required
                                        class="pl-10 w-full py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                </div>
                            </div>

                            <div class="flex flex-col" v-if="form.employment_status === 'Employed'">
                                <label for="position" class="text-sm font-medium text-gray-700 mb-1">Position</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-id-badge text-gray-400"></i>
                                    </div>
                                    <input v-model="form.position" type="text" id="position" required
                                        class="pl-10 w-full py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                </div>
                            </div>

                            <div class="flex flex-col" v-if="form.employment_status === 'Employed'">
                                <label for="date_hired" class="text-sm font-medium text-gray-700 mb-1">Date Hired</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-calendar-alt text-gray-400"></i>
                                    </div>
                                    <input v-model="form.date_hired" type="date" id="date_hired" required
                                        class="pl-10 w-full py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <div>
                            <button 
                                v-if="currentStep > 1" 
                                type="button" 
                                @click="prevStep"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                            >
                                <i class="fas fa-chevron-left mr-2"></i>
                                Previous
                            </button>
                            <button 
                                v-else 
                                @click="goBack"
                                type="button"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                            >
                                <i class="fas fa-times mr-2"></i>
                                Cancel
                            </button>
                        </div>
                        <div>
                            <button
                                v-if="currentStep < totalSteps"
                                type="button"
                                @click="nextStep"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                            >
                                Next
                                <i class="fas fa-chevron-right ml-2"></i>
                            </button>
                            <button
                                v-else
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                            >
                                <i class="fas fa-user-plus mr-2"></i>
                                <span>Register Graduate</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Success Message -->
                    <transition name="fade">
                        <div
                            v-if="showSuccess"
                            class="mt-4 p-4 bg-green-50 border border-green-200 rounded-md text-green-600 font-medium flex items-center"
                        >
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Graduate registered successfully!</span>
                        </div>
                    </transition>
                </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
