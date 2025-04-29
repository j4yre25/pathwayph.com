<script setup>
import Container from '@/Components/Container.vue';
import AppLayout from '@/Layouts/AppLayout.vue';


const props = defineProps({
    job: Object

})
</script>


<template>
    <AppLayout title="View Job">
        <template #header>
            {{ job.id }}


            <Container>
                <h2>Job Details</h2>


            </Container>


        </template>

    </AppLayout>

</template>