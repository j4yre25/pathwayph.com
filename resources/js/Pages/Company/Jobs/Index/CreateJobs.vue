<script setup>
import { router, usePage, useForm } from '@inertiajs/vue3'
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
import { useSectorCategories } from '@/Composables/useSectorCategories';
import { useWorkEnvironmentValidation } from '@/Composables/useWorkEnvironmentValidation';
import { useSalaryValidation } from '@/Composables/useSalaryValidation';
import { useSkills } from '@/Composables/useSkills';

const page = usePage()

const props = defineProps({
    jobs: Array,
    sectors: Array,
    categories: Array,
    programs: Array,
    authUser: Object,
    skills: Array, 
})

const programs = props.programs;

console.log('User ID:', page.props);
console.log('Programs:', programs);
console.log('Auth User:', props.authUser);
console.log('Fullname:', props.authUser.hr.full_name);

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

const form = useForm({
    job_title: '',
    location: '',
    program_id: [],
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
    sector: '',
    category: '',
    job_requirements: '',
    job_application_limit: '',
    job_deadline: '',
    posted_by: postedBy.value,
    error: {}
});

watchEffect(() => {
    form.posted_by = postedBy.value;
});

// Tabs setup
const currentStep = ref('job-details');

const goToPreviousStep = () => {
    if (currentStep.value === 'salary-info') currentStep.value = 'job-details';
    else if (currentStep.value === 'description') currentStep.value = 'salary-info';
    else if (currentStep.value === 'review') currentStep.value = 'description';
};

const goToNextStep = () => {
    if (currentStep.value === 'job-details') currentStep.value = 'salary-info';
    else if (currentStep.value === 'salary-info') currentStep.value = 'description';
    else if (currentStep.value === 'description') currentStep.value = 'review';
};


const expirationDate = ref(null)
const today = new Date()

const { availableCategories } = useSectorCategories(form, props.sectors);
const { validateLocation } = useWorkEnvironmentValidation(form);
const { salaryError, salaryWarning, validateSalary } = useSalaryValidation(form);
const { newSkill, isFocused, filteredSkills, addSkill, removeSkill, selectSuggestion } = useSkills(form, props.skills);

// Extract selected program IDs
const extractProgramIds = () => {
    form.program_id = form.program_id.map(program => program.id);
};

const createJob = () => {
    console.log('Form data:', form);

    form.program_id = form.program_id.map(program => program.id ?? program);

    if (!validateLocation()) {
        console.log('Location validation failed.');
        return;
    }

    form.post(route('company.jobs.store', { user: page.props.auth.user.id }), {
        onSuccess: () => {
            router.visit(route('company.jobs', { user: page.props.auth.user.id }));
        },
        onError: (errors) => {
            console.log('Validation errors:', errors);
        }
    });

    console.log('Route:', route('company.jobs.store', { user: page.props.auth.user.id }));
}
</script>



<template>
    <AppLayout title="Post a New Job">
        <Container class="py-15">
            <div class="mt-8">
                <FormSection @submitted="createJob()">
                    <template #form>
                        <h1 class="text-2xl font-bold ">Create New Job Posting</h1>
                        <p class="text-sm text-gray-500 mb-6">Please fill in the details below to create a new job
                            posting.</p>
                        <!-- Progress Tabs -->
                        <div class="flex justify-center mb-8 border-b">
                            <div @click="currentStep = 'job-details'" :class="[
                                'px-4 py-2 cursor-pointer',
                                currentStep === 'job-details' ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500'
                            ]">
                                Basic Job Details
                            </div>
                            <div @click="currentStep === 'job-details' ? null : currentStep = 'salary-info'" :class="[
                                'px-4 py-2 cursor-pointer',
                                currentStep === 'salary-info' ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500',
                                currentStep === 'job-details' ? 'opacity-50 cursor-not-allowed' : ''
                            ]">
                                Salary Information
                            </div>
                            <div @click="currentStep === 'job-details' || currentStep === 'salary-info' ? null : currentStep = 'description'"
                                :class="[
                                    'px-4 py-2 cursor-pointer',
                                    currentStep === 'description' ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500',
                                    currentStep === 'job-details' || currentStep === 'salary-info' ? 'opacity-50 cursor-not-allowed' : ''
                                ]">
                                Description & Requirements
                            </div>
                            <div @click="currentStep === 'job-details' || currentStep === 'salary-info' || currentStep === 'description' ? null : currentStep = 'review'"
                                :class="[
                                    'px-4 py-2 cursor-pointer',
                                    currentStep === 'review' ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500',
                                    currentStep === 'job-details' || currentStep === 'salary-info' || currentStep === 'description' ? 'opacity-50 cursor-not-allowed' : ''
                                ]">
                                Other Information
                            </div>
                        </div>

                        <div class="w-full border-t border-gray-300 mb-6"></div>

                        <!-- Basic Job Details Tab -->
                        <div v-if="currentStep === 'job-details'" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Job Title -->
                                <div>
                                    <InputLabel for="job_title" value="Job Title" />
                                    <TextInput id="job_title" type="text" class="mt-1 block w-full"
                                        v-model="form.job_title" required autofocus />
                                    <InputError class="mt-2" :message="form.errors.job_title" />
                                </div>

                                <!-- Program Selection -->
                                <div>
                                    <InputLabel for="program_id" value="Program" />
                                    <MultiSelect id="program_id" class="mt-1 block w-full" v-model="form.program_id"
                                        :options="props.programs" label="name" track-by="id" :searchable="true"
                                        :multiple="true" placeholder="Select programs" />
                                    <InputError class="mt-2" :message="form.errors.program_id" />
                                </div>

                                <!-- Job Type -->
                                <div>
                                    <InputLabel for="job_type" value="Job Type" />
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
                                    <InputLabel for="job_experience_level" value="Experience Level" />
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

                                <!-- Sector -->
                                <div>
                                    <InputLabel for="sector" value="Sector" />
                                    <select id="sector"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.sector" required>
                                        <option value="">Select Sector</option>
                                        <option v-for="sector in props.sectors" :key="sector.id" :value="sector.id">
                                            {{ sector.name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.sector" />
                                </div>

                                <!-- Category -->
                                <div>
                                    <InputLabel for="category" value="Category" />
                                    <select id="category"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.category" required :disabled="!form.sector">
                                        <option value="">Select Category</option>
                                        <option v-for="category in availableCategories" :key="category.id"
                                            :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.category" />
                                </div>

                                <!-- Number of Vacancies -->
                                <div>
                                    <InputLabel for="job_vacancies" value="Number of Vacancies" />
                                    <TextInput id="job_vacancies" type="number" class="mt-1 block w-full"
                                        v-model="form.job_vacancies" required min="1" />
                                    <InputError class="mt-2" :message="form.errors.job_vacancies" />
                                </div>

                                <!-- Work Environment -->
                                <div>
                                    <InputLabel for="job_work_environment" value="Work Environment" />
                                    <select id="work_environment"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.work_environment" required>
                                        <option v-for="environment in workEnvironments" :key="environment.id" :value="environment.id">
                                            {{ environment.environment_type }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.job_work_environment" />
                                </div>

                                <!-- Job Location (disabled if Remote is selected) -->
                                <div>
                                    <InputLabel for="location" value="Job Location" />
                                    <TextInput
                                        id="location"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.location"
                                        :disabled="form.work_environment == 2"
                                        :required="form.work_environment != 2"
                                        :placeholder="form.work_environment == 2 ? 'Remote job — no location needed' : 'Enter job location'"/>
                                    <InputError class="mt-2" :message="form.errors.location" />
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <PrimaryButton @click="goToNextStep" type="button">
                                    Next
                                </PrimaryButton>
                            </div>
                        </div>


                        <!-- Salary Information Tab -->
                        <div v-if="currentStep === 'salary-info'" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Salary Type -->
                                <div>
                                    <InputLabel for="job_salary_type" value="Salary Type" />
                                    <select
                                        id="salary_type"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.salary.salary_type"
                                        @change="validateSalary">
                                            <option value="">Select Salary Type</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="hourly">Hourly</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors['salary.salary_type']" />
                                </div>

                                <!-- Negotiable Salary Checkbox -->
                                <div class="flex items-center mt-6">
                                    <input
                                        id="is_negotiable"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        v-model="form.is_negotiable"
                                        @change="validateSalary"/>
                                    <label for="is_negotiable" class="ml-2 block text-sm text-gray-900">
                                        Salary is negotiable
                                    </label>
                                </div>

                                <!-- Minimum Salary -->
                                <div>
                                    <InputLabel for="job_min_salary" value="Minimum Salary" />
                                    <div class="relative mt-1">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">₱</span>
                                        </div>
                                        <TextInput
                                            id="job_min_salary"
                                            type="number"
                                            class="pl-7 block w-full"
                                            v-model="form.salary.job_min_salary"
                                            :disabled="form.is_negotiable"
                                            @input="validateSalary"/>
                                    </div>
                                </div>

                                <!-- Maximum Salary -->
                                <div>
                                    <InputLabel for="job_max_salary" value="Maximum Salary" />
                                    <div class="relative mt-1">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">₱</span>
                                        </div>
                                        <TextInput
                                            id="max_salary"
                                            type="number"
                                            class="pl-7 block w-full"
                                            v-model="form.salary.job_max_salary"
                                            :disabled="form.is_negotiable"
                                            @input="validateSalary"/>
                                    </div>
                                </div>
                            </div>

                            <!-- Salary Error Message -->
                            <div v-if="salaryError" class="text-red-500 text-sm">
                                {{ salaryError }}
                            </div>

                            <!-- Optional Salary Warning Message -->
                            <div v-if="salaryWarning" class="text-yellow-600 text-sm">
                                {{ salaryWarning }}
                            </div>

                            <div class="flex justify-between">
                                <PrimaryButton @click="goToPreviousStep" type="button" class="bg-gray-500 hover:bg-gray-600">
                                    Previous
                                </PrimaryButton>
                                <PrimaryButton @click="goToNextStep" type="button">
                                    Next
                                </PrimaryButton>
                            </div>
                        </div>


                        <!-- Description & Requirements Tab -->
                        <div v-if="currentStep === 'description'" class="space-y-6">
                            <!-- Job Description -->
                            <div>
                                <InputLabel for="job_description" value="Job Description" />
                                <RichTextEditor id="job_description" v-model="form.job_description"
                                    class="mt-1 block w-full" placeholder="Enter job description" />
                                <p class="mt-1 text-xs text-gray-500">Provide a detailed description of the job
                                    responsibilities and duties.</p>
                                <InputError class="mt-2" :message="form.errors.job_description" />
                            </div>

                            <!-- Job Requirements -->
                            <div>
                                <InputLabel for="job_requirements" value="Job Requirements" />
                                <RichTextEditor id="job_requirements" v-model="form.job_requirements"
                                    class="mt-1 block w-full" placeholder="Enter job requirements" />
                                <p class="mt-1 text-xs text-gray-500">List the qualifications, skills, and experience
                                    required for this position.</p>
                                <InputError class="mt-2" :message="form.errors.job_requirements" />
                            </div>

                            <!-- Skills -->
                            <div>
                                <InputLabel for="skills" value="Skills" />

                                <div class="relative">
                                    <!-- Input Field -->
                                    <div class="flex mt-1">
                                        <TextInput
                                            id="skills"
                                            type="text"
                                            class="block w-full"
                                            v-model="newSkill"
                                            placeholder="Enter a skill"
                                            @input="filterSuggestions"
                                            @keyup.enter.prevent="addSkill"
                                            @focus="showSuggestions = true"
                                        />
                                        <PrimaryButton type="button" @click="addSkill" class="ml-2">
                                            Add
                                        </PrimaryButton>
                                    </div>

                                    <!-- Suggestions Dropdown -->
                                    <div v-if="showSuggestions && filteredSkills.length > 0" class="absolute z-10 bg-white border mt-1 w-full rounded shadow max-h-40 overflow-auto">
                                        <div
                                            v-for="(suggestion, i) in filteredSkills"
                                            :key="i"
                                            @click="selectSuggestion(suggestion)"
                                            class="px-3 py-2 hover:bg-indigo-100 cursor-pointer"
                                        >
                                            {{ suggestion }}
                                        </div>
                                    </div>
                                </div>

                                <p class="mt-1 text-xs text-gray-500">
                                    Add relevant skills required for this position (e.g., JavaScript, Project Management).
                                </p>

                                <!-- Skills List -->
                                <div v-if="form.skills.length > 0" class="mt-3 flex flex-wrap gap-2">
                                    <div
                                        v-for="(skill, index) in form.skills"
                                        :key="index"
                                        class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full flex items-center"
                                    >
                                        <span>{{ skill }}</span>
                                        <button type="button" @click="removeSkill(index)" class="ml-2 text-blue-600 hover:text-blue-800 focus:outline-none">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>

                                <InputError class="mt-2" :message="form.errors.skills" />
                            </div>


                            <div class="flex justify-between">
                                <PrimaryButton @click="goToPreviousStep" type="button"
                                    class="bg-gray-500 hover:bg-gray-600">
                                    Previous
                                </PrimaryButton>
                                <PrimaryButton @click="goToNextStep" type="button">
                                    Next
                                </PrimaryButton>
                            </div>
                        </div>

                        <!-- Other Information Tab -->
                        <div v-if="currentStep === 'review'" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Posted By -->
                                <div>
                                    <InputLabel for="posted_by" value="Posted By" />
                                    <TextInput id="posted_by" type="text" class="mt-1 block w-full"
                                        v-model="form.posted_by" required />
                                    <InputError class="mt-2" :message="form.errors.posted_by" />
                                </div>

                                <!-- Application Deadline -->
                                <div>
                                    <InputLabel for="job_deadline" value="Application Deadline" />
                                    <Datepicker v-model="form.job_deadline" :enable-time-picker="false"
                                        input-class-name="w-full p-2 border rounded-lg mt-2" :min-date="today"
                                        placeholder="Select date" required />
                                    <InputError class="mt-2" :message="form.errors.job_deadline" />
                                </div>

                                <!-- Application Limit -->
                                <div>
                                    <InputLabel for="job_application_limit" value="Application Limit" />
                                    <TextInput id="job_application_limit" type="number" class="mt-1 block w-full"
                                        v-model="form.job_application_limit" min="1" />
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
                                                <p>{{ form.job_type || 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Experience Level</p>
                                                <p>{{ form.job_experience_level || 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Work Environment</p>
                                                <p>{{ form.work_environment || 'Not provided' }}</p>
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
                                    Previous
                                </PrimaryButton>
                                <div class="flex space-x-3">
                                    <PrimaryButton type="submit" :disabled="form.processing">
                                        {{ form.processing ? 'Posting...' : 'Post Job' }}
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>
                    </template>
                </FormSection>
            </div>
        </Container>
    </AppLayout>
</template>
