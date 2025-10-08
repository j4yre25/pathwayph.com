<script setup>
import ConfirmationModal from '@/Components/ConfirmationModal.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
    show: Boolean,
    user: Object,
})

console.log('User Verification Modal Props:', props)

const emit = defineEmits(['update:show'])

function close() {
    emit('update:show', false)
}

function isImage(filePath) {
    return /\.(jpg|jpeg|png|gif)$/i.test(filePath)
}
function isPDF(filePath) {
    return /\.pdf$/i.test(filePath)
}
</script>

<template>
    <ConfirmationModal :show="show" @close="close">
        <template #title>
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                    <i class="fas fa-eye text-blue-500"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">User Verification</h3>
            </div>
        </template>
        <template #content>
            <div class="space-y-6">
                <!-- 1. Mayor's Business Permit -->
                <div>
                    <h4 class="font-semibold text-gray-700 mb-2 flex items-center">
                        <i class="fas fa-file-alt text-blue-500 mr-2"></i> Mayor's Business Permit
                    </h4>
                    <div v-if="user?.company?.verification_file_path">
                        <template v-if="isImage(user.company.verification_file_path)">
                            <img :src="`/storage/${user.company.verification_file_path}`" alt="Mayor's Permit"
                                class="max-w-full h-auto rounded border" />
                        </template>
                        <template v-else-if="isPDF(user.company.verification_file_path)">
                            <iframe :src="`/storage/${user.company.verification_file_path}`"
                                class="w-full h-64 border rounded"></iframe>
                        </template>
                        <template v-else>
                            <a :href="`/storage/${user.company.verification_file_path}`" target="_blank"
                                class="text-blue-600 underline">Preview File</a>
                        </template>
                    </div>
                    <div v-else class="text-gray-400 italic">No Mayor's Business Permit uploaded.</div>
                </div>

                <!-- 2. TIN/BIR -->
                <div>
                    <h4 class="font-semibold text-gray-700 mb-2 flex items-center">
                        <i class="fas fa-id-card text-green-500 mr-2"></i> TIN/BIR
                    </h4>
                    <div><span class="font-semibold">TIN/BIR:</span> {{ user.company?.bir_tin }}</div>
                </div>

                <!-- 3. Company Information -->
                <div>
                    <h4 class="font-semibold text-gray-700 mb-2 flex items-center">
                        <i class="fas fa-building text-purple-500 mr-2"></i> Company Information
                    </h4>
                    <div class="flex items-center mb-4">
                        <img v-if="user.company_logo_path" :src="`/storage/${user.company_logo_path}`"
                            alt="Company Logo" class="w-16 h-16 rounded-full border object-cover mr-4" />
                        <div>
                            <div class="font-bold text-lg text-gray-800">{{ user.company?.company_name }}</div>
                            <div class="text-gray-500 text-sm">{{ user.company?.sector?.name }}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-semibold">Business Name:</span> {{ user.company?.company_name }}
                        </div>
                        <div>
                            <span class="font-semibold">Sector:</span> {{ user.company?.sector?.name || '-' }}
                        </div>
                        <div>
                            <span class="font-semibold">Street Address:</span> {{ user.company?.company_street_address
                            || '-' }}
                        </div>
                        <div>
                            <span class="font-semibold">Barangay:</span> {{ user.company?.company_brgy || '-' }}
                        </div>
                        <div>
                            <span class="font-semibold">City:</span> {{ user.company?.company_city || '-' }}
                        </div>
                        <div>
                            <span class="font-semibold">Email:</span> {{ user.company?.company_email || '-' }}
                        </div>
                        <div>
                            <span class="font-semibold">Mobile:</span> {{ user.company?.company_mobile_phone || '-' }}
                        </div>
                        <div>
                            <span class="font-semibold">Telephone:</span> {{ user.company?.company_tel_phone || '-' }}
                        </div>
                    </div>
                    <div class="mt-4">
                        <h5 class="font-semibold text-gray-700 mb-1 flex items-center">
                            <i class="fas fa-user-tie text-blue-400 mr-2"></i> HR Information
                        </h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-semibold">Full Name:</span>
                                {{ user.hr
                                    ? `${user.hr.first_name} ${user.hr.middle_name || ''} ${user.hr.last_name}`.trim()
                                : '-' }}
                            </div>
                            <div>
                                <span class="font-semibold">Mobile:</span> {{ user.hr?.mobile_number || '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-end space-x-3">
                <SecondaryButton @click="close">Close</SecondaryButton>
                <a v-if="user?.institution?.verification_file_path"
                    :href="route('institutions.downloadVerification', user.institution.id)"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm transition-colors duration-200"
                    target="_blank" download>
                    <i class="fas fa-download mr-2"></i> Download Verification File
                </a>
                <a v-if="user?.company?.verification_file_path"
                    :href="route('companies.downloadVerification', user.company.id)"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm transition-colors duration-200"
                    target="_blank" download>
                    <i class="fas fa-download mr-2"></i> Download Verification File
                </a>
            </div>
        </template>
    </ConfirmationModal>
</template>