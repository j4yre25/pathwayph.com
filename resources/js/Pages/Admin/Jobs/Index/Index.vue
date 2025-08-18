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
const selectedSector = ref('');
const selectedCategory = ref('');
const selectedStatus = ref('');


console.log(props.jobs);

const filteredJobs = computed(() => {
    console.log('Selected Sector:', selectedSector.value);
    console.log('Selected Category:', selectedCategory.value);

    return props.jobs.data.filter(job => {
        const matchesSearch = job.job_title.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesSector = selectedSector.value
            ? job.sector_id === parseInt(selectedSector.value)
            : true;
        const matchesCategory = selectedCategory.value
            ? job.category_id === parseInt(selectedCategory.value)
            : true;
        const matchesStatus = selectedStatus.value
            ? (selectedStatus.value === 'pending'
                ? job.is_approved === null
                : job.is_approved === parseInt(selectedStatus.value))
            : true;

        return matchesSearch && matchesSector && matchesCategory && matchesStatus;
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
console.log(route('peso.jobs.manage', { user: page.props.auth.user.id }));


</script>


<template>
    <AppLayout title="Jobs">
        <Container class="py-6 space-y-6">
            <!-- Header Section with Back Button -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <Link :href="route('dashboard')" class="mr-4 text-gray-500 hover:text-gray-700 transition-colors">
                        <i class="fas fa-chevron-left"></i>
                    </Link>
                    <i class="fas fa-briefcase text-blue-500 text-xl mr-2"></i>
                    <h1 class="text-2xl font-bold text-gray-800">Manage Jobs</h1>
                </div>
                <Link :href="route('peso.jobs.create', { user: page.props.auth.user.id })" 
                      class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring focus:ring-blue-300 transition">
                    <i class="fas fa-plus mr-2"></i> Post Job
                </Link>
            </div>
            <p class="text-sm text-gray-500 mb-6">View and manage all job postings in the system.</p>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3 mb-6">
                <Link :href="route('peso.jobs.manage', { user: page.props.auth.user.id })" 
                      class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                    <i class="fas fa-tasks text-blue-500 mr-2"></i> Manage Posted Jobs
                </Link>
                
                <Link :href="route('peso.jobs.archivedlist', { user: page.props.auth.user.id })" 
                      class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                    <i class="fas fa-archive text-gray-500 mr-2"></i> Archived Jobs
                </Link>
                
                <Link :href="route('peso.pesojobs', { user: page.props.auth.user.id })" 
                      class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                    <i class="fas fa-building text-blue-500 mr-2"></i> PESO Jobs
                </Link>
            </div>

            <!-- Filter Section -->
            <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 flex flex-wrap items-center gap-4">
                <!-- Search Input -->
                <div class="flex-1 min-w-[200px]">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input v-model="searchQuery" type="text" placeholder="Search jobs..." 
                               class="w-full pl-10 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" />
                    </div>
                </div>
                
                <!-- Sector Dropdown -->
                <div>
                    <select v-model="selectedSector" 
                            class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                        <option value="">All Sectors</option>
                        <option v-for="sector in sectors" :key="sector.id" :value="sector.id">
                            {{ sector.name }}
                        </option>
                    </select>
                </div>

                <!-- Category Dropdown -->
                <div>
                    <select v-model="selectedCategory" 
                            class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                        <option value="">All Categories</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                </div>

                <!-- Approval Status Dropdown -->
                <div>
                    <select v-model="selectedStatus" 
                            class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                        <option value="">All Statuses</option>
                        <option value="1">Approved</option>
                        <option value="0">Disapproved</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
            </div>

            <!-- Jobs Table Section -->
            <div class="mt-6">
                <MyJobs :jobs="{ data: filteredJobs, links: props.jobs.links, prev_page_url: props.jobs.prev_page_url, next_page_url: props.jobs.next_page_url }" />
            </div>
        </Container>
    </AppLayout>
</template>