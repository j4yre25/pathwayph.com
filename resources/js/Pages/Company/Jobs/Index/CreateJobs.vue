<script setup>
import { router, usePage, useForm, Link } from '@inertiajs/vue3'
import { ref, watch, computed, watchEffect } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import MultiSelect from '@/Components/MultiSelect.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import '@fortawesome/fontawesome-free/css/all.css';
import Modal from '@/Components/Modal.vue';
import { useSectorCategories } from '@/Composables/useSectorCategories';
import { useWorkEnvironmentValidation } from '@/Composables/useWorkEnvironmentValidation';
import { useSkills } from '@/Composables/useSkills';


const page = usePage()

const props = defineProps({
    jobs: Array,
    programs: Array,
    authUser: Object,
    skills: Array,
    departments: Array,
    defaultLocations: { type: Array, default: () => [] } // [{id:1,address:'City, Province'}, ...]
})

const programs = props.programs;

const postedBy = computed(() => {
    return props.authUser?.hr?.full_name ?? props.authUser?.name ?? '';
});

const jobTypes = [
    { id: 1, type: 'Full-time' },
    { id: 2, type: 'Part-time' },
    { id: 3, type: 'Contract' },
    { id: 4, type: 'Freelance' },
    { id: 5, type: 'Internship' }
];

const workEnvironments = [
    { id: 1, environment_type: 'On-site' },
    { id: 2, environment_type: 'Remote' },
    { id: 3, environment_type: 'Hybrid' },
];

const selectedJobTypeLabel = computed(() => {
    const found = jobTypes.find(jt => jt.id === form.job_type);
    return found ? found.type : 'Not provided';
});

const selectedWorkEnvironmentLabel = computed(() => {
    const found = workEnvironments.find(env => env.id === form.work_environment);
    return found ? found.environment_type : 'Not provided';
});

const isSuccessModalOpen = ref(false);
const successMessage = ref('');

const form = useForm({
    job_title: '',
    location: '',
    program_id: [],
    department_id: '',
    job_vacancies: '',
    salary: {
        salary_type: '',
        job_min_salary: '',
        job_max_salary: '',
    },
    is_negotiable: false,
    work_environment: '',
    job_type: '',
    job_experience_level: '',
    job_description: '',
    skills: [],
    // sector: '',
    // category: '',
    job_requirements: '',
    job_application_limit: '',
    job_deadline: '',
    posted_by: postedBy.value,
    error: {}
});

watchEffect(() => {
    form.posted_by = postedBy.value;
});

watch(() => form.department_id, (val) => {
    if (typeof val === 'string' && val !== '') {
        form.department_id = parseInt(val);
    }
});

const selectedDepartmentName = computed(() => {
    if (!form.department_id) return 'Not provided';
    const dep = props.departments.find(d => d.id === form.department_id);
    return dep ? dep.department_name : 'Not provided';
});

// Tabs setup
const currentStep = ref('job-details');
// Navigate to previous step
const goToPreviousStep = () => {
    if (currentStep.value === 'job-details') {
    } else if (currentStep.value === 'salary-info') {
        currentStep.value = 'job-details';
    } else if (currentStep.value === 'description') {
        currentStep.value = 'salary-info';
    } else if (currentStep.value === 'review') {
        currentStep.value = 'description';
    }
};

// Navigate to next step
const goToNextStep = () => {
    if (currentStep.value === 'job-details') {
        currentStep.value = 'salary-info';
    } else if (currentStep.value === 'salary-info') {
        currentStep.value = 'description';
    } else if (currentStep.value === 'description') {
        currentStep.value = 'review';
    }
};
// End for the tabs setup


const expirationDate = ref(null)
const today = new Date()

//Validation for vacancies and application limit
watch(() => form.job_vacancies, (vacancies) => {
    if (form.job_application_limit && Number(form.job_application_limit) < Number(vacancies)) {
        form.job_application_limit = vacancies;
    }
});

// const { availableCategories } = useSectorCategories(form, props.sectors);
const { validateLocation } = useWorkEnvironmentValidation(form);
const { newSkill, showSuggestions, filteredSkills, addSkill, removeSkill, selectSuggestion, filterSuggestions } = useSkills(form, props.skills);

console.log('newSkill:', newSkill);

const showMissingFieldsModal = ref(false);
const missingFields = ref([]);

function getMissingFields() {
    const fields = [];
    if (!form.job_title) fields.push('Job Title');
    if (!form.program_id || form.program_id.length === 0) fields.push('Program');
    if (!form.job_type) fields.push('Job Type');
    if (!form.job_experience_level) fields.push('Experience Level');
    if (!form.job_vacancies) fields.push('Number of Vacancies');
    if (!form.work_environment) fields.push('Work Environment');
    if (props.departments && props.departments.length && !form.department_id) fields.push('Department');
    if (form.work_environment != 2 && !form.location) fields.push('Job Location');
    if (!form.salary.salary_type) fields.push('Salary Type');
    if (!form.is_negotiable && (!form.salary.job_min_salary || !form.salary.job_max_salary)) fields.push('Salary Range');
    if (!form.job_description) fields.push('Job Description');
    if (!form.job_requirements) fields.push('Job Requirements');
    if (!form.posted_by) fields.push('Posted By');
    if (!form.job_deadline) fields.push('Application Deadline');
    return fields;
}

const createJob = () => {
    console.log('createJob called');
    missingFields.value = getMissingFields();
    console.log('missingFields:', missingFields.value);
    if (missingFields.value.length > 0) {
        showMissingFieldsModal.value = true;
        return;
    }

    form.program_id = form.program_id.map(p => p.id ?? p);

    if (String(form.work_environment) === '2') {
        form.location = null
    }

    if (!validateLocation()) {
        console.log('Location validation failed.');
        return;
    }

    form.post(route('company.jobs.store', { user: page.props.auth.user.id }), {
        onSuccess: (inertiaPage) => {
            const invitedCount = inertiaPage.props.flash?.job_invited_count ?? 0
            const plural = invitedCount === 1 ? 'graduate' : 'graduates'
            const verb = invitedCount === 1 ? 'is' : 'are'
            successMessage.value = `Job submitted for PESO approval. ${invitedCount} ${plural} ${verb} possible to be invited.`
            isSuccessModalOpen.value = true
            form.reset()
        },
        onError: (errors) => {
            console.log('Validation errors:', errors);
        },
        onFinish: () => {
            form.processing = false;
        }
    });


}

function onSkillFocus() {
    showSuggestions.value = true;
}

// Autocomplete state for location
const showLocationSuggestions = ref(false)
const filteredLocationSuggestions = computed(() => {
    if (String(form.work_environment) === '2') return []
    const q = (form.location || '').toLowerCase().trim()
    const all = props.defaultLocations || []
    if (!q) return all.slice(0, 8)
    return all
        .filter(l => (l.address || '').toLowerCase().includes(q))
        .slice(0, 8)
})

function onLocationFocus() {
    if (String(form.work_environment) === '2') return
    showLocationSuggestions.value = true
}
function onLocationInput() {
    if (String(form.work_environment) === '2') {
        showLocationSuggestions.value = false
        return
    }
    showLocationSuggestions.value = true
}
function onLocationBlur() {
    // Delay hiding so click can register
    setTimeout(() => { showLocationSuggestions.value = false }, 120)
}
function selectLocationSuggestion(address) {
    form.location = address
    showLocationSuggestions.value = false
}

// Simplify remote watcher
watch(() => form.work_environment, (val) => {
    if (String(val) === '2') {
        form.location = ''
        showLocationSuggestions.value = false
    }
})
</script>


<template>
    <AppLayout title="Post a New Job">
        <template #header>
            <div>
                <div class="flex items-center">
                    <Link :href="route('company.jobs', { user: page.props.auth.user.id })"
                        class="text-gray-500 hover:text-gray-700 mr-3">
                    <i class="fas fa-chevron-left"></i>
                    </Link>
                    <i class="fas fa-folder-plus text-blue-500 text-xl mr-2"></i>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Create New Job Posting
                    </h2>
                </div>
                <p class="text-sm text-gray-500 mt-2">Please fill in the details below to create a new job posting.</p>

            </div>
        </template>

        <!-- Modal for missing required fields -->
        <Modal :model-value="showMissingFieldsModal" @close="showMissingFieldsModal = false">
            <template #header>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100">
                        <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                    </span>
                    <span class="text-lg font-bold text-red-600">Missing Required Fields</span>
                </div>
            </template>
            <template #body>
                <div class="py-2">
                    <p class="mb-3 text-gray-700">
                        Please fill in the following required fields before posting your job:
                    </p>
                    <ul class="list-disc pl-6 space-y-1">
                        <li v-for="field in missingFields" :key="field" class="text-red-600 font-medium">
                            <i class="fas fa-asterisk text-xs mr-1"></i>{{ field }}
                        </li>
                    </ul>
                </div>
            </template>
            <template #footer>
                <PrimaryButton class="bg-red-600 hover:bg-red-700 border-none" @click="showMissingFieldsModal = false">
                    <i class="fas fa-times mr-2"></i> Close
                </PrimaryButton>
            </template>
        </Modal>

        <Container class="py-15">
            <div>
                <FormSection @submitted="createJob">
                    <template #form>
                        <!-- Progress Tabs -->
                        <div class="flex justify-center mb-8 border-b">
                            <div @click="currentStep = 'job-details'"
                                :class="['px-4 py-2 cursor-pointer flex flex-col items-center',
                                    currentStep === 'job-details' ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500']">
                                <i class="fas fa-briefcase text-xl mb-1"></i>
                                <span>Basic Job Details</span>
                            </div>
                            <div @click="currentStep === 'job-details' ? null : currentStep = 'salary-info'" :class="['px-4 py-2 cursor-pointer flex flex-col items-center',
                                currentStep === 'salary-info' ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500',
                                currentStep === 'job-details' ? 'opacity-50 cursor-not-allowed' : '']">
                                <i class="fas fa-money-bill-wave text-xl mb-1"></i>
                                <span>Salary Information</span>
                            </div>
                            <div @click="currentStep === 'job-details' || currentStep === 'salary-info' ? null : currentStep = 'description'"
                                :class="['px-4 py-2 cursor-pointer flex flex-col items-center',
                                    currentStep === 'description' ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500',
                                    currentStep === 'job-details' || currentStep === 'salary-info' ? 'opacity-50 cursor-not-allowed' : '']">
                                <i class="fas fa-clipboard-list text-xl mb-1"></i>
                                <span>Description & Requirements</span>
                            </div>
                            <div @click="currentStep === 'job-details' || currentStep === 'salary-info' || currentStep === 'description' ? null : currentStep = 'review'"
                                :class="['px-4 py-2 cursor-pointer flex flex-col items-center',
                                    currentStep === 'review' ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500',
                                    currentStep === 'job-details' || currentStep === 'salary-info' || currentStep === 'description' ? 'opacity-50 cursor-not-allowed' : '']">
                                <i class="fas fa-info-circle text-xl mb-1"></i>
                                <span>Other Information</span>
                            </div>
                        </div>

                        <!-- Basic Job Details Tab -->
                        <div v-if="currentStep === 'job-details'" class="space-y-6">
                            <div>
                                <!-- Job Title -->
                                <div>
                                    <InputLabel for="job_title">
                                        Job Title <span class="text-pink-500">*</span>
                                    </InputLabel>
                                    <TextInput id="job_title" type="text" class="mt-1 block w-full"
                                        v-model="form.job_title" required autofocus />
                                    <InputError class="mt-2" :message="form.errors.job_title" />
                                </div>

                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <!-- Program Selection -->
                                <div>
                                    <InputLabel for="program_id">
                                        Program <span class="text-pink-500">*</span>
                                    </InputLabel>
                                    <MultiSelect id="program_id" class="mt-1 block w-full" v-model="form.program_id"
                                        :options="props.programs" label="name" track-by="id" :searchable="true"
                                        :multiple="true" placeholder="Select programs" />
                                    <InputError class="mt-2" :message="form.errors.program_id" />
                                </div>

                                <!-- Job Type -->
                                <div>
                                    <InputLabel for="job_type">
                                        Job Type <span class="text-pink-500">*</span>
                                    </InputLabel>
                                    <select id="job_type"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.job_type" required>
                                        <option value="">Select Job Type</option>
                                        <option v-for="type in jobTypes" :key="type.id" :value="type.id">
                                            {{ type.type }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.job_type" />
                                </div>

                                <!-- Experience Level -->
                                <div>
                                    <InputLabel for="job_experience_level">
                                        Experience Level <span class="text-pink-500">*</span>
                                    </InputLabel>
                                    <select id="job_experience_level"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.job_experience_level" required>
                                        <option value="">Select Experience Level</option>
                                        <option value="Entry-level">Entry-level</option>
                                        <option value="Intermediate">Intermediate</option>
                                        <option value="Mid-level">Mid-level</option>
                                        <option value="Senior-executive">Senior/Executive-level</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.job_experience_level" />
                                </div>

                                <!-- Number of Vacancies -->
                                <div>
                                    <InputLabel for="job_vacancies">
                                        Number of Vacancies <span class="text-pink-500">*</span>
                                    </InputLabel>
                                    <TextInput id="job_vacancies" type="number" class="mt-1 block w-full"
                                        v-model="form.job_vacancies" required min="1" />
                                    <InputError class="mt-2" :message="form.errors.job_vacancies" />
                                </div>

                                <!-- Work Environment -->
                                <div>
                                    <InputLabel for="work_environment">
                                        Work Environment <span class="text-pink-500">*</span>
                                    </InputLabel>
                                    <select id="work_environment"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.work_environment" required>
                                        <option v-for="environment in workEnvironments" :key="environment.id"
                                            :value="environment.id">
                                            {{ environment.environment_type }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.work_environment" />
                                </div>

                                <!-- Department Field -->
                                <div>
                                    <InputLabel for="department_id">
                                        Department
                                        <span v-if="props.departments && props.departments.length"
                                            class="text-pink-500">*</span>
                                    </InputLabel>
                                    <template v-if="props.departments && props.departments.length">
                                        <select id="department_id"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            v-model="form.department_id" required>
                                            <option value="">Select Department</option>
                                            <option v-for="dep in props.departments" :key="dep.id" :value="dep.id">
                                                {{ dep.department_name }}
                                            </option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.department_id" />
                                    </template>
                                    <template v-else>
                                        <div
                                            class="mt-2 px-4 py-3 bg-yellow-50 border border-yellow-200 rounded text-yellow-700 text-sm">
                                            No departments found for your company.<br>
                                            You can add a department in the HR & Departments tab. This field will be
                                            required once a department is added.
                                        </div>
                                    </template>
                                </div>

                            </div>
                            <!-- Job Location (autocomplete) -->
                            <div>
                                <InputLabel for="location">
                                    Job Location
                                    <span class="text-pink-500" v-if="form.work_environment != 2">*</span>
                                </InputLabel>

                                <div class="relative mt-1">
                                    <TextInput
                                        id="location"
                                        type="text"
                                        class="block w-full"
                                        v-model="form.location"
                                        :disabled="form.work_environment == 2"
                                        :required="form.work_environment != 2"
                                        :placeholder="form.work_environment == 2
                                          ? 'Remote job — no location needed'
                                          : 'Type an address or start typing to see suggestions'"
                                        @focus="onLocationFocus"
                                        @input="onLocationInput"
                                        @blur="onLocationBlur"
                                    />

                                    <!-- Suggestions dropdown -->
                                    <ul
                                        v-if="showLocationSuggestions && filteredLocationSuggestions.length"
                                        class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-auto text-sm"
                                    >
                                        <li
                                          v-for="loc in filteredLocationSuggestions"
                                          :key="loc.id"
                                          class="px-3 py-2 hover:bg-indigo-50 cursor-pointer flex items-start gap-2"
                                          @mousedown.prevent="selectLocationSuggestion(loc.address)"
                                        >
                                          <i class="fas fa-map-marker-alt text-indigo-500 mt-0.5 text-xs"></i>
                                          <span class="text-gray-700">{{ loc.address }}</span>
                                        </li>
                                        <li
                                          v-if="form.location && !filteredLocationSuggestions.some(l => l.address === form.location)"
                                          class="px-3 py-2 text-gray-500 italic text-xs"
                                        >
                                          Press Enter to keep custom location: "{{ form.location }}"
                                        </li>
                                    </ul>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                  Start typing to search default locations or enter a custom address.
                                </p>
                                <InputError class="mt-2" :message="form.errors.location" />
                            </div>

                            <div class="flex justify-end">
                                <PrimaryButton @click="goToNextStep" type="button">
                                    Next <i class="fas fa-chevron-right ml-1"></i>
                                </PrimaryButton>
                            </div>
                        </div>


                        <!-- Salary Information Tab -->
                        <div v-if="currentStep === 'salary-info'" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Salary Type -->
                                <div>
                                    <InputLabel for="job_salary_type">
                                        Salary Type <span class="text-pink-500">*</span>
                                    </InputLabel>
                                    <select id="salary_type"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.salary.salary_type">
                                        <option value="">Select Salary Type</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="hourly">Hourly</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors['salary.salary_type']" />
                                </div>

                                <!-- Negotiable Salary Checkbox -->
                                <div class="flex items-center mt-6">
                                    <input id="is_negotiable" type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        v-model="form.is_negotiable" />
                                    <label for="is_negotiable" class="ml-2 block text-sm text-gray-900">
                                        Salary is negotiable
                                    </label>
                                </div>

                                <!-- Minimum Salary -->
                                <div>
                                    <InputLabel for="job_min_salary">
                                        Minimum Salary <span class="text-pink-500" v-if="!form.is_negotiable">*</span>
                                    </InputLabel>
                                    <div class="relative mt-1">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">₱</span>
                                        </div>
                                        <TextInput id="job_min_salary" type="number" class="pl-7 block w-full"
                                            v-model="form.salary.job_min_salary" :disabled="form.is_negotiable" />
                                    </div>
                                </div>

                                <!-- Maximum Salary -->
                                <div>
                                    <InputLabel for="job_max_salary">
                                        Maximum Salary <span class="text-pink-500" v-if="!form.is_negotiable">*</span>
                                    </InputLabel>
                                    <div class="relative mt-1">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">₱</span>
                                        </div>
                                        <TextInput id="max_salary" type="number" class="pl-7 block w-full"
                                            v-model="form.salary.job_max_salary" :disabled="form.is_negotiable" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between">
                                <PrimaryButton @click="goToPreviousStep" type="button"
                                    class="bg-gray-500 hover:bg-gray-600">
                                    <i class="fas fa-chevron-left mr-1"></i>Previous
                                </PrimaryButton>
                                <PrimaryButton @click="goToNextStep" type="button">
                                    Next <i class="fas fa-chevron-right ml-1"></i>
                                </PrimaryButton>
                            </div>
                        </div>

                        <!-- Description & Requirements Tab -->
                        <div v-if="currentStep === 'description'" class="space-y-6">
                            <!-- Job Description -->
                            <div>
                                <InputLabel for="job_description">
                                    Job Description <span class="text-pink-500">*</span>
                                </InputLabel>
                                <RichTextEditor id="job_description" v-model="form.job_description"
                                    class="mt-1 block w-full" placeholder="Enter job description" />
                                <p class="mt-1 text-xs text-gray-500">Provide a detailed description of the job
                                    responsibilities and duties.</p>
                                <InputError class="mt-2" :message="form.errors.job_description" />
                            </div>

                            <!-- Job Requirements -->
                            <div>
                                <InputLabel for="job_requirements">
                                    Job Requirements <span class="text-pink-500">*</span>
                                </InputLabel>
                                <RichTextEditor id="job_requirements" v-model="form.job_requirements"
                                    class="mt-1 block w-full" placeholder="Enter job requirements" />
                                <p class="mt-1 text-xs text-gray-500">List the qualifications, skills, and experience
                                    required for this position.</p>
                                <InputError class="mt-2" :message="form.errors.job_requirements" />
                            </div>

                            <!-- Skills -->
                            <div>
                                <InputLabel for="skills">
                                    Skills <span class="text-pink-500">*</span>
                                </InputLabel>
                                <div class="relative">
                                    <div class="flex mt-1">
                                        <input id="skills" type="text" class="block w-full" v-model="newSkill"
                                            placeholder="Enter a skill" @input="filterSuggestions"
                                            @keyup.enter.prevent="addSkill" @focus="onSkillFocus" />
                                        <PrimaryButton type="button" @click="addSkill" class="ml-2">
                                            Add
                                        </PrimaryButton>
                                    </div>
                                    <div v-if="showSuggestions && filteredSkills.length > 0"
                                        class="absolute z-10 bg-white border mt-1 w-full rounded shadow max-h-40 overflow-auto">
                                        <div v-for="(suggestion, i) in filteredSkills" :key="i"
                                            @click="selectSuggestion(suggestion)"
                                            class="px-3 py-2 hover:bg-indigo-100 cursor-pointer">
                                            {{ suggestion }}
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    Add relevant skills required for this position (e.g., JavaScript, Project
                                    Management).
                                </p>
                                <div v-if="form.skills.length > 0" class="mt-3 flex flex-wrap gap-2">
                                    <div v-for="(skill, index) in form.skills" :key="index"
                                        class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full flex items-center">
                                        <span>{{ skill }}</span>
                                        <button type="button" @click="removeSkill(index)"
                                            class="ml-2 text-blue-600 hover:text-blue-800 focus:outline-none">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.skills" />
                            </div>

                            <div class="flex justify-between">
                                <PrimaryButton @click="goToPreviousStep" type="button"
                                    class="bg-gray-500 hover:bg-gray-600">
                                    Previous <i class="fas fa-chevron-left mr-1"></i>
                                </PrimaryButton>
                                <PrimaryButton @click="goToNextStep" type="button">
                                    Next <i class="fas fa-chevron-right ml-1"></i>
                                </PrimaryButton>
                            </div>
                        </div>

                        <!-- Other Information Tab -->
                        <div v-if="currentStep === 'review'" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Posted By -->
                                <div>
                                    <InputLabel for="posted_by">
                                        Posted By <span class="text-pink-500">*</span>
                                    </InputLabel>
                                    <TextInput id="posted_by" type="text" class="mt-1 block w-full"
                                        v-model="form.posted_by" required />
                                    <InputError class="mt-2" :message="form.errors.posted_by" />
                                </div>

                                <!-- Application Deadline -->
                                <div>
                                    <InputLabel for="job_deadline">
                                        Application Deadline <span class="text-pink-500">*</span>
                                    </InputLabel>
                                    <Datepicker v-model="form.job_deadline" :enable-time-picker="false"
                                        input-class-name="w-full p-2 border rounded-lg mt-2" :min-date="today"
                                        placeholder="Select date" required />
                                    <InputError class="mt-2" :message="form.errors.job_deadline" />
                                </div>

                                <!-- Application Limit -->
                                <div>
                                    <InputLabel for="job_application_limit">
                                        Application Limit
                                    </InputLabel>
                                    <TextInput id="job_application_limit" type="number" class="mt-1 block w-full"
                                        v-model="form.job_application_limit" :min="form.job_vacancies || 1"
                                        :placeholder="form.job_vacancies ? `Minimum: ${form.job_vacancies}` : 'Enter application limit'" />
                                    <p class="mt-1 text-xs text-gray-500">Maximum number of applications to accept
                                        (leave empty for unlimited)</p>
                                    <InputError class="mt-2" :message="form.errors.job_application_limit" />
                                </div>
                            </div>

                            <!-- Job Posting Preview -->
                            <div class="mt-8 border rounded-lg p-6 bg-gray-50">
                                <h2 class="text-xl font-semibold mb-4">Job Posting Preview</h2>

                                <div class="space-y-4">
                                    <div>
                                        <h3 class="font-medium text-gray-700">Basic Job Details</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Job Title</p>
                                                <p>{{ form.job_title || 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Job Type</p>
                                                <p>{{ selectedJobTypeLabel || 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Experience Level</p>
                                                <p>{{ form.job_experience_level || 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Work Environment</p>
                                                <p>{{ selectedWorkEnvironmentLabel || 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Location</p>
                                                <p>{{ form.location || 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Vacancies</p>
                                                <p>{{ form.job_vacancies || 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Department</p>
                                                <p>{{ selectedDepartmentName || 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Programs</p>
                                                <p v-if="form.program_id.length > 0">
                                                    {{form.program_id.map(p => typeof p === 'object' ? p.name :
                                                        p).join(', ')}}
                                                </p>
                                                <p v-else>Not provided</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h3 class="font-medium text-gray-700">Salary Information</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                            <div v-if="form.is_negotiable">
                                                <p class="text-sm font-medium text-gray-500">Salary</p>
                                                <p>Negotiable</p>
                                            </div>
                                            <div v-else>
                                                <p class="text-sm font-medium text-gray-500">Salary Range</p>
                                                <p v-if="form.salary.job_min_salary && form.salary.job_max_salary">
                                                    ₱{{ form.salary.job_min_salary }} - ₱{{ form.salary.job_max_salary
                                                    }}
                                                </p>
                                                <p v-else>Not provided</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h3 class="font-medium text-gray-700">Description & Requirements</h3>
                                        <div class="mt-2">
                                            <p class="text-sm font-medium text-gray-500">Job Description</p>
                                            <div v-if="form.job_description" v-html="form.job_description"></div>
                                            <p v-else>Not provided</p>
                                        </div>
                                        <div class="mt-4">
                                            <p class="text-sm font-medium text-gray-500">Job Requirements</p>
                                            <div v-if="form.job_requirements" v-html="form.job_requirements"></div>
                                            <p v-else>Not provided</p>
                                        </div>
                                        <div class="mt-4">
                                            <p class="text-sm font-medium text-gray-500">Skills</p>
                                            <div v-if="form.skills.length > 0" class="flex flex-wrap gap-2 mt-1">
                                                <span v-for="(skill, index) in form.skills" :key="index"
                                                    class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                                    {{ skill }}
                                                </span>
                                            </div>
                                            <p v-else>Not provided</p>
                                        </div>
                                    </div>

                                    <div>
                                        <h3 class="font-medium text-gray-700">Other Information</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Posted By</p>
                                                <p>{{ form.posted_by || 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Application Deadline</p>
                                                <p>{{ form.job_deadline || 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Application Limit</p>
                                                <p>{{ form.job_application_limit || 'Unlimited' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between">
                                <PrimaryButton @click="goToPreviousStep" type="button"
                                    class="bg-gray-500 hover:bg-gray-600">
                                    Previous <i class="fas fa-chevron-left mr-1"></i>
                                </PrimaryButton>
                                <div class="flex space-x-3">
                                    <PrimaryButton type="submit" :disabled="form.processing">
                                        {{ form.processing ? 'Posting...' : 'Post Job' }} <i
                                            class="fas fa-chevron-right ml-1"></i>
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>
                    </template>
                </FormSection>
            </div>
        </Container>
    </AppLayout>

    <!-- Success Modal -->
    <Modal :modelValue="isSuccessModalOpen" @close="isSuccessModalOpen = false">
        <div class="p-6">
            <div class="flex items-center justify-center mb-4 bg-green-100 rounded-full w-12 h-12 mx-auto">
                <i class="fas fa-check text-green-500 text-xl"></i>
            </div>
            <h2 class="text-lg font-medium text-center text-gray-900 mb-2">Awaiting PESO Approval</h2>
            <p class="text-center text-gray-600 whitespace-pre-line">{{ successMessage }}</p>
            <div class="mt-6 flex justify-center">
                <PrimaryButton
                    @click="() => { isSuccessModalOpen = false; router.visit(route('company.jobs', { user: page.props.auth.user.id })); }">
                    OK
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>