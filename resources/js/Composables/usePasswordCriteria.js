// This file is for the password criteria modal

import { computed } from 'vue';

export function usePasswordCriteria(form) {
    const passwordCriteria = computed(() => {
        const password = form.password || '';
        return {
            length: password.length >= 8,
            uppercaseLowercase: /[a-z]/.test(password) && /[A-Z]/.test(password),
            letter: /[a-zA-Z]/.test(password),
            number: /\d/.test(password),
            symbol: /[@$!%*?&.]/.test(password),
        };
    });

    return { passwordCriteria };
}
