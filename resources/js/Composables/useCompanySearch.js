import { ref, computed } from 'vue';

export function useCompanySearch(form, companies) {
  const companySearch = ref('');
  const showSuggestions = ref(false);

  const filteredCompanies = computed(() => {
    if (!companySearch.value.trim()) return [];
    const search = companySearch.value.toLowerCase();
    return companies.value.filter(
      c => c.name.toLowerCase().includes(search)
    );
  });

  const selectCompany = (company) => {
    form.company_name = company.name;
    form.company_id = company.id;
    companySearch.value = company.name;
    showSuggestions.value = false;
  };

  const clearCompany = () => {
    if (form.company_not_found) {
      form.company_name = '';
      form.company_id = '';
      companySearch.value = '';
    }
  };

  return {
    companySearch,
    showSuggestions,
    filteredCompanies,
    selectCompany,
    clearCompany,
  };
}