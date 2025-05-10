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

    console.log(props.job)
    const form = useForm({
        job_title: props.job.job_title,
        description: props.job.description,
        requirements: props.job.requirements,
    })

    const submitForm = () => {
        console.log('Submitted form data:', form); // Log the form data
        form.put(route('company.jobs.update', {job: props.job.id}), {
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
                                <TextInput v-model="form.job_title" type="text" class="block w-full"/>
                                <InputError :message="form.errors.job_title" />
                        </div>
                        
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel value="Job Description"/>
                                <TextInput v-model="form.description" type="text" class="block w-full"/>
                                <InputError :message="form.errors.description" />
                        </div>
                        
                        <div class="col-span-6 sm:col-span-4 mt-2">
                            <InputLabel value="Job Requirements"/>
                                <TextInput v-model="form.requirements" type="text" class="block w-full"/>
                                <InputError :message="form.errors.requirements" />
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