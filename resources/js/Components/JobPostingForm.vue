<script setup>
import { router, usePage, useForm } from '@inertiajs/vue3'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import FormSection from '@/Components/FormSection.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import MultiSelect from '@/Components/MultiSelect.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'
import Datepicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import '@fortawesome/fontawesome-free/css/all.css'
import { ref, watch, computed, watchEffect } from 'vue'

const page = usePage()

const props = defineProps({
    jobs: Array,
    sectors: Array,
    categories: Array,
    programs: Array,
    authUser: Object,
})

const programs = props.programs

const postedBy = computed(() => {
    return props.authUser?.hr?.full_name ?? props.authUser?.name ?? ''
})

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
})

watchEffect(() => {
    form.posted_by = postedBy.value
})

// Tabs setup
const currentStep = ref('job-details')

// Navigate to previous step
const goToPreviousStep = () => {
  if (currentStep.value === 'job-details') {
  } else if (currentStep.value === 'salary-info') {
    currentStep.value = 'job-details'
  } else if (currentStep.value === 'description') {
    currentStep.value = 'salary-info'
  } 
}

// Navigate to next step
const goToNextStep = () => {
  if (currentStep.value === 'job-details') {
    currentStep.value = 'salary-info'
  } else if (currentStep.value === 'salary-info') {
    currentStep.value = 'description'
  } else if (currentStep.value === 'description') {
    currentStep.value = 'review'
  }
}

// Save as draft functionality
const saveDraft = () => {
  // Implement save as draft functionality
  console.log('Saving as draft:', form)
}

const expirationDate = ref(null)
const today = new Date()

// This is for the categories & sector dropdown
const availableCategories = ref([])

watch(() => form.sector, (newSector) => {
    if (newSector) {
        const selectedSector = props.sectors.find(sector => sector.id === newSector)
        availableCategories.value = selectedSector ? selectedSector.categories : []
        form.category = ''
    }
    else {
        availableCategories.value = []
    }
})

// Salary Setup
const salaryError = ref('')

const validateSalary = () => {
    if (form.is_negotiable) {
        salaryError.value = ''
        return
    }
    
    const min = parseInt(form.job_min_salary)
    const max = parseInt(form.job_max_salary)

    if (isNaN(min) || min < 5000 || min > 100000) {
        salaryError.value = 'Minimum salary must be between ₱5,000 and ₱100,000'
    } else if (isNaN(max) || max < 5000 || max > 100000) {
        salaryError.value = 'Maximum salary must be between ₱5,000 and ₱100,000'
    } else if (min > max) {
        salaryError.value = 'Minimum salary cannot be greater than maximum salary'
    } else {
        salaryError.value = ''
    }
}

watch(() => form.is_negotiable, () => {
    validateSalary()
})

// Skill setup
const newSkill = ref('')

const addSkill = () => {
    if (newSkill.value.trim() !== '') {
        form.related_skills.push(newSkill.value.trim())
        newSkill.value = ''
    }
}

const removeSkill = (index) => {
    form.related_skills.splice(index, 1)
}

// Extract selected program IDs
const extractProgramIds = () => {
    form.program_id = form.program_id.map(program => program.id)
}

const createJob = () => {
    form.program_id = form.program_id.map(program => program.id ?? program)

    form.post(route('company.jobs.store', { user: page.props.auth.user.id }), {
        onSuccess: () => {
            router.visit(route('company.jobs', { user: page.props.auth.user.id }))
        },
        onError: (errors) => {
            console.log('Validation errors:', errors)
        }
    })
}
</script>

<template>
    <div>
        <FormSection @submitted="createJob()">
            <template #form>
                <h1 class="text-2xl font-bold">Create Job Posting</h1>
                <p class="text-sm text-gray-500 mb-6">Fill out the form below to create a new job posting for potential candidates.</p>
                
                <!-- Progress Tabs -->
                <div class="flex justify-center mb-8 border-b">
                    <div 
                        @click="currentStep = 'job-details'" 
                        :class="[
                        'px-4 py-2 cursor-pointer', 
                        currentStep === 'job-details' ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500'
                        ]">
                        Basic Job Details
                    </div>
                    <div 
                        @click="currentStep === 'job-details' ? null : currentStep = 'salary-info'" 
                        :class="[
                        'px-4 py-2 cursor-pointer', 
                        currentStep === 'salary-info' ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500',
                        currentStep === 'job-details' ? 'opacity-50 cursor-not-allowed' : ''
                        ]">
                        Salary Information
                    </div>
                    <div 
                        @click="currentStep === 'job-details' || currentStep === 'salary-info' ? null : currentStep = 'description'" 
                        :class="[
                        'px-4 py-2 cursor-pointer', 
                        currentStep === 'description' ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500',
                        currentStep === 'job-details' || currentStep === 'salary-info' ? 'opacity-50 cursor-not-allowed' : ''
                        ]">
                        Description & Requirements
                    </div>
                    <div 
                        @click="currentStep === 'job-details' || currentStep === 'salary-info' || currentStep === 'description' ? null : currentStep = 'review'" 
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
                                <TextInput
                                id="job_title"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.job_title"
                                required
                                autofocus
                                />
                            <InputError class="mt-2" :message="form.errors.job_title" />
                        </div>
                        
                        <!-- Job Location -->
                        <div>
                            <InputLabel for="job_location" value="Job Location" />
                            <TextInput
                            id="job_location"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.job_location"
                            required
                            />
                            <InputError class="mt-2" :message="form.errors.job_location" />
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
                            min="1"
                            />
                            <InputError class="mt-2" :message="form.errors.job_vacancies" />
                        </div>
                        
                        <!-- Program Selection -->
                        <div>
                            <InputLabel for="program_id" value="Program Graduated" />
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
                        
                        <!-- Sector -->
                        <div>
                            <InputLabel for="sector" value="Sector" />
                            <select
                            id="sector"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            v-model="form.sector"
                            required
                            >
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
                            :disabled="!form.sector"
                            >
                            <option value="">Select Category</option>
                            <option v-for="category in availableCategories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.category" />
                        </div>
                        
                        <!-- Experience Level -->
                        <div>
                            <InputLabel for="job_experience_level" value="Experience Level" />
                            <select
                                id="job_experience_level"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                v-model="form.job_experience_level"
                                required
                            >
                                <option value="">Select Experience Level</option>
                                <option value="Entry-level">Beginner</option>
                                <option value="Intermediate">Intermediate</option>
                                <option value="Mid-level">Mid-level</option>
                                <option value="Senior-executive">Senior/Executive Level</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.job_experience_level" />
                        </div>
                        
                        <!-- Work Environment -->
                        <div>
                            <InputLabel for="job_work_environment" value="Work Environment" />
                            <div class="mt-2 space-y-2">
                                <div class="flex items-center">
                                    <input
                                        id="remote"
                                        type="radio"
                                        name="work_environment"
                                        value="Remote"
                                        v-model="form.job_work_environment"
                                        class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                    />
                                    <label for="remote" class="ml-2 block text-sm text-gray-900">Remote</label>
                                </div>
                                <div class="flex items-center">
                                    <input
                                        id="hybrid"
                                        type="radio"
                                        name="work_environment"
                                        value="Hybrid"
                                        v-model="form.job_work_environment"
                                        class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                    />
                                    <label for="hybrid" class="ml-2 block text-sm text-gray-900">Hybrid</label>
                                </div>
                                <div class="flex items-center">
                                    <input
                                        id="onsite"
                                        type="radio"
                                        name="work_environment"
                                        value="On-site"
                                        v-model="form.job_work_environment"
                                        class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                    />
                                    <label for="onsite" class="ml-2 block text-sm text-gray-900">On-site</label>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.job_work_environment" />
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
                            id="job_salary_type"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            v-model="form.job_salary_type"
                            >
                            <option value="">Select Salary Type</option>
                            <option value="monthly">Monthly</option>
                            <option value="weekly">Weekly</option>
                            <option value="hourly">Hourly</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.job_salary_type" />
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
                                max="100000"
                            />
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
                                id="job_max_salary"
                                type="number"
                                class="pl-7 block w-full"
                                v-model="form.job_max_salary"
                                :disabled="form.is_negotiable"
                                @input="validateSalary"
                                min="5000"
                                max="100000"
                            />
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Enter amount between ₱5,000 and ₱100,000</p>
                            <InputError class="mt-2" :message="form.errors.job_max_salary" />
                        </div>
                        
                        <!-- Negotiable Salary Checkbox -->
                        <div class="flex items-center mt-6">
                            <input
                            id="is_negotiable"
                            type="checkbox"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            v-model="form.is_negotiable"
                            @change="validateSalary"
                            />
                            <label for="is_negotiable" class="ml-2 block text-sm text-gray-900">
                            Salary is negotiable
                            </label>
                        </div>
                    </div>
                    
                    <!-- Salary Error Message -->
                    <div v-if="salaryError" class="text-red-500 text-sm">
                        {{ salaryError }}
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
                        <RichTextEditor
                            id="job_description"
                            v-model="form.job_description"
                            class="mt-1 block w-full"
                            placeholder="Enter job description"
                        />
                        <p class="mt-1 text-xs text-gray-500">Clearly describe the job role, responsibilities, and what a typical day might look like.</p>
                        <InputError class="mt-2" :message="form.errors.job_description" />
                    </div>
                    
                    <!-- Job Requirements -->
                    <div>
                        <InputLabel for="job_requirements" value="Job Requirements" />
                        <RichTextEditor
                            id="job_requirements"
                            v-model="form.job_requirements"
                            class="mt-1 block w-full"
                            placeholder="Enter job requirements"
                        />
                        <p class="mt-1 text-xs text-gray-500">List qualifications, education, experience, certifications, etc. required for this position.</p>
                        <InputError class="mt-2" :message="form.errors.job_requirements" />
                    </div>
                    
                    <!-- Skills -->
                    <div>
                        <InputLabel for="related_skills" value="Skills Required" />
                        <div class="flex mt-1">
                            <TextInput
                            id="related_skills"
                            type="text"
                            class="block w-full"
                            v-model="newSkill"
                            placeholder="Add skills..."
                            @keyup.enter.prevent="addSkill"
                            />
                            <PrimaryButton type="button" @click="addSkill" class="ml-2">
                            Add
                            </PrimaryButton>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Press Enter to add multiple skills</p>
                        
                        <!-- Skills List -->
                        <div v-if="form.related_skills.length > 0" class="mt-3 flex flex-wrap gap-2">
                            <div 
                            v-for="(skill, index) in form.related_skills" 
                            :key="index"
                            class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full flex items-center"
                            >
                            <span>{{ skill }}</span>
                            <button 
                                type="button" 
                                @click="removeSkill(index)" 
                                class="ml-2 text-blue-600 hover:text-blue-800 focus:outline-none"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.related_skills" />
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
                            <p class="mt-1 text-xs text-gray-500">Auto-filled with your name</p>
                            <InputError class="mt-2" :message="form.errors.posted_by" />
                        </div>
                        
                        <!-- Application Deadline -->
                        <div>
                            <InputLabel for="job_deadline" value="Application Deadline" />
                           <Datepicker 
                                v-model="form.job_deadline" 
                                :enable-time-picker="false"
                                input-class-name="w-full p-2 border rounded-lg mt-2"
                                :min-date="today"
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
                            min="1"
                            />
                            <p class="mt-1 text-xs text-gray-500">Maximum number of applications to accept (leave blank for no limit)</p>
                            <InputError class="mt-2" :message="form.errors.job_application_limit" />
                        </div>
                    </div>
                    
                    <div class="flex justify-between mt-8">
                        <PrimaryButton @click="goToPreviousStep" type="button" class="bg-gray-500 hover:bg-gray-600">
                            Previous
                        </PrimaryButton>
                        <div class="flex space-x-3">
                            <PrimaryButton @click="saveDraft" type="button" class="bg-gray-500 hover:bg-gray-600">
                                Save as Draft
                            </PrimaryButton>
                            <PrimaryButton type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Submitting...' : 'Submit Job Posting' }}
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </template>
        </FormSection>
    </div>
</template>