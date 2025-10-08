<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, onMounted, computed, watch } from 'vue'
import VueECharts from 'vue-echarts'
import axios from 'axios'
import Container from '@/Components/Container.vue'
import MultiSelect from '@/Components/MultiSelect.vue';
import { usePage } from '@inertiajs/vue3'


const page = usePage()
const institutions = ref(page.props.institutions ?? [])
const graduates = computed(() => analyticsData.value.graduates ?? []);

const industries = ref(page.props.industries ?? []);
const experienceLevels = [
    { id: 'Entry', name: 'Entry' },
    { id: 'Mid', name: 'Mid' },
    { id: 'Senior', name: 'Senior' }
];

const selectedIndustries = ref([]);
const selectedExperienceLevels = ref([]);
const dateFromSalary = ref('');
const dateToSalary = ref('');


console.log('Institutions:', institutions.value);
const programs = ref(page.props.programs ?? [])
const selectedYear = ref('');
const selectedPrograms = ref([]);
const selectedInstitutions = ref([]);
const selectedSkillTypes = ref([]);
const years = computed(() => {
    const set = new Set();
    graduates.value.forEach(g => {
        if (g.school_year?.school_year_range) set.add(g.school_year.school_year_range);
    });
    return Array.from(set).sort().reverse();
});

const programOptions = computed(() =>
    Array.isArray(programs.value)
        ? programs.value
            .filter(p => p && (p.name || typeof p === 'string'))
            .map(program => ({
                id: program.id ?? program.name ?? program,
                name: program.name ?? program
            }))
        : []
);

const institutionOptions = computed(() =>
    Array.isArray(institutions.value)
        ? institutions.value
            .filter(i => i && (i.name || typeof i === 'string'))
            .map(inst => ({
                id: inst.id ?? inst.name,
                name: inst.name ?? inst
            }))
        : []
);

const skillTypeOptions = [
    { id: 'Technical Skills', name: 'Technical Skills' },
    { id: 'Soft Skills', name: 'Soft Skills' },
    { id: 'Language Skills', name: 'Language Skills' }
];
const loading = ref(false)
const analyticsData = ref({})

const reportType = ref('education'); // 'education' or 'salary'

const reportTypes = [
    { key: 'education', label: 'Educational Impact' },
    { key: 'salary', label: 'Salary Insights' }
];

function fetchCareerData() {
    loading.value = true
    axios.get(route('peso.reports.careers.data'))
        .then(res => {
            analyticsData.value = res.data
            console.log('Career Analytics:', res.data)
            if (res.data.graduates) {
                res.data.graduates.forEach(g => {
                    console.log('Graduate object:', g);
                });
            }
        })
        .finally(() => {
            loading.value = false
        })
}

onMounted(fetchCareerData)


const filteredBarChartData = computed(() => {
    let data = analyticsData.value.barChartData ?? [];
    if (selectedYear.value) {
        data = data.filter(d => String(d.year) === String(selectedYear.value));
    }
    if (selectedPrograms.value.length) {
        const selected = selectedPrograms.value.map(p => p.name);
        data = data.filter(d => selected.includes(d.program));
    }
    if (selectedInstitutions.value.length) {
        const selected = selectedInstitutions.value.map(i => i.name);
        data = data.filter(d =>
            selected.includes(d.institution) ||
            selected.includes(d.institution_name)
        );
    }
    data = data.filter(d => d.employment_rate > 0);
    return Array.isArray(data) ? data : [];
});

const filteredRadarData = computed(() => {
    let data = analyticsData.value.radarData ?? [];
    if (selectedPrograms.value.length) {
        const selected = selectedPrograms.value.map(p => p.name);
        data = data.filter(d => selected.includes(d.name));
    }
    // Skill Type filter (optional, only if you want to filter radar indicators)
    if (selectedSkillTypes.value.length) {
        const selectedSkills = selectedSkillTypes.value.map(s => s.name);
        // Filter radar indicators and values to only selected skill types
        // This requires you to also filter radarOption's indicators and each data.value
        // If not needed, you can skip this part
    }
    return Array.isArray(data) ? data : [];
});

// Bar Chart: Employment Rate by Education Level
const barOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    xAxis: {
        type: 'category',
        data: filteredBarChartData.value.map(d => d.education),
        axisLabel: { rotate: 20 }
    },
    yAxis: { type: 'value', name: 'Employment Rate (%)' },
    series: [{
        name: 'Employment Rate',
        type: 'bar',
        data: filteredBarChartData.value.map(d => d.employment_rate),
        itemStyle: { color: '#3b82f6' }
    }]
}))



// Radar Chart: Skills Development vs Employability by Program
const radarOption = computed(() => ({
    tooltip: { trigger: 'item' },
    legend: {
        data: filteredRadarData.value.map(d => d.name),
        bottom: 0,
        type: 'scroll',
        orient: 'horizontal',
        itemWidth: 18,
        itemHeight: 14,
        textStyle: { fontSize: 12 }
    },
    radar: {
        indicator: (analyticsData.value.radarSkills ?? []).map(skill => ({
            name: skill, max: 100
        })),
        radius: 120
    },
    series: [{
        type: 'radar',
        data: filteredRadarData.value,
        areaStyle: { opacity: 0.1 }
    }]
}));
const filteredIndustrySalaryBoxData = computed(() => {
    let data = analyticsData.value.industrySalaryBoxData ?? [];
    if (selectedIndustries.value.length) {
        const selected = selectedIndustries.value.map(i => i.name);
        data = data.filter(d => selected.includes(d.industry));
    }
    if (dateFromSalary.value) {
        data = data.filter(d => !d.date || d.date >= dateFromSalary.value);
    }
    if (dateToSalary.value) {
        data = data.filter(d => !d.date || d.date <= dateToSalary.value);
    }
    return data;
});

// Box Plot: Salary Ranges Across Industries
const boxPlotOption = computed(() => ({
    tooltip: { trigger: 'item' },
    legend: { data: ['Salary Range'] },
    xAxis: {
        type: 'category',
        data: (filteredIndustrySalaryBoxData.value ?? []).map(d => d.industry),
        axisLabel: { rotate: 20 }
    },
    yAxis: { type: 'value', name: 'Salary' },
    series: [{
        name: 'Salary Range',
        type: 'boxplot',
        data: (filteredIndustrySalaryBoxData.value ?? []).map(d => {
            // ECharts expects [min, Q1, median, Q3, max]
            const arr = d.all ?? [];
            arr.sort((a, b) => a - b);
            const min = arr[0] ?? 0;
            const max = arr[arr.length - 1] ?? 0;
            const q1 = arr[Math.floor(arr.length * 0.25)] ?? min;
            const median = arr[Math.floor(arr.length * 0.5)] ?? min;
            const q3 = arr[Math.floor(arr.length * 0.75)] ?? max;
            return [min, q1, median, q3, max];
        }),
        itemStyle: { color: '#f59e42' }
    }]
}));


const filteredIndustryLevelSalaries = computed(() => {
    let data = analyticsData.value.industryLevelSalaries ?? [];
    if (selectedIndustries.value.length) {
        const selected = selectedIndustries.value.map(i => i.name);
        data = data.filter(d => selected.includes(d.industry));
    }
    if (selectedExperienceLevels.value.length) {
        const selectedLevels = selectedExperienceLevels.value.map(e => e.name);
        // Optionally filter series or just show selected levels in chart
        data = data.map(d => {
            let filtered = { industry: d.industry };
            selectedLevels.forEach(level => {
                filtered[level] = d[level];
            });
            return filtered;
        });
    }
    if (dateFromSalary.value) {
        data = data.filter(d => !d.date || d.date >= dateFromSalary.value);
    }
    if (dateToSalary.value) {
        data = data.filter(d => !d.date || d.date <= dateToSalary.value);
    }
    return data;
});


// Stacked Bar: Entry/Mid/Senior Salary per Industry
const stackedBarOption = computed(() => ({
    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
    legend: { data: ['Entry', 'Mid', 'Senior'] },
    xAxis: {
        type: 'category',
        data: (filteredIndustryLevelSalaries.value ?? []).map(d => d.industry),
        axisLabel: { rotate: 20 }
    },
    yAxis: { type: 'value', name: 'Average Salary' },
    series: [
        {
            name: 'Entry',
            type: 'bar',
            stack: 'salary',
            data: (filteredIndustryLevelSalaries.value ?? []).map(d => d.Entry),
            itemStyle: { color: '#60a5fa' }
        },
        {
            name: 'Mid',
            type: 'bar',
            stack: 'salary',
            data: (analyticsData.value.industryLevelSalaries ?? []).map(d => d.Mid),
            itemStyle: { color: '#34d399' }
        },
        {
            name: 'Senior',
            type: 'bar',
            stack: 'salary',
            data: (analyticsData.value.industryLevelSalaries ?? []).map(d => d.Senior),
            itemStyle: { color: '#f472b6' }
        }
    ]
}));

const filteredSalaryExpectations = computed(() => {
    let data = analyticsData.value.salaryExpectations ?? {};
    // If your salary data has date/industry, filter here
    // Otherwise, just use as is
    return data;
});

watch([filteredBarChartData, filteredRadarData], ([bar, radar]) => {
    console.log('Filtered Bar Chart Data:', bar)
    console.log('Filtered Radar Data:', radar)
})


// Histogram: Salary Expectations vs Offered

function binData(data, binSize = 10000) {
    if (!Array.isArray(data)) data = Object.values(data ?? {});
    if (!data.length) return [];
    const min = Math.min(...data);
    const max = Math.max(...data);
    const bins = [];
    for (let i = min; i <= max; i += binSize) {
        bins.push({
            bin: `${i}-${i + binSize - 1}`,
            count: 0
        });
    }
    data.forEach(val => {
        const idx = Math.floor((val - min) / binSize);
        if (bins[idx]) bins[idx].count++;
    });
    return bins;
}

const graduateMinBins = computed(() => binData(analyticsData.value.salaryExpectations?.graduateMin));
const graduateMaxBins = computed(() => binData(analyticsData.value.salaryExpectations?.graduateMax));
const jobMinBins = computed(() => binData(analyticsData.value.salaryExpectations?.jobMin));
const jobMaxBins = computed(() => binData(analyticsData.value.salaryExpectations?.jobMax));

const allBins = computed(() => {
    // Get all unique bin labels
    const labels = new Set([
        ...graduateMinBins.value.map(b => b.bin),
        ...graduateMaxBins.value.map(b => b.bin),
        ...jobMinBins.value.map(b => b.bin),
        ...jobMaxBins.value.map(b => b.bin),
    ]);
    return Array.from(labels).sort((a, b) => {
        // Sort numerically by bin start
        return parseInt(a.split('-')[0]) - parseInt(b.split('-')[0]);
    });
});

const histogramOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    legend: { data: ['Graduate Min', 'Graduate Max', 'Job Min', 'Job Max'] },
    xAxis: { type: 'category', name: 'Salary', data: allBins.value },
    yAxis: { type: 'value', name: 'Count' },
    series: [
        {
            name: 'Graduate Min',
            type: 'bar',
            data: allBins.value.map(bin =>
                binData(filteredSalaryExpectations.value.graduateMin).find(b => b.bin === bin)?.count ?? 0
            ),
            itemStyle: { color: '#60a5fa', opacity: 0.7 }
        },
        {
            name: 'Graduate Max',
            type: 'bar',
            data: allBins.value.map(bin =>
                binData(filteredSalaryExpectations.value.graduateMax).find(b => b.bin === bin)?.count ?? 0
            ),
            itemStyle: { color: '#3b82f6', opacity: 0.7 }
        },
        {
            name: 'Job Min',
            type: 'bar',
            data: allBins.value.map(bin =>
                binData(filteredSalaryExpectations.value.jobMin).find(b => b.bin === bin)?.count ?? 0
            ),
            itemStyle: { color: '#fbbf24', opacity: 0.7 }
        },
        {
            name: 'Job Max',
            type: 'bar',
            data: allBins.value.map(bin =>
                binData(filteredSalaryExpectations.value.jobMax).find(b => b.bin === bin)?.count ?? 0
            ),
            itemStyle: { color: '#f59e42', opacity: 0.7 }
        }
    ]
}));

// Education Analytics Summary
const educationSummary = computed(() => {
    const bar = filteredBarChartData.value;
    const radar = filteredRadarData.value;
    let summary = "<ul class='list-disc ml-6 mb-2'>";

    // Bar Chart: Employment Rate by Education
    if (bar.length) {
        const top = bar.reduce((a, b) => (a.employment_rate > b.employment_rate ? a : b));
        summary += `<li>Highest employment rate: <strong>${top.education}</strong> (${top.employment_rate}%)</li>`;
        const low = bar.reduce((a, b) => (a.employment_rate < b.employment_rate ? a : b));
        summary += `<li>Lowest employment rate: <strong>${low.education}</strong> (${low.employment_rate}%)</li>`;
    } else {
        summary += `<li>No employment rate data for selected filters.</li>`;
    }

    // Radar Chart: Skills Development vs Employability
    if (radar.length) {
        const topProgram = radar.reduce((a, b) => (a.value?.reduce((x, y) => x + y, 0) > b.value?.reduce((x, y) => x + y, 0) ? a : b));
        summary += `<li>Program with strongest skills-employability: <strong>${topProgram.name}</strong></li>`;
    } else {
        summary += `<li>No skills-employability data for selected filters.</li>`;
    }

    summary += "</ul>";
    return summary;
});

// Salary Analytics Summary
const salarySummary = computed(() => {
    const box = filteredIndustrySalaryBoxData.value ?? [];
    const stacked = filteredIndustryLevelSalaries.value ?? [];
    const hist = filteredSalaryExpectations.value ?? {};
    let summary = "<ul class='list-disc ml-6 mb-2'>";

    // Box Plot: Salary Ranges
    if (box.length) {
        const top = box.reduce((a, b) => (Math.max(...(a.all ?? [0])) > Math.max(...(b.all ?? [0])) ? a : b));
        summary += `<li>Highest salary range: <strong>${top.industry}</strong></li>`;
        const low = box.reduce((a, b) => (Math.min(...(a.all ?? [0])) < Math.min(...(b.all ?? [0])) ? a : b));
        summary += `<li>Lowest salary range: <strong>${low.industry}</strong></li>`;
    } else {
        summary += `<li>No salary range data for selected filters.</li>`;
    }

    // Stacked Bar: Salary by Experience Level
    if (stacked.length) {
        const topEntry = stacked.reduce((a, b) => (a.Entry > b.Entry ? a : b));
        summary += `<li>Highest entry-level salary: <strong>${topEntry.industry}</strong> (₱${topEntry.Entry})</li>`;
        const topSenior = stacked.reduce((a, b) => (a.Senior > b.Senior ? a : b));
        summary += `<li>Highest senior-level salary: <strong>${topSenior.industry}</strong> (₱${topSenior.Senior})</li>`;
    } else {
        summary += `<li>No experience-level salary data for selected filters.</li>`;
    }

    // Histogram: Salary Expectations vs Offered
    if (hist.graduateMin && hist.jobMin) {
        const gradMin = Math.min(...Object.values(hist.graduateMin));
        const jobMin = Math.min(...Object.values(hist.jobMin));
        summary += `<li>Lowest expected salary: <strong>₱${gradMin}</strong></li>`;
        summary += `<li>Lowest offered salary: <strong>₱${jobMin}</strong></li>`;
    } else {
        summary += `<li>No salary expectation data for selected filters.</li>`;
    }

    summary += "</ul>";
    return summary;
});


</script>

<template>
    <AppLayout title="Educational Impact Analytics">
        <Container class="py-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Career Reports</h2>


            <div class="flex justify-center mb-8">
                <div class="inline-flex rounded-lg shadow bg-gray-100 overflow-hidden">
                    <button v-for="type in reportTypes" :key="type.key" @click="reportType = type.key" :class="[
                        'px-6 py-2 text-sm font-semibold focus:outline-none transition',
                        reportType === type.key
                            ? 'bg-white text-blue-600 shadow'
                            : 'text-gray-500 hover:bg-gray-200'
                    ]">
                        {{ type.label }}
                    </button>
                </div>
            </div>


            <div v-if="reportType === 'education'"
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-filter text-blue-500 mr-2"></i>
                    Filter Reports
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                        <select v-model="selectedYear"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                            <option value="">All Years</option>
                            <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Program</label>
                        <MultiSelect v-model="selectedPrograms" :options="programOptions" label="name" track-by="id"
                            :searchable="true" :multiple="true" placeholder="Select programs" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Institution</label>
                        <MultiSelect v-model="selectedInstitutions" :options="institutionOptions" label="name"
                            track-by="id" :searchable="true" :multiple="true" placeholder="Select institutions" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Skill Type</label>
                        <MultiSelect v-model="selectedSkillTypes" :options="skillTypeOptions" label="name" track-by="id"
                            :searchable="true" :multiple="true" placeholder="Select skill types" />
                    </div>
                </div>
                <!-- Bar Chart: Employment Rate by Education Level -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-blue-500"></i>
                        Employment Rate by Education Level <span class="font-bold text-blue-600">(Bar Chart)</span>
                    </h3>
                    <div v-if="loading" class="flex justify-center items-center py-16">
                        <span class="loader mr-3"></span>
                        <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                    </div>
                    <div v-else>
                        <VueECharts v-if="filteredBarChartData.length" :option="barOption"
                            style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No bar chart data available.</div>
                    </div>
                </div>

                <!-- Radar Chart: Skills Development vs Employability -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8 ">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-chart-pie text-green-500"></i>
                        Skills Development vs Employability <span class="font-bold text-green-600">(Radar Chart)</span>
                    </h3>
                    <div v-if="loading" class="flex justify-center items-center py-16">
                        <span class="loader mr-3"></span>
                        <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                    </div>
                    <div v-else class="w-full flex flex-col items-center">
                        <VueECharts v-if="filteredRadarData.length" :option="radarOption"
                            style="height: 600px; width: 100%; max-width: 700px;" />
                        <div v-else class="text-gray-400 text-center py-12">No radar chart data available.</div>
                    </div>
                </div>

                <!-- Analytics Summary Card for Education -->
                <div class="mt-8 flex justify-center">
                    <div class="w-full md:w-2/3 lg:w-1/2 shadow-lg rounded-xl bg-white border border-gray-200">
                        <div class="flex items-center px-6 pt-6 pb-2">
                            <svg class="w-6 h-6 text-blue-500 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 3v18h18M9 17V9m4 8V5m4 12v-6" />
                            </svg>
                            <span class="text-lg font-semibold text-gray-800">Analytics Summary</span>
                        </div>
                        <div class="px-6 pb-6 text-gray-700 leading-relaxed">
                            <span v-html="educationSummary"></span>
                        </div>
                    </div>
                </div>

            </div>

            <div v-if="reportType === 'salary'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-filter text-orange-500 mr-2"></i>
                    Filter Reports
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Industry</label>
                        <MultiSelect v-model="selectedIndustries" :options="industries" label="name" track-by="id"
                            :searchable="true" :multiple="true" placeholder="Select industries" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Experience Level</label>
                        <MultiSelect v-model="selectedExperienceLevels" :options="experienceLevels" label="name"
                            track-by="id" :searchable="true" :multiple="true" placeholder="Select experience levels" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" v-model="dateFromSalary"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors duration-200" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" v-model="dateToSalary"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors duration-200" />
                    </div>
                </div>
                <!-- Box Plot: Salary Ranges Across Industries -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-box text-orange-500"></i>
                        Salary Ranges by Industry <span class="font-bold text-orange-600">(Box Plot)</span>
                    </h3>
                    <div v-if="loading" class="flex justify-center items-center py-16">
                        <span class="loader mr-3"></span>
                        <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                    </div>
                    <div v-else>
                        <VueECharts
                            v-if="analyticsData.industrySalaryBoxData && analyticsData.industrySalaryBoxData.length"
                            :option="boxPlotOption" style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No box plot data available.</div>
                    </div>
                </div>

                <!-- Stacked Bar: Entry/Mid/Senior Salary per Industry -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-layer-group text-pink-500"></i>
                        Salary by Experience Level <span class="font-bold text-pink-600">(Stacked Bar)</span>
                    </h3>
                    <div v-if="loading" class="flex justify-center items-center py-16">
                        <span class="loader mr-3"></span>
                        <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                    </div>
                    <div v-else>
                        <VueECharts
                            v-if="analyticsData.industryLevelSalaries && analyticsData.industryLevelSalaries.length"
                            :option="stackedBarOption" style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No stacked bar data available.</div>
                    </div>
                </div>

                <!-- Histogram: Salary Expectations vs Offered -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-yellow-500"></i>
                        Salary Expectations vs Offered <span class="font-bold text-yellow-600">(Histogram)</span>
                    </h3>
                    <div v-if="loading" class="flex justify-center items-center py-16">
                        <span class="loader mr-3"></span>
                        <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                    </div>
                    <div v-else>
                        <VueECharts v-if="analyticsData.salaryExpectations" :option="histogramOption"
                            style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No histogram data available.</div>
                    </div>
                </div>

                <!-- Analytics Summary Card for Salary -->
                <div class="mt-8 flex justify-center">
                    <div class="w-full md:w-2/3 lg:w-1/2 shadow-lg rounded-xl bg-white border border-gray-200">
                        <div class="flex items-center px-6 pt-6 pb-2">
                            <svg class="w-6 h-6 text-orange-500 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 3v18h18M9 17V9m4 8V5m4 12v-6" />
                            </svg>
                            <span class="text-lg font-semibold text-gray-800">Analytics Summary</span>
                        </div>
                        <div class="px-6 pb-6 text-gray-700 leading-relaxed">
                            <span v-html="salarySummary"></span>
                        </div>
                    </div>
                </div>
            </div>


        </Container>
    </AppLayout>
</template>