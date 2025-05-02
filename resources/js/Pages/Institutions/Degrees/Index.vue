<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, usePage } from '@inertiajs/vue3'

import Container from '@/Components/Container.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Link } from '@inertiajs/vue3'
import MyDegrees from './MyDegrees.vue'

const page = usePage()

const props = defineProps({
    degrees: Array
})

const degrees = page.props.degrees;
</script>

<template>
    <AppLayout title="Degrees">
        <template #header>
            Degrees
        </template>

        <Container class="py-4">
            <div class="mt-8">
                <Link :href="route('degrees.create', { user: page.props.auth.user.id })">
                    <PrimaryButton class="mr-2">Add Degree</PrimaryButton>
                </Link>

                <Link :href="route('degrees.list', { user: page.props.auth.user.id })">
                    <PrimaryButton class="mr-2">All Degrees</PrimaryButton>
                </Link>
                <Link class="ml-2" v-if="page.props.roles.isInstitution"
                    :href="route('degrees.archivedlist', { user: page.props.auth.user.id })"
                    :active="route().current('degrees.archivedlist', { user: page.props.auth.user.id })">
                    <PrimaryButton>Archived Degrees</PrimaryButton>
                </Link>
            </div>

            <div class="mt-8">
                <MyDegrees :degrees="degrees" />
            </div>
        </Container>
    </AppLayout>
</template>
