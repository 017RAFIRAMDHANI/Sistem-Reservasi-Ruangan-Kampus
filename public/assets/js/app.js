document.querySelectorAll('[data-confirm]').forEach((element) => {
    element.addEventListener('click', (e) => {
        const message = element.getAttribute('data-confirm') || 'Apakah Anda yakin?';
        if (!confirm(message)) {
            e.preventDefault();
        }
    });
});

const startTimeSelect = document.querySelector('[data-start-time]');
const endTimeSelect = document.querySelector('[data-end-time]');

if (startTimeSelect && endTimeSelect) {
    const endOptionsTemplate = Array.from(endTimeSelect.options).map((option) => ({
        value: option.value,
        text: option.text,
    }));

    const refreshEndTimeOptions = () => {
        const startValue = startTimeSelect.value;
        const currentEndValue = endTimeSelect.value;

        endTimeSelect.innerHTML = '';

        endOptionsTemplate.forEach((optionData) => {
            if (optionData.value === '' || startValue === '' || optionData.value > startValue) {
                const option = document.createElement('option');
                option.value = optionData.value;
                option.textContent = optionData.text;
                endTimeSelect.appendChild(option);
            }
        });

        const hasCurrentValue = Array.from(endTimeSelect.options).some((option) => option.value === currentEndValue);
        if (hasCurrentValue) {
            endTimeSelect.value = currentEndValue;
        } else {
            endTimeSelect.selectedIndex = 0;
        }
    };

    startTimeSelect.addEventListener('change', refreshEndTimeOptions);
    refreshEndTimeOptions();
}

const sidebar = document.querySelector('.sidebar');
const navToggle = document.querySelector('.nav-toggle');

if (sidebar && navToggle) {
    const mobileBreakpoint = window.matchMedia('(max-width: 991px)');

    const syncMobileMenu = () => {
        if (mobileBreakpoint.matches) {
            sidebar.classList.remove('is-open');
            navToggle.setAttribute('aria-expanded', 'false');
            navToggle.setAttribute('aria-label', 'Buka menu navigasi');
        } else {
            sidebar.classList.remove('is-open');
            navToggle.setAttribute('aria-expanded', 'false');
            navToggle.setAttribute('aria-label', 'Menu navigasi');
        }
    };

    navToggle.addEventListener('click', () => {
        const isOpen = sidebar.classList.toggle('is-open');
        navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        navToggle.setAttribute('aria-label', isOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi');
    });

    sidebar.querySelectorAll('.menu a').forEach((link) => {
        link.addEventListener('click', () => {
            if (mobileBreakpoint.matches) {
                sidebar.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.setAttribute('aria-label', 'Buka menu navigasi');
            }
        });
    });

    if (typeof mobileBreakpoint.addEventListener === 'function') {
        mobileBreakpoint.addEventListener('change', syncMobileMenu);
    } else if (typeof mobileBreakpoint.addListener === 'function') {
        mobileBreakpoint.addListener(syncMobileMenu);
    }

    syncMobileMenu();
}
