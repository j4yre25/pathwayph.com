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
  router.visit(route('internship-programs.index'));
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
      <div>
        <div class="flex items-center">
          <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
            <i class="fas fa-chevron-left"></i>
          </button>
          <i class="fas fa-archive text-orange-500 text-xl mr-2"></i>
          <h2 class="text-2xl font-bold text-gray-800">Archived Internship Programs</h2>
        </div>
        <p class="text-sm text-gray-500 mb-1">View the full list of archived internship programs or restore them.</p>
      </div>
    </template>

    <Container class="py-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-1 gap-6 mb-6">
        <div v-for="(stat, index) in stats" :key="index" 
             class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500 relative overflow-hidden">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">{{ stat.title }}</h3>
              <p class="text-3xl font-bold text-gray-800">{{ stat.value }}</p>
            </div>
            <div :class="[stat.bgColor, 'rounded-full p-3 flex items-center justify-center']">
              <i :class="[stat.icon, stat.color]"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200 mb-6">
        <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center">
          <i class="fas fa-list text-blue-500 mr-2"></i>
          <h2 class="font-semibold text-gray-800">Archived Internship Programs List</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Programs</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Career Opportunities</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skills</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              <tr 
                v-for="item in internshipPrograms" 
                :key="item.id" 
                class="hover:bg-gray-50 transition duration-150"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <i class="fas fa-briefcase text-gray-400 mr-2"></i>
                    <span class="text-sm font-medium text-gray-800">{{ item.title }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <i class="fas fa-graduation-cap text-gray-400 mr-2"></i>
                    <span class="text-sm text-gray-700">
                      <span v-for="(p, idx) in item.programs" :key="p.id">
                        {{ p.name }}<span v-if="idx < item.programs.length - 1">, </span>
                      </span>
                      <span v-if="item.programs.length === 0" class="text-gray-400">None</span>
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <i class="fas fa-briefcase text-gray-400 mr-2"></i>
                    <span class="text-sm text-gray-700">
                      <span v-for="(c, idx) in item.career_opportunities" :key="c.id">
                        {{ c.title }}<span v-if="idx < item.career_opportunities.length - 1">, </span>
                      </span>
                      <span v-if="item.career_opportunities.length === 0" class="text-gray-400">None</span>
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <i class="fas fa-tools text-gray-400 mr-2"></i>
                    <span class="text-sm text-gray-700">
                      <span v-for="(s, idx) in item.skills" :key="s.id">
                        {{ s.name }}<span v-if="idx < item.skills.length - 1">, </span>
                      </span>
                      <span v-if="item.skills.length === 0" class="text-gray-400">None</span>
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <i class="fas fa-archive mr-1"></i>
                    Archived
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <button 
                    @click="confirmRestore(item)"
                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                  >
                    <i class="fas fa-undo-alt mr-1"></i>
                    Restore
                  </button>
                </td>
              </tr>
              <tr v-if="internshipPrograms.length === 0">
                <td colspan="6" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <i class="fas fa-archive text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500 text-lg font-medium">No archived internship programs found</p>
                    <p class="text-gray-400 text-sm mt-1">Archived internship programs will appear here</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
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
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                  <i class="fas fa-undo-alt text-blue-600"></i>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                  <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Restore Internship Program</h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      Are you sure you want to restore this internship program? This will make the program active again.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button 
                type="button" 
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                @click="restore"
                :disabled="restoring"
              >
                <span v-if="restoring">Restoring...</span>
                <span v-else>Restore</span>
              </button>
              <button 
                type="button" 
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                @click="showModal = false"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>