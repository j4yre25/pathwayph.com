<script setup>
import { ref, computed } from 'vue';
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

const applyFilters = () => {
    const activeFilters = {};

    // Date validation
    if (filters.value.date_from && filters.value.date_to) {
        const from = new Date(filters.value.date_from);
        const to = new Date(filters.value.date_to);

        // If Date From is after Date To, show modal and stop
        if (from > to) {
            showDateErrorModal.value = true;
            return;
        }
    }

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

    router.get(route('graduates.list'), activeFilters, { preserveState: true });
};

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
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div v-for="(stat, index) in stats" :key="index" 
                    :class="[
                        'bg-white rounded-lg shadow-sm p-6 relative overflow-hidden',
                        stat.border
                    ]">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-gray-600 text-sm font-medium mb-2">{{ stat.title }}</h3>
                            <p class="text-3xl font-bold text-gray-800">{{ stat.value }}</p>
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
            <div class="overflow-x-auto bg-white shadow rounded-2xl">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wide">
                      <tr>
                        <th class="px-6 py-4 text-left">Name</th>
                        <th class="px-6 py-4 text-left">Program</th>
                        <th class="px-6 py-4 text-left">Date Created</th>
                        <th class="px-6 py-4 text-left">Status</th>
                        <th class="px-6 py-4 text-left"></th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm text-gray-800">
                      <tr v-for="graduate in graduates.data" :key="graduate.id" class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                          {{ graduate.first_name }} {{ graduate.middle_name }} {{ graduate.last_name }}
                        </td>
                        <td class="px-6 py-4">{{ graduate.program_name }}</td>
                        <td class="px-6 py-4">{{ formatDate(graduate.created_at) }}</td>
                        <td class="px-6 py-4">
                          <span :class="graduate.status === 'Active' ? 'text-green-600 font-semibold' : 'text-red-500 font-semibold'">
                            {{ graduate.status }}
                          </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                          <Link :href="route('graduates.profile', { id: graduate.id })" title="View Portfolio">
                            <EyeIcon class="w-5 h-5 text-blue-600 hover:text-blue-800 transition" />
                          </Link>
                        </td>
                      </tr>
                      <tr v-if="graduates.data.length === 0">
                        <td colspan="5" class="text-center text-gray-400 py-6">
                          No graduates found with current filters.
                        </td>
                      </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <div class="mt-6 flex justify-center">
              <nav v-if="graduates.links && graduates.links.length > 3" class="inline-flex -space-x-px">
                <button
                  v-for="(link, i) in graduates.links"
                  :key="i"
                  v-html="link.label"
                  :disabled="!link.url"
                  @click="$inertia.get(link.url)"
                  :class="[
                    'px-3 py-1 border text-sm',
                    link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100',
                    i === 0 ? 'rounded-l' : '',
                    i === graduates.links.length - 1 ? 'rounded-r' : ''
                  ]"
                ></button>
              </nav>
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
