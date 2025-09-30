<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed, watch } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    degrees: Array,
});

const form = useForm({
    name: '',
    degree_id: '',
    program_code: '',
    duration: '',
    duration_time: '',
});

const selectedDegree = computed(() =>
    props.degrees.find(d => d.id === form.degree_id)
);

const degreeType = computed(() => selectedDegree.value?.type || '');

const showProgramCode = computed(() =>
    ['Bachelor of Science', 'Bachelor of Arts', 'Master of Science', 'Master of Arts', 'Associate'].includes(degreeType.value)
);

const showDuration = computed(() =>
    ['Associate'].includes(degreeType.value)
);

// Watch for degree change to set initials
watch(() => form.degree_id, () => {
    if (!selectedDegree.value) return;
    const initials = {
        'Bachelor of Science': 'BS',
        'Bachelor of Arts': 'BA',
        'Associate': 'AS',
        'Master of Science': 'MS',
        'Master of Arts': 'MA',
        'Doctoral': 'PhD',
        'Diploma': 'D',
    }[degreeType.value] || '';
    if (showProgramCode.value) {
        // Only set if empty or matches previous initials
        if (!form.program_code || form.program_code.length <= initials.length) {
            form.program_code = initials;
        }
    } else {
        form.program_code = '';
    }
    if (!showDuration.value) {
        form.duration = '';
        form.duration_time = '';
    }
});

const page = usePage();
const userId = page.props.auth?.user?.id;

const createProgram = () => {
    form.post(route('programs.store', { user: page.props.auth.user.id }), {
        onSuccess: () => form.reset()
    });
};

// Add the goBack function to navigate back to the programs list

</script>

<template>
    <AppLayout>
        <template #header>
            <div>
                <div class="flex items-center">
                    <button @click="$inertia.get(route('programs',{ user: userId }))" class="mr-4 text-gray-600 hover:text-gray-900 transition">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <i class="fas fa-book text-blue-500 text-xl mr-2"></i>
                    <h1 class="text-2xl font-bold text-gray-800">Add Program</h1>
                </div>
                <p class="text-sm text-gray-500 mb-1">Enter the program details below to create a new program.</p>
            </div>
        </template>

        <Container class="py-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-8">
                <form @submit.prevent="createProgram" class="space-y-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-graduation-cap text-blue-500 mr-2"></i>
                            Program Details
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex flex-col">
                                <InputLabel for="name" value="Program Name" class="text-sm font-medium text-gray-700 mb-1" />
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-book-open text-gray-400"></i>
                                    </div>
                                    <input 
                                        id="name" 
                                        v-model="form.name" 
                                        type="text" 
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                                        placeholder="ex. Computer Science" 
                                    />
                                </div>
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div class="flex flex-col">
                                <InputLabel for="degree_id" value="Degree" class="text-sm font-medium text-gray-700 mb-1" />
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-award text-gray-400"></i>
                                    </div>
                                    <select 
                                        id="degree_id" 
                                        v-model="form.degree_id"
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                                    >
                                        <option disabled value="">Select Degree</option>
                                        <option v-for="degree in degrees" :key="degree.id" :value="degree.id">
                                            {{ degree.type }}
                                        </option>
                                    </select>
                                </div>
                                <InputError :message="form.errors.degree_id" class="mt-2" />
                            </div>

                            <div v-if="showProgramCode" class="flex flex-col">
                                <InputLabel for="program_code" value="Program Code" class="text-sm font-medium text-gray-700 mb-1" />
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-code text-gray-400"></i>
                                    </div>
                                    <input 
                                        id="program_code" 
                                        v-model="form.program_code" 
                                        type="text" 
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                                        placeholder="e.g. BSCS, BAEL, MSIT"
                                    />
                                </div>
                                <InputError :message="form.errors.program_code" class="mt-2" />
                            </div>

                            <div v-if="showDuration" class="flex flex-col">
                                <InputLabel for="duration" value="Duration" class="text-sm font-medium text-gray-700 mb-1" />
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-clock text-gray-400"></i>
                                    </div>
                                    <select 
                                        id="duration" 
                                        v-model="form.duration"
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                                    >
                                        <option value="">Select</option>
                                        <option value="Year">Year</option>
                                        <option value="Month">Month</option>
                                    </select>
                                </div>
                                <InputError :message="form.errors.duration" class="mt-2" />
                            </div>

                            <div v-if="showDuration" class="flex flex-col">
                                <InputLabel for="duration_time" value="Duration Time" class="text-sm font-medium text-gray-700 mb-1" />
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-hourglass-half text-gray-400"></i>
                                    </div>
                                    <input 
                                        id="duration_time" 
                                        type="number" 
                                        v-model="form.duration_time" 
                                        min="1" 
                                        max="12"
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                                        placeholder="1,2,etc.."
                                    />
                                </div>
                                <InputError :message="form.errors.duration_time" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring focus:ring-blue-200 disabled:opacity-25 transition"
                        >
                            <i class="fas fa-plus mr-2"></i>
                            {{ form.processing ? 'Adding...' : 'Add Program' }}
                        </button>
                    </div>
                </form>
            </div>
        </Container>
    </AppLayout>
</template>
