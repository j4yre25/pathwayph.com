<script setup>
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const confirmRestore = ref(false);
const graduateToRestore = ref(null);

const props = defineProps({
    graduates: {
        type: Array,
        default: () => ([]),
    },
});

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
    if (graduateToRestore.value) {
        restoreGraduate(graduateToRestore.value.id);
        confirmRestore.value = false;
        graduateToRestore.value = null;
    }
};

const goBack = () => {
    window.history.back();
};

const stats = computed(() => {
    return [
        {
            title: 'Total Archived',
            value: props.graduates.length,
            icon: 'fas fa-archive',
            color: 'text-orange-600',
            bgColor: 'bg-orange-100'
        }
    ];
});
</script>

<template>
    <AppLayout title="Archived Graduates">
        <Container>
            <!-- Back Button and Header -->
            <div class="flex items-center mt-6 mb-4">
                <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="flex items-center">
                    <i class="fas fa-archive text-orange-500 text-xl mr-2"></i>
                    <h1 class="text-2xl font-bold text-gray-800">Archived Graduates</h1>
                </div>
            </div>
            <p class="text-sm text-gray-500 mb-6 ml-9">View the full list of archived graduates or restore them.</p>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div v-for="(stat, index) in stats" :key="index" 
                     class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm relative overflow-hidden">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">{{ stat.title }}</p>
                            <p class="text-2xl font-bold">{{ stat.value }}</p>
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
                    <h2 class="font-semibold text-gray-800">Archived Graduates List</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr 
                                v-for="graduate in graduates" 
                                :key="graduate.id" 
                                class="hover:bg-gray-50 transition duration-150"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-user text-gray-400 mr-2"></i>
                                        <span class="text-sm font-medium text-gray-800">
                                            {{ graduate.first_name }} {{ graduate.middle_initial }} {{ graduate.last_name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-graduation-cap text-gray-400 mr-2"></i>
                                        <span class="text-sm text-gray-700">{{ graduate.graduate_program_completed }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-alt text-gray-400 mr-2"></i>
                                        <span class="text-sm text-gray-600">{{ new Date(graduate.created_at).toLocaleDateString() }}</span>
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
                                        @click="confirmRestoreGraduate(graduate)"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                    >
                                        <i class="fas fa-undo-alt mr-1"></i>
                                        Restore
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="graduates.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-archive text-gray-300 text-5xl mb-4"></i>
                                        <p class="text-gray-500 text-lg font-medium">No archived graduates found</p>
                                        <p class="text-gray-400 text-sm mt-1">Archived graduates will appear here</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <div v-if="confirmRestore" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="cancelRestore"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fas fa-undo-alt text-blue-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Restore Graduate</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Are you sure you want to restore this graduate? This will make the graduate record active again.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button 
                                type="button" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                                @click="proceedWithRestore"
                            >
                                Restore
                            </button>
                            <button 
                                type="button" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                @click="cancelRestore"
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
