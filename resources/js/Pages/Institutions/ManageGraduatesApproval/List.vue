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
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center">
                    <button @click="$inertia.get(route('graduates.index'))" class="mr-4 p-2 text-gray-500 hover:text-blue-600 transition duration-150 ease-in-out rounded-full hover:bg-gray-100">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </button>
                    <div class="flex flex-col">
                        <h1 class="text-3xl font-extrabold text-gray-900 flex items-center">
                            <i class="fas fa-user-graduate text-blue-600 text-2xl mr-3"></i>
                            Graduate Directory
                        </h1>
                        <p class="text-sm text-gray-500 mt-1">View and manage all graduate records, apply filters, and track statuses.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div v-for="(stat, index) in stats" :key="index" 
                    :class="[
                        'bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-5 relative overflow-hidden',
                        stat.border, 'border-l-4' // Using border-l-4 for a strong accent line
                    ]">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-gray-500 text-xs font-semibold uppercase mb-1">{{ stat.title }}</h3>
                            <p class="text-3xl font-extrabold text-gray-900">{{ stat.value }}</p>
                        </div>
                        <div :class="[stat.iconBg, 'rounded-full p-3 flex items-center justify-center bg-opacity-70']">
                            <i :class="[stat.icon, stat.iconColor, 'text-xl']"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-100 mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-5 flex items-center border-b pb-3">
                    <i class="fas fa-filter text-blue-600 mr-2"></i>
                    Refine Search Filters
                </h3>
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-5 gap-5 items-end">
                    
                    <div class="col-span-1">
                        <label for="program" class="text-sm font-medium text-gray-700 mb-1 block">Program</label>
                        <select v-model="filters.program" id="program"
                            class="w-full shadow-sm pl-3 pr-10 py-2.5 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 transition">
                            <option value="all">All Programs</option>
                            <option v-for="program in programs" :key="program" :value="program">
                                {{ program }}
                            </option>
                        </select>
                    </div>

                    <div class="col-span-1">
                        <label for="date_from" class="text-sm font-medium text-gray-700 mb-1 block">Date From (Creation)</label>
                        <input v-model="filters.date_from" type="date" id="date_from"
                            class="w-full shadow-sm pl-3 pr-10 py-2.5 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 transition">
                    </div>

                    <div class="col-span-1">
                        <label for="date_to" class="text-sm font-medium text-gray-700 mb-1 block">Date To (Creation)</label>
                        <input v-model="filters.date_to" type="date" id="date_to"
                            class="w-full shadow-sm pl-3 pr-10 py-2.5 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 transition">
                    </div>

                    <div class="col-span-1">
                        <label for="status" class="text-sm font-medium text-gray-700 mb-1 block">Status</label>
                        <select v-model="filters.status" id="status"
                            class="w-full shadow-sm pl-3 pr-10 py-2.5 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 transition">
                            <option value="all">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="col-span-1">
                        <button type="submit" 
                            class="w-full justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg transition shadow-md hover:shadow-lg flex items-center transform hover:scale-[1.01]">
                            <i class="fas fa-search mr-2"></i>
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Full Name</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Program</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Date Created</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm text-gray-800">
                            <tr v-for="graduate in graduates.data" :key="graduate.id" class="hover:bg-blue-50/50 transition duration-150">
                                <td class="px-6 py-4 font-medium whitespace-nowrap">
                                    {{ graduate.first_name }} {{ graduate.middle_name }} {{ graduate.last_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ graduate.program_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ formatDate(graduate.created_at) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        graduate.status === 'Active' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300'
                                    ]">
                                        {{ graduate.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <Link :href="route('graduates.profile', { id: graduate.id })" title="View Portfolio"
                                          class="inline-flex items-center justify-center p-2 rounded-full hover:bg-gray-200 transition">
                                        <EyeIcon class="w-5 h-5 text-blue-600 hover:text-blue-800" />
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="graduates.data.length === 0">
                                <td colspan="5" class="text-center text-gray-400 py-10 text-lg font-medium">
                                    <i class="fas fa-search-minus mr-2"></i>
                                    No graduate records found matching the current filters.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 flex justify-between items-center bg-white p-4 rounded-xl shadow-lg border border-gray-100">
                <p v-if="graduates.meta" class="text-sm text-gray-600">
                    Showing {{ graduates.meta.from }} to {{ graduates.meta.to }} of {{ graduates.meta.total }} results
                </p>

                <nav v-if="graduates.links && graduates.links.length > 3" class="inline-flex -space-x-px rounded-lg shadow-sm">
                    <button
                        v-for="(link, i) in graduates.links"
                        :key="i"
                        v-html="link.label"
                        :disabled="!link.url"
                        @click="$inertia.get(link.url)"
                        :class="[
                            'px-4 py-2 text-sm font-medium border transition duration-150',
                            link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 hover:bg-gray-100 border-gray-300',
                            i === 0 ? 'rounded-l-lg' : '',
                            i === graduates.links.length - 1 ? 'rounded-r-lg' : '',
                            !link.url ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'
                        ]"
                    ></button>
                </nav>
            </div>
        </div>

        <div v-if="showDateErrorModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm transition-opacity">
            <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full transform transition-all duration-300 scale-100 border-t-4 border-red-500">
                <h3 class="text-2xl font-bold text-red-700 mb-3 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-3"></i>
                    Date Range Error
                </h3>
                <p class="mb-6 text-gray-700">
                    The 'Date From' filter cannot be set to a date later than the 'Date To' filter. Please correct your date selection and try again.
                </p>
                <button @click="closeDateErrorModal"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-3 rounded-lg transition shadow-md">
                    Got it
                </button>
            </div>
        </div>
    </AppLayout>
</template>