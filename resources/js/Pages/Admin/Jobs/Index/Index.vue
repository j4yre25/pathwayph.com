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

    return props.jobs.filter(job => {
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
        <template #header>
            Jobs
        </template>



        <Container class="py-4">


            <!-- <PrimaryButton @click="createJob()" class="">Post Job</PrimaryButton> -->
            <div class="flex space-x-2 mb-2">
                <div class="mt-8">
                    <Link :href="route('peso.jobs.create', { user: page.props.auth.user.id })">
                    <PrimaryButton class="mr-2">Post Jobs</PrimaryButton>
                    </Link>
                </div>

                <div class="mt-8">

                    <Link :href="route('peso.jobs.manage', { user: page.props.auth.user.id })">
                    <PrimaryButton class="mr-2">Manage Posted Jobs</PrimaryButton>
                    </Link>
                </div>

                <div class="mt-8">

                    <Link :href="route('peso.jobs.archivedlist', { user: page.props.auth.user.id })">
                    <PrimaryButton class="mr-2">Archived Jobs</PrimaryButton>
                    </Link>
                </div>

            </div>

            <div class="flex items-center mt-4 mb-4 space-x-4">
                <!-- Search Input -->
                <input v-model="searchQuery" type="text" placeholder="Search jobs..."
                    class="border border-gray-300 rounded px-4 py-2" />

                <!-- Sector Dropdown -->
                <select v-model="selectedSector" class="border border-gray-300 rounded px-4 py-2">
                    <option value="">All Sectors</option>
                    <option v-for="sector in sectors" :key="sector.id" :value="sector.id">
                        {{ sector.name }}
                    </option>
                </select>

                <!-- Category Dropdown -->
                <select v-model="selectedCategory" class="border border-gray-300 rounded px-4 py-2">
                    <option value="">All Categories</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>

                <!-- Approval Status Dropdown -->
                <select v-model="selectedStatus" class="border border-gray-300 rounded px-4 py-2">
                    <option value="">All Statuses</option>
                    <option value="1">Approved</option>
                    <option value="0">Disapproved</option>
                    <option value="pending">Pending</option>
                </select>
            </div>


            <div class="mt-8">
                <MyJobs :jobs="filteredJobs" />
            </div>


        </Container>





    </AppLayout>

</template>