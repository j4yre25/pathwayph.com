<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 py-12 px-4">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8 text-gray-800">Institution Reports</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
          <Link
            :href="route('institutions.reports.schoolYear')"
            class="block rounded-xl shadow-md hover:shadow-lg transition p-6 bg-blue-100"
          >
            <div class="flex flex-col items-center">
              <component :is="Calendar" class="w-8 h-8 mb-2" />
              <span class="mt-2 text-lg font-semibold text-gray-700 text-center">School Year Report</span>
            </div>
          </Link>
          <Link
            :href="route('institutions.reports.degree')"
            class="block rounded-xl shadow-md hover:shadow-lg transition p-6 bg-green-100"
          >
            <div class="flex flex-col items-center">
              <component :is="GraduationCap" class="w-8 h-8 mb-2" />
              <span class="mt-2 text-lg font-semibold text-gray-700 text-center">Degree Report</span>
            </div>
          </Link>
          <Link
            :href="route('institutions.reports.programs')"
            class="block rounded-xl shadow-md hover:shadow-lg transition p-6 bg-yellow-100"
          >
            <div class="flex flex-col items-center">
              <component :is="ClipboardList" class="w-8 h-8 mb-2" />
              <span class="mt-2 text-lg font-semibold text-gray-700 text-center">Program Report</span>
            </div>
          </Link>
          <Link
            :href="route('institutions.reports.career')"
            class="block rounded-xl shadow-md hover:shadow-lg transition p-6 bg-purple-100"
          >
            <div class="flex flex-col items-center">
              <component :is="Briefcase" class="w-8 h-8 mb-2" />
              <span class="mt-2 text-lg font-semibold text-gray-700 text-center">Career Report</span>
            </div>
          </Link>
          <Link
            :href="route('institutions.reports.skill')"
            class="block rounded-xl shadow-md hover:shadow-lg transition p-6 bg-pink-100"
          >
            <div class="flex flex-col items-center">
              <component :is="Sparkles" class="w-8 h-8 mb-2" />
              <span class="mt-2 text-lg font-semibold text-gray-700 text-center">Skill Report</span>
            </div>
          </Link>
          <Link
            :href="route('institutions.reports.graduate')"
            class="block rounded-xl shadow-md hover:shadow-lg transition p-6 bg-cyan-100"
          >
            <div class="flex flex-col items-center">
              <component :is="GraduationCap" class="w-8 h-8 mb-2" />
              <span class="mt-2 text-lg font-semibold text-gray-700 text-center">Graduate Report</span>
            </div>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Calendar, GraduationCap, ClipboardList, Briefcase, Sparkles } from 'lucide-vue-next';
</script>