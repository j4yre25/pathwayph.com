<script setup>
import { ref, computed, watch } from 'vue';

// Import the section components
import TestimonialSection from './Testimonial.vue';
import ResumeSection from './Resume.vue';
import AlumniStoriesSection from './AlumniStories.vue';

// Props from parent (Profile.vue)
const props = defineProps({
  activeSection: { type: String, default: 'supporting-documents' },
  activeSubsection: { type: String, default: null },
  // Testimonial props
  testimonialEntries: Array,
  archivedTestimonialEntries: Array,
  // Resume props
  resumeEntries: Array,
  archivedResumeEntries: Array,
  // Alumni Stories props
  alumniStoriesEntries: Array,
  archivedAlumniStoriesEntries: Array,
});

// Emit for closing/resetting modals if needed
const emit = defineEmits(['close-all-modals', 'reset-all-states']);

// Watch for changes in activeSubsection from parent
const activeSubsection = ref(props.activeSubsection);
watch(() => props.activeSubsection, (newValue) => {
  activeSubsection.value = newValue;
});
</script>

<template>
  <div v-if="activeSection === 'supporting-documents'" class="space-y-8">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Supporting Documents</h2>
        <p class="text-gray-600 mt-1">Manage your testimonials, resumes, and other supporting documents</p>
      </div>
    </div>
    
    <!-- Display the appropriate component based on activeSubsection -->
    <div v-if="!activeSubsection" class="text-center py-8">
      <p class="text-gray-500">Select a section from the menu above to manage your supporting documents</p>
    </div>
    
    <!-- Component Display Area -->
    <div v-else>
      <TestimonialSection
        v-if="activeSubsection === 'testimonials'"
        :activeSection="'testimonials'"
        :testimonialEntries="testimonialEntries"
        :archivedTestimonialEntries="archivedTestimonialEntries"
        @close-all-modals="emit('close-all-modals')"
        @reset-all-states="emit('reset-all-states')"
      />
      
      <ResumeSection
        v-if="activeSubsection === 'resume'"
        :activeSection="'resume'"
        :resumeEntries="resumeEntries"
        :archivedResumeEntries="archivedResumeEntries"
        @close-all-modals="emit('close-all-modals')"
        @reset-all-states="emit('reset-all-states')"
      />
      
      <AlumniStoriesSection
        v-if="activeSubsection === 'alumni-stories'"
        :activeSection="'alumni-stories'"
        :alumniStoriesEntries="alumniStoriesEntries"
        :archivedAlumniStoriesEntries="archivedAlumniStoriesEntries"
        @close-all-modals="emit('close-all-modals')"
        @reset-all-states="emit('reset-all-states')"
      />
    </div>
  </div>
</template>