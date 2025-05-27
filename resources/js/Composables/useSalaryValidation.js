import { ref, watch } from 'vue';

export function useSalaryValidation(form) {
  const salaryError = ref('');
  const salaryWarning = ref('');

  const validateSalary = () => {
    salaryWarning.value = '';

    if (form.is_negotiable) {
      salaryError.value = '';
      return;
    }

    const min = parseInt(form.salary.job_min_salary);
    const max = parseInt(form.salary.job_max_salary);

    let minLimit = 12000; // default monthly min wage
    let softMaxLimit = 300000; // for warning only
    let salaryTypeLabel = 'monthly';

    switch (form.salary.salary_type) {
      case 'monthly':
        minLimit = 12000;
        softMaxLimit = 300000;
        salaryTypeLabel = 'monthly';
        break;
      case 'weekly':
        minLimit = 3000;
        softMaxLimit = 70000;
        salaryTypeLabel = 'weekly';
        break;
      case 'hourly':
        minLimit = 100;
        softMaxLimit = 2000;
        salaryTypeLabel = 'hourly';
        break;
      default:
        salaryError.value = 'Please select a valid salary type';
        return;
    }

    if (isNaN(min) || min < minLimit) {
      salaryError.value = `Minimum salary must be at least ₱${minLimit.toLocaleString()} (${salaryTypeLabel})`;
    } else if (isNaN(max) || max < minLimit) {
      salaryError.value = `Maximum salary must be at least ₱${minLimit.toLocaleString()} (${salaryTypeLabel})`;
    } else if (min > max) {
      salaryError.value = 'Minimum salary cannot be greater than maximum salary';
    } else {
      salaryError.value = '';

      if (max > softMaxLimit) {
        salaryWarning.value = `Note: A salary over ₱${softMaxLimit.toLocaleString()} (${salaryTypeLabel}) is unusually high. Please double-check if this is correct.`;
      }
    }
  };

  watch(() => form.is_negotiable, validateSalary);
  watch(() => form.salary.salary_type, validateSalary);
  watch(() => form.salary.job_min_salary, validateSalary);
  watch(() => form.salary.job_max_salary, validateSalary);

  return {
    salaryError,
    salaryWarning,
    validateSalary
  };
}
