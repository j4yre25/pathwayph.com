<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, computed } from 'vue';
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
            tooltip: 'Total number of graduates in the system'
        },
        {
            title: 'Active Graduates',
            value: activeGraduates,
            icon: 'fas fa-check-circle',
            iconBg: 'bg-green-100',
            iconColor: 'text-green-600',
            tooltip: 'Number of graduates currently active'
        },
        {
            title: 'Inactive Graduates',
            value: inactiveGraduates,
            icon: 'fas fa-times-circle',
            iconBg: 'bg-red-100',
            iconColor: 'text-red-600',
            tooltip: 'Number of graduates who are not currently active'
        },
        {
            title: 'Programs',
            value: uniquePrograms,
            icon: 'fas fa-graduation-cap',
            iconBg: 'bg-purple-100',
            iconColor: 'text-purple-600',
            tooltip: 'Number of programs offered'
        }
    ];
});

const applyFilters = () => {
    const activeFilters = {};

    if (filters.value.program !== 'all') {
        activeFilters.program = filters.value.program;
    }
    if (filters.value.date_from) {
        activeFilters.date_from = filters.value.date_from;
    }
    if (filters.value.date_to) {
        activeFilters.date_to = filters.value.date_to;
    }
    if (filters.value.status !== 'all') {
        activeFilters.status = filters.value.status;
    }

    console.log('Active Filters:', activeFilters);
    router.get(route('graduates.list'), activeFilters, { preserveState: true });
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

const goBack = () => {
    window.history.back();
};
</script>
<template>
    <AppLayout title="Graduate List">
        <Container>
            <!-- Back Button and Header -->
            <div class="flex items-center mt-6 mb-4">
                <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="flex items-center">
                    <i class="fas fa-user-graduate text-blue-500 text-xl mr-2"></i>
                    <h1 class="text-2xl font-bold text-gray-800">Graduate List</h1>
                </div>
            </div>
            <p class="text-sm text-gray-500 mb-6">Manage and filter graduate records by program, date, and status.</p>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div v-for="(stat, index) in stats" :key="index" 
                     class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm relative overflow-hidden">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">{{ stat.title }}</p>
                            <p class="text-2xl font-bold">{{ stat.value }}</p>
                        </div>
                        <div :class="[stat.iconBg, 'rounded-full p-3 flex items-center justify-center']">
                            <i :class="[stat.icon, stat.iconColor]"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-filter text-blue-500 mr-2"></i>
                    Filter Graduates
                </h3>
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <!-- Program Filter -->
                    <div class="flex flex-col">
                        <label for="program" class="text-sm font-medium text-gray-700 mb-1">Program</label>
                        <div class="relative">
                            <select v-model="filters.program" id="program"
                                class="w-full pl-3 pr-10 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none appearance-none">
                                <option value="all">All Programs</option>
                                <option v-for="program in programs" :key="program" :value="program">
                                    {{ program }}
                                </option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Date Filters -->
                    <div class="flex flex-col">
                        <label for="date_from" class="text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <div class="relative">
                            <input v-model="filters.date_from" type="date" id="date_from"
                                class="w-full pl-3 pr-10 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <label for="date_to" class="text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <div class="relative">
                            <input v-model="filters.date_to" type="date" id="date_to"
                                class="w-full pl-3 pr-10 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="flex flex-col">
                        <label for="status" class="text-sm font-medium text-gray-700 mb-1">Status</label>
                        <div class="relative">
                            <select v-model="filters.status" id="status"
                                class="w-full pl-3 pr-10 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none appearance-none">
                                <option value="all">All</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-end">
                        <button type="submit" class="w-full justify-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center">
                            <i class="fas fa-search mr-2"></i>
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table Section -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-list text-blue-500 mr-2"></i>
                        Graduate Records
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="graduate in graduates.data" :key="graduate.id"
                                class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-500">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ graduate.first_name }} {{ graduate.middle_name }} {{ graduate.last_name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-graduation-cap text-blue-500 mr-2"></i>
                                        <span class="text-sm text-gray-900">{{ graduate.program_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar text-gray-400 mr-2"></i>
                                        <span class="text-sm text-gray-500">{{ formatDate(graduate.created_at) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="{
                                            'bg-green-100 text-green-800': graduate.status === 'Active',
                                            'bg-red-100 text-red-800': graduate.status !== 'Active'
                                        }"
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        <i :class="{
                                            'fas fa-check-circle mr-1': graduate.status === 'Active',
                                            'fas fa-times-circle mr-1': graduate.status !== 'Active'
                                        }"></i>
                                        {{ graduate.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="graduates.data.length === 0">
                                <td colspan="4" class="px-6 py-10 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500">
                                        <i class="fas fa-search text-4xl mb-3"></i>
                                        <p class="text-lg">No graduates found</p>
                                        <p class="text-sm">Try adjusting your filters to find what you're looking for.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination Controls -->
            <div class="mt-6 flex justify-center">
                <nav
                    v-if="graduates.links && graduates.links.length > 3"
                    class="inline-flex rounded-md shadow-sm -space-x-px overflow-hidden"
                >
                    <button
                        v-for="(link, i) in graduates.links"
                        :key="i"
                        type="button"
                        :disabled="!link.url"
                        @click="$inertia.get(link.url)"
                        :class="[
                            'px-4 py-2 border text-sm font-medium',
                            link.active ? 'z-10 bg-blue-600 border-blue-600 text-white' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                            'focus:z-20 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500',
                            i === 0 ? 'rounded-l-md' : '',
                            i === graduates.links.length - 1 ? 'rounded-r-md' : ''
                        ]"
                    >
                        <span v-if="link.label === '&laquo;'"><i class="fas fa-chevron-left"></i></span>
                        <span v-else-if="link.label === '&raquo;'"><i class="fas fa-chevron-right"></i></span>
                        <span v-else v-html="link.label"></span>
                    </button>
                </nav>
            </div>
        </Container>
    </AppLayout>
</template>
