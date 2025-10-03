<script setup>
import { ref, onMounted, computed, onBeforeUnmount, watch } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import ApplicationMark from '@/Components/ApplicationMark.vue'
import Banner from '@/Components/Banner.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  graduates: { type: [Array, Object], default: () => [] },
  title: {
    type: String,
    default: '',
  },
})

const page = usePage()
const user = page.props.auth?.user


const auth = page.props
const graduates = page.props.graduates
const roles = page.props.roles
const hrCount = page.props.hrCount
const main = page.props.main
const title = page.props.title

const showingNavigationDropdown = ref(false)
const showApprovalModal = ref(false)

onMounted(() => {
  if (page.props.needsApproval) {
    showApprovalModal.value = true
  }
})

const showNotifications = ref(false)
const rawNotifications = computed(() => usePage().props.notifications || [])
const localNotifications = ref([])

onMounted(() => {
  localNotifications.value = JSON.parse(JSON.stringify(rawNotifications.value))
})

watch(rawNotifications, (nv) => {
  localNotifications.value = JSON.parse(JSON.stringify(nv))
})

const serverUnread = computed(() => usePage().props.notifications_count || 0)
const localUnread = ref(null)
const unreadDisplay = computed(() =>
  localUnread.value !== null ? localUnread.value : serverUnread.value
)

const notifBell = ref(null)
const notifDropdown = ref(null)
const marking = ref(false)

async function markNotificationsRead() {
  // Use the same endpoint as markAllNotifications (or keep if you add a separate route)
  if (marking.value) return
  marking.value = true
  try {
    await axios.post(route('notifications.markAll'))
    localUnread.value = 0
    localNotifications.value = localNotifications.value.map(n => ({ ...n, read_at: new Date().toISOString() }))
  } catch (e) {
    console.warn('Mark read failed', e)
  } finally {
    marking.value = false
  }
}

async function toggleNotifications() {
  showNotifications.value = !showNotifications.value
  if (showNotifications.value && unreadDisplay.value > 0) {
    await markNotificationsRead()
  }
}

const showMessages = ref(false)
const msgBell = ref(null)
const msgDropdown = ref(null)

const rawMessages = computed(() => usePage().props.messages || [])

// Track "read" threads locally so badge can drop instantly
const localReadThreadIds = ref(new Set())

const effectiveUnreadMessages = computed(() =>
  rawMessages.value.filter(t => (t.unread_count > 0) && !localReadThreadIds.value.has(t.conversation_id))
)
const unreadMessagesDisplay = computed(() => effectiveUnreadMessages.value.length)

async function markMessagesRead() {
  try {
    if (!unreadMessagesDisplay.value) return
    await axios.post(route('messages.markAll'))
    // Mark current thread ids as read locally
    effectiveUnreadMessages.value.forEach(t => localReadThreadIds.value.add(t.conversation_id))
  } catch (e) {
    console.warn('Mark messages read failed', e)
  }
}

async function toggleMessages() {
  showMessages.value = !showMessages.value
  if (showMessages.value && unreadMessagesDisplay.value > 0) {
    await markMessagesRead()
  }
}

async function openMessage(msg) {
  try {
    // Local mark so badge drops even if not navigating
    localReadThreadIds.value.add(msg.conversation_id)
    // Go to the conversation
    if (msg.conversation_id) window.location.href = route('messages.show', msg.conversation_id)
    else window.location.href = route('messages.index')
  } catch (e) {
    console.warn(e)
  }
}

// Extend outside click handler to close messages dropdown too
function handleClickOutside(e) {
  if (
    showNotifications.value &&
    notifDropdown.value &&
    !notifDropdown.value.contains(e.target) &&
    notifBell.value &&
    !notifBell.value.contains(e.target)
  ) {
    showNotifications.value = false
  }
  if (
    showMessages.value &&
    msgDropdown.value &&
    !msgDropdown.value.contains(e.target) &&
    msgBell.value &&
    !msgBell.value.contains(e.target)
  ) {
    showMessages.value = false
  }
}

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
})
onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleClickOutside)
})



const sector = page.props.sectors

const switchToTeam = (team) => {
  router.put(route('current-team.update'), {
    team_id: team.id,
  },
    {
      preserveState: false,
    })
}

const logout = () => {
  router.post(route('logout'))
}


async function openNotification(notif) {
  try {
    // mark single notification as read on server
    if (!notif.read_at) {
      await axios.post(route('notifications.markOne', notif.id))
      const idx = localNotifications.value.findIndex(n => n.id === notif.id)
      if (idx !== -1) localNotifications.value[idx].read_at = new Date().toISOString()
      if (unreadDisplay.value > 0) localUnread.value = Math.max(0, unreadDisplay.value - 1)
    }

    // normalize data payload
    const data = notif.data ?? {}
    // prefer explicit url, then payload url/link, then legacy link
    const target = notif.url || data.url || data.link || notif.link || null

    // For company notifications we expect application url in payload; fall back to target
    if (target) {
      // navigate to the url (preserves normal behavior)
      window.location.href = target
    } else {
      // No URL: open notifications list page
      window.location.href = route('notifications.index')
    }
  } catch (e) {
    console.warn('openNotification error', e)
  }
}

async function markAllNotifications() {
  await axios.post(route('notifications.markAll'))
  localUnread.value = 0
  localNotifications.value = localNotifications.value.map(n => ({ ...n, read_at: new Date().toISOString() }))
}

const isOnHold = computed(() => {
  return page.props.auth.user.status === 'on_hold';
});

const showOnHoldModal = ref(false);

onMounted(() => {
  if (isOnHold.value) {
    showOnHoldModal.value = true;
  }
});

</script>

<template>
  <div>
    <Banner />

    <Head :title="title" />

    <Modal v-model="showApprovalModal">
      <template #header>
        <h2 class="text-xl font-bold text-yellow-600">Waiting for Approval</h2>
      </template>
      <template #body>
        <p class="mb-6 text-gray-700">
          Your account is still waiting for admin approval.<br />
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


                <NavLink v-if="page.props.auth.user.role === 'company' && page.props.auth.user.is_approved"
                  :href="route('company.jobs', { user: page.props.auth.user.id })"
                  :active="route().current('company.jobs')" :disabled="!page.props.auth.user.is_approved">
                  Jobs
                </NavLink>

                <NavLink v-if="page.props.auth.user.role === 'company'"
                  :href="route('company.job.applicants.index', { user: page.props.auth.user.id })"
                  :active="route().current('company.job.applicants.index')"
                  :disabled="!page.props.auth.user.is_approved">
                  Applicants
                </NavLink>

                <NavLink v-if="roles.isCompany" :href="route('company.manage-hrs', { user: page.props.auth.user.id })"
                  :active="route().current('company.manage-hrs')" :disabled="!page.props.auth.user.is_approved">
                  HR & Departments
                </NavLink>


                <NavLink v-if="page.props.auth.user.role === 'company'"
                  :href="route('company.reports.list', { user: page.props.auth.user.id })"
                  :active="route().current('company.reports.list')" :disabled="!page.props.auth.user.is_approved">
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

                <!-- <NavLink v-if="page.props.auth.user.role === 'graduate'" :href="route('job.inbox')"
                  :active="route().current('job.inbox')"
                  :disabled="!page.props.auth.user.is_approved">
                  Job Inbox
                </NavLink> -->

                <NavLink v-if="page.props.auth.user.role === 'graduate'" :href="route('job.search')"
                  :active="route().current('job.search')" :disabled="!page.props.auth.user.is_approved">
                  Find Jobs
                </NavLink>
                <NavLink v-if="page.props.auth.user.role === 'graduate'" :href="route('graduate.referrals')"
                  :active="route().current('graduate.referrals')" :disabled="!page.props.auth.user.is_approved">
                  Referrals
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

                <!-- Manage Approval Link -->
                <NavLink
                  v-if="page.props.permissions.canManageApprovalGraduate && page.props.auth.user.is_approved && page.props.auth.user.role === 'institution'"
                  :href="route('graduates.manage')" :active="route().current('graduates.manage')">
                  Approval
                </NavLink>

                <NavLink
                  v-if="page.props.permissions.canManageInstitution && page.props.auth.user.is_approved && page.props.auth.user.role === 'institution'"
                  :href="route('institutions.entries', { user: page.props.auth.user.id })"
                  :active="route().current('institutions.entries')">
                  Manage Entries
                </NavLink>

                <NavLink
                  v-if="page.props.permissions.canManageApprovalGraduate && page.props.auth.user.is_approved && page.props.auth.user.role === 'institution'"
                  :href="route('institutions.career-guidance')"
                  :active="route().current('institutions.career-guidance')">
                  Career Guidance
                </NavLink>

                <NavLink
                  v-if="page.props.permissions.canManageApprovalGraduate && page.props.auth.user.is_approved && page.props.auth.user.role === 'institution'"
                  :href="route('internship-programs.index')" :active="route().current('internship-programs.index')">
                  Internships
                </NavLink>

                <NavLink
                  v-if="page.props.permissions.canManageApprovalGraduate && page.props.auth.user.is_approved && page.props.auth.user.role === 'institution'"
                  :href="route('institutions.reports.index')" :active="route().current('institutions.reports.index')">
                  Reports
                </NavLink>


              </div>

              <!-- Navigation Links -->
              <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                <NavLink :href="route('dashboard')" v-if="page.props.auth.user.role === 'peso'"
                  :active="route().current('dashboard')" :disabled="!page.props.auth.user.is_approved">
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
                  Users
                </NavLink>

                <NavLink v-if="page.props.auth.user.role === 'peso'" :href="route('admin.jobs.index')"
                  :active="route().current('admin.jobs.index')">
                  Jobs
                </NavLink>


                <NavLink v-if="page.props.auth.user.role === 'peso'"
                  :href="route('sectors', { user: page.props.auth.user.id })" :active="route().current('sectors')">
                  Sectors
                </NavLink>

                <NavLink :href="route('categories.index', { user: page.props.auth.user.id })"
                  v-if="page.props.auth.user.role === 'peso'" Categories :active="route().current('categories.index')">
                  Categories
                </NavLink>









                <!-- <NavLink v-if="page.props.auth.user.role === 'peso' && page.props.auth.user.is_approved"
                  :href="route('peso.jobs', { user: page.props.auth.user.id })"
                  :active="route().current('peso.jobs')"
                  :disabled="!page.props.auth.user.is_approved">
                  PESO Job Posting
                </NavLink> -->

                <NavLink :href="route('peso.job-referrals.index')" v-if="page.props.auth.user.role === 'peso'"
                  Categories :active="route().current('peso.job-referrals.index')">
                  Referrals
                </NavLink>

                <NavLink :href="route('peso.career-guidance')" v-if="page.props.auth.user.role === 'peso'" Categories
                  :active="route().current('peso.career-guidance')">
                  Seminars
                </NavLink>
                <!-- 
                                <NavLink v-if="page.props.auth.user.role === 'peso'"
                                    :href="route('peso.reports.index', { user: page.props.auth.user.id })"
                                    :active="route().current('peso.reports.index')"
                                    :disabled="!page.props.auth.user.is_approved">
                                    Reports (Old)
                                </NavLink> -->

                <NavLink v-if="page.props.auth.user.role === 'peso'"
                  :href="route('peso.reports.home', { user: page.props.auth.user.id })"
                  :active="route().current('peso.reports.home')" :disabled="!page.props.auth.user.is_approved">
                  Reports
                </NavLink>
                <NavLink v-if="page.props.auth.user.role === 'peso'"
                  :href="route('peso.reports.overview', { user: page.props.auth.user.id })"
                  :active="route().current('peso.reports.overview')" :disabled="!page.props.auth.user.is_approved">
                  Reports Overview
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

                        <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                          viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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
                      <DropdownLink :href="route('teams.show', $page.props.auth.user.current_team)">
                        Team Settings
                      </DropdownLink>

                      <button ref="notifBell" @click.stop="toggleNotifications" class="relative focus:outline-none mr-3"
                        aria-label="Notifications">
                        <i class="fas fa-bell text-xl"></i>
                        <div class="font-semibold">Notification</div>
                        <span v-if="unreadDisplay"
                          class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full text-xs px-1">
                          {{ unreadDisplay }}
                        </span>
                      </button>
                      <div v-if="showNotifications" class="absolute right-0 mt-2 w-80 bg-white shadow-lg rounded z-50">
                        <div v-if="localNotifications.length">
                          <div class="flex justify-between items-center px-3 pt-3 pb-1">
                            <div class="text-xs font-semibold text-gray-600">Notifications</div>
                            <button class="text-[10px] text-indigo-600 hover:underline"
                              @click.stop="markAllNotifications" v-if="unreadDisplay > 0">Mark all read</button>
                          </div>
                          <div class="max-h-96 overflow-y-auto divide-y">
                             <div v-for="notif in localNotifications" :key="notif.id"
                                class="p-3 cursor-pointer hover:bg-gray-50" @click.stop="openNotification(notif)">
                              <div class="flex items-start gap-3">
                                <!-- left avatar / icon -->
                                <div class="w-10 h-10 flex-shrink-0 rounded-full bg-gray-100 flex items-center justify-center text-gray-700">
                                  <template v-if="notif.data && notif.data.graduate_id">
                                    <i class="fas fa-user-graduate"></i>
                                  </template>
                                  <template v-else>
                                    <i class="fas fa-info-circle"></i>
                                  </template>
                                </div>

                                <div class="flex-1 min-w-0">
                                  <!-- Company-specific visual: show title + subtitle when company user -->
                                  <div v-if="page.props.auth.user.role === 'company'">
                                    <div class="font-semibold text-sm" :class="!notif.read_at ? 'text-gray-800' : 'text-gray-600'">
                                      {{ notif.title || (notif.data && notif.data.title) || 'Notification' }}
                                    </div>
                                    <div class="text-xs text-gray-600 mt-1">
                                      {{ (notif.data && notif.data.subtitle) || notif.subtitle || (notif.data && notif.data.body) || notif.body || '' }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-2">{{ notif.created_at }}</div>
                                  </div>

                                  <!-- Default (non-company) layout -->
                                  <div v-else>
                                    <div class="font-semibold text-sm" :class="!notif.read_at ? 'text-gray-800' : 'text-gray-600'">
                                      {{ notif.title || (notif.data && notif.data.title) || 'Notification' }}
                                    </div>
                                    <div class="text-xs text-gray-600 mt-1 line-clamp-2">
                                      {{ (notif.data && notif.data.body) || notif.body || '' }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-1">{{ notif.created_at }}</div>
                                  </div>
                                </div>

                                <!-- CTA badge for company so it's obvious -->
                                <div v-if="page.props.auth.user.role === 'company'" class="ms-3 flex items-center">
                                  <span class="inline-flex items-center px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded text-[11px] font-medium">
                                    View Applicant
                                  </span>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div v-else class="p-3 text-gray-500 text-center">No notifications</div>
                      </div>

                      <DropdownLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')">
                        Create New Team
                      </DropdownLink>

                      <!-- Team Switcher -->
                      <template v-if="$page.props.auth.user.all_teams.length > 1">
                        <div class="border-t border-gray-200" />
                        <div class="block px-4 py-2 text-xs text-gray-400">
                          Switch Teams
                        </div>

                        <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                          <form @submit.prevent="switchToTeam(team)">
                            <DropdownLink as="button">
                              <div class="flex items-center">
                                <svg v-if="team.id == $page.props.auth.user.current_team_id"
                                  class="me-2 size-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                  viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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
                      <button ref="notifBell" @click.stop="toggleNotifications" class="relative focus:outline-none mr-3"
                        aria-label="Notifications">
                        <i class="fas fa-bell text-xl"></i>
                        <!-- OLD: span v-if="notifications.length" -->
                        <span v-if="unreadDisplay"
                          class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full text-xs px-1">
                          {{ unreadDisplay }}
                        </span>
                      </button>

                      <!-- Messages (graduates only) -->
                      <button v-if="['graduate', 'company'].includes(page.props.auth.user.role)" ref="msgBell"
                        @click.stop="toggleMessages" class="relative focus:outline-none mr-3" aria-label="Messages">
                        <i class="fas fa-comment-dots text-xl"></i>
                        <span v-if="unreadMessagesDisplay"
                          class="absolute -top-1 -right-1 bg-blue-500 text-white rounded-full text-xs px-1">
                          {{ unreadMessagesDisplay }}
                        </span>
                      </button>

                      <!-- Messages Dropdown -->
                      <div v-if="showMessages && ['graduate', 'company'].includes(page.props.auth.user.role)"
                        ref="msgDropdown" class="absolute right-0 mt-2 w-96 bg-white shadow-lg rounded z-50"
                        style="top: 2.5rem;">
                        <div class="flex justify-between items-center px-3 pt-3 pb-1">
                          <div class="text-xs font-semibold text-gray-600">Messages</div>
                          <button class="text-[10px] text-indigo-600 hover:underline"
                            @click.stop="() => { showMessages = false; window.location.href = route('messages.index') }">
                            See all
                          </button>
                        </div>
                        <div v-if="rawMessages.length" class="max-h-96 overflow-y-auto divide-y">
                          <div v-for="msg in rawMessages" :key="msg.conversation_id || msg.id"
                            class="p-3 cursor-pointer hover:bg-gray-50" @click.stop="openMessage(msg)">
                            <div class="flex items-start gap-3">
                              <img :src="msg.sender_avatar_url || '/images/default-logo.png'" alt="avatar"
                                class="w-10 h-10 rounded-full object-cover border" />
                              <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                  <div class="font-semibold text-sm truncate"
                                    :class="(msg.unread_count > 0 && !localReadThreadIds.has(msg.conversation_id)) ? 'text-gray-800' : 'text-gray-600'">
                                    {{ msg.sender_name || '—' }}
                                  </div>
                                  <div class="text-[10px] text-gray-400 ml-2 shrink-0">
                                    {{ msg.created_at }}
                                  </div>
                                </div>
                                <div class="text-[11px] text-gray-500 truncate">
                                  {{ msg.sender_company || '—' }}
                                </div>
                                <div class="text-xs text-gray-600 mt-0.5 line-clamp-2"
                                  v-html="msg.body_preview || msg.body"></div>
                              </div>
                              <span v-if="msg.unread_count > 0"
                                class="ml-2 mt-1 inline-flex items-center justify-center min-w-5 h-5 rounded-full bg-blue-600 text-white text-[10px] px-1">
                                {{ msg.unread_count }}
                              </span>
                            </div>
                          </div>
                        </div>
                        <div v-else class="p-3 text-gray-500 text-center">No messages</div>
                      </div>

                      <!-- Profile Photo -->
                      <button v-if="$page.props.jetstream.managesProfilePhotos"
                        class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                        <img class="size-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url"
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
                      <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                      </svg>
                      <!-- Notification Dropdown -->
                      <div v-if="showNotifications" ref="notifDropdown"
                        class="absolute right-0 mt-2 w-80 bg-white shadow-lg rounded z-50" style="top: 2.5rem;">
                        <div v-if="localNotifications.length">
                          <div class="flex justify-between items-center px-3 pt-3 pb-1">
                            <div class="text-xs font-semibold text-gray-600">Notifications</div>
                            <button class="text-[10px] text-indigo-600 hover:underline"
                              @click.stop="markAllNotifications" v-if="unreadDisplay > 0">Mark all read</button>
                          </div>
                          <div class="max-h-96 overflow-y-auto divide-y">
                            <div v-for="notif in localNotifications" :key="notif.id"
                              class="p-3 cursor-pointer hover:bg-gray-50" @click.stop="openNotification(notif)">
                              <div class="flex justify-between items-start gap-2">
                                <div class="flex-1">
                                  <div class="font-semibold text-sm"
                                    :class="!notif.read_at ? 'text-gray-800' : 'text-gray-600'">
                                    {{ notif.title }}
                                  </div>
                                  <div class="text-xs text-gray-600 mt-0.5" v-html="notif.body"></div>
                                </div>
                                <span v-if="!notif.read_at"
                                  class="inline-block w-2 h-2 rounded-full bg-indigo-500 mt-1"></span>
                              </div>
                              <div class="text-[10px] text-gray-400 mt-1">{{ notif.created_at }}</div>
                              <div v-if="notif.status === 'job_invite'" class="mt-2">
                                <span
                                  class="inline-flex items-center px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded text-[10px] font-medium">
                                  Job Invite
                                </span>
                              </div>
                            </div>
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
                    <DropdownLink v-if="page.props.auth.user.role === 'peso' && page.props.auth.user.is_approved"
                      :disabled="!page.props.auth.user.is_approved" :href="route('peso.profile')">
                      Profile
                    </DropdownLink>

                    <DropdownLink v-if="page.props.auth.user.role === 'peso' && page.props.auth.user.is_approved"
                      :disabled="!page.props.auth.user.is_approved" :href="route('peso.profile.settings')">
                      Profile Settings
                    </DropdownLink>

                    <DropdownLink v-if="page.props.auth.user.role === 'institution'"
                      :href="route('institution.profile')">
                      Profile
                    </DropdownLink>

                    <DropdownLink v-if="page.props.auth.user.role === 'institution'"
                      :href="route('institution.profile.settings')">
                      Profile Settings
                    </DropdownLink>

                    <DropdownLink v-if="page.props.auth.user.role === 'graduate' && page.props.graduate"
                      :href="route('graduates.profile', page.props.graduate.id)"
                      :active="route().current('graduates.profile')">
                      View Profile
                    </DropdownLink>

                    <DropdownLink v-if="page.props.auth.user.role === 'graduate'"
                      :href="route('profile.index', { user: page.props.auth.user.id })"
                      :active="route().current('profile.index')">
                      Profile Settings </DropdownLink>

                    <DropdownLink v-if="page.props.auth.user.role === 'peso'" :href="route('admin.register')">
                      Admin Registration
                    </DropdownLink>

                    <DropdownLink v-if="page.props.auth.user.role === 'company'" :href="route('company.profile')">
                      Profile
                    </DropdownLink>

                    <DropdownLink v-if="page.props.auth.user.role === 'company'"
                      :href="route('company.profile.settings')">
                      Profile Settings
                    </DropdownLink>

                    <DropdownLink v-if="page.props.auth.user.role === 'company' && page.props.auth.user.is_approved"
                      :disabled="!page.props.auth.user.is_approved" :href="route('hr.register')">
                      Add Human Resource Officer (HRO)

                    </DropdownLink>

                    <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">
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
                  <path :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                  <path :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }" class="sm:hidden">
          <div class="pt-2 pb-3 space-y-1">

          </div>

          <!-- Responsive Settings Options -->
          <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="flex items-center px-4">
              <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                <img class="size-10 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url"
                  :alt="$page.props.auth.user.name">
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

              <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')"
                :active="route().current('api-tokens.index')">
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

                <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')"
                  :active="route().current('teams.create')">
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
                            class="me-2 size-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none"
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


  <Modal v-model="showOnHoldModal">
    <template #header>
      <h2 class="text-xl font-bold text-yellow-600 flex items-center">
        <i class="fas fa-pause-circle mr-2"></i>
        Account On Hold
      </h2>
    </template>
    <template #body>
      <div class="text-yellow-700">
        Your company account is currently <b>On Hold</b>.<br>
        You cannot post, edit, or manage jobs/applicants.<br>
        Please contact PESO for assistance.
      </div>
    </template>
    <template #footer>
      <button class="btn btn-primary" @click="showOnHoldModal = false">OK</button>
    </template>
  </Modal>

</template>
