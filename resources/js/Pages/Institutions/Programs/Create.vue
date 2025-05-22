<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
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

const createProgram = () => {
    form.post(route('programs.store', { user: page.props.auth.user.id }), {
        onSuccess: () => form.reset()
    });
};
</script>

<template>
    <AppLayout>
        <template #header>Add Program</template>

        <Container class="py-16">
            <div class="mt-8">
                <FormSection @submitted="createProgram">
                    <template #title>Add a New Program</template>
                    <template #description>Enter the program name and select the related degree.</template>

                    <template #form>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="name" value="Program Name" />
                            <input id="name" v-model="form.name" type="text" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200"
                            placeholder="ex. Computer Science" />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <InputLabel for="degree_id" value="Degree" />
                            <select id="degree_id" v-model="form.degree_id">
                                <option disabled value="">Select Degree</option>
                                <option v-for="degree in degrees" :key="degree.id" :value="degree.id">
                                    {{ degree.type }}
                                </option>
                            </select>
                            <InputError :message="form.errors.degree_id" class="mt-2" />
                        </div>

                        <div v-if="showProgramCode" class="col-span-6 sm:col-span-4 mt-4">
                            <InputLabel for="program_code" value="Program Code" />
                            <input id="program_code" v-model="form.program_code" type="text" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200" 
                            placeholder="e.g. BSCS, BAEL, MSIT"/>
                            <InputError :message="form.errors.program_code" class="mt-2" />
                        </div>

                        <div v-if="showDuration" class="col-span-6 sm:col-span-4 mt-4">
                            <InputLabel for="duration" value="Duration" />
                            <select id="duration" v-model="form.duration" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200">
                                <option value="">Select</option>
                                <option value="Year">Year</option>
                                <option value="Month">Month</option>
                            </select>
                            <InputError :message="form.errors.duration" class="mt-2" />

                            <InputLabel for="duration_time" value="Duration Time" class="mt-4" />
                            <input id="duration_time" type="number" v-model="form.duration_time" min="1" max="12" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200" 
                            placeholder="1,2,etc.."/>
                            <InputError :message="form.errors.duration_time" class="mt-2" />
                        </div>
                    </template>

                    <template #actions>
                        <PrimaryButton type="submit" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                            Add
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </Container>
    </AppLayout>
</template>
