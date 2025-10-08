<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const categories = [
  {
    code: 'A',
    title: 'Job Market & Openings Reports',
    reports: [
      // { 
      //   label: 'Job Openings Overview', 
      //   route: 'company.reports.overview',
      //   description: 'Shows total job openings, active listings, and roles filled.'
      // },
      // { 
      //   label: 'Department-Wise Job Listings', 
      //   route: 'company.reports.department',
      //   description: 'Breaks down job openings across departments and role levels.'
      // },
      // { 
      //   label: 'Job Posting Trends', 
      //   route: 'company.reports.trends',
      //   description: 'Tracks job postings over time and by department activity.'
      // },
      // { 
      //   label: 'Employment Type', 
      //   route: 'company.reports.employmentType',
      //   description: 'Displays job openings by type (full-time, part-time, freelance,  etc.).'
      // },
      // { 
      //   label: 'Salary Insights', 
      //   route: 'company.reports.salary',
      //   description: 'Analyzes salary ranges and distribution across roles and departments.'
      // },
      // { 
      //   label: 'Job Posting Performance', 
      //   route: 'company.reports.performance',
      //   description: 'Evaluates applicant diversity and engagement for job postings.'
      // },
    ]
  },
  {
    code: 'B',
    title: 'Recruitment Process & Pipeline Reports',
    reports: [
      // { 
      //   label: 'Hiring Funnel', 
      //   route: 'company.reports.hiringFunnel',
      //   description: 'Visualizes the recruitment pipeline from applications to hires.'
      // },
      // { 
      //   label: 'Application Analysis', 
      //   route: 'company.reports.applicationAnalysis',
      //   description: 'Examines volume, trends, and patterns of job applications.'
      // },
      // { 
      //   label: 'Applicant Status Overview', 
      //   route: 'company.reports.applicantStatus',
      //   description: 'Summarizes candidate statuses such as applied, screened, or hired.'
      // },
      // { 
      //   label: 'Candidate Screening Insights', 
      //   route: 'company.reports.screening',
      //   description: 'Shows how candidates are screened by qualifications and skills.'
      // },
      // { 
      //   label: 'Interview Progress', 
      //   route: 'company.reports.interviewProgress',
      //   description: 'Tracks candidates through different interview stages.'
      // },
      // { 
      //   label: 'Recruitment Efficiency', 
      //   route: 'company.reports.efficiency',
      //   description: 'Measures time and success rate across recruitment stages.'
      // },
    ]
  },
  {
    code: 'C',
    title: 'Skills, Competency & Qualification Reports',
    reports: [
      { 
        label: 'Skills and Qualifications', 
        route: 'company.reports.skills',
        description: 'Highlights required skills in jobs versus skills in the talent pool.'
      },
      { 
        label: 'Skills and Competency Analysis', 
        route: 'company.reports.competency',
        description: 'Compares candidate skill levels with job role requirements.'
      },
    ]
  },
  {
    code: 'D',
    title: 'Graduate Insights & Matching Reports',
    reports: [
      { 
        label: 'Graduate Pool Overview', 
        route: 'company.reports.graduatePool',
        description: 'Shows total graduates, matches to jobs, and average qualifications.'
      },
      // { 
      //   label: 'Graduate Demographics', 
      //   route: 'company.reports.graduateDemographics',
      //   description: 'Breaks down graduates by age, gender, education, and location.'
      // },
      { 
        label: 'Employment Preferences', 
        route: 'company.reports.preferences',
        description: 'Summarizes graduates’ preferred job types and industries.'
      },
      // { 
      //   label: 'Matching Success Rate', 
      //   route: 'company.reports.matchingSuccess',
      //   description: 'Tracks how many graduates match and get placed in job roles.'
      // },
      // { 
      //   label: 'Internship Experience', 
      //   route: 'company.reports.internship',
      //   description: 'Analyzes internship backgrounds and links them to hiring outcomes.'
      // },
      // { 
      //   label: 'Future Potential', 
      //   route: 'company.reports.futurePotential',
      //   description: 'Forecasts talent growth and high-potential fields of study.'
      // },
    ]
  },
  // {
  //   code: 'E',
  //   title: 'Feedback & Inclusion Reports',
  //   reports: [
  //     { 
  //       label: 'Diversity and Inclusion', 
  //       route: 'company.reports.diversity',
  //       description: 'Evaluates inclusivity in job postings and workforce composition.'
  //     },
  //     // { 
  //     //   label: 'Employer Feedback', 
  //     //   route: 'company.reports.employerFeedback',
  //     //   description: 'Summarizes employer evaluations of graduate skills and readiness.'
  //     // },
  //     { 
  //       label: 'Feedback and Satisfaction', 
  //       route: 'company.reports.feedback',
  //       description: 'Analyzes candidate feedback on recruitment experience.'
  //     },
  //   ]
  // },
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
                  {{ report.description }}
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