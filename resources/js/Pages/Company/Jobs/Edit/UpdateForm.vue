<script setup>
import { ref, computed, watch, watchEffect } from 'vue';
import { useForm } from '@inertiajs/vue3';
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import MultiSelect from '@/Components/MultiSelect.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { useWorkEnvironmentValidation } from '@/Composables/useWorkEnvironmentValidation';
import { useSalaryValidation } from '@/Composables/useSalaryValidation';
import { useSkills } from '@/Composables/useSkills';

const props = defineProps({
    job: Object,
    departments: Array,
    jobTypes: Array,
    workEnvironments: Array,
    programs: Array,
    skills: Array,
    authUser: Object,
});

const postedBy = computed(() => {
    return props.authUser?.hr?.full_name ?? props.authUser?.name ?? '';
});

// Ensure all initial values are correct types
const form = useForm({
    job_title: props.job.job_title ?? '',
    location: props.job.location ?? '',
    program_id: Array.isArray(props.job.programs)
        ? props.job.programs.map(p => String(p.id))
        : [],
    department_id: props.job.department_id ? String(props.job.department_id) : '',
    job_vacancies: props.job.job_vacancies ?? '',
    salary: {
        salary_type: props.job.salary?.salary_type ?? props.job.salary_type ?? '',
        job_min_salary: props.job.salary?.job_min_salary ?? props.job.job_min_salary ?? '',
        job_max_salary: props.job.salary?.job_max_salary ?? props.job.job_max_salary ?? '',
    },
    is_negotiable: props.job.is_negotiable === 1 || props.job.is_negotiable === true,
    work_environment: props.job.work_environment
        ? String(props.job.work_environment)
        : (props.job.workEnvironments?.[0]?.id ? String(props.job.workEnvironments[0].id) : ''),
    job_type: props.job.job_type
        ? String(props.job.job_type)
        : (props.job.jobTypes?.[0]?.id ? String(props.job.jobTypes[0].id) : ''),
    job_experience_level: props.job.job_experience_level ?? '',
    job_description: props.job.job_description ?? '',
    skills: Array.isArray(props.job.skills)
        ? props.job.skills.map(s => typeof s === 'string' ? s : s.name)
        : [],
    job_requirements: props.job.job_requirements ?? '',
    job_application_limit: props.job.job_application_limit ?? '',
    job_deadline: props.job.job_deadline ?? '',
    posted_by: props.job.posted_by ?? postedBy.value,
    error: {}
});

watchEffect(() => {
    form.posted_by = postedBy.value;
});

watch(() => form.department_id, (val) => {
    if (typeof val === 'string' && val !== '') {
        form.department_id = String(val);
    }
});

const selectedJobTypeLabel = computed(() => {
    const found = props.jobTypes.find(jt => String(jt.id) === String(form.job_type));
    return found ? found.type : 'Not provided';
});
const selectedWorkEnvironmentLabel = computed(() => {
    const found = props.workEnvironments.find(env => String(env.id) === String(form.work_environment));
    return found ? env.environment_type : 'Not provided';
});
const selectedDepartmentName = computed(() => {
    if (!form.department_id) return 'Not provided';
    const dep = props.departments.find(d => String(d.id) === String(form.department_id));
    return dep ? dep.department_name : 'Not provided';
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

const today = new Date();

watch(() => form.job_vacancies, (vacancies) => {
    if (form.job_application_limit && Number(form.job_application_limit) < Number(vacancies)) {
        form.job_application_limit = vacancies;
    }
});

const { validateLocation } = useWorkEnvironmentValidation(form);
const { salaryError, salaryWarning, validateSalary } = useSalaryValidation(form);
const { newSkill, showSuggestions, filteredSkills, addSkill, removeSkill, selectSuggestion, filterSuggestions } = useSkills(form, props.skills ?? []);

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
    if (!form.department_id) fields.push('Department');
    if (form.work_environment != 2 && !form.location) fields.push('Job Location');
    if (!form.salary.salary_type) fields.push('Salary Type');
    if (!form.is_negotiable && (!form.salary.job_min_salary || !form.salary.job_max_salary)) fields.push('Salary Range');
    if (!form.job_description) fields.push('Job Description');
    if (!form.job_requirements) fields.push('Job Requirements');
    if (!form.posted_by) fields.push('Posted By');
    if (!form.job_deadline) fields.push('Application Deadline');
    return fields;
}

console.log('Programs:', props.programs);

const updateJob = () => {
    missingFields.value = getMissingFields();
    if (missingFields.value.length > 0) {
        showMissingFieldsModal.value = true;
        return;
    }

    form.program_id = form.program_id.map(program => program.id ?? program);

    if (!validateLocation()) {
        return;
    }

    form.put(route('company.jobs.update', { job: props.job.id }), {
        preserveScroll: true
    });
};
</script>

<template>
    <FormSection @submitted="updateJob">
        <template #title>
            Edit your Job
        </template>
        <template #description>
            Update the details of your job posting.
        </template>
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
                    <InputLabel for="job_title">
                        Job Title <span class="text-pink-500">*</span>
                    </InputLabel>
                    <TextInput id="job_title" type="text" class="mt-1 block w-full"
                        v-model="form.job_title" required autofocus />
                    <InputError class="mt-2" :message="form.errors.job_title" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="program_id">
                            Program <span class="text-pink-500">*</span>
                        </InputLabel>
                        <MultiSelect id="program_id" class="mt-1 block w-full" v-model="form.program_id"
                            :options="props.programs" label="name" track-by="id" :searchable="true"
                            :multiple="true" placeholder="Select programs" />
                        <InputError class="mt-2" :message="form.errors.program_id" />
                    </div>
                    <div>
                        <InputLabel for="job_type">
                            Job Type <span class="text-pink-500">*</span>
                        </InputLabel>
                        <select id="job_type"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            v-model="form.job_type" required>
                            <option value="">Select Job Type</option>
                            <option v-for="type in props.jobTypes" :key="type.id" :value="String(type.id)">
                                {{ type.type }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.job_type" />
                    </div>
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
                    <div>
                        <InputLabel for="job_vacancies">
                            Number of Vacancies <span class="text-pink-500">*</span>
                        </InputLabel>
                        <TextInput id="job_vacancies" type="number" class="mt-1 block w-full"
                            v-model="form.job_vacancies" required min="1" />
                        <InputError class="mt-2" :message="form.errors.job_vacancies" />
                    </div>
                    <div>
                        <InputLabel for="work_environment">
                            Work Environment <span class="text-pink-500">*</span>
                        </InputLabel>
                        <select id="work_environment"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            v-model="form.work_environment" required>
                            <option value="">Select Work Environment</option>
                            <option v-for="environment in props.workEnvironments" :key="environment.id"
                                :value="String(environment.id)">
                                {{ environment.environment_type }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.work_environment" />
                    </div>
                    <div v-if="props.departments && props.departments.length">
                        <InputLabel for="department_id">
                            Department <span class="text-pink-500">*</span>
                        </InputLabel>
                        <select id="department_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            v-model="form.department_id" required>
                            <option value="">Select Department</option>
                            <option v-for="dep in props.departments" :key="dep.id" :value="String(dep.id)">
                                {{ dep.department_name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.department_id" />
                    </div>
                    
                </div>
                <div>
                    <InputLabel for="location">
                        Job Location <span class="text-pink-500" v-if="form.work_environment != 2">*</span>
                    </InputLabel>
                    <TextInput id="location" type="text" class="mt-1 block w-full"
                        v-model="form.location" :disabled="form.work_environment == 2"
                        :required="form.work_environment != 2"
                        :placeholder="form.work_environment == 2 ? 'Remote job — no location needed' : 'Enter job location'" />
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
                    <div>
                        <InputLabel for="job_salary_type">
                            Salary Type <span class="text-pink-500">*</span>
                        </InputLabel>
                        <select id="salary_type"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            v-model="form.salary.salary_type" @change="validateSalary">
                            <option value="">Select Salary Type</option>
                            <option value="monthly">Monthly</option>
                            <option value="weekly">Weekly</option>
                            <option value="hourly">Hourly</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors['salary.salary_type']" />
                    </div>
                    <div class="flex items-center mt-6">
                        <input id="is_negotiable" type="checkbox"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            v-model="form.is_negotiable" @change="validateSalary" />
                        <label for="is_negotiable" class="ml-2 block text-sm text-gray-900">
                            Salary is negotiable
                        </label>
                    </div>
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
                                v-model="form.salary.job_min_salary" :disabled="form.is_negotiable"
                                @input="validateSalary" />
                        </div>
                    </div>
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
                                v-model="form.salary.job_max_salary" :disabled="form.is_negotiable"
                                @input="validateSalary" />
                        </div>
                    </div>
                </div>
                <div v-if="salaryError" class="text-red-500 text-sm">
                    {{ salaryError }}
                </div>
                <div v-if="salaryWarning" class="text-yellow-600 text-sm">
                    {{ salaryWarning }}
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
                <div>
                    <InputLabel for="skills">
                        Skills  <span class="text-pink-500">*</span>
                    </InputLabel>
                    <div class="relative">
                        <div class="flex mt-1">
                            <TextInput id="skills" type="text" class="block w-full" v-model="newSkill"
                                placeholder="Enter a skill" @input="filterSuggestions"
                                @keyup.enter.prevent="addSkill" @focus="showSuggestions = true" />
                            <PrimaryButton type="button" @click="addSkill" class="ml-2">
                                Add
                            </PrimaryButton>
                        </div>
                        <div v-if="showSuggestions && filteredSkills.length > 0"
                            class="absolute z-10 bg-white border mt-1 w-full rounded shadow max-h-40 overflow-auto">
                            <div v-for="(suggestion, i) in filteredSkills" :key="i"
                                @mousedown.prevent="selectSuggestion(suggestion)"
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
                    <div>
                        <InputLabel for="posted_by">
                            Posted By <span class="text-pink-500">*</span>
                        </InputLabel>
                        <TextInput id="posted_by" type="text" class="mt-1 block w-full"
                            v-model="form.posted_by" required />
                        <InputError class="mt-2" :message="form.errors.posted_by" />
                    </div>
                    <div>
                        <InputLabel for="job_deadline">
                            Application Deadline <span class="text-pink-500">*</span>
                        </InputLabel>
                        <Datepicker v-model="form.job_deadline" :enable-time-picker="false"
                            input-class-name="w-full p-2 border rounded-lg mt-2" :min-date="today"
                            placeholder="Select date" required />
                        <InputError class="mt-2" :message="form.errors.job_deadline" />
                    </div>
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
                <div class="flex justify-between">
                    <PrimaryButton @click="goToPreviousStep" type="button"
                        class="bg-gray-500 hover:bg-gray-600">
                        Previous <i class="fas fa-chevron-left mr-1"></i>
                    </PrimaryButton>
                    <div class="flex space-x-3">
                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Save Changes' }} <i
                                class="fas fa-save ml-1"></i>
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </template>
    </FormSection>

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
                    Please fill in the following required fields before saving your job:
                </p>
                <ul class="list-disc pl-6 space-y-1">
                    <li v-for="field in missingFields" :key="field" class="text-red-600 font-medium">
                        <i class="fas fa-asterisk text-xs mr-1"></i>{{ field }}
                    </li>
                </ul>
            </div>
        </template>
        <template #footer>
            <PrimaryButton
                class="bg-red-600 hover:bg-red-700 border-none"
                @click="showMissingFieldsModal = false"
            >
                <i class="fas fa-times mr-2"></i> Close
            </PrimaryButton>
        </template>
    </Modal>
</template>