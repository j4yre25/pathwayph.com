<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePage, Link } from '@inertiajs/vue3'
import Container from '@/Components/Container.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import MyJobsPeso from './MyJobsPeso.vue'
import { ref, computed } from 'vue'

const page = usePage()

const props = defineProps({
    jobs: Object, // paginated jobs object
    sectors: Array,
    categories: Array,
})

const searchQuery = ref('');
const selectedSector = ref('');
const selectedCategory = ref('');
const selectedStatus = ref('');

const filteredJobs = computed(() => {
    return props.jobs.data.filter(job => {
        const matchesSearch = job.job_title?.toLowerCase().includes(searchQuery.value.toLowerCase());
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
</script>

<template>
    <AppLayout title="Jobs">
        <template #header>
            Jobs
        </template>
        <Container class="py-4">
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
                <div class="mt-8">
                    <Link :href="route('peso.jobs', { user: page.props.auth.user.id })">
                        <PrimaryButton class="mr-2">All Jobs</PrimaryButton>
                    </Link>
                </div>
            </div>

            <div class="flex items-center mt-4 mb-4 space-x-4">
                <input v-model="searchQuery" type="text" placeholder="Search jobs..."
                    class="border border-gray-300 rounded px-4 py-2" />

                <select v-model="selectedSector" class="border border-gray-300 rounded px-4 py-2">
                    <option value="">All Sectors</option>
                    <option v-for="sector in sectors" :key="sector.id" :value="sector.id">
                        {{ sector.name }}
                    </option>
                </select>

                <select v-model="selectedCategory" class="border border-gray-300 rounded px-4 py-2">
                    <option value="">All Categories</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>

                <select v-model="selectedStatus" class="border border-gray-300 rounded px-4 py-2">
                    <option value="">All Statuses</option>
                    <option value="1">Approved</option>
                    <option value="0">Disapproved</option>
                    <option value="pending">Pending</option>
                </select>
            </div>

            <div class="mt-8">
                <MyJobsPeso
                    :jobs="{
                        data: filteredJobs,
                        links: props.jobs.links,
                        prev_page_url: props.jobs.prev_page_url,
                        next_page_url: props.jobs.next_page_url
                    }"
                />
            </div>
        </Container>
    </AppLayout>
</template>