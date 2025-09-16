import { computed } from "vue";

export function useFormattedBIRTIN(form, field = "bir_tin") {
  const formattedBIRTIN = computed({
    get() {
      const raw = String(form[field] || "");
      const digits = raw.replace(/\D/g, "").slice(0, 12); // max 12 digits
      const parts = digits.match(/.{1,3}/g) || [];
      return parts.join("-");
    },
    set(v) {
      const digits = String(v || "").replace(/\D/g, "").slice(0, 12);
      const parts = digits.match(/.{1,3}/g) || [];
      form[field] = parts.join("-");
    },
  });

  return { formattedBIRTIN };
}