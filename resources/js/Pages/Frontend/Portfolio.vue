<template>
  <Graduate>
    <div class="bg-gray-100 min-h-screen p-6">
      <div class="container mx-auto">
        <h1 class="text-3xl font-bold mb-4">Profile Portfolio</h1>
        <p class="text-gray-600 mb-6">View your personal information and download your resume</p>
        
        <div v-for="(section, index) in sections" :key="index" class="bg-white rounded-lg shadow-md p-6 mb-6">
          <h2 class="text-xl font-semibold mb-4">{{ section.title }}</h2>
          <div v-if="section.type === 'general'">
            <div class="mb-4">
              <strong>Full Name:</strong> {{ profile.fullName }}
            </div>
            <div class="mb-4">
              <strong>Professional Title:</strong> {{ profile.graduate_professional_title }}
            </div>
            <div class="mb-4">
              <strong>Email:</strong> {{ profile.graduate_email }}
            </div>
            <div class="mb-4">
              <strong>Phone:</strong> {{ profile.graduate_phone }}
            </div>
            <div class="mb-4">
              <strong>Location:</strong> {{ profile.graduate_location }}
            </div>
            <div class="mb-4">
              <strong>Birthdate:</strong> {{ profile.graduate_birthdate }}
            </div>
            <div class="mb-4">
              <strong>Gender:</strong> {{ profile.graduate_gender }}
            </div>
            <div class="mb-4">
              <strong>Ethnicity:</strong> {{ profile.graduate_ethnicity }}
            </div>
            <div class="mb-4">
              <strong>Address:</strong> {{ profile.graduate_address }}
            </div>
            <div class="mb-4">
              <strong>About Me:</strong> {{ profile.graduate_about_me }}
            </div>
          </div>
          <div v-else-if="section.type === 'education'">
            <div v-if="educationEntries.length > 0">
              <ul>
                <li v-for="entry in educationEntries" :key="entry.id" class="mb-2">
                  <strong>{{ entry.graduate_education_institution_id }}</strong> - 
                  {{ entry.graduate_education_program }} in {{ entry.graduate_education_field_of_study }} 
                  ({{ entry.graduate_education_start_date }} - {{ entry.graduate_education_end_date }})
                </li>
              </ul>
            </div>
            <div v-else>
              <p>No education entries added yet.</p>
            </div>
          </div>
          <div v-else-if="section.type === 'skills'">
            <div v-if="skills.length > 0">
              <ul>
                <li v-for="skill in skills" :key="skill.graduate_skills_name" class="mb-2">
                  <strong>{{ skill.graduate_skills_name }}</strong> - 
                  Proficiency: {{ skill.graduate_skills_proficiency }}%
                </li>
              </ul>
            </div>
            <div v-else>
              <p>No skills added yet.</p>
            </div>
          </div>
          <div v-else-if="section.type === 'experience'">
            <div v-if="experienceEntries.length > 0">
              <ul>
                <li v-for="entry in experienceEntries" :key="entry.id" class="mb-2">
                  <strong>{{ entry.graduate_experience_title }}</strong> at 
                  {{ entry.graduate_experience_company }} 
                  ({{ entry.graduate_experience_start_date }} - {{ entry.graduate_experience_end_date }})
                </li>
              </ul>
            </div>
            <div v-else>
              <p>No experience entries added yet.</p>
            </div>
          </div>
          <div v-else-if="section.type === 'certifications'">
            <div v-if="certificationEntries.length > 0">
              <ul>
                <li v-for="entry in certificationEntries" :key="entry.id" class="mb-2">
                  <strong>{{ entry.graduate_certification_name }}</strong> - 
                  Issued by {{ entry.graduate_certification_issuer }}
                </li>
              </ul>
            </div>
            <div v-else>
              <p>No certifications added yet.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Graduate>
</template>

<script setup>
import { ref } from 'vue';

const profile = ref({
  fullName: 'John Doe',
  graduate_professional_title: 'Software Engineer',
  graduate_email: 'john.doe@example.com',
  graduate_phone: '123-456-7890',
  graduate_location: 'New York, NY',
  graduate_birthdate: '1990-01-01',
  graduate_gender: 'Male',
  graduate_ethnicity: 'Caucasian',
  graduate_address: '123 Main St, New York, NY',
  graduate_about_me: 'Passionate software engineer with 5 years of experience.'
});

const educationEntries = ref([
  {
    id: 1,
    graduate_education_institution_id: 'Harvard University',
    graduate_education_program: 'Bachelor of Science',
    graduate_education_field_of_study: 'Computer Science',
    graduate_education_start_date: '2010-09-01',
    graduate_education_end_date: '2014-05-01',
    graduate_education_description: 'Studied computer science and software engineering.'
  }
]);

const skills = ref([
  {
    graduate_skills_name: 'JavaScript',
    graduate_skills_proficiency: 90
  },
  {
    graduate_skills_name: 'Vue.js',
    graduate_skills_proficiency: 85
  }
]);

const experienceEntries = ref([
  {
    id: 1,
    graduate_experience_title: 'Frontend Developer',
    graduate_experience_company: 'Tech Corp',
    graduate_experience_start_date: '2015-06-01',
    graduate_experience_end_date: '2018-12-01',
    graduate_experience_address: 'New York, NY',
    graduate_experience_achievements: 'Developed multiple web applications.'
  }
]);

const certificationEntries = ref([
  {
    id: 1,
    graduate_certification_name: 'AWS Certified Solutions Architect',
    graduate_certification_issuer: 'Amazon',
    graduate_certification_date: '2020-01-01'
  }
]);

const sections = ref([
  {
    title: 'General Information',
    type: 'general'
  },
  {
    title: 'Education',
    type: 'education'
  },
  {
    title: 'Skills',
    type: 'skills'
  },
  {
    title: 'Experience',
    type: 'experience'
  },
  {
    title: 'Certifications',
    type: 'certifications'
  }
]);
</script>
