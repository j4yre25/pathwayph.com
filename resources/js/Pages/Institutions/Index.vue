<template>
    <AppLayout title="Institution Management">
      <template #header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Institution Management</h2>
      </template>
  
      <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="bg-white shadow-xl rounded-2xl p-6">
  
            <!-- Tab Navigation -->
            <div class="flex border-b border-gray-200 mb-6 space-x-6">
              <button
                v-for="tab in tabs"
                :key="tab"
                @click="selectedTab = tab"
                :class="[
                  'pb-2 px-1 text-sm font-medium',
                  selectedTab === tab
                    ? 'text-blue-600 border-b-2 border-blue-600'
                    : 'text-gray-500 hover:text-gray-700'
                ]"
              >
                {{ tab }}
              </button>
            </div>
  
            <!-- Dynamic Tab Content -->
            <div v-if="selectedTab === 'School Years'">
              <SchoolYearsTab :schoolYears="schoolYears" />
            </div>
            <div v-else-if="selectedTab === 'Degrees'">
              <DegreesTab :degrees="degrees" />
            </div>
            <div v-else-if="selectedTab === 'Programs'">
              <ProgramsTab :programs="programs" :degrees="degrees" />
            </div>
            <div v-else-if="selectedTab === 'Career Opportunities'">
              <CareerOpportunitiesTab :careerOpportunities="careerOpportunities" :programs="programs" />
            </div>
            <div v-else-if="selectedTab === 'Skills'">
              <SkillsTab :skills="skills" :programs="programs" :careerOpportunities="careerOpportunities" />
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  import AppLayout from '@/Layouts/AppLayout.vue'
  import SchoolYearsTab from '@/Pages/Institutions/SchoolYearsTab.vue'
  import DegreesTab from '@/Pages/Institutions/DegreesTab.vue'
  import ProgramsTab from '@/Pages/Institutions/ProgramsTab.vue'
  import CareerOpportunitiesTab from '@/Pages/Institutions/CareerOpportunitiesTab.vue'
  import SkillsTab from '@/Pages/Institutions/SkillsTab.vue'
  
  defineProps({
    schoolYears: Array,
    degrees: Array,
    programs: Array,
    careerOpportunities: Array,
    skills: Array
  })
  
  const tabs = ['School Years', 'Degrees', 'Programs', 'Career Opportunities', 'Skills']
  const selectedTab = ref('School Years')
  </script>
  