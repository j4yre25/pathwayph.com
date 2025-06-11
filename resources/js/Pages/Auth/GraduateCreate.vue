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
    router.post(route('graduates.store'), form.value);
};

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <AppLayout title="Graduate Registration">
        <Container>
            <!-- Back Button and Header -->
            <div class="flex items-center mt-6 mb-4">
                <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="flex items-center">
                    <i class="fas fa-user-graduate text-blue-500 text-xl mr-2"></i>
                    <h1 class="text-2xl font-bold text-gray-800">Graduate Registration</h1>
                </div>
            </div>
            <p class="text-sm text-gray-500 mb-6">Register a new graduate in the system.</p>

            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-8">
                <form @submit.prevent="createGraduate" class="space-y-8">
                    <!-- Personal Information -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-user text-blue-500 mr-2"></i>
                            Personal Information
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="flex flex-col">
                                <label for="first_name" class="text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input v-model="form.first_name" type="text" id="first_name" required
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <label for="middle_name" class="text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input v-model="form.middle_name" type="text" id="middle_name"
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <label for="last_name" class="text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input v-model="form.last_name" type="text" id="last_name" required
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <label for="email" class="text-sm font-medium text-gray-700 mb-1">Email</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                    </div>
                                    <input v-model="form.email" type="email" id="email" required
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <label for="contact_number" class="text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-phone text-gray-400"></i>
                                    </div>
                                    <input v-model="form.contact_number" type="text" id="contact_number" required
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <label for="gender" class="text-sm font-medium text-gray-700 mb-1">Gender</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-venus-mars text-gray-400"></i>
                                    </div>
                                    <select v-model="form.gender" id="gender" required
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none appearance-none">
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
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
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                                </div>
                            </div>

                            <div class="flex flex-col md:col-span-2">
                                <label for="address" class="text-sm font-medium text-gray-700 mb-1">Address</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-map-marker-alt text-gray-400"></i>
                                    </div>
                                    <input v-model="form.address" type="text" id="address" required
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Information -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-graduation-cap text-blue-500 mr-2"></i>
                            Academic Information
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="flex flex-col">
                                <label for="degree_id" class="text-sm font-medium text-gray-700 mb-1">Degree</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-scroll text-gray-400"></i>
                                    </div>
                                    <select v-model="form.degree_id" id="degree_id" required
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none appearance-none">
                                        <option value="">Select Degree</option>
                                        <option v-for="degree in degrees" :key="degree.id" :value="degree.id">
                                            {{ degree.type }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
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
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none appearance-none"
                                        :disabled="!form.degree_id">
                                        <option value="">Select Program</option>
                                        <option v-for="program in filteredPrograms" :key="program.id" :value="program.id">
                                            {{ program.name }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
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
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none appearance-none">
                                        <option value="">Select School Year</option>
                                        <option v-for="school_year in school_years" :key="school_year.id"
                                            :value="school_year.id">
                                            {{ school_year.school_year_range }} - {{ school_year.term }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Information -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-briefcase text-blue-500 mr-2"></i>
                            Employment Information
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="flex flex-col">
                                <label for="employment_status" class="text-sm font-medium text-gray-700 mb-1">Employment Status</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user-tie text-gray-400"></i>
                                    </div>
                                    <select v-model="form.employment_status" id="employment_status" required
                                        @change="handleEmploymentStatusChange"
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none appearance-none">
                                        <option value="Employed">Employed</option>
                                        <option value="Unemployed">Unemployed</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
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
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                                </div>
                            </div>

                            <div class="flex flex-col" v-if="form.employment_status === 'Employed'">
                                <label for="position" class="text-sm font-medium text-gray-700 mb-1">Position</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-id-badge text-gray-400"></i>
                                    </div>
                                    <input v-model="form.position" type="text" id="position" required
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                                </div>
                            </div>

                            <div class="flex flex-col" v-if="form.employment_status === 'Employed'">
                                <label for="date_hired" class="text-sm font-medium text-gray-700 mb-1">Date Hired</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-calendar-alt text-gray-400"></i>
                                    </div>
                                    <input v-model="form.date_hired" type="date" id="date_hired" required
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition flex items-center">
                            <i class="fas fa-save mr-2"></i>
                            Register Graduate
                        </button>
                    </div>
                </form>
            </div>
        </Container>
    </AppLayout>
</template>
