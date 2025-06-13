<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';

const reports = [
    { label: "Job Openings Overview", route: "company.reports.overview" },
    { label: "Department-Wise Job Listings", route: "company.reports.department" },
    { label: "Hiring Funnel", route: "company.reports.hiringFunnel" },
    { label: "Job Posting Trends", route: "company.reports.trends" },
    { label: "Application Analysis", route: "company.reports.applicationAnalysis" },
    { label: "Skill and Qualification Analysis", route: "company.reports.skills" },
    { label: "Employment Type", route: "company.reports.employmentType" },
    { label: "Salary Insights", route: "company.reports.salary" },
    { label: "Diversity and Inclusion", route: "company.reports.diversity" },
    { label: "Applicants Status Overview", route: "company.reports.applicantStatus"},
    { label: "Candidate Screening Insights", route: "company.reports.screening"},
    { label: "Interview Progress", route: "company.reports.interviewProgress" },
    { label: "Skills and Competency Analysis", route: "company.reports.competency" },
    { label: "Recruitment Efficiency", route: "company.reports.efficiency" },
    { label: "Job Posting Performance", route: "company.reports.performance" },
    { label: "Feedback and Satisfication", route: "company.reports.feedback" },
    { label: "Graduate Pool Overview", route: "company.reports.graduatePool" },
    { label: "Graduate Demographics", route: "company.reports.graduateDemographics" },
    { label: "Academic Performance", route: "company.reports.academicPerformance" },
    { label: "Employment Preferences", route: "company.reports.preferences" },
    { label: "Matching Success Rate", route: "company.reports.matchingSuccess" },
    { label: "Internship Experience", route: "company.reports.internship" },
    { label: "Employer Feedback", route: "company.reports.employerFeedback" },
    { label: "Future Potential", route: "company.reports.futurePotential" },
];

function goToReport(routeName) {
  router.visit(route(routeName));
}
</script>

<template>
  <AppLayout title="Reports">
    <div class="max-w-3xl mx-auto py-10">
      <h1 class="text-2xl font-bold mb-6">Available Reports</h1>
      <table class="min-w-full bg-white border rounded shadow">
        <thead>
          <tr>
            <th class="py-2 px-4 border-b text-left">Report Name</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="report in reports"
            :key="report.route"
            class="hover:bg-gray-100 cursor-pointer transition"
            @click="goToReport(report.route)"
          >
            <td class="py-2 px-4 border-b">{{ report.label }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>