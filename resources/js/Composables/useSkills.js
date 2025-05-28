import { ref, computed } from 'vue';

export function useSkills(form, propsSkills) {
  const newSkill = ref('');
  const isFocused = ref(false);

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
    isFocused,
    filteredSkills,
    addSkill,
    removeSkill,
    selectSuggestion
  };
}
