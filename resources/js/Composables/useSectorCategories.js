import { ref, watch } from 'vue';

export function useSectorCategories(form, sectors) {
  const availableCategories = ref([]);

  watch(() => form.sector, (newSector) => {
    if (newSector) {
      const selectedSector = sectors.find(sector => sector.id === newSector);
      availableCategories.value = selectedSector ? selectedSector.categories : [];
      form.category = '';
    } else {
      availableCategories.value = [];
    }
  });

  return {
    availableCategories
  };
}
