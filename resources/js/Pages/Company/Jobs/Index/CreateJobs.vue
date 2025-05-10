<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, usePage, useForm  } from '@inertiajs/vue3'
import Container from '@/Components/Container.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { ref, watch } from 'vue';


const page = usePage()

const props = defineProps ({
    jobs: Array,
    sectors: Array,
    categories: Array,
})


console.log('User ID:', page.props);

const form = useForm({
    job_title: '', 
    location: '',
    vacancy: '',
    min_salary: '',  
    max_salary: '', 
    job_type: '',
    experience_level: '',
    description: '',
    skills: [],
    sector: '', 
    category: '',
    requirements: '',
    branch_location: '',
    applicants_limit: '',
    expiration_date: '',
    posted_by: `${page.props.auth.user.company_hr_first_name} ${page.props.auth.user.company_hr_last_name}`,
});

// Tabs setup
const activeTab = ref('basic');
const tabs = [
    { name: 'Basic Job Details', id: 'basic' },
    { name: 'Job Description & Requirements', id: 'description' },
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
    if (form.min_salary < 5000 || form.min_salary > 100000) {
        salaryError.value = 'Minimum salary must be between 5,000 and 100,000';
    } else if (form.max_salary < 5000 || form.max_salary > 100000) {
        salaryError.value = 'Maximum salary must be between 5,000 and 100,000';
    } else if (form.min_salary > form.max_salary) {
        salaryError.value = 'Minimum salary cannot be greater than maximum salary';
    } else {
        salaryError.value = '';
    }
};
// End of Salary Setup

const newSkill = ref('');


const addSkill = () => {
    if (newSkill.value.trim() !== '') {
        form.skills.push(newSkill.value.trim());
        newSkill.value = '';
    }
};

const removeSkill = (index) => {
    form.skills.splice(index, 1);
};


const createJob = () => {
    console.log('Form data:', form);
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

                    <template #description>
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
                            <div class=" col-span-6 sm:col-span-4">
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


                            <div class="grid grid-cols-4 gap-4 mt-4">
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
                                    <InputLabel for="job_type" value="Job Type" class="mb-2"/>
                                    <select v-model="form.job_type" id="job_type" class="w-full mt-1 mb-2 p-2 border rounded-lg">
                                        <option value="">Select Employment Type</option>
                                        <option value="full-time">Full-Time</option>
                                        <option value="part-time">Part-Time</option>
                                    </select>
                                    <InputError :message="form.errors.job_type" class="mt-2" />
                                </div>

                                <div class="col-span-1">
                                        <InputLabel for="experience_level" value="Experience Level" class="mb-2" />
                                        <select v-model="form.experience_level" id="experience_level" class="w-full mt-1 mb-2 p-2 border rounded-lg">
                                            <option value="">Select Experience Level</option>
                                            <option value="entry-level">Entry-level</option>
                                            <option value="intermediate">Intermediate</option>
                                            <option value="mid-level">Mid-level</option>
                                            <option value="senior-executive">Senior/Executive-level</option>
                                        </select>
                                        <InputError :message="form.errors.experience_level" class="mt-2" />
                                    </div>
                            </div>
                            
                            <div class="grid grid-cols-5 gap-4 mt-4">
                                <div class="col-span-2">    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="col-span-1">
                                            <InputLabel for="min_salary" value="Minimum Salary" class="mb-2" />
                                            <TextInput 
                                                id="min_salary" 
                                                v-model="form.min_salary" 
                                                type="text" 
                                                class="mt-1 p-2 border rounded-lg w-full text-center" 
                                                required
                                                min="5000"
                                                max="100000"
                                                @input="validateSalary"
                                            />
                                            <InputError :message="form.errors.min_salary" class="mt-2" />
                                        </div>
                                    
                                        <div class="col-span-1">
                                            <InputLabel for="max_salary" value="Maximum Salary" class="mb-2" />
                                            <TextInput 
                                                id="max_salary" 
                                                v-model="form.max_salary" 
                                                type="text" 
                                                class="mt-1 p-2 border rounded-lg w-full text-center" 
                                                required
                                                min="5000"
                                                max="100000"
                                                @input="validateSalary"
                                            />
                                            <InputError :message="form.errors.max_salary" class="mt-2" />
                                        </div>

                                        <div class="col-span-2">
                                            <p class="text-gray-500 text-sm ">Salary Range: Min 5,000 - Max 100,000</p>
                                            <p v-if="salaryError" class="text-red-500 text-sm mt-1">{{ salaryError }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-1">
                                    <InputLabel for="vacancy" value="No. of Vacancies" class="mb-2" />
                                    <TextInput 
                                    id="vacancy"    
                                    v-model="form.vacancy" 
                                    type="number" 
                                    placeholder="No. of Vacancies" 
                                    class="w-full mt-1 p-2 border rounded-lg" 
                                    required />
                                    <InpError :message="form.errors.vacancy" class="mt-2" />
                                </div>
                                
                                <div class="col-span-2">
                                    <InputLabel for="branch_location" value="Branch Location" class="mb-2" />
                                    <TextInput 
                                        id="branch_location"    
                                        v-model="form.branch_location" 
                                        type="text" 
                                        placeholder="e.g. Lagao  Branch" 
                                        class="w-full mt-1 p-2 border rounded-lg" 
                                        required />
                                    <InputError :message="form.errors.branch_location" class="mt-2" />
                                </div>
                                
                            </div>
                                
                            <div class="grid grid-cols-1 mt-4 gap-4">
                                <div class="col-span-1">
                                    <InputLabel for="location" value="Job Location" class="mb-2"/>
                                    <TextInput 
                                        id="location" 
                                        v-model="form.location" 
                                        type="text" 
                                        placeholder="e.g. J.Catolico Avenue, General Santos City, South Cotabato" 
                                        class="w-full p-2 border rounded-lg mt-1" 
                                        required />
                                    <InputError :message="form.errors.location" class="mt-2" />
                                </div>
                                    

                            </div>
                        </div>
                            
                                
                        <div v-if="activeTab === 'description'">
                            <div class="col-span-6 sm:col-span-4 mt-5" >
                                <InputLabel for="description" value="Job Description" />
                                <textarea id="description" v-model="form.description" placeholder="Job Description" class="w-full p-2 border rounded-lg h-40 mt-2 resize-none" required></textarea>
                                <InputError :message="form.errors.description" class="mt-2" />
                            </div>

                            <div class="col-span-6 sm:col-span-4 mt-5" >
                                <InputLabel for="requirements" value="Job Requirements" />
                                <textarea id="requirements" v-model="form.requirements" placeholder="Job Requirements" class="w-full p-2 border rounded-lg h-40 mt-2  resize-none" required></textarea>
                                <InputError :message="form.errors.requirements" class="mt-2" />
                            </div>

                            <div class="mt-4">
                                <InputLabel value="Skills" />
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span v-for="(skill, index) in form.skills" :key="index" class="px-3 py-1 bg-gray-200 rounded-full flex items-center">
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
                                <InputError :message="form.errors.posted_by" class="mt-2" />
                            </div>

                            <!-- Application Deadline -->
                            <div class="col-span-6 sm:col-span-4 mt-5">
                                <InputLabel for="expiration_date" value="Application Deadline" />
                                <Datepicker 
                                    v-model="form.expiration_date" 
                                    :enable-time-picker="false"
                                    input-class-name="w-full p-2 border rounded-lg mt-2"
                                    :min-date= "today"
                                    placeholder="Select date"
                                    required />
                                <InputError :message="form.errors.expiration_date" class="mt-2" />
                            </div>

                            <!-- Application Limit -->
                            <div class="col-span-6 sm:col-span-4 mt-5">
                                <InputLabel for="applicants_limit" value="Application Limit (optional)" />
                                <TextInput id="applicants_limit" v-model="form.applicants_limit" type="number" min="1" class="w-full p-2 border rounded-lg mt-2" />
                                <InputError :message="form.errors.applicants_limit" class="mt-2" />
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
