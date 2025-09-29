<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import axios from 'axios'
import Modal from '@/Components/Modal.vue'

const props = defineProps({ seminarId: Number })
const attendances = ref([])
const byGender = ref({})
const byAge = ref({})
const loading = ref(true)
const isSuccessModalOpen = ref(false)
const isErrorModalOpen = ref(false)
const successMessage = ref('')
const errorMessage = ref('')


const form = useForm({
    attendees: [{ attendee_name: '', gender: '', age: '' }]
})

if (!form.attendees) form.attendees = [];

const fetchAttendance = async () => {
    loading.value = true
    const res = await axios.get(route('admin.seminar-requests.attendance.data', props.seminarId))
    attendances.value = Array.isArray(res.data.attendances) ? res.data.attendances : []
    byGender.value = Array.isArray(res.data.byGender) ? res.data.byGender : {}
    byAge.value = Array.isArray(res.data.byAge) ? res.data.byAge : {}
    loading.value = false
}

const addRow = () => form.attendees.push({ attendee_name: '', gender: '', age: '' })

const submit = () => {
    form.post(route('admin.seminar-requests.attendance.store', props.seminarId), {
        onSuccess: () => {
            form.attendees = [{ attendee_name: '', gender: '', age: '' }]
            fetchAttendance()
            successMessage.value = 'Attendance successfully recorded.'
            isSuccessModalOpen.value = true
        },
        onError: (errors) => {
            errorMessage.value = 'Please fill in all required fields correctly.'
            isErrorModalOpen.value = true
        }
    })
}

onMounted(fetchAttendance)
</script>

<template>
    <AppLayout title="Seminar Attendance">
        <div class="max-w-3xl mx-auto py-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-users text-indigo-500"></i>
                    Seminar Attendance
                </h2>
                <p class="text-gray-500 text-sm">Record and view attendance by gender and age group.</p>
            </div>

            <div class="bg-white rounded-xl shadow border border-gray-200 p-6 mb-8">
                <h3 class="text-lg font-semibold text-indigo-700 mb-4 flex items-center gap-2">
                    <i class="fas fa-user-plus"></i> Add Attendees
                </h3>
                <form @submit.prevent="submit" class="space-y-3">
                    <div v-for="(att, i) in (form.attendees || [])" :key="i" class="flex flex-col md:flex-row gap-2">
                        <input v-model="att.attendee_name" placeholder="Name"
                            class="input input-bordered flex-1 rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
                        <select v-model="att.gender"
                            class="input input-bordered flex-1 rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Gender</option>
                            <option>Male</option>
                            <option>Female</option>
                            <option>Other</option>
                        </select>
                        <input v-model="att.age" type="number" min="0" max="120" placeholder="Age"
                            class="input input-bordered flex-1 rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>
                    <div class="flex gap-2 mt-2">
                        <button type="button" @click="addRow"
                            class="inline-flex items-center px-3 py-1 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 text-sm font-medium transition">
                            <i class="fas fa-plus mr-1"></i> Add Row
                        </button>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-1.5 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 text-sm font-semibold shadow transition">
                            <i class="fas fa-save mr-1"></i> Save Attendance
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-indigo-700 mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-bar"></i> Attendance Summary
                </h3>
                <div v-if="loading" class="text-gray-500">Loading...</div>
                <div v-else class="grid grid-colsn-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="(count, gender) in byGender" :key="gender"
                                class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold">
                                <i class="fas fa-user mr-1"></i> {{ gender }}: {{ count }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="(count, group) in byAge" :key="group"
                                class="inline-flex items-center px-3 py-1 rounded-full bg-green-50 text-green-700 text-xs font-semibold">
                                <i class="fas fa-birthday-cake mr-1"></i> {{ group }}: {{ count }}
                            </span>
                        </div>
                    </div>
                </div>
                <div v-if="attendances && attendances.length" class="mt-6">
                    <h4 class="font-semibold text-gray-700 mb-2">Attendance List</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Name</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Gender</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Age</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="a in attendances.filter(a => a.attendee_name || a.gender || a.age)"
                                    :key="a.id" class="hover:bg-indigo-50">
                                    <td class="px-3 py-2">{{ a.attendee_name || '-' }}</td>
                                    <td class="px-3 py-2">{{ a.gender || '-' }}</td>
                                    <td class="px-3 py-2">{{ a.age !== null && a.age !== undefined ? a.age : '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>

    <Modal :modelValue="isSuccessModalOpen" @close="isSuccessModalOpen = false">
  <div class="p-6">
    <div class="flex items-center justify-center mb-4 bg-green-100 rounded-full w-12 h-12 mx-auto">
      <i class="fas fa-check text-green-500 text-xl"></i>
    </div>
    <h2 class="text-lg font-medium text-center text-gray-900 mb-2">Success</h2>
    <p class="text-center text-gray-600">{{ successMessage }}</p>
    <div class="mt-6 flex justify-center">
      <button @click="isSuccessModalOpen = false" class="btn btn-primary">OK</button>
    </div>
  </div>
</Modal>

<Modal :modelValue="isErrorModalOpen" @close="isErrorModalOpen = false">
  <div class="p-6">
    <div class="flex items-center justify-center mb-4 bg-red-100 rounded-full w-12 h-12 mx-auto">
      <i class="fas fa-times text-red-500 text-xl"></i>
    </div>
    <h2 class="text-lg font-medium text-center text-gray-900 mb-2">Error</h2>
    <p class="text-center text-gray-600">{{ errorMessage }}</p>
    <div class="mt-6 flex justify-center">
      <button @click="isErrorModalOpen = false" class="btn btn-primary">OK</button>
    </div>
  </div>
</Modal>
</template>