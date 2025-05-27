import { watch } from 'vue';

export function useWorkEnvironmentValidation(form) {

  const validateLocation = () => {
    const selectedEnvironment = parseInt(form.work_environment);
    if (selectedEnvironment !== 2 && (!form.location || form.location.trim() === '')) {
      form.errors.location = 'Location is required for On-site or Hybrid jobs.';
      return false;
    } else {
      form.errors.location = '';
      return true;
    }
  };

  watch(() => form.work_environment, () => {
    validateLocation();
  });

  return {
    validateLocation
  };
}
