<script setup>
import { ref } from 'vue';

// Import the four accomplishment components
import ProjectSection from './Project.vue';
import CertificationSection from './Certification.vue';
import AchievementSection from './Achievement.vue';
import TestimonialSection from './Testimonial.vue';

// Props from parent (Profile.vue)
const props = defineProps({
  activeSection: { type: String, default: 'graduate-accomplishments' },
  projectsEntries: Array,
  archivedProjectsEntries: Array,
  certificationsEntries: Array,
  archivedCertificationsEntries: Array,
  achievementEntries: Array,
  archivedAchievementEntries: Array,
  testimonialEntries: Array,
  archivedTestimonialEntries: Array,
});

// Emit for closing/resetting modals if needed
const emit = defineEmits(['close-all-modals', 'reset-all-states']);
</script>

<template>
  <div v-if="activeSection === 'graduate-accomplishments'" class="space-y-10">
    <!-- Projects Section -->
    <ProjectSection
      :activeSection="'projects'"
      :projectsEntries="projectsEntries"
      :archivedProjectsEntries="archivedProjectsEntries"
      @close-all-modals="emit('close-all-modals')"
      @reset-all-states="emit('reset-all-states')"
    />

    <!-- Certifications Section -->
    <CertificationSection
      :activeSection="'certifications'"
      :certificationsEntries="certificationsEntries"
      :archivedCertificationsEntries="archivedCertificationsEntries"
      @close-all-modals="emit('close-all-modals')"
      @reset-all-states="emit('reset-all-states')"
    />

    <!-- Achievements Section -->
    <AchievementSection
      :activeSection="'achievements'"
      :achievementEntries="achievementEntries"
      :archivedAchievementEntries="archivedAchievementEntries"
      @close-all-modals="emit('close-all-modals')"
      @reset-all-states="emit('reset-all-states')"
    />

    <!-- Testimonials Section -->
    <TestimonialSection
      :activeSection="'testimonials'"
      :testimonialEntries="testimonialEntries"
      :archivedTestimonialEntries="archivedTestimonialEntries"
      @close-all-modals="emit('close-all-modals')"
      @reset-all-states="emit('reset-all-states')"
    />
  </div>
</template>