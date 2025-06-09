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
            <!-- Desktop Table (Shown on large screens and above) -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300 table-fixed shadow rounded">
                    <thead class="bg-blue-100 text-gray-700 uppercase text-sm">
                        <tr>
                            <th class="w-[20%] px-4 py-3 border text-center">Full Name</th>
                            <th class="w-[25%] px-4 py-3 border text-center">Email Address</th>
                            <th class="w-[20%] px-4 py-3 border text-center">Contact Details</th>
                            <th class="w-[15%] px-4 py-3 border text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="hr in hrs" :key="hr.User_Email" class="hover:bg-gray-50">
                            <td class="px-4 py-3 border">{{ hr.HR_Name }}</td>
                            <td class="px-4 py-3 border">{{ hr.User_Email }}</td>
                            <td class="px-4 py-3 border">{{ hr.Mob_Num }}</td>
                            <td class="px-4 py-3 border text-center">
                                <span
                                    :class="hr.Status === 'active'
                                        ? 'bg-green-100 text-green-700 px-2 py-1 rounded-full text-md font-medium'
                                        : 'bg-gray-200 text-gray-600 px-2 py-1 rounded-full text-xs italic'">
                                    {{ hr.Status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>

            <!-- Mobile Card Layout (Shown on screens below lg) -->
            <div class="block lg:hidden space-y-4">
                <div
                    v-for="hr in hrs"
                    :key="hr.User_Email"
                    class="bg-white border border-gray-300 rounded shadow-sm p-4">
                    <div class="mb-2">
                        <span class="font-semibold">HR Name:</span> {{ hr.HR_Name }}
                    </div>
                    <div class="mb-2">
                        <span class="font-semibold">Email:</span> {{ hr.User_Email }}
                    </div>
                    <div class="mb-2">
                        <span class="font-semibold">Contact:</span> {{ hr.Mob_Num }}
                    </div>
                    <div class="mb-4">
                        <span class="font-semibold">Company:</span> {{ hr.Company_Name }}
                    </div>
                    <div class="mb-2">
                        <span class="font-semibold">Status:</span>
                        <span
                            :class="hr.Status === 'active'
                            ? 'text-green-600 font-semibold'
                            : 'text-gray-500 italic'"
                        >
                            {{ hr.Status }}
                        </span>
                    </div>
                </div>
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
