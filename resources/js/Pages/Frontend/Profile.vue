<script setup>
import Graduate from '@/Layouts/AppLayout.vue';
import { ref, computed, onMounted, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import TextArea from '@/Components/TextArea.vue';
import SelectInput from '@/Components/SelectInput.vue';
import axios from 'axios';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Datepicker from 'vue3-datepicker';
import { isValid } from 'date-fns';
import VueApexCharts from 'vue3-apexcharts';
import SkillsChart from '@/Components/SkillsChart.vue';



// Modal State Management
const isSuccessModalOpen = ref(false);
const isNoChangesModalOpen = ref(false);
const isPasswordUpdatedModalOpen = ref(false);
const isDuplicateEducationModalOpen = ref(false);
const isEducationAddedModalOpen = ref(false);
const isEducationUpdatedModalOpen = ref(false);
const modalState = ref({
 profile: false,
 password: false
});
const duplicateEducationMessage = ref('');
const educationModalMessage = ref('');
 
// Modal Handlers
const showSuccessModal = () => {
isSuccessModalOpen.value = true;
 setTimeout(() => {
   isSuccessModalOpen.value = false;
 }, 3000);
};
 
const showPasswordUpdatedModal = () => {
isPasswordUpdatedModalOpen.value = true;
 setTimeout(() => {
  isPasswordUpdatedModalOpen.value = false;
  router.visit(route('logout'), { method: 'post' });
 }, 3000);
};

// Profile Data
const { props } = usePage();
const user = ref(props?.user || {});
const profile = ref({
 fullName: `${props.user?.graduate_first_name || ''} ${props.user?.graduate_middle_initial || ''} ${props.user?.graduate_last_name || ''}`.trim(),
 graduate_first_name: props.user?.graduate_first_name || '',
graduate_middle_initial: props.user?.graduate_middle_initial || '',
 graduate_last_name: props.user?.graduate_last_name || '',
graduate_professional_title: props.user?.graduate_professional_title || '',
 email: props.user?.email || '',
 graduate_phone: props.user?.contact_number || '',
 graduate_location: props.user?.graduate_location || '',
 graduate_birthdate: props.user?.dob ? new Date(props.user.dob) : null,
 graduate_gender: props.user?.gender || '',
 graduate_ethnicity: props.user?.graduate_ethnicity || '',
 graduate_address: props.user?.graduate_address || '',
 graduate_about_me: props.user?.graduate_about_me || '',
graduate_picture_url: props.user?.profile_picture || 'path/to/default/image.jpg',
});
 
// Form for settings
const settingsForm = useForm({
 current_password: '',
 password: '',
password_confirmation: '',
 graduate_first_name: profile.value.graduate_first_name,
graduate_middle_initial: profile.value.graduate_middle_initial,
 graduate_last_name: profile.value.graduate_last_name,
graduate_professional_title: profile.value.graduate_professional_title,
 email: profile.value.email,
 graduate_phone: profile.value.graduate_phone,
 graduate_location: profile.value.graduate_location,
 dob: profile.value.dob,
 graduate_gender: profile.value.graduate_gender,
 graduate_ethnicity: profile.value.graduate_ethnicity,
 graduate_address: profile.value.graduate_address,
 graduate_about_me: profile.value.graduate_about_me,
graduate_picture_url: profile.value.graduate_picture_url,
});
 
// Profile Name Handling
const parseFullName = () => {
 const fullName = profile.value.fullName.trim();
 const nameParts = fullName.split(' ');
 
 if (nameParts.length >= 1) {
  profile.value.graduate_first_name = nameParts[0];
 }
 if (nameParts.length >= 2) {
  profile.value.graduate_last_name = nameParts[nameParts.length - 1];
 }
 if (nameParts.length > 2) {
  profile.value.graduate_middle_initial = nameParts[1].charAt(0);
 }
};
 
// Watch for name changes
watch(() => profile.value.fullName, (newFullName) => {
 const nameParts = newFullName.trim().split(' ');
 profile.value.graduate_first_name = nameParts[0] || '';
profile.value.graduate_last_name = nameParts[nameParts.length - 1] || '';
profile.value.graduate_middle_initial = nameParts.length > 2 ? nameParts[1].charAt(0) : '';
});
 
watch(() => profile.value.fullName, () => {
 parseFullName();
});
 
// Profile Update Handler
const saveProfile = () => {
 if (profile.value.graduate_birthdate) {
   const date = new Date(profile.value.graduate_birthdate);
   settingsForm.dob = date.toISOString().split('T')[0];
 } else {
   settingsForm.dob = null;
 }
settingsForm.graduate_first_name = profile.value.graduate_first_name;
settingsForm.graduate_middle_initial = profile.value.graduate_middle_initial;
settingsForm.graduate_last_name = profile.value.graduate_last_name;
 settingsForm.email = profile.value.email;
settingsForm.graduate_phone = profile.value.graduate_phone;
settingsForm.graduate_professional_title = profile.value.graduate_professional_title;
 settingsForm.graduate_location = profile.value.graduate_location;
settingsForm.graduate_gender = profile.value.graduate_gender;
settingsForm.graduate_ethnicity = profile.value.graduate_ethnicity;
settingsForm.graduate_address = profile.value.graduate_address;
settingsForm.graduate_about_me = profile.value.graduate_about_me;
settingsForm.graduate_picture_url = profile.value.graduate_picture_url;
 
 const hasChanges = Object.keys(settingsForm.data()).some(
   (key) => settingsForm[key] !== profile.value[key]
 );
 
 if (!hasChanges) {
  isNoChangesModalOpen.value = true;
   return;
 }
 
settingsForm.post(route('profile.updateProfile'), {
   onSuccess: (response) => {
    Object.assign(profile.value, settingsForm.data());
    modalState.value.profile = true;
    showSuccessModal(); // Show success modal
    
    console.log('Profile saved successfully on the backend:', response.user);
   },
   onError: (errors) => {
    console.error('Error updating profile:', errors);
     alert('An error occurred while updating the profile. Please try again.');
   },
 });
};
 
// Profile Picture Handler
const onFileChange = (event) => {
 const file = event.target.files[0];
 if (file) {
   const reader = new FileReader();
   reader.onload = (e) => {
    profile.value.graduate_picture_url = e.target.result;
    console.log('Profile picture updated:', file.name);
   };
  reader.readAsDataURL(file);
 }
};
 
// Password Management
const passwordInput = ref(null);
const currentPasswordInput = ref(null);
const currentPassword = ref('');
const newPassword = ref('');
const confirmPassword = ref('');
 
const validatePassword = (password) => {
 const minLength = 8;
 const hasUpperCase = /[A-Z]/.test(password);
 const hasLowerCase = /[a-z]/.test(password);
 const hasNumber = /[0-9]/.test(password);
 const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
 return password.length >= minLength && hasUpperCase && hasLowerCase && hasNumber && hasSpecialChar;
};
 
const updatePassword = () => {
 if (!validatePassword(settingsForm.password)) {
   alert('Password must be at least 8 characters long and contain uppercase, lowercase, number, and special characters.');
   return;
 }
 if (settingsForm.password !== settingsForm.password_confirmation) {
   alert('Password confirmation does not match.');
   return;
 }
 
settingsForm.put(route('user-password.update'), {
   errorBag: 'updatePassword',
   preserveScroll: true,
   onSuccess: () => {
    settingsForm.reset();
    showPasswordUpdatedModal();
   },
   onError: () => {
    console.log('Form Data:', {
      current_password: settingsForm.current_password,
       password: settingsForm.password,
      password_confirmation: settingsForm.password_confirmation,
     });
 
     if (settingsForm.errors.password) {
      settingsForm.reset('password', 'password_confirmation');
      passwordInput.value.focus();
     }
     if (settingsForm.errors.current_password) {
       settingsForm.reset('current_password');
      currentPasswordInput.value.focus();
     }
   },
 });
};
 
const handleSubmit = () => {
 updatePassword();
};
 
//End

const datepickerConfig = {
  format: 'YYYY-MM-DD',
  enableTime: false,
  parseDate: (dateStr) => {
    if (!dateStr) return null;
    const date = new Date(dateStr);
    return isNaN(date.getTime()) ? null : date;
  },
  formatDate: (date) => {
    if (!date || isNaN(date.getTime())) return '';
    return date.toISOString().split('T')[0];
  }
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
    console.error('Date formatting error:', error);
    return '';
  }
};

const formatCertificationDate = (issueDate, expiryDate, noExpiry = false) => {
  const formattedIssueDate = formatDisplayDate(issueDate);
  return noExpiry ? `${formattedIssueDate} - No expiry date` : `${formattedIssueDate} - ${formatDisplayDate(expiryDate)}`;
};

const formattedStartDate = computed(() => {
  return education.value.graduate_education_start_date
    ? new Date(education.value.graduate_education_start_date).toISOString().split('T')[0]
    : null;
});

const formattedEndDate = computed(() => {
  if (education.value.is_current) return null;
  return education.value.graduate_education_end_date
    ? new Date(education.value.graduate_education_end_date).toISOString().split('T')[0]
    : null;
});

// State Management
const stillInRole = ref(false);
const isDeleteConfirmationModalOpen = ref(false);
const itemToDelete = ref(null);
const activeSection = ref(localStorage.getItem('activeSection') || 'general');
const setActiveSection = (section) => {
  console.log('Active Section:', section);
  activeSection.value = section;
  localStorage.setItem('activeSection', section);
};

// Education Data
const educationEntries = ref(props.educationEntries || []);
const education = ref({
  graduate_education_institution_id: '',
  graduate_education_program: '',
  graduate_education_field_of_study: '',
  graduate_education_start_date: null,
  graduate_education_end_date: null,
  graduate_education_description: '',
  is_current: false,
  achievements: '',
  noAchievements: false,
});

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
const apexchart = VueApexCharts;

// Experience Data
const experienceEntries = ref(props.experienceEntries || []);
const isAddExperienceModalOpen = ref(false);
const isUpdateExperienceModalOpen = ref(false);
const experience = ref({
  graduate_experience_title: '',
  graduate_experience_company: '',
  graduate_experience_start_date: null,
  graduate_experience_end_date: null,
  graduate_experience_address: '',
  graduate_experience_description: '',
  graduate_experience_employment_type: '',
  is_current: false,
  id: null
});

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
    graduate_experience_title: '',
    graduate_experience_company: '',
    graduate_experience_start_date: null,
    graduate_experience_end_date: null,
    graduate_experience_address: '',
    graduate_experience_description: '',
    graduate_experience_employment_type: '',
    is_current: false,
    id: null
  };
  stillInRole.value = false;
  console.log('Experience reset.');
};

const validateExperience = () => {
  const errors = {};
  if (!experience.value.graduate_experience_title) errors.title = 'Title is required';
  if (!experience.value.graduate_experience_company) errors.company = 'Company is required';
  if (!experience.value.graduate_experience_start_date) errors.startDate = 'Start date is required';
  if (!experience.value.graduate_experience_employment_type) errors.employmentType = 'Employment type is required';
  
  // Handle end date validation
  if (!experience.value.is_current) {
    if (!experience.value.graduate_experience_end_date) {
      errors.endDate = 'End date is required when not current position';
    } else {
      const startDate = new Date(experience.value.graduate_experience_start_date);
      const endDate = new Date(experience.value.graduate_experience_end_date);
      
      if (isNaN(endDate.getTime())) {
        errors.endDate = 'Please enter a valid end date';
      } else if (startDate && !isNaN(startDate.getTime()) && endDate < startDate) {
        errors.endDate = 'End date cannot be earlier than start date';
      }
    }
  }
  
  // Validate description length
  if (experience.value.graduate_experience_description && experience.value.graduate_experience_description.length > 1000) {
    errors.description = 'Description cannot exceed 1000 characters';
  }
  
  return Object.keys(errors).length === 0 ? null : errors;
};

const openUpdateExperienceModal = (experienceEntry) => {
  resetExperience();
  experience.value = {
    id: experienceEntry.id,
    graduate_experience_title: experienceEntry.graduate_experience_title,
    graduate_experience_company: experienceEntry.graduate_experience_company,
    graduate_experience_start_date: experienceEntry.graduate_experience_start_date ? formatDate(experienceEntry.graduate_experience_start_date) : null,
    graduate_experience_end_date: experienceEntry.is_current ? null : (experienceEntry.graduate_experience_end_date ? formatDate(experienceEntry.graduate_experience_end_date) : null),
    graduate_experience_address: experienceEntry.graduate_experience_address || '',
    graduate_experience_description: experienceEntry.graduate_experience_description || 'No description provided',
    graduate_experience_employment_type: experienceEntry.graduate_experience_employment_type || '',
    is_current: experienceEntry.is_current
  };
  stillInRole.value = experienceEntry.is_current;
  isUpdateExperienceModalOpen.value = true;
};

const addExperience = () => {
  const errors = validateExperience();
  if (errors) {
    console.error('Validation errors:', errors);
    return;
  }

  const experienceData = {
    graduate_experience_title: experience.value.graduate_experience_title.trim(),
    graduate_experience_company: experience.value.graduate_experience_company.trim(),
    graduate_experience_start_date: formatDate(experience.value.graduate_experience_start_date),
    graduate_experience_end_date: experience.value.is_current ? null : formatDate(experience.value.graduate_experience_end_date),
    graduate_experience_address: experience.value.graduate_experience_address?.trim() || '',
    graduate_experience_description: experience.value.graduate_experience_description?.trim() || 'No description provided',
    graduate_experience_employment_type: experience.value.graduate_experience_employment_type.trim(),
    is_current: experience.value.is_current
  };

  const experienceForm = useForm(experienceData);

  experienceForm.post(route('experience.add'), {
    onSuccess: (response) => {
      const updatedExperiences = response?.props?.experienceEntries;
      if (updatedExperiences) {
        experienceEntries.value = updatedExperiences;
      }
      resetExperience();
      isAddExperienceModalOpen.value = false;
    },
    onError: (errors) => {
      console.error('Error adding experience:', errors);
      alert('An error occurred while adding the experience. Please try again.');
    },
  });
};

const updateExperience = () => {
  const errors = validateExperience();
  if (errors) {
    console.error('Validation errors:', errors);
    return;
  }

  const experienceData = {
    id: experience.value.id,
    graduate_experience_title: experience.value.graduate_experience_title,
    graduate_experience_company: experience.value.graduate_experience_company,
    graduate_experience_start_date: formatDate(experience.value.graduate_experience_start_date),
    graduate_experience_end_date: experience.value.is_current ? null : formatDate(experience.value.graduate_experience_end_date),
    graduate_experience_address: experience.value.graduate_experience_address || '',
    graduate_experience_description: experience.value.graduate_experience_description || 'No description provided',
    graduate_experience_employment_type: experience.value.graduate_experience_employment_type || '',
    is_current: experience.value.is_current
  };

  const experienceForm = useForm(experienceData);

  experienceForm.put(route('experience.update', experience.value.id), {
    onSuccess: () => {
      const index = experienceEntries.value.findIndex(e => e.id === experience.value.id);
      if (index !== -1) {
        experienceEntries.value[index] = { ...experienceData };
      }
      resetExperience();
      isUpdateExperienceModalOpen.value = false;
    },
    onError: (errors) => {
      console.error('Error updating experience:', errors);
    },
  });
};

const closeUpdateExperienceModal = () => {
  isUpdateExperienceModalOpen.value = false;
  resetExperience();
};

const deleteExperience = (experienceId) => {
  itemToDelete.value = { id: experienceId, type: 'experience' };
  isDeleteConfirmationModalOpen.value = true;
};

const formattedExperienceStartDate = computed(() => {
  return experience.value.graduate_experience_start_date
    ? new Date(experience.value.graduate_experience_start_date).toISOString().split('T')[0]
    : null;
});

const formattedExperienceEndDate = computed(() => {
  if (experience.value.is_current) return null;
  return experience.value.graduate_experience_end_date
    ? new Date(experience.value.graduate_experience_end_date).toISOString().split('T')[0]
    : null;
});

watch(() => experience.value.is_current, (newValue) => {
  stillInRole.value = newValue;
  if (newValue) {
    experience.value.graduate_experience_end_date = null;
  } else {
    experience.value.graduate_experience_end_date = '';
  }
}, { immediate: true });

watch(() => experience.value.graduate_experience_end_date, (newValue) => {
  if (newValue && isNaN(new Date(newValue).getTime())) {
    experience.value.graduate_experience_end_date = null;
  }
});

// Project Data
const projectsEntries = ref(props.projectsEntries || []);
const projects = ref({
  graduate_projects_title: '',
  graduate_projects_description: '',
  graduate_projects_role: '',
  graduate_projects_start_date: null,
  graduate_projects_end_date: null,
  graduate_projects_url: '',
  graduate_projects_key_accomplishments: '',
  is_current: false,
  id: null
});

const ongoingProject = ref(false);

watch(() => ongoingProject.value, (newValue) => {
  projects.value.is_current = newValue;
  if (newValue) {
    projects.value.graduate_projects_end_date = null;
  } else if (!projects.value.graduate_projects_end_date) {
    projects.value.graduate_projects_end_date = '';
  }
}, { immediate: true });

const validateProject = () => {
  const errors = {};
  if (!projects.value.graduate_projects_title) errors.title = 'Project title is required';
  if (!projects.value.graduate_projects_role) errors.role = 'Project role is required';
  if (!projects.value.graduate_projects_start_date) errors.startDate = 'Start date is required';
  if (!projects.value.is_current && !projects.value.graduate_projects_end_date) {
    errors.endDate = 'End date is required when not current project';
  }
  if (projects.value.graduate_projects_start_date && projects.value.graduate_projects_end_date && !projects.value.is_current) {
    const startDate = new Date(projects.value.graduate_projects_start_date);
    const endDate = new Date(projects.value.graduate_projects_end_date);
    if (endDate < startDate) {
      errors.endDate = 'End date cannot be earlier than start date';
    }
  }
  return Object.keys(errors).length === 0 ? null : errors;
};

watch(() => projects.value.is_current, (newValue) => {
  ongoingProject.value = newValue;
  if (newValue) {
    projects.value.graduate_projects_end_date = null;
  } else {
    projects.value.graduate_projects_end_date = '';
  }
}, { immediate: true });

watch(() => projects.value.graduate_projects_start_date, (newValue) => {
  if (newValue && isNaN(new Date(newValue).getTime())) {
    projects.value.graduate_projects_start_date = null;
  }
});

watch(() => projects.value.graduate_projects_end_date, (newValue) => {
  if (newValue && isNaN(new Date(newValue).getTime())) {
    projects.value.graduate_projects_end_date = null;
  }
});

const resetProject = () => {
  projects.value = {
    graduate_projects_title: '',
    graduate_projects_description: '',
    graduate_projects_role: '',
    graduate_projects_start_date: null,
    graduate_projects_end_date: null,
    graduate_projects_url: '',
    graduate_projects_key_accomplishments: '',
    is_current: false,
    id: null
  };
  noProjectUrl.value = false;
  ongoingProject.value = false;
  console.log('Project reset.');
};

const deleteProject = (projectId) => {
  itemToDelete.value = { id: projectId, type: 'project' };
  isDeleteConfirmationModalOpen.value = true;
};

const confirmDelete = () => {
  if (!itemToDelete.value) return;

  if (itemToDelete.value.type === 'project') {
    router.delete(route('projects.remove', itemToDelete.value.id), {
      onSuccess: () => {
        const index = projectsEntries.value.findIndex(project => project.id === itemToDelete.value.id);
        if (index !== -1) {
          projectsEntries.value.splice(index, 1);
        }
        isDeleteConfirmationModalOpen.value = false;
        itemToDelete.value = null;
      },
      onError: (errors) => {
        console.error('Error deleting project:', errors);
      },
    });
  } else if (itemToDelete.value.type === 'experience') {
    router.delete(route('experience.remove', itemToDelete.value.id), {
      onSuccess: () => {
        const index = experienceEntries.value.findIndex(exp => exp.id === itemToDelete.value.id);
        if (index !== -1) {
          experienceEntries.value.splice(index, 1);
        }
        isDeleteConfirmationModalOpen.value = false;
        itemToDelete.value = null;
      },
      onError: (errors) => {
        console.error('Error deleting experience:', errors);
      },
    });
  }
};

const openUpdateProjectModal = (projectEntry) => {
  // Reset form state before updating
  resetProject();

  // Update project values with properly formatted dates
  projects.value = {
    id: projectEntry.id,
    graduate_projects_title: projectEntry.graduate_projects_title,
    graduate_projects_description: projectEntry.graduate_projects_description || 'No description provided',
    graduate_projects_role: projectEntry.graduate_projects_role,
    graduate_projects_start_date: projectEntry.graduate_projects_start_date ? new Date(projectEntry.graduate_projects_start_date) : null,
    graduate_projects_end_date: projectEntry.is_current ? null : (projectEntry.graduate_projects_end_date ? new Date(projectEntry.graduate_projects_end_date) : null),
    graduate_projects_url: projectEntry.graduate_projects_url || '',
    graduate_projects_key_accomplishments: projectEntry.graduate_projects_key_accomplishments || 'No key accomplishment provided',
    is_current: projectEntry.is_current
  };

  // Set ongoing project status and URL flag
  ongoingProject.value = projectEntry.is_current;
  noProjectUrl.value = !projectEntry.graduate_projects_url;

  // Open the modal
  isUpdateProjectModalOpen.value = true;
};

const updateProject = () => {
  const errors = validateProject();
  if (errors) {
    console.error('Validation errors:', errors);
    return;
  }

  const projectData = {
    id: projects.value.id,
    graduate_projects_title: projects.value.graduate_projects_title,
    graduate_projects_description: projects.value.graduate_projects_description || 'No description provided',
    graduate_projects_role: projects.value.graduate_projects_role,
    graduate_projects_start_date: formatDate(projects.value.graduate_projects_start_date),
    graduate_projects_end_date: projects.value.is_current ? null : formatDate(projects.value.graduate_projects_end_date),
    graduate_projects_url: noProjectUrl.value ? '' : projects.value.graduate_projects_url,
    graduate_projects_key_accomplishments: projects.value.graduate_projects_key_accomplishments,
    is_current: projects.value.is_current
  };

  const projectForm = useForm(projectData);

  projectForm.put(route('projects.update', projects.value.id), {
    onSuccess: () => {
      const index = projectsEntries.value.findIndex(p => p.id === projects.value.id);
      if (index !== -1) {
        projectsEntries.value[index] = { ...projectData };
      }
      resetProject();
      isUpdateProjectModalOpen.value = false;
    },
    onError: (errors) => {
      console.error('Error updating project:', errors);
    },
  });
};

const closeUpdateProjectModal = () => {
  isUpdateProjectModalOpen.value = false;
  resetProject();
};
const noProjectUrl = ref(false);

const isAddProjectModalOpen = ref(false);

const addProject = () => {
  const errors = validateProject();
  if (errors) {
    console.error('Validation errors:', errors);
    return;
  }

  const projectData = {
    graduate_projects_title: projects.value.graduate_projects_title,
    graduate_projects_description: projects.value.graduate_projects_description || 'No description provided',
    graduate_projects_role: projects.value.graduate_projects_role,
    graduate_projects_start_date: formatDate(projects.value.graduate_projects_start_date),
    graduate_projects_end_date: projects.value.is_current ? null : formatDate(projects.value.graduate_projects_end_date),
    graduate_projects_url: noProjectUrl.value ? '' : projects.value.graduate_projects_url,
    graduate_projects_key_accomplishments: projects.value.graduate_projects_key_accomplishments || 'No key accomplishments provided',
    is_current: projects.value.is_current
  };

  const projectForm = useForm(projectData);

  projectForm.post(route('projects.add'), {
    onSuccess: (response) => {
      // Refresh the projects list from the updated page props
      const updatedProjects = response?.props?.projectsEntries;
      if (updatedProjects) {
        projectsEntries.value = updatedProjects;
      }
      resetProject();
      isAddProjectModalOpen.value = false;
    },
    onError: (errors) => {
      console.error('Error adding project:', errors);
    },
  });
};

console.log('yawa', props.projectsEntries);

// Certification Data
const certificationsEntries = ref(props.certificationsEntries || []);
const form = useForm({
  graduate_certification_name: '',
  graduate_certification_issuer: '',
  graduate_certification_issue_date: null,
  graduate_certification_expiry_date: null,
  graduate_certification_credential_url: '',
  avatar: null,
  noExpiryDate: false,
  noCredentialUrl: false,
});

const certificationEntries = ref([]);
const certification = ref({
  graduate_certification_name: '',
  graduate_certification_issuer: '',
  graduate_certification_issue_date: null,
  graduate_certification_expiry_date: null,
  graduate_certification_credential_id: '',
  graduate_certification_credential_url: ''
});



// Achievement Data
const achievementEntries = ref(props.achievementEntries || []);
const previewImage = ref(null);
const isSubmittingAchievement = ref(false);
const achievementErrors = ref({});
const achievementForm = ref({
  graduate_achievement_title: '',
  graduate_achievement_type: '',
  graduate_achievement_issuer: '',
  graduate_achievement_date: null,
  graduate_achievement_description: '',
  graduate_achievement_url: '',
  noCredentialUrl: false,
  credential_picture: null,
  credential_picture_url: null
});

const handleFileUpload = (event, type) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validate file type and size
  const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
  const maxSize = 2 * 1024 * 1024; // 2MB

  if (!allowedTypes.includes(file.type)) {
    alert('Please upload only JPG, JPEG or PNG files');
    return;
  }

  if (file.size > maxSize) {
    alert('File size should not exceed 2MB');
    return;
  }

  // Create preview URL
  const reader = new FileReader();
  reader.onload = (e) => {
    if (type === 'achievement') {
      achievementForm.value.credential_picture = file;
      previewImage.value = e.target.result;
    } else if (type === 'testimonial') {
      testimonials.value.graduate_testimonials_letters = file;
      testimonialsPreview.value = e.target.result;
    }
  };
  reader.readAsDataURL(file);
};

const testimonialsPreview = ref(null);


const change = (e) => {
  console.log('File selected:', e.target.files[0]);
};

const noCredentialUrl = ref(false);

const toggleUrlField = () => {
  if (noCredentialUrl.value) {
    achievementForm.value.graduate_achievement_url = '';
  }
};

// Tedtimonials Data
const testimonialsEntries = ref(props.testimonialsEntries || []);
const testimonials = ref({
  graduate_testimonials_name: '',
  graduate_testimonials_role_title: '',
  graduate_testimonials_testimonial: '',
  graduate_testimonials_letters: null,
});

// Employment Data
const employmentPreferences = ref(props.employmentPreferencesEntries || {
  jobTypes: [],
  salaryExpectations: {
    range: 'Select expected salary range',
    frequency: 'per year'
  },
  preferredLocations: [],
  workEnvironment: [],
  availability: [],
  additionalNotes: ''
});

const fetchEmploymentPreferences = async () => {
  try {
    const response = await axios.get(route('employment.references.get'));
    if (response.data) {
      employmentPreferences.value = {
        jobTypes: response.data.jobTypes ? response.data.jobTypes.split(', ') : [],
        salaryExpectations: response.data.salaryExpectations ? JSON.parse(response.data.salaryExpectations) : { // Parse the JSON string
          range: 'Select expected salary range',
          frequency: 'per year'
        },
        preferredLocations: response.data.preferredLocations ? response.data.preferredLocations.split(', ') : [],
        workEnvironment: response.data.workEnvironment ? response.data.workEnvironment.split(', ') : [],
        availability: response.data.availability ? response.data.availability.split(', ') : [],
        additionalNotes: response.data.additionalNotes || ''
      };
    }
  } catch (error) {
    console.error('Error fetching employment preferences:', error);
  }
};

onMounted(() => {
  fetchEmploymentPreferences();
});

// Career Goals Data
const newIndustry = ref('');
const careerGoalsEntries = ref(props.careerGoalsEntries || []);

const careerGoals = ref({
  shortTermGoals: '',
  longTermGoals: '',
  industriesOfInterest: [],
  careerPath: ''
});

const fetchCareerGoals = async () => {
  try {
    const response = await axios.get(route('career.goals.get'));
    if (response.data) {
      careerGoals.value = {
        shortTermGoals: response.data.shortTermGoals || '',
        longTermGoals: response.data.longTermGoals || '',
        industriesOfInterest: response.data.industriesOfInterest ? response.data.industriesOfInterest.split(',') : [],
        careerPath: response.data.careerPath || ''
      };
    }
  } catch (error) {
    console.error('Error fetching career goals:', error);
  }
};

onMounted(() => {
  fetchCareerGoals();
});


// Resume Data
const resumeEntries = ref(props.resumeEntries || []);
const resume = ref({
  file: null,
  fileName: ''
});

// Modal State
const isProfileModalOpen = ref(false);
const isPasswordModalOpen = ref(false);
const isAddEducationModalOpen = ref(false);
const isUpdateEducationModalOpen = ref(false);
const isAddSkillModalOpen = ref(false);
const isSkillsAddedModalOpen = ref(false);
let currentExperienceIndex = ref(null);
const isAddCertificationModalOpen = ref(false);
const isAddAchievementModalOpen = ref(false);
const isUpdateAchievementModalOpen = ref(false);
const isAddIndustryModalOpen = ref(false);
let currentAchievementIndex = ref(null);
const isUpdateCertificationModalOpen = ref(false);
const isAddTestimonialsModalOpen = ref(false);
const isUpdateTestimonialsModalOpen = ref(false);
let currentTestimonialsIndex = ref(null);
const isAddLocationModalOpen = ref(false);
const isUpdateProjectModalOpen = ref(false);

// Watchers
watch(() => profile.value.fullName, (newFullName) => {
  const nameParts = newFullName.trim().split(' ');
  profile.value.graduate_first_name = nameParts[0] || '';
  profile.value.graduate_last_name = nameParts[nameParts.length - 1] || '';
  profile.value.graduate_middle_initial = nameParts.length > 2 ? nameParts[1].charAt(0) : '';
});

watch(() => form.noExpiryDate, (newValue) => {
    if (newValue) {
        form.graduate_certification_expiry_date = null;
    }
});

watch(() => education.value.graduate_education_start_date, (newValue) => {
  if (newValue && isNaN(new Date(newValue).getTime())) {
    education.value.graduate_education_start_date = null;
  }
});

watch(() => education.value.is_current, (newValue) => {
  if (newValue) {
    education.value.graduate_education_end_date = null;
  } else {
    education.value.graduate_education_end_date = '';
  }
});

watch(() => education.value.graduate_education_end_date, (newValue) => {
  if (newValue && isNaN(new Date(newValue).getTime())) {
    education.value.graduate_education_end_date = null;
  }
});

watch(() => profile.value.fullName, () => {
  parseFullName();
});

watch(noProjectUrl, (newValue) => {
  if (newValue) {
    projects.value.graduate_projects_url = 'No project URL provided';
  } else {
    projects.value.graduate_projects_url = '';
  }
});

watch(() => projects.value.is_current, (newValue) => {
  if (newValue) {
    projects.value.graduate_projects_end_date = null;
  } else {
    projects.value.graduate_projects_end_date = '';
  }
});

watch(() => form.noCredentialUrl, (newValue) => {
    if (newValue) {
        form.graduate_certification_credential_url = '';
    }
});

watch(() => achievementForm.noCredentialUrl, (newValue) => {
  if (newValue) {
    achievementForm.graduate_achievement_url = '';
  }
});



const handleSaveCareerGoals = () => {
  // Your save logic here
  router.post('/graduate/career-goals', careerGoals.value, {
    onSuccess: () => {
      savedCareerGoals.value = { ...careerGoals.value };
    },
  });
};

// Education Handlers
const addEducation = () => {
  if (
    !education.value.graduate_education_institution_id ||
    !education.value.graduate_education_program ||
    !education.value.graduate_education_field_of_study
  ) {
    alert('Please fill in all required fields.');
    return;
  }

  // Check for duplicates
  const isDuplicate = educationEntries.value.some(entry => 
    entry.graduate_education_institution_id === education.value.graduate_education_institution_id &&
    entry.graduate_education_program === education.value.graduate_education_program &&
    entry.graduate_education_field_of_study === education.value.graduate_education_field_of_study
  );

  if (isDuplicate) {
    alert('This education entry already exists.');
    return;
  }

  const educationForm = useForm({
    graduate_education_institution_id: education.value.graduate_education_institution_id,
    graduate_education_program: education.value.graduate_education_program,
    graduate_education_field_of_study: education.value.graduate_education_field_of_study,
    graduate_education_start_date: education.value.graduate_education_start_date,
    graduate_education_end_date: education.value.is_current ? null : education.value.graduate_education_end_date,
    graduate_education_description: education.value.graduate_education_description,
    is_current: education.value.is_current,
    achievements: education.value.noAchievements ? null : education.value.achievements,
  });

  console.log('Data being sent to backend for adding education:', educationForm.data());

  educationForm.post(route('education.add'), {
    onSuccess: (response) => {
      console.log('Education added successfully:', response);
      educationEntries.value.push({ ...educationForm.data(), id: response.id });
      resetEducation();
      isAddEducationModalOpen.value = false;
    },
    onError: (errors) => {
      if (errors.duplicate) {
        alert('This education entry already exists.');
      } else {
        alert('An error occurred while adding education. Please try again.');
      }
    },
  });
};

const updateEducation = () => {
  if (!education.value.id) {
    console.error('Education ID is required for update');
    return;
  }

  const formData = {
    graduate_education_institution_id: education.value.graduate_education_institution_id,
    graduate_education_program: education.value.graduate_education_program,
    graduate_education_field_of_study: education.value.graduate_education_field_of_study,
    graduate_education_start_date: formatDate(education.value.graduate_education_start_date),
    graduate_education_end_date: education.value.is_current ? null : formatDate(education.value.graduate_education_end_date),
    graduate_education_description: education.value.graduate_education_description,
    is_current: education.value.is_current,
    achievements: education.value.achievements,
    no_achievements: education.value.noAchievements,
  };

  router.put(route('education.update', { id: education.value.id }), formData, {
    onSuccess: () => {
      const index = educationEntries.value.findIndex(entry => entry.id === education.value.id);
      if (index !== -1) {
        educationEntries.value[index] = { ...formData, id: education.value.id };
      }
      closeUpdateEducationModal();
      isEducationUpdatedModalOpen.value = true;
    },
    onError: (error) => {
      if (error.response?.data?.message?.includes('duplicate')) {
        alert('This education entry already exists.');
      } else {
        alert('An error occurred while updating the education entry. Please try again.');
      }
    },
  });
};

const handleIsCurrent = () => {
  if (education.value.is_current) {
    education.value.graduate_education_end_date = null;
  } else {
    education.value.graduate_education_end_date = '';
  }
};

const handleNoAchievements = () => {
  if (education.value.noAchievements) {
    education.value.achievements = '';
  }
};

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

  skillForm.post(route('skills.add'), {
    onSuccess: (response) => {
      skills.value.push({ ...skillForm.data(), id: response.id });
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

  skillForm.put(route('skills.update', { id: skillId }), {
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

// Project HandlersS
const closeAddProjectModal = () => {
  resetProject();
  isAddProjectModalOpen.value = false;
};



// Certification Handlers
const addCertification = () => {
  if (
    !form.graduate_certification_name ||
    !form.graduate_certification_issuer ||
    !form.graduate_certification_issue_date
  ) {
    alert("Please fill in all required fields.");
    return;
  }

  const certificationData = {
    graduate_certification_name: form.graduate_certification_name,
    graduate_certification_issuer: form.graduate_certification_issuer,
    graduate_certification_issue_date: form.graduate_certification_issue_date,
    graduate_certification_expiry_date: form.noExpiryDate ? null : form.graduate_certification_expiry_date,
    graduate_certification_credential_url: form.noCredentialUrl ? 'No Credential URL' : form.graduate_certification_credential_url,
    file: form.avatar,
  };

  const certificationForm = useForm(certificationData);

  certificationForm.post(route('certifications.add'), {
    onSuccess: (response) => {
      certificationsEntries.value.push({ ...certificationForm.data(), id: response.id });
      resetForm();
      isAddCertificationModalOpen.value = false;
    },
    onError: (errors) => {
      console.error('Error adding certification:', errors);
      alert('An error occurred while adding the certification. Please try again.');
    },
  });
};

const updateCertification = () => {
  if (
    !form.graduate_certification_name ||
    !form.graduate_certification_issuer ||
    !form.graduate_certification_issue_date
  ) {
    alert("Please fill in all required fields.");
    return;
  }

  const certificationData = {
    graduate_certification_name: form.graduate_certification_name,
    graduate_certification_issuer: form.graduate_certification_issuer,
    graduate_certification_issue_date: form.graduate_certification_issue_date,
    graduate_certification_expiry_date: form.noExpiryDate ? null : form.graduate_certification_expiry_date,
    graduate_certification_credential_url: form.noCredentialUrl ? 'No Credential URL' : form.graduate_certification_credential_url,
    file: form.avatar,
  };

  const certificationForm = useForm(certificationData);

  certificationForm.put(route('certifications.update', { id: form.id }), {
    onSuccess: (response) => {
      const index = certificationsEntries.value.findIndex(entry => entry.id === form.id);
      if (index !== -1) {
        certificationsEntries.value[index] = { ...certificationForm.data(), id: form.id };
      }
      resetForm();
      isUpdateCertificationModalOpen.value = false;
    },
    onError: (errors) => {
      console.error('Error updating certification:', errors);
      alert('An error occurred while updating the certification. Please try again.');
    },
  });
};

const closeUpdateCertificationModal = () => {
  isUpdateCertificationModalOpen.value = false;
  resetForm();
};

const resetForm = () => {
    form.reset();
    form.noExpiryDate = false;
    const addAchievement = async () => {
  try {
    isSubmittingAchievement.value = true;
    achievementErrors.value = {};

    if (!achievementForm.value.graduate_achievement_title || 
        !achievementForm.value.graduate_achievement_issuer || 
        !achievementForm.value.graduate_achievement_date) {
      alert("Please fill in all required fields.");
      return;
    }

    const formData = new FormData();
    formData.append('graduate_achievement_title', achievementForm.value.graduate_achievement_title);
    formData.append('graduate_achievement_issuer', achievementForm.value.graduate_achievement_issuer);
    formData.append('graduate_achievement_date', achievementForm.value.graduate_achievement_date);
    formData.append('graduate_achievement_description', 
      achievementForm.value.graduate_achievement_description?.trim() || 'No description provided'
    );
    formData.append('graduate_achievement_url', achievementForm.value.graduate_achievement_url);
    formData.append('graduate_achievement_type', achievementForm.value.graduate_achievement_type);
    
    if (achievementForm.value.credential_picture) {
      formData.append('credential_picture', achievementForm.value.credential_picture);
    }

    const form = useForm(Object.fromEntries(formData));
    const response = await form.post(route('achievements.add'));

    if (response.data) {
      achievementEntries.value.push({
        ...achievementForm.value,
        id: response.data.id,
        credential_picture_url: response.data.credential_picture_url,
        graduate_achievement_date: response.data.graduate_achievement_date
      });
      
      resetAchievementForm();
      previewImage.value = null;
      isAddAchievementModalOpen.value = false;
    }
  } catch (error) {
    console.error('Error adding achievement:', error);
    achievementErrors.value = error.response?.data?.errors || {};
  } finally {
    isSubmittingAchievement.value = false;
  }
};};

// Achievement Handlers
const addAchievement = async () => {
  try {
    isSubmittingAchievement.value = true;
    achievementErrors.value = {};

    if (!achievementForm.value.graduate_achievement_title || 
        !achievementForm.value.graduate_achievement_issuer || 
        !achievementForm.value.graduate_achievement_date) {
      alert("Please fill in all required fields.");
      return;
    }

    const formData = new FormData();
    formData.append('graduate_achievement_title', achievementForm.value.graduate_achievement_title);
    formData.append('graduate_achievement_issuer', achievementForm.value.graduate_achievement_issuer);
    
    // Format the date to match Carbon's expected format
    const date = new Date(achievementForm.value.graduate_achievement_date);
    const formattedDate = date.toISOString().split('T')[0];
    formData.append('graduate_achievement_date', formattedDate);
    
    formData.append('graduate_achievement_description', 
      achievementForm.value.graduate_achievement_description?.trim() || 'No description provided'
    );
    formData.append('graduate_achievement_url', achievementForm.value.graduate_achievement_url);
    formData.append('graduate_achievement_type', achievementForm.value.graduate_achievement_type);
    
    if (achievementForm.value.credential_picture) {
      formData.append('credential_picture', achievementForm.value.credential_picture);
    }

    const form = useForm(Object.fromEntries(formData));
    
    form.post(route('achievements.add'), {
      onSuccess: (response) => {
        achievementEntries.value.push({
          ...achievementForm.value,
          id: response.id,
          credential_picture_url: response.credential_picture_url,
          graduate_achievement_date: formattedDate
        });
        
        resetAchievementForm();
        previewImage.value = null;
        isAddAchievementModalOpen.value = false;
      },
      onError: (errors) => {
        console.error('Error adding achievement:', errors);
        achievementErrors.value = errors;
      }
    });
  } catch (error) {
    console.error('Error adding achievement:', error);
    achievementErrors.value = error.response?.data?.errors || {};
  } finally {
    isSubmittingAchievement.value = false;
  }
};


const resetAchievementForm = () => {
  achievementForm.value = {
    graduate_achievement_title: '',
      graduate_achievement_type: '',
      graduate_achievement_issuer: '',
      graduate_achievement_date: null,
      graduate_achievement_description: '',
      graduate_achievement_url: '',
      noCredentialUrl: false,
      credential_picture: null,
      credential_picture_url: null
  };
};

const validateAchievementForm = () => {
  const errors = {};
  if (!achievementForm.value.graduate_achievement_title?.trim()) {
    errors.title = 'Achievement title is required';
  }
  if (!achievementForm.value.graduate_achievement_issuer?.trim()) {
    errors.issuer = 'Achievement issuer is required';
  }
  if (!achievementForm.value.graduate_achievement_date) {
    errors.date = 'Achievement date is required';
  }
  if (!achievementForm.value.graduate_achievement_type?.trim()) {
    errors.type = 'Achievement type is required';
  }
  return Object.keys(errors).length === 0 ? null : errors;
};

const editAchievement = (achievement) => {
  achievementForm.value = {
    id: achievement.id,
    graduate_achievement_title: achievement.graduate_achievement_title,
    graduate_achievement_type: achievement.graduate_achievement_type,
    graduate_achievement_issuer: achievement.graduate_achievement_issuer,
    graduate_achievement_date: achievement.graduate_achievement_date ? new Date(achievement.graduate_achievement_date) : null,
    graduate_achievement_description: achievement.graduate_achievement_description || '',
    graduate_achievement_url: achievement.graduate_achievement_url || '',
    noCredentialUrl: !achievement.graduate_achievement_url,
    credential_picture: null,
    credential_picture_url: achievement.credential_picture_url
  };
  achievementErrors.value = {};
  isUpdateAchievementModalOpen.value = true;
};

// Update achievement handler
const updateAchievement = () => {
  const errors = validateAchievementForm();
  if (errors) {
    achievementErrors.value = errors;
    return;
  }

  isSubmittingAchievement.value = true;
  achievementErrors.value = {};

  const formData = new FormData();
  formData.append('graduate_achievement_title', achievementForm.value.graduate_achievement_title.trim());
  formData.append('graduate_achievement_issuer', achievementForm.value.graduate_achievement_issuer.trim());
  formData.append('graduate_achievement_date', formatDate(achievementForm.value.graduate_achievement_date));
  formData.append('graduate_achievement_description', 
    achievementForm.value.graduate_achievement_description?.trim() || 'No description provided'
  );
  formData.append('graduate_achievement_url', achievementForm.value.noCredentialUrl ? '' : (achievementForm.value.graduate_achievement_url || ''));
  formData.append('graduate_achievement_type', achievementForm.value.graduate_achievement_type.trim());
  
  if (achievementForm.value.credential_picture) {
    formData.append('credential_picture', achievementForm.value.credential_picture);
  }

  const form = useForm(Object.fromEntries(formData));

  form.put(route('achievements.update', achievementForm.value.id), {
    onSuccess: (response) => {
      const index = achievementEntries.value.findIndex(entry => entry.id === achievementForm.value.id);
      if (index !== -1) {
        achievementEntries.value[index] = {
          ...achievementForm.value,
          graduate_achievement_date: formatDate(achievementForm.value.graduate_achievement_date),
          credential_picture_url: response.credential_picture_url
        };
      }
      resetAchievementForm();
      previewImage.value = null;
      isSubmittingAchievement.value = false;
      isUpdateAchievementModalOpen.value = false;
    },
    onError: (errors) => {
      console.error('Error updating achievement:', errors);
      isSubmittingAchievement.value = false;
      if (errors.response?.data?.errors) {
        achievementErrors.value = errors.response.data.errors;
      } else {
        alert('An error occurred while updating the achievement. Please try again.');
      }
    },
  });
};

const deleteAchievement = async (id) => {
  if (confirm('Are you sure you want to delete this achievement?')) {
    try {
      await axios.delete(route('achievements.delete', id));
      achievementEntries.value = achievementEntries.value.filter(entry => entry.id !== id);
    } catch (error) {
      console.error('Error deleting achievement:', error);
      alert('An error occurred while deleting the achievement');
    }
  }
};''


// Testimonials Handlers
const addTestimonials = () => {
  if (
    testimonials.value.graduate_testimonials_name.trim() &&
    testimonials.value.graduate_testimonials_role_title.trim() &&
    testimonials.value.graduate_testimonials_testimonial.trim()
  ) {
    const testimonialsForm = useForm({
      graduate_testimonials_name: testimonials.value.graduate_testimonials_name,
      graduate_testimonials_role_title: testimonials.value.graduate_testimonials_role_title,
      graduate_testimonials_testimonial: testimonials.value.graduate_testimonials_testimonial
    });

    console.log('Data sent to backend for testimonial:', testimonialsForm.data());

    testimonialsForm.post(route('testimonials.add'), {
      onSuccess: (response) => {
        testimonialsEntries.value.push({ ...testimonialsForm.data(), id: response.id });
        resetTestimonials();
        isAddTestimonialsModalOpen.value = false;

        console.log('Testimonial added successfully:', response);
      },
      onError: (errors) => {
        console.error('Error adding testimonial:', errors);
        alert('An error occurred while adding the testimonial. Please try again.');
      },
    });
  } else {
    alert("Please fill in all required fields.");
  }
};

// Update testimonials handler
const updateTestimonials = () => {
  const index = currentTestimonialsIndex.value;

  if (
    testimonials.value.graduate_testimonials_name.trim() &&
    testimonials.value.graduate_testimonials_role_title.trim() &&
    testimonials.value.graduate_testimonials_testimonial.trim()
  ) {
    const updatedTestimonial = {
      id: testimonialsEntries.value[index].id,
      graduate_testimonials_name: testimonials.value.graduate_testimonials_name,
      graduate_testimonials_role_title: testimonials.value.graduate_testimonials_role_title,
      graduate_testimonials_testimonial: testimonials.value.graduate_testimonials_testimonial
    };
    testimonialsEntries.value[index] = updatedTestimonial;

    console.log('Testimonial updated:', updatedTestimonial);

    resetTestimonials();
    isUpdateTestimonialsModalOpen.value = false;
  } else {
    alert("Please fill in all required fields.");
  }
};

const handleTestimonialFileUpload = (event) => {
  const file = event.target.files[0];
}

const submitTestimonial = () => {
  const formData = new FormData();
  formData.append('graduate_testimonials_name', testimonials.value.graduate_testimonials_name);
  formData.append('graduate_testimonials_role_title', testimonials.value.graduate_testimonials_role_title);
  formData.append('graduate_testimonials_testimonial', testimonials.value.graduate_testimonials_testimonial);
  formData.append('graduate_testimonials_file', testimonials.value.graduate_testimonials_file);
  const form = useForm(Object.fromEntries(formData));
  form.post(route('testimonials.add'), {
    
  })
}

// Employment Preferences Handlers
const saveEmploymentPreferences = () => {
  console.log('Employment Preferences Data:', employmentPreferences.value); // Debugging

  if (
    !employmentPreferences.value.jobTypes.length &&
    !employmentPreferences.value.salaryExpectations.range &&
    !employmentPreferences.value.preferredLocations.length &&
    !employmentPreferences.value.workEnvironment.length &&
    !employmentPreferences.value.availability.length &&
    !employmentPreferences.value.additionalNotes.trim()
  ) {
    alert("Please fill in at least one employment preference.");
    return;
  }

  const employmentForm = useForm({
    jobTypes: employmentPreferences.value.jobTypes.join(', '),
    salaryExpectations: JSON.stringify(employmentPreferences.value.salaryExpectations),
    preferredLocations: employmentPreferences.value.preferredLocations.join(', '),
    workEnvironment: employmentPreferences.value.workEnvironment.join(', '),
    availability: employmentPreferences.value.availability.join(', '),
    additionalNotes: employmentPreferences.value.additionalNotes
});

  console.log('Data sent to backend for employment preferences:', employmentForm.data());

  employmentForm.post(route('employment.preferences.save'), {
    onSuccess: (response) => {
      console.log('Employment preferences saved successfully:', response);
    },
    onError: (errors) => {
      console.error('Error saving employment preferences:', errors);
      alert('An error occurred while saving the employment preferences. Please try again.');
    },
  });
};

const newLocation = ref('');

const addPreferredLocation = () => {
  if (!newLocation.value.trim()) {
    alert("Please enter a valid location.");
    return;
  }

  if (!employmentPreferences.value.preferredLocations.includes(newLocation.value.trim())) {
    employmentPreferences.value.preferredLocations.push(newLocation.value.trim());

    console.log('Is Add Location Modal Open:', isAddLocationModalOpen.value);

    console.log("Location added:", newLocation.value);

    alert("Location added successfully!");
  } else {
    alert("This location is already in your preferences.");
  }

  closeAddLocationModal();
};


// Career Goals Handlers
const savedCareerGoals = () => {
  console.log("Saving career goals:", careerGoals.value); // Debugging
  if (!careerGoals.value.shortTermGoals || !careerGoals.value.longTermGoals) {
    alert("Please fill in both short-term and long-term goals.");
    return;
  }

  const careerGoalsForm = useForm({
    shortTermGoals: careerGoals.value.shortTermGoals,
    longTermGoals: careerGoals.value.longTermGoals,
    industriesOfInterest: careerGoals.value.industriesOfInterest,
    careerPath: careerGoals.value.careerPath,
  });

  console.log("Data being sent to backend for career goals:", careerGoalsForm.data());

  careerGoalsForm.post(route('career.goals.save'), {
    onSuccess: (response) => {
      console.log("Backend response for career goals:", response);
      careerGoalsEntries.value.push({ ...careerGoalsForm.data(), id: response.id });
    },
    onError: (errors) => {
      console.error("Error saving career goals:", errors);
      alert("An error occurred while saving the career goals. Please try again.");
    },
  });
};

const closeAddIndustryModal = () => {
  isAddIndustryModalOpen.value = false;
  newIndustry.value = '';
};

const addPreferredIndustry = () => {
  if (!newIndustry.value.trim()) {
    alert("Please enter a valid industry name.");
    return;
  }

  if (!careerGoals.value.industriesOfInterest.includes(newIndustry.value.trim())) {
    careerGoals.value.industriesOfInterest.push(newIndustry.value.trim());

    console.log("New Industry Added:", newIndustry.value);
    console.log("Updated Industries of Interest:", careerGoals.value.industriesOfInterest);

    const careerGoalsForm = useForm({
      industriesOfInterest: careerGoals.value.industriesOfInterest,
    });

    console.log("Data being sent to backend:", careerGoalsForm.data());

    careerGoalsForm.post(route('career.goals.add.industry'), { 
      onSuccess: (response) => {
        console.log("Backend response:", response);
      },
      onError: (errors) => {
        console.error("Error saving industry:", errors);
        alert("An error occurred while saving the industry. Please try again.");
      },
    });
  } else {
    alert("This industry is already in your preferences.");
  }

  closeAddIndustryModal();
};

// Resume Handlers
const resumeForm = useForm({
  file: null,
  fileName: ''
});

const uploadResume = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  if (!['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
      .includes(file.type)) {
    alert('Please upload a PDF or Word document');
    return;
  }

  resumeForm.file = file;
  resumeForm.fileName = file.name;
  resume.value.file = file;
  resume.value.fileName = file.name;
};

const saveResume = () => {
  if (!resumeForm.file) {
    alert('Please upload a resume before saving.');
    return;
  }

  const formData = new FormData();
  formData.append('resume', resumeForm.file);
  formData.append('fileName', resumeForm.fileName);

  resumeForm.post(route('resume.upload'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      alert('Resume saved successfully!');
    },
    onError: (errors) => {
      console.error('Error saving resume:', errors);
      alert('An error occurred while saving the resume. Please try again.');
    },
  });
};

const removeResume = () => {
  resumeForm.delete(route('resume.delete'), {
    onSuccess: () => {
      resume.value.file = null;
      resume.value.fileName = '';
      resumeForm.reset();
      alert('Resume removed successfully');
    },
    onError: () => {
      alert('Failed to remove resume');
    },
  });
};

//  Reset Functions
const resetEducation = () => {
  education.value = {
    graduate_education_institution_id: '',
    graduate_education_program: '',
    graduate_education_field_of_study: '',
    graduate_education_start_date: null,
    graduate_education_end_date: null,
    graduate_education_description: '',
    is_current: false,
    achievements: '',
    noAchievements: false,
  };
  console.log('Education reset.');
};

const resetSkill = () => {
  skillName.value = '';
  skillProficiencyType.value = '';
  skillType.value = '';
  yearsExperience.value = 0;
  console.log('Skill reset.');
};

const resetCertification = () => {
  certification.value = {
    graduate_certification_name: '',
    graduate_certification_issuer: '',
    graduate_certification_issue_date: null,
    graduate_certification_expiry_date: null,
    graduate_certification_credential_id: '',
    graduate_certification_credential_url: ''
  };
  console.log('Certification reset.');
};

const resetAchievement = () => {
  achievement.value = {
    graduate_achievement_title: '',
    graduate_achievement_issuer: '',
    graduate_achievement_date: null,
    graduate_achievement_description: '',
    graduate_achievement_url: '',
    graduate_achievement_type: '',
    credential_picture: null
  };
  noCredentialUrl.value = false;
};

const resetTestimonials = () => {
  testimonials.value = {
    graduate_testimonials_name: '',
    graduate_testimonials_role_title: '',
    graduate_testimonials_testimonial: ''
  };
  console.log('Testimonials reset.');
};

const resetEmploymentPreferences = () => {
  employmentPreferences.value = {
    jobTypes: [],
    salaryExpectations: {
      range: 'Select expected salary range',
      frequency: 'per year'
    },
    preferredLocations: [],
    workEnvironment: [],
    availability: [],
    additionalNotes: ''
  };
  console.log('Employment preferences reset.');
};

const resetCareerGoals = () => {
  careerGoals.value = {
    shortTermGoals: '',
    longTermGoals: '',
    industriesOfInterest: [],
    careerPath: ''
  };
  console.log('Career goals reset.');
};

const resetResume = () => {
  resume.value = {
    file: null,
    fileName: ''
  };
  console.log('Resume reset.');
};


// Modal Control Functions
const closeProfileModal = () => {
  isProfileModalOpen.value = false;
  console.log('Profile modal closed.');
};

const closeNoChangesModal = () => {
  isNoChangesModalOpen.value = false;
  console.log('No changes modal closed.');
};

const closePasswordModal = () => {
  isPasswordModalOpen.value = false;
  console.log('Password modal closed.');
};

const closeAddEducationModal = () => {
  isAddEducationModalOpen.value = false;
  resetEducation();
  console.log('Add education modal closed.');
};

const closeUpdateEducationModal = () => {
  isUpdateEducationModalOpen.value = false;
  education.value = {
    graduate_education_institution_id: '',
    graduate_education_program: '',
    graduate_education_field_of_study: '',
    graduate_education_start_date: null,
    graduate_education_end_date: null,
    graduate_education_description: ''
  };
  console.log('Update education modal closed.');
  resetEducation();
};

const closeEducationAddedModal = () => {
  isEducationAddedModalOpen.value = false;
  console.log('Education added modal closed.');
};

const closeEducationUpdatedModal = () => {
  isEducationUpdatedModalOpen.value = false;
  console.log('Education updated modal closed.');
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

const closeAddCertificationModal = () => {
  isAddCertificationModalOpen.value = false;
  resetCertification();
  noExpiryDate.value = false;
  console.log('Add certification modal closed.');
};

const closeAddAchievementModal = () => {
  isAddAchievementModalOpen.value = false;
  achievementForm.reset();
  console.log('Add achievement modal closed.');
};

const closeUpdateAchievementModal = () => {
  isUpdateAchievementModalOpen.value = false;
  resetAchievement();
  console.log('Update achievement modal closed.');
};

const closeAddTestimonialsModal = () => {
  isAddTestimonialsModalOpen.value = false;
  resetTestimonials();
  console.log('Add testimonials modal closed.');
};

const closeUpdateTestimonialsModal = () => {
  isUpdateTestimonialsModalOpen.value = false;
  resetTestimonials();
  console.log('Update testimonials modal closed.');
};

const closeAddLocationModal = () => {
  isAddLocationModalOpen.value = false;
  newLocation.value = '';
  console.log('Add location modal closed.');
};



//  Grouping and Filtering Functions
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

const toggleJobType = (type) => {
  const index = employmentPreferences.value.jobTypes.indexOf(type);
  if (index === -1) {
    console.log('Job type added:', type);
    employmentPreferences.value.jobTypes.push(type);
  } else {
    employmentPreferences.value.jobTypes.splice(index, 1);
    console.log('Job type removed:', type);
  }
};

// Modal Opening Functions
const openAddSkillModal = () => {
  isAddSkillModalOpen.value = true;
  console.log('Add skill modal opened.');
};

const openAddIndustryModal = () => {
  isAddIndustryModalOpen.value = true;
  console.log('Add industry modal opened.');
};

const openUpdateEducationModal = (entry) => {
  education.value = {
    id: entry.id,
    graduate_education_institution_id: entry.graduate_education_institution_id,
    graduate_education_program: entry.graduate_education_program,
    graduate_education_field_of_study: entry.graduate_education_field_of_study,
    graduate_education_start_date: new Date(entry.graduate_education_start_date),
    graduate_education_end_date: entry.graduate_education_end_date ? new Date(entry.graduate_education_end_date) : null,
    graduate_education_description: entry.graduate_education_description || '',
    is_current: !entry.graduate_education_end_date,
    achievements: entry.achievements || '',
    noAchievements: !entry.achievements
  };
  isUpdateEducationModalOpen.value = true;
};

const openUpdateAchievementModal = (entry, index) => {
  achievement.value = { ...entry };
  currentAchievementIndex.value = index;
  isUpdateAchievementModalOpen.value = true;
  console.log('Update achievement modal opened with entry:', entry);
};

const openUpdateCertificationModal = (entry) => {
  // Populate the form with the selected entry data
  form.id = entry.id;
  form.graduate_certification_name = entry.graduate_certification_name;
  form.graduate_certification_issuer = entry.graduate_certification_issuer;
  form.graduate_certification_issue_date = entry.graduate_certification_issue_date;
  form.graduate_certification_expiry_date = entry.graduate_certification_expiry_date;
  form.graduate_certification_credential_url = entry.graduate_certification_credential_url;
  form.avatar = null; 
  form.noExpiryDate = !entry.graduate_certification_expiry_date;
  form.noCredentialUrl = !entry.graduate_certification_credential_url;
  isUpdateCertificationModalOpen.value = true;
};

const openUpdateTestimonialsModal = (entry, index) => {
  testimonials.value = { ...entry };
  currentTestimonialsIndex.value = index;
  isUpdateTestimonialsModalOpen.value = true;
  console.log('Update testimonials modal opened with entry:', entry);
};

//  Confirmation and Removal Functions
const deleteForm = useForm({});

const removeEducation = (education) => {
  if (confirm(`Are you sure you want to remove this education entry: ${education.graduate_education_program}?`)) {
    deleteForm.delete(route('education.remove', education.id), {
      onSuccess: () => {
        const index = educationEntries.value.findIndex(e => e.id === education.id);
        if (index !== -1) {
          educationEntries.value.splice(index, 1);
        }
      }
    });
  }
};

const removeExperience = (experience) => {
  if (confirm(`Are you sure you want to remove this experience entry: ${experience.graduate_experience_title}?`)) {
    deleteForm.delete(route('experience.remove', experience.id), {
      onSuccess: () => {
        const index = experienceEntries.value.findIndex(e => e.id === experience.id);
        if (index !== -1) {
          experienceEntries.value.splice(index, 1);
        }
      }
    });
  }
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

const removeTestimonials = (testimonial) => {
  if (confirm(`Are you sure you want to remove this testimonial from ${testimonial.graduate_testimonials_name}?`)) {
    const index = testimonialsEntries.value.findIndex(t => t.id === testimonial.id);
    if (index !== -1) {
      testimonialsEntries.value.splice(index, 1);
      console.log(`Testimonial from "${testimonial.graduate_testimonials_name}" removed from view.`);
    }
  }
};

const removeCertification = (certification) => {
  if (confirm(`Are you sure you want to remove this certification: ${certification.graduate_certification_name}?`)) {
    deleteForm.delete(route('certification.remove', certification.id), {
      onSuccess: () => {
        const index = certificationsEntries.value.findIndex(c => c.id === certification.id);
        if (index !== -1) {
          certificationsEntries.value.splice(index, 1);
        }
      }
    });
  }
};

const removeAchievement = (achievement) => {
  if (confirm(`Are you sure you want to remove this achievement: ${achievement.graduate_achievement_title}?`)) {
    const index = achievementEntries.value.findIndex(a => a.id === achievement.id);
    if (index !== -1) {
      achievementEntries.value.splice(index, 1);
      console.log(`Achievement "${achievement.graduate_achievement_title}" removed from view.`);
    }
  }
};

const removeProject = (project) => {
  if (!project || !project.graduate_projects_title) {
    console.error('Invalid project data');
    return;
  }
  if (confirm(`Are you sure you want to remove this project: ${project.graduate_projects_title}?`)) {
    deleteForm.delete(route('project.delete', project.id), {
      onSuccess: () => {
        const index = projectsEntries.value.findIndex(p => p.id === project.id);
        if (index !== -1) {
          projectsEntries.value.splice(index, 1);
          console.log(`Project "${project.graduate_projects_title}" removed successfully.`);
        }
      },
      onError: (error) => {
        console.error('Error removing project:', error);
      }
    });
  }
};

const closeAllModals = () => {
  closeProfileModal();
  closeNoChangesModal();
  closePasswordModal();
  closeAddEducationModal();
  closeUpdateEducationModal();
  closeSkillsAddedModal();
  closeAddExperienceModal();
  closeUpdateExperienceModal();
  closeAddCertificationModal();
  closeAddAchievementModal();
  closeUpdateAchievementModal();
  closeAddTestimonialsModal();
  closeUpdateTestimonialsModal();
  closeAddLocationModal();
  closeAddIndustryModal();
  closeUpdateCertificationModal();
};

// Additional cleanup for resetting states
const resetAllStates = () => {
  resetEducation();
  resetExperience();
  resetAchievement();
  resetCertification();
  resetTestimonials();
  resetCareerGoals();
  resetEmploymentPreferences();
  console.log('All states reset.');
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
  <Graduate>
    <div class="bg-gray-100 min-h-screen p-6">
      <div class="container mx-auto">
        <h1 class="text-3xl font-bold mb-4">Profile Settings</h1>
        <p class="text-gray-600 mb-6">Manage your personal information and account settings</p>
        <!-- Success Message Alert -->
       <div v-if="isSuccessModalOpen" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
         <strong class="font-bold">Success!</strong>
         <span class="block sm:inline"> Your profile has been updated successfully.</span>
         <span class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="isSuccessModalOpen = false">
           <i class="fas fa-times"></i>
        </span>
       </div>
      
       <!-- Success Modal -->
       <Modal :show="modalState.profile" @close="modalState.profile = false">
         <div class="p-6">
           <div class="flex items-center mb-4">
             <div class="rounded-full bg-green-100 p-2 mr-3">
               <i class="fas fa-check text-green-500 text-xl"></i>
            </div>
             <h3 class="text-lg font-medium text-gray-900">Profile Updated Successfully</h3>
          </div>
           <div class="mt-3 text-gray-600">
            <p>Your profile information has been updated successfully.</p>
          </div>
           <div class="mt-6 flex justify-end">
            <button type="button" class="bg-indigo-600 text-white py-2 px-4 rounded-md" @click="modalState.profile = false">
               Close
            </button>
          </div>
         </div>
       </Modal>
      
       <!-- No Changes Modal -->
       <Modal :show="isNoChangesModalOpen" @close="isNoChangesModalOpen = false">
         <div class="p-6">
           <div class="flex items-center mb-4">
             <div class="rounded-full bg-blue-100 p-2 mr-3">
               <i class="fas fa-info-circle text-blue-500 text-xl"></i>
            </div>
             <h3 class="text-lg font-medium text-gray-900">No Changes Detected</h3>
          </div>
           <div class="mt-3 text-gray-600">
            <p>No changes were detected in your profile information.</p>
          </div>
           <div class="mt-6 flex justify-end">
            <button type="button" class="bg-indigo-600 text-white py-2 px-4 rounded-md" @click="isNoChangesModalOpen = false">
               Close
            </button>
          </div>
         </div>
       </Modal>
      
       <!-- Password Updated Modal -->
       <Modal :show="isPasswordUpdatedModalOpen" @close="isPasswordUpdatedModalOpen = false">
         <div class="p-6">
           <div class="flex items-center mb-4">
             <div class="rounded-full bg-green-100 p-2 mr-3">
               <i class="fas fa-check text-green-500 text-xl"></i>
            </div>
             <h3 class="text-lg font-medium text-gray-900">Password Updated Successfully</h3>
          </div>
           <div class="mt-3 text-gray-600">
            <p>Your password has been updated successfully. You will be logged out for security reasons.</p>
          </div>
           <div class="mt-6 flex justify-end">
            <button type="button" class="bg-indigo-600 text-white py-2 px-4 rounded-md" @click="isPasswordUpdatedModalOpen = false">
               Close
            </button>
          </div>
         </div>
       </Modal>
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex border-b border-gray-200 mb-6">
            <button class="py-2 px-4"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'general' }"
              @click="setActiveSection('general')">
              General
            </button>

            <button class="py-2 px-4"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'security' }"
              @click="setActiveSection('security')">
              Credentials
            </button>

            <button class="py-2 px-4"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'education' }"
              @click="setActiveSection('education')">
              Education
            </button>

            <button class="py-2 px-4"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'skills' }"
              @click="setActiveSection('skills')">
              Skills
            </button>

            <button class="py-2 px-4"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'experience' }"
              @click="setActiveSection('experience')">
              Experience
            </button>

            <button class="py-2 px-4"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'projects' }"
              @click="setActiveSection('projects')">
              Projects
            </button>

            <button class="py-2 px-4"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'certifications' }"
              @click="setActiveSection('certifications')">
              Certifications
            </button>

            <button class="py-2 px-4"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'achievements' }"
              @click="setActiveSection('achievements')">
              Achievements
            </button>

            <button class="py-2 px-4"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'testimonials' }"
              @click="setActiveSection('testimonials')">
              Testimonials
            </button>

            <button class="py-2 px-4"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'employment' }"
              @click="setActiveSection('employment')">
              Employment
            </button>

            <button class="py-2 px-4"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'career-goals' }"
              @click="setActiveSection('career-goals')">
              Career Goals
            </button>

            <button class="py-2 px-4"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'resume' }"
              @click="setActiveSection('resume')">
              Resume
            </button>
          </div>

          <!-- General Section -->
          <div v-if="activeSection === 'general'" class="flex flex-col lg:flex-row">
            <div class="w-full lg:w-1/3 mb-6 lg:mb-0">
              <h2 class="text-xl font-semibold mb-4">Profile Picture</h2>
              <p class="text-gray-600 mb-4">Update your profile picture</p>
              <div class="flex flex-col items-center">
                <img id="profile-picture" :src="profile.graduate_picture_url" alt="Profile picture"
                  class="rounded-full mb-4 w-32 h-32 object-cover"/>
                <input type="file" id="file-input" class="hidden" accept="image/*" @change="onFileChange" />
                <label for="file-input" class="text-indigo-600 cursor-pointer">Choose an image</label>
              </div>
            </div>

            <div class="w-full lg:w-2/3">
              <h2 class="text-xl font-semibold mb-4">Profile Information</h2>
              <p class="text-gray-600 mb-4">Update your personal information</p>
              <form @submit.prevent="saveProfile">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-4">
                  <div class="relative">
                    <label for="full-name" class="block text-gray-700 mb-1">Full Name</label>
                    <i class="fas fa-user absolute left-3 top-10 text-gray-400"></i>
                    <input type="text" id="full-name"
                      class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-2 focus:ring-indigo-600"
                      v-model="profile.fullName" placeholder="Enter your full name" />
                  </div>

                  <div class="relative">
                    <label for="professional-title" class="block text-gray-700 mb-1">Professional Title</label>
                    <i class="fas fa-briefcase absolute left-3 top-10 text-gray-400"></i>
                    <input type="text" id="graduate_professional-title"
                      class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-2 focus:ring-indigo-600"
                      v-model="profile.graduate_professional_title" placeholder="Enter your professional title" />
                  </div>

                  <div class="relative">
                    <label for="email-address" class="block text-gray-700 mb-1">Email Address</label>
                    <i class="fas fa-envelope absolute left-3 top-10 text-gray-400"></i>
                    <input type="email" id="email-address"
                      class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-2 focus:ring-indigo-600"
                      v-model="profile.email" placeholder="Enter your email address" />
                  </div>

                  <div class="relative">
                    <label for="phone" class="block text-gray-700 mb-1">Phone Number</label>
                    <i class="fas fa-phone absolute left-3 top-10 text-gray-400"></i>
                    <input type="text" id="phone"
                      class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-2 focus:ring-indigo-600"
                      v-model="profile.graduate_phone" placeholder="Enter your phone number" />
                  </div>

                  <div class="relative">
                    <label for="location" class="block text-gray-700 mb-1">Location</label>
                    <i class="fas fa-map-marker-alt absolute left-3 top-10 text-gray-400"></i>
                    <input type="text" id="location"
                      class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-2 focus:ring-indigo-600"
                      v-model="profile.graduate_location" placeholder="Enter your location" />
                  </div>

                  <div class="relative">
                    <label for="birthdate" class="block text-gray-700 mb-1">Birthdate</label>
                    <i class="fas fa-calendar-alt absolute left-3 top-10 text-gray-400"></i>
                    <datepicker v-model="profile.graduate_birthdate" :config="datepickerConfig"
                      class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-2 focus:ring-indigo-600"
                      placeholder="Select your birthdate" />
                  </div>

                  <div class="relative">
                    <label for="gender" class="block text-gray-700 mb-1">Gender</label>
                    <i class="fas fa-venus-mars absolute left-3 top-10 text-gray-400"></i>
                    <select id="gender"
                      class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-2 focus:ring-indigo-600"
                      v-model="profile.graduate_gender"> // Updated field name
                      <option value="" disabled selected>Select gender</option>
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                      <option value="Other">Other</option>
                      <option value="Prefer not to say">Prefer not to say</option>
                    </select>
                  </div>

                  <div class="relative">
                    <label for="ethnicity" class="block text-gray-700 mb-1">Ethnicity</label>
                    <i class="fas fa-user-friends absolute left-3 top-10 text-gray-400"></i>
                    <input type="text" id="ethnicity"
                      class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-2 focus:ring-indigo-600"
                      v-model="profile.graduate_ethnicity" placeholder="Enter your ethnicity" />
                  </div>
                </div>

                <div class="mb-4 relative">
                  <label for="address" class="block text-gray-700 mb-1">Home Address</label>
                  <i class="fas fa-home absolute left-3 top-10 text-gray-400"></i>
                  <input type="text" id="address"
                    class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-2 focus:ring-indigo-600"
                    v-model="profile.graduate_address" placeholder="Enter your address" />
                </div>

                <div class="mb-4 relative">
                  <label for="about-me" class="block text-gray-700 mb-1">About Me</label>
                  <i class="fas fa-info-circle absolute left-3 top-10 text-gray-400"></i>
                  <textarea id="about-me"
                    class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-2 focus:ring-indigo-600"
                    rows="4" v-model="profile.graduate_about_me" placeholder="Tell us about yourself"></textarea>
                </div>

                <button class="bg-indigo-600 text-white py-2 px-4 rounded-md">
                  <i class="fas fa-save mr-2"></i> Save Changes
                </button>
              </form>
            </div>
          </div>

          <!-- Security Section -->
          <div v-if="activeSection === 'security'" class="flex flex-col lg:flex-row">
            <div class="w-full lg:w-1/1 mb-6 lg:mb-0">
              <h1 class="text-xl font-semibold mb-4">Change Password</h1>
              <p class="text-gray-600 mb-4">Update your password to maintain account security</p>
              <form @submit.prevent="handleSubmit">
                <div class="mb-4">
                  <label class="block text-gray-700 mb-2" for="current-password">Current Password</label>
                  <input type="password" id="current-password" v-model="settingsForm.current_password"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                    placeholder="Current Password" />
                </div>
                <div class="mb-4">
                  <label class="block text-gray-700 mb-2" for="new-password">New Password</label>
                  <input type="password" id="new-password" v-model="settingsForm.password"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                    placeholder="New Password" />
                </div>
                <div class="mb-6">
                  <label class="block text-gray-700 mb-2" for="confirm-password">Confirm New Password</label>
                  <input type="password" id="confirm-password" v-model="settingsForm.password_confirmation"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                    placeholder="Confirm New Password" />
                </div>
                <button type="submit"
                  class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-600">
                  <i class="fas fa-key mr-2"></i> Change Password
                </button>
              </form>
            </div>
          </div>

          <!-- Education Section -->
          <div v-if="activeSection === 'education'" class="flex flex-col lg:flex-row">
            <div class="w-full lg:w-1/1 mb-6 lg:mb-0">
              <div class="flex justify-between items-center mb-4">
                <h1 class="text-xl font-semibold mb-4">Education</h1>
                <button class="bg-indigo-600 text-white px-4 py-2 rounded flex items-center"
                  @click="isAddEducationModalOpen = true">
                  <i class="fas fa-plus mr-2"></i>
                  Add Education
                </button>
              </div>
              <p class="text-gray-600 mb-6">Manage your educational background</p>
              <div v-if="educationEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div v-for="entry in educationEntries" :key="entry.id" class="bg-white p-8 rounded-lg shadow relative">
                  <div>
                    <div class="border-b pb-2">
                      <h2 class="text-xl font-bold">{{ entry.graduate_education_institution_id }}</h2>
                      <p class="text-gray-600">
                        {{ entry.graduate_education_program }} in {{ entry.graduate_education_field_of_study }}
                      </p>
                    </div>
                    <div class="flex items-center text-gray-600 mt-2">
                      <i class="far fa-calendar-alt mr-2"></i>
                      <span>
                        {{ formatDate(entry.graduate_education_start_date) }} - {{ entry.graduate_education_end_date ?
                          formatDate(entry.graduate_education_end_date) : 'present' }}
                      </span>
                    </div>
                    <p class="mt-2">
                      <strong>
                        <i class="fas fa-info-circle text-gray-500 mr-2"></i> Description:
                      </strong>
                      {{ entry.graduate_education_description || 'No description provided' }}
                    </p>
                    <p class="mt-2">
                      <strong>
                        <i class="fas fa-trophy text-gray-500 mr-2"></i> Achievements:
                      </strong>
                      <span v-if="entry.achievements && entry.achievements.includes(',')">
                        <ul class="list-disc list-inside">
                          <li v-for="(achievement, index) in entry.achievements.split(',')" :key="index">
                            {{ achievement.trim() }}
                          </li>
                        </ul>
                      </span>
                      <span v-else>
                        {{ entry.achievements || 'None' }}
                      </span>
                    </p>
                  </div>
                  <div class="absolute top-8 right-4 flex space-x-4">
                    <button class="text-gray-600 hover:text-indigo-600" @click="openUpdateEducationModal(entry)">
                      <i class="fas fa-pen"></i>
                    </button>
                    <button class="text-red-600 hover:text-red-800" @click="removeEducation(entry)">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- If no education entries exist -->
              <div v-else class="bg-white p-8 rounded-lg shadow">
                <p class="text-gray-600">No education entries added yet.</p>
              </div>
            </div>


            <!-- Add Education Modal -->
            <div v-if="isAddEducationModalOpen"
              class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
              <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                  <h2 class="text-xl font-semibold">Add Education</h2>
                  <button class="text-gray-500 hover:text-gray-700" @click="closeAddEducationModal">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <p class="text-gray-600 mb-4">Add details about your educational background</p>
                <div class="max-h-96 overflow-y-auto">
                  <form @submit.prevent="addEducation">
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Institution <span class="text-red-500">*</span></label>
                      <input type="text" v-model="education.graduate_education_institution_id"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. Harvard University" required>
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Degree <span class="text-red-500">*</span></label>
                      <input type="text" v-model="education.graduate_education_program"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. Bachelor of Science" required>
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Field of Study <span class="text-red-500">*</span></label>
                      <input type="text" v-model="education.graduate_education_field_of_study"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. Computer Science" required>
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Start Date <span class="text-red-500">*</span></label>
                      <Datepicker v-model="education.graduate_education_start_date"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Select start date" required />
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">End Date</label>
                      <Datepicker v-model="education.graduate_education_end_date"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Select end date" :disabled="education.is_current" />
                      <div class="mt-2">
                        <input type="checkbox" id="is-current" v-model="education.is_current" />
                        <label for="is-current" class="text-sm text-gray-700 ml-2">I currently study here</label>
                      </div>
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Description</label>
                      <textarea v-model="education.graduate_education_description"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        rows="3" placeholder="Describe your experience..."></textarea>
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Achievements</label>
                      <textarea v-model="education.achievements"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        rows="3" placeholder="List honors, awards, and scholarships..."
                        :disabled="education.noAchievements"></textarea>
                      <div class="mt-2">
                        <input type="checkbox" id="no-achievements" v-model="education.noAchievements"
                          @change="handleNoAchievements" />
                        <label for="no-achievements" class="text-sm text-gray-700 ml-2">No Achievements</label>
                      </div>
                    </div>
                    <div class="flex justify-end">
                      <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i>
                        Add Education
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Update Education Modal -->
            <div v-if="isUpdateEducationModalOpen"
              class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
              <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                  <h2 class="text-xl font-semibold">Update Education</h2>
                  <button class="text-gray-500 hover:text-gray-700" @click="closeUpdateEducationModal">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <p class="text-gray-600 mb-4">Update details about your educational background</p>
                <div class="max-h-96 overflow-y-auto">
                  <form @submit.prevent="updateEducation">
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Institution <span class="text-red-500">*</span></label>
                      <input type="text" v-model="education.graduate_education_institution_id"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. Harvard University" required>
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Degree <span class="text-red-500">*</span></label>
                      <input type="text" v-model="education.graduate_education_program"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. Bachelor of Science" required>
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Field of Study <span class="text-red-500">*</span></label>
                      <input type="text" v-model="education.graduate_education_field_of_study"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. Computer Science" required>
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Start Date <span class="text-red-500">*</span></label>
                      <Datepicker v-model="education.graduate_education_start_date" :config="datepickerConfig"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Select start date" required/>
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">End Date</label>
                      <Datepicker v-model="education.graduate_education_end_date"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Select end date" :disabled="education.is_current" />
                      <div class="mt-2">
                        <input type="checkbox" id="is-current-update" v-model="education.is_current"
                          @change="handleIsCurrent" />
                        <label for="is-current-update" class="text-sm text-gray-700 ml-2">I currently study here</label>
                      </div>
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Description</label>
                      <textarea v-model="education.graduate_education_description"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        rows="3" placeholder="Describe your experience..."></textarea>
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Achievements</label>
                      <textarea v-model="education.achievements"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        rows="3" placeholder="List honors, awards, and scholarships..."
                        :disabled="education.noAchievements"></textarea>
                      <div class="mt-2">
                        <input type="checkbox" id="no-achievements" v-model="education.noAchievements"
                          @change="handleNoAchievements" />
                        <label for="no-achievements" class="text-sm text-gray-700 ml-2">No Achievements</label>
                      </div>
                    </div>

                    <div class="flex justify-end">
                      <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i>
                        Update Education
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <!-- Skills Section -->
          <div v-if="activeSection === 'skills'" class="space-y-8">
            <!-- Skills Header -->
            <div class="flex flex-col space-y-4 sm:flex-row sm:justify-between sm:items-center sm:space-y-0">
              <div>
                <h1 class="text-2xl font-semibold text-gray-900">Skills</h1>
                <p class="mt-2 text-gray-600">Showcase your professional expertise and competencies</p>
              </div>
              <PrimaryButton @click="openAddSkillModal" class="inline-flex px-4 bg-indigo-600 text-white py-2 rounded-md items-center justify-center hover:bg-indigo-700">
                <i class="fas fa-plus mr-2"></i>
                Add Skill
              </PrimaryButton>
            </div>

            <!-- Skills Content -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
              <!-- Skills List -->
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
                              <button @click="editSkill(skill)"
                                class="text-gray-600 hover:text-indigo-600">
                                <i class="fas fa-pen"></i>
                              </button>
                              <button @click="removeSkill(skill)"
                                class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                              </button>
                            </div>
                          </div>
                          <!-- Proficiency Bar -->
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
                            
                            <!-- Experience Level -->
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
                <div v-else class="bg-white rounded-xl shadow-sm p-8 text-center border border-gray-100">
                  <div class="text-gray-400 mb-4">
                    <i class="fas fa-lightbulb text-4xl"></i>
                  </div>
                  <h3 class="text-lg font-medium text-gray-900 mb-2">No skills added yet</h3>
                  <p class="text-gray-600 mb-4">Start building your skill profile by adding your technical, soft, and language skills.</p>
                </div>
              </div>

              <!-- Skills Chart -->
              <div class="xl:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-4">
                  <SkillsChart :skills="skills" />
                </div>
              </div>
            </div>

            <!-- Add Skills Modal -->
            <div v-if="isAddSkillModalOpen"
              class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
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
                      class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                  </div>
                  <div class="mb-4">
                    <label for="skillProficiencyType" class="block text-gray-700">Proficiency Type <span class="text-red-500">*</span></label>
                    <select id="skillProficiencyType" v-model="skillProficiencyType"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                      <option value="" disabled>Select proficiency type</option>
                      <option value="Beginner">Beginner</option>
                      <option value="Intermediate">Intermediate</option>
                      <option value="Advanced">Advanced</option>
                      <option value="Expert">Expert</option>
                    </select>
                  </div>
                  <div class="mb-4">
                    <label for="skillType" class="block text-gray-700">Skill Type <span class="text-red-500">*</span></label>
                    <select id="skillType" v-model="skillType"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                      <option value="" disabled>Select skill type</option>
                      <option value="Technical Skills">Technical Skills</option>
                      <option value="Soft Skills">Soft Skills</option>
                      <option value="Language Skills">Language Skills</option>
                      <option value="Tool/Platform">Tool/Platform</option>
                      <option value="Other Skills">Other Skills</option>
                    </select>
                  </div>
                  <div class="mb-4">
                    <label for="yearsExperience" class="block text-gray-700">Years of Experience <span class="text-red-500">*</span></label>
                    <input type="number" id="yearsExperience" v-model="yearsExperience"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                  </div>
                  <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
                    <i class="fas fa-save mr-2"></i>
                    Save Skill
                  </button>
                </form>
              </div>
            </div>

            <!-- Update Skills Modal -->
            <div v-if="isUpdateSkillModalOpen"
              class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
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
                      class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                  </div>
                  <div class="mb-4">
                    <label for="skillProficiencyType" class="block text-gray-700">Proficiency Type <span class="text-red-500">*</span></label>
                    <select id="skillProficiencyType" v-model="skillProficiencyType"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                      <option value="" disabled>Select proficiency type</option>
                      <option value="Beginner">Beginner</option>
                      <option value="Intermediate">Intermediate</option>
                      <option value="Advanced">Advanced</option>
                      <option value="Expert">Expert</option>
                    </select>
                  </div>
                  <div class="mb-4">
                    <label for="skillType" class="block text-gray-700">Skill Type <span class="text-red-500">*</span></label>
                    <select id="skillType" v-model="skillType"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                      <option value="" disabled>Select skill type</option>
                      <option value="Technical Skills">Technical Skills</option>
                      <option value="Soft Skills">Soft Skills</option>
                      <option value="Language Skills">Language Skills</option>
                      <option value="Tool/Platform">Tool/Platform</option>
                    </select>
                  </div>
                  <div class="mb-4">
                    <label for="yearsExperience" class="block text-gray-700">Years of Experience <span class="text-red-500">*</span></label>
                    <input type="number" id="yearsExperience" v-model="yearsExperience"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                  </div>
                  <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
                    <i class="fas fa-save mr-2"></i>
                    Update Skill
                  </button>
                </form>
              </div>
            </div>
          </div>

          <!-- Experience Section -->
          <div v-if="activeSection === 'experience'" class="flex flex-col lg:flex-row">
            <div class="w-full lg:w-1/1 mb-6 lg:mb-0">
              <div class="flex justify-between items-center mb-4">
                <h1 class="text-xl font-semibold mb-4">Work Experience</h1>
                <button class="bg-indigo-600 text-white px-4 py-2 rounded flex items-center"
                  @click="isAddExperienceModalOpen = true">
                  <i class="fas fa-plus mr-2"></i> Add Experience
                </button>
              </div>
              <p class="text-gray-600 mb-6">Showcase your professional experience</p>
              <div v-if="experienceEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div v-for="entry in experienceEntries" :key="entry.id"
                  class="bg-white p-8 rounded-lg shadow relative">
                  <div>
                    <div class="border-b pb-2">
                    <h2 class="text-xl font-bold">{{ entry.graduate_experience_title }}</h2>
                    <p class="text-gray-600">{{ entry.graduate_experience_company }}</p>
                    </div>
                    <div class="flex items-center text-gray-600 mt-2">
                      <i class="fas fa-map-marker-alt mr-2"></i>
                      <span>{{ entry.graduate_experience_address }}</span>
                    </div>
                    <div class="flex items-center text-gray-600 mt-2">
                      <i class="far fa-calendar-alt mr-2"></i>
                      <span>
                        {{ formatDate(entry.graduate_experience_start_date) }} - {{ entry.graduate_experience_end_date ? 
                        formatDate(entry.graduate_experience_end_date) : 'present' }}
                      </span>
                    </div>
                    <p class="text-gray-600 mt-2 flex items-center">
                      <i class="fas fa-briefcase text-gray-500 mr-2"></i>
                      <strong>Employment Type:</strong> {{ entry.graduate_experience_employment_type }}
                    </p>
                    <p class="mt-2">
                      <strong>
                        <i class="fas fa-info-circle text-gray-500 mr-2"></i> Description:
                      </strong>
                      {{ entry.graduate_experience_description || 'No description provided' }}
                    </p>
                  </div>
                  <div class="absolute top-2 right-2 flex space-x-2">
                    <button class="text-gray-600 hover:text-indigo-600"
                      @click="openUpdateExperienceModal(entry)">
                      <i class="fas fa-pen"></i>
                    </button>
                    <button class="text-red-600 hover:text-red-800" @click="removeExperience(entry)">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- If no experience entries exist -->
              <div v-else class="bg-white p-8 rounded-lg shadow">
                <p class="text-gray-600">No experience entries added yet.</p>
              </div>
            </div>

            <!-- Add Experience Modal -->
            <div v-if="isAddExperienceModalOpen"
              class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
              <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                  <h2 class="text-xl font-semibold">Add Experience</h2>
                  <button class="text-gray-500 hover:text-gray-700" @click="closeAddExperienceModal">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <div class="max-h-96 overflow-y-auto">
                  <form @submit.prevent="addExperience">
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Job Title <span class="text-red-500">*</span></label>
                      <input type="text" v-model="experience.graduate_experience_title"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. Software Engineer" required />
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Company <span class="text-red-500">*</span></label>
                      <input type="text" v-model="experience.graduate_experience_company"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. Tech Corp" required />
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Location <span class="text-red-500">*</span></label>
                      <input type="text" v-model="experience.graduate_experience_address"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. New York, NY" required />
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Employment Type <span class="text-red-500">*</span></label>
                      <select v-model="experience.graduate_experience_employment_type"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        required>
                        <option value="" disabled>Select employment type</option>
                        <option value="Full-time">Full-time</option>
                        <option value="Part-time">Part-time</option>
                        <option value="Contract">Contract</option>
                        <option value="Freelance">Freelance</option>
                        <option value="Internship">Internship</option>
                      </select>
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Start Date <span class="text-red-500">*</span></label>
                      <Datepicker v-model="experience.graduate_experience_start_date"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Select start date" required />
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">End Date</label>
                      <Datepicker v-model="experience.graduate_experience_end_date"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Select end date" :disabled="stillInRole" />
                      <div class="mt-2">
                        <input type="checkbox" id="still-in-role" v-model="stillInRole"
                          @change="experience.graduate_experience_end_date = stillInRole ? null : experience.graduate_experience_end_date" />
                        <label for="still-in-role" class="text-sm text-gray-700 ml-2">I currently work here</label>
                      </div>
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Description</label>
                      <textarea v-model="experience.graduate_experience_description"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        rows="3" placeholder="Describe your role and responsibilities..."></textarea>
                    </div>
                    <div class="flex justify-end">
                      <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i>
                        Save Experience
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- UpdateExperience Modal -->
            <div v-if="isUpdateExperienceModalOpen"
              class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
              <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                  <h2 class="text-xl font-semibold">Update Experience</h2>
                  <button class="text-gray-500 hover:text-gray-700" @click="closeUpdateExperienceModal">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <div class="max-h-96 overflow-y-auto">
                  <form @submit.prevent="updateExperience">
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Job Title <span class="text-red-500">*</span></label>
                      <input type="text" v-model="experience.graduate_experience_title"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. Software Engineer" required />
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Company <span class="text-red-500">*</span></label>
                      <input type="text" v-model="experience.graduate_experience_company"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. Tech Corp" required />
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Location <span class="text-red-500">*</span></label>
                      <input type="text" v-model="experience.graduate_experience_address"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. New York, NY" required />
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Employment Type <span class="text-red-500">*</span></label>
                      <select v-model="experience.graduate_experience_employment_type"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        required>
                        <option value="" disabled>Select employment type</option>
                        <option value="Full-time">Full-time</option>
                        <option value="Part-time">Part-time</option>
                        <option value="Contract">Contract</option>
                        <option value="Freelance">Freelance</option>
                        <option value="Internship">Internship</option>
                      </select>
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Start Date <span class="text-red-500">*</span></label>
                      <Datepicker v-model="experience.graduate_experience_start_date"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Select start date" required />
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">End Date</label>
                      <Datepicker v-model="experience.graduate_experience_end_date"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Select end date" :disabled="stillInRole" />
                      <div class="mt-2">
                        <input type="checkbox" id="still-in-role-update" v-model="stillInRole"
                          @change="experience.graduate_experience_end_date = stillInRole ? null : experience.graduate_experience_end_date" />
                        <label for="still-in-role-update" class="text-sm text-gray-700 ml-2">
                          I currently work here
                        </label>
                      </div>
                    </div>
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Description</label>
                      <textarea v-model="experience.graduate_experience_description"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        rows="3" placeholder="Describe your role and responsibilities..."></textarea>
                    </div>
                    <div class="flex justify-end">
                      <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i>
                        Update
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <!-- Project Section -->
          <div v-if="activeSection === 'projects'" class="flex flex-col lg:flex-row">
            <div class="w-full lg:w-full mb-6 lg:mb-0">
              <div class="flex justify-between items-center mb-4">
                <h1 class="text-xl font-semibold">Projects</h1>
                <button class="bg-indigo-600 text-white px-4 py-2 rounded flex items-center hover:bg-indigo-700"
                  @click="isAddProjectModalOpen = true">
                  <i class="fas fa-plus mr-2"></i> Add Project
                </button>
              </div>
              <p class="text-gray-600 mb-6">Showcase your personal and professional projects</p>

              <div v-if="projectsEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="entry  in projectsEntries" :key="entry.id" 
                    class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                  <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                      <div>
                        <h2 class="text-xl font-bold text-gray-800">{{ entry.graduate_projects_title }}</h2>
                        <p class="text-indigo-600 font-medium">{{ entry.graduate_projects_role }}</p>
                      </div>
                      <div class="flex space-x-2">
                        <button class="text-gray-600 hover:text-gray-800" @click="openUpdateProjectModal(entry)">
                          <i class="fas fa-pen"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-800 transition-colors" 
                                @click="deleteProject(entry.id)">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </div>

                    <p class="text-gray-600 mb-4">
                      {{ entry.graduate_projects_description || 'No description provided' }}
                    </p>

                    <div class="space-y-3">
                      <div class="flex items-center text-gray-600">
                        <i class="far fa-calendar-alt mr-2 text-gray-500"></i>
                        <span>
                        {{ formatDate(entry.graduate_projects_start_date) }} - 
                        {{ entry.graduate_projects_end_date === null ? 'Present' : formatDate(entry.graduate_projects_end_date) }}
                      </span>
                      </div>

                      <div class="mt-3">
                        <h4 class="text-sm font-semibold text-gray-700 mb-1">Project URL:</h4>
                        <p class="text-gray-600">
                          <span v-if="entry.graduate_projects_url">
                            <a :href="entry.graduate_projects_url" target="_blank" class="text-indigo-600 hover:underline break-all">
                              {{ entry.graduate_projects_url }}
                            </a>
                          </span>
                          <span v-else class="text-gray-500">No project URL provided</span>
                        </p>
                      </div>

                      <div class="mt-3">
                        <h4 class="text-sm font-semibold text-gray-700 mb-1">Key Accomplishments:</h4>
                        <p class="text-gray-600">
                          <span v-if="entry.graduate_projects_key_accomplishments">
                            {{ entry.graduate_projects_key_accomplishments }}
                          </span>
                          <span v-else>No key accomplishment provided</span> 
                        </p>
                      </div>

                      <div v-if="projects.graduate_project_file" class="mt-3">
                    <img :src="`/storage/${projects.graduate_project_file}`" 
                        :alt="projects.graduate_projects_title"
                        class="max-w-full h-auto rounded-lg shadow"
                    />
                    </div>
                  </div>
                </div>
              </div>
              </div>

              <div v-else class="bg-white p-8 rounded-lg shadow">
                <p class="text-gray-600">No project entries added yet.</p>
              </div>

              <!-- Add Project Modal -->
              <div v-if="isAddProjectModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                  <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Add Project</h2>
                    <button class="text-gray-500 hover:text-gray-700" @click="closeAddProjectModal">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                  <div class="max-h-96 overflow-y-auto">
                    <form @submit.prevent="addProject">
                      <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Project Title <span class="text-red-500">*</span></label>
                        <input type="text" v-model="projects.graduate_projects_title"
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                          placeholder="e.g. E-commerce Platform" required />
                      </div>
                      <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Description</label>
                        <textarea v-model="projects.graduate_projects_description"
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                          rows="3" placeholder="Describe your project..."></textarea>
                      </div>
                      <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Role <span class="text-red-500">*</span></label>
                        <input type="text" v-model="projects.graduate_projects_role"
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                          placeholder="Your role in the project" required />
                      </div>
                      <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Start Date <span class="text-red-500">*</span></label>
                        <Datepicker v-model="projects.graduate_projects_start_date"
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                          placeholder="Select start date" required />
                      </div>
                      <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">End Date</label>
                        <Datepicker v-model="projects.graduate_projects_end_date"
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                          placeholder="Select end date" :disabled="projects.is_current" />
                        <div class="mt-2">
                          <input type="checkbox" v-model="projects.is_current" id="isCurrentProject" />
                          <label for="isCurrentProject" class="text-sm text-gray-700 ml-2">This is an ongoing project</label>
                        </div>
                      </div>
                      <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Project URL</label>
                        <input type="url" v-model="projects.graduate_projects_url"
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                          placeholder="e.g. https://yourproject.com" :disabled="noProjectUrl" />
                      </div>
                      <div class="mb-4">
                        <input type="checkbox" v-model="noProjectUrl" id="noProjectUrl" />
                        <label for="noProjectUrl" class="text-sm text-gray-700 ml-2">No Project URL</label>
                      </div>
                      <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Key Accomplishments</label>
                        <textarea v-model="projects.graduate_projects_key_accomplishments"
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                          rows="3" placeholder="What did you achieve?"></textarea>
                      </div>
                      <div class="mb-4">
                        <label for="project-file" class="block text-sm font-medium text-gray-700">Upload File</label>
                        <input type="file" id="project-file" @change="handleFileUpload"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                      </div>
                      <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700">Add Project</button>
                    </form>
                  </div>
                </div>
              </div>
              <!-- Update Project Modal -->
              <div v-if="isUpdateProjectModalOpen"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                  <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Update Project</h2>
                    <button class="text-gray-500 hover:text-gray-700" @click="closeUpdateProjectModal">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                  <div class="max-h-96 overflow-y-auto">
                    <form @submit.prevent="updateProject">
                      <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Project Title <span class="text-red-500">*</span></label>
                        <input type="text" v-model="projects.graduate_projects_title"
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                          placeholder="e.g. E-commerce Platform" required />
                      </div>
                      <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Description</label>
                        <textarea v-model="projects.graduate_projects_description"
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                          rows="3" placeholder="Describe your personal or professional project..."></textarea>
                      </div>
                      <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Role <span class="text-red-500">*</span></label>
                        <input type="text" v-model="projects.graduate_projects_role"
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                          placeholder="Your role in the project" required />
                      </div>
                      <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Start Date <span class="text-red-500">*</span></label>
                        <Datepicker v-model="projects.graduate_projects_start_date"
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                          placeholder="Start Date" required />
                      </div>
                      <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">End Date</label>
                        <Datepicker v-model="projects.graduate_projects_end_date"
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                          placeholder="End Date" :disabled="projects.is_current" />
                        <div class="mt-2">
                          <input type="checkbox" v-model="projects.is_current" id="isCurrentProject" />
                          <label for="isCurrentProject" class="text-sm text-gray-700 ml-2">This is an ongoing
                            project</label>
                        </div>
                      </div>
                      <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Project URL</label>
                        <input type="url" v-model="projects.graduate_projects_url"
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                          placeholder="e.g. https://yourproject.com" :disabled="noProjectUrl" />
                      </div>
                      <div class="mb-4">
                        <input type="checkbox" v-model="noProjectUrl" id="noProjectUrl" />
                        <label for="noProjectUrl" class="text-gray-700 font-medium ml-2">No Project URL</label>
                      </div>
                      <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Key Accomplishments</label>
                        <textarea v-model="projects.graduate_projects_key_accomplishments"
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                          rows="3" placeholder="What did you achieve?"></textarea>
                      </div>
                      <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700">Update
                        Project</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Certifications Section -->
          <div v-if="activeSection === 'certifications'" class="flex flex-col lg:flex-row">
            <div class="w-full lg:w-full mb-6 lg:mb-0">
              <div class="flex justify-between items-center mb-4">
                <h1 class="text-xl font-semibold mb-4">Certifications</h1>
                <button class="bg-indigo-600 text-white px-4 py-2 rounded flex items-center hover:bg-indigo-700"
                  @click="isAddCertificationModalOpen = true">
                  <i class="fas fa-plus mr-2"></i> Add Certification
                </button>
              </div>
              <p class="text-gray-600 mb-6">Manage your professional certifications</p>
              <div v-if="certificationsEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div v-for="entry in certificationsEntries" :key="entry.id" class="bg-white p-8 rounded-lg shadow relative">
                  <div>
                    <div class="border-b pb-2">
                    <h2 class="text-xl font-bold">{{ entry.graduate_certification_name }}</h2>
                    <p class="text-gray-600">{{ entry.graduate_certification_issuer }}</p>
                  </div>
                    <div class="flex items-center text-gray-600 mt-2">
                      <i class="far fa-calendar-alt mr-2"></i>
                      <span>{{ entry.graduate_certification_issue_date }} - {{ entry.graduate_certification_expiry_date || 'No expiry date' }}</span>
                    </div>
                    <p class="mt-2">
                      URL: 
                      <span v-if="entry.graduate_certification_credential_url && !entry.noCredentialUrl">
                        <a :href="entry.graduate_certification_credential_url" class="text-blue-600 hover:underline" target="_blank">
                          {{ entry.graduate_certification_credential_url }}
                        </a>
                      </span>
                      <span v-else class="text-gray-500">No Credential URL</span>
                    </p>
                    <p class="mt-2" v-if="entry.file_path">
                      Photo: <img :src="`/storage/${entry.file_path}`" class="w-24 h-24 object-cover rounded-md" alt="Certification Photo" />
                    </p>
                  </div>
                  <div class="absolute top-2 right-2 flex space-x-2">
                    <button class="text-gray-600 hover:text-indigo-600" @click="openUpdateCertificationModal(entry)">
                      <i class="fas fa-pen"></i>
                    </button>
                    <button class="text-red-600 hover:text-red-800" @click="removeCertification(entry)">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- If no certifications exist -->
              <div v-else class="bg-white p-8 rounded-lg shadow mb-4">
                <p class="text-gray-600">No certifications added yet.</p>
              </div>
            </div>

            <!-- Add Certification Modal -->
            <div v-if="isAddCertificationModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
              <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                  <h2 class="text-xl font-semibold">Add Certification</h2>
                  <button class="text-gray-500 hover:text-gray-700" @click="closeAddCertificationModal">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <form @submit.prevent="addCertification" novalidate>
                  <div class="mb-4">
                    <label for="certification-name" class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                    <input type="text" id="certification-name" v-model="form.graduate_certification_name"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      required>
                  </div>
                  <div class="mb-4">
                    <label for="certification-issuer" class="block text-sm font-medium text-gray-700">Issuer <span class="text-red-500">*</span></label>
                    <input type="text" id="certification-issuer" v-model="form.graduate_certification_issuer"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      required>
                  </div>
                  <div class="mb-4">
                    <label for="certification-issue-date" class="block text-sm font-medium text-gray-700">Issue Date <span class="text-red-500">*</span></label>
                    <Datepicker v-model="form.graduate_certification_issue_date"
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                      placeholder="Select issue date" required />
                  </div>
                  <div class="mb-4">
                    <label for="certification-expiry-date" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                    <Datepicker v-model="form.graduate_certification_expiry_date"
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                      placeholder="Select expiry date" :required="!form.noExpiryDate" :disabled="form.noExpiryDate" />
                    <div class="mt-2">
                      <input type="checkbox" id="no-expiry-date" v-model="form.noExpiryDate" class="mr-2" />
                      <label for="no-expiry-date" class="text-sm text-gray-700">No expiry date</label>
                    </div>
                  </div>
                  <div class="mb-4">
                    <label for="certification-credential-url" class="block text-sm font-medium text-gray-700">Credential URL</label>
                    <input type="url" id="certification-credential-url" v-model="form.graduate_certification_credential_url"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :required="!form.noCredentialUrl" :disabled="form.noCredentialUrl" />
                    <div class="mt-2">
                      <input type="checkbox" id="no-credential-url" v-model="form.noCredentialUrl" class="mr-2" />
                      <label for="no-credential-url" class="text-sm text-gray-700">No Credential URL</label>
                    </div>
                  </div>
                  <div class="mb-4">
                    <label for="certification-file" class="block text-sm font-medium text-gray-700">Upload File</label>
                    <input type="file" id="certification-file" @change="handleFileUpload"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                  </div>
                  <div class="flex justify-end">
                    <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
                      <i class="fas fa-save mr-2"></i>
                      Save Certification
                    </button>
                  </div>
                </form>
              </div>
            </div>

            <!-- Update Certification Modal -->
            <div v-if="isUpdateCertificationModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
              <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                  <h2 class="text-xl font-semibold">Update Certification</h2>
                  <button class="text-gray-500 hover:text-gray-700" @click="closeUpdateCertificationModal">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <form @submit.prevent="updateCertification" novalidate>
                  <div class="mb-4">
                    <label for="update-certification-name" class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                    <input type="text" id="update-certification-name" v-model="form.graduate_certification_name"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      required>
                  </div>
                  <div class="mb-4">
                    <label for="update-certification-issuer" class="block text-sm font-medium text-gray-700">Issuer <span class="text-red-500">*</span></label>
                    <input type="text" id="update-certification-issuer" v-model="form.graduate_certification_issuer"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      required>
                  </div>
                  <div class="mb-4">
                    <label for="update-certification-issue-date" class="block text-sm font-medium text-gray-700">Issue Date <span class="text-red-500">*</span></label>
                    <Datepicker v-model="form.graduate_certification_issue_date"
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                      placeholder="Select issue date" required />
                  </div>
                  <div class="mb-4">
                    <label for="update-certification-expiry-date" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                    <Datepicker v-model="form.graduate_certification_expiry_date"
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                      placeholder="Select expiry date" :required="!form.noExpiryDate" :disabled="form.noExpiryDate" />
                    <div class="mt-2">
                      <input type="checkbox" id="update-no-expiry-date" v-model="form.noExpiryDate" class="mr-2" />
                      <label for="update-no-expiry-date" class="text-sm text-gray-700">No expiry date</label>
                    </div>
                  </div>
                  <div class="mb-4">
                    <label for="update-certification-credential-url" class="block text-sm font-medium text-gray-700">Credential URL</label>
                    <input type="url" id="update-certification-credential-url" v-model="form.graduate_certification_credential_url"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :required="!form.noCredentialUrl" :disabled="form.noCredentialUrl" />
                    <div class="mt-2">
                      <input type="checkbox" id="update-no-credential-url" v-model="form.noCredentialUrl" class="mr-2" />
                      <label for="update-no-credential-url" class="text-sm text-gray-700">No Credential URL</label>
                    </div>
                  </div>
                  <div class="mb-4">
                    <label for="update-certification-file" class="block text-sm font-medium text-gray-700">Upload File</label>
                    <input type="file" id="update-certification-file" @change="handleFileUpload"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                  </div>
                  <div class="flex justify-end">
                    <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
                      <i class="fas fa-save mr-2"></i>
                      Update Certification
                    </button>
                  </div>
                </form>
              </div>
            </div> 
          </div>

          <!-- Achievement Section -->
          <div v-if="activeSection === 'achievements'" class="flex flex-col lg:flex-row">
            <div class="w-full lg:w-1/1 mb-6 lg:mb-0">
              <div class="flex justify-between items-center mb-4">
                <h1 class="text-xl font-semibold mb-4">Achievements</h1>
                <button class="bg-indigo-600 text-white px-4 py-2 rounded flex items-center" @click="isAddAchievementModalOpen = true">
                  <i class="fas fa-plus mr-2"></i>
                  Add Achievement
                </button>
              </div>
              <p class="text-gray-600 mb-6">Showcase your awards and recognition</p>

              <!-- Achievement Entries -->
              <div v-if="achievementEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="achievement in achievementEntries" :key="achievement.id" class="bg-white rounded-lg shadow-md p-4 space-y-3">
                  <!-- Achievement Title and Type -->
                  <div class="border-b pb-2">
                    <h3 class="text-lg font-semibold">{{ achievement.graduate_achievement_title }}</h3>
                    <span class="text-sm text-gray-600">{{ achievement.graduate_achievement_type }}</span>
                  </div>
                  
                  <!-- Issuer and Date -->
                  <div class="space-y-1">
                    <p class="text-sm"><span class="font-medium">Issuer:</span> {{ achievement.graduate_achievement_issuer }}</p>
                    <p class="text-sm"><span class="font-medium">Date:</span> {{ achievement.graduate_achievement_date }}</p>
                  </div>
                  
                  <!-- Description -->
                  <div class="text-sm">
                    <p class="text-sm"><span class="font-medium">Description:</span>{{ achievement.graduate_achievement_description }}</p>
                  </div>
                  
                  <!-- URL if available -->
                  <div v-if="achievement.graduate_achievement_url" class="text-sm">
                    <a :href="achievement.graduate_achievement_url" 
                      target="_blank" 
                      class="text-blue-600 hover:text-blue-800">
                      View Certificate
                    </a>
                  </div>
                  
                  <!-- Credential Picture -->
                  <div v-if="achievement.credential_picture_url" class="mt-3">
                    <img :src="`/storage/${achievement.credential_picture_url}`" 
                        :alt="achievement.graduate_achievement_title"
                        class="max-w-full h-auto rounded-lg shadow"
                    />
                  </div>
                  
                  <!-- Action Buttons -->
                  <div class="flex justify-end space-x-2 mt-3">
                    <button @click="editAchievement(achievement)" 
                            class="text-gray-600 hover:text-indigo-600">
                      <i class="fas fa-pen"></i>
                    </button>
                    <button @click="removeAchievement(achievement)" class="text-red-600 hover:text-red-800">
                  <i class="fas fa-trash"></i>
                </button>
                  </div>
                </div>
              </div>

              <!-- No Achievement Entries Message -->
              <div v-else class="bg-white p-8 rounded-lg shadow mb-4">
                <p class="text-gray-600">No achievement entries added yet.</p>
              </div>
            </div>

            <!-- Update Project Modal -->
            <div v-if="isUpdateProjectModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
              <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                  <h2 class="text-xl font-semibold">Update Project</h2>
                  <button class="text-gray-500 hover:text-gray-700" @click="closeUpdateProjectModal">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <p class="text-gray-600 mb-4">Update details about your project</p>
                <form @submit.prevent="updateProject">
                  <div class="max-h-96 overflow-y-auto">
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Project Title <span class="text-red-500">*</span></label>
                      <input type="text" v-model="projects.graduate_projects_title"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Enter project title" required />
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Role <span class="text-red-500">*</span></label>
                      <input type="text" v-model="projects.graduate_projects_role"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Your role in the project" required />
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Description</label>
                      <textarea v-model="projects.graduate_projects_description"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        rows="3" placeholder="Describe the project..."></textarea>
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Start Date <span class="text-red-500">*</span></label>
                      <Datepicker v-model="projects.graduate_projects_start_date"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Select start date" required />
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">End Date</label>
                      <Datepicker v-model="projects.graduate_projects_end_date"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Select end date" :disabled="projects.is_current" />
                      <div class="mt-2">
                        <input type="checkbox" id="ongoing-project-update" v-model="ongoingProject" />
                        <label for="ongoing-project-update" class="text-sm text-gray-700 ml-2">This is an ongoing project</label>
                      </div>
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Project URL</label>
                      <input type="url" v-model="projects.graduate_projects_url"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. https://example.com"
                        :disabled="noProjectUrl" />
                      <div class="mt-2">
                        <input type="checkbox" id="no-project-url-update" v-model="noProjectUrl" />
                        <label for="no-project-url-update" class="text-sm text-gray-700 ml-2">No Project URL</label>
                      </div>
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Key Accomplishments</label>
                      <textarea v-model="projects.graduate_projects_key_accomplishments"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        rows="3" placeholder="List key accomplishments..."></textarea>
                    </div>

                    <div class="flex justify-end">
                      <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i>
                        Update Project
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <!-- Add Achievement Modal -->
            <div v-if="isAddAchievementModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
              <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                  <h2 class="text-xl font-semibold">Add Achievement</h2>
                  <button class="text-gray-500 hover:text-gray-700" @click="closeAddAchievementModal">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <p class="text-gray-600 mb-4">Add details about awards, recognition, or other achievements</p>
                <form @submit.prevent="addAchievement" enctype="multipart/form-data">
                  <div class="max-h-96 overflow-y-auto">
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Title</label>
                      <input type="text" 
                        v-model="achievementForm.graduate_achievement_title"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Name of the Award or Certification" 
                        required>
                    </div>

                    <div class="mb-4">
                      <label for="achievementType" class="block text-gray-700">Achievement Type <span class="text-red-500">*</span></label>
                      <select id="achievementType" name="graduate_achievement_type" v-model="achievementForm.graduate_achievement_type"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        <option value="" disabled>Select achievement type</option>
                        <option value="Award">Award</option>
                        <option value="Recognition">Recognition</option>
                        <option value="Publication">Publication</option>
                        <option value="Patent">Patent</option>
                      </select>
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Issuer <span class="text-red-500">*</span></label>
                      <input type="text" name="graduate_achievement_issuer" v-model="achievementForm.graduate_achievement_issuer"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Name of the Organization or Institution" required>
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Date <span class="text-red-500">*</span></label>
                      <Datepicker name="graduate_achievement_date" v-model="achievementForm.graduate_achievement_date"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Select start date" required />
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Description</label>
                      <textarea name="graduate_achievement_description" v-model="achievementForm.graduate_achievement_description"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Describe your achievement"></textarea>
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">URL</label>
                      <input type="url" v-model="achievementForm.graduate_achievement_url"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. https://example.com" 
                        :required="!achievementForm.noCredentialUrl"
                        :disabled="achievementForm.noCredentialUrl" />
                      <div class="mt-2">
                        <input type="checkbox" id="no-credential-url" v-model="achievementForm.noCredentialUrl" class="mr-2" />
                        <label for="no-credential-url" class="text-sm text-gray-700">No Credential URL</label>
                      </div>
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 mb-2">Credential Picture</label>
                      <input
                        type="file"
                        name="credential_picture"
                        accept="image/*"
                        @change="handleCredentialPictureUpload"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"/>
                      <img
                        v-if="previewImage"
                        :src="previewImage"
                        class="mt-2 max-w-xs rounded"
                        alt="Credential Preview"/>
                    </div>

                    <div class="flex justify-end">
                      <button 
                        type="submit" 
                        class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center"
                        :disabled="isSubmittingAchievement">
                        <i class="fas fa-spinner fa-spin mr-2" v-if="isSubmittingAchievement"></i>
                        <i class="fas fa-save mr-2" v-else></i>
                        {{ isSubmittingAchievement ? 'Saving...' : 'Add Achievement' }}
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <!-- Update Achievement Modal -->
            <div v-if="isUpdateAchievementModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
              <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                  <h2 class="text-xl font-semibold">Update Achievement</h2>
                  <button class="text-gray-500 hover:text-gray-700" @click="closeUpdateAchievementModal">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <p class="text-gray-600 mb-4">Update details about your achievement</p>
                <form @submit.prevent="updateAchievement">
                  <div class="max-h-96 overflow-y-auto">
                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Title <span class="text-red-500">*</span></label>
                      <input type="text" v-model="achievementForm.graduate_achievement_title"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Name of the Award or Certification" required />
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Type</label>
                      <select v-model="achievementForm.graduate_achievement_type"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600">
                        <option value="" disabled>Select type</option>
                        <option value="Award">Award</option>
                        <option value="Recognition">Recognition</option>
                        <option value="Publication">Publication</option>
                        <option value="Patent">Patent</option>
                      </select>
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Issuer <span class="text-red-500">*</span></label>
                      <input type="text" v-model="achievementForm.graduate_achievement_issuer"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Name of the Organization or Institution" required />
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Date <span class="text-red-500">*</span></label>
                      <Datepicker v-model="achievementForm.graduate_achievement_date"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Select date" required />
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">Description</label>
                      <textarea v-model="achievementForm.graduate_achievement_description"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        rows="3" placeholder="Describe your achievement..."></textarea>
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 font-medium mb-2">URL</label>
                      <input type="url" v-model="achievementForm.graduate_achievement_url"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. https://example.com" :disabled="noCredentialUrl" />
                    </div>

                    <div class="mb-4">
                      <label class="inline-flex items-center">
                        <input type="checkbox" v-model="noCredentialUrl"
                          class="form-checkbox h-5 w-5 text-indigo-600" @change="toggleUrlField" />
                        <span class="ml-2 text-gray-700">No Credential URL</span>
                      </label>
                    </div>

                    <div class="mb-4">
                      <label class="block text-gray-700 mb-2">Credential Picture</label>
                      <input
                        type="file"
                        accept="image/*"
                        @change="handleCredentialPictureUpload"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"/>
                      <img
                        v-if="achievementForm.credential_picture_url"
                        :src="achievementForm.credential_picture_url"
                        class="mt-2 max-w-xs rounded"
                        alt="Credential Preview"/>
                    </div>

                    <div class="flex justify-end">
                      <button type="submit"
                      class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i>
                        Update Achievement
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Testimonials Section -->
          <div v-if="activeSection === 'testimonials'" class="bg-white p-6 rounded-lg shadow space-y-6">
            <!-- Testimonials Header -->
            <div class="flex justify-between items-center">
              <div>
                <h1 class="text-xl font-semibold">Testimonials</h1>
                <p class="text-gray-600 mt-2">Recommendations from colleagues and clients</p>
              </div>
              <button class="bg-indigo-600 text-white px-4 py-2 rounded flex items-center hover:bg-indigo-700 transition-colors"
                @click="isAddTestimonialsModalOpen = true">
                <i class="fas fa-plus mr-2"></i>
                Add Testimonials
              </button>
            </div>

            <!-- Testimonials Entries -->
            <div v-if="testimonialsEntries.length > 0" class="space-y-4">
              <div v-for="(entry, index) in testimonialsEntries" :key="entry.id"
                class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 relative hover:shadow-md transition-shadow">
                <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                  <div class="flex-grow">
                    <h2 class="text-xl font-bold text-gray-900">{{ entry.graduate_testimonials_name }}</h2>
                    <p class="text-gray-600 font-medium">{{ entry.graduate_testimonials_role_title }}</p>
                    <p class="mt-3 text-gray-700 italic">"{{ entry.graduate_testimonials_testimonial }}"</p>
                  </div>
                  <div v-if="testimonials.graduate_testimonials_letters" class="md:w-1/3">
                    <img :src="`/storage/${testimonials.graduate_testimonials_letters}`" 
                        :alt="testimonials.graduate_testimonials_name"
                        class="w-full h-auto rounded-lg shadow-sm"
                    />
                  </div>
                </div>
                <div class="absolute top-4 right-4 flex space-x-2">
                  <button class="text-gray-600 hover:text-indigo-600" 
                    @click="openUpdateTestimonialsModal(entry, index)">
                    <i class="fas fa-pen"></i>
                  </button>
                  <button class="text-red-600 hover:text-red-800" 
                    @click="removeTestimonials(entry)">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- If no testimonials entries exist -->
            <div v-else class="text-center py-8">
              <p class="text-gray-600">No testimonial entries added yet.</p>
              <p class="text-sm text-gray-500 mt-2">Click the "Add Testimonials" button to get started.</p>
            </div>
          

            <!-- Add Testimonials Modal -->
            <div v-if="isAddTestimonialsModalOpen"
              class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
              <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                  <h2 class="text-xl font-semibold">Add Testimonials</h2>
                  <button class="text-gray-500 hover:text-gray-700" @click="closeAddTestimonialsModal">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <p class="text-gray-600 mb-4">Add a recommendation from a colleague or client</p>
                <form @submit.prevent="addTestimonials">
                  <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" v-model="testimonials.graduate_testimonials_name"
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                      placeholder="e.g. John Doe" required />
                  </div>
                  <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Role/Title <span class="text-red-500">*</span></label>
                    <input type="text" v-model="testimonials.graduate_testimonials_role_title"
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                      placeholder="e.g. Manager" required />
                  </div>
                  <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Testimonial <span class="text-red-500">*</span></label>
                    <textarea v-model="testimonials.graduate_testimonials_testimonial"
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
            rows="3" placeholder="Write the testimonial here..." required></textarea>
                  </div>
                  <div class="mb-4">
                    <label for="testimonial-file" class="block text-sm font-medium text-gray-700">Upload File</label>
                    <input type="file" id="testimonial-file" @change="handleFileUpload"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                  </div>
                  <div class="flex justify-end">
                    <button type="submit"
                      class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
                      <i class="fas fa-save mr-2"></i>
                      Add Testimonial
                    </button>
                  </div>
                </form>
              </div>
            </div>

            <!-- Update Testimonials Modal -->
            <div v-if="isUpdateTestimonialsModalOpen"
              class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
              <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                  <h2 class="text-xl font-semibold">Update Testimonials</h2>
                  <button class="text-gray-500 hover:text-gray-700" @click="closeUpdateTestimonialsModal">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <p class="text-gray-600 mb-4">Update a recommendation from a colleague or client</p>
                <form @submit.prevent="updateTestimonials">
                  <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" v-model="testimonials.graduate_testimonials_name"
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                      placeholder="e.g. John Doe" required />
                  </div>
                  <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Role/Title <span class="text-red-500">*</span></label>
                    <input type="text" v-model="testimonials.graduate_testimonials_role_title"
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                      placeholder="e.g. Manager" required />
                  </div>
                  <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Testimonial <span class="text-red-500">*</span></label>
                    <textarea v-model="testimonials.graduate_testimonials_testimonial"
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                      rows="3" placeholder="Write the testimonial here..." required></textarea>
                  </div>
                  <div class="flex justify-end">
                    <button type="submit"
                      class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
                      <i class="fas fa-save mr-2"></i>
                      Update Testimonial
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Employment Preferences Section -->
          <div v-if="activeSection === 'employment'" class="flex flex-col lg:flex-row">
            <div class="w-full lg:w-1/1 mb-6 lg:mb-0">
              <div class="flex justify-between items-center mb-4">
                <h1 class="text-xl font-semibold mb-4">Employment Preferences</h1>
              </div>
              <p class="text-gray-600 mb-6">Set your job preferences and requirements</p>
              <!-- Saved Preferences Container -->
              <div v-if="employmentPreferences.jobTypes.length || employmentPreferences.salaryExpectations.range || employmentPreferences.preferredLocations.length || employmentPreferences.workEnvironment.length || employmentPreferences.availability.length || employmentPreferences.additionalNotes" class="mb-6 p-4 border border-gray-300 rounded-lg bg-gray-50">
                <h2 class="text-xl font-semibold mb-4">Saved Employment Preferences</h2>
                <div class="space-y-3">
                  <p><strong>Job Types:</strong> {{ employmentPreferences.jobTypes.join(', ') }}</p>
                  <p><strong>Salary Expectations:</strong> {{ employmentPreferences.salaryExpectations.range }} {{ employmentPreferences.salaryExpectations.frequency }}</p>
                  <p><strong>Preferred Locations:</strong> {{ employmentPreferences.preferredLocations.join(', ') }}</p>
                  <p><strong>Work Environment:</strong> {{ employmentPreferences.workEnvironment.join(', ') }}</p>
                  <p><strong>Availability:</strong> {{ employmentPreferences.availability.join(', ') }}</p>
                  <p><strong>Additional Notes:</strong> {{ employmentPreferences.additionalNotes }}</p>
                </div>
              </div>
              <!-- Form Fields -->
              <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Job Types</h2>
                <div class="flex flex-wrap gap-2">
                  <button v-for="type in ['Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship']" :key="type"
                    :class="employmentPreferences.jobTypes.includes(type) ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                    class="px-4 py-2 rounded-full hover:bg-indigo-700" @click="toggleJobType(type)">
                    {{ type }}
                  </button>
                </div>
              </div>
              <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Salary Expectations</h2>
                <div class="flex items-center gap-2">
                  <select v-model="employmentPreferences.salaryExpectations.range"
                    class="border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-indigo-600">
                    <option value="" disabled>Select expected salary range</option>
                    <option value="Below ₱20,000">Below ₱20,000</option>
                    <option value="₱20,000 - ₱30,000">₱20,000 - ₱30,000</option>
                    <option value="₱30,000 - ₱40,000">₱30,000 - ₱40,000</option>
                    <option value="₱40,000 - ₱50,000">₱40,000 - ₱50,000</option>
                    <option value="₱50,000 and above">₱50,000 and above</option>
                  </select>
                  <select v-model="employmentPreferences.salaryExpectations.frequency"
                    class="border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-indigo-600">
                    <option>per year</option>
                    <option>per month</option>
                    <option>per hour</option>
                  </select>
                </div>
              </div>
              <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Preferred Locations</h2>
                <div class="flex flex-wrap gap-2">
                  <span v-for="location in employmentPreferences.preferredLocations" :key="location" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-full flex items-center">
                    {{ location }}
                    <button @click="removePreferredLocation(location)" class="ml-2 text-red-500 hover:text-red-700">
                      &times; <!-- This is the "X" character -->
                    </button>
                  </span>
                  <button class="bg-indigo-600 text-white px-4 py-2 rounded-full hover:bg-indigo-700" @click="isAddLocationModalOpen = true">
                    + Add Location
                  </button>
                </div>
              </div>
              <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Work Environment</h2>
                <div class="flex flex-wrap gap-8">
                  <label class="flex items-center">
                    <input type="checkbox" value="Remote" v-model="employmentPreferences.workEnvironment"
                      class="form-checkbox text-indigo-600 focus:ring-indigo-600" />
                    <span class="ml-2 text-gray-700">Remote</span>
                  </label>
                  <label class="flex items-center">
                    <input type="checkbox" value="Hybrid" v-model="employmentPreferences.workEnvironment"
                      class="form-checkbox text-indigo-600 focus:ring-indigo-600" />
                    <span class="ml-2 text-gray-700">Hybrid</span>
                  </label>
                  <label class="flex items-center">
                    <input type="checkbox" value="On-site" v-model="employmentPreferences.workEnvironment"
                      class="form-checkbox text-indigo-600 focus:ring-indigo-600" />
                    <span class="ml-2 text-gray-700">On-site</span>
                  </label>
                </div>
              </div>
              <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Availability</h2>
                <div class="flex flex-wrap gap-8">
                  <label class="flex items-center">
                    <input type="checkbox" value="Immediately" v-model="employmentPreferences.availability"
                      class="form-checkbox text-indigo-600 focus:ring-indigo-600" />
                    <span class="ml-2 text-gray-700">Immediately</span>
                  </label>
                  <label class="flex items-center">
                    <input type="checkbox" value="2 weeks notice" v-model="employmentPreferences.availability"
                      class="form-checkbox text-indigo-600 focus:ring-indigo-600" />
                    <span class="ml-2 text-gray-700">2 weeks notice</span>
                  </label>
                  <label class="flex items-center">
                    <input type="checkbox" value ="1 month notice" v-model="employmentPreferences.availability"
                      class="form-checkbox text-indigo-600 focus:ring-indigo-600" />
                    <span class="ml-2 text-gray-700">1 month notice</span>
                  </label>
                </div>
              </div>
              <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Additional Notes</h2>
                <textarea v-model="employmentPreferences.additionalNotes"
                  class="border border-gray-300 rounded w-full px-4 py-2 focus:ring-2 focus:ring-indigo-600" rows="4"
                  placeholder="Any other preferences or requirements..."></textarea>
              </div>
              <div class="flex space-x-4">
                <button class="bg-indigo-600 text-white px-6 py-3 rounded-lg flex items-center" @click="saveEmploymentPreferences">
                  <i class="fas fa-save mr-2"></i> Save Preferences
                </button>
                <button class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg flex items-center hover:bg-gray-400" @click="resetEmploymentPreferences">
                  <i class="fas fa-undo mr-2"></i> Reset Preferences
                </button>
              </div>
            </div>
            <!-- Add Location Modal -->
            <div v-if="isAddLocationModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
              <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                  <h2 class="text-xl font-semibold">Add Location</h2>
                  <button class="text-gray-500 hover:text-gray-700" @click="closeAddLocationModal">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <p class="text-gray-600 mb-4">Add a preferred location for employment</p>
                <form @submit.prevent="addPreferredLocation">
                  <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Location <span class="text-red-500">*</span></label>
                    <input type="text" v-model="newLocation" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600" placeholder="e.g. New York, NY" required />
                  </div>
                  <div class="flex justify-end">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Add</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Career Goals Section-->
          <div v-if="activeSection === 'career-goals'" class="flex flex-col lg:flex-row">
          <div class="w-full lg:w-1/1 mb-6 lg:mb-0">
            <div class="flex justify-between items-center mb-4">
              <h1 class="text-xl font-semibold mb-4">Career Goals</h1>
            </div>
            <p class="text-gray-600 mb-6">Define your short and long-term career aspirations</p>
            <!-- Saved Career Goals Container -->
            <div v-if="careerGoals.shortTermGoals || careerGoals.longTermGoals" class="mb-6 p-4 border border-gray-300 rounded-lg bg-gray-50">
              <h2 class="text-xl font-semibold mb-4">Saved Career Goals</h2>
              <div class="space-y-3">
                <p><strong>Short-term Goals:</strong> {{ careerGoals.shortTermGoals }}</p>
                <p><strong>Long-term Goals:</strong> {{ careerGoals.longTermGoals }}</p>
                <p><strong>Industries of Interest:</strong> 
                  <span v-if="careerGoals.industriesOfInterest.length">
                    {{ careerGoals.industriesOfInterest.join(', ') }}
                  </span>
                  <span v-else>No industries specified</span>
                </p>
                <p><strong>Career Path:</strong> {{ careerGoals.careerPath || 'Not specified' }}</p>
              </div>
            </div>
            <!-- Form Fields -->
            <div class="mb-6">
              <h2 class="text-xl font-semibold mb-2">Short-term Goals (1-2 years)</h2>
              <textarea 
                class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" 
                rows="3"
                v-model="careerGoals.shortTermGoals"
                placeholder="Enter your short-term career goals...">
              </textarea>
            </div>
            <div class="mb-6">
              <h2 class="text-xl font-semibold mb-2">Long-term Goals (3-5 years)</h2>
              <textarea 
                class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" 
                rows="3"
                v-model="careerGoals.longTermGoals"
                placeholder="Enter your long-term career goals...">
              </textarea>
            </div>
            <div class="mb-6">
              <h2 class="text-xl font-semibold mb-2">Industries of Interest</h2>
              <div class="flex flex-wrap gap-2">
                <span v-for="industry in careerGoals.industriesOfInterest" :key="industry"
                  class="bg-gray-200 text-gray-700 px-4 py-2 rounded-full">
                  {{ industry }}
                </span>
                <button class="bg-indigo-600 text-white px-4 py-2 rounded-full hover:bg-indigo-700"
                  @click="openAddIndustryModal">
                  + Add Industry
                </button>
              </div>
            </div>
            <div class="mb-6">
              <h2 class="text-xl font-semibold mb-2">Career Path</h2>
              <input type="text"
                class="w-full border border-gray-300 rounded-md p-2 outline-none focus:ring-2 focus:ring-indigo-600"
                v-model="careerGoals.careerPath" placeholder="Enter your career path" />
            </div>
            <div class="flex space-x-4">
              <button class="bg-indigo-600 text-white px-6 py-3 rounded-lg flex items-center"
                @click="savedCareerGoals">
                <i class="fas fa-save mr-2"></i> Save Goals
              </button>
              <button class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg flex items-center hover:bg-gray-400"
                @click="resetCareerGoals">
                <i class="fas fa-undo mr-2"></i> Reset Goals
              </button>
            </div>
          </div>

            <!-- Add Industry Modal -->
            <div v-if="isAddIndustryModalOpen"
              class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
              <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                  <h2 class="text-xl font-semibold">Add Industry</h2>
                  <button class="text-gray-500 hover:text-gray-700" @click="closeAddIndustryModal">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <p class="text-gray-600 mb-4">Add a preferred industry of interest to your profile.</p>
                <form @submit.prevent="addPreferredIndustry">
                  <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Industry <span class="text-red-500">*</span></label>
                    <input type="text" v-model="newIndustry"
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                      placeholder="e.g. Technology, Healthcare" required />
                  </div>
                  <div class="flex justify-end">
                    <button type="submit"
                      class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Add</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Resume Section -->
          <div v-if="activeSection === 'resume'" class="flex flex-col lg:flex-row">
            <div class="w-full lg:w-1/1 mb-6 lg:mb-0">
              <h2 class="text-xl font-semibold mb-4">Resume</h2>
              <p class="text-gray-600 mb-4">Upload and manage your resume</p>
              <div class="bg-white p-6 rounded-lg shadow-md border border-gray-300">
                <!-- Display Uploaded Resume -->
                <div v-if="resume.file"
                  class="flex items-center justify-between border border-gray-300 rounded-lg p-8 mb-4">
                  <div class="flex items-center">
                    <i class="fas fa-file-alt text-gray-500 text-2xl mr-4"></i>
                    <span class="text-gray-700 font-medium">{{ resume.fileName }}</span>
                  </div>
                  <button class="text-red-600 hover:text-red-800" @click="removeResume">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>

                <!-- If No Resume Uploaded -->
                <div v-else class="flex items-center justify-between border border-gray-300 rounded-lg p-8 mb-4">
                  <div class="flex items-center">
                    <i class="fas fa-file-alt text-gray-500 text-2xl mr-4"></i>
                    <span class="text-gray-500">No resume uploaded yet</span>
                  </div>
                </div>

                <!-- Upload Resume Button -->
                <label for="resume-upload"
                  class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-600 text-center cursor-pointer block">
                  <i class="fas fa-upload mr-2"></i> Upload New Resume
                </label>
                <input type="file" id="resume-upload" class="hidden" accept=".pdf,.doc,.docx" @change="uploadResume" />

                <button v-if="resume.file" @click="saveResume"
                  class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-600 text-center mt-4">
                  <i class="fas fa-save mr-2"></i> Save Resume
                </button>
              </div>
            </div>
          </div>
        </div>

        <teleport to="body">
          <!-- <div v-if="isDeleteConfirmationModalOpen"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded shadow-lg">
              <h2 class="text-lg font-bold">Confirm Deletion</h2>
              <p>Are you sure you want to delete this item?</p>
              <div class="flex justify-end mt-4 space-x-2">
                <button @click="confirmDeletion"
                  class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Delete</button>
                <button @click="closeDeleteConfirmationModal"
                  class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
              </div>
            </div>
          </div> -->
          <div v-if="isProfileModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded shadow-lg">
              <h2 class="text-lg font-bold">Profile Information Updated</h2>
              <p>Your profile information has been successfully updated.</p>
              <button @click="closeProfileModal" class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded">Close</button>
            </div>
          </div>
          <div v-if="isNoChangesModalOpen"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded shadow-lg">
              <h2 class="text-lg font-bold">No Changes Made</h2>
              <p>Your profile information has not been changed.</p>
              <button @click="closeNoChangesModal"
                class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded">Close</button>
            </div>
          </div>
          <div v-if="isPasswordModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded shadow-lg">
              <h2 class="text-lg font-bold">Password Updated</h2>
              <p>Your password has been successfully updated.</p>
              <button @click="closePasswordModal" class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded">Close</button>
            </div>
          </div>
        </teleport>
      </div>
    </div>
  </Graduate>
</template>
