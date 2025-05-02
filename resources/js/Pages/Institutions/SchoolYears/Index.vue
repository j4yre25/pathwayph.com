<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, usePage } from '@inertiajs/vue3'

import Container from '@/Components/Container.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Link } from '@inertiajs/vue3'
import MySchoolYears from './MySchoolYears.vue'

const page = usePage()

const props = defineProps({
    school_years: Array
})

const school_years = page.props.school_years;
</script>

<template>
    <AppLayout title="School Years">
        <template #header>
            School Years
        </template>

        <Container class="py-4">
            <div class="mt-8">
                <Link :href="route('school-years.create', { user: page.props.auth.user.id })">
                    <PrimaryButton class="mr-2">Add School Year</PrimaryButton>
                </Link>

                <Link :href="route('school-years.list', { user: page.props.auth.user.id })">
                    <PrimaryButton class="mr-2">All School Years</PrimaryButton>
                </Link>

                <Link class="ml-2" v-if="page.props.roles.isInstitution"
                    :href="route('school-years.archivedlist', { user: page.props.auth.user.id })"
                    :active="route().current('school-years.archivedlist', { user: page.props.auth.user.id })">
                    <PrimaryButton>Archived School Years</PrimaryButton>
                </Link>
            </div>

            <div class="mt-8">
                <MySchoolYears :school_years="school_years" />
            </div>
        </Container>
    </AppLayout>
</template>
