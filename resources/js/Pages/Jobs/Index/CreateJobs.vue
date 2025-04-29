<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, usePage, useForm  } from '@inertiajs/vue3'
import Container from '@/Components/Container.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
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
    min_salary: 5000,  
    max_salary: 100000, 
    job_type: '',
    experience_level: '',
    description: '',
    skills: [],
    sector: '', 
    category: '',
    requirements: '',
});

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
    form.post(route('jobs.store', { user: page.props.auth.user.id }), {
        onSuccess: () => {
            // form.reset();
            router.visit(route('jobs', { user: page.props.auth.user.id }));
        },
        onError: (errors) => {
            console.log('Validation errors:', errors);
        }
    });

    console.log('Route:', route('jobs.store', { user: page.props.auth.user.id }));
}   

</script>


<template>
   <AppLayout>
        <template >
            Jobs
        </template>
        


        <Container class="py-15">


            <!-- <PrimaryButton @click="createJob()" class="">Post Job</PrimaryButton> --> 
            <div class="mt-8">
                <FormSection @submitted="createJob()">
                    <template #title>
                        Post a New Job
                    </template>

                    <template #description>
                        Fill in the detailcs below to post a new job.
                    </template>


                    <template #form>

                        <div class=" col-span-6 sm:col-span-4">
                            <InputLabel for="job_title" value="Job Title" class="mb-2"/>
                            <TextInput id="job_title" v-model="form.job_title" type="text" placeholder="Job Title" class="mt-1 mb-5 block w-full p-2 border rounded-lg" required />
                            <InputError :message="form.errors.job_title" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-1">
                                <InputLabel for="location" value="Job Location" class="mb-2"/>
                                <TextInput id="location" v-model="form.location" type="text" placeholder="Job Location" class="w-full p-2 border rounded-lg mt-1" required />
                                <InputError :message="form.errors.location" class="mt-2" />
                            </div>

                            <div class="mx-20 col-span-1">
                                <InputLabel for="vacancy" value="No. of Vacancies" class="mb-2" />
                                <TextInput id="vacancy" v-model="form.vacancy" type="number" placeholder="No. of Vacancies" class="w-80 mt-1 p-2 border rounded-lg" required />
                                <InputError :message="form.errors.vacancy" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mt-4">
                            <div class="col-span-1">
                                <InputLabel for="sector" value="Sector" class="mb-2"/>
                                <select v-model="form.sector" id="sector" class="w-80 mt-1 mb-2 p-2 border rounded-lg" required>
                                    <option value="" disabled class="text-gray-400">Select Sector</option>
                                    <option v-for="sector in props.sectors" :key="sector.id" :value="sector.id">
                                        {{ sector.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.category" class="mt-2" />
                            </div>

                            <div class="mx-2 col-span-1">
                                <InputLabel for="category" value="Category" class="mb-2"/>
                                <select v-model="form.category" id="category" class="w-80 mt-1 mb-2 p-2 border rounded-lg" :disabled="!form.sector" required>
                                    <option value="" disabled class="text-gray-400">Select Category</option>
                                    <option v-for="category in availableCategories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.category" class="mt-2" />
                                <p class="text-red-500 text-sm mt-1">{{ form.errors.category }}</p>

                            </div>

                            <!-- Example minimum and maximum salary range  -->
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

                        <div class="grid grid-cols-2 gap-4 mt-4 place-items-center">
                            <div class="col-span-1">
                                <InputLabel for="job_type" value="Job Type" class="mb-2"/>
                                <select v-model="form.job_type" id="job_type" class="w-80 mt-1 mb-2 p-2 border rounded-lg">
                                    <option value="">Select Employment Type</option>
                                    <option value="full-time">Full-Time</option>
                                    <option value="part-time">Part-Time</option>
                                </select>
                                <InputError :message="form.errors.job_type" class="mt-2" />
                            </div>

                            <div class="col-span-1">
                                <InputLabel for="experience_level" value="Experience Level" class="mb-2" />
                                <select v-model="form.experience_level" id="experience_level" class="w-80 mt-1 mb-2 p-2 border rounded-lg">
                                    <option value="">Select Experience Level</option>
                                    <option value="entry-level">Entry-level</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="mid-level">Mid-level</option>
                                    <option value="senior-executive">Senior/Executive-level</option>
                                </select>
                                <InputError :message="form.errors.experience_level" class="mt-2" />
                            </div>
                        </div>

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
                    </template>
                    <template #actions>
                        <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Post Job
                        </PrimaryButton>
                    </template>
                </FormSection>


            </div>
 
        </Container>
   
    </AppLayout>
</template>
