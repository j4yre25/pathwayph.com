<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { usePage } from "@inertiajs/vue3";
import { computed, watch, ref } from "vue";
import Welcome from "@/Components/Welcome.vue";
import Modal from "../Components/Modal.vue";
import CompanyDashboard from "../Pages/Company/CompanyDashboard.vue";
import { Inertia } from "@inertiajs/inertia";

const page = usePage()
const userNotApproved = computed(() => page.props.userNotApproved ?? false);
const showModal = ref(false);

if (userNotApproved.value) {
    showModal.value = true;
}

console.log("Show Modal:", showModal.value);

const handleLogout = () => {
    Inertia.visit(route("logout"), { method: "post" });
};
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>


            <div v-if="page.props.roles.isCompany" class="py-12">
                <Welcome v-if="!page.props.roles.isCompany" />
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800">
                        Welcome to the Dashboard
                    </h3>
                    <p class="mt-2 text-gray-600">
                        Here you can manage your account and view your
                        statistics.
                    </p>
                </div>
                <CompanyDashboard :summary="page.props.summary" />
            </div>

        </template>

        <div class="py-12">
            <div class="max-w-7x1 mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <Modal v-if="showModal" :show="showModal">
                        <template #title> Account Not Approved </template>
                        <template #content>
                            <p>
                                Your account is not approved yet. Some features
                                may be disabled.
                            </p>
                        </template>
                        <template #footer>
                            <button class="bg-blue-500 text-white px-4 py-2 rounded" @click="handleLogout">
                                Okay
                            </button>
                        </template>
                    </Modal>

                    <CompanyDashboard v-if="page.props.auth.user.role === 'company'" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
