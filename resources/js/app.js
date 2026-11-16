import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';

window.Alpine = Alpine;
window.ApexCharts = ApexCharts;

// Theme Controller (Light/Dark mode with gray palette)
window.themeManager = {
    isDark: false,
    init() {
        this.isDark = localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
        this.applyTheme();
    },
    toggle() {
        this.isDark = !this.isDark;
        localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
        this.applyTheme();
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { isDark: this.isDark } }));
    },
    applyTheme() {
        if (this.isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
};

window.themeManager.init();

// Toast notification helper
window.showToast = (message, type = 'info') => {
    window.dispatchEvent(new CustomEvent('toast-notify', { detail: { message, type } }));
};

// Format bytes helper
window.formatBytes = (bytes, decimals = 2) => {
    if (!+bytes) return '0 B';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
};

// Format currency IDR helper
window.formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number || 0);
};

// Parse formatted number string into integer (e.g. "50.000" -> 50000, "5000.00" -> 5000, "5.000.000" -> 5000000)
window.parseNumber = (val) => {
    if (val === null || val === undefined || val === '') return 0;
    if (typeof val === 'number') return Math.round(val);

    let str = String(val).trim();
    if (!str) return 0;

    // If it contains comma as decimal separator (e.g. "5.000,00" or "5000,00")
    if (str.includes(',')) {
        str = str.split(',')[0];
    }

    // Check for standard SQL float string format: single dot followed by 1 or 2 decimal digits (e.g. "5000.00", "0.00", "125.50")
    if (/^\d+\.\d{1,2}$/.test(str)) {
        return Math.round(parseFloat(str));
    }

    // Otherwise, treat dots as thousand separators (e.g. "5.000", "5.000.000", "Rp 50.000")
    const clean = str.replace(/[^0-9]/g, '');
    return clean ? parseInt(clean, 10) : 0;
};

// Format raw number into dot-separated thousand string (e.g. 50000 -> "50.000")
window.formatNumber = (val, decimals = 0) => {
    if (val === null || val === undefined || val === '') return '0';
    const num = typeof val === 'number' ? val : window.parseNumber(val);
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(num);
};

// Parse flexible date input into valid Date object
window.parseDateObj = (dateVal) => {
    if (!dateVal || dateVal === '-') return null;
    if (dateVal instanceof Date) return isNaN(dateVal.getTime()) ? null : dateVal;
    
    if (typeof dateVal === 'string') {
        let str = dateVal.trim();
        if (/^\d{4}-\d{2}-\d{2}/.test(str)) {
            str = str.replace(' ', 'T');
        }
        const parsed = new Date(str);
        if (!isNaN(parsed.getTime())) return parsed;

        const fallback = new Date(dateVal.replace(/-/g, '/'));
        if (!isNaN(fallback.getTime())) return fallback;
    }
    const d = new Date(dateVal);
    return isNaN(d.getTime()) ? null : d;
};

// Format date into Indonesian localized date string (e.g. "15 November 2026" or "15 Nov 2026")
window.formatDateIndo = (dateVal, options = {}) => {
    const d = window.parseDateObj(dateVal);
    if (!d) return dateVal ? String(dateVal) : '-';

    try {
        const isShort = options.shortMonth || options.short;
        const defaultOpts = {
            day: 'numeric',
            month: isShort ? 'short' : 'long',
            year: 'numeric',
            ...options
        };
        delete defaultOpts.shortMonth;
        delete defaultOpts.short;
        return new Intl.DateTimeFormat('id-ID', defaultOpts).format(d);
    } catch (e) {
        return String(dateVal);
    }
};

// Format datetime into Indonesian localized format (e.g. "15 Nov 2026, 14:30")
window.formatDateTimeIndo = (dateVal, withSeconds = false) => {
    const d = window.parseDateObj(dateVal);
    if (!d) return dateVal ? String(dateVal) : '-';

    try {
        const opts = {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        };
        if (withSeconds) {
            opts.second = '2-digit';
        }
        return new Intl.DateTimeFormat('id-ID', opts).format(d);
    } catch (e) {
        return String(dateVal);
    }
};

window.formatDate = window.formatDateIndo;
window.formatDateTime = window.formatDateTimeIndo;

// Alpine Custom Directive: x-money for live thousand separator currency inputs
Alpine.directive('money', (el, { expression }, { evaluateLater, effect, cleanup }) => {
    const getModel = evaluateLater(expression);
    const setModel = evaluateLater(`${expression} = $val`);

    const updateDisplay = (val) => {
        if (val === null || val === undefined || val === '') {
            el.value = '';
            return;
        }
        const num = typeof val === 'number' ? val : window.parseNumber(val);
        el.value = num === 0 ? '0' : new Intl.NumberFormat('id-ID').format(num);
    };

    el.setAttribute('inputmode', 'numeric');

    const handleInput = (e) => {
        const raw = e.target.value.replace(/[^0-9]/g, '');
        const num = raw ? parseInt(raw, 10) : 0;
        setModel(() => {}, { scope: { $val: num } });
        e.target.value = raw === '' ? '' : (num === 0 ? '0' : new Intl.NumberFormat('id-ID').format(num));
    };

    el.addEventListener('input', handleInput);
    cleanup(() => el.removeEventListener('input', handleInput));

    effect(() => {
        getModel((val) => {
            if (document.activeElement !== el) {
                updateDisplay(val);
            }
        });
    });
});

Alpine.magic('formatNumber', () => window.formatNumber);
Alpine.magic('formatDate', () => window.formatDateIndo);
Alpine.magic('formatDateTime', () => window.formatDateTimeIndo);
Alpine.magic('formatRupiah', () => window.formatRupiah);
Alpine.magic('formatBytes', () => window.formatBytes);

Alpine.start();

// PWA Service Worker Registration
if ('serviceWorker' in navigator && (window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1' || window.location.hostname.endsWith('.lan') || window.location.hostname.endsWith('.test'))) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then((registration) => {
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    if (newWorker) {
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                window.dispatchEvent(new CustomEvent('pwa-update-available'));
                            }
                        });
                    }
                });
            })
            .catch((err) => {
                console.debug('PWA ServiceWorker registration notice:', err);
            });
    });
}
