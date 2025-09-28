<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  internshipPrograms: Array, // Archived internship programs from the controller
});

const showModal = ref(false);
const selected = ref(null);
const restoring = ref(false);

const restore = () => {
  if (!selected.value) return;
  restoring.value = true;
  router.post(
    route('internship-programs.restore', { id: selected.value.id }),
    {},
    {
      onSuccess: () => {
        showModal.value = false;
        restoring.value = false;
        router.reload({ only: ['internshipPrograms'] });
      },
      onFinish: () => {
        restoring.value = false;
      },
    }
  );
};

const confirmRestore = (item) => {
  selected.value = item;
  showModal.value = true;
};

const goBack = () => {
  window.history.back();
};

const stats = computed(() => {
  return [
    {
      title: 'Total Archived',
      value: props.internshipPrograms.length,
      icon: 'fas fa-archive',
      color: 'text-orange-600',
      bgColor: 'bg-orange-100'
    }
  ];
});
</script>

<template>
  <AppLayout title="Archived Internship Programs">
    <template #header>
      <div class="flex items-center">
        <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
          <i class="fas fa-chevron-left"></i>
        </button>
        <h2 class="font-semibold text-xl text-gray-800 flex items-center">
          <i class="fas fa-archive text-blue-500 text-xl mr-2"></i> Archived Internship Programs
        </h2>
      </div>
    </template>

    <Container class="py-6 space-y-6">
      <!-- KPI Card -->
      <div class="grid grid-cols-1 gap-6 mb-6">
        <div class="bg-gradient-to-br from-orange-100 to-orange-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-archive text-white text-lg2"></i>
            </div>
            <h3 class="text-orange-700 text-sm font-medium mb-2">Total Archived Programs</h3>
            <p class="text-2xl font-bold text-orange-900">{{ stats[0].value }}</p>
          </div>
        </div>
      </div>

      <!-- Table Card -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
          <div class="flex items-center">
            <div class="flex items-center">
              <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-table text-white"></i>
              </div>
              <div>
                <h3 class="text-xl font-bold text-gray-800">Archived Internship Programs</h3>
                <p class="text-sm text-gray-600">Manage archived internship programs</p>
                <span class="text-sm font-semibold text-gray-700">{{ internshipPrograms.length }} total</span>
              </div>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full table-auto">
            <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 text-sm font-semibold text-gray-700">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                  <div class="flex items-center">Title</div>
                </th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                  <div class="flex items-center">Programs</div>
                </th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                  <div class="flex items-center">Career Opportunities</div>
                </th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                  <div class="flex items-center">Skills</div>
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
              <tr v-for="item in internshipPrograms" :key="item.id" class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group">
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center text-blue-600 shadow-sm group-hover:shadow-md transition-shadow">
                      <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-semibold text-gray-900">{{ item.title }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="text-sm text-gray-700 font-medium">
                    <span v-for="(p, idx) in item.programs" :key="p.id">
                      {{ p.name }}<span v-if="idx < item.programs.length - 1">, </span>
                    </span>
                    <span v-if="item.programs.length === 0" class="text-gray-400">None</span>
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="text-sm text-gray-700 font-medium">
                    <span v-for="(c, idx) in item.career_opportunities" :key="c.id">
                      {{ c.title }}<span v-if="idx < item.career_opportunities.length - 1">, </span>
                    </span>
                    <span v-if="item.career_opportunities.length === 0" class="text-gray-400">None</span>
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="text-sm text-gray-700 font-medium">
                    <span v-for="(s, idx) in item.skills" :key="s.id">
                      {{ s.name }}<span v-if="idx < item.skills.length - 1">, </span>
                    </span>
                    <span v-if="item.skills.length === 0" class="text-gray-400">None</span>
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 shadow-sm">
                    <i class="fas fa-archive mr-1.5"></i> Archived
                  </span>
                </td>
                <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <button 
                      @click="confirmRestore(item)"
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
        <div v-if="internshipPrograms.length === 0" class="py-12 text-center">
          <i class="fas fa-archive text-gray-300 text-5xl mb-4"></i>
          <h3 class="text-lg font-medium text-gray-700">No archived internship programs found</h3>
          <p class="text-gray-500 mt-1">Archived internship programs will appear here</p>
        </div>
      </div>

      <!-- Confirmation Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showModal = false"></div>
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-gradient-to-br from-red-100 to-red-200 sm:mx-0 sm:h-10 sm:w-10 shadow-lg">
                  <i class="fas fa-question-circle text-blue-500"></i>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                  <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Confirm Restore</h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-600">Are you sure you want to restore this internship program? This will make the program active again.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button type="button" @click="restore" :disabled="restoring" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm transition-colors duration-200 flex items-center">
                <i class="fas fa-undo mr-2"></i> Restore
              </button>
              <button type="button" @click="showModal = false" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200 flex items-center sm:mr-3 sm:ml-0 mt-3 sm:mt-0">
                <i class="fas fa-times mr-2"></i> Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>