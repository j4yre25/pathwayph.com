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
    all_users: Array,
    // user: Object

})



const filters = ref({
    role: 'all',
    date_from: '',
    date_to: '',
    status: 'all',

});


const applyFilters = () => {
    const activeFilters = {};

    if (filters.value.role !== 'all') {
        activeFilters.role = filters.value.role;
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

    router.get(route('admin.manage_users.list'), activeFilters, { preserveState: true });
};


const filteredUsers = computed(() => {
    return props.all_users.filter(user => {
        const matchesRole = filters.value.role === 'all' || user.role === filters.value.role;
        const matchesDateFrom = !filters.value.date_from || new Date(user.created_at) >= new Date(filters.value.date_from);
        const matchesDateTo = !filters.value.date_to || new Date(user.created_at) <= new Date(filters.value.date_to);
        const matchesStatus = filters.value.status === 'all' || 
            (filters.value.status === 'active' && user.is_approved) || 
            (filters.value.status === 'inactive' && !user.is_approved);

        return matchesRole && matchesDateFrom && matchesDateTo && matchesStatus;
    });
});

const showModal = ref(false);






</script>


<template>
    <AppLayout title="List Of Users">
        <template #header>
            List of Users

        </template>

        <Container class="py-16">
            <div class="mb-6">
                <form @submit.prevent="applyFilters" class="flex flex-wrap gap-4">
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">User Level</label>
                        <select v-model="filters.role" id="role"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="all">All</option>
                            <option value="peso">Peso</option>
                            <option value="company">Company</option>
                            <option value="institution">Institution</option>
                        </select>
                    </div>

                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                        <input v-model="filters.date_from" type="date" id="date_from"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                        <input v-model="filters.date_to" type="date" id="date_to"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select v-model="filters.status" id="status"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="all">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>


                    <div class="flex items-end">
                        <PrimaryButton type="submit">Apply Filters</PrimaryButton>
                    </div>
                </form>

            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="border border-gray-200 px-6 py-3 text-left">User Level</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Name</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Date Creation</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <tr v-for="user in filteredUsers" :key="user.id"
                            class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="border border-gray-200 px-6 py-4">{{ user.role }}</td>
                            <td class="border border-gray-200 px-6 py-4">
                                <!-- Conditionally display the name based on role -->

                                <template v-if="user.role === 'company'">
                                    {{ user.company_name }}
                                </template>
                                <template v-else-if="user.role === 'institution'">
                                    {{ user.institution_career_officer_first_name }} {{
                                        user.institution_career_officer_last_name }}
                                </template>
                                <template v-else-if="user.role === 'peso'">
                                    {{ user.peso_first_name }} {{ user.peso_last_name }}

                                </template>
                                <template v-else>
                                    {{ user.name }}
                                </template>
                            </td>
                            
                            <td class="border border-gray-200 px-6 py-4">
                                {{ new Date(user.created_at).toLocaleDateString() }}
                            </td>

                            <!-- Status -->
                            <td class="border border-gray-200 px-6 py-4">
                                <span :class="user.is_approved ? 'text-green-600' : 'text-red-600'"
                                    class="font-semibold">
                                    {{ user.is_approved ? 'Active' : 'Inactive' }}
                                </span>
                            </td>






                        </tr>
                    </tbody>
                </table>
            </div>
            <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="handleDelete">
                <template #title>
                    Confirm Deletion
                </template>
                <template #content>
                    Are you sure you want to delete this user?
                </template>
                <template #footer>
                    <PrimaryButton @click="showModal = false">Cancel</PrimaryButton>
                    <DangerButton @click="handleDelete">Delete</DangerButton>
                </template>
            </ConfirmationModal>


        </Container>
    </AppLayout>




</template>