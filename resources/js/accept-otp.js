const RESEND_WAIT_PLACEHOLDER = ':seconds';

function formatResendWait(template, seconds, locale) {
    const displaySeconds = locale === 'ar'
        ? String(seconds).replace(/\d/g, (digit) => '٠١٢٣٤٥٦٧٨٩'[digit])
        : String(seconds);

    return template.replace(RESEND_WAIT_PLACEHOLDER, displaySeconds);
}

function disableFormOnSubmit(form) {
    form.addEventListener('submit', (event) => {
        if (event.defaultPrevented) {
            return;
        }

        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
        });
    });
}

function startResendCooldown(button) {
    const resendLabel = button.dataset.resendLabel ?? button.textContent.trim();
    const waitTemplate = button.dataset.resendWait ?? '';
    const locale = button.dataset.locale === 'ar' ? 'ar' : 'en';

    let secondsRemaining = Number.parseInt(button.dataset.secondsRemaining ?? '0', 10);

    if (Number.isNaN(secondsRemaining) || secondsRemaining <= 0) {
        return;
    }

    const updateLabel = () => {
        button.textContent = formatResendWait(waitTemplate, secondsRemaining, locale);
    };

    button.disabled = true;
    button.classList.add('cursor-not-allowed', 'text-slate-400', 'no-underline');
    button.classList.remove('text-[#4E2E36]', 'hover:text-[#4E2E36]');
    updateLabel();

    const intervalId = window.setInterval(() => {
        secondsRemaining -= 1;

        if (secondsRemaining <= 0) {
            window.clearInterval(intervalId);
            button.disabled = false;
            button.textContent = resendLabel;
            button.classList.remove('cursor-not-allowed', 'text-slate-400', 'no-underline');
            button.classList.add('text-[#4E2E36]');

            return;
        }

        updateLabel();
    }, 1000);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-disable-on-submit]').forEach((form) => {
        disableFormOnSubmit(form);
    });

    const resendButton = document.getElementById('otp-resend-btn');

    if (resendButton) {
        startResendCooldown(resendButton);
    }
});
