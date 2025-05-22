<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, usePage, useForm  } from '@inertiajs/vue3'
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
import { ref, watch, computed, watchEffect } from 'vue';


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
const activeTab = ref('basic');
const tabs = [
    { name: 'Basic Job Details', id: 'basic' },
    { name: 'Job description & requirements', id: 'description' },
    { name: 'Other informations', id: 'others' },
];
// Navigation between tabs
const goToNextTab = () => {
    const currentIndex = tabs.findIndex(tab => tab.id === activeTab.value);
    if (currentIndex < tabs.length - 1) {
        activeTab.value = tabs[currentIndex + 1].id;
    }
};

const goToPreviousTab = () => {
    const currentIndex = tabs.findIndex(tab => tab.id === activeTab.value);
    if (currentIndex > 0) {
        activeTab.value = tabs[currentIndex - 1].id;
    }
};
// End for the tabs setup


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

// End of Salary Setup


console.log(form.program_id); // It should print something like [1, 2]

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

// Extract selected program IDs (new function)
const extractProgramIds = () => {
    // Extract only the ids from the selected programs
    form.program_id = form.program_id.map(program => program.id);  // Make sure we're only passing program ids (not full objects)
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
        <Container class="py-15">
            <div class="mt-8">
                <FormSection @submitted="createJob()">
                    <template #title>
                        Post a New Job
                    </template>

                    <template #job_description>
                        Fill in the detailc below to post a new job.
                    </template>


                    <template #form>
                        <div class="tabs mb-4">
                            <div class="flex justify-center space-x-8">
                                <div 
                                    v-for="tab in tabs" 
                                    :key="tab.id" 
                                    @click="activeTab = tab.id" 
                                    :class="{'font-bold border-b-2 border-blue-600': activeTab === tab.id, 'cursor-pointer': true}"
                                >
                                    {{ tab.name }}
                                </div>
                            </div>
                        </div>

                        <div class="w-full border-t border-gray-300 mb-6"></div>

                        <div v-if="activeTab === 'basic'">    
                            <div class=" col-span-6 sm:col-span-4 mx-6">
                                <InputLabel for="job_title" value="Job Title" class="mb-2"/>
                                <TextInput 
                                    id="job_title" 
                                    v-model="form.job_title" 
                                    type="text" 
                                    placeholder="Job Title" 
                                    class="mt-1 mb-5 block w-full p-2 border rounded-lg" 
                                    required />
                                <InputError :message="form.errors.job_title" class="mt-2" />
                            </div>


                            <div class="grid grid-cols-3 gap-4 mx-6 mt-4">
                                
                                <div class="col-span-1">
                                    <div class="flex items-center mb-2">
                                        <InputLabel for="program_id" value="Program Graduated" class="mb-0" />
                                        <div class="relative ml-2 group cursor-pointer">
                                            <span class="text-blue-500 font-bold">?</span>
                                            <div
                                                class="absolute z-10 w-64 p-2 text-sm text-white bg-gray-800 rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 -top-2 left-6"
                                            >
                                                Select the degree or program related to the job. (e.g., BSIT, BEED, BSA).
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <MultiSelect
                                        v-model="form.program_id"
                                        :options="programs"
                                        :multiple="true"
                                        label="name"
                                        track-by="id"
                                        placeholder="Select programs"
                                    />
  
                                    <InputError :message="form.errors.program_id" class="mt-2" />
                                </div>


                                <div class="col-span-1">
                                    <InputLabel for="job_employment_type" value="Job Type" class="mb-2"/>
                                    <select v-model="form.job_employment_type" id="job_employment_type" class="w-full mt-1 mb-2 p-2 border rounded-lg">
                                        <option value="">Select Employment Type</option>
                                        <option value="Full-Time">Full-Time</option>
                                        <option value="Part-time">Part-Time</option>
                                        <option value="Contract">Contract</option>
                                        <option value="Freelance">Freelance</option>
                                        <option value="Internship">Internship</option>
                                    </select>
                                    <InputError :message="form.errors.job_employment_type" class="mt-2" />
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-4 mx-6 mt-4">
                                <div class="col-span-1">
                                    <InputLabel for="job_work_environment" value="Work Environment Type" class="mb-2" />
                                    <select v-model="form.job_work_environment" id="job_work_environment" class="w-full mt-1 mb-2 p-2 border rounded-lg">
                                        <option value="">Select Work Environment</option>
                                        <option value="Remote">Remote</option>
                                        <option value="Hybrid">Hybrid</option>
                                        <option value="On-site">On-site</option>
                                    </select>
                                    <InputError :message="form.errors.job_work_environment" class="mt-2" />
                                </div>
                                <div class="col-span-1">
                                    <InputLabel for="job_experience_level" value="Experience Level" class="mb-2" />
                                    <select v-model="form.job_experience_level" id="job_experience_level" class="w-full mt-1 mb-2 p-2 border rounded-lg">
                                        <option value="">Select Experience Level</option>
                                        <option value="Entry-level">Entry-level</option>
                                        <option value="Intermediate">Intermediate</option>
                                        <option value="Mid-level">Mid-level</option>
                                        <option value="Senior-executive">Senior/Executive-level</option>
                                    </select>
                                    <InputError :message="form.errors.job_experience_level" class="mt-2" />
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-3 gap-4 mx-6 mt-4">
                                <div class="col-span-1">
                                    <InputLabel for="sector" value="Sector" class="mb-2"/>
                                    <select v-model="form.sector" id="sector" class="w-full mt-1 mb-2 p-2 border rounded-lg" required>
                                        <option value="" disabled class="text-gray-400">Select Sector</option>
                                        <option v-for="sector in props.sectors" :key="sector.id" :value="sector.id">
                                            {{ sector.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.category" class="mt-2" />
                                </div>

                                <div class="col-span-1">
                                    <InputLabel for="category" value="Category" class="mb-2"/>
                                    <select v-model="form.category" id="category" class="w-full mt-1 mb-2 p-2 border rounded-lg" :disabled="!form.sector" required>
                                        <option value="" disabled class="text-gray-400">Select Category</option>
                                        <option v-for="category in availableCategories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.category" class="mt-2" />
                                    <p class="text-red-500 text-sm mt-1">{{ form.errors.category }}</p>
                                </div>
                                
                                <div class="col-span-1">
                                    <InputLabel for="job_vacancies" value="No. of Vacancies" class="mb-2" />
                                    <TextInput 
                                    id="job_vacancies"    
                                    v-model="form.job_vacancies" 
                                    type="number" 
                                    placeholder="No. of Vacancies" 
                                    class="w-50 mt-1 p-2 border rounded-lg" 
                                    required />
                                    <InputError :message="form.errors.job_vacancies" class="mt-2" />
                                </div>
                            </div>
                            
                            <div class="w-full border-t border-gray-300 my-6"></div>
                            
                            <div class="grid grid-cols-1 gap-4 mx-6 mt-4">
                                <h2 class="font-bold">Wage/Salary per Month</h2>
                            </div>

                            <div class="grid grid-cols-5 gap-4 mx-6 mt-4">
                                <div class="col-span-3">
                                    <div class="flex items-center mb-2">
                                        <InputLabel for="job_min_salary" value="Salary Range" class="mb-0" />
                                    </div>

                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                           <select v-model="form.job_salary_type" id="job_salary_type" class="w-full mt-1 mb-2 p-2 border rounded-lg">
                                                <option value="">Select Salary Type</option>
                                                <option value="monthly">Monthly</option>
                                                <option value="weekly">Weekly</option>
                                                <option value="hourly">Hourly</option>
                                            </select>
                                            <InputError :message="form.errors.job_salary_type" class="mt-2" />
                                        </div>
                                        <div>
                                            <TextInput 
                                                id="job_min_salary" 
                                                v-model="form.job_min_salary"
                                                :disabled="form.is_negotiable"
                                                placeholder="Minimum Salary" 
                                                type="number"
                                                class="mt-1 p-2 border rounded-lg w-full text-center" 
                                                min="5000"
                                                max="100000"
                                                @input="validateSalary"
                                            />
                                            <InputError :message="form.errors.job_min_salary" class="mt-2" />
                                        </div>

                                        <div>
                                            <TextInput 
                                                id="job_max_salary" 
                                                v-model="form.job_max_salary" 
                                                :disabled="form.is_negotiable"
                                                placeholder="Maximum Salary"
                                                type="number" 
                                                class="mt-1 p-2 border rounded-lg w-full text-center" 
                                                min="5000"
                                                max="100000"
                                                @input="validateSalary"
                                            />
                                            <InputError :message="form.errors.job_max_salary" class="mt-2" />
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <label class="inline-flex items-center">
                                            <input
                                                type="checkbox"
                                                v-model="form.is_negotiable"
                                                class="form-checkbox text-blue-600"
                                            />
                                            <span class="ml-2 text-sm text-gray-700">Salary is negotiable</span>
                                        </label>
                                    </div>

                                    <p class="text-gray-500 text-sm mt-2">Salary Range: Min ₱5,000 - Max ₱100,000</p>
                                    <p v-if="salaryError" class="text-red-500 text-sm mt-1">{{ salaryError }}</p>
                                </div>
                            </div>

                            <div class="w-full border-t border-gray-300 my-6"></div>

                            <div class="grid grid-cols-1 gap-4 mx-6 mt-4">
                                <h2 class="font-bold">Workplace job_location Details</h2>
                            </div>
                            
                            <div class="grid grid-cols-2 mt-4 mx-6 gap-4">
                                <div class="col-span-1">
                                    <InputLabel for="job_location" value="Job job_location" class="mb-2"/>
                                    <TextInput 
                                    id="job_location" 
                                    v-model="form.job_location" 
                                    type="text" 
                                    placeholder="e.g. J.Catolico Avenue, General Santos City, South Cotabato" 
                                    class="w-full p-2 border rounded-lg mt-1" 
                                    required />
                                    <InputError :message="form.errors.job_location" class="mt-2" />
                                </div>

                            </div>
                        </div>
                            
                                
                        <div v-if="activeTab === 'description'">
                            <div class="col-span-6 sm:col-span-4 mt-5" >
                                <InputLabel for="job_description" value="Job job_description" />
                                <RichTextEditor id="job_description" v-model="form.job_description" placeholder="Write a detailed job job_description..." class="mt-2" required></RichTextEditor>
                                <InputError :message="form.errors.job_description" class="mt-2" />
                            </div>

                            <div class="col-span-6 sm:col-span-4 mt-5" >
                                <InputLabel for="job_requirements"  value="job_requirements/Qualifications" />
                                <RichTextEditor id="job_requirements" v-model="form.job_requirements" placeholder="List the required qualifications..." class="mt-2" required></RichTextEditor>
                                <InputError :message="form.errors.job_requirements" class="mt-2" />
                            </div>
                            <div class="mt-4">
                                <InputLabel value="related_skills" />
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span v-for="(skill, index) in form.related_skills" :key="index" class="px-3 py-1 bg-gray-200 rounded-full flex items-center">
                                        {{ skill }}
                                        <button @click="removeSkill(index)" class="ml-2 text-red-500 font-bold">×</button>
                                    </span>
                                </div>
                                <div class="mt-2 flex">
                                    <TextInput v-model="newSkill" type="text" placeholder="Add a skill" class="p-2 border rounded-lg w-full" />
                                    <button @click="addSkill" type="button" class="ml-2 px-4 py-2 bg-blue-600 text-white rounded-lg">+</button>
                                    <InputError :message="form.errors.newSkill" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div v-if="activeTab === 'others'">
                            <!-- Posted By -->
                            <div class="col-span-6 sm:col-span-4 mt-5">
                                <InputLabel for="posted_by" value="Posted By" />
                                <TextInput id="posted_by" v-model="form.posted_by" class="w-full p-2 border rounded-lg mt-2" required disabled />
                            </div>

                            <!-- Application Deadline -->
                            <div class="col-span-6 sm:col-span-4 mt-5">
                                <InputLabel for="job_deadline" value="Application Deadline" />
                                <Datepicker 
                                    v-model="form.job_deadline" 
                                    :enable-time-picker="false"
                                    input-class-name="w-full p-2 border rounded-lg mt-2"
                                    :min-date= "today"
                                    placeholder="Select date"
                                    required />
                                <InputError :message="form.errors.job_deadline" class="mt-2" />
                            </div>

                            <!-- Application Limit -->
                            <div class="col-span-6 sm:col-span-4 mt-5">
                                <InputLabel for="job_application_limit" value="Application Limit (optional)" />
                                <TextInput id="job_application_limit" v-model="form.job_application_limit" type="number" min="1" class="w-full p-2 border rounded-lg mt-2" />
                                <InputError :message="form.errors.job_application_limit" class="mt-2" />
                            </div>
                        </div>
                        
                        
                    </template>
                    <template #actions>
                        <!-- NAVIGATION BUTTONS -->
                        <div class="flex justify-between gap-4 mt-10">
                            <button
                                type="button"
                                @click="goToPreviousTab"
                                class="bg-gray-200 hover:bg-gray-300 text-black font-medium py-2 px-4 rounded"
                                :hidden="activeTab === 'basic'"
                            >
                                Previous
                            </button>
                            <button
                                type="button"
                                @click="goToNextTab"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded"
                                :hidden="activeTab === 'others'"
                            >
                                Next
                            </button>
                            <PrimaryButton 
                            v-if ="activeTab === 'others'"
                            type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Post Job
                        </PrimaryButton>
                     </div>
                    </template>
                </FormSection>


            </div>
 
        </Container>
   
    </AppLayout>
</template>
