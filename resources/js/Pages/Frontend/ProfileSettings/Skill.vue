<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SkillsChart from '@/Components/SkillsChart.vue';
import ApexCharts from 'vue3-apexcharts';
import '@fortawesome/fontawesome-free/css/all.css';
import Modal from '@/Components/Modal.vue';

// Define props
const props = defineProps({
  activeSection: {
    type: String,
    default: 'skills'
  },
  skillEntries: {
    type: Array,
    default: () => []
  }
});

// Modal State Management
const isSuccessModalOpen = ref(false);
const isNoChangesModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const isDuplicateModalOpen = ref(false);
const emit = defineEmits(['close-all-modals', 'reset-all-states']);
// Skills Data
const skills = ref(props.skillEntries || []);
const skillName = ref('');
const skillProficiencyType = ref('');
const skillType = ref('');
const yearsExperience = ref(0);
const isUpdateSkillModalOpen = ref(false);
let currentSkillIndex = ref(null);
const noExpiryDate = ref(false);

// Register ApexCharts component
const apexchart = ApexCharts;

// Modal State
const isAddSkillModalOpen = ref(false);
const isSkillsAddedModalOpen = ref(false);

// Skill Handlers
const saveSkill = () => {
  if (!skillName.value.trim()) {
    alert("Skill name cannot be empty.");
    return;
  }
  if (!skillProficiencyType.value) {
    alert("Please select a proficiency type.");
    return;
  }
  if (!skillType.value) {
    alert("Please select a skill type.");
    return;
  }
  if (yearsExperience.value < 0) {
    alert("Years of experience cannot be negative.");
    return;
  }

  const skillForm = useForm({
    graduate_skills_name: skillName.value,
    graduate_skills_proficiency_type: skillProficiencyType.value,
    graduate_skills_type: skillType.value,
    graduate_skills_years_experience: yearsExperience.value,
  });

  console.log('Data sent to backend for skill:', skillForm.data());
  console.log('Selected Proficiency Type:', skillProficiencyType.value);
  console.log('Selected Skill Type:', skillType.value);
  console.log('Years of Experience:', yearsExperience.value);

  skillForm.post(route('profile.skills.add'), {
    onSuccess: (response) => {
      emit('close-all-modals');      skills.value.push({ ...skillForm.data(), id: response.id });
      closeAddSkillModal();
      console.log('Skill added successfully:', response);
    },
    onError: (errors) => {
      console.error('Error adding skill:', errors);
      alert('An error occurred while adding the skill. Please try again.');
    },
  });
};



const skillForm = useForm({
  graduate_skills_name: skillName.value,
  graduate_skills_proficiency_type: skillProficiencyType.value,
  graduate_skills_type: skillType.value,
  graduate_skills_years_experience: yearsExperience.value,
});

console.log('Current Skill Index:', currentSkillIndex.value);
console.log('Skills Array:', skills.value);
console.log('Skill at Index:', skills.value[currentSkillIndex.value])

// Update Skill Handler
const openEditSkillModal = (skill) => {
  currentSkillIndex.value = skills.value.findIndex(s => s.id === skill.id);
  skillName.value = skill.graduate_skills_name;
  skillProficiencyType.value = skill.graduate_skills_proficiency_type;
  skillType.value = skill.graduate_skills_type;
  yearsExperience.value = skill.graduate_skills_years_experience;
  isUpdateSkillModalOpen.value = true;
};

const updateSkill = () => {
  if (!skillProficiencyType.value) {
    alert("Please select a proficiency type.");
    return;
  }

  const duplicateSkill = skills.value.some(
    (skill, index) =>
      skill.graduate_skills_name.toLowerCase() === skillName.value.toLowerCase() &&
      index !== currentSkillIndex.value
  );

  if (duplicateSkill) {
    alert(`The skill "${skillName.value}" already exists.`);
    return;
  }

  const skillForm = useForm({
    graduate_skills_name: skillName.value,
    graduate_skills_proficiency_type: skillProficiencyType.value,
    graduate_skills_type: skillType.value,
    graduate_skills_years_experience: yearsExperience.value,
  });

  const skillId = skills.value[currentSkillIndex.value]?.id;
  if (!skillId) {
    console.error('No skill ID found for update');
    return;
  }

  console.log('Updating skill with data:', skillForm.data());

  skillForm.put(route('profile.skills.update', { id: skillId }), {
    onSuccess: () => {
      console.log('Skill updated successfully.');
      skills.value[currentSkillIndex.value] = { ...skillForm.data(), id: skillId };
      closeUpdateSkillModal();
    },
    onError: (errors) => {
      console.error('Error updating skill:', errors);
      alert('An error occurred while updating the skill. Please try again.');
    },
  });
};

const editSkill = (skill) => {
  skillName.value = skill.graduate_skills_name;
  skillProficiencyType.value = skill.graduate_skills_proficiency_type;
  skillType.value = skill.graduate_skills_type;
  yearsExperience.value = skill.graduate_skills_years_experience;
  currentSkillIndex.value = skills.value.indexOf(skill);
  isUpdateSkillModalOpen.value = true;
};

const closeAddSkillModal = () => {
  isAddSkillModalOpen.value = false;
  skillName.value = '';
  skillProficiencyType.value = '';
  skillType.value = '';
  yearsExperience.value = 0;
};


const newSkill = ref('');

const resetSkill = () => {
  skillName.value = '';
  skillProficiencyType.value = '';
  skillType.value = '';
  yearsExperience.value = 0;
  console.log('Skill reset.');
};

const closeSkillsAddedModal = () => {
  isSkillsAddedModalOpen.value = false;
  console.log('Skills added modal closed.');
};

const closeUpdateSkillModal = () => {
  isUpdateSkillModalOpen.value = false;
  skillName.value = '';
  skillProficiencyType.value = '';
  skillType.value = '';
  yearsExperience.value = 0;
  console.log('Update skill modal closed.');
};

// Computed property to group skills by type
const groupedSkills = computed(() => {
  if (!Array.isArray(skills.value)) {
    console.error('skills.value is not an array:', skills.value);
    return {};
  }
  return skills.value.reduce((acc, skill) => {
    const type = skill.graduate_skills_type || 'Unknown';
    if (!acc[type]) {
      acc[type] = [];
    }
    acc[type].push(skill);
    return acc;
  }, {});
});

// Computed property to filter skills based on selected type
const filteredSkills = computed(() => {
  if (!skillType.value) return skills.value;
  return skills.value.filter(skill => skill.graduate_skills_type === skillType.value);
});

// Modal Opening Functions
const openAddSkillModal = () => {
  isAddSkillModalOpen.value = true;
  console.log('Add skill modal opened.');
};

const removeSkill = (skill) => {
  if (confirm(`Are you sure you want to remove the skill: ${skill.graduate_skills_name}?`)) {
    const index = skills.value.findIndex(s => s.id === skill.id);
    if (index !== -1) {
      skills.value.splice(index, 1);
      console.log(`Skill "${skill.graduate_skills_name}" removed from view.`);
    }
  }
};

// Function to initialize data on component mount
const initializeData = () => {
  // Fetch initial data or set defaults
  console.log('Initializing data...');
};

// Call initialize function on component mount
onMounted(() => {
  initializeData();
});

</script>

<template>
  <!-- Success Modal -->
  <Modal :show="isSuccessModalOpen" @close="isSuccessModalOpen = false">
      <div class="p-6">
          <div class="flex items-center justify-center mb-4 bg-green-100 rounded-full w-12 h-12 mx-auto">
              <i class="fas fa-check text-green-500 text-xl"></i>
          </div>
          <h2 class="text-lg font-medium text-center text-gray-900 mb-2">Success</h2>
          <p class="text-center text-gray-600">{{ successMessage }}</p>
          <div class="mt-6 flex justify-center">
              <PrimaryButton @click="isSuccessModalOpen = false">OK</PrimaryButton>
          </div>
      </div>
  </Modal>

  <!-- Error Modal -->
  <Modal :show="isErrorModalOpen" @close="isErrorModalOpen = false">
      <div class="p-6">
          <div class="flex items-center justify-center mb-4 bg-red-100 rounded-full w-12 h-12 mx-auto">
              <i class="fas fa-times text-red-500 text-xl"></i>
          </div>
          <h2 class="text-lg font-medium text-center text-gray-900 mb-2">Error</h2>
          <p class="text-center text-gray-600">{{ errorMessage }}</p>
          <div class="mt-6 flex justify-center">
              <PrimaryButton @click="isErrorModalOpen = false">OK</PrimaryButton>
          </div>
      </div>
  </Modal>

  <!-- Duplicate Modal -->
  <Modal :show="isDuplicateModalOpen" @close="isDuplicateModalOpen = false">
      <div class="p-6">
          <div class="flex items-center justify-center mb-4 bg-yellow-100 rounded-full w-12 h-12 mx-auto">
              <i class="fas fa-exclamation-triangle text-yellow-500 text-xl"></i>
          </div>
          <h2 class="text-lg font-medium text-center text-gray-900 mb-2">Duplicate Entry</h2>
          <p class="text-center text-gray-600">This skill already exists in your profile.</p>
          <div class="mt-6 flex justify-center">
              <PrimaryButton @click="isDuplicateModalOpen = false">OK</PrimaryButton>
          </div>
      </div>
  </Modal>

  <!-- Skills Section -->
  <div v-if="activeSection === 'skills'" class="flex flex-col">
      <div class="w-full mb-6">
        <div class="flex justify-between items-center mb-4">
          <h1 class="text-2xl font-semibold text-gray-900">Skills</h1>
          <div class="flex space-x-4">
            <PrimaryButton class="bg-indigo-600 text-white px-4 py-2 rounded flex items-center"
              @click="openAddSkillModal">
              <i class="fas fa-plus mr-2"></i>
              Add Skill
            </PrimaryButton>
            <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded flex items-center transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 cursor-pointer" @click="toggleArchivedSkills">
              <i class="fas" :class="showArchivedSkills ? 'fa-eye-slash' : 'fa-eye'"></i>
              <span class="ml-2">{{ showArchivedSkills ? 'Hide Archived' : 'Show Archived' }}</span>
            </button>
          </div>
        </div>
        <p class="mt-2 text-gray-600">Showcase your professional expertise and competencies</p>

        <!-- Active Skills Entries -->
        <div>
          <h2 class="text-lg font-medium mb-4">Active Skills</h2>
          <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <div class="xl:col-span-2 space-y-6">
              <div v-if="Object.keys(groupedSkills).length > 0" class="space-y-8">
                <div v-for="(skillsGroup, type) in groupedSkills" :key="type"
                  class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                  <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">{{ type }}</h2>
                  </div>
                  <div class="p-6">
                    <div class="grid gap-6">
                      <div v-for="skill in skillsGroup" :key="skill.graduate_skills_name"
                        class="bg-gray-50 rounded-lg p-6 transition-all duration-200 hover:bg-gray-100">
                        <div class="flex items-start justify-between mb-4">
                          <div class="flex-1">
                            <h3 class="text-lg font-medium text-gray-900">{{ skill.graduate_skills_name }}</h3>
                            <span class="inline-flex mt-2 px-3 py-1 rounded-full text-sm font-medium"
                              :class="{
                                'bg-blue-100 text-blue-800': skill.graduate_skills_proficiency_type === 'Beginner',
                                'bg-green-100 text-green-800': skill.graduate_skills_proficiency_type === 'Intermediate',
                                'bg-purple-100 text-purple-800': skill.graduate_skills_proficiency_type === 'Advanced',
                                'bg-indigo-100 text-indigo-800': skill.graduate_skills_proficiency_type === 'Expert'
                              }">
                              {{ skill.graduate_skills_proficiency_type }}
                            </span>
                          </div>
                          <div class="flex space-x-2">
                            <button class="text-gray-600 hover:text-indigo-600" @click="editSkill(skill)">
                              <i class="fas fa-pen"></i>
                            </button>
                            <button class="text-amber-600 hover:text-amber-800" @click="archiveSkill(skill)">
                              <i class="fas fa-archive"></i>
                            </button>
                          </div>
                        </div>
                        <div class="space-y-4">
                          <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all duration-300"
                              :class="{
                                'w-1/4 bg-blue-500': skill.graduate_skills_proficiency_type === 'Beginner',
                                'w-2/4 bg-green-500': skill.graduate_skills_proficiency_type === 'Intermediate',
                                'w-3/4 bg-purple-500': skill.graduate_skills_proficiency_type === 'Advanced',
                                'w-full bg-indigo-500': skill.graduate_skills_proficiency_type === 'Expert'
                              }"></div>
                          </div>
                          <div class="flex items-center justify-between text-sm">
                            <span class="font-medium text-gray-700">Years of Experience</span>
                            <span class="text-gray-600">{{ skill.graduate_skills_years_experience }} year(s)</span>
                          </div>
                          <div class="flex space-x-2">
                            <div v-for="n in 5" :key="n"
                              class="flex-1 h-1.5 rounded transition-all duration-200"
                              :class="{
                                'bg-indigo-500': n <= skill.graduate_skills_years_experience,
                                'bg-gray-200': n > skill.graduate_skills_years_experience
                              }"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- No Skills Message -->
              <div v-else class="bg-white p-8 rounded-lg shadow">
                <p class="text-gray-600">No skills added yet</p>
              </div>
            </div>

            <!-- Skills Chart -->
            <div class="xl:col-span-1">
              <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-4">
                <SkillsChart :skills="skills" />
              </div>
            </div>
          </div>
        </div>

        <!-- Archived Skills -->
        <div v-if="showArchivedSkills" class="mt-8">
          <h2 class="text-lg font-medium mb-4">Archived Skills</h2>
          <div v-if="archivedSkillsList.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div v-for="skill in archivedSkillsList" :key="skill.id"
              class="bg-gray-50 rounded-lg p-6 transition-all duration-200 hover:bg-gray-100 border border-gray-200 relative">
              <div class="opacity-75">
                <div class="flex items-start justify-between mb-4">
                  <div class="flex-1">
                    <h3 class="text-lg font-medium text-gray-900">{{ skill.graduate_skills_name }}</h3>
                    <span class="inline-flex mt-2 px-3 py-1 rounded-full text-sm font-medium"
                      :class="{
                        'bg-blue-100 text-blue-800': skill.graduate_skills_proficiency_type === 'Beginner',
                        'bg-green-100 text-green-800': skill.graduate_skills_proficiency_type === 'Intermediate',
                        'bg-purple-100 text-purple-800': skill.graduate_skills_proficiency_type === 'Advanced',
                        'bg-indigo-100 text-indigo-800': skill.graduate_skills_proficiency_type === 'Expert'
                      }">
                      {{ skill.graduate_skills_proficiency_type }}
                    </span>
                  </div>
                </div>
                <div class="space-y-4">
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all duration-300"
                      :class="{
                        'w-1/4 bg-blue-500': skill.graduate_skills_proficiency_type === 'Beginner',
                        'w-2/4 bg-green-500': skill.graduate_skills_proficiency_type === 'Intermediate',
                        'w-3/4 bg-purple-500': skill.graduate_skills_proficiency_type === 'Advanced',
                        'w-full bg-indigo-500': skill.graduate_skills_proficiency_type === 'Expert'
                      }">
                    </div>
                  </div>
                  <div class="flex items-center justify-between text-sm">
                    <span class="font-medium text-gray-700">Years of Experience</span>
                    <span class="text-gray-600">{{ skill.graduate_skills_years_experience }} year(s)</span>
                  </div>
                  <div class="flex space-x-2">
                    <div v-for="n in 5" :key="n"
                      class="flex-1 h-1.5 rounded transition-all duration-200"
                      :class="{
                        'bg-indigo-500': n <= skill.graduate_skills_years_experience,
                        'bg-gray-200': n > skill.graduate_skills_years_experience
                      }">
                    </div>
                  </div>
                </div>
              </div>
              <div class="absolute top-2 right-2 flex space-x-2">
                <button class="text-green-600 hover:text-green-800" @click="unarchiveSkill(skill)">
                  <i class="fas fa-box-open"></i>
                </button>
                <button class="text-red-600 hover:text-red-800" @click="removeSkill(skill)">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
              <div class="absolute top-2 left-2 bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded">
                Archived
              </div>
            </div>
          </div>

          <!-- If no archived skills exist -->
          <div v-else class="bg-white p-8 rounded-lg shadow">
            <p class="text-gray-600">No archived skills found.</p>
          </div>
        </div>
      </div>

      <!-- Add Skills Modal -->
      <div v-if="isAddSkillModalOpen"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Add Skill</h2>
            <button class="text-gray-500 hover:text-gray-700" @click="closeAddSkillModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <p class="text-gray-600 mb-4">Add a new skill to your profile</p>
          <form @submit.prevent="saveSkill">
            <div class="mb-4">
              <label for="skillName" class="block text-gray-700">Skill Name <span class="text-red-500">*</span></label>
              <input type="text" id="skillName" v-model="skillName"
                class="w-full px-3 py-2 border border-gray-300 rounded-md" 
                :class="{'border-red-500': skillForm.errors.graduate_skills_name}"
                required>
              <div v-if="skillForm.errors.graduate_skills_name" class="text-red-500 text-sm mt-1">
                {{ skillForm.errors.graduate_skills_name }}
              </div>
            </div>
            <div class="mb-4">
              <label for="skillProficiencyType" class="block text-gray-700">Proficiency Type <span class="text-red-500">*</span></label>
              <select id="skillProficiencyType" v-model="skillProficiencyType"
                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                :class="{'border-red-500': skillForm.errors.graduate_skills_proficiency_type}"
                required>
                <option value="" disabled>Select proficiency type</option>
                <option value="Beginner">Beginner</option>
                <option value="Intermediate">Intermediate</option>
                <option value="Advanced">Advanced</option>
                <option value="Expert">Expert</option>
              </select>
              <div v-if="skillForm.errors.graduate_skills_proficiency_type" class="text-red-500 text-sm mt-1">
                {{ skillForm.errors.graduate_skills_proficiency_type }}
              </div>
            </div>
            <div class="mb-4">
              <label for="skillType" class="block text-gray-700">Skill Type <span class="text-red-500">*</span></label>
              <select id="skillType" v-model="skillType"
                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                :class="{'border-red-500': skillForm.errors.graduate_skills_type}"
                required>
                <option value="" disabled>Select skill type</option>
                <option value="Technical Skills">Technical Skills</option>
                <option value="Soft Skills">Soft Skills</option>
                <option value="Language Skills">Language Skills</option>
                <option value="Tool/Platform">Tool/Platform</option>
              </select>
              <div v-if="skillForm.errors.graduate_skills_type" class="text-red-500 text-sm mt-1">
                {{ skillForm.errors.graduate_skills_type }}
              </div>
            </div>
            <div class="mb-4">
              <label for="yearsExperience" class="block text-gray-700">Years of Experience <span class="text-red-500">*</span></label>
              <input type="number" id="yearsExperience" v-model="yearsExperience"
                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                :class="{'border-red-500': skillForm.errors.graduate_skills_years_experience}"
                required>
              <div v-if="skillForm.errors.graduate_skills_years_experience" class="text-red-500 text-sm mt-1">
                {{ skillForm.errors.graduate_skills_years_experience }}
              </div>
            </div>
            <button type="submit"
              class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md flex items-center justify-center transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              <i class="fas fa-save mr-2"></i>
              Save Skill
            </button>
          </form>
        </div>
      </div>

      <!-- Update Skills Modal -->
      <div v-if="isUpdateSkillModalOpen"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Update Skill</h2>
            <button class="text-gray-500 hover:text-gray-700" @click="closeUpdateSkillModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <p class="text-gray-600 mb-4">Update the skill details</p>
          <form @submit.prevent="updateSkill">
            <div class="mb-4">
              <label for="skillName" class="block text-gray-700">Skill Name <span class="text-red-500">*</span></label>
              <input type="text" id="skillName" v-model="skillName"
                class="w-full px-3 py-2 border border-gray-300 rounded-md" 
                :class="{'border-red-500': skillForm.errors.graduate_skills_name}"
                required>
              <div v-if="skillForm.errors.graduate_skills_name" class="text-red-500 text-sm mt-1">
                {{ skillForm.errors.graduate_skills_name }}
              </div>
            </div>
            <div class="mb-4">
              <label for="skillProficiencyType" class="block text-gray-700">Proficiency Type <span class="text-red-500">*</span></label>
              <select id="skillProficiencyType" v-model="skillProficiencyType"
                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                :class="{'border-red-500': skillForm.errors.graduate_skills_proficiency_type}"
                required>
                <option value="" disabled>Select proficiency type</option>
                <option value="Beginner">Beginner</option>
                <option value="Intermediate">Intermediate</option>
                <option value="Advanced">Advanced</option>
                <option value="Expert">Expert</option>
              </select>
              <div v-if="skillForm.errors.graduate_skills_proficiency_type" class="text-red-500 text-sm mt-1">
                {{ skillForm.errors.graduate_skills_proficiency_type }}
              </div>
            </div>
            <div class="mb-4">
              <label for="skillType" class="block text-gray-700">Skill Type <span class="text-red-500">*</span></label>
              <select id="skillType" v-model="skillType"
                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                :class="{'border-red-500': skillForm.errors.graduate_skills_type}"
                required>
                <option value="" disabled>Select skill type</option>
                <option value="Technical Skills">Technical Skills</option>
                <option value="Soft Skills">Soft Skills</option>
                <option value="Language Skills">Language Skills</option>
                <option value="Tool/Platform">Tool/Platform</option>
              </select>
              <div v-if="skillForm.errors.graduate_skills_type" class="text-red-500 text-sm mt-1">
                {{ skillForm.errors.graduate_skills_type }}
              </div>
            </div>
            <div class="mb-4">
              <label for="yearsExperience" class="block text-gray-700">Years of Experience <span class="text-red-500">*</span></label>
              <input type="number" id="yearsExperience" v-model="yearsExperience"
                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                :class="{'border-red-500': skillForm.errors.graduate_skills_years_experience}"
                required>
              <div v-if="skillForm.errors.graduate_skills_years_experience" class="text-red-500 text-sm mt-1">
                {{ skillForm.errors.graduate_skills_years_experience }}
              </div>
            </div>
            <button type="submit"
              class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md flex items-center justify-center transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              <i class="fas fa-save mr-2"></i>
              Update Skill
            </button>
          </form>
        </div>
      </div>
    </div>
</template>

<style scoped>
/* Skill card transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Archived badge styling */
.archived-badge {
  position: absolute;
  top: 8px;
  left: 8px;
  background-color: rgb(254, 243, 199);
  color: rgb(146, 64, 14);
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
}
</style>