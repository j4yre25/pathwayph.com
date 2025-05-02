<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router, usePage, Link } from '@inertiajs/vue3';

import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import MyPrograms from './MyPrograms.vue';

const page = usePage();

const props = defineProps({
    programs: Array,
});

const programs = page.props.programs;
</script>

<template>
    <AppLayout title="Programs">
        <template #header>
            Programs
        </template>

        <Container class="py-4">
            <div class="mt-8">
                <Link :href="route('programs.create', { user: page.props.auth.user.id })">
                    <PrimaryButton class="mr-2">Add Program</PrimaryButton>
                </Link>

                <Link :href="route('programs.list', { user: page.props.auth.user.id })">
                    <PrimaryButton class="mr-2">All Programs</PrimaryButton>
                </Link>

                <Link class="ml-2" v-if="page.props.roles.isInstitution"
                    :href="route('programs.archivedlist', { user: page.props.auth.user.id })"
                    :active="route().current('programs.archivedlist', { user: page.props.auth.user.id })">
                    <PrimaryButton>Archived Programs</PrimaryButton>
                </Link>
            </div>

            <div class="mt-8">
                <MyPrograms :programs="programs" />
            </div>
        </Container>
    </AppLayout>
</template>
