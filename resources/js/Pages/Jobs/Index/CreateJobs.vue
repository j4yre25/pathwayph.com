<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, usePage, useForm, Link } from '@inertiajs/vue3'
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
import { ref, watch, computed, watchEffect, onMounted } from 'vue';

const page = usePage()

const props = defineProps ({
    jobs: Array,
    sectors: Array,
    categories: Array,
    programs: Array,
    authUser: Object,
})

const programs = props.programs;

console.log('User ID:', page.props);
console.log('Programs:', programs);
console.log('Auth User:', props.authUser);
console.log('Fullname:', props.authUser.hr.full_name);

const postedBy = computed(() => {
    return props.authUser?.hr?.full_name ?? props.authUser?.name ?? '';
});

const form = useForm({
    job_title: '', 
    job_location: '',
    program_id: [],
    job_vacancies: '',
    job_salary_type: '',
    job_min_salary: '',
    job_max_salary: '',
    is_negotiable: false,
    job_work_environment: '',
    job_employment_type: '',
    job_experience_level: '',
    job_description: '',
    related_skills: [],
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
// Save as draft functionality
const saveDraft = () => {
  // Implement save as draft functionality
  console.log('Saving as draft:', form);
};


const expirationDate = ref(null)
const today = new Date()



// This is for the categories & sector dropdown
const availableCategories = ref([])

watch(() => form.sector, (newSector) => {
    if (newSector) {
        const selectedSector = props.sectors.find(sector => sector.id === newSector);
        availableCategories.value = selectedSector ? selectedSector.categories : [];
        form.category = '';
    }
    else {
        availableCategories.value = [];
    }
});


// Salary Setup
const salaryError = ref('');

const validateSalary = () => {
    if (form.is_negotiable) {
        salaryError.value = '';
        return;
    }
    
    const min = parseInt(form.job_min_salary);
    const max = parseInt(form.job_max_salary);

    if (isNaN(min) || min < 5000 || min > 100000) {
        salaryError.value = 'Minimum salary must be between ₱5,000 and ₱100,000';
    } else if (isNaN(max) || max < 5000 || max > 100000) {
        salaryError.value = 'Maximum salary must be between ₱5,000 and ₱100,000';
    } else if (min > max) {
        salaryError.value = 'Minimum salary cannot be greater than maximum salary';
    } else {
        salaryError.value = '';
    }
};

watch(() => form.is_negotiable, () => {
    validateSalary();
});

console.log(form.program_id); 

// Skill setup
const newSkill = ref('');


const addSkill = () => {
    if (newSkill.value.trim() !== '') {
        form.related_skills.push(newSkill.value.trim());
        newSkill.value = '';
    }
};

const removeSkill = (index) => {
    form.related_skills.splice(index, 1);
};

const extractProgramIds = () => {
    // Extract only the ids from the selected programs
    form.program_id = form.program_id.map(program => program.id);
};

const createJob = () => {
    console.log('Form data:', form);
  
    form.program_id = form.program_id.map(program => program.id ?? program);

    form.post(route('company.jobs.store', { user: page.props.auth.user.id }), {
        onSuccess: () => {
            // form.reset();
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
   <AppLayout title ="Post a New Job">
    <template #header>
        <div>
            <div class="flex items-center">
                <Link :href="route('company.jobs', { user: page.props.auth.user.id })" class="text-gray-500 hover:text-gray-700 mr-3">
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

        <Container class="py-15">
            <div class="mt-8">
                <FormSection @submitted="createJob()">
                    <template #form>

                        <!-- Progress Tabs -->
                         <div class="flex justify-center mb-8 border-b">
                            <div @click="currentStep = 'job-details'" 
                            :class="['px-4 py-2 cursor-pointer flex flex-col items-center', 
                            currentStep === 'job-details' ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500']">
                                <i class="fas fa-briefcase text-xl mb-1"></i> 
                                <span>Basic Job Details</span>
                            </div>
                            <div @click="currentStep === 'job-details' ? null : currentStep = 'salary-info'" 
                                :class="['px-4 py-2 cursor-pointer flex flex-col items-center', 
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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Job Title -->
                                <div>
                                    <InputLabel for="job_title" value="Job Title" />
                                        <TextInput
                                        id="job_title"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.job_title"
                                        required
                                        autofocus/>
                                    <InputError class="mt-2" :message="form.errors.job_title" />
                                </div>
                                
                                <!-- Program Selection -->
                                <div>
                                    <InputLabel for="program_id" value="Program" />
                                        <MultiSelect
                                            id="program_id"
                                            class="mt-1 block w-full"
                                            v-model="form.program_id"
                                            :options="props.programs"
                                            label="name"
                                            track-by="id"
                                            :searchable="true"
                                            :multiple="true"
                                            placeholder="Select programs"/>
                                    <InputError class="mt-2" :message="form.errors.program_id" />
                                </div>
                                
                                <!-- Job Type -->
                                <div>
                                    <InputLabel for="job_employment_type" value="Job Type" />
                                    <select
                                        id="job_employment_type"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.job_employment_type"
                                        required>
                                        <option value="">Select Job Type</option>
                                        <option value="Full-time">Full-time</option>
                                        <option value="Part-time">Part-time</option>
                                        <option value="Contract">Contract</option>
                                        <option value="Freelance">Freelance</option>
                                        <option value="Internship">Internship</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.job_employment_type" />
                                </div>
                                
                                <!-- Experience Level -->
                                <div>
                                    <InputLabel for="job_experience_level" value="Experience Level" />
                                    <select
                                        id="job_experience_level"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.job_experience_level"
                                        required>
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
                                    <select
                                    id="sector"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.sector"
                                    required>
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
                                    <select
                                    id="category"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.category"
                                    required
                                    :disabled="!form.sector">
                                        <option value="">Select Category</option>
                                        <option v-for="category in availableCategories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                        </select>
                                    <InputError class="mt-2" :message="form.errors.category" />
                                </div>
                                
                                <!-- Number of Vacancies -->
                                <div>
                                    <InputLabel for="job_vacancies" value="Number of Vacancies" />
                                        <TextInput
                                        id="job_vacancies"
                                        type="number"
                                        class="mt-1 block w-full"
                                        v-model="form.job_vacancies"
                                        required
                                        min="1"/>
                                    <InputError class="mt-2" :message="form.errors.job_vacancies" />
                                </div>
                                
                                <!-- Work Environment -->
                                <div>
                                    <InputLabel for="job_work_environment" value="Work Environment" />
                                    <select
                                    id="job_work_environment"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.job_work_environment"
                                    required>
                                        <option value="">Select Work Environment</option>
                                        <option value="On-site">On-site</option>
                                        <option value="Remote">Remote</option>
                                        <option value="Hybrid">Hybrid</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.job_work_environment" />
                                </div>
                                
                                <!-- Job Location -->
                                <div>
                                    <InputLabel for="job_location" value="Job Location" />
                                        <TextInput
                                        id="job_location"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.job_location"
                                        required/>
                                    <InputError class="mt-2" :message="form.errors.job_location" />
                                </div>
                            </div>
                            
                            <div class="flex justify-between mt-6 py-4 px-6 bg-gray-100 rounded-b-lg">
                                <div></div>
                                <div class="flex space-x-3">
                                    <PrimaryButton @click="saveDraft" type="button" class="bg-gray-500 hover:bg-gray-600">
                                        Save as Draft
                                    </PrimaryButton>
                                    <PrimaryButton @click="goToNextStep" type="button">
                                        Next <i class="fas fa-chevron-right ml-1"></i>
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>
                            
                                
                        <!-- Salary Information Tab -->
                        <div v-if="currentStep === 'salary-info'" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Salary Type -->
                                <div>
                                    <InputLabel for="job_salary_type" value="Salary Type" />
                                    <select
                                    id="job_salary_type"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.job_salary_type">
                                        <option value="">Select Salary Type</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="hourly">Hourly</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.job_salary_type" />
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
                                            v-model="form.job_min_salary"
                                            :disabled="form.is_negotiable"
                                            @input="validateSalary"
                                            min="5000"
                                            max="100000"/>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Enter amount between ₱5,000 and ₱100,000</p>
                                    <InputError class="mt-2" :message="form.errors.job_min_salary" />
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
                                            v-model="form.max_salary"
                                            :disabled="form.is_negotiable"
                                            @input="validateSalary"
                                            min="5000"
                                            max="100000"/>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Enter amount between ₱5,000 and ₱100,000</p>
                                    <InputError class="mt-2" :message="form.errors.max_salary" />
                                </div>
                            </div>
                            
                            <!-- Salary Error Message -->
                            <div v-if="salaryError" class="text-red-500 text-sm">
                                {{ salaryError }}
                            </div>
                                
                            <div class="flex justify-between mt-6 py-4 px-6 bg-gray-100 rounded-b-lg">
                                <PrimaryButton @click="goToPreviousStep" type="button" class="bg-gray-500 hover:bg-gray-600">
                                    <i class="fas fa-chevron-left mr-1"></i> Previous
                                </PrimaryButton>
                                <div class="flex space-x-3">
                                    <PrimaryButton @click="saveDraft" type="button" class="bg-gray-500 hover:bg-gray-600">
                                        Save as Draft
                                    </PrimaryButton>
                                    <PrimaryButton @click="goToNextStep" type="button">
                                        Next <i class="fas fa-chevron-right ml-1"></i>
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>

                        <!-- Description & Requirements Tab -->
                        <div v-if="currentStep === 'description'" class="space-y-6">
                            <!-- Job Description -->
                            <div>
                                <InputLabel for="job_description" value="Job Description" />
                                <RichTextEditor
                                    id="job_description"
                                    v-model="form.job_description"
                                    class="mt-1 block w-full"
                                    placeholder="Enter job description"/>
                                <p class="mt-1 text-xs text-gray-500">Provide a detailed description of the job responsibilities and duties.</p>
                                <InputError class="mt-2" :message="form.errors.job_description" />
                            </div>
                            
                            <!-- Job Requirements -->
                            <div>
                                <InputLabel for="job_requirements" value="Job Requirements" />
                                <RichTextEditor
                                    id="job_requirements"
                                    v-model="form.job_requirements"
                                    class="mt-1 block w-full"
                                    placeholder="Enter job requirements"/>
                                <p class="mt-1 text-xs text-gray-500">List the qualifications, skills, and experience required for this position.</p>
                                <InputError class="mt-2" :message="form.errors.job_requirements" />
                            </div>
                            
                            <!-- Skills -->
                            <div>
                                <InputLabel for="related_skills" value="Skills" />
                                <div class="flex mt-1">
                                    <TextInput
                                    id="related_skills"
                                    type="text"
                                    class="block w-full"
                                    v-model="newSkill"
                                    placeholder="Enter a skill"
                                    @keyup.enter.prevent="addSkill"/>
                                    <PrimaryButton type="button" @click="addSkill" class="ml-2">
                                        Add
                                    </PrimaryButton>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Add relevant skills required for this position (e.g., JavaScript, Project Management).</p>
                            
                                <!-- Skills List -->
                                <div v-if="form.related_skills.length > 0" class="mt-3 flex flex-wrap gap-2">
                                    <div 
                                        v-for="(skill, index) in form.related_skills" 
                                        :key="index"
                                        class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full flex items-center">
                                        <span>{{ skill }}</span>
                                        <button 
                                            type="button" 
                                            @click="removeSkill(index)" 
                                            class="ml-2 text-blue-600 hover:text-blue-800 focus:outline-none">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.related_skills" />
                            </div>        
                            
                            <div class="flex justify-between mt-6 py-4 px-6 bg-gray-100 rounded-b-lg">
                                <PrimaryButton @click="goToPreviousStep" type="button" class="bg-gray-500 hover:bg-gray-600">
                                    <i class="fas fa-chevron-left mr-1"></i> Previous
                                </PrimaryButton>
                                <div class="flex space-x-3">
                                    <PrimaryButton @click="saveDraft" type="button" class="bg-gray-500 hover:bg-gray-600">
                                        Save as Draft
                                    </PrimaryButton>
                                    <PrimaryButton @click="goToNextStep" type="button">
                                        Next <i class="fas fa-chevron-right ml-1"></i>
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>

                        <!-- Other Information Tab -->
                        <div v-if="currentStep === 'review'" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                 <!-- Posted By -->
                                <div>
                                    <InputLabel for="posted_by" value="Posted By" />
                                    <TextInput
                                    id="posted_by"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.posted_by"
                                    required
                                    />
                                    <InputError class="mt-2" :message="form.errors.posted_by" />
                                </div>
                            
                                <!-- Application Deadline -->
                                <div>
                                    <InputLabel for="job_deadline" value="Application Deadline" />
                                <Datepicker 
                                        v-model="form.job_deadline" 
                                        :enable-time-picker="false"
                                        input-class-name="w-full p-2 border rounded-lg mt-2"
                                        :min-date= "today"
                                        placeholder="Select date"
                                        required />
                                    <InputError class="mt-2" :message="form.errors.job_deadline" />
                                </div>
                            
                                <!-- Application Limit -->
                                <div>
                                    <InputLabel for="job_application_limit" value="Application Limit" />
                                    <TextInput
                                    id="job_application_limit"
                                    type="number"
                                    class="mt-1 block w-full"
                                    v-model="form.job_application_limit"
                                    min="1"/>
                                    <p class="mt-1 text-xs text-gray-500">Maximum number of applications to accept (leave empty for unlimited)</p>
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
                                                <p>{{ form.job_employement_type || 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Experience Level</p>
                                                <p>{{ form.job_experience_level || 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Work Environment</p>
                                                <p>{{ form.job_work_environment || 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Location</p>
                                                <p>{{ form.job_location || 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Vacancies</p>
                                                <p>{{ form.job_vacancies || 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Programs</p>
                                                <p v-if="form.program_id.length > 0">
                                                    {{ form.program_id.map(p => typeof p === 'object' ? p.name : p).join(', ') }}
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
                                                <p v-if="form.job_min_salary && form.job_max_salary">
                                                    ₱{{ form.job_min_salary }} - ₱{{ form.job_max_salary }}
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
                                            <div v-if="form.related_skills.length > 0" class="flex flex-wrap gap-2 mt-1">
                                            <span 
                                                v-for="(skill, index) in form.related_skills" 
                                                :key="index"
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
                            
                            <div class="flex justify-between mt-6 py-4 px-6 bg-gray-100 rounded-b-lg">
                                <PrimaryButton @click="goToPreviousStep" type="button" class="bg-gray-500 hover:bg-gray-600">
                                    <i class="fas fa-chevron-left mr-1"></i> Previous
                                </PrimaryButton>
                                <div class="flex space-x-3">
                                    <PrimaryButton @click="saveDraft" type="button" class="bg-gray-500 hover:bg-gray-600">
                                        Save as Draft
                                    </PrimaryButton>
                                    <PrimaryButton type="submit" :disabled="form.processing">
                                        {{ form.processing ? 'Posting...' : 'Submit Job Posting' }} <i class="fas fa-chevron-right ml-1"></i>
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
