<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, usePage } from '@inertiajs/vue3'

import Container from '@/Components/Container.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
 import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import MySectors from './MySectors.vue';



const page = usePage()

const props = defineProps ({
    sectors: Array
})

const sectors = page.props.sectors;
console.log('Sectors:', sectors);


const form = useForm({
    name: '',

});

const createSector = () => {
    form.post(route('sectors', { user: page.props.auth.user.id }), {
        onSuccess: () => {
            form.reset();
        }
    });
    
}

</script>


<template>
    <AppLayout title ="Sectors">
        <template #header>
            Sectors
        </template>
        


        <Container class="py-4">


            <!-- <PrimaryButton @click="createJob()" class="">Post Job</PrimaryButton> --> 
            <div class="mt-8">
                <Link :href="route('sectors.create', { user: page.props.auth.user.id })">
                <PrimaryButton class="mr-2">Add Sectors</PrimaryButton>
              </Link>
 
                <Link :href="route('sectors.list', { user: page.props.auth.user.id })">
                <PrimaryButton class="mr-2">All Sectors</PrimaryButton>
              </Link>

              <Link class ="ml-2" v-if="page.props.roles.isPeso" :href="route('sectors.archivedlist', { user: page.props.auth.user.id })"
                    :active="route().current('sectors.archivedlist', { user: page.props.auth.user.id })">
                <PrimaryButton>Archived Sectors</PrimaryButton>
                </Link>

       
           
        
        


            

             

              

              
            </div>


            <div class="mt-8">
                <MySectors :sectors="sectors" />
                

            </div>

 
        </Container>

  

     

    </AppLayout>
 
</template>