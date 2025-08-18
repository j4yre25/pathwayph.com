<script setup>
import { ref, computed, watch } from 'vue';

// Import the career profile components
import CareerGoalsSection from './CareerGoals.vue';
import EmploymentSection from './Employment.vue';

// Props from parent (ProfileSettings.vue)
const props = defineProps({
  activeSection: { type: String, default: 'career-profile' },
  // Career Goals props
  careerGoals: Object,
  // Employment Preferences props
  employmentPreferences: Object,
});

// Emit for closing/resetting modals if needed
const emit = defineEmits(['close-all-modals', 'reset-all-states']);
</script>

<template>
  <div class="space-y-8">
    <div class="flex justify-between items-center bg-gradient-to-r from-blue-700 to-blue-900 p-6 rounded-lg text-white shadow-lg">
      <div>
        <h2 class="text-2xl font-bold">Career Profile</h2>
        <p class="mt-1 opacity-90">Manage your career goals and employment preferences</p>
      </div>
      <div class="hidden md:block">
        <i class="fas fa-bullseye text-4xl opacity-80"></i>
      </div>
    </div>
    
    <!-- Career Goals Section -->
    <div class="section-card">
      <div class="section-header">
        <div class="section-header-icon">
          <i class="fas fa-bullseye"></i>
        </div>
        <h3 class="section-header-title">Career Goals</h3>
      </div>
      <CareerGoalsSection 
        :activeSection="'career-goals'"
        :careerGoals="careerGoals"
        @close-all-modals="emit('close-all-modals')"
        @reset-all-states="emit('reset-all-states')"
      />
    </div>
    
    <!-- Employment Preferences Section -->
    <div class="section-card">
      <div class="section-header">
        <div class="section-header-icon">
          <i class="fas fa-building"></i>
        </div>
        <h3 class="section-header-title">Employment Preferences</h3>
      </div>
      <EmploymentSection 
        :activeSection="'employment'"
        :employmentPreferences="employmentPreferences"
        @close-all-modals="emit('close-all-modals')"
        @reset-all-states="emit('reset-all-states')"
      />
    </div>
  </div>
</template>