<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  experiences: { type: Array, default: () => [] },
  education: { type: Array, default: () => [] },
  highestEducation: { type: Object, default: null }
});

const activeCareerTab = ref('experience');

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const d = new Date(dateString);
  if (isNaN(d)) return dateString;
  return d.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
};

const sortedExperiences = computed(() =>
  [...props.experiences]
    .filter(e => !e?.deleted_at)
    .sort((a, b) => {
      if (a.is_current && !b.is_current) return -1;
      if (!a.is_current && b.is_current) return 1;
      return new Date(b.start_date) - new Date(a.start_date);
    })
);

// Normalize education
const normalizedEducation = computed(() =>
  (props.education || [])
    .filter(e => !e?.deleted_at)
    .map(e => ({
      id: e.id,
      school_name: e.school_name || '—',
      program: e.program || e.level_of_education || '—',
      level_of_education: e.level_of_education || '—',
      start_date: e.start_date,
      end_date: e.is_current ? null : e.end_date,
      is_current: !!e.is_current,
      description: e.description,
      achievement: e.achievement
    }))
);

// Highest (use passed prop if valid, else first entry)
const derivedHighest = computed(() => {
  if (props.highestEducation && (props.highestEducation.program || props.highestEducation.school_name)) {
    return {
      ...props.highestEducation,
      program: props.highestEducation.program || props.highestEducation.level_of_education || '—',
      school_name: props.highestEducation.school_name || '—',
      level_of_education: props.highestEducation.level_of_education || '—',
      is_current: !!props.highestEducation.is_current
    };
  }
  return normalizedEducation.value.length ? normalizedEducation.value[0] : null;
});

// Remaining entries (exclude highest)
const otherEducation = computed(() =>
  derivedHighest.value
    ? normalizedEducation.value.filter(e => e.id !== derivedHighest.value.id)
    : normalizedEducation.value
);
</script>

<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg">
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-4">
      <h3 class="text-lg font-semibold text-gray-800 flex items-center">
        <i class="fas fa-briefcase mr-2 text-indigo-500"></i>
        Career
      </h3>
    </div>

    <div class="transition-all duration-500">
      <!-- Tabs -->
      <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
          <button
            @click="activeCareerTab = 'experience'"
            :class="[activeCareerTab === 'experience' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300','flex-1 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-300']">
            <i class="fas fa-briefcase mr-2"></i> Work Experience
          </button>
          <button
            @click="activeCareerTab = 'education'"
            :class="[activeCareerTab === 'education' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300','flex-1 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-300']">
            <i class="fas fa-graduation-cap mr-2"></i> Education
          </button>
        </nav>
      </div>

      <!-- Work Experience -->
      <div
        v-show="activeCareerTab === 'experience'"
        class="p-6 border-b border-gray-100"
        role="tabpanel"
        aria-labelledby="tab-button-experience"
        id="tab-experience">
        <div v-if="!sortedExperiences.length" class="bg-gray-50 text-gray-500 text-center py-6 rounded-lg">
          <i class="fas fa-briefcase text-gray-300 text-3xl mb-2"></i>
          <p>No work experience added yet</p>
        </div>

        <!-- Experience timeline -->
        <div v-else class="space-y-6 text-sm">
          <div class="relative pl-6 space-y-6">
            <!-- Timeline line -->
            <div class="absolute top-0 left-2 h-full w-0.5 bg-indigo-100"></div>

            <div
              v-for="experience in sortedExperiences"
              :key="experience.id"
              class="relative">
              <!-- Timeline dot -->
              <div class="absolute -left-4 top-1 w-3 h-3 rounded-full bg-indigo-500 border-2 border-white shadow-md"></div>

              <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md transition-all duration-300 hover:border-indigo-200">
                <div class="flex justify-between items-start">
                  <div>
                    <h4 class="font-semibold text-gray-800">{{ experience.title }}</h4>
                    <p class="text-gray-600 mt-1">{{ experience.company_name }}</p>
                  </div>
                  <span v-if="experience.is_current" class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-medium">Current</span>
                </div>

                <div class="mt-2 text-gray-500 flex items-center">
                  <i class="far fa-calendar-alt mr-2 text-indigo-400"></i>
                  <p>{{ formatDate(experience.start_date) }} - {{ experience.is_current ? 'Present' : formatDate(experience.end_date) }}</p>
                </div>

                <div v-if="experience.description" class="mt-3 text-gray-600">
                  <p class="whitespace-pre-line">{{ experience.description }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Education -->
      <div
        v-show="activeCareerTab === 'education'"
        class="p-6"
        role="tabpanel"
        aria-labelledby="tab-button-education"
        id="tab-education">
        <div v-if="!educationEntries.length" class="bg-gray-50 text-gray-500 text-center py-6 rounded-lg">
          <i class="fas fa-graduation-cap text-gray-300 text-3xl mb-2"></i>
          <p>No education added yet</p>
        </div>

        <!-- Highest Education Highlight -->
        <div v-else-if="derivedHighest" class="mb-6 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-4 border border-indigo-100 shadow-md">
          <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
            <i class="fas fa-award mr-2 text-indigo-500"></i>
            Highest Education
          </h4>
          <div class="bg-white rounded-lg border border-indigo-200 p-4 shadow-sm">
            <div class="flex justify-between items-start">
              <h5 class="font-semibold text-gray-800">{{ derivedHighest.program }}</h5>
              <span v-if="derivedHighest.is_current" class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-medium">Current</span>
            </div>
            <p class="text-gray-600 mt-1">{{ derivedHighest.school_name }}</p>
            <div class="mt-2 text-gray-500 flex items-center">
              <i class="far fa-calendar-alt mr-2 text-indigo-400"></i>
              <p>{{ formatDate(derivedHighest.start_date) }} - {{ derivedHighest.is_current ? 'Present' : formatDate(derivedHighest.end_date) }}</p>
            </div>
            <div v-if="derivedHighest.description" class="mt-3 text-gray-600">
              <p class="whitespace-pre-line">{{ derivedHighest.description }}</p>
            </div>
            <div v-if="derivedHighest.achievement" class="mt-3 text-gray-600">
              <strong>Achievements:</strong>
              <ul class="list-disc list-inside">
                <li v-for="(ach, idx) in derivedHighest.achievement.split(',')" :key="idx">{{ ach.trim() }}</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Other Education Entries -->
        <div v-if="otherEducation.length" class="space-y-6">
          <div v-for="education in otherEducation" :key="education.id" class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex justify-between items-start">
              <div>
                <h4 class="font-semibold text-gray-800">{{ education.program }}</h4>
                <p class="text-gray-600 mt-1">{{ education.school_name }}</p>
              </div>
              <span v-if="education.is_current" class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-medium">Current</span>
            </div>
            
            <div class="mt-2 text-gray-500 flex items-center">
              <i class="far fa-calendar-alt mr-2 text-indigo-400"></i>
              <p>{{ formatDate(education.start_date) }} - {{ education.is_current ? 'Present' : formatDate(education.end_date) }}</p>
            </div>
            
            <div v-if="education.description" class="mt-3 text-gray-600">
              <p class="whitespace-pre-line">{{ education.description }}</p>
            </div>
            
            <div v-if="education.achievement" class="mt-3 text-gray-600">
              <strong>Achievements:</strong>
              <ul class="list-disc list-inside">
                <li v-for="(ach, idx) in education.achievement.split(',')" :key="idx">{{ ach.trim() }}</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>