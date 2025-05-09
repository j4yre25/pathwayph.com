<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import { router } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';


defineProps({
  hrs: Array
})

const editHR = (hr) => {
  // Logic for editing HR
  console.log('Edit HR', hr);
};

const deleteHR = async (hrId) => {
  // API call to delete HR
  if (confirm('Are you sure you want to delete this HR?')) {
    try {
      await axios.delete(`/api/hrs/${hrId}`);
      alert('HR deleted successfully!');
      // Reload HR list or update UI
    } catch (error) {
      console.error(error);
      alert('An error occurred while deleting the HR.');
    }
  }
};
</script>


<template>
    <AppLayout title="Manage Users">
        <template #header>
            Manage Users
        </template>

        <Container class="py-4">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300">
                    <thead>
                        <tr>
                        <th class="px-4 py-2 border">First Name</th>
                        <th class="px-4 py-2 border">Last Name</th>
                        <th class="px-4 py-2 border">Email</th>
                        <th class="px-4 py-2 border">Contact</th>
                        <th class="px-4 py-2 border">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="hr in hrs" :key="hr.id">
                        <td class="px-4 py-2 border">{{ hr.company_hr_first_name }}</td>
                        <td class="px-4 py-2 border">{{ hr.company_hr_last_name }}</td>
                        <td class="px-4 py-2 border">{{ hr.email }}</td>
                        <td class="px-4 py-2 border">{{ hr.contact_number }}</td>
                        <td class="px-4 py-2 border">
                            <PrimaryButton @click="editHR(hr)" class="text-blue-500 hover:underline">Activate</PrimaryButton>
                            <PrimaryButton @click="editHR(hr)" class="text-blue-500 hover:underline ml-2">Deactivate</PrimaryButton>
                            <PrimaryButton @click="deleteHR(hr.id)" class="text-red-500 hover:underline ml-2">Archived</PrimaryButton>
                        </td>
                        </tr>
                    </tbody>
                    </table>            
                </div>

            <!-- Confirmation Modal -->
            <ConfirmationModal @close="showModal = false" :show="showModal">
                <template #title>
                    Are you sure?
                </template>

                <template #content>
                    Are you sure you want to archive this user {{ userToArchive?.company_hr_first_name }} {{ userToArchive?.company_hr_last_name }}?
                </template>

                <template #footer>
                    <DangerButton @click="archiveUser" class="mr-2">
                        Archive User
                    </DangerButton>
                    <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>
