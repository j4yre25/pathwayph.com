import { computed } from 'vue';

export function useFormattedMobileNumber(form, fieldName, countryCode = '+63', maxLength = 10) {
    const formattedMobileNumber = computed({
        get: () => {
            let rawNumber = form[fieldName].replace(/\D/g, ""); // Remove non-numeric characters
            if (rawNumber.length > maxLength) rawNumber = rawNumber.slice(0, maxLength); // Limit to maxLength
            return `${countryCode} ${rawNumber.replace(/(\d{3})(\d{3})(\d{4})/, "$1 $2 $3")}`.trim(); // Format as +63 XXX XXX XXXX
        },
        set: (value) => {
            let rawValue = value.replace(/\D/g, ""); // Remove non-numeric characters
            if (rawValue.startsWith(countryCode.replace(/\D/g, ""))) rawValue = rawValue.slice(countryCode.replace(/\D/g, "").length); // Remove country code
            if (rawValue.startsWith("0")) rawValue = rawValue.slice(1); // Remove leading zero
            if (rawValue.length > maxLength) rawValue = rawValue.slice(0, maxLength); // Limit to maxLength
            form[fieldName] = rawValue; // Update the form field
        },
    });

    return { formattedMobileNumber };
}