<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import Container from '@/Components/Container.vue';
    import UpdateForm from './UpdateForm.vue';
    import DeleteForm from './DeleteForm.vue';
    import SectionBorder from '@/Components/SectionBorder.vue';
   


    const props = defineProps({
        job: Object
    })

</script>

<template>
    <AppLayout title="Edit Job">
        <template #header>
           Edit #{{ job.id }}


            <Container class="py-16">
                <UpdateForm :job="job" />

                <SectionBorder />

                <DeleteForm :job="job" />

            </Container>


        </template>

    </AppLayout>

</template>