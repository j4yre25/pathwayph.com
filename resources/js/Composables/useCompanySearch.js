import { ref, computed } from 'vue';

export function useCompanySearch(form, companies) {
  const companySearch = ref('');
  const showSuggestions = ref(false);

  const filteredCompanies = computed(() => {
    if (!companySearch.value) return [];
    return companies.value.filter(c =>
      c.name.toLowerCase().includes(companySearch.value.toLowerCase())
    );
  });

  function selectCompany(company) {
    form.value.company = company.name;
    form.value.company_id = company.id;
    companySearch.value = company.name;
    showSuggestions.value = false;
    form.value.company_not_found = false;
  }

  function clearCompany() {
    form.value.company = '';
    form.value.company_id = '';
    companySearch.value = '';
  }

  return {
    companySearch,
    showSuggestions,
    filteredCompanies,
    selectCompany,
    clearCompany,
  };
}