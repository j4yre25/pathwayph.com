<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, usePage } from '@inertiajs/vue3'
import CreateJobs from './CreateJobs.vue'
import Container from '@/Components/Container.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import MyJobs from './MyJobs.vue'
import ManageJobs from './ManageJobs.vue'
import { ref, computed } from 'vue';

const page = usePage()

const props = defineProps({
    jobs: Array,
    sectors: Array,
    categories: Array,
})

const searchQuery = ref('');
const selectedDate = ref('');
const selectedJobType = ref('');
const selectedJobLevel= ref('');
const selectedVacancy = ref('');

console.log(props.jobs);

const filteredJobs = computed(() => {
    return props.jobs.filter(job => {
        const matchesSearch = job.job_title.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesDate = selectedDate.value
            ? job.created_at
            : true;
        const matchesJobType = selectedJobType.value
            ? job.type
            : true;

        const matchesJobLevel = selectedJobLevel.value
            ? selectedJobLevel.value 
            : true;

        const matchesVacancy = selectedVacancy.value
            ? selectedVacancy.value 
            : true;

        return matchesSearch && matchesDate && matchesJobType && matchesJobLevel && matchesVacancy; 
    });
});


console.log('User ID:', page.props);

const form = useForm({
    name: '',
    description: '',
    from_datetime: '',
    to_datetime: '',
    location: ''
});
// console.log(route('jobs.list'));
console.log(route('company.jobs.manage', { user: page.props.auth.user.id }));


</script>


<template>
    <AppLayout title="Manage Jobs">
        <template #header>
            Jobs
        </template>

        <Container class="py-4">
            <div class="flex space-x-2">
                <!-- Post Jobs Button -->
                <div class="mt-8">
                    <Link :href="route('company.jobs.create', { user: page.props.auth.user.id })">
                        <PrimaryButton class="mr-2 bg-blue-400 hover:bg-blue-700 text-white">
                            Post Jobs
                        </PrimaryButton>
                    </Link>
                </div>

                <!-- Manage Posted Jobs Button -->
                <div class="mt-8">
                    <Link :href="route('company.jobs.manage', { user: page.props.auth.user.id })">
                        <PrimaryButton class="mr-2 bg-green-500 hover:bg-green-600 text-white">
                            Manage Posted Jobs
                        </PrimaryButton>
                    </Link>
                </div>

                <!-- Archived Jobs Button -->
                <div class="mt-8">
                    <Link :href="route('company.jobs.archivedlist', { user: page.props.auth.user.id })">
                        <PrimaryButton class="mr-2 bg-gray-700 hover:bg-gray-800 text-white">
                            Archived Jobs
                        </PrimaryButton>
                    </Link>
                </div>
            </div>

            <div class="flex items-center mt-6 mb-6 space-x-4">
                <!-- Search Input -->
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search jobs..."
                    class="border border-gray-300 rounded-lg px-4 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500 w-64"
                />

                <!-- Date Dropdown -->
                <select
                    v-model="selectedDate"
                    class="border border-gray-300 rounded-lg px-4 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">All Dates</option>
                    <option v-for="(sector, index) in sectors" :key="index" :value="index + 1">
                        {{ sector }}
                    </option>
                </select>

                <!-- Category Dropdown -->
                <select
                    v-model="selectedCategory"
                    class="border border-gray-300 rounded-lg px-4 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">All Categories</option>
                    <option v-for="(category, index) in categories" :key="index" :value="index + 1">
                        {{ category }}
                    </option>
                </select>
            </div>

            <div class="mt-8">
                <MyJobs :jobs="filteredJobs" />
            </div>
        </Container>
    </AppLayout>
</template>