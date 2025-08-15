<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';

const reports = [
    { label: "Academic Performance", route: "company.reports.academicPerformance" },
    { label: "Application Analysis", route: "company.reports.applicationAnalysis" },
    { label: "Applicants Status Overview", route: "company.reports.applicantStatus" },
    { label: "Candidate Screening Insights", route: "company.reports.screening" },
    { label: "Department-Wise Job Listings", route: "company.reports.department" },
    { label: "Diversity and Inclusion", route: "company.reports.diversity" },
    { label: "Employer Feedback", route: "company.reports.employerFeedback" },
    { label: "Employment Preferences", route: "company.reports.preferences" },
    { label: "Employment Type", route: "company.reports.employmentType" },
    { label: "Feedback and Satisfication", route: "company.reports.feedback" },
    { label: "Future Potential", route: "company.reports.futurePotential" },
    { label: "Graduate Demographics", route: "company.reports.graduateDemographics" },
    { label: "Graduate Pool Overview", route: "company.reports.graduatePool" },
    { label: "Hiring Funnel", route: "company.reports.hiringFunnel" },
    { label: "Interview Progress", route: "company.reports.interviewProgress" },
    { label: "Internship Experience", route: "company.reports.internship" },
    { label: "Job Openings Overview", route: "company.reports.overview" },
    { label: "Job Posting Performance", route: "company.reports.performance" },
    { label: "Job Posting Trends", route: "company.reports.trends" },
    { label: "Matching Success Rate", route: "company.reports.matchingSuccess" },
    { label: "Professional Certification Tracking", route: "company.reports.certificationTracking" },
    { label: "Recruitment Efficiency", route: "company.reports.efficiency" },
    { label: "Salary Insights", route: "company.reports.salary" },
    { label: "School-wise Graduate Employability", route: "company.reports.schoolWise" },
    { label: "Skill and Qualification Analysis", route: "company.reports.skills" },
    { label: "Skills and Competency Analysis", route: "company.reports.competency" }
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