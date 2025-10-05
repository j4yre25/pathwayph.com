<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Users, BarChart2, Briefcase, Share2 } from 'lucide-vue-next';
</script>


<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 py-12 px-4">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8 text-gray-800">Company Reports</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
          <Link
            :href="route('company.reports.jobs')"
            class="block rounded-xl shadow-md hover:shadow-lg transition p-6 bg-blue-100"
          >
            <div class="flex flex-col items-center">
              <component :is="Briefcase" class="w-8 h-8 mb-2" />
              <span class="mt-2 text-lg font-semibold text-gray-700 text-center">Job Market & Openings Reports</span>
            </div>
          </Link>
          <Link
            :href="route('company.reports.recruitment')"
            class="block rounded-xl shadow-md hover:shadow-lg transition p-6 bg-green-100"
          >
            <div class="flex flex-col items-center">
              <component :is="Share2" class="w-8 h-8 mb-2" />
              <span class="mt-2 text-lg font-semibold text-gray-700 text-center">Recruitment Process & Pipeline Reports</span>
            </div>
          </Link>
          <Link
            :href="route('peso.reports.skills')"
            class="block rounded-xl shadow-md hover:shadow-lg transition p-6 bg-green-100"
          >
            <div class="flex flex-col items-center">
              <component :is="BarChart2" class="w-8 h-8 mb-2" />
              <span class="mt-2 text-lg font-semibold text-gray-700 text-center">Skills, Competency & Qualification Reports</span>
            </div>
          </Link>
          <!-- <Link
            :href="route('institutions.reports.career')"
            class="block rounded-xl shadow-md hover:shadow-lg transition p-6 bg-purple-100"
          >
            <div class="flex flex-col items-center">
              <component :is="Briefcase" class="w-8 h-8 mb-2" />
              <span class="mt-2 text-lg font-semibold text-gray-700 text-center">Graduate Insights & Matching Reports</span>
            </div>
          </Link>
          <Link
            :href="route('institutions.reports.skill')"
            class="block rounded-xl shadow-md hover:shadow-lg transition p-6 bg-pink-100"
          >
            <div class="flex flex-col items-center">
              <component :is="Users" class="w-8 h-8 mb-2" />
              <span class="mt-2 text-lg font-semibold text-gray-700 text-center">Feedback & Inclusion Reports</span>
            </div>
          </Link> -->
        </div>
      </div>
    </div>
  </AppLayout>
</template>

