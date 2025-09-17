<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import { router } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, computed } from 'vue';

const page = usePage();

const props = defineProps({
    archivedDepartments: Array,
    hr: Object
})

const showModal = ref(false);
const departmentToRestore = ref(null);

const restoreDepartment = () => {
    router.post(route('company.departments.restore', { 
        id: departmentToRestore.value.id 
    }), {}, {
        onSuccess: () => {
            console.log('Department restored successfully!');
            showModal.value = false; // Close the modal after success
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
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Archived Departments</h2>
                    <p class="text-sm text-gray-600 mt-1">Manage and restore archived departments</p>
                </div>
                <div class="flex space-x-3">
                    <Link href="/company/departments" class="flex-shrink-0">
                        <PrimaryButton class="flex items-center bg-gray-700 hover:bg-gray-800">
                            <i class="fas fa-list mr-2"></i>
                            All Departments
                        </PrimaryButton>
                    </Link>
                    <Link href="/company/departments/manage" class="flex-shrink-0">
                        <PrimaryButton class="flex items-center">
                            <i class="fas fa-tasks mr-2"></i>
                            Manage Departments
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <Container class="py-8">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-200 flex items-center">
                    <i class="fas fa-archive text-red-500 mr-2"></i>
                    <div>
                        <h3 class="font-medium text-gray-700">Archived Departments</h3>
                        <p class="text-sm text-gray-500">Departments that have been archived and can be restored</p>
                    </div>
                </div>

                <div v-if="archivedDepartments && archivedDepartments.length > 0" class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gradient-to-r from-red-50 to-pink-50 text-sm font-semibold text-gray-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Department Name</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Created By</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date Created</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date Archived</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="department in archivedDepartments" :key="department.id" class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-building text-red-600"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ department.department_name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ department.hr_name || 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ new Date(department.created_at).toLocaleDateString() }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ new Date(department.deleted_at).toLocaleDateString() }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-archive mr-1"></i>
                                        Archived
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button 
                                        @click="confirmRestore(department)"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150"
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
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-archive text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-500 text-lg font-medium">No archived departments found</p>
                        <p class="text-gray-400 text-sm mt-1">All departments are currently active</p>
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