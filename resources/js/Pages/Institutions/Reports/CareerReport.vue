<template>
  <AppLayout>
    <div class="max-w-4xl mx-auto py-12 px-4">
      <h1 class="text-3xl font-bold mb-6 text-gray-800 flex items-center gap-2">
        <Briefcase class="w-8 h-8 text-cyan-500" />
        Career Opportunity Report
      </h1>
      <div class="bg-white rounded-lg shadow p-8">
        <!-- Your graduate report content goes here -->
        <p class="text-gray-600">This is the Graduate Report page. Add your report content here.</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Briefcase } from 'lucide-vue-next';
</script>