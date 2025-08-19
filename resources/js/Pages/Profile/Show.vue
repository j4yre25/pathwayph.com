<script setup>
import { usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import UpdateCompanyProfileInformationForm from './Partials/UpdateCompanyProfileInformationForm.vue';

const user = usePage().props.user;
const company = usePage().props.company;

const queryParams = new URLSearchParams(window.location.search);
const editMode = queryParams.get('edit') || 'company';

const cancelEdit = () => {
  router.visit(route('profile.show'));
};
</script>

<template>
  <AppLayout title="Company Profile">
    <div class="py-6 bg-gray-50">
      <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg overflow-hidden border border-gray-200 shadow-sm">
          <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">
              Update Company Profile
            </h2>
            <UpdateCompanyProfileInformationForm :user="user" :company="company">
              <template #actions>
                <div class="flex items-center space-x-3">
                  <button 
                    @click.prevent="cancelEdit"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-md shadow-sm flex items-center"
                  >
                    Cancel
                  </button>
                  <button 
                    type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 transition-colors text-white py-2 px-6 rounded-md shadow-sm flex items-center"
                  >
                    Save
                  </button>
                </div>
              </template>
            </UpdateCompanyProfileInformationForm>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>