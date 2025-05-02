<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import UpdateCompanyProfileInformationForm from './Partials/UpdateCompanyProfileInformationForm.vue';

const user = computed(() => usePage().props.auth?.user || null);
const queryParams = new URLSearchParams(window.location.search);
const editMode = queryParams.get('edit') || 'user'; // Default to 'user'

const isProfileInfoOpen = ref(false);
</script>

<template>
    <AppLayout title="Profile">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Profile
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <div id="accordion-collapse" data-accordion="collapse">
                    <div v-if="editMode === 'user' && $page.props?.jetstream?.canUpdateProfileInformation">
                        <h2 id="accordion-collapse-heading-1">
                            <button type="button" @click="isProfileInfoOpen = !isProfileInfoOpen">
                                <h3>Update Profile Information</h3>
                            </button>
                        </h2>
                        <div v-if="isProfileInfoOpen">
                            <UpdateProfileInformationForm v-if="user" :user="user" />
                        </div>
                    </div>

                    <div v-if="editMode === 'company' && $page.props?.jetstream?.canUpdateProfileInformation">
                        <h2 id="accordion-collapse-heading-2">
                            <button type="button" @click="isProfileInfoOpen = !isProfileInfoOpen">
                                <h3>Update Company Profile Information</h3>
                            </button>
                        </h2>
                        <div v-if="isProfileInfoOpen">
                            <UpdateCompanyProfileInformationForm v-if="user" :user="user" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>