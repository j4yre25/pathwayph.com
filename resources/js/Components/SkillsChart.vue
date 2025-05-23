<template>
  <div class="bg-white rounded-lg shadow p-6 w-full float-right ml-6 mb-6">
    <div class="text-sm text-gray-600 mb-4">Breakdown of your skills by category</div>
    <h3 class="text-lg font-semibold mb-4">Skills Distribution</h3>
    <div class="h-[300px] relative">
      <apexchart
        type="pie"
        height="100%"
        :options="chartOptions"
        :series="series"
        class="absolute inset-0"
      />
    </div>
  </div>
</template>

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
      proficiency: proficiencyValue
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
const series = computed(() => Object.values(categoryAverages.value));

const chartOptions = computed(() => ({
  chart: {
    type: 'pie',
    toolbar: {
      show: false
    },
    fontFamily: 'inherit'
  },
  labels: Object.keys(categoryAverages.value),
  colors: ['#818cf8', '#6366f1', '#4f46e5', '#4338ca', '#3730a3'],
  legend: {
    position: 'bottom',
    fontSize: '14px',
    labels: {
      colors: '#6b7280'
    }
  },
  dataLabels: {
    enabled: true,
    formatter: function (val, opts) {
      return Math.round(val) + '%';
    }
  },
  tooltip: {
    custom: function(opts) {
      const category = opts.w.globals.labels[opts.seriesIndex];
      const skills = skillsByCategory.value[category] || [];
      const avgProficiency = opts.series[opts.seriesIndex];
      
      let content = `<div class="p-2">`;
      content += `<div class="font-semibold mb-2">${category}</div>`;
      content += `<div class="text-sm mb-2">Average Proficiency: ${avgProficiency}%</div>`;
      content += `<div class="text-sm">Skills:</div>`;
      content += `<ul class="list-disc pl-4 text-sm">`;
      skills.forEach(skill => {
        content += `<li>${skill.name} (${skill.proficiency}%)</li>`;
      });
      content += `</ul></div>`;
      
      return content;
    }
  }
}));
</script>