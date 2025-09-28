<script setup>
import Container from '@/Components/Container.vue';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { router } from '@inertiajs/vue3';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
    archivedDepartments: Object,
    hr: Object
});

const showModal = ref(false);
const departmentToRestore = ref(null);

const goBack = () => {
    window.history.back();
};

const restoreDepartment = () => {
    if (!departmentToRestore.value) return;
    router.post(route('company.departments.restore', {
        id: departmentToRestore.value.id
    }), {}, {
        onSuccess: () => {
            showModal.value = false;
        }
    });
};

const confirmRestore = (department) => {
    departmentToRestore.value = department;
    showModal.value = true;
};




</script>


<template>
    <AppLayout title="Archived Departments">
        <template #header>
            <div class="flex items-center">
                <button 
                    @click="goBack"
                    class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <h2 class="font-semibold text-xl text-gray-800 flex items-center">
                    <i class="fas fa-archive text-blue-500 text-xl mr-2"></i> Archived Departments
                </h2>
            </div>
        </template>

        <Container class="py-6 space-y-6">
            <!-- Stats Card -->
            <div class="grid grid-cols-1 gap-6 mb-6">
                <div class="bg-gradient-to-br from-orange-100 to-orange-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center mb-3">
                             <i class="fas fa-archive text-white text-lg"></i>
                        </div>
                        <h3 class="text-orange-700 text-sm font-medium mb-2">Total Archived Departments</h3>
                        <p class="text-2xl font-bold text-orange-900">{{ archivedDepartments ? archivedDepartments.length : 0 }}</p>
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
                                <h3 class="text-xl font-bold text-gray-800">Archived Departments</h3>
                                <p class="text-sm text-gray-600">Manage archived departments</p>
                                <span class="text-sm font-semibold text-gray-700">{{ archivedDepartments ? archivedDepartments.length : 0 }} total</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="archivedDepartments && archivedDepartments.length > 0" class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gradient-to-r from-blue-50 to-indigo-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Department Name</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Created By</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date Created</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date Archived</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="department in archivedDepartments" :key="department.id" class="hover:bg-gray-50 transition-all duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-building text-orange-600"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ department.department_name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-700">{{ department.hr_name || 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-700">{{ new Date(department.created_at).toLocaleDateString() }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-700">{{ new Date(department.deleted_at).toLocaleDateString() }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        <i class="fas fa-archive mr-1"></i>
                                        Archived
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button 
                                        @click="confirmRestore(department)"
                                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white text-sm font-medium rounded-lg hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md"
                                    >
                                        <i class="fas fa-undo mr-2"></i>
                                        Restore
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="p-12 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 bg-gradient-to-br from-orange-100 to-orange-200 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-archive text-orange-500 text-3xl"></i>
                        </div>
                        <p class="text-gray-700 text-lg font-semibold mb-2">No archived departments found</p>
                        <p class="text-gray-500 text-sm">All departments are currently active</p>
                    </div>
                </div>
            </div>

            <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="restoreDepartment">
                <template #title>
                    Confirm Restoration
                </template>
                <template #content>
                    Are you sure you want to restore the department "{{ departmentToRestore?.department_name }}"? This will make it active again.
                </template>
                <template #footer>
                    <PrimaryButton @click="showModal = false" class="mr-2">Cancel</PrimaryButton>
                    <DangerButton @click="restoreDepartment" class="bg-green-600 hover:bg-green-700">
                        <i class="fas fa-undo mr-2"></i>
                        Restore Department
                    </DangerButton>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>