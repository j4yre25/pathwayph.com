<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  archivedDepartments: Object, 
  hr: Object
});

const showModal = ref(false);
const departmentToRestore = ref(null);

const confirmRestore = (department) => {
  departmentToRestore.value = department;
  showModal.value = true;
};

const restoreDepartment = () => {
  if (!departmentToRestore.value) return;
  router.post(
    route('company.departments.restore', { id: departmentToRestore.value.id }),
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        showModal.value = false;
      },
    }
  );
};

const goTo = (url) => {
  if (url) router.get(url, {}, { preserveScroll: true });
};
</script>

<template>
  <AppLayout title="Archived Departments">
    <template #header>
      <div class="flex items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
          <i class="fas fa-archive text-red-500 mr-2"></i>
          Archived Departments
        </h2>
        <div class="ml-auto">
          <Link href="/company/manage-hrs?tab=departments">
            <PrimaryButton class="flex items-center bg-gray-700 hover:bg-gray-800">
              <i class="fas fa-list mr-2"></i>
              Back to Departments
            </PrimaryButton>
          </Link>
        </div>
      </div>
    </template>

    <Container class="py-8">
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center">
          <i class="fas fa-box-archive text-red-500 mr-2"></i>
          <div>
            <h3 class="font-medium text-gray-800">Archived list</h3>
            <p class="text-sm text-gray-500">Restore departments to make them active again.</p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gradient-to-r from-red-50 to-rose-50 text-sm font-semibold text-gray-700">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Department Name
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Created By
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Date Created
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Action
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-white">
              <tr
                v-for="department in archivedDepartments?.data || []"
                :key="department.id"
                class="hover:bg-gradient-to-r hover:from-red-50 hover:to-rose-50 transition-all duration-200 group"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-semibold text-gray-900">{{ department.department_name }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-700">{{ department.hr_name || 'N/A' }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-700">
                    {{ new Date(department.created_at).toLocaleDateString() }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border shadow-sm bg-red-100 text-red-800">
                    <i class="fas fa-box-archive mr-1.5"></i>
                    Archived
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <DangerButton
                    class="bg-green-600 hover:bg-green-700"
                    @click="confirmRestore(department)"
                  >
                    <i class="fas fa-rotate-left mr-2"></i>
                    Restore
                  </DangerButton>
                </td>
              </tr>

              <tr v-if="(archivedDepartments?.data || []).length === 0">
                <td colspan="5" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                      <i class="fas fa-search text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500 text-lg font-medium">No archived departments</p>
                    <p class="text-gray-400 text-sm mt-1">Restored or active departments won’t show here.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="archivedDepartments?.links && archivedDepartments.links.length > 1" class="px-6 py-4 border-t border-gray-100 bg-white">
          <nav aria-label="Page navigation" class="flex justify-center">
            <ul class="inline-flex -space-x-px text-sm">
              <li
                v-for="link in archivedDepartments.links"
                :key="link.label + (link.url || '')"
              >
                <a
                  v-if="link.url"
                  href="#"
                  @click.prevent="goTo(link.url)"
                  class="px-3 h-9 inline-flex items-center justify-center border"
                  :class="link.active
                    ? 'bg-blue-600 border-blue-600 text-white'
                    : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50'"
                >
                  <span v-html="link.label"></span>
                </a>
                <span
                  v-else
                  class="px-3 h-9 inline-flex items-center justify-center border bg-gray-100 border-gray-300 text-gray-400 cursor-not-allowed"
                >
                  <span v-html="link.label"></span>
                </span>
              </li>
            </ul>
          </nav>
        </div>
      </div>

      <!-- Confirmation Modal -->
      <ConfirmationModal :show="showModal" @close="showModal = false">
        <template #title>
          Confirm Restoration
        </template>
        <template #content>
          Are you sure you want to restore the department "{{ departmentToRestore?.department_name }}"? This will make it active again.
        </template>
        <template #footer>
          <PrimaryButton @click="showModal = false" class="mr-2">
            Cancel
          </PrimaryButton>
          <DangerButton @click="restoreDepartment" class="bg-green-600 hover:bg-green-700">
            <i class="fas fa-rotate-left mr-2"></i>
            Restore Department
          </DangerButton>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>