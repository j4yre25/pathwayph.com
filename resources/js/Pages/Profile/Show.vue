<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import UpdateCompanyProfileInformationForm from './Partials/UpdateCompanyProfileInformationForm.vue';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});

const user = computed(() => usePage().props.auth.user);
const queryParams = new URLSearchParams(window.location.search);
const editMode = queryParams.get('edit'); // 'user' or 'company'

const isProfileInfoOpen = ref(false);
const isPasswordUpdateOpen = ref(false);
const isTwoFactorAuthOpen = ref(false);
const isLogoutSessionsOpen = ref(false);
const isDeleteAccountOpen = ref(false);


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
                    <!-- Update Profile Information Section -->
                    <div v-if="editMode === 'user' && $page.props.jetstream.canUpdateProfileInformation">
                        <h2 id="accordion-collapse-heading-1">
                            <button type="button" class="flex items-center justify-between w-full p-5 font-medium rtl:text-right text-gray-500 border border-b-0 border-gray-200 rounded-t-xl  dark:border-gray-700 dark:text-gray-400 hover:bg-white dark:hover:bg-white gap-3" data-accordion-target="#accordion-collapse-body-1" aria-expanded="true" aria-controls="accordion-collapse-body-1" @click="isProfileInfoOpen = !isProfileInfoOpen">
                                <h3 class="text-lg font-medium text-gray-900"> Update Profile Information </h3>
                                <svg data-accordion-icon class="w-3 h-3 shrink-0" :class="{ 'rotate-180': isProfileInfoOpen }" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                </svg>
                            </button>
                        </h2>
                        <div id="accordion-collapse-body-1" :class="{ 'hidden': !isProfileInfoOpen }" aria-labelledby="accordion-collapse-heading-1">
                            <div class="p-5 border border-b-0 border-gray-200 dark:border-gray-700">
                                <UpdateProfileInformationForm :user="$page.props.auth.user" />
                                <SectionBorder />
                            </div>
                        </div>
                    </div>

                    <div v-if="editMode === 'company' && $page.props.jetstream.canUpdateProfileInformation">
                        <h2 id="accordion-collapse-heading-1">
                            <button type="button" class="flex items-center justify-between w-full p-5 font-medium rtl:text-right text-gray-500 border border-b-0 border-gray-200 rounded-t-xl  dark:border-gray-700 dark:text-gray-400 hover:bg-white dark:hover:bg-white gap-3" data-accordion-target="#accordion-collapse-body-1" aria-expanded="true" aria-controls="accordion-collapse-body-1" @click="isProfileInfoOpen = !isProfileInfoOpen">
                                <h3 class="text-lg font-medium text-gray-900"> Update Company Profile Information </h3>
                                <svg data-accordion-icon class="w-3 h-3 shrink-0" :class="{ 'rotate-180': isProfileInfoOpen }" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                </svg>
                            </button>
                        </h2>
                        <div id="accordion-collapse-body-1" >
                            <div class="p-5 border border-b-200 border-gray-200 dark:border-gray-700">
                                <UpdateCompanyProfileInformationForm :user="$page.props.auth.user" />
                                
                            </div>
                        </div>
                    </div>

                    <!-- Update Password Section -->
                    <div v-if="editMode === 'user' && $page.props.jetstream.canUpdatePassword">
                        <h2 id="accordion-collapse-heading-2">
                            <button type="button" class="flex items-center justify-between w-full p-5 font-medium rtl:text-right text-gray-500 border border-b-0 border-gray-200  dark:border-gray-700 dark:text-gray-400 hover:bg-white dark:hover:bg-white gap-3 hover:text-gray" data-accordion-target="#accordion-collapse-body-2" aria-expanded="false" aria-controls="accordion-collapse-body-2" @click="isPasswordUpdateOpen = !isPasswordUpdateOpen">
                                <h3 class="text-lg font-medium text-gray-900 hover:text-white"> Update Password </h3>
                                <svg data-accordion-icon class="w-3 h-3 shrink-0" :class="{ 'rotate-180': isPasswordUpdateOpen }" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                </svg>
                            </button>
                        </h2>
                        <div id="accordion-collapse-body-2" :class="{ 'hidden': !isPasswordUpdateOpen }" aria-labelledby="accordion-collapse-heading-2">
                            <div class="p-5 border border-b-0 border-gray-200 dark:border-gray-700">
                                <UpdatePasswordForm />
                                <SectionBorder />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>