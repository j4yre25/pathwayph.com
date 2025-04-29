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






const showModal = ref(false);
const userToRestore = ref(null);


const restoreUser = () => {
    console.log(route('admin.manage_users.restore', { user: userToRestore.value.id })); // Log the generated URL
    router.post(route('admin.manage_users.restore', { 
        user: userToRestore.value.id 
    }), {}, {
        onSuccess: () => {
            console.log('User restored successfully!');
            showModal.value = false; // Close the modal after success
        }
    });
};

const confirmRestore = (user) => {
    userToRestore.value = user;
    showModal.value = true;
};




</script>


<template>
    <AppLayout title="Archived Users">
        <template #header>
            Archived Users

        </template>

        <Container class="py-16">


            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="border border-gray-200 px-6 py-3 text-left">User Level</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Name</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Date Creation</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Status</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Actions</th>

                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <tr v-for="user in all_users" :key="user.id" class="border-b border-gray-200 hover:bg-gray-100">
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
                            <td class="border border-gray-200 px-6 py-4">
                                <span class="text-red-600 font-semibold">Archived</span>
                            </td>
                            
                     
                            <td class="border border-gray-200 px-6 py-4">
                                <DangerButton class="mr-2"  @click="confirmRestore(user)">Restore User
                                </DangerButton>

                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="restoreUser">
                <template #title>
                    Confirm Deletion
                </template>
                <template #content>
                    Are you sure you want to restore this user?
                </template>
                <template #footer>
                    <PrimaryButton @click="showModal = false"  class="mr-2">Cancel</PrimaryButton>
                    <DangerButton @click="restoreUser">Restore</DangerButton>
                </template>
            </ConfirmationModal>


        </Container>
    </AppLayout>




</template>