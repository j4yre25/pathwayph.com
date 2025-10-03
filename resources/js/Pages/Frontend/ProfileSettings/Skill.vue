<script setup>
import { ref, computed, onMounted, watch, reactive } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import '@fortawesome/fontawesome-free/css/all.css';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ApexCharts from 'vue3-apexcharts';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import TextArea from '@/Components/TextArea.vue';
import SelectInput from '@/Components/SelectInput.vue';
import Datepicker from 'vue3-datepicker';
import { isValid } from 'date-fns';
import { useCompanySearch } from '@/Composables/useCompanySearch';

// Define props
const props = defineProps({
  skillEntries: { type: Array, default: () => [] },
  archivedSkillEntries: { type: Array, default: () => [] },
  experienceEntries: { type: Array, default: () => [] },
  archivedExperienceEntries: { type: Array, default: () => [] },
  activeSection: { type: String, default: '' },
  companies: { type: Array, default: () => [] },
  sectors: { type: Array, default: () => [] },

});


const confirmModal = reactive({
  show: false,
  entry: null,
  message: '',
  confirmLabel: '',
  confirmAction: null,
});

function openArchiveConfirm(skill) {
  confirmModal.show = true;
  confirmModal.entry = skill;
  confirmModal.message = 'Are you sure you want to archive this skill?';
  confirmModal.confirmLabel = 'Archive';
  confirmModal.confirmAction = () => {
    archiveSkill(skill);
    confirmModal.show = false;
  };
}

function closeConfirm() {
  confirmModal.show = false;
  confirmModal.entry = null;
  confirmModal.message = '';
  confirmModal.confirmLabel = '';
  confirmModal.confirmAction = null;
}

// Emit for parent communication
const emit = defineEmits(['closeAllModals', 'resetAllStates', 'refresh-skills']);

// -------------------- SKILLS SECTION --------------------
const isSuccessModalOpen = ref(false);
const isNoChangesModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const isDuplicateModalOpen = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
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

const skillForm = useForm({
  graduate_skills_name: skillName.value,
  graduate_skills_proficiency_type: skillProficiencyType.value,
  graduate_skills_type: skillType.value,
  graduate_skills_years_experience: yearsExperience.value,
});

const saveSkill = () => {
  // Validate fields
  if (!skillName.value.trim()) {
    skillForm.setError('graduate_skills_name', 'The graduate skills name field is required.');
    return;
  }
  if (!skillProficiencyType.value) {
    skillForm.setError('graduate_skills_proficiency_type', 'The graduate skills proficiency type field is required.');
    return;
  }
  if (!skillType.value) {
    skillForm.setError('graduate_skills_type', 'The graduate skills type field is required.');
    return;
  }
  if (yearsExperience.value < 0) {
    skillForm.setError('graduate_skills_years_experience', 'Years of experience cannot be negative.');
    return;
  }

  // Sync form values
  skillForm.graduate_skills_name = skillName.value;
  skillForm.graduate_skills_proficiency_type = skillProficiencyType.value;
  skillForm.graduate_skills_type = skillType.value;
  skillForm.graduate_skills_years_experience = yearsExperience.value;

  skillForm.post(route('profile.skills.add'), {
    preserveScroll: true,
    onSuccess: () => {
      emit('refresh-skills');
      closeAddSkillModal();
      isSuccessModalOpen.value = true;
      skillForm.reset();
    },
    onError: (errors) => {
      if (errors.response?.status === 409) {
        isDuplicateModalOpen.value = true;
      } else {
        isErrorModalOpen.value = true;
      }
    },
  });
};

// skillForm is already defined in saveSkill function, so we don't need to redefine it here

// Update Skill Handler
const openEditSkillModal = (skill) => {
  skillForm.clearErrors();
  currentSkillIndex.value = skills.value.findIndex(s => s.id === skill.id);
  skillName.value = skill.graduate_skills_name;
  skillProficiencyType.value = skill.graduate_skills_proficiency_type;
  skillType.value = skill.graduate_skills_type;
  yearsExperience.value = skill.graduate_skills_years_experience;
  isUpdateSkillModalOpen.value = true;
};

const updateSkill = () => {

    skillForm.graduate_skills_name = skillName.value;
  skillForm.graduate_skills_proficiency_type = skillProficiencyType.value;
  skillForm.graduate_skills_type = skillType.value;
  skillForm.graduate_skills_years_experience = yearsExperience.value;
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



  const skillId = skills.value[currentSkillIndex.value]?.id;
  if (!skillId) {
    console.error('No skill ID found for update');
    return;
  }

  skillForm.put(route('profile.skills.update', { id: skillId }), {
    onSuccess: () => {
      emit('refresh-skills');
      skills.value[currentSkillIndex.value] = { ...skillForm.data(), id: skillId };
      closeUpdateSkillModal();
    },
    onError: (errors) => {
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

const resetSkill = () => {
  skillName.value = '';
  skillProficiencyType.value = '';
  skillType.value = '';
  yearsExperience.value = 0;
};

const closeSkillsAddedModal = () => {
  isSkillsAddedModalOpen.value = false;
};

const closeUpdateSkillModal = () => {
  isUpdateSkillModalOpen.value = false;
  skillName.value = '';
  skillProficiencyType.value = '';
  skillType.value = '';
  yearsExperience.value = 0;
};

// Computed property to group skills by type
const groupedSkills = computed(() => {
  if (!Array.isArray(skills.value)) return {};
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
  skillForm.clearErrors();
  isAddSkillModalOpen.value = true;
};

const unarchiveSkill = (skill) => {
  if (!skill.id) {
    alert('Skill ID not found.');
    return;
  }
  if (!confirm(`Unarchive skill: ${skill.graduate_skills_name}?`)) {
    return;
  }
  router.put(route('profile.skills.unarchive', { id: skill.id }), {}, {
    preserveScroll: true,
    onSuccess: () => {
      emit('refresh-skills');
    },
    onError: (errors) => {
      alert('Failed to unarchive skill.');
    }
  });
};

const removeSkill = (skill) => {
  if (!skill.id) {
    alert('Skill ID not found.');
    return;
  }
  if (!confirm(`Permanently delete skill: ${skill.graduate_skills_name}? This cannot be undone.`)) {
    return;
  }
  router.delete(route('profile.skills.remove', { id: skill.id }), {
    preserveScroll: true,
    onSuccess: () => {
      emit('refresh-skills');
    },
    onError: (errors) => {
      alert('Failed to remove skill.');
    }
  });
};

const archiveSkill = (skill) => {
  if (!skill.id) {
    alert('Skill ID not found.');
    return;
  }
  if (!confirm(`Are you sure you want to archive the skill: ${skill.graduate_skills_name}?`)) {
    return;
  }
  router.put(route('profile.skills.archive', { id: skill.id }), {}, {
    preserveScroll: true,
    onSuccess: () => {
      isSuccessModalOpen.value = true;
      successMessage.value = 'Skill archived successfully!';
      emit('refresh-skills');
    },
    onError: (errors) => {
      isErrorModalOpen.value = true;
      errorMessage.value = 'Failed to archive skill.';
    }
  });
};

// Watcher to update skills when prop changes
watch(
  () => props.skillEntries,
  (newVal) => {
    skills.value = newVal || [];
  },
  { immediate: true }
);

const showArchivedSkills = ref(false);
const archivedSkills = ref(props.archivedSkillEntries || []);

// Watch for changes in archivedSkillEntries prop
watch(() => props.archivedSkillEntries, (newArchivedSkillEntries) => {
  archivedSkills.value = newArchivedSkillEntries || [];
});

const toggleArchivedSkills = () => {
  showArchivedSkills.value = !showArchivedSkills.value;
};

// -------------------- EXPERIENCE SECTION --------------------
const isExperienceSuccessModalOpen = ref(false);
const isExperienceErrorModalOpen = ref(false);
const isExperienceDuplicateModalOpen = ref(false);
const companies = computed(() => props.companies ?? []);
const sectors = ref(props.sectors || []);
const experienceForm = useForm({
  graduate_experience_title: '',
  graduate_experience_company: '',
  graduate_experience_start_date: null,
  graduate_experience_end_date: null,
  graduate_experience_address: '',
  graduate_experience_description: '',
  graduate_experience_employment_type: '',
  is_current: false,
});
const experience = ref({
  title: '',
  company: '',
  start_date: null,
  end_date: null,
  address: '',
  description: '',
  employment_type: '',
  is_current: false,
  id: null,
  company_id: '',
  company_not_found: false,
  other_company_name: '',
  other_company_sector: '',
});
const {
  companySearch,
  showSuggestions,
  filteredCompanies,
  selectCompany,
  clearCompany,
} = useCompanySearch(experience, companies);

const stillInRole = ref(false);
watch(stillInRole, (val) => {
  if (val) {
    experience.value.end_date = null;
    experience.value.current_job_title = experience.value.title || '';
  }
});

const isAddExperienceModalOpen = ref(false);
const isUpdateExperienceModalOpen = ref(false);
const showArchivedExperience = ref(false);
const itemToDelete = ref(null);

const experienceEntries = computed(() => props.experienceEntries || []);
const archivedExperienceEntries = computed(() => props.archivedExperienceEntries || []);

const openAddExperienceModal = () => {
  resetExperience();
  isAddExperienceModalOpen.value = true;
};

const closeAddExperienceModal = () => {
  isAddExperienceModalOpen.value = false;
  resetExperience();
};

const resetExperience = () => {
  experience.value = {
    title: '',
    company: '',
    start_date: null,
    end_date: null,
    address: '',
    description: '',
    employment_type: '',
    is_current: false,
    id: null,
    company_id: '',
    company_not_found: false,
    other_company_name: '',
    other_company_sector: '',
  };
  stillInRole.value = false;
};

const openUpdateExperienceModal = (experienceEntry) => {
  resetExperience();
  experience.value = {
    id: experienceEntry.id,
    title: experienceEntry.title,
    company: experienceEntry.company,
    start_date: experienceEntry.start_date ? formatDate(experienceEntry.start_date) : null,
    end_date: experienceEntry.is_current ? null : (experienceEntry.end_date ? formatDate(experienceEntry.end_date) : null),
    address: experienceEntry.address || '',
    description: experienceEntry.description || 'No description provided',
    employment_type: experienceEntry.employment_type || '',
    is_current: experienceEntry.is_current,
    company_id: experienceEntry.company_id || '',
    company_not_found: experienceEntry.company_not_found || false,
    other_company_name: experienceEntry.other_company_name || '',
    other_company_sector: experienceEntry.other_company_sector || '',
  };
  stillInRole.value = experienceEntry.is_current;
  isUpdateExperienceModalOpen.value = true;
};

const addExperience = () => {
  experienceForm.post(route('profile.experience.add'), {
    onSuccess: (response) => {
      emit('closeAllModals');
      experienceForm.reset();
      isAddExperienceModalOpen.value = false;
      isExperienceSuccessModalOpen.value = true;
    },
    onError: (errors) => {
      isExperienceErrorModal.value = true;
      alert('An error occurred while adding the experience. Please check the form and try again.');
    },
  });
};

const updateExperience = () => {
  experienceForm.graduate_experience_title = experience.value.title?.trim() || '';
  experienceForm.graduate_experience_company = experience.value.company?.trim() || '';
  experienceForm.graduate_experience_start_date = formatDate(experience.value.start_date);
  experienceForm.graduate_experience_end_date = stillInRole.value ? null : formatDate(experience.value.end_date);
  experienceForm.graduate_experience_address = experience.value.address?.trim() || '';
  experienceForm.graduate_experience_description = experience.value.description?.trim() || 'No description provided';
  experienceForm.graduate_experience_employment_type = experience.value.employment_type?.trim() || '';
  experienceForm.is_current = stillInRole.value;

  experienceForm.put(route('profile.experience.update', experience.value.id), {
    onSuccess: () => {
      resetExperience();
      isUpdateExperienceModalOpen.value = false;
    },
    onError: (errors) => {
      alert('Error updating experience.');
    },
  });
};

const closeUpdateExperienceModal = () => {
  isUpdateExperienceModalOpen.value = false;
  resetExperience();
};

const deleteExperience = (experienceId) => {
  if (!experienceId) {
    alert('Experience ID not found.');
    return;
  }
  if (!confirm('Are you sure you want to permanently delete this experience? This action cannot be undone.')) {
    return;
  }
  router.delete(route('profile.experience.remove', { id: experienceId }), {
    preserveScroll: true,
    onSuccess: () => {
      emit('refresh-skills');
    },
    onError: (errors) => {
      alert('Failed to delete experience.');
    }
  });
};

const formatDate = (date) => {
  if (!date) return '';
  const parsedDate = new Date(date);
  if (isNaN(parsedDate.getTime())) return '';
  parsedDate.setHours(0, 0, 0, 0);
  return parsedDate.toISOString().split('T')[0];
};

const formatDisplayDate = (date) => {
  if (!date) return '';
  const d = new Date(date);
  if (!isValid(d)) return '';
  try {
    return d.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  } catch (error) {
    return '';
  }
};

const toggleArchivedExperience = () => {
  showArchivedExperience.value = !showArchivedExperience.value;
};

const unarchiveExperience = (experienceEntry) => {
  if (!experienceEntry.id) {
    alert('Experience ID not found.');
    return;
  }
  if (!confirm(`Unarchive experience: ${experienceEntry.title}?`)) {
    return;
  }
  router.put(route('profile.experience.unarchive', { id: experienceEntry.id }), {}, {
    preserveScroll: true,
    onSuccess: () => {
      emit('refresh-skills');
    },
    onError: (errors) => {
      alert('Failed to unarchive experience.');
    }
  });
};

const archiveExperience = (experienceEntry) => {
  if (!experienceEntry.id) {
    alert('Experience ID not found.');
    return;
  }
  if (!confirm(`Are you sure you want to archive the experience: ${experienceEntry.title}?`)) {
    return;
  }
  router.put(route('profile.experience.archive', { id: experienceEntry.id }), {}, {
    preserveScroll: true,
    onSuccess: () => {
      emit('refresh-skills');
    },
    onError: (errors) => {
      alert('Failed to archive experience.');
    }
  });
};

</script>

<template>
  <div v-if="activeSection === 'skills' || activeSection === 'skills-experience'" class="flex flex-col lg:flex-row">
    <div class="w-full mb-6">
      <!-- Skills Card -->
      <div
        class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-300 mb-6">
        <!-- Card Header -->
        <div class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-100 to-white">
          <div class="flex items-center">
            <div class="bg-blue-200 p-2 rounded-full mr-3">
              <i class="fas fa-cogs text-blue-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-blue-700">Skills</h3>
          </div>
          <div class="flex space-x-2">
            <PrimaryButton
              class="bg-blue-600 text-white px-3 py-1 rounded-lg flex items-center hover:bg-blue-700 text-sm"
              @click="openAddSkillModal">
              <i class="fas fa-plus mr-1"></i> Add Skill
            </PrimaryButton>
            <button
              class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded-lg flex items-center transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer text-sm"
              @click="toggleArchivedSkills">
              <i class="fas" :class="showArchivedSkills ? 'fa-eye-slash' : 'fa-eye'"></i>
              <span class="ml-1">{{ showArchivedSkills ? 'Hide Archived' : 'Show Archived' }}</span>
            </button>
          </div>
        </div>

        <!-- Card Body -->
        <div class="p-6 transition-all duration-300">
          <p class="text-gray-600 mb-6">Showcase your professional expertise and competencies</p>

          <!-- Skills Entries -->
          <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <div class="xl:col-span-2 space-y-6">
              <div v-if="Object.keys(groupedSkills).length > 0" class="space-y-8">
                <div v-for="(skillsGroup, type) in groupedSkills" :key="type"
                  class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                  <div class="p-4 border-b border-gray-200 flex items-center bg-gradient-to-r from-blue-50 to-white">
                    <div class="bg-blue-200 p-2 rounded-full mr-3">
                      <i class="fas text-blue-600" :class="{
                        'fa-laptop-code': type === 'Technical Skills',
                        'fa-comments': type === 'Soft Skills',
                        'fa-language': type === 'Language Skills',
                        'fa-tools': type === 'Tool/Platform',
                        'fa-cogs': true
                      }"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-blue-700">{{ type }}</h2>
                  </div>
                  <div class="p-6">
                    <div class="grid md:grid-cols-2 gap-6">
                      <div v-for="skill in skillsGroup" :key="skill.graduate_skills_name"
                        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-all duration-300 space-y-3 relative border border-gray-200 group">
                        <div class="border-b border-blue-100 pb-3">
                          <h3 class="text-xl font-bold text-blue-900">{{ skill.graduate_skills_name }}</h3>
                          <div class="flex items-center mt-1">
                            <span
                              class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 mr-2">
                              {{ skill.graduate_skills_proficiency_type }}
                            </span>
                            <span class="text-gray-600 text-sm">
                              {{ skill.graduate_skills_years_experience }} year(s)
                            </span>
                          </div>
                        </div>

                        <!-- Proficiency Rating Stars -->
                        <div class="flex items-center mt-2">
                          <i class="fas fa-star-half-alt text-blue-600 mr-2"></i>
                          <span class="font-medium text-blue-900">Proficiency:</span>
                          <div class="flex ml-2">
                            <template v-for="n in 5" :key="n">
                              <i class="fas fa-star text-sm mr-0.5" :class="{
                                'text-blue-500': (skill.graduate_skills_proficiency_type === 'Beginner' && n <= 1) ||
                                  (skill.graduate_skills_proficiency_type === 'Intermediate' && n <= 2) ||
                                  (skill.graduate_skills_proficiency_type === 'Advanced' && n <= 4) ||
                                  (skill.graduate_skills_proficiency_type === 'Expert' && n <= 5),
                                'text-gray-300': (skill.graduate_skills_proficiency_type === 'Beginner' && n > 1) ||
                                  (skill.graduate_skills_proficiency_type === 'Intermediate' && n > 2) ||
                                  (skill.graduate_skills_proficiency_type === 'Advanced' && n > 4) ||
                                  (skill.graduate_skills_proficiency_type === 'Expert' && n > 5)
                              }"></i>
                            </template>
                          </div>
                        </div>

                        <!-- Skill Progress Bar -->
                        <div class="mt-3">
                          <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                            <div class="h-2.5 rounded-full transition-all duration-300 bg-blue-500" :class="{
                              'w-1/4': skill.graduate_skills_proficiency_type === 'Beginner',
                              'w-2/4': skill.graduate_skills_proficiency_type === 'Intermediate',
                              'w-3/4': skill.graduate_skills_proficiency_type === 'Advanced',
                              'w-full': skill.graduate_skills_proficiency_type === 'Expert'
                            }">
                            </div>
                          </div>
                        </div>

                        <!-- Experience Indicator -->
                        <div class="mt-3 flex items-center">
                          <i class="fas fa-history text-blue-600 mr-2"></i>
                          <span class="font-medium text-blue-900">Experience:</span>
                          <span class="ml-2">{{ skill.graduate_skills_years_experience }} year(s)</span>
                        </div>
                        <div class="flex space-x-1 mt-1">
                          <div v-for="n in 5" :key="n" class="flex-1 h-1.5 rounded transition-all duration-200" :class="{
                            'bg-blue-500': n <= skill.graduate_skills_years_experience,
                            'bg-gray-200': n > skill.graduate_skills_years_experience
                          }">
                          </div>
                        </div>

                        <!-- Action Buttons -->
                        <div
                          class="absolute top-2 right-2 flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                          <button
                            class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 p-1.5 rounded-full transition-colors duration-200"
                            @click="editSkill(skill)">
                            <i class="fas fa-edit"></i>
                          </button>
                          <button
                            class="text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 p-1.5 rounded-full transition-colors duration-200"
                            @click="openArchiveConfirm(skill)">
                            <i class="fas fa-archive"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- No Skills Message -->
              <div v-else class="bg-white p-8 rounded-lg shadow-md border border-gray-200 text-center">
                <div class="flex flex-col items-center justify-center py-4">
                  <div class="bg-blue-100 text-blue-600 p-3 rounded-full mb-3">
                    <i class="fas fa-code text-2xl"></i>
                  </div>
                  <h3 class="text-xl font-semibold text-gray-900 mb-2">No skills added yet</h3>
                  <p class="text-gray-600 mb-4">Add your skills to showcase your expertise</p>
                  <PrimaryButton @click="openAddSkillModal"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors duration-200">
                    <i class="fas fa-plus mr-2"></i>
                    Add Skill
                  </PrimaryButton>
                </div>
              </div>
            </div>


          </div>
        </div>

        <!-- Archived Skills -->
        <div v-if="showArchivedSkills" class="mt-8">
          <h2 class="text-lg font-medium mb-4">Archived Skills</h2>
          <div v-if="archivedSkills.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div v-for="skill in archivedSkills" :key="skill.id"
              class="bg-blue-50/30 rounded-lg p-6 transition-all duration-200 hover:bg-blue-50/50 border border-blue-100 relative group">
              <div class="opacity-75">
                <div class="flex items-start justify-between mb-4">
                  <div class="flex-1">
                    <h3 class="text-lg font-medium text-blue-800">{{ skill.graduate_skills_name }}</h3>
                    <span class="inline-flex mt-2 px-3 py-1 rounded-full text-sm font-medium" :class="{
                      'bg-blue-100 text-blue-800': skill.graduate_skills_proficiency_type === 'Beginner',
                      'bg-blue-200 text-blue-800': skill.graduate_skills_proficiency_type === 'Intermediate',
                      'bg-blue-300 text-blue-800': skill.graduate_skills_proficiency_type === 'Advanced',
                      'bg-blue-400 text-blue-900': skill.graduate_skills_proficiency_type === 'Expert'
                    }">
                      {{ skill.graduate_skills_proficiency_type }}
                    </span>
                  </div>
                </div>
                <div class="space-y-4">
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all duration-300" :class="{
                      'w-1/4 bg-blue-500': skill.graduate_skills_proficiency_type === 'Beginner',
                      'w-2/4 bg-blue-600': skill.graduate_skills_proficiency_type === 'Intermediate',
                      'w-3/4 bg-blue-700': skill.graduate_skills_proficiency_type === 'Advanced',
                      'w-full bg-blue-800': skill.graduate_skills_proficiency_type === 'Expert'
                    }">
                    </div>
                  </div>
                  <div class="flex items-center justify-between text-sm">
                    <span class="font-medium text-blue-700">Years of Experience</span>
                    <span class="text-blue-600">{{ skill.graduate_skills_years_experience }} year(s)</span>
                  </div>
                  <div class="flex space-x-2">
                    <div v-for="n in 5" :key="n" class="flex-1 h-1.5 rounded transition-all duration-200" :class="{
                      'bg-blue-500': n <= skill.graduate_skills_years_experience,
                      'bg-blue-100': n > skill.graduate_skills_years_experience
                    }">
                    </div>
                  </div>
                </div>
              </div>
              <div
                class="absolute top-2 right-2 flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <button
                  class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 p-1.5 rounded-full transition-colors duration-200"
                  @click="unarchiveSkill(skill)">
                  <i class="fas fa-box-open"></i>
                </button>
                <button
                  class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 p-1.5 rounded-full transition-colors duration-200"
                  @click="removeSkill(skill)">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
              <div
                class="absolute top-2 left-2 bg-amber-100 text-amber-800 text-xs px-3 py-1 rounded-full shadow-sm font-medium">
                <i class="fas fa-archive mr-1"></i> Archived
              </div>
            </div>
          </div>

          <!-- If no archived skills exist -->
          <div v-else class="bg-white p-8 rounded-lg shadow-md border border-gray-200 text-center">
            <div class="flex flex-col items-center justify-center py-4">
              <div class="bg-blue-100 text-blue-600 p-3 rounded-full mb-3">
                <i class="fas fa-archive text-2xl"></i>
              </div>
              <h3 class="text-xl font-semibold text-gray-900 mb-2">No archived skills found</h3>
              <p class="text-gray-600 mb-4">Archived skills will appear here when you archive them</p>
            </div>
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
                :class="{ 'border-red-500': skillForm.errors.graduate_skills_name }" required>
              <div v-if="skillForm.errors.graduate_skills_name" class="text-red-500 text-sm mt-1">
                {{ skillForm.errors.graduate_skills_name }}
              </div>
            </div>
            <div class="mb-4">
              <label for="skillProficiencyType" class="block text-gray-700">Proficiency Type <span
                  class="text-red-500">*</span></label>
              <select id="skillProficiencyType" v-model="skillProficiencyType"
                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                :class="{ 'border-red-500': skillForm.errors.graduate_skills_proficiency_type }" required>
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
              <select id="skillType" v-model="skillType" class="w-full px-3 py-2 border border-gray-300 rounded-md"
                :class="{ 'border-red-500': skillForm.errors.graduate_skills_type }" required>
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
              <label for="yearsExperience" class="block text-gray-700">Years of Experience <span
                  class="text-red-500">*</span></label>
              <input type="number" id="yearsExperience" v-model="yearsExperience"
                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                :class="{ 'border-red-500': skillForm.errors.graduate_skills_years_experience }" required>
              <div v-if="skillForm.errors.graduate_skills_years_experience" class="text-red-500 text-sm mt-1">
                {{ skillForm.errors.graduate_skills_years_experience }}
              </div>
            </div>
            <button type="submit"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md flex items-center justify-center transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
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
                :class="{ 'border-red-500': skillForm.errors.graduate_skills_name }" required>
              <div v-if="skillForm.errors.graduate_skills_name" class="text-red-500 text-sm mt-1">
                {{ skillForm.errors.graduate_skills_name }}
              </div>
            </div>
            <div class="mb-4">
              <label for="skillProficiencyType" class="block text-gray-700">Proficiency Type <span
                  class="text-red-500">*</span></label>
              <select id="skillProficiencyType" v-model="skillProficiencyType"
                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                :class="{ 'border-red-500': skillForm.errors.graduate_skills_proficiency_type }" required>
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
              <select id="skillType" v-model="skillType" class="w-full px-3 py-2 border border-gray-300 rounded-md"
                :class="{ 'border-red-500': skillForm.errors.graduate_skills_type }" required>
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
              <label for="yearsExperience" class="block text-gray-700">Years of Experience <span
                  class="text-red-500">*</span></label>
              <input type="number" id="yearsExperience" v-model="yearsExperience"
                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                :class="{ 'border-red-500': skillForm.errors.graduate_skills_years_experience }" required>
              <div v-if="skillForm.errors.graduate_skills_years_experience" class="text-red-500 text-sm mt-1">
                {{ skillForm.errors.graduate_skills_years_experience }}
              </div>
            </div>
            <button type="submit"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md flex items-center justify-center transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              <i class="fas fa-save mr-2"></i>
              Update Skill
            </button>
          </form>
        </div>

        <!-- Archived Experience Entries -->
        <div v-if="showArchivedExperience" class="mt-8">
          <h2 class="text-lg font-medium mb-4">Archived Experience</h2>
          <div v-if="archivedExperienceEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div v-for="experienceEntry in archivedExperienceEntries" :key="experienceEntry.id"
              class="bg-blue-50/30 p-6 rounded-lg shadow-md space-y-4 relative border border-blue-100 group hover:bg-blue-50/50 transition-all duration-300">
              <div class="opacity-80">
                <div class="border-b border-blue-100 pb-3">
                  <h2 class="text-xl font-bold text-blue-800">{{ experienceEntry.title }}</h2>
                  <p class="text-gray-600 font-medium">
                    {{ experienceEntry.company }}
                  </p>
                </div>
                <div class="mt-4 space-y-3">
                  <div class="flex items-center text-gray-600">
                    <div class="bg-blue-100 p-1.5 rounded-full mr-3">
                      <i class="fas fa-map-marker-alt text-blue-500"></i>
                    </div>
                    <span>
                      {{ experienceEntry.address }}
                    </span>
                  </div>
                  <div class="flex items-center text-gray-600">
                    <div class="bg-blue-100 p-1.5 rounded-full mr-3">
                      <i class="far fa-calendar-alt text-blue-500"></i>
                    </div>
                    <span>
                      {{ formatDate(experienceEntry.start_date) }} - {{ experienceEntry.is_current ? 'Present' :
                        formatDate(experienceEntry.end_date) }}
                    </span>
                  </div>
                  <div class="flex items-center text-gray-600">
                    <div class="bg-blue-100 p-1.5 rounded-full mr-3">
                      <i class="fas fa-briefcase text-blue-500"></i>
                    </div>
                    <span><strong>Employment Type:</strong> {{ experienceEntry.employment_type }}</span>
                  </div>
                  <div class="flex items-start text-gray-600 mt-1">
                    <div class="bg-blue-100 p-1.5 rounded-full mr-3 mt-0.5">
                      <i class="fas fa-info-circle text-blue-500"></i>
                    </div>
                    <div>
                      <strong>Description:</strong>
                      <p class="mt-1">{{ experienceEntry.description || 'No description provided' }}</p>
                    </div>
                  </div>
                </div>
              </div>
              <div
                class="absolute top-4 right-4 flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <button class="text-blue-600 hover:text-blue-800 bg-blue-100 p-1.5 rounded-full"
                  @click="unarchiveExperience(experienceEntry)">
                  <i class="fas fa-box-open"></i>
                </button>
                <button class="text-red-600 hover:text-red-800 bg-red-50 p-1.5 rounded-full"
                  @click="deleteExperience(experienceEntry.id)">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
              <div
                class="absolute -top-2 -left-2 bg-blue-200 text-blue-800 text-xs px-3 py-1 rounded-full shadow-sm font-medium">
                <i class="fas fa-archive mr-1"></i> Archived
              </div>
            </div>
          </div>

          <!-- If no archived experience entries exist -->
          <div v-else class="bg-white p-8 rounded-lg shadow border border-blue-50 text-center">
            <div class="flex flex-col items-center justify-center py-4">
              <div class="bg-blue-50 text-blue-500 p-3 rounded-full mb-3">
                <i class="fas fa-archive text-2xl"></i>
              </div>
              <h3 class="text-lg font-medium text-blue-800 mb-2">No archived experience entries</h3>
              <p class="text-gray-600 max-w-md mx-auto">When you archive work experience entries, they will appear here.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Experience Modal -->
      <div v-if="isAddExperienceModalOpen"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md border border-blue-100">
          <div class="flex justify-between items-center mb-6 border-b border-blue-100 pb-3">
            <h2 class="text-xl font-semibold text-blue-800 flex items-center">
              <div class="bg-blue-50 p-2 rounded-full mr-3">
                <i class="fas fa-briefcase text-blue-500"></i>
              </div>
              Add Experience
            </h2>
            <button class="text-gray-500 hover:text-blue-700 bg-blue-50 p-2 rounded-full transition-colors duration-200"
              @click="closeAddExperienceModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="max-h-96 overflow-y-auto px-1">
            <form @submit.prevent="addExperience">
              <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Job Title <span
                    class="text-red-500">*</span></label>
                <input type="text" v-model="experienceForm.graduate_experience_title"
                  class="w-full px-3 py-2 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="e.g. Software Engineer" required />
                <InputError :message="experienceForm.errors.graduate_experience_title" class="mt-2" />
              </div>
              <div class="mb-4">
                <label class="block text-gray-700 font-medium  mb-2">Company <span class="text-red-500">*</span></label>
                <div class="relative">
                  <input v-model="companySearch" @focus="showSuggestions = true" @input="showSuggestions = true"
                    @blur="setTimeout(() => showSuggestions = false, 200)" type="text"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                    placeholder="Type to search company" :disabled="experience.company_not_found" autocomplete="off"
                    required />
                  <!-- Suggestions Dropdown -->
                  <ul v-if="showSuggestions && filteredCompanies.length"
                    class="absolute z-10 bg-white border border-gray-200 w-full mt-1 rounded shadow max-h-48 overflow-y-auto">
                    <li v-for="company in filteredCompanies" :key="company.id"
                      @mousedown.prevent="selectCompany(company)" class="px-4 py-2 hover:bg-indigo-100 cursor-pointer">
                      {{ company.name }}
                    </li>
                  </ul>
                </div>
                <InputError :message="experienceForm.errors.graduate_experience_company" class="mt-2" />
                <!-- Company Not Found Checkbox -->
                <div class="mt-2 flex items-center">
                  <input type="checkbox" id="company-not-found" v-model="experience.company_not_found"
                    @change="clearCompany" class="mr-2" />
                  <label for="company-not-found" class="text-sm text-gray-700">Company not found</label>
                </div>
                <!-- Other Company Fields -->
                <div v-if="experience.company_not_found" class="mt-4 space-y-2">
                  <input v-model="experience.other_company_name" type="text"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                    placeholder="Enter company name" required />
                  <select v-model="experience.other_company_sector"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                    required>
                    <option value="" disabled>Select sector</option>
                    <option v-for="sector in props.sectors" :key="sector.id" :value="sector.id">
                      {{ sector.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Location <span class="text-red-500">*</span></label>
                <input type="text" v-model="experienceForm.graduate_experience_address"
                  class="w-full px-3 py-2 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="e.g. New York, NY" required />
                <InputError :message="experienceForm.errors.graduate_experience_address" class="mt-2" />
              </div>
              <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Employment Type <span
                    class="text-red-500">*</span></label>
                <select v-model="experienceForm.graduate_experience_employment_type"
                  class="w-full px-3 py-2 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required>
                  <option value="" disabled>Select employment type</option>
                  <option value="Full-time">Full-time</option>
                  <option value="Part-time">Part-time</option>
                  <option value="Contract">Contract</option>
                  <option value="Freelance">Freelance</option>
                  <option value="Internship">Internship</option>
                </select>
                <InputError :message="experienceForm.errors.graduate_experience_employment_type" class="mt-2" />
              </div>
              <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Start Date <span
                    class="text-red-500">*</span></label>
                <Datepicker v-model="experienceForm.graduate_experience_start_date"
                  class="w-full px-3 py-2 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Select start date" required :enable-time-picker="false" :format="'yyyy-MM-dd'"
                  :preview-format="'yyyy-MM-dd'" />
                <InputError :message="experienceForm.errors.graduate_experience_start_date" class="mt-2" />
              </div>
              <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">End Date</label>
                <Datepicker v-model="experienceForm.graduate_experience_end_date"
                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                  placeholder="Select end date" :disabled="stillInRole" :enable-time-picker="false"
                  :format="'yyyy-MM-dd'" :preview-format="'yyyy-MM-dd'" />
                <div class="mt-2 flex items-center">
                  <input type="checkbox" id="still-in-role-update" v-model="stillInRole" class="mr-2" />
                  <label for="still-in-role-update" class="text-sm text-gray-700">I currently work here</label>
                </div>
              </div>
              <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Description</label>
                <textarea v-model="experienceForm.graduate_experience_description"
                  class="w-full px-3 py-2 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  rows="3" placeholder="Describe your role and responsibilities..."></textarea>
                <InputError :message="experienceForm.errors.graduate_experience_description" class="mt-2" />
              </div>
              <div class="flex justify-end">
                <button type="submit"
                  class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-md flex items-center justify-center transition-colors duration-200"
                  :disabled="experienceForm.processing">
                  <i class="fas fa-save mr-2"></i>
                  {{ experienceForm.processing ? 'Saving...' : 'Save Experience' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- UpdateExperience Modal -->
      <div v-if="isUpdateExperienceModalOpen"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md border border-blue-100">
          <div class="flex justify-between items-center mb-6 border-b border-blue-100 pb-3">
            <h2 class="text-xl font-semibold text-blue-800 flex items-center">
              <div class="bg-blue-50 p-2 rounded-full mr-3">
                <i class="fas fa-edit text-blue-500"></i>
              </div>
              Update Experience
            </h2>
            <button class="text-gray-500 hover:text-blue-700 bg-blue-50 p-2 rounded-full transition-colors duration-200"
              @click="closeUpdateExperienceModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="max-h-96 overflow-y-auto px-1">
            <form @submit.prevent="updateExperience">
              <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Job Title <span
                    class="text-red-500">*</span></label>
                <input type="text" v-model="experience.title"
                  class="w-full px-3 py-2 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="e.g. Software Engineer" required />
                <InputError :message="experienceForm.errors.graduate_experience_title" class="mt-2" />
              </div>
              <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Company <span class="text-red-500">*</span></label>
                <div class="relative">
                  <input v-model="companySearch" @focus="showSuggestions = true" @input="showSuggestions = true"
                    @blur="setTimeout(() => showSuggestions = false, 200)" type="text"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                    placeholder="Type to search company" :disabled="experience.company_not_found" autocomplete="off"
                    required />
                  <!-- Suggestions Dropdown -->
                  <ul v-if="showSuggestions && filteredCompanies.length"
                    class="absolute z-10 bg-white border border-gray-200 w-full mt-1 rounded shadow max-h-48 overflow-y-auto">
                    <li v-for="company in filteredCompanies" :key="company.id"
                      @mousedown.prevent="selectCompany(company)" class="px-4 py-2 hover:bg-indigo-100 cursor-pointer">
                      {{ company.name }}
                    </li>
                  </ul>
                </div>
                <InputError :message="experienceForm.errors.graduate_experience_company" class="mt-2" />
                <!-- Company Not Found Checkbox -->
                <div class="mt-2 flex items-center">
                  <input type="checkbox" id="company-not-found" v-model="experience.company_not_found"
                    @change="clearCompany" class="mr-2" />
                  <label for="company-not-found" class="text-sm text-gray-700">Company not found</label>
                </div>
                <!-- Other Company Fields -->
                <div v-if="experience.company_not_found" class="mt-4 space-y-2">
                  <input v-model="experience.other_company_name" type="text"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                    placeholder="Enter company name" required />
                  <select v-model="experience.other_company_sector"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                    required>
                    <option value="" disabled>Select sector</option>
                    <option v-for="sector in props.sectors" :key="sector.id" :value="sector.id">
                      {{ sector.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Location <span class="text-red-500">*</span></label>
                <input type="text" v-model="experience.address"
                  class="w-full px-3 py-2 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="e.g. New York, NY" required />
                <InputError :message="experienceForm.errors.graduate_experience_address" class="mt-2" />
              </div>
              <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Employment Type <span
                    class="text-red-500">*</span></label>
                <select v-model="experience.employment_type"
                  class="w-full px-3 py-2 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required>
                  <option value="" disabled>Select employment type</option>
                  <option value="Full-time">Full-time</option>
                  <option value="Part-time">Part-time</option>
                  <option value="Contract">Contract</option>
                  <option value="Freelance">Freelance</option>
                  <option value="Internship">Internship</option>
                </select>
                <InputError :message="experienceForm.errors.graduate_experience_employment_type" class="mt-2" />
              </div>
              <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Start Date <span
                    class="text-red-500">*</span></label>
                <Datepicker v-model="experience.start_date"
                  class="w-full px-3 py-2 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Select start date" required :enable-time-picker="false" :format="'yyyy-MM-dd'"
                  :preview-format="'yyyy-MM-dd'" />
                <InputError :message="experienceForm.errors.graduate_experience_start_date" class="mt-2" />
              </div>
              <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">End Date</label>
                <Datepicker v-model="experience.end_date"
                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                  placeholder="Select end date" :disabled="stillInRole" :enable-time-picker="false"
                  :format="'yyyy-MM-dd'" :preview-format="'yyyy-MM-dd'" />
                <div class="mt-2 flex items-center">
                  <input type="checkbox" id="still-in-role-update" v-model="stillInRole" class="mr-2" />
                  <label for="still-in-role-update" class="text-sm text-gray-700">I currently work here</label>
                </div>
              </div>
              <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Description</label>
                <textarea v-model="experience.description"
                  class="w-full px-3 py-2 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  rows="3" placeholder="Describe your role and responsibilities..."></textarea>
                <InputError :message="experienceForm.errors.graduate_experience_description" class="mt-2" />
              </div>
              <div class="flex justify-end">
                <button type="submit"
                  class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-md flex items-center justify-center transition-colors duration-200"
                  :disabled="experienceForm.processing">
                  <i class="fas fa-save mr-2"></i>
                  {{ experienceForm.processing ? 'Updating...' : 'Update Experience' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <Modal :modelValue="isSuccessModalOpen" @close="isSuccessModalOpen = false">
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
  <Modal :modelValue="isErrorModalOpen" @close="isErrorModalOpen = false">
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

  <!-- Archive Confirmation Modal -->
  <Modal :show="confirmModal.show" @close="closeConfirm">
    <div class="p-6">
      <div class="flex items-center justify-center mb-4">
        <div class="bg-amber-100 rounded-full p-3">
          <i class="fas fa-archive text-amber-500 text-xl"></i>
        </div>
      </div>
      <h3 class="text-lg font-medium text-center text-gray-900 mb-2">
        {{ confirmModal.confirmLabel }} Skill
      </h3>
      <p class="text-center text-gray-600 mb-4">{{ confirmModal.message }}</p>
      <div class="mt-6 flex justify-center space-x-2">
        <SecondaryButton type="button" @click="closeConfirm">Cancel</SecondaryButton>
        <PrimaryButton type="button" class="bg-amber-500 hover:bg-amber-600" @click="confirmModal.confirmAction">
          {{ confirmModal.confirmLabel }}
        </PrimaryButton>
      </div>
    </div>
  </Modal>
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
  background-color: rgb(219, 234, 254);
  /* blue-100 */
  color: rgb(29, 78, 216);
  /* blue-700 */
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
}

/* Glow effects for form elements */
@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
  }

  70% {
    box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
  }

  100% {
    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

input:focus,
textarea:focus,
select:focus {
  animation: pulse 1.5s infinite;
  transition: all 0.3s ease;
}

button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
}

.form-group {
  animation: fadeIn 0.6s ease-out;
}

.modal-content {
  animation: fadeIn 0.4s ease-out;
}
</style>