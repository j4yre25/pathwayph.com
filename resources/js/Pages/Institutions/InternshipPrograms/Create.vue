<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  programs: Array,
  careerOpportunities: Array,
  skills: Array,
});

const form = useForm({
  title: '',
  program_id: [],
  career_opportunity_id: [],
  skill_id: [],
});

const showSuccess = ref(false);
const currentStep = ref(1);
const totalSteps = 4;

function submit() {
  form.post(route('internship-programs.store'), {
    onSuccess: () => {
      showSuccess.value = true;
      form.reset();
      currentStep.value = 1;
    },
    onError: () => {
      showSuccess.value = false;
    },
  });
}

function nextStep() {
  if (currentStep.value < totalSteps) {
    currentStep.value++;
  }
}

function prevStep() {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
}

function goToStep(step) {
  if (step >= 1 && step <= totalSteps) {
    currentStep.value = step;
  }
}

const stepTitles = [
  'General Information',
  'Programs',
  'Career Opportunities',
  'Skills'
];

const stepIcons = [
  'fas fa-info-circle',
  'fas fa-graduation-cap',
  'fas fa-briefcase',
  'fas fa-tools'
];

const stepColors = [
  'blue',
  'green',
  'indigo',
  'purple'
];
</script>

<template>
  <AppLayout title="Add Internship Program">
        <template #header>
      <div class="flex items-center">
        <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
          <i class="fas fa-chevron-left"></i>
        </button>
        <h2 class="font-semibold text-xl text-gray-800 flex items-center">
          <i class="fas fa-seedling text-emerald-600 text-xl mr-2"></i> Create Internship Program
        </h2>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-lg sm:rounded-2xl">
          <div class="p-6 bg-white">
            <div class="flex items-center justify-between mb-6">
            </div>
            
            <!-- Progress Tabs (aligned with Jobs Create) -->
            <div class="flex justify-center mb-8">
              <div class="flex flex-col items-center">
                <div class="flex items-center space-x-4">
                  <!-- Step 1 -->
                  <div class="flex flex-col items-center">
                    <div @click="goToStep(1)"
                         :class="[
                           'w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg cursor-pointer transition-all duration-300',
                           currentStep === 1 ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white scale-110' :
                           (currentStep > 1 ? 'bg-emerald-500 text-white' : 'bg-gray-200 border-gray-300 text-gray-600')
                         ]">
                      <svg v-if="currentStep > 1" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                      </svg>
                      <i v-else class="fas fa-info-circle"></i>
                    </div>
                    <div class="mt-2 text-center text-sm font-medium text-gray-700">General Information</div>
                  </div>

                  <div class="w-12 h-0.5 bg-gray-300 mt-6"></div>

                  <!-- Step 2 -->
                  <div class="flex flex-col items-center">
                    <div @click="goToStep(2)"
                         :class="[
                           'w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg cursor-pointer transition-all duration-300',
                           currentStep === 2 ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white scale-110' :
                           (currentStep > 2 ? 'bg-emerald-500 text-white' : 'bg-gray-200 border-gray-300 text-gray-600')
                         ]">
                      <svg v-if="currentStep > 2" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                      </svg>
                      <i v-else class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="mt-2 text-center text-sm font-medium text-gray-700">Programs</div>
                  </div>

                  <div class="w-12 h-0.5 bg-gray-300 mt-6"></div>

                  <!-- Step 3 -->
                  <div class="flex flex-col items-center">
                    <div @click="goToStep(3)"
                         :class="[
                           'w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg cursor-pointer transition-all duration-300',
                           currentStep === 3 ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white scale-110' :
                           (currentStep > 3 ? 'bg-emerald-500 text-white' : 'bg-gray-200 border-gray-300 text-gray-600')
                         ]">
                      <svg v-if="currentStep > 3" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                      </svg>
                      <i v-else class="fas fa-briefcase"></i>
                    </div>
                    <div class="mt-2 text-center text-sm font-medium text-gray-700">Career Opportunities</div>
                  </div>

                  <div class="w-12 h-0.5 bg-gray-300 mt-6"></div>

                  <!-- Step 4 -->
                  <div class="flex flex-col items-center">
                    <div @click="goToStep(4)"
                         :class="[
                           'w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg cursor-pointer transition-all duration-300',
                           currentStep === 4 ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white scale-110' : 'bg-gray-200 border-gray-300 text-gray-600'
                         ]">
                      <i class="fas fa-tools"></i>
                    </div>
                    <div class="mt-2 text-center text-sm font-medium text-gray-700">Skills</div>
                  </div>
                </div>
              </div>
            </div>
            
            <form @submit.prevent="submit" class="space-y-6">

              <!-- Step 1: General Information -->
              <div v-show="currentStep === 1">
                <div>
                  <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                  <input
                    id="title"
                    v-model="form.title"
                    type="text"
                    placeholder="Enter internship title"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors"
                  />
                  <p v-if="form.errors.title" class="text-sm text-red-500 mt-1">{{ form.errors.title }}</p>
                </div>
              </div>

              <!-- Step 2: Programs -->
              <div v-show="currentStep === 2">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                  <div
                    v-for="p in programs"
                    :key="p.id"
                    class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-50 transition-colors"
                  >
                    <input
                      type="checkbox"
                      :id="'program-' + p.id"
                      :value="p.id"
                      v-model="form.program_id"
                      class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    />
                    <label :for="'program-' + p.id" class="text-sm text-gray-700 cursor-pointer">{{ p.name }}</label>
                  </div>
                </div>
                <p v-if="form.errors.program_id" class="text-sm text-red-500 mt-1">{{ form.errors.program_id }}</p>
              </div>

              <!-- Step 3: Career Opportunities -->
              <div v-show="currentStep === 3" class="bg-white rounded-2xl shadow-lg p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                  <div
                    v-for="c in careerOpportunities"
                    :key="c.id"
                    class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-50 transition-colors"
                  >
                    <input
                      type="checkbox"
                      :id="'career-' + c.id"
                      :value="c.id"
                      v-model="form.career_opportunity_id"
                      class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    />
                    <label :for="'career-' + c.id" class="text-sm text-gray-700 cursor-pointer">{{ c.title }}</label>
                  </div>
                </div>
                <p v-if="form.errors.career_opportunity_id" class="text-sm text-red-500 mt-1">{{ form.errors.career_opportunity_id }}</p>
              </div>

              <!-- Step 4: Skills -->
              <div v-show="currentStep === 4">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                  <div
                    v-for="s in skills"
                    :key="s.id"
                    class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-50 transition-colors"
                  >
                    <input
                      type="checkbox"
                      :id="'skill-' + s.id"
                      :value="s.id"
                      v-model="form.skill_id"
                      class="rounded text-purple-600 focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50"
                    />
                    <label :for="'skill-' + s.id" class="text-sm text-gray-700 cursor-pointer">{{ s.name }}</label>
                  </div>
                </div>
                <p v-if="form.errors.skill_id" class="text-sm text-red-500 mt-1">{{ form.errors.skill_id }}</p>
              </div>

              <!-- Navigation Buttons -->
              <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <div>
                  <button 
                    v-if="currentStep > 1" 
                    type="button" 
                    @click="prevStep"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 flex items-center"
                  >
                    <i class="fas fa-chevron-left mr-2"></i>
                    Previous
                  </button>
                  <Link 
                    v-else 
                    :href="route('internship-programs.index')" 
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 flex items-center"
                  >
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                  </Link>
                </div>
                <div>
                  <button
                    v-if="currentStep < totalSteps"
                    type="button"
                    @click="nextStep"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                  >
                    Next
                    <i class="fas fa-chevron-right ml-2"></i>
                  </button>
                  <button
                    v-else
                    type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 border border-transparent rounded-md shadow-lg hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 flex items-center"
                    :disabled="form.processing"
                  >
                    <i class="fas fa-save mr-2"></i>
                    <span v-if="form.processing">Saving...</span>
                    <span v-else>Save Program</span>
                  </button>
                </div>
              </div>
              
              <!-- Success Message -->
              <transition name="fade">
                <div
                  v-if="showSuccess"
                  class="mt-4 p-4 bg-green-50 border border-green-200 rounded-md text-green-600 font-medium flex items-center"
                >
                  <i class="fas fa-check-circle text-green-500 mr-2"></i>
                  <span>Internship program added successfully!</span>
                </div>
              </transition>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
