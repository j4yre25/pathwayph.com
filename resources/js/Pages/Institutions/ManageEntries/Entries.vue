<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()

const userId = page.props.auth?.user?.id
</script>

<template>
  <AppLayout title="Manage Entries">
    <Container class="py-8">
      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
        <!-- School Years KPI Card -->
        <Link :href="route('school-years', { user: userId })" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500 hover:shadow-md transition group flex flex-col items-center">
          <div class="bg-blue-100 rounded-full p-4 mb-3 flex items-center justify-center">
            <i class="fas fa-calendar-alt text-blue-500 text-3xl"></i>
          </div>
          <div class="text-gray-600 text-sm font-medium mb-1">School Years</div>
          <span class="text-xs text-blue-500 group-hover:underline">Manage School Years</span>
        </Link>

        <!-- Degrees KPI Card -->
        <Link :href="route('degrees', { user: userId })" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500 hover:shadow-md transition group flex flex-col items-center">
          <div class="bg-green-100 rounded-full p-4 mb-3 flex items-center justify-center">
            <i class="fas fa-graduation-cap text-green-500 text-3xl"></i>
          </div>
          <div class="text-gray-600 text-sm font-medium mb-1">Degrees</div>
          <span class="text-xs text-green-500 group-hover:underline">Manage Degrees</span>
        </Link>

        <!-- Programs KPI Card -->
        <Link :href="route('programs', { user: userId })" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500 hover:shadow-md transition group flex flex-col items-center">
          <div class="bg-indigo-100 rounded-full p-4 mb-3 flex items-center justify-center">
            <i class="fas fa-book text-indigo-500 text-3xl"></i>
          </div>
          <div class="text-gray-600 text-sm font-medium mb-1">Programs</div>
          <span class="text-xs text-indigo-500 group-hover:underline">Manage Programs</span>
        </Link>

        <!-- Career Opportunities KPI Card -->
        <Link :href="route('careeropportunities', { user: userId })" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500 hover:shadow-md transition group flex flex-col items-center">
          <div class="bg-purple-100 rounded-full p-4 mb-3 flex items-center justify-center">
            <i class="fas fa-briefcase text-purple-500 text-3xl"></i>
          </div>
          <div class="text-gray-600 text-sm font-medium mb-1">Career Opportunities</div>
          <span class="text-xs text-purple-500 group-hover:underline">Manage Opportunities</span>
        </Link>

        <!-- Skills KPI Card -->
        <Link :href="route('instiskills', { user: userId })" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-pink-500 hover:shadow-md transition group flex flex-col items-center">
          <div class="bg-pink-100 rounded-full p-4 mb-3 flex items-center justify-center">
            <i class="fas fa-cogs text-pink-500 text-3xl"></i>
          </div>
          <div class="text-gray-600 text-sm font-medium mb-1">Skills</div>
          <span class="text-xs text-pink-500 group-hover:underline">Manage Skills</span>
        </Link>
      </div>
    </Container>
  </AppLayout>
</template>