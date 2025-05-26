<script setup>
import { ref, computed, onMounted, watch, defineProps } from 'vue';
import { router, usePage, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import MultiSelect from '@/Components/MultiSelect.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import '@fortawesome/fontawesome-free/css/all.css';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faBriefcase, faMoneyBillWave, faFileAlt, faInfoCircle, faSpinner, faEye, faCheck, faExclamationTriangle, faQuestionCircle } from '@fortawesome/free-solid-svg-icons';

// Add icons to library
library.add(faBriefcase, faMoneyBillWave, faFileAlt, faInfoCircle, faSpinner, faEye, faCheck, faExclamationTriangle, faQuestionCircle);

const page = usePage();

const props = defineProps({
  sectors: Array,
  categories: Array,
  programs: Array,
  company: Object,
});

const currentStep = ref('job-details');
const availableCategories = ref([]);
const salaryError = ref('');
const newSkill = ref('');
const selectedSector = ref(null);
const filteredCategories = ref([]);
const skills = ref([]);

// Define steps for better organization
const steps = {
  JOB_DETAILS: 'job-details',
  SALARY_INFO: 'salary-info',
  DESCRIPTION: 'description',
  OTHER_INFO: 'review'
};

// Track if we're viewing all sections
const viewingAllSections = ref(false);

// Function to toggle between tab view and all sections view
const toggleViewMode = () => {
  viewingAllSections.value = !viewingAllSections.value;
};

// Calculate minimum date for application deadline (today's date)
const minDate = computed(() => {
  const today = new Date();
  return today.toISOString().split('T')[0];
});

// Form data using useForm
const form = useForm({
  job_title: '',
  location: '',
  program_id: [],
  vacancy: '',
  min_salary: '',
  max_salary: '',
  is_salary_negotiable: false,
  job_type: '',
  experience_level: '',
  description: '',
  requirements: '',
  skills: [],
  sector: '',
  category: '',
  work_environment: '',
  branch_location: '',
  application_deadline: '',
  application_limit: '',
  posted_by: `${page.props.auth.user.company_hr_first_name} ${page.props.auth.user.company_hr_last_name}`,
  company_id: page.props.auth.user.company_id,
});

// Watch for sector changes to update available categories
watch(() => form.sector, (newSector) => {
  if (newSector) {
    const selectedSector = props.sectors.find(sector => sector.id === newSector);
    availableCategories.value = selectedSector ? selectedSector.categories : [];
    form.category = '';
  } else {
    availableCategories.value = [];
  }
});

// Validate salary range
const validateSalary = () => {
  // Skip validation if salary is negotiable
  if (form.is_salary_negotiable) {
    salaryError.value = '';
    return;
  }
  
  const min = parseInt(form.min_salary);
  const max = parseInt(form.max_salary);
  
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

// Watch for changes in the negotiable checkbox
watch(() => form.is_salary_negotiable, () => {
  validateSalary();
});

// Add skill to the list
const addSkill = () => {
  if (newSkill.value.trim() !== '') {
    form.skills.push(newSkill.value.trim());
    newSkill.value = '';
  }
};

// Remove skill from the list
const removeSkill = (index) => {
  form.skills.splice(index, 1);
};

// Navigate to previous step
const goToPreviousStep = () => {
  if (currentStep.value === steps.JOB_DETAILS) {
    // Already at first step, do nothing
  } else if (currentStep.value === steps.SALARY_INFO) {
    currentStep.value = steps.JOB_DETAILS;
  } else if (currentStep.value === steps.DESCRIPTION) {
    currentStep.value = steps.SALARY_INFO;
  } else if (currentStep.value === steps.OTHER_INFO) {
    currentStep.value = steps.DESCRIPTION;
  }
};

// Navigate to next step
const goToNextStep = () => {
  if (currentStep.value === steps.JOB_DETAILS) {
    currentStep.value = steps.SALARY_INFO;
  } else if (currentStep.value === steps.SALARY_INFO) {
    currentStep.value = steps.DESCRIPTION;
  } else if (currentStep.value === steps.DESCRIPTION) {
    currentStep.value = steps.OTHER_INFO;
  }
  // No next step after OTHER_INFO (review)
};

// Extract selected program IDs (new function)
const extractProgramIds = () => {
  // Extract only the ids from the selected programs
  form.program_id = form.program_id.map(program => program.id);
};

// Loading state for form submission
const isSubmitting = ref(false);
const isDraftSaving = ref(false);

// Highlight fields with errors
const highlightFields = () => {
  // Add error class to all fields with errors
  const fieldsWithErrors = Object.keys(form.errors);
  fieldsWithErrors.forEach(field => {
    const element = document.getElementById(field);
    if (element) {
      element.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
      // Add aria-invalid attribute for screen readers
      element.setAttribute('aria-invalid', 'true');
      // Add aria-describedby to connect to error message
      element.setAttribute('aria-describedby', `${field}-error`);
    }
  });
};

// Submit the form
const submit = () => {
  isSubmitting.value = true;
  form.program_id = form.program_id.map(program => program.id ?? program);
  
  form.post(route('company.jobs.store', { user: page.props.auth.user.id }), {
    onSuccess: () => {
      isSubmitting.value = false;
      router.visit(route('company.jobs', { user: page.props.auth.user.id }));
    },
    onError: (errors) => {
      isSubmitting.value = false;
      console.log('Validation errors:', errors);
      highlightFields();
    }
  });
};

// Save as draft functionality
const saveDraft = () => {
  isDraftSaving.value = true;
  // Implement save as draft functionality
  console.log('Saving as draft:', form);
  // Simulate API call
  setTimeout(() => {
    isDraftSaving.value = false;
    // Show success message
  }, 1000);
};
</script>

<template>
  <AppLayout title="Create Job">
    <Container>
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-4">Create New Job Posting</h1>
        
        <!-- Help Section -->
        <div class="bg-blue-50 p-4 rounded-lg mb-6 border border-blue-200">
          <h2 class="text-lg font-semibold text-blue-700 mb-2 flex items-center">
            <font-awesome-icon :icon="['fas', 'info-circle']" class="mr-2" aria-hidden="true" />
            Helpful Tips
          </h2>
          <ul class="text-sm text-blue-800 space-y-2 list-disc pl-5">
            <li>Fill out each section completely for best results</li>
            <li>Use the <strong>Tab View</strong> for a step-by-step process or <strong>View All</strong> to see everything at once</li>
            <li>Required fields are marked with an asterisk (*)</li>
            <li>You can save your progress as a draft at any time</li>
            <li>Preview your job posting at the bottom of the form before submitting</li>
          </ul>
        </div>
        
        <!-- Progress Tabs -->
         <div class="flex justify-between mb-8">
           <div class="flex border-b flex-grow">
             <div 
               @click="currentStep = steps.JOB_DETAILS" 
               :class="[
                 'px-4 py-2 cursor-pointer flex items-center', 
                 currentStep === steps.JOB_DETAILS ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500'
               ]"
               role="tab"
               :aria-selected="currentStep === steps.JOB_DETAILS"
               tabindex="0"
               @keydown.enter="currentStep = steps.JOB_DETAILS"
               @keydown.space="currentStep = steps.JOB_DETAILS">
               <font-awesome-icon :icon="['fas', 'briefcase']" class="mr-2" aria-hidden="true" />
               <span>Basic Job Details</span>
             </div>
             <div 
               @click="currentStep === steps.JOB_DETAILS ? null : currentStep = steps.SALARY_INFO" 
               :class="[
                 'px-4 py-2 cursor-pointer flex items-center', 
                 currentStep === steps.SALARY_INFO ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500',
                 currentStep === steps.JOB_DETAILS ? 'opacity-50 cursor-not-allowed' : ''
               ]"
               role="tab"
               :aria-selected="currentStep === steps.SALARY_INFO"
               :aria-disabled="currentStep === steps.JOB_DETAILS"
               tabindex="0"
               @keydown.enter="currentStep === steps.JOB_DETAILS ? null : currentStep = steps.SALARY_INFO"
               @keydown.space="currentStep === steps.JOB_DETAILS ? null : currentStep = steps.SALARY_INFO">
               <font-awesome-icon :icon="['fas', 'money-bill-wave']" class="mr-2" aria-hidden="true" />
               <span>Salary Information</span>
             </div>
             <div 
               @click="currentStep === steps.JOB_DETAILS || currentStep === steps.SALARY_INFO ? null : currentStep = steps.DESCRIPTION" 
               :class="[
                 'px-4 py-2 cursor-pointer flex items-center', 
                 currentStep === steps.DESCRIPTION ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500',
                 currentStep === steps.JOB_DETAILS || currentStep === steps.SALARY_INFO ? 'opacity-50 cursor-not-allowed' : ''
               ]"
               role="tab"
               :aria-selected="currentStep === steps.DESCRIPTION"
               :aria-disabled="currentStep === steps.JOB_DETAILS || currentStep === steps.SALARY_INFO"
               tabindex="0"
               @keydown.enter="currentStep === steps.JOB_DETAILS || currentStep === steps.SALARY_INFO ? null : currentStep = steps.DESCRIPTION"
               @keydown.space="currentStep === steps.JOB_DETAILS || currentStep === steps.SALARY_INFO ? null : currentStep = steps.DESCRIPTION">
               <font-awesome-icon :icon="['fas', 'file-alt']" class="mr-2" aria-hidden="true" />
               <span>Description & Requirements</span>
             </div>
             <div 
               @click="currentStep === steps.JOB_DETAILS || currentStep === steps.SALARY_INFO || currentStep === steps.DESCRIPTION ? null : currentStep = steps.OTHER_INFO" 
               :class="[
                 'px-4 py-2 cursor-pointer flex items-center', 
                 currentStep === steps.OTHER_INFO ? 'border-b-2 border-blue-500 text-blue-500 font-semibold' : 'text-gray-500',
                 currentStep === steps.JOB_DETAILS || currentStep === steps.SALARY_INFO || currentStep === steps.DESCRIPTION ? 'opacity-50 cursor-not-allowed' : ''
               ]"
               role="tab"
               :aria-selected="currentStep === steps.OTHER_INFO"
               :aria-disabled="currentStep === steps.JOB_DETAILS || currentStep === steps.SALARY_INFO || currentStep === steps.DESCRIPTION"
               tabindex="0"
               @keydown.enter="currentStep === steps.JOB_DETAILS || currentStep === steps.SALARY_INFO || currentStep === steps.DESCRIPTION ? null : currentStep = steps.OTHER_INFO"
               @keydown.space="currentStep === steps.JOB_DETAILS || currentStep === steps.SALARY_INFO || currentStep === steps.DESCRIPTION ? null : currentStep = steps.OTHER_INFO">
               <font-awesome-icon :icon="['fas', 'info-circle']" class="mr-2" aria-hidden="true" />
               <span>Other Information</span>
             </div>
           </div>
           <div class="ml-4">
             <PrimaryButton @click="toggleViewMode" type="button" class="bg-indigo-600 hover:bg-indigo-700 flex items-center">
               <font-awesome-icon :icon="['fas', 'eye']" class="mr-2" aria-hidden="true" />
               {{ viewingAllSections ? 'Tab View' : 'View All' }}
             </PrimaryButton>
           </div>
         </div>
        
        <form @submit.prevent="submit">
          <!-- All Sections View -->
          <div v-if="viewingAllSections" class="space-y-12">
            <!-- Basic Job Details Section -->
            <div class="space-y-6">
              <h3 class="text-lg font-medium text-gray-700 mb-4">Basic Job Information</h3>
              <div class="space-y-8">
                <!-- Job Identity Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="text-md font-medium text-gray-700 mb-3">Job Identity</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Job Title -->
                    <div>
                      <div class="flex items-center justify-between">
                        <InputLabel for="job_title" value="Job Title" />
                        <span class="text-xs text-red-500">*Required</span>
                      </div>
                      <div class="relative">
                        <TextInput
                          id="job_title"
                          type="text"
                          :class="[
                            'mt-1 block w-full',
                            form.errors.job_title ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''
                          ]"
                          v-model="form.job_title"
                          required
                          aria-required="true"
                          aria-describedby="job_title-help job_title-error"
                          autofocus/>
                        <div v-if="form.job_title" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                          <font-awesome-icon :icon="['fas', 'check']" class="text-green-500" aria-hidden="true" />
                        </div>
                      </div>
                      <p id="job_title-help" class="mt-1 text-xs text-gray-500">Enter a clear, specific job title (e.g., "Senior Software Engineer")</p>
                      <InputError id="job_title-error" class="mt-2" :message="form.errors.job_title" />
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
                  </div>
                </div>
                
                <!-- Job Classification Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="text-md font-medium text-gray-700 mb-3">Job Classification</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Job Type -->
                    <div>
                      <InputLabel for="job_type" value="Job Type" />
                      <select
                        id="job_type"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        v-model="form.job_type"
                        required>
                        <option value="">Select Job Type</option>
                        <option value="Full-time">Full-time</option>
                        <option value="Part-time">Part-time</option>
                        <option value="Contract">Contract</option>
                        <option value="Temporary">Temporary</option>
                        <option value="Internship">Internship</option>
                      </select>
                      <InputError class="mt-2" :message="form.errors.job_type" />
                    </div>
                    
                    <!-- Experience Level -->
                    <div>
                      <InputLabel for="experience_level" value="Experience Level" />
                      <select
                        id="experience_level"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        v-model="form.experience_level"
                        required>
                        <option value="">Select Experience Level</option>
                        <option value="Entry Level">Entry Level</option>
                        <option value="Mid Level">Mid Level</option>
                        <option value="Senior Level">Senior Level</option>
                        <option value="Executive">Executive</option>
                      </select>
                      <InputError class="mt-2" :message="form.errors.experience_level" />
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
                  </div>
                </div>
                
                <!-- Position Details Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="text-md font-medium text-gray-700 mb-3">Position Details</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Number of Vacancies -->
                    <div>
                      <InputLabel for="vacancy" value="Number of Vacancies" />
                      <TextInput
                        id="vacancy"
                        type="number"
                        class="mt-1 block w-full"
                        v-model="form.vacancy"
                        required
                        min="1"/>
                      <InputError class="mt-2" :message="form.errors.vacancy" />
                    </div>
                    
                    <!-- Work Environment -->
                    <div>
                      <InputLabel for="work_environment" value="Work Environment" />
                      <select
                        id="work_environment"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        v-model="form.work_environment"
                        required>
                        <option value="">Select Work Environment</option>
                        <option value="On-site">On-site</option>
                        <option value="Remote">Remote</option>
                        <option value="Hybrid">Hybrid</option>
                      </select>
                      <InputError class="mt-2" :message="form.errors.work_environment" />
                    </div>
                  </div>
                </div>
                
                <!-- Location Information Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="text-md font-medium text-gray-700 mb-3">Location Information</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Job Location -->
                    <div>
                      <InputLabel for="location" value="Job Location" />
                      <TextInput
                        id="location"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.location"
                        required/>
                      <InputError class="mt-2" :message="form.errors.location" />
                    </div>
                    
                    <!-- Branch Location -->
                    <div>
                      <InputLabel for="branch_location" value="Branch Location" />
                      <TextInput
                        id="branch_location"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.branch_location"
                        required/>
                      <InputError class="mt-2" :message="form.errors.branch_location" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Salary Information Section -->
            <div class="space-y-6">
              <h3 class="text-lg font-medium text-gray-700 mb-4">Salary Details</h3>
              <div class="space-y-8">
                <!-- Salary Type Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="text-md font-medium text-gray-700 mb-3">Salary Type</h4>
                  <div>
                    <InputLabel for="salary_type" value="Salary Type" />
                    <select
                      id="salary_type"
                      class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                      v-model="form.salary_type">
                      <option value="">Select Salary Type</option>
                      <option value="monthly">Monthly</option>
                      <option value="annual">Annual</option>
                      <option value="hourly">Hourly</option>
                      <option value="daily">Daily</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.salary_type" />
                  </div>
                </div>
                
                <!-- Salary Options Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="text-md font-medium text-gray-700 mb-3">Salary Options</h4>
                  
                  <!-- Negotiable Salary Checkbox -->
                  <div class="flex items-center mb-4">
                    <input
                      id="is_salary_negotiable"
                      type="checkbox"
                      class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                      v-model="form.is_salary_negotiable"
                      @change="validateSalary"/>
                    <label for="is_salary_negotiable" class="ml-2 block text-sm text-gray-900">
                      Salary is negotiable
                    </label>
                  </div>
                  
                  <!-- Salary Range -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Minimum Salary -->
                    <div>
                      <div class="flex items-center justify-between">
                        <InputLabel for="min_salary" value="Minimum Salary" />
                        <div class="flex items-center">
                          <font-awesome-icon 
                            :icon="['fas', 'question-circle']"
                            class="text-gray-400 mr-1 cursor-help"
                            title="The minimum salary you're offering for this position"
                            aria-hidden="true" />
                          <span v-if="!form.is_salary_negotiable" class="text-xs text-red-500">*Required</span>
                        </div>
                      </div>
                      <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                          <span class="text-gray-500 sm:text-sm">₱</span>
                        </div>
                        <TextInput
                          id="min_salary"
                          type="number"
                          :class="[
                            'pl-7 block w-full',
                            salaryError && !form.is_salary_negotiable ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''
                          ]"
                          v-model="form.min_salary"
                          :disabled="form.is_salary_negotiable"
                          @input="validateSalary"
                          min="5000"
                          max="100000"
                          aria-required="true"
                          aria-describedby="min_salary-help min_salary-error"/>
                        <div v-if="form.min_salary && !salaryError && !form.is_salary_negotiable" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                          <font-awesome-icon :icon="['fas', 'check']" class="text-green-500" aria-hidden="true" />
                        </div>
                        <div v-if="salaryError && !form.is_salary_negotiable" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                          <font-awesome-icon :icon="['fas', 'exclamation-triangle']" class="text-red-500" aria-hidden="true" />
                        </div>
                      </div>
                      <p id="min_salary-help" class="mt-1 text-xs text-gray-500">Enter amount between ₱5,000 and ₱100,000</p>
                      <p v-if="salaryError && !form.is_salary_negotiable" id="min_salary-error" class="mt-1 text-xs text-red-500">{{ salaryError }}</p>
                      <InputError class="mt-2" :message="form.errors.min_salary" />
                    </div>
                    
                    <!-- Maximum Salary -->
                    <div>
                      <div class="flex items-center justify-between">
                        <InputLabel for="max_salary" value="Maximum Salary" />
                        <div class="flex items-center">
                          <font-awesome-icon 
                            :icon="['fas', 'question-circle']"
                            class="text-gray-400 mr-1 cursor-help"
                            title="The maximum salary you're offering for this position"
                            aria-hidden="true" />
                          <span v-if="!form.is_salary_negotiable" class="text-xs text-red-500">*Required</span>
                        </div>
                      </div>
                      <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                          <span class="text-gray-500 sm:text-sm">₱</span>
                        </div>
                        <TextInput
                          id="max_salary"
                          type="number"
                          :class="[
                            'pl-7 block w-full',
                            salaryError && !form.is_salary_negotiable ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''
                          ]"
                          v-model="form.max_salary"
                          :disabled="form.is_salary_negotiable"
                          @input="validateSalary"
                          min="5000"
                          max="100000"
                          aria-required="true"
                          aria-describedby="max_salary-help max_salary-error"/>
                        <div v-if="form.max_salary && !salaryError && !form.is_salary_negotiable" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                          <font-awesome-icon :icon="['fas', 'check']" class="text-green-500" aria-hidden="true" />
                        </div>
                        <div v-if="salaryError && !form.is_salary_negotiable" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                          <font-awesome-icon :icon="['fas', 'exclamation-triangle']" class="text-red-500" aria-hidden="true" />
                        </div>
                      </div>
                      <p id="max_salary-help" class="mt-1 text-xs text-gray-500">Enter amount between ₱5,000 and ₱100,000</p>
                      <p v-if="salaryError && !form.is_salary_negotiable" id="max_salary-error" class="mt-1 text-xs text-red-500">{{ salaryError }}</p>
                      <InputError class="mt-2" :message="form.errors.max_salary" />
                    </div>
                  </div>
                  <p v-if="form.is_salary_negotiable" class="text-sm text-gray-500 italic mt-2">Salary will be displayed as "Negotiable" to applicants.</p>
                </div>
              </div>
              
              <!-- Salary Error Message -->
              <div v-if="salaryError" class="text-red-500 text-sm">
                {{ salaryError }}
              </div>
            </div>
            
            <!-- Description & Requirements Section -->
            <div class="space-y-6">
              <h3 class="text-lg font-medium text-gray-700 mb-4">Job Description & Requirements</h3>
              <div class="space-y-8">
                <!-- Job Description Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="text-md font-medium text-gray-700 mb-3">Job Description</h4>
                  <div>
                    <InputLabel for="description" value="Job Description" />
                    <RichTextEditor
                      id="description"
                      v-model="form.description"
                      class="mt-1 block w-full"
                      placeholder="Enter job description"/>
                    <p class="mt-1 text-xs text-gray-500">Provide a detailed description of the job responsibilities and duties.</p>
                    <InputError class="mt-2" :message="form.errors.description" />
                  </div>
                </div>
                
                <!-- Job Requirements Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="text-md font-medium text-gray-700 mb-3">Job Requirements</h4>
                  <div>
                    <InputLabel for="requirements" value="Job Requirements" />
                    <RichTextEditor
                      id="requirements"
                      v-model="form.requirements"
                      class="mt-1 block w-full"
                      placeholder="Enter job requirements"/>
                    <p class="mt-1 text-xs text-gray-500">List the qualifications, skills, and experience required for this position.</p>
                    <InputError class="mt-2" :message="form.errors.requirements" />
                  </div>
                </div>
                
                <!-- Skills Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="text-md font-medium text-gray-700 mb-3">Required Skills</h4>
                  <div>
                    <InputLabel for="skills" value="Skills" />
                    <div class="flex mt-1">
                      <TextInput
                        id="skills"
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
                    <div v-if="form.skills.length > 0" class="mt-3 flex flex-wrap gap-2">
                      <div 
                        v-for="(skill, index) in form.skills" 
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
                    <InputError class="mt-2" :message="form.errors.skills" />
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Other Information Section -->
            <div class="space-y-6">
              <h3 class="text-lg font-medium text-gray-700 mb-4">Additional Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-8">
                <!-- Posted By Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="text-md font-medium text-gray-700 mb-3">Contact Information</h4>
                  <div>
                    <InputLabel for="posted_by" value="Posted By" />
                    <TextInput
                      id="posted_by"
                      type="text"
                      class="mt-1 block w-full"
                      v-model="form.posted_by"
                      required/>
                    <InputError class="mt-2" :message="form.errors.posted_by" />
                  </div>
                </div>
                
                <!-- Application Deadline Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="text-md font-medium text-gray-700 mb-3">Application Timeline</h4>
                  <div>
                    <InputLabel for="application_deadline" value="Application Deadline" />
                    <TextInput
                      id="application_deadline"
                      type="date"
                      class="mt-1 block w-full"
                      v-model="form.application_deadline"
                      required
                      :min="minDate"/>
                    <InputError class="mt-2" :message="form.errors.application_deadline" />
                  </div>
                </div>
                
                <!-- Application Limit Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="text-md font-medium text-gray-700 mb-3">Application Settings</h4>
                  <div>
                    <InputLabel for="application_limit" value="Application Limit" />
                    <TextInput
                      id="application_limit"
                      type="number"
                      class="mt-1 block w-full"
                      v-model="form.application_limit"
                      min="1"/>
                    <p class="mt-1 text-xs text-gray-500">Maximum number of applications to accept (leave empty for unlimited)</p>
                    <InputError class="mt-2" :message="form.errors.application_limit" />
                  </div>
                </div>
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
                        <p>{{ form.experience_level || 'Not provided' }}</p>
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
                        <p class="text-sm font-medium text-gray-500">Branch Location</p>
                        <p>{{ form.branch_location || 'Not provided' }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500">Vacancies</p>
                        <p>{{ form.vacancy || 'Not provided' }}</p>
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
                      <div v-if="form.is_salary_negotiable">
                        <p class="text-sm font-medium text-gray-500">Salary</p>
                        <p>Negotiable</p>
                      </div>
                      <div v-else>
                        <p class="text-sm font-medium text-gray-500">Salary Range</p>
                        <p v-if="form.min_salary && form.max_salary">
                          ₱{{ form.min_salary }} - ₱{{ form.max_salary }}
                        </p>
                        <p v-else>Not provided</p>
                      </div>
                    </div>
                  </div>
                  
                  <div>
                    <h3 class="font-medium text-gray-700">Description & Requirements</h3>
                    <div class="mt-2">
                      <p class="text-sm font-medium text-gray-500">Job Description</p>
                      <div v-if="form.description" v-html="form.description"></div>
                      <p v-else>Not provided</p>
                    </div>
                    <div class="mt-4">
                      <p class="text-sm font-medium text-gray-500">Job Requirements</p>
                      <div v-if="form.requirements" v-html="form.requirements"></div>
                      <p v-else>Not provided</p>
                    </div>
                    <div class="mt-4">
                      <p class="text-sm font-medium text-gray-500">Skills</p>
                      <div v-if="form.skills.length > 0" class="flex flex-wrap gap-2 mt-1">
                        <span 
                          v-for="(skill, index) in form.skills" 
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
                        <p>{{ form.application_deadline || 'Not provided' }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500">Application Limit</p>
                        <p>{{ form.application_limit || 'Unlimited' }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="flex justify-end">
                <div class="flex space-x-3">
                  <PrimaryButton @click="saveDraft" type="button" class="bg-gray-500 hover:bg-gray-600">
                    Save as Draft
                  </PrimaryButton>
                  <PrimaryButton type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Posting...' : 'Post Job' }}
                  </PrimaryButton>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Tab View -->
          <!-- Basic Job Details Tab -->
          <div v-if="!viewingAllSections && currentStep === steps.JOB_DETAILS" class="space-y-6">
            <h3 class="text-lg font-medium text-gray-700 mb-4">Basic Job Information</h3>
            <div class="space-y-8">
              <!-- Job Identity Section -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-700 mb-3">Job Identity</h4>
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
                </div>
              </div>
              
              <!-- Job Classification Section -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-700 mb-3">Job Classification</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Job Type -->
                  <div>
                    <InputLabel for="job_type" value="Job Type" />
                    <select
                      id="job_type"
                      class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                      v-model="form.job_type"
                      required>
                      <option value="">Select Job Type</option>
                      <option value="Full-time">Full-time</option>
                      <option value="Part-time">Part-time</option>
                      <option value="Contract">Contract</option>
                      <option value="Temporary">Temporary</option>
                      <option value="Internship">Internship</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.job_type" />
                  </div>
                  
                  <!-- Experience Level -->
                  <div>
                    <InputLabel for="experience_level" value="Experience Level" />
                    <select
                      id="experience_level"
                      class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                      v-model="form.experience_level"
                      required>
                      <option value="">Select Experience Level</option>
                      <option value="Entry Level">Entry Level</option>
                      <option value="Mid Level">Mid Level</option>
                      <option value="Senior Level">Senior Level</option>
                      <option value="Executive">Executive</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.experience_level" />
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
                </div>
              </div>
              
              <!-- Position Details Section -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-700 mb-3">Position Details</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Number of Vacancies -->
                  <div>
                    <InputLabel for="vacancy" value="Number of Vacancies" />
                    <TextInput
                      id="vacancy"
                      type="number"
                      class="mt-1 block w-full"
                      v-model="form.vacancy"
                      required
                      min="1"/>
                    <InputError class="mt-2" :message="form.errors.vacancy" />
                  </div>
                  
                  <!-- Work Environment -->
                  <div>
                    <InputLabel for="work_environment" value="Work Environment" />
                    <select
                      id="work_environment"
                      class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                      v-model="form.work_environment"
                      required>
                      <option value="">Select Work Environment</option>
                      <option value="On-site">On-site</option>
                      <option value="Remote">Remote</option>
                      <option value="Hybrid">Hybrid</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.work_environment" />
                  </div>
                </div>
              </div>
              
              <!-- Location Information Section -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-700 mb-3">Location Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Job Location -->
                  <div>
                    <InputLabel for="location" value="Job Location" />
                    <TextInput
                      id="location"
                      type="text"
                      class="mt-1 block w-full"
                      v-model="form.location"
                      required/>
                    <InputError class="mt-2" :message="form.errors.location" />
                  </div>
                  
                  <!-- Branch Location -->
                  <div>
                    <InputLabel for="branch_location" value="Branch Location" />
                    <TextInput
                      id="branch_location"
                      type="text"
                      class="mt-1 block w-full"
                      v-model="form.branch_location"
                      required/>
                    <InputError class="mt-2" :message="form.errors.branch_location" />
                  </div>
                </div>
              </div>
            </div>
            
            <div class="flex justify-end">
              <PrimaryButton @click="goToNextStep" type="button">
                Next
              </PrimaryButton>
            </div>
          </div>
          
          <!-- Salary Information Tab -->
          <div v-if="!viewingAllSections && currentStep === steps.SALARY_INFO" class="space-y-6">
            <h3 class="text-lg font-medium text-gray-700 mb-4">Salary Details</h3>
            <div class="space-y-8">
              <!-- Salary Type Section -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-700 mb-3">Salary Type</h4>
                <div>
                  <InputLabel for="salary_type" value="Salary Type" />
                  <select
                    id="salary_type"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    v-model="form.salary_type">
                    <option value="">Select Salary Type</option>
                    <option value="monthly">Monthly</option>
                    <option value="annual">Annual</option>
                    <option value="hourly">Hourly</option>
                    <option value="daily">Daily</option>
                  </select>
                  <InputError class="mt-2" :message="form.errors.salary_type" />
                </div>
              </div>
              
              <!-- Salary Options Section -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-700 mb-3">Salary Options</h4>
                
                <!-- Negotiable Salary Checkbox -->
                <div class="flex items-center mb-4">
                  <input
                    id="is_salary_negotiable"
                    type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    v-model="form.is_salary_negotiable"
                    @change="validateSalary"/>
                  <label for="is_salary_negotiable" class="ml-2 block text-sm text-gray-900">
                    Salary is negotiable
                  </label>
                </div>
                
                <!-- Salary Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Minimum Salary -->
                  <div>
                    <InputLabel for="min_salary" value="Minimum Salary" />
                    <div class="relative mt-1">
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">₱</span>
                      </div>
                      <TextInput
                        id="min_salary"
                        type="number"
                        class="pl-7 block w-full"
                        v-model="form.min_salary"
                        :disabled="form.is_salary_negotiable"
                        @input="validateSalary"
                        min="5000"
                        max="100000"/>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Enter amount between ₱5,000 and ₱100,000</p>
                    <InputError class="mt-2" :message="form.errors.min_salary" />
                  </div>
                  
                  <!-- Maximum Salary -->
                  <div>
                    <InputLabel for="max_salary" value="Maximum Salary" />
                    <div class="relative mt-1">
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">₱</span>
                      </div>
                      <TextInput
                        id="max_salary"
                        type="number"
                        class="pl-7 block w-full"
                        v-model="form.max_salary"
                        :disabled="form.is_salary_negotiable"
                        @input="validateSalary"
                        min="5000"
                        max="100000"/>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Enter amount between ₱5,000 and ₱100,000</p>
                    <InputError class="mt-2" :message="form.errors.max_salary" />
                  </div>
                </div>
                <p v-if="form.is_salary_negotiable" class="text-sm text-gray-500 italic mt-2">Salary will be displayed as "Negotiable" to applicants.</p>
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
          <div v-if="!viewingAllSections && currentStep === steps.DESCRIPTION" class="space-y-6">
            <h3 class="text-lg font-medium text-gray-700 mb-4">Job Description & Requirements</h3>
            <div class="space-y-8">
              <!-- Job Description Section -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-700 mb-3">Job Description</h4>
                <div>
                  <InputLabel for="description" value="Job Description" />
                  <RichTextEditor
                    id="description"
                    v-model="form.description"
                    class="mt-1 block w-full"
                    placeholder="Enter job description"/>
                  <p class="mt-1 text-xs text-gray-500">Provide a detailed description of the job responsibilities and duties.</p>
                  <InputError class="mt-2" :message="form.errors.description" />
                </div>
              </div>
              
              <!-- Job Requirements Section -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-700 mb-3">Job Requirements</h4>
                <div>
                  <InputLabel for="requirements" value="Job Requirements" />
                  <RichTextEditor
                    id="requirements"
                    v-model="form.requirements"
                    class="mt-1 block w-full"
                    placeholder="Enter job requirements"/>
                  <p class="mt-1 text-xs text-gray-500">List the qualifications, skills, and experience required for this position.</p>
                  <InputError class="mt-2" :message="form.errors.requirements" />
                </div>
              </div>
              
              <!-- Skills Section -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-700 mb-3">Required Skills</h4>
                <div>
                  <InputLabel for="skills" value="Skills" />
                  <div class="flex mt-1">
                    <TextInput
                      id="skills"
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
                  <div v-if="form.skills.length > 0" class="mt-3 flex flex-wrap gap-2">
                    <div 
                      v-for="(skill, index) in form.skills" 
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
                  <InputError class="mt-2" :message="form.errors.skills" />
                </div>
              </div>
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
          <div v-if="!viewingAllSections && currentStep === steps.OTHER_INFO" class="space-y-6">
            <h3 class="text-lg font-medium text-gray-700 mb-4">Additional Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-8">
              <!-- Posted By Section -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-700 mb-3">Contact Information</h4>
                <div>
                  <InputLabel for="posted_by" value="Posted By" />
                  <TextInput
                    id="posted_by"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.posted_by"
                    required/>
                  <InputError class="mt-2" :message="form.errors.posted_by" />
                </div>
              </div>
              
              <!-- Application Deadline Section -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-700 mb-3">Application Timeline</h4>
                <div>
                  <InputLabel for="application_deadline" value="Application Deadline" />
                  <TextInput
                    id="application_deadline"
                    type="date"
                    class="mt-1 block w-full"
                    v-model="form.application_deadline"
                    required
                    :min="minDate"/>
                  <InputError class="mt-2" :message="form.errors.application_deadline" />
                </div>
              </div>
              
              <!-- Application Limit Section -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-700 mb-3">Application Settings</h4>
                <div>
                  <InputLabel for="application_limit" value="Application Limit" />
                  <TextInput
                    id="application_limit"
                    type="number"
                    class="mt-1 block w-full"
                    v-model="form.application_limit"
                    min="1"/>
                  <p class="mt-1 text-xs text-gray-500">Maximum number of applications to accept (leave empty for unlimited)</p>
                  <InputError class="mt-2" :message="form.errors.application_limit" />
                </div>
              </div>
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
                      <p>{{ form.experience_level || 'Not provided' }}</p>
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
                      <p class="text-sm font-medium text-gray-500">Branch Location</p>
                      <p>{{ form.branch_location || 'Not provided' }}</p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-500">Vacancies</p>
                      <p>{{ form.vacancy || 'Not provided' }}</p>
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
                    <div v-if="form.is_salary_negotiable">
                      <p class="text-sm font-medium text-gray-500">Salary</p>
                      <p>Negotiable</p>
                    </div>
                    <div v-else>
                      <p class="text-sm font-medium text-gray-500">Salary Range</p>
                      <p v-if="form.min_salary && form.max_salary">
                        ₱{{ form.min_salary }} - ₱{{ form.max_salary }}
                      </p>
                      <p v-else>Not provided</p>
                    </div>
                  </div>
                </div>
                
                <div>
                  <h3 class="font-medium text-gray-700">Description & Requirements</h3>
                  <div class="mt-2">
                    <p class="text-sm font-medium text-gray-500">Job Description</p>
                    <div v-if="form.description" v-html="form.description"></div>
                    <p v-else>Not provided</p>
                  </div>
                  <div class="mt-4">
                    <p class="text-sm font-medium text-gray-500">Job Requirements</p>
                    <div v-if="form.requirements" v-html="form.requirements"></div>
                    <p v-else>Not provided</p>
                  </div>
                  <div class="mt-4">
                    <p class="text-sm font-medium text-gray-500">Skills</p>
                    <div v-if="form.skills.length > 0" class="flex flex-wrap gap-2 mt-1">
                      <span 
                        v-for="(skill, index) in form.skills" 
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
                      <p>{{ form.application_deadline || 'Not provided' }}</p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-500">Application Limit</p>
                      <p>{{ form.application_limit || 'Unlimited' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="flex justify-between">
              <PrimaryButton 
                @click="goToPreviousStep" 
                type="button" 
                class="bg-gray-500 hover:bg-gray-600"
                aria-label="Go to previous step">
                <span>Previous</span>
              </PrimaryButton>
              <div class="flex space-x-3">
                <PrimaryButton 
                  @click="saveDraft" 
                  type="button" 
                  class="bg-gray-500 hover:bg-gray-600 flex items-center"
                  :disabled="isDraftSaving"
                  aria-label="Save job posting as draft">
                  <font-awesome-icon v-if="isDraftSaving" :icon="['fas', 'spinner']" class="mr-2 animate-spin" aria-hidden="true" />
                  <span>{{ isDraftSaving ? 'Saving...' : 'Save as Draft' }}</span>
                </PrimaryButton>
                <PrimaryButton 
                  type="submit" 
                  :disabled="isSubmitting || form.processing"
                  class="flex items-center"
                  aria-label="Submit job posting">
                  <font-awesome-icon v-if="isSubmitting || form.processing" :icon="['fas', 'spinner']" class="mr-2 animate-spin" aria-hidden="true" />
                  <span>{{ isSubmitting || form.processing ? 'Posting...' : 'Post Job' }}</span>
                </PrimaryButton>
              </div>
            </div>
          </div>
        </form>
      </div>
    </Container>
  </AppLayout>
</template>