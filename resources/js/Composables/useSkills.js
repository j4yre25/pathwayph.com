import { ref, computed } from 'vue';

export function useSkills(form, propsSkills) {
  const newSkill = ref('');
  const isFocused = ref(false);
  const showSuggestions = ref(false); // <-- add this


  const filteredSkills = computed(() => {
    if (!newSkill.value.trim()) return [];

    const search = newSkill.value.toLowerCase();

    return propsSkills.filter(
      skill => skill.toLowerCase().includes(search) && !form.skills.includes(skill)
    );
  });

  const addSkill = () => {
    if (newSkill.value.trim() !== '') {
      form.skills.push(newSkill.value.trim());
      newSkill.value = '';
      showSuggestions.value = false; // optionally hide suggestions after add

    }
  };

  const removeSkill = (index) => {
    form.skills.splice(index, 1);
  };

  const selectSuggestion = (skill) => {
    form.skills.push(skill);
    newSkill.value = '';
  };

  return {
    newSkill,
    showSuggestions, // <-- return this
    isFocused,
    filteredSkills,
    addSkill,
    removeSkill,
    selectSuggestion
  };
}
