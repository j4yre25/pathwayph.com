<script setup>
import { ref, computed, onMounted } from 'vue';
import { default as ApexCharts } from 'vue3-apexcharts';

// Register the ApexCharts component
const apexchart = ApexCharts;

const props = defineProps({
  skills: {
    type: Array,
    required: true,
    default: () => []
  }
});

// Group skills by type and calculate proficiency
const skillsByCategory = computed(() => {
  const categories = {};
  props.skills.forEach(skill => {
    if (!categories[skill.graduate_skills_type]) {
      categories[skill.graduate_skills_type] = [];
    }
    // Convert proficiency type to percentage
    let proficiencyValue;
    switch (skill.graduate_skills_proficiency_type) {
      case 'Beginner': proficiencyValue = 25; break;
      case 'Intermediate': proficiencyValue = 50; break;
      case 'Advanced': proficiencyValue = 75; break;
      case 'Expert': proficiencyValue = 100; break;
      default: proficiencyValue = 0;
    }
    categories[skill.graduate_skills_type].push({
      name: skill.graduate_skills_name,
      proficiency: proficiencyValue,
      years: skill.graduate_skills_years_experience
    });
  });
  return categories;
});

// Calculate average proficiency for each category
const categoryAverages = computed(() => {
  const averages = {};
  Object.entries(skillsByCategory.value).forEach(([category, skills]) => {
    const total = skills.reduce((sum, skill) => sum + skill.proficiency, 0);
    averages[category] = Math.round(total / skills.length);
  });
  return averages;
});

// Prepare data for pie chart
const chartSeries = computed(() => {
  return Object.values(categoryAverages.value);
});

const chartLabels = computed(() => {
  return Object.keys(categoryAverages.value);
});

// Get detailed skills for tooltips
const categoryDetails = computed(() => {
  const details = {};
  Object.entries(skillsByCategory.value).forEach(([category, skills]) => {
    details[category] = skills.map(skill => `${skill.name} (${skill.proficiency}%)`);
  });
  return details;
});

// Chart options
const chartOptions = computed(() => {
  return {
    chart: {
      type: 'pie',
      fontFamily: 'Inter, sans-serif',
    },
    labels: chartLabels.value,
    colors: ['#2563eb', '#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe', '#dbeafe'],
    legend: {
      position: 'bottom',
      fontWeight: 600,
      formatter: function(seriesName, opts) {
        const percent = opts.w.globals.series[opts.seriesIndex];
        return `${seriesName}: ${percent}%`;
      }
    },
    tooltip: {
      y: {
        formatter: function(value, { seriesIndex, dataPointIndex, w }) {
          const category = chartLabels.value[seriesIndex];
          const skills = categoryDetails.value[category];
          
          let tooltip = `<div class="p-2">`;
          tooltip += `<div class="font-bold text-blue-800">${category}: ${value}%</div>`;
          tooltip += `<div class="text-sm mt-2">Skills in this category:</div>`;
          tooltip += `<ul class="text-xs mt-1">`;
          
          skills.forEach(skill => {
            tooltip += `<li>• ${skill}</li>`;
          });
          
          tooltip += `</ul></div>`;
          return tooltip;
        }
      }
    },
    responsive: [{
      breakpoint: 480,
      options: {
        chart: {
          height: 300
        },
        legend: {
          position: 'bottom'
        }
      }
    }],
    dataLabels: {
      enabled: true,
      formatter: function(val, opts) {
        return `${val.toFixed(0)}%`;
      },
      style: {
        fontSize: '12px',
        fontWeight: 'bold',
        colors: ['#fff']
      },
      dropShadow: {
        enabled: true,
        blur: 3,
        opacity: 0.3
      }
    }
  };
});
</script>

<template>
  <div class="skills-chart-container">
    <div class="bg-white rounded-xl shadow-sm border border-blue-50 overflow-hidden">
      <div class="p-6 border-b border-blue-100 flex items-center">
        <div class="bg-blue-100 text-blue-700 p-2 rounded-full mr-3">
          <i class="fas fa-chart-pie"></i>
        </div>
        <h2 class="text-lg font-semibold text-blue-800">Skills Distribution</h2>
      </div>
      <div class="p-6">
        <div v-if="chartSeries.length > 0" class="chart-wrapper">
          <apexchart
            type="pie"
            height="350"
            :options="chartOptions"
            :series="chartSeries"
          ></apexchart>
        </div>
        <div v-else class="flex items-center justify-center h-64 text-gray-500">
          <p>No skills data available</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.chart-wrapper {
  height: 350px;
}
</style>