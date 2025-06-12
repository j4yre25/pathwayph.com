<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, computed } from 'vue';
import { EyeIcon } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

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
    </AppLayout>
</template>
