<script setup>
import { router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const confirmRestore = ref(false);
const graduateToRestore = ref(null);

// --- Filter State ---
const filters = ref({
    program: 'all',
    date_from: '',
    date_to: '',
});

const showDateErrorModal = ref(false);

const props = defineProps({
    graduates: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    programs: {
        type: Array,
        default: () => ([]),
    },
    filters: {
        type: Object,
        default: () => ({
            program: 'all',
            date_from: '',
            date_to: '',
        }),
    },
});

// Initialize filters from props
filters.value = { ...props.filters };

// --- Automatic Filter Logic ---
const applyFilters = () => {
    const activeFilters = {};

    // Date validation
    if (filters.value.date_from && filters.value.date_to) {
        const from = new Date(filters.value.date_from);
        const to = new Date(filters.value.date_to);

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

    router.get(route('graduates.archived'), activeFilters, { preserveState: true });
};

// Watch for filter changes and apply automatically
watch(filters, () => {
    applyFilters();
}, { deep: true });

const closeDateErrorModal = () => {
    showDateErrorModal.value = false;
};

// --- Restore Logic ---
const restoreGraduate = (id) => {
    router.put(route('graduates.restore', { graduate: id }));
};

const confirmRestoreGraduate = (graduate) => {
    graduateToRestore.value = graduate;
    confirmRestore.value = true;
};

const cancelRestore = () => {
    confirmRestore.value = false;
    graduateToRestore.value = null;
};

const proceedWithRestore = () => {
    if (graduateToRestore.value && graduateToRestore.value.id) {
        restoreGraduate(graduateToRestore.value.id);
        confirmRestore.value = false;
        graduateToRestore.value = null;
    }
};


const stats = computed(() => {
    return [
        {
            title: 'Total Archived Graduates',
            value: props.graduates?.data?.length || 0,
            icon: 'fas fa-archive',
            color: 'text-white',
            bgGradient: 'bg-gradient-to-br from-orange-100 to-orange-200',
            iconBg: 'bg-orange-500',
            textColor: 'text-orange-700',
            valueColor: 'text-orange-900'
        }
    ];
});
</script>

<template>
    <AppLayout title="Archived Graduates">
        <template #header>
            <div class="flex items-center">
                <button @click="$inertia.get(route('graduates.index'))" class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <h2 class="font-semibold text-xl text-gray-800 flex items-center">
                    <i class="fas fa-archive text-blue-500 text-xl mr-2"></i> Archived Graduates
                </h2>
            </div>
        </template>

        <Container class="py-6 space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 mb-6">
                <div v-for="(stat, index) in stats" :key="index"
                    :class="[stat.bgGradient, 'rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105']">
                    <div class="flex flex-col items-center text-center">
                        <div :class="[stat.iconBg, 'w-12 h-12 rounded-full flex items-center justify-center mb-3']">
                            <i :class="[stat.icon, stat.color, 'text-lg']"></i>
                        </div>
                        <h3 :class="[stat.textColor, 'text-sm font-medium mb-2']">{{ stat.title }}</h3>
                        <p :class="[stat.valueColor, 'text-2xl font-bold']">{{ stat.value }}</p>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-filter text-blue-500 mr-2"></i>
                    Filter Graduates
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                            <div
                                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                            </div>
                        </div>
                    </div>

                    <!-- Date Filters -->
                    <div class="flex flex-col">
                        <label for="date_from" class="text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <div class="relative">
                            <input v-model="filters.date_from" type="date" id="date_from"
                                class="w-full pl-3 pr-10 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                            <div
                                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <label for="date_to" class="text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <div class="relative">
                            <input v-model="filters.date_to" type="date" id="date_to"
                                class="w-full pl-3 pr-10 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                            <div
                                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-table text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Archived Graduates</h3>
                            <p class="text-sm text-gray-600">Manage archived graduates</p>
                            <span class="text-sm font-semibold text-gray-700">{{ graduates.data.length }} total</span>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 text-sm font-semibold text-gray-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center">Name</div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center">Program</div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center">Date Created</div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center">Status</div>
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center justify-end">Actions</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <tr v-for="graduate in graduates.data" :key="graduate.id"
                                class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group">
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ graduate.first_name }} {{ graduate.middle_name }} {{ graduate.last_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm text-gray-700">{{ graduate.graduate_program_completed }}</div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm text-gray-600">{{ new Date(graduate.created_at).toLocaleDateString() }}</div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 shadow-sm">
                                        <i class="fas fa-archive mr-1.5"></i> Archived
                                    </span>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button @click="confirmRestoreGraduate(graduate)"
                                            class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-2 rounded-full hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md"
                                            title="Restore">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty State -->
                <div v-if="graduates.data.length === 0" class="py-12 text-center">
                    <i class="fas fa-archive text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-700">No archived graduates found</h3>
                    <p class="text-gray-500 mt-1">When graduates are archived, they will appear here</p>
                </div>
            </div>

            <!-- Pagination Controls -->
            <div class="mt-6 flex justify-center">
                <nav v-if="graduates.links && graduates.links.length > 3" class="inline-flex -space-x-px">
                    <button v-for="(link, i) in graduates.links" :key="i" v-html="link.label" :disabled="!link.url"
                        @click="$inertia.get(link.url)" :class="[
                            'px-3 py-1 border text-sm',
                            link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100',
                            i === 0 ? 'rounded-l' : '',
                            i === graduates.links.length - 1 ? 'rounded-r' : ''
                        ]"></button>
                </nav>
            </div>

            <!-- Date Error Modal -->
            <div v-if="showDateErrorModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
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

            <!-- Confirmation Modal -->
            <div v-if="confirmRestore" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
                role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                        @click="cancelRestore"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fas fa-undo-alt text-blue-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Restore
                                        Graduate
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Are you sure you want to restore this graduate? This will make the graduate
                                            record
                                            active again.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                                @click="proceedWithRestore">
                                Restore
                            </button>
                            <button type="button"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                @click="cancelRestore">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Container>
    </AppLayout>
</template>
