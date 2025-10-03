<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import Container from '@/Components/Container.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { ref, computed } from 'vue'
import '@fortawesome/fontawesome-free/css/all.css'
import { Link } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'


const props = defineProps({
    jobs: Object, // Now an object with data, links, meta, etc.
})

const isSuccessModalOpen = ref(false)
const successMessage = ref('')

const allJobs = computed(() => props.jobs.data ?? [])

const kpi = computed(() => {
    return {
        total: allJobs.value.length,
        open: allJobs.value.filter(j => j.status === 'open').length,
        closed: allJobs.value.filter(j => j.status === 'closed').length,
        expired: allJobs.value.filter(j => j.status === 'expired').length,
    }
})

const searchQuery = ref('')
const selectedStatus = ref('') // Approved/Disapproved/Pending
const companyQuery = ref('')   // <-- Add this line

const filteredJobs = computed(() => {
    // Filtering only applies to the current page's jobs
    return props.jobs.data.filter(job => {
        const matchesSearch = job.job_title
            .toLowerCase()
            .includes(searchQuery.value.toLowerCase())

        const matchesCompany = job.company?.company_name
            ? job.company.company_name.toLowerCase().includes(companyQuery.value.toLowerCase())
            : false

        const matchesStatus = selectedStatus.value
            ? (selectedStatus.value === 'approved' && job.is_approved === 1) ||
            (selectedStatus.value === 'disapproved' && job.is_approved === 0) ||
            (selectedStatus.value === 'pending' && job.is_approved === null)
            : true

        return matchesSearch && (companyQuery.value ? matchesCompany : true) && matchesStatus
    })
})

const isActionLoading = ref(false)
const activeJobId = ref(null)
const actionError = ref(null)

function approveJob(job) {
    isActionLoading.value = true
    activeJobId.value = job.id
    router.post(route('admin.jobs.approve', { job: job.id }), {}, {
        onFinish: () => {
            isActionLoading.value = false
            activeJobId.value = null
            successMessage.value = 'Job approved successfully. The company has been notified.'
            isSuccessModalOpen.value = true
        },
        onError: (err) => {
            actionError.value = err
            isActionLoading.value = false
            activeJobId.value = null
        }
    })
}

function disapproveJob(job) {
    isActionLoading.value = true
    activeJobId.value = job.id
    router.post(route('admin.jobs.disapprove', { job: job.id }), {}, {
        onFinish: () => {
            isActionLoading.value = false
            activeJobId.value = null
            successMessage.value = 'Job disapproved. The company has been notified.'
            isSuccessModalOpen.value = true
        },
        onError: (err) => {
            actionError.value = err
            isActionLoading.value = false
            activeJobId.value = null
        }
    })
}
</script>

<template>
    <AppLayout title="Job Approval">
        <template #header>
            <div class="flex items-center">
                <i class="fas fa-clipboard-check text-blue-500 text-xl mr-2"></i>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Job Approval
                </h2>
            </div>
        </template>

        <Container class="py-8">

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Jobs -->
                <div
                    class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-briefcase text-white text-xl"></i>
                        </div>
                        <h3 class="text-blue-700 text-sm font-medium mb-2">Total Jobs</h3>
                        <p class="text-3xl font-bold text-blue-700">{{ kpi.total }}</p>
                    </div>
                </div>
                <!-- Open Jobs -->
                <div
                    class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-unlock text-white text-xl"></i>
                        </div>
                        <h3 class="text-green-700 text-sm font-medium mb-2">Open Jobs</h3>
                        <p class="text-3xl font-bold text-green-900">
                            {{ kpi.open }}
                        </p>
                    </div>
                </div>
                <!-- Closed Jobs -->
                <div
                    class="bg-gradient-to-br from-red-100 to-red-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-lock text-white text-xl"></i>
                        </div>
                        <h3 class="text-red-700 text-sm font-medium mb-2">Closed Jobs</h3>
                        <p class="text-3xl font-bold text-red-900">
                            {{ kpi.closed }}
                        </p>
                    </div>
                </div>
                <!-- Expired Jobs -->
                <div
                    class="bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-hourglass-end text-white text-xl"></i>
                        </div>
                        <h3 class="text-yellow-700 text-sm font-medium mb-2">Expired Jobs</h3>
                        <p class="text-3xl font-bold text-yellow-900">
                            {{ kpi.expired }}
                        </p>
                    </div>
                </div>
            </div>
            <!-- Search and Status Filter -->
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6 shadow-sm">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Search & Filter</h3>
                    <div class="ml-auto">
                        <button type="button" @click="
                            searchQuery = '';
                        companyQuery = '';
                        selectedStatus = '';
                        "
                            class="px-6 py-3 bg-white text-gray-700 rounded-xl text-sm font-medium flex items-center hover:bg-gray-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 shadow-sm border border-gray-300">
                            <i class="fas fa-undo mr-2"></i> Reset Filter
                        </button>
                    </div>
                </div>
                <div class="p-4 flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search Job Title</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input v-model="searchQuery" type="text" placeholder="Search jobs..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm" />
                        </div>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search Company</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fas fa-building text-gray-400"></i>
                            </div>
                            <input v-model="companyQuery" type="text" placeholder="Search company..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select v-model="selectedStatus"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10">
                            <option value="">All Statuses</option>
                            <option value="approved">Approved</option>
                            <option value="disapproved">Disapproved</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Error message -->
            <div v-if="actionError"
                class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline ml-1">{{ actionError }}</span>
                <button @click="actionError = null" class="absolute top-0 bottom-0 right-0 px-4 py-3"
                    aria-label="Close alert">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Jobs Table -->
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <div class="flex items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Jobs for Approval</h3>
                        <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{
                            filteredJobs.length }} jobs</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Job
                                    Title</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Company</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Posted</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                   Vacancies</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <tr v-for="job in filteredJobs" :key="job.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900">{{ job.job_title }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-gray-700">{{ job.company?.company_name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-gray-500 text-sm">{{ new Date(job.created_at).toLocaleDateString()
                                        }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <template v-if="job.is_approved === 1">
                                        <span v-if="job.status === 'open'"
                                            class="px-3 py-1.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Approved
                                            & Active</span>
                                        <span v-else
                                            class="px-3 py-1.5 text-xs font-semibold rounded-full bg-gray-200 text-gray-700">Approved
                                            & Closed</span>
                                    </template>
                                    <span v-else-if="job.is_approved === 0"
                                        class="px-3 py-1.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">Disapproved</span>
                                    <span v-else
                                        class="px-3 py-1.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">

                                    <div class="text-gray-700">{{ job.job_vacancies }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <PrimaryButton v-if="job.is_approved === null"
                                            :disabled="isActionLoading && activeJobId === job.id"
                                            @click="approveJob(job)" class="bg-green-500 hover:bg-green-600 text-white">
                                            <i class="fas fa-check mr-1"></i> Approve
                                        </PrimaryButton>
                                        <PrimaryButton v-if="job.is_approved === null"
                                            :disabled="isActionLoading && activeJobId === job.id"
                                            @click="disapproveJob(job)" class="bg-red-500 hover:bg-red-600 text-white">
                                            <i class="fas fa-times mr-1"></i> Disapprove
                                        </PrimaryButton>
                                        <Link :href="route('admin.jobs.view', { job: job.id })"
                                            class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md text-sm font-medium transition-colors">
                                        <i class="fas fa-eye mr-1"></i> View Job
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredJobs.length === 0">
                                <td colspan="5" class="text-center text-gray-400 py-8">No jobs found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 flex justify-center">
                <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <button v-for="link in jobs.links" :key="link.label" :disabled="!link.url"
                        @click="link.url && router.get(link.url)" v-html="link.label" :class="[
                            'px-3 py-2 border text-sm font-medium',
                            link.active ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
                            !link.url ? 'opacity-50 cursor-not-allowed' : ''
                        ]" />
                </nav>
            </div>
        </Container>
    </AppLayout>


    <Modal :modelValue="isSuccessModalOpen" @close="isSuccessModalOpen = false">
        <div class="p-6">
            <div class="flex items-center justify-center mb-4 bg-green-100 rounded-full w-12 h-12 mx-auto">
                <i class="fas fa-check text-green-500 text-xl"></i>
            </div>
            <h2 class="text-lg font-medium text-center text-gray-900 mb-2">Success</h2>
            <p class="text-center text-gray-600">{{ successMessage }}</p>
            <div class="mt-6 flex justify-center">
                <PrimaryButton @click="isSuccessModalOpen = false">OK</PrimaryButton>
            </div>
        </div>
    </Modal>
</template>