<script setup>

import { ref, computed } from 'vue';

const props = defineProps({
  projects: {
    type: Array,
    default: () => []
  },
  testimonials: {
    type: Array,
    default: () => []
  },
  resume: {
    type: Object,
    default: null
  },
  graduate: {
    type: Object,
    required: true
  },

  schoolYears: { type: Array, default: () => [] }, // [{ id: 1, school_year_range: "2024-2025" }, ...]

  experiences: {
    type: Array,
    default: () => []
  },
  education: {
    type: Array,
    default: () => []
  },
  skills: {
    type: Array,
    default: () => []
  }


});

console.log('Graduate:', props);


function formatUrl(url) {
  if (!url) return '';
  if (/^https?:\/\//i.test(url)) {
    return url;
  }
  return 'https://' + url;
}

const mapEducationEntry = (entry) => ({
  degree: entry.degree || entry.program || entry.graduate_education_program || '',
  institution: entry.institution || entry.education || entry.graduate_education_institution_id || '',
  location: entry.location || entry.city || entry.graduate_education_location || '',
  level: entry.level || entry.field_of_study || entry.graduate_education_field_of_study || '',
  start_date: entry.start_date || entry.graduate_education_start_date,
  end_date: entry.end_date || entry.graduate_education_end_date,
  is_current: entry.is_current,
  description: entry.description,
  achievements: Array.isArray(entry.achievements)
    ? entry.achievements
    : entry.achievements
      ? [entry.achievements]
      : [],
});


const highestEducation = computed(() => {
  if (!props.education || props.education.length === 0) return null;
  const mapped = props.education.map(mapEducationEntry);
  return mapped.sort((a, b) => new Date(b.end_date || b.start_date) - new Date(a.end_date || a.start_date))[0];
});

const mappedYearGraduated = computed(() => {
  // Try nested objects first
  if (props.graduate.institution_school_year?.school_year_range?.school_year_range) {
    return props.graduate.institution_school_year.school_year_range.school_year_range;
  }
  // Try direct value
  if (props.graduate.school_year?.school_year_range) {
    return props.graduate.school_year.school_year_range;
  }
  // Map school_year_id to school_year_range
  if (props.graduate.school_year_id && props.schoolYears.length > 0) {
    const found = props.schoolYears.find(
      sy => sy.id === props.graduate.school_year_id
    );
    if (found) return found.school_year_range;
  }
  // Fallbacks
  return (
    props.graduate.graduate_year_graduated ||
    props.graduate.year_graduated ||
    'Not provided'
  );
});
const sortedEducation = computed(() => {
  if (!props.education || props.education.length === 0) return [];
  return props.education.map(mapEducationEntry)
    .sort((a, b) => new Date(b.end_date || b.start_date) - new Date(a.end_date || a.start_date));
});

const mapExperienceEntry = (entry) => ({
  id: entry.id,
  title: entry.title || entry.graduate_experience_title || '',
  company: entry.company || entry.graduate_experience_company || '',
  start_date: entry.start_date || entry.graduate_experience_start_date || '',
  end_date: entry.end_date || entry.graduate_experience_end_date || '',
  address: entry.address || entry.graduate_experience_address || '',
  description: entry.description || entry.graduate_experience_description || '',
  employment_type: entry.employment_type || entry.graduate_experience_employment_type || '',
  is_current: entry.is_current || false,
  ongoing: entry.is_current || false,
  skills: entry.skills || [],
});

const sortedExperiences = computed(() => {
  if (!props.experiences || props.experiences.length === 0) return [];
  return props.experiences.map(mapExperienceEntry)
    .sort((a, b) => new Date(b.start_date) - new Date(a.start_date));
});

// Compute grouped skills by category
const mappedGroupedSkills = computed(() => {
  const grouped = {};
  if (!props.skills || !Array.isArray(props.skills)) return grouped;

  props.skills.forEach(skill => {
    // Use 'type' for grouping (e.g., 'Technical Skills', 'Soft Skills')
    const groupKey = skill.type || 'Other';
    if (!grouped[groupKey]) {
      grouped[groupKey] = [];
    }
    grouped[groupKey].push({
      ...skill,
      name: skill.name || skill.skill_name || skill.proficiency_type || 'Skill',
      percentage: typeof skill.percentage === 'number'
        ? skill.percentage
        : skill.proficiency_type === 'Expert' ? 100
          : skill.proficiency_type === 'Advanced' ? 80
            : skill.proficiency_type === 'Intermediate' ? 60
              : skill.proficiency_type === 'Elementary' ? 40
                : skill.proficiency_type === 'Beginner' ? 20
                  : 0,
    });
  });

  return grouped;
});



const showAllSkills = ref(false);
const SKILLS_LIMIT = 5;
const activeTab = ref('skills');
const totalSkillsCount = computed(() => {
  return Object.values(mappedGroupedSkills.value).reduce((acc, arr) => acc + arr.length, 0);
});


const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
};





// Computed properties for Career section
const groupedEducationByLevel = computed(() => {
  const grouped = {};
  props.education.forEach(edu => {
    if (!grouped[edu.level]) {
      grouped[edu.level] = [];
    }
    grouped[edu.level].push(edu);
  });
  return grouped;
});

const showAllEducation = ref(false);
const maxEducationToShow = 3;


const visibleEducation = computed(() => {
  if (showAllEducation.value || !props.education || props.education.length <= maxEducationToShow) {
    return props.education;
  }
  return props.education.slice(0, maxEducationToShow);

});

// ADD (after existing education-related computed like sortedEducation) -------------
const normalizeEduEntry = (e) => ({
  id: e.id,
  school_name: e.school_name || e.institution || e.education || e.graduate_education_institution_id || '—',
  program: e.program || e.degree || e.graduate_education_program || e.level || '—',
  level_of_education: e.level_of_education || e.level || e.field_of_study || e.graduate_education_field_of_study || e.degree || null,
  start_date: e.start_date || e.graduate_education_start_date || null,
  end_date: e.is_current ? null : (e.end_date || e.graduate_education_end_date || null),
  is_current: !!(e.is_current || e.ongoing),
  description: e.description || e.graduate_education_description || '',
  achievement: Array.isArray(e.achievements)
    ? e.achievements.join(', ')
    : (e.achievement || e.achievements || ''),
  deleted_at: e.deleted_at || null,
});

const normalizedEducation = computed(() =>
  (props.education || [])
    .filter(e => !e.deleted_at)
    .map(normalizeEduEntry)
);

// Ranking similar to CareerSection
const eduRankMap = {
  'phd': 1, 'doctor': 1, 'doctorate': 1,
  "master's": 2, 'masters': 2, 'master': 2,
  "bachelor's": 3, 'bachelors': 3, 'bachelor': 3,
  'associate': 4,
  'certificate': 5,
  'vocational': 6,
  'senior high': 7, 'high school': 7,
};

const derivedHighest = computed(() => {
  if (!normalizedEducation.value.length) return null;
  const norm = s => (s || '').toLowerCase();
  return [...normalizedEducation.value].sort((a, b) => {
    const ra = eduRankMap[norm(a.level_of_education)] ?? 999;
    const rb = eduRankMap[norm(b.level_of_education)] ?? 999;
    if (ra !== rb) return ra - rb;
    if (a.is_current && !b.is_current) return -1;
    if (!a.is_current && b.is_current) return 1;
    const aEnd = a.end_date || a.start_date || '';
    const bEnd = b.end_date || b.start_date || '';
    return (bEnd || '').localeCompare(aEnd || '');
  })[0];
});

// For the list we include all (including highest, which we badge)
const allEducation = computed(() => normalizedEducation.value);

// (Optional) Remove old showAllEducation / maxEducationToShow usage in template
// ...existing code...
</script>

<template>
  <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 w-full">
    <!-- Tabs Navigation - Modern Minimalist Style -->
    <div class="border-b border-gray-200 bg-white overflow-x-auto sticky top-0 z-10">
      <nav class="flex" aria-label="Tabs">
        <button @click="activeTab = 'career'" :class="[
          activeTab === 'career'
            ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
          'px-4 py-3 text-sm flex items-center transition-all duration-200 focus:outline-none'
        ]" role="tab" aria-selected="false" aria-controls="tab-career" id="tab-button-career">
          <i class="fas fa-briefcase mr-2 text-gray-500"></i>
          Career
          <span v-if="experiences.length + education.length > 0"
            class="ml-2 bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">{{ experiences.length +
              education.length }}</span>
        </button>
        <button @click="activeTab = 'resume'" :class="[
          activeTab === 'resume'
            ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
          'px-4 py-3 text-sm flex items-center transition-all duration-200 focus:outline-none'
        ]" aria-current="page" role="tab" aria-selected="true" aria-controls="tab-resume" id="tab-button-resume">
          <i class="far fa-file-alt mr-2 text-gray-500"></i>
          Resume
        </button>
        <button @click="activeTab = 'activities'" :class="[
          activeTab === 'activities'
            ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
          'px-4 py-3 text-sm flex items-center transition-all duration-200 focus:outline-none'
        ]" role="tab" aria-selected="false" aria-controls="tab-activities" id="tab-button-activities">
          <i class="fas fa-chart-line mr-2 text-gray-500"></i>
          Activities
          <span v-if="projects && projects.length > 0"
            class="ml-2 bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">{{ projects ? projects.length : 0
            }}</span>
        </button>
        <button @click="activeTab = 'documents'" :class="[
          activeTab === 'documents'
            ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
          'px-4 py-3 text-sm flex items-center transition-all duration-200 focus:outline-none'
        ]" role="tab" aria-selected="false" aria-controls="tab-documents" id="tab-button-documents">
          <i class="fas fa-folder mr-2 text-gray-500"></i>
          Documents
          <span class="ml-2 bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">0</span>
        </button>
        <button @click="activeTab = 'testimonials'" :class="[
          activeTab === 'testimonials'
            ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
          'px-4 py-3 text-sm flex items-center transition-all duration-200 focus:outline-none'
        ]" role="tab" aria-selected="false" aria-controls="tab-testimonials" id="tab-button-testimonials">
          <i class="fas fa-quote-right mr-2 text-gray-500"></i>
          Testimonials
          <span v-if="testimonials && testimonials.length > 0"
            class="ml-2 bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">
            {{ testimonials.length }}
          </span>
        </button>
        <!-- <button @click="activeTab = 'feedback'" :class="[
          activeTab === 'feedback'
            ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
          'px-4 py-3 text-sm flex items-center transition-all duration-200 focus:outline-none'
        ]" role="tab" aria-selected="false" aria-controls="tab-feedback" id="tab-button-feedback">
          <i class="fas fa-comment-alt mr-2 text-gray-500"></i>
          Feedback
        </button> -->
        <button @click="activeTab = 'skills'" :class="[
          activeTab === 'skills'
            ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
          'px-4 py-3 text-sm flex items-center transition-all duration-200 focus:outline-none'
        ]" role="tab" aria-selected="false" aria-controls="tab-skills" id="tab-button-skills">
          <i class="fas fa-code mr-2 text-gray-500"></i>
          Skills
          <span v-if="skills && skills.length > 0"
            class="ml-2 bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">{{ skills.length }}</span>
        </button>
        <button @click="activeTab = 'details'" :class="[
          activeTab === 'details'
            ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
          'px-4 py-3 text-sm flex items-center transition-all duration-200 focus:outline-none'
        ]" role="tab" aria-selected="false" aria-controls="tab-details" id="tab-button-details">
          <i class="fas fa-user mr-2 text-gray-500"></i>
          Details
        </button>
        <!-- <button @click="activeTab = 'assessment'" :class="[
          activeTab === 'assessment'
            ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
          'px-4 py-3 text-sm flex items-center transition-all duration-200 focus:outline-none'
        ]" role="tab" aria-selected="false" aria-controls="tab-assessment" id="tab-button-assessment">
          <i class="fas fa-clipboard-check mr-2 text-gray-500"></i>
          Assessment
        </button> -->
      </nav>
    </div>

    <!-- Tab Content -->
    <div class="p-5">
      <!-- Resume Tab -->
      <div v-if="activeTab === 'resume'" class="transition-opacity duration-300">
        <div v-if="resume" class="space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-800 flex items-center">
              <i class="fas fa-file-alt text-gray-500 mr-2"></i>
              Resume
            </h3>
            <div class="text-sm text-gray-500">
              Last updated: {{ formatDate(resume.updated_at) }}
            </div>
          </div>

          <div class="flex flex-wrap gap-4">
            <a :href="resume.file_url" download
              class="flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
              <i class="fas fa-download mr-2"></i>
              Download Resume
            </a>
            <a :href="resume.file_url" target="_blank"
              class="flex items-center px-4 py-2 bg-white text-blue-600 border border-blue-600 rounded hover:bg-blue-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
              <i class="fas fa-external-link-alt mr-2"></i>
              Open Resume
            </a>
          </div>

          <!-- PDF Viewer -->
          <div v-if="resume.file_url && resume.file_url.endsWith('.pdf')"
            class="mt-4 border border-gray-200 rounded overflow-hidden">
            <iframe :src="resume.file_url" class="w-full h-[600px]" frameborder="0"></iframe>
          </div>
        </div>

        <div v-else class="text-center py-8">
          <div class="text-gray-400 mb-4">
            <i class="fas fa-file-alt text-5xl"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-800 mb-2">No Resume Uploaded</h3>
          <p class="text-gray-500">The graduate has not uploaded a resume yet.</p>
        </div>
      </div>

      <!-- Career Tab -->
      <div v-else-if="activeTab === 'career'" class="transition-opacity duration-300">
        <!-- Work Experience Section -->
        <div class="mb-8">
          <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
            <i class="fas fa-briefcase text-gray-500 mr-2"></i>
            Work Experience
          </h3>

          <div v-if="sortedExperiences.length > 0" class="space-y-6">
            <!-- Timeline Layout -->
            <div class="relative border-l-2 border-gray-200 pl-6 ml-3">
              <div v-for="(experience, index) in sortedExperiences" :key="index"
                class="mb-8 relative transform transition-all duration-200 hover:translate-x-1">
                <!-- Timeline Dot -->
                <div
                  class="absolute -left-[31px] mt-1.5 w-5 h-5 rounded-full border-4 border-white bg-blue-500 shadow-sm">
                </div>

                <!-- Experience Card -->
                <div
                  class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow transition-all duration-200">
                  <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-2">
                    <h4 class="text-base font-medium text-gray-900 mb-1 sm:mb-0">
                      {{ experience.title || experience.position || 'Position' }}
                      <span v-if="experience.ongoing"
                        class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">
                        Current
                      </span>
                    </h4>
                    <div class="text-sm text-gray-500">
                      {{ formatDate(experience.start_date) }} - {{ experience.ongoing ? 'Present' :
                        formatDate(experience.end_date) }}
                    </div>
                  </div>

                  <div class="text-sm font-medium text-blue-600 mb-2">
                    {{  experience.company_name || 'Company' }}
                  </div>

                  <div class="text-sm text-gray-600 mb-3">
                    {{ experience.address || 'Location' }}
                  </div>

                  <div class="text-sm text-gray-700 mb-3 whitespace-pre-line">
                    {{ experience.description || 'No description provided' }}
                  </div>

                  <div v-if="experience.skills && experience.skills.length > 0" class="flex flex-wrap gap-2 mt-2">
                    <span v-for="(skill, skillIndex) in experience.skills" :key="skillIndex"
                      class="px-2 py-1 text-xs font-medium rounded-md bg-gray-100 text-gray-700 transition-all duration-200 hover:bg-gray-200">
                      {{ skill }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-6 bg-gray-50 rounded-lg border border-gray-200 transition-all duration-200">
            <div class="text-gray-400 mb-2">
              <i class="fas fa-briefcase text-4xl"></i>
            </div>
            <h4 class="text-base font-medium text-gray-800 mb-1">No Work Experience</h4>
            <p class="text-sm text-gray-500">The graduate has not added any work experience yet.</p>
          </div>
        </div>

        <!-- Education Section (REPLACED) -->
        <div class="mt-12">
          <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
            <i class="fas fa-graduation-cap text-gray-500 mr-2"></i>
            Education
            <span v-if="allEducation.length"
                  class="ml-2 text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
              {{ allEducation.length }}
            </span>
          </h3>

          <div v-if="!allEducation.length"
               class="text-center py-6 bg-gray-50 rounded-lg border border-gray-200">
            <div class="text-gray-400 mb-2">
              <i class="fas fa-graduation-cap text-4xl"></i>
            </div>
            <h4 class="text-base font-medium text-gray-800 mb-1">No Education</h4>
            <p class="text-sm text-gray-500">The graduate has not added any education yet.</p>
          </div>

          <template v-else>
            <!-- Highest Highlight -->
            <div v-if="derivedHighest"
                 class="mb-8 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-5 border border-indigo-100 shadow-sm">
              <div class="flex justify-between items-start mb-2">
                <h4 class="text-base font-semibold text-gray-800 flex items-center">
                  <i class="fas fa-award text-indigo-500 mr-2"></i>
                  Highest Education
                </h4>
                <span v-if="derivedHighest.is_current"
                      class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">
                  Current
                </span>
              </div>
              <div class="bg-white rounded-lg border border-indigo-200 p-4 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-1">
                  <h5 class="text-base font-medium text-gray-900">
                    {{ derivedHighest.program }}
                    <span v-if="derivedHighest.level_of_education && derivedHighest.level_of_education !== derivedHighest.program"
                          class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-indigo-100 text-indigo-700">
                      {{ derivedHighest.level_of_education }}
                    </span>
                  </h5>
                  <div class="text-sm text-gray-500">
                    {{ formatDate(derivedHighest.start_date) }} -
                    {{ derivedHighest.is_current ? 'Present' : formatDate(derivedHighest.end_date) }}
                  </div>
                </div>
                <div class="text-sm font-medium text-blue-600 mb-2">
                  {{ derivedHighest.school_name }}
                </div>
                <div v-if="derivedHighest.description"
                     class="text-sm text-gray-700 mb-3 whitespace-pre-line">
                  {{ derivedHighest.description }}
                </div>
                <div v-if="derivedHighest.achievement"
                     class="mt-2 text-sm text-gray-700">
                  <strong>Achievements:</strong>
                  <ul class="list-disc list-inside">
                    <li v-for="(ach, idx) in derivedHighest.achievement.split(',')" :key="idx">
                      {{ ach.trim() }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- All Education Entries -->
            <div>
              <h4 class="text-sm font-semibold text-gray-600 flex items-center mb-3">
                <i class="fas fa-layer-group mr-2 text-indigo-400"></i>
                All Education
              </h4>
              <div class="grid md:grid-cols-2 gap-5">
                <div
                  v-for="edu in allEducation"
                  :key="edu.id"
                  class="relative bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md transition hover:border-indigo-200"
                >
                  <div class="flex justify-between items-start">
                    <div class="font-semibold text-gray-800">
                      {{ edu.program }}
                    </div>
                    <span
                      v-if="edu.is_current"
                      class="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">
                      Current
                    </span>
                  </div>
                  <p class="text-gray-600 text-sm mt-1">
                    {{ edu.school_name }}
                  </p>
                  <p v-if="edu.level_of_education && edu.level_of_education !== edu.program"
                     class="text-xs text-indigo-600 mt-1">
                    {{ edu.level_of_education }}
                  </p>
                  <div class="mt-2 text-gray-500 flex items-center text-xs">
                    <i class="far fa-calendar-alt mr-1 text-indigo-300"></i>
                    <span>
                      {{ formatDate(edu.start_date) }} -
                      {{ edu.is_current ? 'Present' : formatDate(edu.end_date) }}
                    </span>
                  </div>
                  <div v-if="edu.description"
                       class="mt-2 text-gray-600 text-xs line-clamp-5 whitespace-pre-line">
                    {{ edu.description }}
                  </div>
                  <div v-if="edu.achievement"
                       class="mt-2 text-gray-600 text-xs">
                    <strong>Achievements:</strong>
                    <ul class="list-disc list-inside">
                      <li v-for="(ach, idx) in edu.achievement.split(',')" :key="idx">
                        {{ ach.trim() }}
                      </li>
                    </ul>
                  </div>
                  <div
                    v-if="derivedHighest && edu.id === derivedHighest.id"
                    class="absolute top-2 left-2 text-[10px] bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded uppercase font-semibold tracking-wide">
                    Highest
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- Activities Tab -->
      <div v-else-if="activeTab === 'activities'" class="transition-opacity duration-300">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-800 flex items-center">
            <i class="fas fa-project-diagram text-indigo-500 mr-2"></i>
            Projects
            <span v-if="projects && projects.length > 0"
              class="ml-2 px-2 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700">
              {{ projects.length }}
            </span>
          </h3>
        </div>

        <div v-if="projects && projects.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div v-for="(project, index) in projects" :key="index"
            class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-300 transform hover:-translate-y-1">
            <div v-if="project.image_url" class="h-40 overflow-hidden">
              <img :src="project.image_url" :alt="project.title" class="w-full h-full object-cover">
            </div>
            <div class="p-4">
              <h4 class="text-base font-medium text-gray-900 mb-1">{{ project.title || 'Project Title' }}</h4>

              <div v-if="project.role" class="text-sm font-medium text-indigo-600 mb-2">
                {{ project.role }}
              </div>

              <div class="text-sm text-gray-500 mb-3">
                {{ formatDate(project.start_date) }} - {{ project.ongoing ? 'Present' : formatDate(project.end_date) }}
              </div>

              <div class="text-sm text-gray-700 mb-3 line-clamp-3">
                {{ project.description || 'No description provided' }}
              </div>

              <div v-if="project.url" class="mb-3">
                <a :href="formatUrl(project.url)" target="_blank"
                  class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 rounded-md px-2 py-1 inline-block">
                  <i class="fas fa-external-link-alt mr-1"></i>
                  View Project
                </a>
              </div>

              <div v-if="project.accomplishments && project.accomplishments.length > 0" class="mt-3">
                <h5 class="text-sm font-medium text-gray-800 mb-2">Key Accomplishments</h5>
                <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                  <li v-for="(accomplishment, accomplishmentIndex) in project.accomplishments"
                    :key="accomplishmentIndex">
                    {{ accomplishment }}
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <div v-else
          class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200 transition-all duration-300 hover:border-indigo-100 hover:bg-gray-100">
          <div class="text-gray-400 mb-4">
            <i class="fas fa-project-diagram text-5xl"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-800 mb-2">No Projects</h3>
          <p class="text-gray-500">The graduate has not added any projects yet.</p>


        </div>

      </div>



      <!-- Documents Tab -->
      <div v-else-if="activeTab === 'documents'" class="transition-opacity duration-300">
        <div class="mb-4">
          <h3 class="text-lg font-medium text-gray-800 flex items-center">
            <i class="fas fa-file text-indigo-500 mr-2"></i>
            Documents
          </h3>
        </div>

        <div v-if="resume"
          class="bg-white rounded-lg border border-gray-200 p-4 mb-4 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-300">
          <div class="flex items-center">
            <div class="flex-shrink-0 mr-4">
              <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                <i class="fas fa-file-pdf text-indigo-600"></i>
              </div>
            </div>
            <div class="flex-grow">
              <h4 class="text-base font-medium text-gray-900">Resume</h4>
              <div class="text-sm text-gray-500">
                Last updated: {{ formatDate(resume.updated_at) }}
              </div>
            </div>
            <div class="flex-shrink-0 flex space-x-2">
              <a v-if="resume.file_url" :href="resume.file_url" download
                class="p-2 text-indigo-600 hover:text-indigo-800 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 rounded-full"
                title="Download">
                <i class="fas fa-download"></i>
              </a>
              <a v-if="resume.file_url" :href="resume.file_url" target="_blank"
                class="p-2 text-indigo-600 hover:text-indigo-800 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 rounded-full"
                title="View">
                <i class="fas fa-external-link-alt"></i>
              </a>
            </div>
          </div>
        </div>

        <div v-if="!resume"
          class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200 transition-all duration-300 hover:border-indigo-100 hover:bg-gray-100">
          <div class="text-gray-400 mb-4">
            <i class="fas fa-file text-5xl"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-800 mb-2">No Documents</h3>
          <p class="text-gray-500">The graduate has not uploaded any documents yet.</p>
        </div>
      </div>

      <!-- Skills Section (Added) -->
      <div v-else-if="activeTab === 'skills'" class="mb-8 mt-6">
        <div class="bg-white rounded-lg shadow-sm p-6 transition-all duration-200 border border-gray-200">
          <div class="flex justify-between items-center mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
              <h3 class="text-lg font-medium text-gray-800 flex items-center">
                <i class="fas fa-code text-gray-500 mr-2"></i>
                Skills
              </h3>
            </div>
            <div class="flex items-center space-x-2">
              <span v-if="skills && skills.length > 0" class="text-xs text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
                {{ skills.length }} {{ skills.length === 1 ? 'skill' : 'skills' }}
              </span>

            </div>
          </div>

          <div class="transition-all duration-500 ease-in-out">
            <div v-if="!skills || skills.length === 0"
              class="text-gray-500 text-center py-8 text-sm bg-gray-50 rounded-lg border border-gray-200">
              <i class="fas fa-code text-gray-300 text-3xl mb-2"></i>
              <p>No skills added yet</p>
              <p class="text-xs mt-1">Skills will appear here once added to the profile</p>
            </div>

            <!-- Skills grid with 2 columns -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
              <template v-for="(categorySkills, category) in mappedGroupedSkills" :key="category">
                <!-- Category heading -->
                <div class="md:col-span-2 mt-2 mb-4 first:mt-0">
                  <h4 class="font-medium text-gray-700 border-b border-gray-200 pb-2 flex items-center">
                    <i class="fas fa-layer-group mr-2 text-gray-500"></i>
                    {{ category }}
                    <span class="ml-2 text-xs text-gray-600 bg-gray-100 px-2 py-0.5 rounded-full">
                      {{ categorySkills.length }}
                    </span>
                  </h4>
                </div>

                <!-- Skills in this category -->
                <div v-for="skill in showAllSkills ? categorySkills : categorySkills.slice(0, SKILLS_LIMIT)"
                  :key="skill.id"
                  class="space-y-3 group transition-all duration-200 hover:bg-gray-50 p-4 rounded-lg border border-gray-200 hover:shadow relative">
                  <div class="flex justify-between items-center">
                    <span class="text-gray-800 font-medium flex items-center">
                      <i :class="[`fas fa-${skill.icon || 'code'} mr-2 text-blue-600`]"></i>
                      {{ skill.name }}
                    </span>
                    <span
                      class="text-blue-700 font-medium bg-gray-100 group-hover:bg-white px-3 py-1 rounded-full text-xs transition-colors duration-200">
                      {{ Math.round(skill.percentage) }}%
                    </span>
                  </div>
                  <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500" :class="{
                      'bg-blue-500': skill.percentage >= 80,
                      'bg-blue-400': skill.percentage >= 40 && skill.percentage < 80,
                      'bg-blue-300': skill.percentage < 40
                    }" :style="{ width: `${skill.percentage}%` }"></div>
                  </div>
                  <!-- Tooltip that appears on hover -->
                  <div v-if="skill.years_experience"
                    class="hidden group-hover:block text-xs text-gray-700 mt-1 bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm absolute -top-10 left-1/2 transform -translate-x-1/2 whitespace-nowrap z-10 before:content-[''] before:absolute before:top-full before:left-1/2 before:-translate-x-1/2 before:border-8 before:border-transparent before:border-t-white">
                    <i class="far fa-clock mr-1 text-gray-500"></i>
                    {{ skill.years_experience }} {{ skill.years_experience === 1 ? 'year' : 'years' }} of experience
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
      <!-- Personal Details Section -->
      <div v-else-if="activeTab === 'details'" class="mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6 transition-all duration-200 border border-gray-200">
          <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
            <i class="fas fa-user text-gray-500 mr-2"></i>
            Personal Details
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
              class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-300">
              <h4 class="text-base font-medium text-gray-800 mb-3 flex items-center">
                <i class="fas fa-id-card text-gray-500 mr-2"></i>
                Basic Information
              </h4>

              <div class="space-y-3">
                <div class="flex">
                  <div class="w-1/3 text-sm font-medium text-gray-500">Full Name</div>
                  <div class="w-2/3 text-sm text-gray-800">
                    {{ graduate.first_name }} {{ graduate.middle_name ? graduate.middle_name + ' ' : '' }}{{
                      graduate.last_name }}
                  </div>
                </div>

                <div class="flex">
                  <div class="w-1/3 text-sm font-medium text-gray-500">Date of Birth</div>
                  <div class="w-2/3 text-sm text-gray-800">
                    {{ graduate.dob ? formatDate(graduate.dob) : 'Not provided' }}
                  </div>
                </div>

                <div class="flex">
                  <div class="w-1/3 text-sm font-medium text-gray-500">Gender</div>
                  <div class="w-2/3 text-sm text-gray-800">
                    {{ graduate.gender || 'Not provided' }}
                  </div>
                </div>



                <div class="flex">
                  <div class="w-1/3 text-sm font-medium text-gray-500">Ethnicity</div>
                  <div class="w-2/3 text-sm text-gray-800">
                    {{ graduate.ethnicity || graduate.graduate_ethnicity || 'Not provided' }}
                  </div>
                </div>
              </div>
            </div>

            <div
              class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-300">
              <h4 class="text-base font-medium text-gray-800 mb-3 flex items-center">
                <i class="fas fa-map-marker-alt text-gray-500 mr-2"></i>
                Location
              </h4>

              <div class="space-y-3">
                <div class="flex">
                  <div class="w-1/3 text-sm font-medium text-gray-500">Address</div>
                  <div class="w-2/3 text-sm text-gray-800">
                    {{ graduate.address || graduate.graduate_address || 'Not provided' }}
                  </div>
                </div>

                <div class="flex">
                  <div class="w-1/3 text-sm font-medium text-gray-500">Location</div>
                  <div class="w-2/3 text-sm text-gray-800">
                    {{ graduate.location || graduate.graduate_location || 'Not provided' }}
                  </div>
                </div>

              </div>
            </div>

            <div
              class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-300">
              <h4 class="text-base font-medium text-gray-800 mb-3 flex items-center">
                <i class="fas fa-envelope text-gray-500 mr-2"></i>
                Contact Information
              </h4>

              <div class="space-y-3">
                <div class="flex">
                  <div class="w-1/3 text-sm font-medium text-gray-500">Email</div>
                  <div class="w-2/3 text-sm text-gray-800">
                    {{ graduate.user.email || 'Not provided' }}
                  </div>
                </div>

                <div class="flex">
                  <div class="w-1/3 text-sm font-medium text-gray-500">Phone</div>
                  <div class="w-2/3 text-sm text-gray-800">
                    {{ graduate.contact_number || graduate.graduate_phone || 'Not provided' }}
                  </div>
                </div>

                <div class="flex">
                  <div class="w-1/3 text-sm font-medium text-gray-500">Website</div>
                  <div class="w-2/3 text-sm text-gray-800">
                    <a v-if="graduate.personal_website" :href="formatUrl(graduate.personal_website)" target="_blank"
                      class="text-indigo-600 hover:text-indigo-800 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 rounded-md px-2 py-1 inline-block">
                      {{ graduate.personal_website }}
                    </a>
                    <span v-else>Not provided</span>
                  </div>
                </div>

                <div class="flex">
                  <div class="w-1/3 text-sm font-medium text-gray-500">LinkedIn</div>
                  <div class="w-2/3 text-sm text-gray-800">
                    <a v-if="graduate.linkedin_url" :href="formatUrl(graduate.linkedin_url)" target="_blank"
                      class="text-indigo-600 hover:text-indigo-800 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 rounded-md px-2 py-1 inline-block">
                      <i class="fab fa-linkedin mr-1"></i> LinkedIn Profile
                    </a>
                    <span v-else>Not provided</span>
                  </div>
                </div>

                <div class="flex">
                  <div class="w-1/3 text-sm font-medium text-gray-500">GitHub</div>
                  <div class="w-2/3 text-sm text-gray-800">
                    <a v-if="graduate.github_url" :href="formatUrl(graduate.github_url)" target="_blank"
                      class="text-indigo-600 hover:text-indigo-800 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 rounded-md px-2 py-1 inline-block">
                      <i class="fab fa-github mr-1"></i> GitHub Profile
                    </a>
                    <span v-else>Not provided</span>
                  </div>
                </div>
              </div>
            </div>

            <div
              class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-300">
              <h4 class="text-base font-medium text-gray-800 mb-3 flex items-center">
                <i class="fas fa-graduation-cap text-gray-500 mr-2"></i>
                Program Information
              </h4>

              <div class="space-y-3">
                <div class="flex">
                  <div class="w-1/3 text-sm font-medium text-gray-500">Institution</div>
                  <div class="w-2/3 text-sm text-gray-800">
                    {{ graduate.institution?.institution_name || graduate.graduate_school_graduated_from ||
                      graduate.institution_name || 'Not provided' }}
                  </div>
                </div>

                <div class="flex">
                  <div class="w-1/3 text-sm font-medium text-gray-500">Program</div>
                  <div class="w-2/3 text-sm text-gray-800">
                    {{ graduate.program?.name || graduate.graduate_program_completed || graduate.program_name ||
                      'Notprovided'
                    }}
                  </div>
                </div>

                <div class="flex">
                  <div class="w-1/3 text-sm font-medium text-gray-500">Year Graduated</div>
                  <div class="w-2/3 text-sm text-gray-800">
                    {{ mappedYearGraduated }}
                  </div>
                </div>

                <div class="flex">
                  <div class="w-1/3 text-sm font-medium text-gray-500">Employment Status</div>
                  <div class="w-2/3 text-sm text-gray-800">
                    {{ graduate.employment_status || 'Not provided' }}
                  </div>
                </div>

                <div class="flex">
                  <div class="w-1/3 text-sm font-medium text-gray-500">Current Job</div>
                  <div class="w-2/3 text-sm text-gray-800">
                    {{ graduate.current_job_title || 'No Job' }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Feedback Tab -->
      <div v-else-if="activeTab === 'feedback'" class="transition-opacity duration-300">
        <div
          class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200 transition-all duration-300 hover:border-indigo-100 hover:bg-gray-100">
          <div class="text-gray-400 mb-4">
            <i class="fas fa-comment-alt text-5xl"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-800 mb-2">No Feedback</h3>
          <p class="text-gray-500 mb-4">There is no feedback available for this graduate.</p>

          <button
            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-all duration-300 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50">
            <i class="fas fa-plus mr-2"></i>
            Add Feedback
          </button>


        </div>

      </div>

      <!-- Testimonials Tab -->
      <div v-else-if="activeTab === 'testimonials'" class="transition-opacity duration-300">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-800 flex items-center">
            <i class="fas fa-quote-left text-indigo-500 mr-2"></i>
            Testimonials
            <span v-if="testimonials && testimonials.length > 0"
              class="ml-1 px-1.5 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700">
              {{ testimonials.length }}
            </span>
          </h3>

        </div>

        <div v-if="testimonials && testimonials.length > 0" class="space-y-6">
          <div v-for="(testimonial, index) in testimonials" :key="index"
            class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-start mb-4">
              <div class="flex-shrink-0 mr-4">
                <div v-if="testimonial.author_avatar" class="w-12 h-12 rounded-full overflow-hidden">
                  <img :src="testimonial.author_avatar" :alt="testimonial.author_name"
                    class="w-full h-full object-cover">
                </div>
                <div v-else class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                  <i class="fas fa-user text-indigo-600"></i>
                </div>
              </div>
              <div class="flex-grow">
                <h4 class="text-base font-medium text-gray-900">{{ testimonial.author|| 'Anonymous' }}</h4>
               
              </div>
              <div class="flex-shrink-0 text-sm text-gray-500">
                {{ formatDate(testimonial.created_at) }}
              </div>
            </div>

            <div class="text-sm text-gray-700 mb-2 whitespace-pre-line">
              <i class="fas fa-quote-left text-indigo-300 mr-1 opacity-50"></i>
              {{ testimonial.content || 'No content provided' }}
              <i class="fas fa-quote-right text-indigo-300 ml-1 opacity-50"></i>
            </div>
          </div>  
        </div>

        <div v-else
          class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200 transition-all duration-300 hover:border-indigo-100 hover:bg-gray-100">
          <div class="text-gray-400 mb-4">
            <i class="fas fa-quote-left text-5xl"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-800 mb-2">No Testimonials</h3>
          <p class="text-gray-500 mb-4">There are no testimonials available for this graduate.</p>

          <button
            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-all duration-300 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50">
            <i class="fas fa-plus mr-2"></i>
            Request Testimonial
          </button>
        </div>



      </div>

      <!-- Assessment Tab -->
      <!-- <div v-else-if="activeTab === 'assessment'" class="transition-opacity duration-300">
        <div
          class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200 transition-all duration-300 hover:border-indigo-100 hover:bg-gray-100">
          <div class="text-gray-400 mb-4">
            <i class="fas fa-clipboard-check text-5xl"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-800 mb-2">No Assessment</h3>
          <p class="text-gray-500 mb-4">There is no assessment available for this graduate.</p>

          <button
            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-all duration-300 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50">
            <i class="fas fa-plus mr-2"></i>
            Request Assessment
          </button>
        </div>

      </div> -->

    </div>

  </div>
</template>
