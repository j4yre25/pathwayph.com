<script setup>
import { defineProps, onMounted } from "vue";

const props = defineProps({
    summary: Object,
});

onMounted(() => {
    console.log('Summary:', props.summary);
});
</script>

<template>
    <div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-600">Total Job Postings</h3>
                <p class="text-3xl font-bold text-indigo-600">
                    {{ summary.total_jobs }}
                </p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-600">Applications Received</h3>
                <p class="text-3xl font-bold text-green-600">
                    {{ summary.total_applications }}
                </p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-600">Total Hires</h3>
                <p class="text-3xl font-bold text-blue-600">
                    {{ summary.total_hires }}
                </p>
            </div>
        </div>

        <!-- You can add a table or chart here next -->
    </div>
</template>
