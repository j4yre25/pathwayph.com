<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';


const props = defineProps ({
    all_users: Array

})


</script>


<template>
    <AppLayout title="Manage Users">
        <template #header>
            Manage Users
        
        </template>
        
        <Container class="py-16">
            {{ all_users }}


        </Container>
    </AppLayout>




</template>