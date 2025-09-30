<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
dayjs.extend(relativeTime)

const props = defineProps({
  thread: { type: Object, required: true },
  messages: { type: Array, default: () => [] },
  request: { type: Object, default: null },
})

const files = ref([])
const links = ref([''])
const submitting = ref(false)

function onFilesChange(e) {
  files.value = Array.from(e.target.files || [])
}
function addLink() { links.value.push('') }
function removeLink(i) { links.value.splice(i, 1) }

const messages = computed(() => props.messages ?? []) // local reactive alias

// Helper: group messages by day without mutating original message object
function groupMessagesByDay(msgs) {
  // ensure we have an array
  if (!msgs || !Array.isArray(msgs)) return [];
  try {
    const groups = [];
    let lastDate = null;
    msgs.forEach((m) => {
      // tolerate different created timestamp keys and missing values
      const created = m?.created_at ?? m?.createdAt ?? m?.created ?? null;
      // fallback to today's date if created is missing (prevents dayjs errors)
      const date = created ? dayjs(created).format('YYYY-MM-DD') : dayjs().format('YYYY-MM-DD');
      if (date !== lastDate) {
        groups.push({ kind: 'date', date });
        lastDate = date;
      }
      // keep original message object intact
      groups.push({ kind: 'msg', message: m });
    });
    return groups;
  } catch (err) {
    // fail safe: log and return empty array to avoid breaking render
    console.error('groupMessagesByDay error', err);
    return [];
  }
}

const groupedMessages = computed(() => groupMessagesByDay(Array.isArray(messages.value) ? messages.value : []))

// debug
console.log('Messages:', messages.value)

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

function getInitials(name) {
  if (!name) return 'C'
  return name.split(' ').map(w => w[0]).join('').slice(0,2).toUpperCase()
}
</script>

<template>
  <AppLayout :title="thread.company_name ? `Chat • ${thread.company_name}` : 'Chat'">
    <div class="max-w-6xl mx-auto p-4 sm:p-6">
      <div class="bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col" style="min-height:70vh;">
        <!-- Header (unchanged) -->
        <div class="flex items-center gap-4 p-5 border-b bg-white">
          <div class="relative">
            <img v-if="thread.avatar_url" :src="thread.avatar_url" class="w-12 h-12 rounded-full object-cover border" />
            <div v-else class="w-12 h-12 rounded-full flex items-center justify-center bg-blue-500 text-white font-bold text-xl">
              {{ getInitials(thread.company_name) }}
            </div>
          </div>
          <div class="min-w-0 flex-1">
            <div class="font-bold text-gray-900 text-lg truncate">{{ thread.company_name || 'Company' }}</div>
            <div class="text-xs text-gray-500 truncate font-medium">{{ thread.job_title }}</div>
          </div>
          <Link class="ml-auto px-3 py-1 rounded-full border border-blue-500 text-blue-600 hover:bg-blue-50 transition text-sm font-semibold"
                :href="route('messages.index')">All messages</Link>
        </div>

        <!-- Messages -->
        <div class="flex-1 bg-gray-50 px-2 sm:px-6 py-4 overflow-y-auto">
          <div v-if="!messages.length" class="flex flex-col items-center justify-center h-48">
            <div class="text-5xl mb-2">📭</div>
            <div class="text-gray-500 text-lg font-medium">Start the conversation by sending your first message.</div>
          </div>

          <template v-else>
            <div>
              <template v-for="(item, idx) in groupedMessages" :key="idx">
                <div v-if="item.kind === 'date'" class="flex items-center my-6">
                  <div class="flex-1 border-t border-gray-300"></div>
                  <div class="px-3 text-xs text-gray-500 font-semibold">
                    {{ dayjs(item.date).isSame(dayjs(), 'day') ? 'Today' :
                       dayjs(item.date).isSame(dayjs().subtract(1, 'day'), 'day') ? 'Yesterday' :
                       dayjs(item.date).format('MMM D, YYYY') }}
                  </div>
                  <div class="flex-1 border-t border-gray-300"></div>
                </div>

                <transition name="fade-slide" appear>
                  <div v-if="item.kind === 'msg'" class="flex mb-2" :class="item.message.isMine ? 'justify-end' : 'justify-start'">
                    <div class="max-w-[75%] px-4 py-3 rounded-xl shadow-sm relative"
                         :class="item.message.isMine
                           ? 'bg-gradient-to-br from-blue-500 to-indigo-600 text-white rounded-tr-xl'
                           : 'bg-white text-gray-800 border border-gray-200 rounded-tl-xl'">
                      <div class="text-sm whitespace-pre-wrap break-words">{{ item.message.body }}</div>

                      <!-- Request Info (check original message type) -->
                      <div v-if="item.message.type === 'request_info' && item.message.meta?.requested?.length"
                           class="text-[11px] mt-2 opacity-80">
                        <span class="font-semibold text-blue-600">Requested:</span>
                        {{ item.message.meta.requested.join(', ') }}
                      </div>

                      <!-- Submitted responses -->
                      <div v-if="item.message.meta?.responses?.length" class="mt-2">
                        <div class="bg-gray-100 border-l-4 border-blue-400 rounded p-2 space-y-2">
                          <div class="text-xs font-semibold text-gray-700 mb-1">Submitted:</div>
                          <div class="flex flex-wrap gap-2">
                            <template v-for="(r, idx2) in item.message.meta.responses" :key="idx2">
                              <div v-if="r.kind === 'file'" class="flex items-center bg-white border rounded px-2 py-1 shadow text-xs">
                                <span class="mr-2 text-blue-500">📄</span>
                                <a :href="r.url" target="_blank" download class="text-blue-700 hover:underline font-medium">{{ r.name }}</a>
                                <span class="ml-2 text-gray-400">{{ r.uploaded_at }}</span>
                              </div>
                              <div v-else-if="r.kind === 'link'" class="flex items-center bg-white border rounded px-2 py-1 shadow text-xs">
                                <span class="mr-2 text-indigo-500">🔗</span>
                                <a :href="r.url" target="_blank" class="text-indigo-700 hover:underline font-medium">{{ r.url }}</a>
                                <span class="ml-2 text-gray-400">{{ r.added_at }}</span>
                              </div>
                            </template>
                          </div>
                        </div>
                      </div>

                      <div class="text-[10px] opacity-75 mt-1 text-right">{{ dayjs(item.message.created_at).fromNow() }}</div>
                    </div>
                  </div>
                </transition>
              </template>
            </div>
          </template>
        </div>

        <!-- Composer (unchanged) -->
        <div v-if="request?.canRespond" class="p-5 bg-gray-100 border-t">
          <div class="font-semibold text-gray-800 mb-2 text-sm">Submit requested information</div>
          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">Upload files</label>
              <div class="border-2 border-dashed border-gray-300 rounded-lg bg-white p-4 flex flex-col items-center justify-center cursor-pointer hover:border-blue-400 transition"
                   @click="$refs.fileInput.click()">
                <span class="text-2xl text-blue-400 mb-2">📄</span>
                <span class="text-xs text-gray-500">Drag & drop or click to upload (PDF, JPG, PNG, DOC, DOCX)</span>
                <input ref="fileInput" type="file" multiple
                       class="hidden"
                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                       @change="onFilesChange">
              </div>
              <div v-if="files.length" class="text-xs text-gray-500 mt-2">
                {{ files.length }} file(s) selected
              </div>
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">Add links</label>
              <div class="space-y-2 mt-1">
                <div v-for="(l, i) in links" :key="i" class="flex gap-2 items-center">
                  <input v-model="links[i]" type="url" placeholder="https://..."
                         class="w-full border rounded px-2 py-1 text-sm" />
                  <button v-if="links.length > 1" @click="removeLink(i)" type="button"
                          class="w-7 h-7 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-200 text-red-600 text-lg">
                    ❌
                  </button>
                </div>
                <button @click="addLink" type="button"
                        class="text-xs text-blue-600 font-semibold mt-2">+ Add another link</button>
              </div>
            </div>
          </div>
          <div class="mt-4 flex justify-end">
            <button :disabled="submitting" @click="submitResponse"
                    class="px-6 py-2 rounded-full bg-blue-600 text-white text-base font-semibold shadow hover:shadow-lg transition disabled:opacity-50">
              {{ submitting ? 'Submitting...' : 'Submit' }}
            </button>
          </div>
        </div>

        <div v-else-if="request && request.responses?.length" class="p-5 bg-gray-100 border-t">
          <div class="text-xs text-gray-600">You have already submitted information for this request.</div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s cubic-bezier(.4,0,.2,1);
}
.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(10px);
}
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>