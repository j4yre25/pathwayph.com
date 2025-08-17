
<script setup>
import { ref, onMounted, computed, onBeforeUnmount } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Modal from '@/Components/Modal.vue';


const props = defineProps({
    graduates: { type: [Array, Object], default: () => [] },
});

const page = usePage();
const user = page.props.auth?.user;

console.log('Graduates', props);

const auth = page.props;
const graduates = page.props.graduates;
const roles = page.props.roles;
const hrCount = page.props.hrCount;
const main = page.props.main;
const title = page.props.title;

const showingNavigationDropdown = ref(false);
const showApprovalModal = ref(false)

onMounted(() => {
  if (page.props.needsApproval) {
    showApprovalModal.value = true;
  }
});

const showNotifications = ref(false);
const notifications = computed(() => usePage().props.notifications || []);
const notifBell = ref(null);
const notifDropdown = ref(null);

function toggleNotifications() {
    showNotifications.value = !showNotifications.value;
}

function handleClickOutside(event) {
    if (
        notifDropdown.value &&
        !notifDropdown.value.contains(event.target) &&
        notifBell.value &&
        !notifBell.value.contains(event.target)
    ) {
        showNotifications.value = false;
    }
}

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
});
onBeforeUnmount(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});


// Safe logging
console.log('Auth:', auth);
console.log('Graduates:', graduates);
console.log('Role:', roles);
console.log('HR Count:', hrCount);
console.log('isMainHR:', main);
console.log('Company Name:', page.props.app?.currentUser?.company?.company_name);
console.log('Sector:', page.props.sectors ?? 'No sectors found');
console.log('Permission to manage:', page.props.permissions?.canManageInstitution);

const sector = page.props.sectors
console.log('Sector:', sector);
console.log('Role', page.props.auth.user.role);

console.log(page.props.app.currentUser.company?.company_name)
console.log('user.company:', user.company);
console.log('user.hr:', user.hr);


const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};

console.log(page.props.permissions.canManageInstitution)
</script>

<template>
    <div>

        <Head :title="title" />

        <Banner />
        
        <Modal v-model="showApprovalModal">
            <template #header>
            <h2 class="text-xl font-bold text-yellow-600">Waiting for Approval</h2>
            </template>
            <template #body>
            <p class="mb-6 text-gray-700">
                Your company account is still waiting for admin approval.<br>
                You will be notified once your account is approved.
            </p>
            </template>
            <template #footer>
            <button class="btn btn-primary" @click="showApprovalModal = false">OK</button>
            </template>
        </Modal>

        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex ">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                <ApplicationMark class="block h-9 w-auto" />
                                </Link>
                            </div>

                            <!-- Company Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                                v-if="page.props.auth.user.role === 'company' && page.props.auth.user.is_approved">

                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')"
                                    :disabled="!page.props.auth.user.is_approved">
                                    Dashboard
                                </NavLink>


                                <NavLink
                                    v-if="page.props.auth.user.role === 'company' && page.props.auth.user.is_approved"
                                    :href="route('company.jobs', { user: page.props.auth.user.id })"
                                    :active="route().current('company.jobs')"
                                    :disabled="!page.props.auth.user.is_approved">
                                    Job Listing
                                </NavLink>

                                <NavLink v-if="page.props.auth.user.role === 'company'"
                                    :href="route('company.job.applicants.index', { user: page.props.auth.user.id })"
                                    :active="route().current('company.job.applicants.index')"
                                    :disabled="!page.props.auth.user.is_approved">
                                    Manage Applicants
                                </NavLink>

                                <NavLink v-if="roles.isCompany"
                                    :href="route('company.manage-hrs', { user: page.props.auth.user.id })"
                                    :active="route().current('company.manage-hrs')" :disabled="!page.props.auth.user.is_approved">
                                    Human Resource Accounts
                                </NavLink>

                            
                                <NavLink v-if="page.props.auth.user.role === 'company'"
                                    :href="route('company.reports.list', { user: page.props.auth.user.id })"
                                    :active="route().current('company.reports.list')"
                                    :disabled="!page.props.auth.user.is_approved">
                                    Reports
                                </NavLink>
                            </div>

                            <!-- Graduate Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                                v-if="page.props.auth.user.role === 'graduate' && page.props.auth.user.is_approved">

                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')"
                                    :disabled="!page.props.auth.user.is_approved">
                                    Dashboard
                                </NavLink>
                                
                                <NavLink v-if="page.props.auth.user.role === 'graduate'" :href="route('job.inbox')"
                                    :active="route().current('job.inbox')"
                                    :disabled="!page.props.auth.user.is_approved">
                                    Job Inbox
                                </NavLink>

                                <NavLink v-if="page.props.auth.user.role === 'graduate'" :href="route('job.search')"
                                    :active="route().current('job.search')"
                                    :disabled="!page.props.auth.user.is_approved">
                                    Find Jobs
                                </NavLink>
                                
                            </div>

                            <!-- Institution Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                                v-if="page.props.auth.user.role === 'institution' && page.props.auth.user.is_approved">

                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')"
                                    :disabled="!page.props.auth.user.is_approved">
                                    Dashboard
                                </NavLink>
                                
                                
                                <NavLink
                                    v-if="page.props.permissions.canManageGraduate && page.props.auth.user.is_approved && page.props.auth.user.role === 'institution'"
                                    :href="route('graduates.index')" :active="route().current('graduates.index')">
                                    Graduate
                                </NavLink>
    
                                 <NavLink
                                    v-if="page.props.permissions.canManageApprovalGraduate && page.props.auth.user.is_approved && page.props.auth.user.role === 'institution'"
                                    :href="route('institutions.reports.index')" :active="route().current('institutions.reports.index')">
                                    Reports
                                </NavLink>
    
                                <NavLink
                                    v-if="page.props.permissions.canManageInstitution && page.props.auth.user.is_approved && page.props.auth.user.role === 'institution'"
                                    :href="route('school-years', { user: page.props.auth.user.id })"
                                    :active="route().current('school-years')">
                                    Manage School Year
                                </NavLink>
    
                                <NavLink
                                    v-if="page.props.permissions.canManageInstitution && page.props.auth.user.is_approved && page.props.auth.user.role === 'institution'"
                                    :href="route('degrees', { user: page.props.auth.user.id })"
                                    :active="route().current('degrees')">
                                    Manage Degrees
                                </NavLink>
    
                                <NavLink
                                    v-if="page.props.permissions.canManageInstitution && page.props.auth.user.is_approved && page.props.auth.user.role === 'institution'"
                                    :href="route('programs', { user: page.props.auth.user.id })"
                                    :active="route().current('programs')">
                                    Manage Programs
                                </NavLink>
    
                                <NavLink
                                    v-if="page.props.permissions.canManageInstitution && page.props.auth.user.is_approved && page.props.auth.user.role === 'institution'"
                                    :href="route('careeropportunities', { user: page.props.auth.user.id })"
                                    :active="route().current('careeropportunities')">
                                    Manage Career Opportunities
                                </NavLink>
    
                                <NavLink
                                    v-if="page.props.permissions.canManageInstitution && page.props.auth.user.is_approved && page.props.auth.user.role === 'institution'"
                                    :href="route('instiskills', { user: page.props.auth.user.id })"
                                    :active="route().current('instiskills')">
                                    Manage Skills
                                </NavLink>
    
                                <!-- Manage Approval Link -->
                                <NavLink
                                    v-if="page.props.permissions.canManageApprovalGraduate && page.props.auth.user.is_approved && page.props.auth.user.role === 'institution'"
                                    :href="route('graduates.manage')" :active="route().current('graduates.manage')">
                                    Manage Approval
                                </NavLink>
    
                                <NavLink
                                    v-if="page.props.permissions.canManageApprovalGraduate && page.props.auth.user.is_approved && page.props.auth.user.role === 'institution'"
                                    :href="route('internship-programs.index')" :active="route().current('internship-programs.index')">
                                    Manage Internship
                                </NavLink>
                                
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                                <NavLink :href="route('dashboard')" v-if="page.props.auth.user.role === 'peso'"
                                    :active="route().current('dashboard')"
                                    :disabled="!page.props.auth.user.is_approved">
                                    Dashboard
                                </NavLink>


                                

                                <!-- Graduate Portfolio -->
                                <!-- <NavLink v-if="page.props.auth.user.role === 'graduate'" :href="route('graduate.portfolio')"
                                    :active="route().current('graduate.portfolio')"
                                    :disabled="!page.props.auth.user.is_approved">
                                    Portfolio
                                </NavLink> -->



                                <NavLink v-if="page.props.auth.user.role === 'peso'"
                                    :href="route('admin.manage_users', { user: page.props.auth.user.id })"
                                    :active="route().current('admin.manage_users')">
                                    Manage Users

                                </NavLink>




                                <NavLink v-if="page.props.auth.user.role === 'peso'"
                                    :href="route('sectors', { user: page.props.auth.user.id })"
                                    :active="route().current('sectors')">
                                    Manage Sectors
                                </NavLink>

                                <NavLink :href="route('categories.index', { user: page.props.auth.user.id })"
                                    v-if="page.props.auth.user.role === 'peso'" Categories
                                    :active="route().current('categories.index')">
                                    Manage Categories
                                </NavLink>







                                <NavLink v-if="page.props.auth.user.role === 'peso' && page.props.auth.user.is_approved"
                                    :href="route('peso.jobs', { user: page.props.auth.user.id })"
                                    :active="route().current('peso.jobs')"
                                    :disabled="!page.props.auth.user.is_approved">
                                    PESO Job Posting
                                </NavLink>





                                <NavLink :href="route('peso.job-referrals.index')"
                                    v-if="page.props.auth.user.role === 'peso'" Categories
                                    :active="route().current('peso.job-referrals.index')">
                                    Manage Job Referrals
                                </NavLink>

                                <NavLink :href="route('peso.career-guidance')"
                                    v-if="page.props.auth.user.role === 'peso'" Categories
                                    :active="route().current('peso.career-guidance')">
                                    Manage Career Guidance
                                </NavLink>

                                <NavLink v-if="page.props.auth.user.role === 'peso'"
                                    :href="route('peso.reports.index', { user: page.props.auth.user.id })"
                                    :active="route().current('peso.reports.index')"
                                    :disabled="!page.props.auth.user.is_approved">
                                    Reports
                                </NavLink>
                                <!--
                                <NavLink :href="route('jobs.list')" v-if="page.props.auth.user.role === 'peso'" Categories
                                    :active="route().current('job.list')">
                                    Reports
                                </NavLink> -->

                                <!-- Institution Link -->



                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <div class="ms-3 relative">
                                <!-- Teams Dropdown -->
                                <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.current_team.name }}

                                                <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="w-60">
                                            <!-- Team Management -->
                                            <div class="block px-4 py-2 text-xs text-gray-400">
                                                Manage Team
                                            </div>

                                            <!-- Team Settings -->
                                            <DropdownLink
                                                :href="route('teams.show', $page.props.auth.user.current_team)">
                                                Team Settings
                                            </DropdownLink>

                                            <div class="ms-3 relative">
                                                <button @click="showNotifications = !showNotifications" class="relative focus:outline-none">
                                                    <i class="fas fa-bell text-xl"></i>
                                                    <span v-if="notifications.length" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full text-xs px-1">
                                                        {{ notifications.length }}
                                                    </span>
                                                </button>
                                                <div v-if="showNotifications" class="absolute right-0 mt-2 w-80 bg-white shadow-lg rounded z-50">
                                                    <div v-if="notifications.length">
                                                        <div v-for="notif in notifications" :key="notif.id" class="p-3 border-b last:border-b-0">
                                                            <div class="font-semibold">{{ notif.title }}</div>
                                                            <div class="text-sm text-gray-600">{{ notif.body }}</div>
                                                            <div class="text-xs text-gray-400">{{ notif.created_at }}</div>
                                                        </div>
                                                    </div>
                                                    <div v-else class="p-3 text-gray-500 text-center">No notifications</div>
                                                </div>
                                            </div>

                                            <DropdownLink v-if="$page.props.jetstream.canCreateTeams"
                                                :href="route('teams.create')">
                                                Create New Team
                                            </DropdownLink>

                                            <!-- Team Switcher -->
                                            <template v-if="$page.props.auth.user.all_teams.length > 1">
                                                <div class="border-t border-gray-200" />
                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Switch Teams
                                                </div>

                                                <template v-for="team in $page.props.auth.user.all_teams"
                                                    :key="team.id">
                                                    <form @submit.prevent="switchToTeam(team)">
                                                        <DropdownLink as="button">
                                                            <div class="flex items-center">
                                                                <svg v-if="team.id == $page.props.auth.user.current_team_id"
                                                                    class="me-2 size-5 text-green-400"
                                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke-width="1.5"
                                                                    stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>

                                                                <div>{{ team.name }}</div>
                                                            </div>
                                                        </DropdownLink>
                                                    </form>
                                                </template>
                                            </template>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                       <span class="inline-flex rounded-md items-center relative">
                                            <!-- Notification Bell -->
                                            <button
                                                ref="notifBell"
                                                @click.stop="toggleNotifications"
                                                class="relative focus:outline-none mr-3"
                                                aria-label="Notifications"
                                            >
                                                <i class="fas fa-bell text-xl"></i>
                                                <span v-if="notifications.length" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full text-xs px-1">
                                                    {{ notifications.length }}
                                                </span>
                                            </button>
                                            <!-- Profile Photo -->
                                            <button v-if="$page.props.jetstream.managesProfilePhotos"
                                                class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                                <img class="size-8 rounded-full object-cover"
                                                    :src="$page.props.auth.user.profile_photo_url"
                                                    :alt="$page.props.auth.user.name">
                                            </button>
                                            <!-- User Name -->
                                            <template v-if="$page.props.auth.user.role === 'graduate'">
                                                {{ $page.props.auth.user.graduate_first_name }}
                                            </template>
                                            <template v-if="$page.props.auth.user.role === 'peso'">
                                                {{ $page.props.auth.user.peso_first_name }}
                                            </template>
                                            <template v-else-if="$page.props.auth.user.role === 'company'">
                                                {{ $page.props.app.currentUser.company?.company_name }}
                                            </template>
                                            <template v-else-if="$page.props.auth.user.role === 'institution'">
                                                {{ $page.props.auth.user.institution_name }}
                                            </template>
                                            <template v-else>
                                                {{ $page.props.auth.user.name }}
                                            </template>
                                            <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg"
                                                fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                            </svg>
                                            <!-- Notification Dropdown -->
                                            <div
                                                v-if="showNotifications"
                                                ref="notifDropdown"
                                                class="absolute right-0 mt-2 w-80 bg-white shadow-lg rounded z-50"
                                                style="top: 2.5rem;"
                                            >
                                                <div v-if="notifications.length">
                                                    <div v-for="notif in notifications" :key="notif.id" class="p-3 border-b last:border-b-0">
                                                        <div class="font-semibold">{{ notif.title }}</div>
                                                        <div class="text-sm text-gray-600">{{ notif.body }}</div>
                                                        <div class="text-xs text-gray-400">{{ notif.created_at }}</div>
                                                    </div>
                                                </div>
                                                <div v-else class="p-3 text-gray-500 text-center">No notifications</div>
                                            </div>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Manage Account
                                        </div>
                                        <DropdownLink
                                            v-if="page.props.auth.user.role === 'peso' && page.props.auth.user.is_approved"
                                            :disabled="!page.props.auth.user.is_approved" :href="route('peso.profile')">
                                            Profile
                                        </DropdownLink>

                                        <DropdownLink
                                            v-if="page.props.auth.user.role === 'institution'"
                                            :href="route('institution.profile')">
                                            Profile
                                        </DropdownLink>


                                        <DropdownLink v-if="page.props.auth.user.role === 'graduate'"
                                            :href="route('profile.index', { user: page.props.auth.user.id })"
                                            :active="route().current('profile.index')">
                                            Profile Settings </DropdownLink>

                                        <DropdownLink
                                            v-if="page.props.auth.user.role === 'graduate' && page.props.graduate"
                                            :href="route('graduates.profile', page.props.graduate.id)"
                                            :active="route().current('graduates.profile')">
                                            View Profile
                                        </DropdownLink>

                                        <DropdownLink v-if="page.props.auth.user.role === 'peso'"
                                            :href="route('admin.register')">
                                            Admin Registration
                                        </DropdownLink>

                                        <DropdownLink v-if="page.props.auth.user.role === 'institution'"
                                            :href="route('careerofficer.register')">
                                            Career Officer Registration
                                        </DropdownLink>

                                        <DropdownLink
                                            v-if="page.props.auth.user.role === 'company'"
                                            :href="route('company.profile')">
                                            Profile
                                        </DropdownLink>

                                        <DropdownLink
                                            v-if="page.props.auth.user.role === 'company' && page.props.auth.user.is_approved"
                                            :disabled="!page.props.auth.user.is_approved" :href="route('hr.register')">
                                            Add Human Resource Officer (HRO)

                                        </DropdownLink>

                                        <DropdownLink v-if="$page.props.jetstream.hasApiFeatures"
                                            :href="route('api-tokens.index')">
                                            API Tokens
                                        </DropdownLink>

                                        <div class="border-t border-gray-200" />

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                                @click="showingNavigationDropdown = !showingNavigationDropdown">
                                <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path
                                        :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }"
                    class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">

                    </div>



                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                                <img class="size-10 rounded-full object-cover"
                                    :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                            </div>

                            <div>
                                <div class="font-medium text-base text-gray-800">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="font-medium text-sm text-gray-500">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                                Profile
                            </ResponsiveNavLink>

                            <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures"
                                :href="route('api-tokens.index')" :active="route().current('api-tokens.index')">
                                API Tokens
                            </ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    Log Out
                                </ResponsiveNavLink>
                            </form>

                            <!-- Team Management -->
                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                <div class="border-t border-gray-200" />

                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    Manage Team
                                </div>

                                <!-- Team Settings -->
                                <ResponsiveNavLink :href="route('teams.show', $page.props.auth.user.current_team)"
                                    :active="route().current('teams.show')">
                                    Team Settings
                                </ResponsiveNavLink>

                                <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams"
                                    :href="route('teams.create')" :active="route().current('teams.create')">
                                    Create New Team
                                </ResponsiveNavLink>

                                <!-- Team Switcher -->
                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                    <div class="border-t border-gray-200" />

                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        Switch Teams
                                    </div>

                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                        <form @submit.prevent="switchToTeam(team)">
                                            <ResponsiveNavLink as="button">
                                                <div class="flex items-center">
                                                    <svg v-if="team.id == $page.props.auth.user.current_team_id"
                                                        class="me-2 size-5 text-green-400"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <div>{{ team.name }}</div>
                                                </div>
                                            </ResponsiveNavLink>
                                        </form>
                                    </template>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow">
                <div class="max-w-full mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
