<script setup>
    import FormSection from '@/Components/FormSection.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import TextInput from '@/Components/TextInput.vue';
    import InputError from '@/Components/InputError.vue';
    import { useForm } from '@inertiajs/vue3';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import ActionMessage from '@/Components/ActionMessage.vue';


    const props = defineProps({
        job: Object
    })

    const form = useForm({
        job_name: props.job.job_title,
        description: props.job.description,
    })

    const submitForm = () => {
        form.put(route('jobs.update', {job: props.job.id}), {
            preserveScroll: true
        })
    }

</script>

<template>

 
                <FormSection @submitted="submitForm()">
                    <template #title>
                        Edit your Job
                    </template>

                    <template #description>
                        Form to edit your Job
                    </template>

                    <template #form>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel value="Job Title"/>
                                <TextInput v-model="form.job_name" type="text" class="block w-full"/>
                                <InputError :message="form.errors.job_name" />
                        </div>
                        
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel value="Job Description"/>
                                <TextInput v-model="form.description" type="text" class="block w-full"/>
                                <InputError :message="form.errors.description" />
                        </div>
                    </template>
                    <template #actions>
                        <ActionMessage :on="form.recentlySuccessful" class="mr-2">
                            Saved.
                        </ActionMessage>
                        <PrimaryButton type ="submit">Save</PrimaryButton>
                    
                    </template>
                </FormSection>


</template>