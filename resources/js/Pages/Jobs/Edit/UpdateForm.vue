<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ActionMessage from '@/Components/ActionMessage.vue';
import '@fortawesome/fontawesome-free/css/all.css';
import { ref, watch } from 'vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import MultiSelect from '@/Components/MultiSelect.vue';
import FormSection from '@/Components/FormSection.vue';

const props = defineProps({
    job: Object,
    sectors: Array,
    categories: Array,
    programs: Array
});

// Tabs setup
const currentStep = ref('job-details');

// Navigate to previous step
const goToPreviousStep = () => {
  if (currentStep.value === 'salary-info') {
    currentStep.value = 'job-details'
  } else if (currentStep.value === 'description') {
    currentStep.value = 'salary-info'
  }
}


// Navigate to next step
const goToNextStep = () => {
  if (currentStep.value === 'job-details') {
    currentStep.value = 'salary-info';
  } else if (currentStep.value === 'salary-info') {
    currentStep.value = 'description';
  }
};

// Available categories based on sector
const availableCategories = ref([]);

// Salary validation
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

// Initialize form with job data
const form = useForm({
    job_title: props.job.job_title,
    job_type: props.job.job_type || '',
    experience_level: props.job.experience_level || '',
    work_environment: props.job.work_environment || '',
    location: props.job.location || '',
    vacancy: props.job.vacancy || 1,
    sector: props.job.sector_id || '',
    category: props.job.category_id || '',
    description: props.job.description || '',
    requirements: props.job.job_requirements || '',
    related_skills: props.job.skills ? props.job.skills.split(',').map(skill => skill.trim()) : [],
    job_min_salary: props.job.job_min_salary || '',
    job_max_salary: props.job.job_max_salary || '',
    salary_type: props.job.salary_type || '',
    is_negotiable: props.job.is_negotiable || false,
});

// Watch for sector changes to update categories
watch(() => form.sector, (newSector) => {
    if (newSector && props.sectors) {
        const selectedSector = props.sectors.find(sector => sector.id === newSector);
        availableCategories.value = selectedSector ? selectedSector.categories : [];
        if (!availableCategories.value.some(cat => cat.id === form.category)) {
            form.category = '';
        }
    } else {
        availableCategories.value = [];
    }
});

// Watch for is_negotiable changes to validate salary
watch(() => form.is_negotiable, () => {
    validateSalary();
});

// Initialize categories if sector is already selected
if (form.sector && props.sectors) {
    const selectedSector = props.sectors.find(sector => sector.id === form.sector);
    availableCategories.value = selectedSector ? selectedSector.categories : [];
}

// Skill management
const newSkill = ref('');

const addSkill = () => {
    if (newSkill.value.trim() !== '' && !form.related_skills.includes(newSkill.value.trim())) {
        form.related_skills.push(newSkill.value.trim());
        newSkill.value = '';
    }
};

const removeSkill = (index) => {
    form.related_skills.splice(index, 1);
};

const submitForm = () => {
    form.put(route('jobs.update', {job: props.job.id}), {
        preserveScroll: true
    });
};
</script>

<template>
    <div class="p-6 bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="border-b border-gray-200 pb-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-pen-to-square text-blue-500 mr-2"></i>
                Update Job Information
            </h3>
            <p class="text-sm text-gray-600 mt-2">
                Update your job posting details below. All fields marked with an asterisk (*) are required.
            </p>
        </div>
        
        <FormSection @submitted="submitForm">
            <template #form>
                <!-- Progress Tabs -->
                <div class="flex justify-center mb-8 border-b">
                    <div @click="currentStep = 'job-details'" 
                        :class="['px-4 py-2 cursor-pointer flex flex-col items-center', 
                        currentStep === 'job-details' ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500']">
                        <i class="fas fa-briefcase text-xl mb-1"></i> 
                        <span>Basic Job Details</span>
                    </div>
                    <div @click="currentStep = 'salary-info'" 
                        :class="['px-4 py-2 cursor-pointer flex flex-col items-center', 
                        currentStep === 'salary-info' ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500']">
                        <i class="fas fa-briefcase text-xl mb-1"></i> 
                        <span>Salary Information</span>
                    </div>
                    <div @click="currentStep === 'job-details' ? null : currentStep = 'description'" 
                        :class="['px-4 py-2 cursor-pointer flex flex-col items-center', 
                        currentStep === 'description' ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500',
                        currentStep === 'job-details' ? 'opacity-50 cursor-not-allowed' : '']">
                        <i class="fas fa-clipboard-list text-xl mb-1"></i> 
                        <span>Description & Requirements</span>
                    </div>
                </div>

                <!-- Basic Job Details Tab -->
                <div v-if="currentStep === 'job-details'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Job Title Field -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex items-center mb-2 border-b border-gray-200 pb-2">
                                <i class="fas fa-briefcase text-blue-500 mr-2"></i>
                                <InputLabel for="job_title" value="Job Title*" class="font-medium text-gray-700" />
                            </div>
                            <TextInput 
                                id="job_title"
                                v-model="form.job_title" 
                                type="text" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter job title"
                                required/>
                            <p class="mt-1 text-xs text-gray-500">A clear and concise title will attract more qualified candidates.</p>
                            <InputError :message="form.errors.job_title" class="mt-1" />
                        </div>
                        
                        <!-- Job Location Field -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex items-center mb-2 border-b border-gray-200 pb-2">
                                <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                                <InputLabel for="location" value="Job Location*" class="font-medium text-gray-700" />
                            </div>
                            <TextInput 
                                id="location"
                                v-model="form.location" 
                                type="text" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter job location"
                                required/>
                            <p class="mt-1 text-xs text-gray-500">Specify the city, state, or if it's a remote position.</p>
                            <InputError :message="form.errors.location" class="mt-1" />
                        </div>
                        
                        <!-- Job Type Field -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex items-center mb-2 border-b border-gray-200 pb-2">
                                <i class="fas fa-business-time text-blue-500 mr-2"></i>
                                <InputLabel for="job_type" value="Job Type*" class="font-medium text-gray-700" />
                            </div>
                            <select
                                id="job_type"
                                v-model="form.job_type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required>
                                <option value="">Select Job Type</option>
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Contract">Contract</option>
                                <option value="Freelance">Freelance</option>
                                <option value="Internship">Internship</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Select the employment type for this position.</p>
                            <InputError :message="form.errors.job_type" class="mt-1" />
                        </div>
                        
                        <!-- Experience Level Field -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex items-center mb-2 border-b border-gray-200 pb-2">
                                <i class="fas fa-user-tie text-blue-500 mr-2"></i>
                                <InputLabel for="experience_level" value="Experience Level*" class="font-medium text-gray-700" />
                            </div>
                            <select
                                id="experience_level"
                                v-model="form.experience_level"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required>
                                <option value="">Select Experience Level</option>
                                <option value="Entry-level">Entry-level</option>
                                <option value="Intermediate">Intermediate</option>
                                <option value="Mid-level">Mid-level</option>
                                <option value="Senior-executive">Senior/Executive-level</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Indicate the level of experience required for this role.</p>
                            <InputError :message="form.errors.experience_level" class="mt-1" />
                        </div>
                        
                        <!-- Work Environment Field -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex items-center mb-2 border-b border-gray-200 pb-2">
                                <i class="fas fa-building text-blue-500 mr-2"></i>
                                <InputLabel for="work_environment" value="Work Environment*" class="font-medium text-gray-700" />
                            </div>
                            <select
                                id="work_environment"
                                v-model="form.work_environment"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required>
                                <option value="">Select Work Environment</option>
                                <option value="On-site">On-site</option>
                                <option value="Remote">Remote</option>
                                <option value="Hybrid">Hybrid</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Specify whether this position is on-site, remote, or hybrid.</p>
                            <InputError :message="form.errors.work_environment" class="mt-1" />
                        </div>
                        
                        <!-- Number of Vacancies Field -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex items-center mb-2 border-b border-gray-200 pb-2">
                                <i class="fas fa-users text-blue-500 mr-2"></i>
                                <InputLabel for="vacancy" value="Number of Vacancies*" class="font-medium text-gray-700" />
                            </div>
                            <TextInput 
                                id="vacancy"
                                v-model="form.vacancy" 
                                type="number" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                min="1"
                                required/>
                            <p class="mt-1 text-xs text-gray-500">Indicate how many positions are available.</p>
                            <InputError :message="form.errors.vacancy" class="mt-1" />
                        </div>
                        
                        <!-- Sector Field -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex items-center mb-2 border-b border-gray-200 pb-2">
                                <i class="fas fa-industry text-blue-500 mr-2"></i>
                                <InputLabel for="sector" value="Sector*" class="font-medium text-gray-700" />
                            </div>
                            <select
                                id="sector"
                                v-model="form.sector"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required>
                                <option value="">Select Sector</option>
                                <option v-for="sector in props.sectors" :key="sector.id" :value="sector.id">
                                    {{ sector.name }}
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Choose the industry sector for this position.</p>
                            <InputError :message="form.errors.sector" class="mt-1" />
                        </div>
                        
                        <!-- Category Field -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex items-center mb-2 border-b border-gray-200 pb-2">
                                <i class="fas fa-tag text-blue-500 mr-2"></i>
                                <InputLabel for="category" value="Category*" class="font-medium text-gray-700" />
                            </div>
                            <select
                                id="category"
                                v-model="form.category"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                :disabled="!form.sector"
                                required>
                                <option value="">Select Category</option>
                                <option v-for="category in availableCategories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Select a specific category within the chosen sector.</p>
                            <InputError :message="form.errors.category" class="mt-1" />
                        </div>
                    </div>
                    
                    <div class="flex justify-between mt-6 py-4 px-6 bg-gray-100 rounded-lg">
                        <div></div>
                        <div class="flex space-x-3">
                            <PrimaryButton @click="goToNextStep" type="button">
                                Next <i class="fas fa-chevron-right ml-1"></i>
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
                
                <!-- Salary Information Tab -->
                <div v-if="currentStep === 'salary-info'" class="space-y-6">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex items-center mb-2 border-b border-gray-200 pb-2">
                            <i class="fas fa-align-left text-blue-500 mr-2"></i>
                            <InputLabel for="salary_type" value="Salary Type*" class="font-medium text-gray-700" />
                        </div>
                        <select
                            id="salary_type"
                            v-model="form.salary_type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required>
                            <option value="">Select Salary Type</option>
                            <option value="monthly">Monthly</option>
                            <option value="weekly">Weekly</option>
                            <option value="hourly">Hourly</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Specify the type of salary (e.g., monthly, hourly, commission).</p>
                    </div>

                    <!-- Negotiable Salary Checkbox -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex items-center">
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
                        <p class="mt-1 text-xs text-gray-500">Check this if the salary is open to negotiation.</p>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex items-center mb-2 border-b border-gray-200 pb-2">
                            <i class="fas fa-align-left text-blue-500 mr-2"></i>
                            <InputLabel for="salary" value="Salary Range*" class="font-medium text-gray-700" />
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">₱</span>
                                    </div>
                                    <TextInput
                                        id="job_min_salary"
                                        v-model="form.job_min_salary"
                                        type="number"
                                        class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="Enter minimum salary"
                                        min="5000"
                                        max="100000"
                                        :disabled="form.is_negotiable"
                                        @input="validateSalary"
                                        required
                                    />
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Enter amount between ₱5,000 and ₱100,000</p>
                            </div>
                            <div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">₱</span>
                                    </div>
                                    <TextInput
                                        id="job_max_salary"
                                        v-model="form.job_max_salary"
                                        type="number"
                                        class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="Enter maximum salary"
                                        min="5000"
                                        max="100000"
                                        :disabled="form.is_negotiable"
                                        @input="validateSalary"
                                        required
                                    />
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Enter amount between ₱5,000 and ₱100,000</p>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Specify the salary range for this position.</p>
                        
                        <!-- Salary Error Message -->
                        <div v-if="salaryError" class="text-red-500 text-sm mt-2">
                            {{ salaryError }}
                        </div>
                    </div>

                    <div class="flex justify-between mt-6 py-4 px-6 bg-gray-100 rounded-lg">
                        <div class="flex space-x-3">
                            <PrimaryButton @click="goToPreviousStep" type="button">
                                <i class="fas fa-chevron-left mr-1"></i> 
                                Previous
                            </PrimaryButton>
                        </div>
                        <div class="flex space-x-3">
                            <PrimaryButton @click="goToNextStep" type="button">
                                Next <i class="fas fa-chevron-right ml-1"></i>
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            
                <!-- Description & Requirements Tab -->
                <div v-if="currentStep === 'description'" class="space-y-6">
                    <!-- Job Description Field -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex items-center mb-2 border-b border-gray-200 pb-2">
                            <i class="fas fa-align-left text-blue-500 mr-2"></i>
                            <InputLabel for="description" value="Job Description*" class="font-medium text-gray-700" />
                        </div>
                        <RichTextEditor
                            id="description"
                            v-model="form.description"
                            placeholder="Enter detailed job description"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500">Provide a comprehensive description of the job responsibilities and expectations.</p>
                        <InputError :message="form.errors.description" class="mt-1" />
                    </div>
                    
                    <!-- Job Requirements Field -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex items-center mb-2 border-b border-gray-200 pb-2">
                            <i class="fas fa-clipboard-check text-blue-500 mr-2"></i>
                            <InputLabel for="requirements" value="Job Requirements*" class="font-medium text-gray-700" />
                        </div>
                        <RichTextEditor
                            id="requirements"
                            v-model="form.requirements"
                            placeholder="Enter job requirements"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500">List qualifications, education, and experience required for this position.</p>
                        <InputError :message="form.errors.requirements" class="mt-1" />
                    </div>
                    
                    <!-- Related Skills Field -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex items-center mb-2 border-b border-gray-200 pb-2">
                            <i class="fas fa-tools text-blue-500 mr-2"></i>
                            <InputLabel for="related_skills" value="Related Skills" class="font-medium text-gray-700" />
                        </div>
                        <div class="flex">
                            <TextInput 
                                id="related_skills"
                                v-model="newSkill" 
                                type="text" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter a skill"
                            />
                            <PrimaryButton 
                                @click="addSkill" 
                                type="button" 
                                class="ml-2 mt-1">
                                <i class="fas fa-plus"></i>
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
                        <InputError :message="form.errors.related_skills" class="mt-1" />
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-between mt-6 py-4 px-6 bg-gray-100 rounded-lg">
                        <PrimaryButton @click="goToPreviousStep" type="button" class="bg-gray-500 hover:bg-gray-600">
                            <i class="fas fa-chevron-left mr-1"></i> Previous
                        </PrimaryButton>
                        
                        <div class="flex items-center">
                            <ActionMessage :on="form.recentlySuccessful" class="mr-3">
                                <div class="flex items-center bg-green-50 px-3 py-1 rounded-full text-sm text-green-600">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <span>Changes saved successfully!</span>
                                </div>
                            </ActionMessage>
                            
                            <PrimaryButton 
                                type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition shadow-sm hover:shadow-md"
                                :class="{ 'opacity-25': form.processing }" 
                                :disabled="form.processing"
                            >
                                <i class="fas fa-save mr-2"></i>
                                Save Changes
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </template>
        </FormSection>
    </div>
</template>