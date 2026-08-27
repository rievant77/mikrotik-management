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
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
};

Alpine.start();
