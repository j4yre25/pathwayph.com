<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const categories = [
  {
    code: 'A',
    title: 'Job Market & Openings Reports',
    reports: [
      { label: 'Job Openings Overview', route: 'company.reports.overview' },
      { label: 'Department-Wise Job Listings', route: 'company.reports.department' },
      { label: 'Job Posting Trends', route: 'company.reports.trends' },
      { label: 'Employment Type', route: 'company.reports.employmentType' },
      { label: 'Salary Insights', route: 'company.reports.salary' },
      { label: 'Job Posting Performance', route: 'company.reports.performance' },
    ]
  },
  {
    code: 'B',
    title: 'Recruitment Process & Pipeline Reports',
    reports: [
      { label: 'Hiring Funnel', route: 'company.reports.hiringFunnel' },
      { label: 'Application Analysis', route: 'company.reports.applicationAnalysis' },
      { label: 'Applicant Status Overview', route: 'company.reports.applicantStatus' },
      { label: 'Candidate Screening Insights', route: 'company.reports.screening' },
      { label: 'Interview Progress', route: 'company.reports.interviewProgress' },
      { label: 'Recruitment Efficiency', route: 'company.reports.efficiency' },
    ]
  },
  {
    code: 'C',
    title: 'Skills, Competency & Qualification Reports',
    reports: [
      { label: 'Skills and Qualifications', route: 'company.reports.skills' },
      { label: 'Skills and Competency Analysis', route: 'company.reports.competency' },
    ]
  },
  {
    code: 'D',
    title: 'Graduate Insights & Matching Reports',
    reports: [
      { label: 'Graduate Pool Overview', route: 'company.reports.graduatePool' },
      { label: 'Graduate Demographics', route: 'company.reports.graduateDemographics' },
      { label: 'Employment Preferences', route: 'company.reports.preferences' },
      { label: 'Matching Success Rate', route: 'company.reports.matchingSuccess' },
      { label: 'Internship Experience', route: 'company.reports.internship' },
      { label: 'Future Potential', route: 'company.reports.futurePotential' },
    ]
  },
  {
    code: 'E',
    title: 'Feedback & Inclusion Reports',
    reports: [
      { label: 'Diversity and Inclusion', route: 'company.reports.diversity' },
      { label: 'Employer Feedback', route: 'company.reports.employerFeedback' },
      { label: 'Feedback and Satisfaction', route: 'company.reports.feedback' },
    ]
  },
]

const search = ref('')
const flatReports = computed(() =>
  categories.flatMap(c =>
    c.reports.map(r => ({ ...r, category: c.title, code: c.code }))
  )
)

const filteredCategories = computed(() => {
  if (!search.value.trim()) return categories
  const term = search.value.toLowerCase()
  return categories
    .map(c => ({
      ...c,
      reports: c.reports.filter(r => r.label.toLowerCase().includes(term))
    }))
    .filter(c => c.reports.length)
})

function go(routeName) {
  router.visit(route(routeName))
}
</script>

<template>
  <AppLayout title="Reports">
    <div class="flex flex-col md:flex-row gap-8 py-10 max-w-7xl mx-auto px-4">
      <!-- Sidebar (category quick nav) -->
      <aside class="md:w-56 flex-shrink-0">
        <div class="sticky top-4 space-y-4">
          <div>
            <input
              v-model="search"
              type="text"
              placeholder="Search reports..."
              class="w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
            />
          </div>
          <nav class="space-y-2">
            <div
              v-for="cat in categories"
              :key="cat.code"
              class="text-sm"
            >
              <a
                :href="'#cat-' + cat.code"
                class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-gray-700"
              >
                {{ cat.code }}. {{ cat.title }}
              </a>
            </div>
          </nav>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="flex-1">
        <h1 class="text-2xl font-bold mb-6">Company Reports</h1>

        <div
          v-for="cat in filteredCategories"
          :key="cat.code"
          :id="'cat-' + cat.code"
          class="mb-10"
        >
          <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
            <span class="inline-block bg-indigo-600 text-white text-xs font-bold px-2 py-1 rounded">
              {{ cat.code }}
            </span>
            {{ cat.title }}
          </h2>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
              <div
                v-for="report in cat.reports"
                :key="report.route"
                @click="go(report.route)"
                class="group cursor-pointer rounded-lg border bg-white p-4 shadow-sm hover:shadow transition hover:border-indigo-300"
              >
                <div class="font-medium text-gray-800 group-hover:text-indigo-600 text-sm">
                  {{ report.label }}
                </div>
                <div class="mt-1 text-xs text-gray-500">
                  {{ cat.title }}
                </div>
              </div>
            </div>
        </div>

        <div v-if="!filteredCategories.length" class="text-gray-500 text-sm">
          No reports match your search.
        </div>
      </main>
    </div>
  </AppLayout>
</template>