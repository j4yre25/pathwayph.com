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
  if (currentStep.value === 'job-details') {
  } else if (currentStep.value === 'description') {
    currentStep.value = 'job-details';
  }
};

// Navigate to next step
const goToNextStep = () => {
  if (currentStep.value === 'job-details') {
    currentStep.value = 'description';
  }
};

// Available categories based on sector
const availableCategories = ref([]);

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
                                required
                            />
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
                                required
                            />
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
                                required
                            >
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
                                <i class="fas fa-chart-line text-purple-500 mr-2"></i>
                                <InputLabel for="experience_level" value="Experience Level*" class="font-medium text-gray-700" />
                            </div>
                            <select
                                id="experience_level"
                                v-model="form.experience_level"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
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
                                <i class="fas fa-building text-teal-500 mr-2"></i>
                                <InputLabel for="work_environment" value="Work Environment*" class="font-medium text-gray-700" />
                            </div>
                            <select
                                id="work_environment"
                                v-model="form.work_environment"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                                <option value="">Select Work Environment</option>
                                <option value="On-site">On-site</option>
                                <option value="Remote">Remote</option>
                                <option value="Hybrid">Hybrid</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Specify the work environment for this position.</p>
                            <InputError :message="form.errors.work_environment" class="mt-1" />
                        </div>
                        
                        <!-- Number of Vacancies Field -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex items-center mb-2 border-b border-gray-200 pb-2">
                                <i class="fas fa-users text-green-500 mr-2"></i>
                                <InputLabel for="vacancy" value="Number of Vacancies*" class="font-medium text-gray-700" />
                            </div>
                            <TextInput 
                                id="vacancy"
                                v-model="form.vacancy" 
                                type="number" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter number of vacancies"
                                min="1"
                                required
                            />
                            <p class="mt-1 text-xs text-gray-500">Specify how many positions are available.</p>
                            <InputError :message="form.errors.vacancy" class="mt-1" />
                        </div>

                        <!-- Sector Field -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200" v-if="props.sectors && props.sectors.length">
                            <div class="flex items-center mb-2 border-b border-gray-200 pb-2">
                                <i class="fas fa-industry text-blue-500 mr-2"></i>
                                <InputLabel for="sector" value="Sector*" class="font-medium text-gray-700" />
                            </div>
                            <select
                                id="sector"
                                v-model="form.sector"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                                <option value="">Select Sector</option>
                                <option v-for="sector in props.sectors" :key="sector.id" :value="sector.id">
                                    {{ sector.name }}
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Select the industry sector for this job.</p>
                            <InputError :message="form.errors.sector" class="mt-1" />
                        </div>

                        <!-- Category Field -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200" v-if="props.sectors && props.sectors.length">
                            <div class="flex items-center mb-2 border-b border-gray-200 pb-2">
                                <i class="fas fa-tag text-orange-500 mr-2"></i>
                                <InputLabel for="category" value="Category*" class="font-medium text-gray-700" />
                            </div>
                            <select
                                id="category"
                                v-model="form.category"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                                :disabled="!form.sector || !availableCategories.length"
                            >
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
                            class="mt-1 block w-full"
                            placeholder="Describe the job responsibilities and expectations"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500">Provide detailed information about the role, responsibilities, and what a typical day looks like.</p>
                        <InputError :message="form.errors.description" class="mt-1" />
                    </div>

                    <!-- Job Requirements Field -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex items-center mb-2 border-b border-gray-200 pb-2">
                            <i class="fas fa-list-check text-blue-500 mr-2"></i>
                            <InputLabel for="requirements" value="Job Requirements*" class="font-medium text-gray-700" />
                        </div>
                        <RichTextEditor 
                            id="requirements"
                            v-model="form.requirements" 
                            class="mt-1 block w-full"
                            placeholder="List the qualifications and skills required for this position"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500">Specify education, experience, skills, and any other qualifications needed for this role.</p>
                        <InputError :message="form.errors.requirements" class="mt-1" />
                    </div>

                    <!-- Skills Field -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex items-center mb-2 border-b border-gray-200 pb-2">
                            <i class="fas fa-tags text-green-500 mr-2"></i>
                            <InputLabel for="related_skills" value="Related Skills" class="font-medium text-gray-700" />
                        </div>
                        <div class="flex mt-1">
                            <TextInput
                                id="related_skills"
                                type="text"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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