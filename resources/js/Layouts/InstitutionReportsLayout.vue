<!-- Example: resources/js/Layouts/InstitutionReportsLayout.vue -->
<template>
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-800 text-white flex-shrink-0">
      <div class="p-6 font-bold text-lg border-b border-gray-700">
        Reports
      </div>
      <nav class="mt-4 flex flex-col space-y-2">
        <Link href="/institution/reports/school-year" class="px-6 py-2 hover:bg-gray-700 rounded transition">School Year</Link>
        <Link href="/institution/reports/degree" class="px-6 py-2 hover:bg-gray-700 rounded transition">Degree</Link>
        <Link href="/institution/reports/programs" class="px-6 py-2 hover:bg-gray-700 rounded transition">Programs</Link>
        <!-- <Link href="/institution/reports/career" class="px-6 py-2 hover:bg-gray-700 rounded transition">Career</Link> -->
        <Link href="/institution/reports/skill" class="px-6 py-2 hover:bg-gray-700 rounded transition">Skill</Link>
        <Link href="/institution/reports/graduate" class="px-6 py-2 hover:bg-gray-700 rounded transition">Graduate</Link>
      </nav>
    </aside>
    <!-- Main Content -->
    <main class="flex-1 bg-gray-100 p-8">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>