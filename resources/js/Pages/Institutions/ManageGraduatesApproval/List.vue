<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, computed } from 'vue';

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
        const matchesProgram = filters.value.program === 'all' || g.program_name === filters.value.program;
        const matchesDateFrom = !filters.value.date_from || new Date(g.created_at) >= new Date(filters.value.date_from);
        const matchesDateTo = !filters.value.date_to || new Date(g.created_at) <= new Date(filters.value.date_to);
        const matchesStatus = filters.value.status === 'all' ||
            (filters.value.status === 'active' && g.is_approved) ||
            (filters.value.status === 'inactive' && !g.is_approved);

        return matchesProgram && matchesDateFrom && matchesDateTo && matchesStatus;
    });
});
</script>
<template>
    <AppLayout title="Graduate List">
        <Container>

            <div class="mt-8 mb-8 flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">Graduate List</h1>
                    <p class="text-sm text-gray-500 mt-1">Manage and filter graduate records by program, date, and
                        status.</p>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white p-6 rounded-2xl shadow mb-8">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <!-- Program Filter -->
                    <div class="flex flex-col">
                        <label for="program" class="text-sm font-medium text-gray-700">Program</label>
                        <select v-model="filters.program" id="program"
                            class="mt-1 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="all">All Programs</option>
                            <option v-for="program in programs" :key="program" :value="program">
                                {{ program }}
                            </option>
                        </select>
                    </div>

                    <!-- Date Filters -->
                    <div class="flex flex-col">
                        <label for="date_from" class="text-sm font-medium text-gray-700">Date From</label>
                        <input v-model="filters.date_from" type="date" id="date_from"
                            class="mt-1 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div class="flex flex-col">
                        <label for="date_to" class="text-sm font-medium text-gray-700">Date To</label>
                        <input v-model="filters.date_to" type="date" id="date_to"
                            class="mt-1 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <!-- Status Filter -->
                    <div class="flex flex-col">
                        <label for="status" class="text-sm font-medium text-gray-700">Status</label>
                        <select v-model="filters.status" id="status"
                            class="mt-1 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="all">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-end">
                        <PrimaryButton type="submit" class="w-full justify-center">Apply Filters</PrimaryButton>
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
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm text-gray-800">
                        <tr v-for="graduate in filteredGraduates" :key="graduate.id"
                            class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                {{ graduate.graduate_first_name }} {{ graduate.graduate_middle_initial }} {{
                                graduate.graduate_last_name }}
                            </td>
                            <td class="px-6 py-4">{{ graduate.program_name }}</td>
                            <td class="px-6 py-4">{{ new Date(graduate.created_at).toLocaleDateString() }}</td>
                            <td class="px-6 py-4">
                                <span
                                    :class="graduate.status === 'Active' ? 'text-green-600 font-semibold' : 'text-red-500 font-semibold'">
                                    {{ graduate.status }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="filteredGraduates.length === 0">
                            <td colspan="4" class="text-center text-gray-400 py-6">
                                No graduates found with current filters.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Container>
    </AppLayout>
</template>
