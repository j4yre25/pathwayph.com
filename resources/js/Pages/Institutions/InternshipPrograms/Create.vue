<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  programs: Array,
  careerOpportunities: Array,
  skills: Array,
  graduates: Array,
});

const form = useForm({
  title: '',
  program_id: [],
  career_opportunity_id: [],
  skill_id: [],
  graduate_ids: [],
});

const showSuccess = ref(false);
const currentStep = ref(1);
const totalSteps = 5;

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
  'Skills',
  'Assign Graduates'
];

const stepIcons = [
  'fas fa-info-circle',
  'fas fa-graduation-cap',
  'fas fa-briefcase',
  'fas fa-tools',
  'fas fa-user-graduate'
];

const stepColors = [
  'blue',
  'green',
  'indigo',
  'purple',
  'teal'
];
</script>

<template>
  <AppLayout title="Add Internship Program">
    <template #header>
      <div class="flex items-center">
        <Link :href="route('internship-programs.index')" class="mr-4 text-gray-600 hover:text-gray-900 transition">
        <i class="fas fa-chevron-left"></i>
        </Link>
        <i class="fas fa-plus-circle text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Internship Program</h2>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center">
                <i class="fas fa-briefcase text-blue-500 mr-2"></i>
                <h3 class="text-lg font-medium text-gray-900">New Internship Program</h3>
              </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-8">
              <div class="flex justify-between mb-2">
                <div v-for="(title, index) in stepTitles" :key="index" class="flex flex-col items-center cursor-pointer"
                  @click="goToStep(index + 1)">
                  <div :class="[`text-${stepColors[index]}-500 bg-${stepColors[index]}-100`,
                  currentStep > index + 1 ? `bg-${stepColors[index]}-500 text-white` : '',
                  currentStep === index + 1 ? `ring-2 ring-${stepColors[index]}-500` : '']"
                    class="w-10 h-10 rounded-full flex items-center justify-center mb-1 transition-all duration-200">
                    <i v-if="currentStep > index + 1" class="fas fa-check"></i>
                    <i v-else :class="stepIcons[index]"></i>
                  </div>
                  <span
                    :class="[currentStep === index + 1 ? `text-${stepColors[index]}-600 font-medium` : 'text-gray-500']"
                    class="text-xs text-center hidden sm:block">
                    {{ title }}
                  </span>
                </div>
              </div>
              <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="absolute top-0 left-0 h-full transition-all duration-300 ease-in-out"
                  :class="`bg-${stepColors[currentStep - 1]}-500`"
                  :style="{ width: `${(currentStep / totalSteps) * 100}%` }">
                </div>
              </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">

              <!-- Step 1: General Information -->
              <div v-show="currentStep === 1" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                  <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                  General Information
                </h3>
                <div>
                  <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                  <input id="title" v-model="form.title" type="text" placeholder="Enter internship title"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors" />
                  <p v-if="form.errors.title" class="text-sm text-red-500 mt-1">{{ form.errors.title }}</p>
                </div>
              </div>

              <!-- Step 2: Programs -->
              <div v-show="currentStep === 2" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                  <i class="fas fa-graduation-cap text-green-500 mr-2"></i>
                  Programs
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                  <div v-for="p in programs" :key="p.id"
                    class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-50 transition-colors">
                    <input type="checkbox" :id="'program-' + p.id" :value="p.id" v-model="form.program_id"
                      class="rounded text-green-600 focus:ring-2 focus:ring-green-500 focus:ring-opacity-50" />
                    <label :for="'program-' + p.id" class="text-sm text-gray-700 cursor-pointer">{{ p.name }}</label>
                  </div>
                </div>
                <p v-if="form.errors.program_id" class="text-sm text-red-500 mt-1">{{ form.errors.program_id }}</p>
              </div>

              <!-- Step 3: Career Opportunities -->
              <div v-show="currentStep === 3" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                  <i class="fas fa-briefcase text-indigo-500 mr-2"></i>
                  Career Opportunities
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                  <div v-for="c in careerOpportunities" :key="c.id"
                    class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-50 transition-colors">
                    <input type="checkbox" :id="'career-' + c.id" :value="c.id" v-model="form.career_opportunity_id"
                      class="rounded text-indigo-600 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50" />
                    <label :for="'career-' + c.id" class="text-sm text-gray-700 cursor-pointer">{{ c.title }}</label>
                  </div>
                </div>
                <p v-if="form.errors.career_opportunity_id" class="text-sm text-red-500 mt-1">{{
                  form.errors.career_opportunity_id }}</p>
              </div>

              <!-- Step 4: Skills -->
              <div v-show="currentStep === 4" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                  <i class="fas fa-tools text-purple-500 mr-2"></i>
                  Skills
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                  <div v-for="s in skills" :key="s.id"
                    class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-50 transition-colors">
                    <input type="checkbox" :id="'skill-' + s.id" :value="s.id" v-model="form.skill_id"
                      class="rounded text-purple-600 focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50" />
                    <label :for="'skill-' + s.id" class="text-sm text-gray-700 cursor-pointer">{{ s.name }}</label>
                  </div>
                </div>
                <p v-if="form.errors.skill_id" class="text-sm text-red-500 mt-1">{{ form.errors.skill_id }}</p>
              </div>

              <!-- Step 5: Assign Graduates -->
              <div v-show="currentStep === 5" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-teal-500">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                  <i class="fas fa-user-graduate text-teal-500 mr-2"></i>
                  Assign Graduates
                </h3>
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          <input type="checkbox"
                            :checked="props.graduates.length > 0 && form.graduate_ids.length === props.graduates.length"
                            @change="e => e.target.checked ? form.graduate_ids = props.graduates.map(g => g.id) : form.graduate_ids = []"
                            class="focus:ring-teal-500 h-4 w-4 text-teal-600 border-gray-300 rounded">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Program
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="graduate in props.graduates" :key="graduate.id"
                        class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <input type="checkbox" :value="graduate.id" v-model="form.graduate_ids"
                            class="focus:ring-teal-500 h-4 w-4 text-teal-600 border-gray-300 rounded">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm font-medium text-gray-900">{{ graduate.first_name }} {{ graduate.last_name
                          }}
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm text-gray-500">{{ graduate.program ? graduate.program.name : 'N/A' }}
                          </div>
                        </td>
                      </tr>
                      <tr v-if="props.graduates.length === 0">
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                          No graduates available.
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <p v-if="form.errors.graduate_ids" class="text-sm text-red-500 mt-1">{{ form.errors.graduate_ids }}</p>
              </div>

              <!-- Navigation Buttons -->
              <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <div>
                  <button v-if="currentStep > 1" type="button" @click="prevStep"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center">
                    <i class="fas fa-chevron-left mr-2"></i>
                    Previous
                  </button>
                  <Link v-else :href="route('internship-programs.index')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center">
                  <i class="fas fa-times mr-2"></i>
                  Cancel
                  </Link>
                </div>
                <div>
                  <button v-if="currentStep < totalSteps" type="button" @click="nextStep"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center">
                    Next
                    <i class="fas fa-chevron-right ml-2"></i>
                  </button>
                  <button v-else type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                    :disabled="form.processing">
                    <i class="fas fa-save mr-2"></i>
                    <span v-if="form.processing">Saving...</span>
                    <span v-else>Save Program</span>
                  </button>
                </div>
              </div>

              <!-- Success Message -->
              <transition name="fade">
                <div v-if="showSuccess"
                  class="mt-4 p-4 bg-green-50 border border-green-200 rounded-md text-green-600 font-medium flex items-center">
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
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
