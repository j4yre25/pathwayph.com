import { computed } from 'vue';

export function useFormattedTelephoneNumber(form, fieldName = 'telephone_number') {
    const formattedTelephoneNumber = computed({
        get: () => {
            let raw = form[fieldName].replace(/\D/g, ""); // Remove non-digit chars

            if (raw.startsWith('02') && raw.length > 2) {
                // Metro Manila (02) area code + 7-digit number
                raw = raw.slice(0, 10); 
                return raw.replace(/(02)(\d{3})(\d{4})/, '($1) $2-$3');
            } else if (raw.startsWith('0') && raw.length > 3) {
                // Other area codes (0XX) + 7-digit number
                raw = raw.slice(0, 11); // Max 11 digits
                return raw.replace(/(0\d{2})(\d{3})(\d{4})/, '($1) $2-$3');
            }

            return raw;
        },

        set: (value) => {
            let digits = value.replace(/\D/g, "");

            if (digits.length > 15) digits = digits.slice(0, 15);
            form[fieldName] = digits;
        },
    });

    return { formattedTelephoneNumber };
}
