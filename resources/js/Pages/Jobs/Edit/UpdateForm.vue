<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ActionMessage from '@/Components/ActionMessage.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
    job: Object
});

const form = useForm({
    job_name: props.job.job_title,
    description: props.job.description,
    requirements: props.job.job_requirements || '',
    location: props.job.location || '',
    job_type: props.job.job_type || '',
    experience_level: props.job.experience_level || '',
    vacancy: props.job.vacancy || 1,
});

const submitForm = () => {
    form.put(route('jobs.update', {job: props.job.id}), {
        preserveScroll: true
    });
};
</script>

<template>
    <div class="p-6">
        <div class="border-b border-gray-200 pb-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-pen-to-square text-blue-500 mr-2"></i>
                Update Job Information
            </h3>
            <p class="text-sm text-gray-600 mt-2">
                Update your job posting details below. All fields marked with an asterisk (*) are required.
            </p>
        </div>
        
        <form @submit.prevent="submitForm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Job Title Field -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-briefcase text-blue-500 mr-2"></i>
                        <InputLabel for="job_name" value="Job Title*" class="font-medium text-gray-700" />
                    </div>
                    <TextInput 
                        id="job_name"
                        v-model="form.job_name" 
                        type="text" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Enter job title"
                        required
                    />
                    <p class="mt-1 text-xs text-gray-500">A clear and concise title will attract more qualified candidates.</p>
                    <InputError :message="form.errors.job_name" class="mt-1" />
                </div>
                
                <!-- Job Location Field -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="flex items-center mb-2">
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
                    <div class="flex items-center mb-2">
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
                    <div class="flex items-center mb-2">
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
                
                <!-- Number of Vacancies Field -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="flex items-center mb-2">
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
            </div>
            
            <!-- Job Description Field -->
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
                <div class="flex items-center mb-2">
                    <i class="fas fa-align-left text-blue-500 mr-2"></i>
                    <InputLabel for="description" value="Job Description*" class="font-medium text-gray-700" />
                </div>
                <textarea 
                    id="description"
                    v-model="form.description" 
                    rows="6"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Describe the job responsibilities and expectations"
                    required
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">Provide detailed information about the role, responsibilities, and what a typical day looks like.</p>
                <InputError :message="form.errors.description" class="mt-1" />
            </div>

            <!-- Job Requirements Field -->
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
                <div class="flex items-center mb-2">
                    <i class="fas fa-list-check text-blue-500 mr-2"></i>
                    <InputLabel for="requirements" value="Job Requirements*" class="font-medium text-gray-700" />
                </div>
                <textarea 
                    id="requirements"
                    v-model="form.requirements" 
                    rows="6"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="List the qualifications and skills required for this position"
                    required
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">Specify education, experience, skills, and any other qualifications needed for this role.</p>
                <InputError :message="form.errors.requirements" class="mt-1" />
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex items-center justify-end border-t border-gray-200 pt-6">
                <ActionMessage :on="form.recentlySuccessful" class="mr-3 text-sm text-green-600">
                    <div class="flex items-center bg-green-50 px-3 py-1 rounded-full">
                        <i class="fas fa-check-circle mr-1"></i>
                        <span>Changes saved successfully!</span>
                    </div>
                </ActionMessage>
                
                <PrimaryButton 
                    type="submit" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
                    :class="{ 'opacity-25': form.processing }" 
                    :disabled="form.processing"
                >
                    <i class="fas fa-save mr-2"></i>
                    Save Changes
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>