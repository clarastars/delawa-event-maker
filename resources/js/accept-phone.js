import intlTelInput from 'intl-tel-input';
import 'intl-tel-input/build/css/intlTelInput.css';

const E164_REGEX = /^\+[1-9]\d{6,14}$/;

/** Saudi & Egypt first, then GCC, then other Arab states; remaining countries follow alphabetically. */
const COUNTRY_ORDER = [
    'sa',
    'eg',
    // GCC (excluding Saudi Arabia, listed above)
    'ae',
    'bh',
    'kw',
    'om',
    'qa',
    // Other Arab countries
    'jo',
    'lb',
    'ps',
    'sy',
    'iq',
    'ye',
    'ly',
    'tn',
    'dz',
    'ma',
    'mr',
    'sd',
    'so',
    'dj',
    'km',
];

const ERROR_MESSAGES = {
    en: {
        invalid: 'Please enter a valid phone number.',
        invalidCountry: 'Invalid country code.',
        tooShort: 'Phone number is too short.',
        tooLong: 'Phone number is too long.',
        notNumber: 'Please enter numbers only.',
        invalidLength: 'Phone number length is invalid.',
        required: 'Phone number is required.',
        format: 'Please enter a valid international phone number (e.g. +9665XXXXXXXX).',
    },
    ar: {
        invalid: 'يرجى إدخال رقم جوال صحيح.',
        invalidCountry: 'رمز الدولة غير صالح.',
        tooShort: 'رقم الجوال قصير جداً.',
        tooLong: 'رقم الجوال طويل جداً.',
        notNumber: 'يرجى إدخال أرقام فقط.',
        invalidLength: 'طول رقم الجوال غير صالح.',
        required: 'رقم الجوال مطلوب.',
        format: 'يرجى إدخال رقم جوال دولي صحيح (مثال: +9665XXXXXXXX).',
    },
};

function messageForError(errorCode, locale) {
    const messages = ERROR_MESSAGES[locale] ?? ERROR_MESSAGES.en;

    switch (errorCode) {
        case 1:
            return messages.invalidCountry;
        case 2:
            return messages.tooShort;
        case 3:
            return messages.tooLong;
        case 4:
            return messages.notNumber;
        case 5:
            return messages.invalidLength;
        default:
            return messages.invalid;
    }
}

function stripLeadingZero(value) {
    return value.replace(/^0+/, '');
}

function buildE164FromVisible(iti, input) {
    const dialCode = iti.getSelectedCountryData()?.dialCode ?? '';
    const localDigits = stripLeadingZero(input.value.replace(/\D/g, ''));

    if (dialCode === '' || localDigits === '') {
        return '';
    }

    return `+${dialCode}${localDigits}`;
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('accept-phone-form');

    if (! form) {
        return;
    }

    const input = document.getElementById('phone');
    const errorEl = document.getElementById('phone-client-error');
    const locale = form.dataset.locale === 'ar' ? 'ar' : 'en';
    const messages = ERROR_MESSAGES[locale] ?? ERROR_MESSAGES.en;
    const initialValue = input?.value?.trim() ?? '';

    const iti = intlTelInput(input, {
        initialCountry: 'sa',
        countryOrder: COUNTRY_ORDER,
        separateDialCode: true,
        strictMode: true,
        loadUtils: () => import('intl-tel-input/build/js/utils.js'),
    });

    input.closest('.iti')?.querySelector('.iti__dropdown-content')?.setAttribute('dir', 'ltr');

    if (initialValue !== '') {
        iti.setNumber(initialValue);
    }

    const showError = (message) => {
        if (! errorEl) {
            return;
        }

        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
        input?.classList.add('border-red-400', 'ring-red-100');
        input?.classList.remove('border-slate-200');
    };

    const clearError = () => {
        if (! errorEl) {
            return;
        }

        errorEl.textContent = '';
        errorEl.classList.add('hidden');
        input?.classList.remove('border-red-400', 'ring-red-100');
        input?.classList.add('border-slate-200');
    };

    const syncPhoneValue = () => {
        let e164 = iti.getNumber();

        if (! E164_REGEX.test(e164)) {
            e164 = buildE164FromVisible(iti, input);
        }

        if (E164_REGEX.test(e164)) {
            input.value = e164;
        }

        return e164;
    };

    const validatePhone = () => {
        const digitsOnly = input.value.replace(/\D/g, '');

        if (digitsOnly === '') {
            showError(messages.required);

            return false;
        }

        if (! iti.isValidNumber()) {
            showError(messageForError(iti.getValidationError(), locale));

            return false;
        }

        const e164 = syncPhoneValue();

        if (! E164_REGEX.test(e164)) {
            showError(messages.format);

            return false;
        }

        clearError();

        return true;
    };

    input.addEventListener('blur', () => {
        const dialCode = iti.getSelectedCountryData()?.dialCode ?? '';
        const localDigits = stripLeadingZero(input.value.replace(/\D/g, ''));

        if (dialCode !== '' && localDigits !== '') {
            input.value = localDigits;
        }

        syncPhoneValue();
    });

    input.addEventListener('countrychange', () => {
        clearError();
    });

    input.addEventListener('input', () => {
        if (errorEl && ! errorEl.classList.contains('hidden')) {
            clearError();
        }
    });

    form.addEventListener('submit', (event) => {
        if (! validatePhone()) {
            event.preventDefault();
            input.focus();
        }
    });
});
