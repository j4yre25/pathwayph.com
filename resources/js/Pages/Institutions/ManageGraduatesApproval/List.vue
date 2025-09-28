<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { EyeIcon } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
    graduates: {
        type: Array,
        default: () => ([]),
    },
    programs: {
        type: Array,
        default: () => ([]),
    },
});

const filters = ref({
    program: 'all',
    date_from: '',
    date_to: '',
    status: 'all',
});

const showDateErrorModal = ref(false);

// Stats for the dashboard cards
const stats = computed(() => {
    const totalGraduates = props.graduates?.data?.length || 0;
    const activeGraduates = props.graduates?.data?.filter(g => g.status === 'Active').length || 0;
    const inactiveGraduates = props.graduates?.data?.filter(g => g.status !== 'Active').length || 0;
    const uniquePrograms = [...new Set(props.graduates?.data?.map(g => g.program_name) || [])].length;
    
    return [
        {
            title: 'Total Graduates',
            value: totalGraduates,
            icon: 'fas fa-user-graduate',
            iconBg: 'bg-blue-100',
            iconColor: 'text-blue-600',
            tooltip: 'Total number of graduates in the system',
            border: 'border-l-4 border-blue-500'
        },
        {
            title: 'Active Graduates',
            value: activeGraduates,
            icon: 'fas fa-check-circle',
            iconBg: 'bg-green-100',
            iconColor: 'text-green-600',
            tooltip: 'Number of graduates currently active',
            border: 'border-l-4 border-green-500'
        },
        {
            title: 'Inactive Graduates',
            value: inactiveGraduates,
            icon: 'fas fa-times-circle',
            iconBg: 'bg-red-100',
            iconColor: 'text-red-600',
            tooltip: 'Number of graduates who are not currently active',
            border: 'border-l-4 border-red-500'
        },
        {
            title: 'Programs',
            value: uniquePrograms,
            icon: 'fas fa-graduation-cap',
            iconBg: 'bg-purple-100',
            iconColor: 'text-purple-600',
            tooltip: 'Number of programs offered',
            border: 'border-l-4 border-purple-500'
        }
    ];
});

// Watch for filter changes and apply automatically
watch(filters, (newFilters) => {
    // Date validation
    if (newFilters.date_from && newFilters.date_to) {
        const from = new Date(newFilters.date_from);
        const to = new Date(newFilters.date_to);

        // If Date From is after Date To, show modal and stop
        if (from > to) {
            showDateErrorModal.value = true;
            return;
        }
    }

    const activeFilters = {};

    if (newFilters.program !== 'all') {
        activeFilters.program = newFilters.program;
    }
    if (newFilters.date_from) {
        activeFilters.date_from = newFilters.date_from;
    }
    if (newFilters.date_to) {
        activeFilters.date_to = newFilters.date_to;
    }
    if (newFilters.status !== 'all') {
        activeFilters.status = newFilters.status;
    }

    router.get(route('graduates.list'), activeFilters, { preserveState: true });
}, { deep: true });

const closeDateErrorModal = () => {
    showDateErrorModal.value = false;
};

const filteredGraduates = computed(() => {
  return props.graduates.filter(g => {
    const createdAt = new Date(g.created_at);
    const dateFrom = filters.value.date_from ? new Date(filters.value.date_from) : null;
    
    let dateTo = null;
    if (filters.value.date_to) {
      // Create a Date from the filter and set its time to the end of the day.
      dateTo = new Date(filters.value.date_to);
      dateTo.setHours(23, 59, 59, 999);
    }
    
    const matchesDateFrom = !dateFrom || createdAt >= dateFrom;
    const matchesDateTo = !dateTo || createdAt <= dateTo;
    
    const matchesProgram = filters.value.program === 'all' || g.program_name === filters.value.program;
    
    return matchesProgram && matchesDateFrom && matchesDateTo;
  });
});

const formatDate = (dateString) => {
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return dateString; // fallback for invalid dates
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
};

</script>

<template>
    <AppLayout title="Graduate List">
        <template #header>
            <div>
                <div class="flex items-center">
                    <button @click="$inertia.get(route('graduates.index'))" class="mr-4 text-gray-600 hover:text-gray-900 transition">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <i class="fas fa-user-graduate text-blue-500 text-xl mr-2"></i>
                    <h2 class="text-2xl font-bold text-gray-800">Graduate List</h2>
                </div>
                <p class="text-sm text-gray-500 mb-1">Manage and filter graduate records by program, date, and status.</p>
            </div>
        </template>

            <Container class="py-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Graduates Card -->
                <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-user-graduate text-white text-lg"></i>
                        </div>
                        <h3 class="text-blue-700 text-sm font-medium mb-2">Total Graduates</h3>
                        <p class="text-2xl font-bold text-blue-900">{{ stats[0].value }}</p>
                    </div>
                </div>

                <!-- Active Graduates Card -->
                <div class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-check-circle text-white text-lg"></i>
                        </div>
                        <h3 class="text-green-700 text-sm font-medium mb-2">Active Graduates</h3>
                        <p class="text-2xl font-bold text-green-900">{{ stats[1].value }}</p>
                    </div>
                </div>

                <!-- Inactive Graduates Card -->
                <div class="bg-gradient-to-br from-red-100 to-red-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-times-circle text-white text-lg"></i>
                        </div>
                        <h3 class="text-red-700 text-sm font-medium mb-2">Inactive Graduates</h3>
                        <p class="text-2xl font-bold text-red-900">{{ stats[2].value }}</p>
                    </div>
                </div>

                <!-- Programs Card -->
                <div class="bg-gradient-to-br from-purple-100 to-purple-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-graduation-cap text-white text-lg"></i>
                        </div>
                        <h3 class="text-purple-700 text-sm font-medium mb-2">Programs</h3>
                        <p class="text-2xl font-bold text-purple-900">{{ stats[3].value }}</p>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-filter text-blue-500 mr-2"></i>
                    Filter Graduate Approvals
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Program Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Program</label>
                        <select v-model="filters.program" id="program"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                            <option value="all">All Programs</option>
                            <option v-for="program in programs" :key="program" :value="program">
                                {{ program }}
                            </option>
                        </select>
                    </div>

                    <!-- Date From Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input v-model="filters.date_from" type="date" id="date_from" placeholder="Date From"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                    </div>

                    <!-- Date To Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input v-model="filters.date_to" type="date" id="date_to" placeholder="Date To"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select v-model="filters.status" id="status"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                            <option value="all">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-user-graduate text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Graduate Approval Management</h3>
                            <p class="text-sm text-gray-600">Review and manage graduate approval requests</p>
                            <span class="text-sm font-semibold text-gray-700">{{ graduates.data.length }} graduates</span>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 text-sm font-semibold text-gray-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Name</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Program</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Date Created</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Status</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center justify-center">
                                        Actions
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <tr v-for="graduate in graduates.data" :key="graduate.id" class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group">
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center text-blue-600 shadow-sm group-hover:shadow-md transition-shadow">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ graduate.first_name }} {{ graduate.middle_name }} {{ graduate.last_name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">{{ graduate.program_name }}</div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatDate(graduate.created_at) }}</div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <span :class="graduate.status === 'Active' ? 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800' : 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800'">
                                        <i :class="graduate.status === 'Active' ? 'fas fa-check-circle mr-1' : 'fas fa-times-circle mr-1'"></i>
                                        {{ graduate.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center">
                                        <Link :href="route('graduates.profile', { id: graduate.id })" 
                                              class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                                              title="View Portfolio">
                                            <EyeIcon class="w-4 h-4 mr-1" />
                                            View Portfolio
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="graduates.data.length === 0" class="py-12 text-center">
                    <i class="fas fa-user-graduate text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-700">No graduates found</h3>
                    <p class="text-gray-500 mt-1">Try adjusting your filters or check back later</p>
                </div>
           

                <!-- Pagination -->
                <div v-if="graduates.links && graduates.links.length > 3" class="flex items-center justify-center mt-8 mb-6 px-6">
                    <div class="flex items-center space-x-2">
                        <button
                            v-for="(link, index) in graduates.links"
                            :key="index"
                            v-html="link.label"
                            :class="[
                                'px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                                link.active 
                                    ? 'bg-blue-600 text-white shadow-md' 
                                    : link.url 
                                        ? 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 hover:border-gray-400' 
                                        : 'text-gray-400 bg-gray-100 cursor-not-allowed'
                            ]"
                            :disabled="!link.url"
                            @click="link.url && router.get(link.url, filters, { preserveState: true, replace: true })"
                        />
                    </div>
                </div>
            </div>
        </Container>

        <!-- Date Error Modal -->
        <div v-if="showDateErrorModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full">
                <h3 class="text-lg font-semibold text-red-600 mb-2">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Invalid Date Range
                </h3>
                <p class="mb-4 text-gray-700">
                    Please ensure your 'Date To' is on or after your 'Date From'.
                </p>
                <button @click="closeDateErrorModal"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">
                    OK
                </button>
            </div>
        </div>
    </AppLayout>
</template>
