<script setup>
import { ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Datepicker from 'vue3-datepicker';
import AppLayout from '@/Layouts/AppLayout.vue';
import CandidatePipeline from '@/Components/CandidatePipeline.vue';
import useInterviewScheduler from '@/Composables/useInterviewScheduler';
import useApplicantNote from '@/Composables/useApplicantNote';
import  { useVisitWebsite }  from '@/Composables/useVisitWebsite';

const props = defineProps({
  applicant: Object,
  skills: { type: Array, default: () => [] },
  education: { type: Array, default: () => [] },
  experiences: { type: Array, default: () => [] },
  projects: { type: Array, default: () => [] },
  achievements: { type: Array, default: () => [] },
  certifications: { type: Array, default: () => [] },
  testimonials: { type: Array, default: () => [] },
  employmentPreferences: { type: Object, default: null },
  careerGoals: { type: Object, default: null },
  resume: { type: Object, default: null },
});

// Stages setup
const stageOrder = [
  'applying',
  'screening',
  'testing',
  'final interview',
  'onboarding'
]

const currentStage = ref(props.applicant.stage || 'applying')

function moveStage() {
  const idx = stageOrder.indexOf(currentStage.value)
  if (idx < stageOrder.length - 1) {
    const nextStage = stageOrder[idx + 1]
    router.put(
      route('company.applications.updateStage', props.applicant.id),
      { stage: nextStage },
      {
        onSuccess: () => {
          currentStage.value = nextStage
        }
      }
    )
  }
}
// End of stages setup

// Copy to clipboard function
function copyToClipboard(text) {
  if (!text) return;
  navigator.clipboard.writeText(text);
}
//End of copy to clipboard function

const activeTab = ref('Resume');

// Fetch tab-specific data on viewApplicantDetails()
const resume = ref(props.resume);

//For scheduling interview
const {
  showScheduleModal,
  showConfirmationModal,
  scheduleForm,
  submitSchedule,
  incrementHour,
  decrementHour,
  incrementMinute,
  decrementMinute,
  toggleAMPM
} = useInterviewScheduler(props.applicant.id, () => {
  console.log('Interview scheduled successfully!');
});
//End of scheduling interview

//For note section
const {
  note,
  editingNote,
  noteInput,
  startEditNote,
  cancelEditNote,
  saveNote
} = useApplicantNote(props.applicant);
//End of note section

//For social media links
const { formatUrl } = useVisitWebsite();

function formatDate(dateStr) {
  if (!dateStr) return 'N/A';
  const date = new Date(dateStr);
  if (isNaN(date)) return dateStr;
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
}



const totalYearsExperience = computed(() => {
  if (!props.experiences.length) return 'Not Specified';
  let totalMonths = 0;
  props.experiences.forEach(exp => {
    if (exp.start_date) {
      const start = new Date(exp.start_date);
      const end = exp.is_current ? new Date() : (exp.end_date ? new Date(exp.end_date) : new Date());
      const months = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
      totalMonths += months > 0 ? months : 0;
    }
  });
  const years = Math.floor(totalMonths / 12);
  return years > 0 ? `${years} Year${years > 1 ? 's' : ''}` : '< 1 Year';
});

onMounted(() => {
  console.log('Applicant data:', props.applicant)
})

</script>

<template>
  <AppLayout>
    <template #header>
      <div class="flex items-center">
        <i class="fas fa-users-cog text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ props.applicant.graduate.first_name }} {{ props.applicant.graduate.last_name }}&#39;s Profile
        </h2>
      </div>
    </template>
    
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 md:pt-20">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Sidebar -->
                <aside class="md:w-1/3 w-full space-y-6 relative">
                    <!-- Social Icons -->
                    <div class=" border border-gray-200 bg-white rounded-xl shadow-lg p-4 flex flex-col items-center">
                      <div class="flex justify-center space-x-3 mb-2">
                        <a
                          :href="applicant.graduate.linkedin_url ? formatUrl(applicant.graduate.linkedin_url, 'linkedin.com') : '#'"
                          target="_blank"
                          class="text-blue-700 text-xl"
                          :class="{ 'opacity-40 pointer-events-none cursor-not-allowed': !applicant.graduate.linkedin_url }"
                          title="LinkedIn"
                        >
                          <i class="fab fa-linkedin"></i>
                        </a>
                        <a
                          :href="applicant.graduate.github_url ? formatUrl(applicant.graduate.github_url, 'github.com') : '#'"
                          target="_blank"
                          class="text-gray-800 text-xl"
                          :class="{ 'opacity-40 pointer-events-none cursor-not-allowed': !applicant.graduate.github_url }"
                          title="GitHub"
                        >
                          <i class="fab fa-github"></i>
                        </a>
                      </div>
                      <hr class="    w-full border-gray-200" />
                      <!-- Profile Picture -->
                      <div class="flex flex-col items-center mt-2">
                        <img :src="applicant.graduate.graduate_picture ? `/storage/${applicant.graduate.graduate_picture}` : '/images/default-avatar.png'"
                          alt="Graduate Picture"
                          class="w-24 h-24 rounded-full object-cover border-4 border-white shadow mb-2" />
                        <div class="text-lg font-semibold text-gray-900">{{ applicant.graduate.first_name }} {{ applicant.graduate.last_name }}</div>
                        <div class="text-sm text-gray-500 mb-2">{{ applicant.graduate.current_job_title || 'Software Engineer' }}</div>
                        <div class="flex items-center text-xs text-gray-500 space-x-2 mb-2">
                          <span>{{ totalYearsExperience }}</span>
                          <span>|</span>
                          <span>{{ applicant.graduate.address || 'Not Specified' }}</span>
                        </div>
                        <div class="flex items-center text-xs text-gray-500 space-x-2">
                          <span>{{ applicant.graduate.contact_number || '9876543210' }}</span>
                          <span>|</span>
                          <span>{{ applicant.graduate.user?.email || 'Not Specified' }}</span>
                          <button class="ml-1 text-gray-400 hover:text-indigo-500" @click="copyToClipboard(applicant.graduate.user?.email)">
                            <i class="far fa-copy"></i>
                          </button>
                        </div>
                      </div>
                      <hr class="my-4 w-full border-gray-200" />

                      <!-- Current -->
                      <div class="w-full flex justify-between text-sm text-gray-700 mb-2">
                        <div>
                          <div class="font-semibold">Year Graduated</div>
                          <div>{{ applicant.graduate.notice_period || 'Not Specified' }}</div>
                        </div>
                        <div>
                          <div class="font-semibold">Highest Education</div>
                          <div>{{ education[0]?.education || 'Not Specified' }}</div>
                        </div>
                      </div>
                      <hr class="my-2 w-full border-gray-200" />

                      <!-- Preferences -->
                      <div class="w-full flex justify-between text-sm text-gray-700 mb-2">
                        <div>
                          <div class="font-semibold">Pref. Salary</div>
                            <div>
                              <template v-if="employmentPreferences?.employment_min_salary || employmentPreferences?.employment_max_salary">
                                {{ employmentPreferences?.employment_min_salary ? employmentPreferences.employment_min_salary : 'N/A' }}
                                -
                                {{ employmentPreferences?.employment_max_salary ? employmentPreferences.employment_max_salary : 'N/A' }}
                              </template>
                              <template v-else>
                                Not Specified
                              </template>
                          </div>
                        </div>
                        <div>
                          <div class="font-semibold">Pref. Location</div>
                          <div>
                            <template v-if="employmentPreferences && employmentPreferences.locations && employmentPreferences.locations.length">
                              {{ employmentPreferences.locations.map(loc => loc.address).join(', ') }}
                            </template>
                            <template v-else>
                              Not Specified
                            </template>
                          </div>
                        </div>
                      </div>
                      <hr class="my-2 w-full border-gray-200" />
                      
                      <!-- Notes -->
                      <div class="w-full text-sm text-gray-700">
                        <div class="font-semibold">Notes</div>
                        <div v-if="!editingNote">
                          <div>{{ note || 'No notes yet.' }}</div>
                          <a href="#" class="text-indigo-600 hover:underline text-xs mt-1 inline-block" @click.prevent="startEditNote">
                            {{ note ? 'Edit note' : 'Add note' }}
                          </a>
                        </div>
                        <div v-else>
                          <textarea v-model="noteInput" rows="3" class="w-full border rounded p-2 text-sm"></textarea>
                          <div class="flex gap-2 mt-2">
                            <button @click="saveNote" class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Save</button>
                            <button @click="cancelEditNote" class="bg-gray-300 text-gray-700 px-3 py-1 rounded text-xs hover:bg-gray-400">Cancel</button>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Skills -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3">Skills</h4>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="skill in skills" :key="skill.id"
                                class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm">
                                {{ skill.skill_name }}
                            </span>
                        </div>
                    </div>

                    <!-- Resume -->
                    <div v-if="resume && resume.file_url" class="bg-white rounded-lg shadow-lg p-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3">Resume</h4>
                        <div class="flex items-center gap-4 mb-4">
                            <i v-if="resume.file_type && resume.file_type.includes('pdf')"
                                class="fas fa-file-pdf text-3xl text-red-500"></i>
                            <i v-else-if="resume.file_type && resume.file_type.includes('word')"
                                class="fas fa-file-word text-3xl text-blue-500"></i>
                            <i v-else class="fas fa-file-alt text-3xl text-gray-500"></i>
                            <span class="font-semibold">{{ resume.file_name }}</span>
                            <a :href="resume.file_url" download target="_blank"
                                class="inline-flex items-center text-indigo-600 hover:underline font-semibold ml-4">
                                <i class="fas fa-file-download mr-2"></i>
                                Download
                            </a>
                        </div>
                        <!-- PDF Preview -->
                        <div v-if="resume.file_type && resume.file_type.includes('pdf')"
                            class="border rounded mt-4 overflow-hidden">
                            <iframe :src="resume.file_url" width="100%" height="400px"></iframe>
                        </div>
                    </div>
                    <div v-else class="bg-white rounded-lg shadow-lg p-6 text-gray-600">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3">Resume</h4>
                        No resume uploaded.
                    </div>
                </aside>

                <!-- Main Content -->
                <!-- Added pt-8 for extra top padding so About Me is never covered -->
                <main class="md:w-2/3 w-full space-y-8 pt-8">
                  <!-- Action Buttons -->
                  <div class="w-full flex flex-wrap gap-4 mb-6">
                    <div>
                      <button
                          class="bg-black text-white px-6 py-2 rounded-full font-semibold flex items-center gap-2"
                          @click="showScheduleModal = true"
                        >
                          Schedule Interview <span class="text-xl leading-none">+</span>
                        </button>

                        <div v-if="showScheduleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
                          <div class="bg-white rounded-xl shadow-lg w-96 p-6 relative">
                            <div class="text-lg font-semibold mb-4 text-center tracking-wide" style="letter-spacing: 0.05em;">
                              Schedule Interview Date
                            </div>
                            <!-- Calendar Date Picker -->
                            <div class="flex flex-col items-center mb-4">
                              <label class="mb-2 text-sm font-medium text-gray-700">Interview Date</label>
                              <Datepicker
                                v-model="scheduleForm.date"
                                :inline="true"
                                :format="'yyyy-MM-dd'"
                                :min-date="new Date()"
                                class="mb-2"
                              />
                              <div v-if="scheduleForm.date" class="mt-2 text-lg font-semibold text-indigo-700 tracking-wide">
                                {{ new Date(scheduleForm.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                              </div>
                            </div>

                            <div class="flex flex-col items-center mb-4">
                              <label class="mb-2 text-sm font-medium text-gray-700">Schedule at</label>
                              <div class="flex items-center gap-2">
                                <!-- Hour -->
                                <div class="flex flex-col items-center">
                                  <button @click="incrementHour" class="text-lg font-bold text-gray-500 hover:text-black">&#9650;</button>
                                  <input
                                    type="number"
                                    v-model="scheduleForm.hour"
                                    min="1"
                                    max="12"
                                    class="w-20 text-center text-2xl font-bold border-none bg-transparent focus:ring-0"
                                    style="appearance: textfield;"
                                  />
                                  <button @click="decrementHour" class="text-lg font-bold text-gray-500 hover:text-black">&#9660;</button>
                                </div>
                                <span class="text-2xl font-bold">:</span>
                                <!-- Minute -->
                                <div class="flex flex-col items-center">
                                  <button @click="incrementMinute" class="text-lg font-bold text-gray-500 hover:text-black">&#9650;</button>
                                  <input
                                    type="number"
                                    v-model="scheduleForm.minute"
                                    min="0"
                                    max="59"
                                    class="w-20 text-center text-2xl font-bold border-none bg-transparent focus:ring-0"
                                    style="appearance: textfield;"
                                  />
                                  <button @click="decrementMinute" class="text-lg font-bold text-gray-500 hover:text-black">&#9660;</button>
                                </div>
                                <!-- AM/PM -->
                                <div class="flex flex-col items-center ml-2">
                                  <button @click="toggleAMPM" class="text-lg font-bold text-gray-500 hover:text-black">&#9650;</button>
                                  <div class="w-12 text-center text-2xl font-bold select-none">{{ scheduleForm.ampm }}</div>
                                  <button @click="toggleAMPM" class="text-lg font-bold text-gray-500 hover:text-black">&#9660;</button>
                                </div>
                              </div>
                            </div>
                            <div class="flex justify-between mt-6">
                              <button @click="showScheduleModal = false" class="px-4 py-2 rounded bg-gray-200">Cancel</button>
                              <button @click="submitSchedule" class="px-4 py-2 rounded bg-black text-white">Schedule</button>
                            </div>
                          </div>
                        </div>
                    </div>
                    <button class="bg-black text-white px-6 py-2 rounded-full font-semibold flex items-center gap-2">
                      Write Feedback <span class="text-xl leading-none">+</span>
                    </button>
                    <button class="bg-black text-white px-6 py-2 rounded-full font-semibold flex items-center gap-2">
                      Offer Job <span class="text-xl leading-none">+</span>
                    </button>
                  </div>
                  
                  <!-- Hiring Process -->
                  <section class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <div class="flex items">
                    <h4 class="text-base font-semibold text-gray-800 mb-4">Hiring Process</h4>
                    <span class="flex-1 flex justify-end">
                      <button class="bg-gray-200 text-gray-700 px-6 py-2 rounded-full font-semibold"
                        @click="moveStage"
                        :disabled="stageOrder.indexOf(currentStage) === stageOrder.length - 1"
                      >
                        Move Stage &gt;
                      </button>
                    </span>
                    </div>
                    <CandidatePipeline :stage="currentStage" />
                  </section>

                  <!-- Professional Skills -->
                  <section class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h4 class="text-base font-semibold text-gray-800 mb-4">Professional Skills</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                      <div
                        v-for="skill in skills"
                        :key="skill.id"
                        class="bg-gray-50 rounded-lg p-3 shadow-sm border border-gray-100 flex flex-col justify-between"
                      >
                        <div class="flex items-start justify-between mb-2">
                          <div class="flex items-center gap-2">
                            <h3 class="text-base font-medium text-gray-900">
                              {{ skill.skill_name }}
                            </h3>
                          </div>
                          <span
                          class="inline-flex px-3 py-1 rounded-full text-xs font-medium"
                          :class="{
                            'bg-blue-100 text-blue-800': skill.proficiency_type === 'Beginner',
                            'bg-green-100 text-green-800': skill.proficiency_type === 'Intermediate',
                            'bg-purple-100 text-purple-800': skill.proficiency_type === 'Advanced',
                            'bg-indigo-100 text-indigo-800': skill.proficiency_type === 'Expert'
                          }"
                          >
                          {{ skill.proficiency_type || skill.graduate_skills_proficiency_type }}
                        </span>
                      </div>
                      <div class="text-xs text-gray-500 font-normal">
                        ({{ skill.years_experience || skill.graduate_skills_years_experience || 0 }} yr<span v-if="(skill.years_experience || skill.graduate_skills_years_experience || 0) != 1">s</span>)
                      </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2 mb-2">
                          <div
                            class="h-2 rounded-full transition-all duration-300"
                            :class="{
                              'w-1/4 bg-blue-500': (skill.proficiency_type || skill.graduate_skills_proficiency_type) === 'Beginner',
                              'w-2/4 bg-green-500': (skill.proficiency_type || skill.graduate_skills_proficiency_type) === 'Intermediate',
                              'w-3/4 bg-purple-500': (skill.proficiency_type || skill.graduate_skills_proficiency_type) === 'Advanced',
                              'w-full bg-indigo-500': (skill.proficiency_type || skill.graduate_skills_proficiency_type) === 'Expert'
                            }"
                          ></div>
                        </div>
                      </div>
                    </div>
                  </section>

                  <!-- Resume Preview -->
                  <section class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <div class="flex flex-col md:flex-row gap-8">
                      <div class="flex-1">
                        <div class="flex items-center justify-between mb-4">
                          <div class="text-gray-400 text-sm">Resume</div>
                          <div class="flex gap-4">
                            <a v-if="resume && resume.file_url" :href="resume.file_url" download target="_blank" title="Download">
                              <i class="fas fa-download text-gray-500 cursor-pointer"></i>
                            </a>
                          </div>
                        </div>
                        <div v-if="resume && resume.file_url" class="bg-gray-50 rounded-lg p-6">
                          <div class="flex items-center gap-3 mb-4">
                            <i v-if="resume.file_type && resume.file_type.includes('pdf')" class="fas fa-file-pdf text-3xl text-red-500"></i>
                            <i v-else-if="resume.file_type && resume.file_type.includes('word')" class="fas fa-file-word text-3xl text-blue-500"></i>
                            <i v-else class="fas fa-file-alt text-3xl text-gray-500"></i>
                            <span class="font-semibold">{{ resume.file_name }}</span>
                          </div>
                          <!-- PDF Preview -->
                          <div v-if="resume.file_type && resume.file_type.includes('pdf')" class="border rounded mt-4 overflow-hidden">
                           <iframe :src="resume.file_url || ''" width="100%" height="400px"></iframe>
                          </div>
                          <!-- For non-PDF files, just show info and download -->
                          <div v-else class="text-gray-600 text-base">
                            <a :href="resume.file_url" download target="_blank" class="text-indigo-600 hover:underline font-semibold">
                              Download Resume
                            </a>
                          </div>
                        </div>
                        <div v-else class="bg-gray-50 rounded-lg p-6 text-gray-600">
                          No resume uploaded.
                        </div>
                      </div>
                    </div>
                  </section>

                  <!-- Tabs and tab content (keep your existing tab logic here) -->
                  <!-- ...existing tabbed content... -->


                  <!-- Confirmation Modal -->
                  <div v-if="showConfirmationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
                    <div class="bg-white rounded-xl shadow-lg w-96 p-6 relative flex flex-col items-center">
                      <div class="text-2xl text-green-600 mb-2">
                        <i class="fas fa-check-circle"></i>
                      </div>
                      <div class="text-lg font-semibold mb-2 text-center">
                        Interview Scheduled!
                      </div>
                      <div class="text-gray-600 mb-4 text-center">
                        The interview date has been set successfully.
                      </div>
                      <button @click="showConfirmationModal = false" class="px-4 py-2 rounded bg-indigo-600 text-white">
                        OK
                      </button>
                    </div>
                  </div>
                </main>
            </div>
        </div>
  </AppLayout>
</template>