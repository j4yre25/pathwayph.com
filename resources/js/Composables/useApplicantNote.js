import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export default function useApplicantNote(applicant) {
  const note = ref(applicant.notes || '');
  const editingNote = ref(false);
  const noteInput = ref(note.value);

  const startEditNote = () => {
    noteInput.value = note.value;
    editingNote.value = true;
  };

  const cancelEditNote = () => {
    editingNote.value = false;
  };

  const saveNote = () => {
    router.put(
      route('applicants.note.update', applicant.id),
      { notes: noteInput.value },
      {
        preserveScroll: true,
        onSuccess: () => {
          note.value = noteInput.value;
          editingNote.value = false;
        },
      }
    );
  };

  return {
    note,
    editingNote,
    noteInput,
    startEditNote,
    cancelEditNote,
    saveNote,
  };
}
