<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  thread: { type: Object, required: true },
  messages: { type: Array, default: () => [] },
  request: { type: Object, default: null }, // { id, canRespond, requested, responses }
})

const files = ref([])
const links = ref([''])
const submitting = ref(false)

function onFilesChange(e) {
  files.value = Array.from(e.target.files || [])
}

function addLink() {
  links.value.push('')
}

function removeLink(i) {
  links.value.splice(i, 1)
}

async function submitResponse() {
  if (!props.request?.id) return
  const fd = new FormData()
  links.value.filter(Boolean).forEach(l => fd.append('links[]', l))
  files.value.forEach(f => fd.append('files[]', f))
  submitting.value = true
  try {
    await router.post(route('messages.respond', props.request.id), fd, {
      forceFormData: true,
      onSuccess: () => {
        files.value = []
        links.value = ['']
      },
      onFinish: () => { submitting.value = false },
    })
  } catch (e) {
    submitting.value = false
    console.warn(e)
  }
}
</script>

<template>
  <AppLayout :title="thread.company_name ? `Chat • ${thread.company_name}` : 'Chat'">
    <div class="max-w-6xl mx-auto p-6">
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <!-- Header -->
        <div class="flex items-center gap-3 p-4 border-b">
          <img :src="thread.avatar_url || '/images/default-logo.png'"
               class="w-10 h-10 rounded-full object-cover border" />
          <div class="min-w-0">
            <div class="font-semibold text-gray-900 truncate">
              {{ thread.company_name || 'Company' }}
            </div>
            <div class="text-xs text-gray-500 truncate">
              {{ thread.job_title }}
            </div>
          </div>
          <div class="ml-auto">
            <Link class="text-sm text-blue-600 hover:underline" :href="route('messages.index')">All messages</Link>
          </div>
        </div>

        <!-- Messages -->
        <div class="h-[60vh] overflow-y-auto p-4 space-y-3 bg-gray-50">
          <div
            v-for="m in messages"
            :key="m.id"
            class="flex"
            :class="m.isMine ? 'justify-end' : 'justify-start'"
          >
            <div
              class="max-w-[75%] px-3 py-2 rounded-lg shadow"
              :class="m.isMine ? 'bg-blue-600 text-white' : 'bg-white text-gray-800'"
            >
              <div class="text-sm whitespace-pre-wrap break-words">{{ m.body }}</div>
              <div v-if="m.type === 'request_info' && m.meta?.requested?.length" class="text-[11px] mt-2 opacity-80">
                Requested: {{ m.meta.requested.join(', ') }}
              </div>
              <div v-if="m.meta?.responses?.length" class="mt-2 space-y-1">
                <div class="text-[11px] font-semibold opacity-80">Submitted:</div>
                <ul class="text-[11px] list-disc ml-4 space-y-1">
                  <li v-for="(r, idx) in m.meta.responses" :key="idx">
                    <template v-if="r.kind === 'file'">
                      <a :href="r.url" target="_blank" download class="text-blue-600 hover:underline">
                        {{ r.name }}
                      </a>
                      <span class="text-gray-400 ml-2">{{ r.uploaded_at }}</span>
                    </template>
                    <template v-else-if="r.kind === 'link'">
                      <a :href="r.url" target="_blank" class="text-blue-600 hover:underline">
                        {{ r.url }}
                      </a>
                      <span class="text-gray-400 ml-2">{{ r.added_at }}</span>
                    </template>
                  </li>
                </ul>
              </div>
              <div class="text-[10px] opacity-75 mt-1 text-right">{{ m.created_at }}</div>
            </div>
          </div>
          <div v-if="!messages.length" class="text-center text-gray-500 text-sm py-8">
            No messages yet.
          </div>
        </div>

        <!-- Request Info Composer -->
        <div v-if="request?.canRespond" class="p-4 border-t bg-white">
          <div class="text-sm font-medium text-gray-800 mb-2">
            Submit requested information (optional files/links)
          </div>
          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="text-xs text-gray-600">Upload files (PDF, JPG, PNG, DOC, DOCX)</label>
              <input type="file" multiple
                     class="mt-1 block w-full text-sm"
                     accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                     @change="onFilesChange">
              <div v-if="files.length" class="text-xs text-gray-500 mt-1">
                {{ files.length }} file(s) selected
              </div>
            </div>
            <div>
              <label class="text-xs text-gray-600">Add links</label>
              <div class="space-y-2 mt-1">
                <div v-for="(l, i) in links" :key="i" class="flex gap-2">
                  <input v-model="links[i]" type="url" placeholder="https://..."
                         class="w-full border rounded px-2 py-1 text-sm" />
                  <button v-if="links.length > 1" @click="removeLink(i)" type="button"
                          class="px-2 text-xs text-red-600">Remove</button>
                </div>
                <button @click="addLink" type="button" class="text-xs text-blue-600">+ Add another link</button>
              </div>
            </div>
          </div>
          <div class="mt-3">
            <button :disabled="submitting"
                    @click="submitResponse"
                    class="px-4 py-2 rounded bg-blue-600 text-white text-sm disabled:opacity-50">
              {{ submitting ? 'Submitting...' : 'Submit' }}
            </button>
          </div>
        </div>
        <div v-else-if="request && request.responses?.length" class="p-4 border-t bg-white">
          <div class="text-xs text-gray-600">You have already submitted information for this request.</div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>