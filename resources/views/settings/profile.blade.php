<x-layouts.app>
    <x-slot:header>Profil & Identitas Aplikasi</x-slot:header>

    <div class="space-y-6" x-data="profileSettingsManager()">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Pengaturan Profil & Aplikasi</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Kelola data akun administrator, kata sandi, serta kustomisasi logo dan nama aplikasi</p>
            </div>

            <!-- Navigation Tabs -->
            <div class="flex items-center p-1 bg-zinc-200/80 dark:bg-zinc-800 rounded-xl text-xs font-semibold self-start sm:self-auto">
                <button 
                    type="button"
                    @click="activeTab = 'profile'"
                    :class="activeTab === 'profile' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100'"
                    class="px-4 py-2 rounded-lg transition-all flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profil & Keamanan</span>
                </button>
                <button 
                    type="button"
                    @click="activeTab = 'branding'"
                    :class="activeTab === 'branding' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100'"
                    class="px-4 py-2 rounded-lg transition-all flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                    <span>Identitas & Icon Aplikasi</span>
                </button>
            </div>
        </div>

        <!-- ================= TAB 1: PROFIL & KEAMANAN ================= -->
        <div x-show="activeTab === 'profile'" class="space-y-6" style="display: none;">
            <!-- Section 1: Informasi Akun -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-xs">
                <div class="pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Informasi Akun Administrator</h2>
                    <p class="text-xs text-zinc-500">Perbarui nama dan email yang digunakan untuk masuk ke sistem</p>
                </div>

                <form @submit.prevent="saveProfile()" class="mt-6 space-y-6">
                    <!-- Avatar Upload -->
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Foto Profil / Avatar</label>
                        <div class="flex items-center gap-4">
                            <!-- Preview Box -->
                            <div class="relative w-16 h-16 rounded-full overflow-hidden bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 flex items-center justify-center font-bold text-lg border-2 border-zinc-200 dark:border-zinc-700 shadow-sm shrink-0">
                                <template x-if="avatarPreview">
                                    <img :src="avatarPreview" alt="Avatar" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!avatarPreview && user.avatar">
                                    <img :src="'/' + user.avatar" alt="Avatar" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!avatarPreview && !user.avatar">
                                    <span x-text="(user.name || 'AD').substring(0, 2).toUpperCase()"></span>
                                </template>
                            </div>

                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2">
                                    <label class="px-3 py-1.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-semibold cursor-pointer transition-colors">
                                        <span>Pilih Foto Baru</span>
                                        <input type="file" @change="handleAvatarFile($event)" accept="image/*" class="hidden">
                                    </label>
                                    <button 
                                        type="button" 
                                        x-show="user.avatar || avatarPreview" 
                                        @click="removeAvatar()" 
                                        class="px-3 py-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 text-xs font-semibold transition-colors"
                                    >
                                        Hapus Foto
                                    </button>
                                </div>
                                <p class="text-[11px] text-zinc-400">Format: JPG, PNG, WEBP, SVG (Maks. 2MB)</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Nama Lengkap *</label>
                            <input 
                                type="text" 
                                x-model="userForm.name" 
                                required 
                                placeholder="Nama Administrator" 
                                class="w-full px-3.5 py-2.5 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Alamat Email Login *</label>
                            <input 
                                type="email" 
                                x-model="userForm.email" 
                                required 
                                placeholder="admin@example.com" 
                                class="w-full px-3.5 py-2.5 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400"
                            >
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button 
                            type="submit" 
                            :disabled="isSavingProfile" 
                            class="px-5 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-white text-white dark:text-zinc-900 font-semibold text-xs transition-colors shadow-xs flex items-center gap-2"
                        >
                            <span x-show="isSavingProfile">Menyimpan...</span>
                            <span x-show="!isSavingProfile">Simpan Perubahan Profil</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Section 2: Ganti Password -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-xs">
                <div class="pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Ganti Kata Sandi (Password)</h2>
                    <p class="text-xs text-zinc-500">Pastikan akun Anda menggunakan kombinasi password yang kuat dan aman</p>
                </div>

                <form @submit.prevent="savePassword()" class="mt-6 space-y-4 max-w-xl">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Password Saat Ini (Lama) *</label>
                        <div class="relative">
                            <input 
                                :type="showCurrentPass ? 'text' : 'password'" 
                                x-model="passForm.current_password" 
                                required 
                                placeholder="Masukkan password lama" 
                                class="w-full px-3.5 py-2.5 pr-10 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400"
                            >
                            <button type="button" @click="showCurrentPass = !showCurrentPass" class="absolute inset-y-0 right-0 pr-3 flex items-center text-zinc-400 hover:text-zinc-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Password Baru *</label>
                            <div class="relative">
                                <input 
                                    :type="showNewPass ? 'text' : 'password'" 
                                    x-model="passForm.password" 
                                    required 
                                    minlength="6" 
                                    placeholder="Min. 6 karakter" 
                                    class="w-full px-3.5 py-2.5 pr-10 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400"
                                >
                                <button type="button" @click="showNewPass = !showNewPass" class="absolute inset-y-0 right-0 pr-3 flex items-center text-zinc-400 hover:text-zinc-600">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Konfirmasi Password Baru *</label>
                            <div class="relative">
                                <input 
                                    :type="showNewPass ? 'text' : 'password'" 
                                    x-model="passForm.password_confirmation" 
                                    required 
                                    minlength="6" 
                                    placeholder="Ulangi password baru" 
                                    class="w-full px-3.5 py-2.5 pr-10 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button 
                            type="submit" 
                            :disabled="isSavingPassword" 
                            class="px-5 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-white text-white dark:text-zinc-900 font-semibold text-xs transition-colors shadow-xs flex items-center gap-2"
                        >
                            <span x-show="isSavingPassword">Mengubah Password...</span>
                            <span x-show="!isSavingPassword">Perbarui Kata Sandi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= TAB 2: IDENTITAS & ICON APLIKASI ================= -->
        <div x-show="activeTab === 'branding'" class="space-y-6" style="display: none;">
            <form @submit.prevent="saveBranding()" class="space-y-6">
                <!-- Section: Nama & Tagline -->
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-xs">
                    <div class="pb-4 border-b border-zinc-100 dark:border-zinc-800">
                        <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Nama & Identitas Aplikasi</h2>
                        <p class="text-xs text-zinc-500">Nama dan teks ini ditampilkan pada Sidebar, Header Navbar, dan Halaman Login</p>
                    </div>

                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Nama Aplikasi / Brand *</label>
                            <input 
                                type="text" 
                                x-model="brandForm.app_name" 
                                required 
                                placeholder="MikroTik Manager" 
                                class="w-full px-3.5 py-2.5 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400 font-bold"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Tagline / Slogan</label>
                            <input 
                                type="text" 
                                x-model="brandForm.tagline" 
                                placeholder="Hotspot & Bandwidth Management" 
                                class="w-full px-3.5 py-2.5 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Kontak WhatsApp / Support</label>
                            <input 
                                type="text" 
                                x-model="brandForm.contact_phone" 
                                placeholder="0812-3456-7890" 
                                class="w-full px-3.5 py-2.5 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400"
                            >
                        </div>
                    </div>
                </div>

                <!-- Section: Logo & Favicon Upload -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Logo Aplikasi -->
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="pb-4 border-b border-zinc-100 dark:border-zinc-800">
                                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Logo / Ikon Aplikasi</h3>
                                <p class="text-xs text-zinc-500">Ikon yang tampil di pojok atas Sidebar dan Halaman Login</p>
                            </div>

                            <div class="py-6 flex flex-col items-center justify-center">
                                <!-- Preview on dark & light background -->
                                <div class="p-4 rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-xl bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 flex items-center justify-center overflow-hidden shadow-sm">
                                        <template x-if="logoPreview">
                                            <img :src="logoPreview" alt="Logo" class="w-full h-full object-contain p-1">
                                        </template>
                                        <template x-if="!logoPreview && setting.app_logo">
                                            <img :src="'/' + setting.app_logo" alt="Logo" class="w-full h-full object-contain p-1">
                                        </template>
                                        <template x-if="!logoPreview && !setting.app_logo">
                                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </template>
                                    </div>
                                    <div class="text-left">
                                        <strong class="text-xs font-bold text-zinc-900 dark:text-zinc-100 block" x-text="brandForm.app_name || 'MikroTik Manager'"></strong>
                                        <span class="text-[10px] text-zinc-400 block" x-text="brandForm.tagline || 'Hotspot & Bandwidth'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between gap-2">
                            <label class="px-3.5 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-semibold cursor-pointer transition-colors">
                                <span>Pilih Logo Baru</span>
                                <input type="file" @change="handleLogoFile($event)" accept="image/*" class="hidden">
                            </label>
                            <button 
                                type="button" 
                                x-show="setting.app_logo || logoPreview" 
                                @click="removeLogo()" 
                                class="px-3.5 py-2 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 text-xs font-semibold transition-colors"
                            >
                                Reset ke Icon Default
                            </button>
                        </div>
                    </div>

                    <!-- Favicon Browser -->
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="pb-4 border-b border-zinc-100 dark:border-zinc-800">
                                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Favicon Browser Web</h3>
                                <p class="text-xs text-zinc-500">Ikon kecil yang tampil pada tab browser pengguna</p>
                            </div>

                            <div class="py-6 flex flex-col items-center justify-center">
                                <!-- Mock Browser Tab Preview -->
                                <div class="px-4 py-2 rounded-t-lg bg-zinc-200 dark:bg-zinc-800 border-t border-x border-zinc-300 dark:border-zinc-700 flex items-center gap-2 text-xs font-medium text-zinc-700 dark:text-zinc-300 shadow-xs">
                                    <div class="w-4 h-4 rounded flex items-center justify-center overflow-hidden">
                                        <template x-if="faviconPreview">
                                            <img :src="faviconPreview" alt="Favicon" class="w-full h-full object-contain">
                                        </template>
                                        <template x-if="!faviconPreview && setting.app_favicon">
                                            <img :src="'/' + setting.app_favicon" alt="Favicon" class="w-full h-full object-contain">
                                        </template>
                                        <template x-if="!faviconPreview && !setting.app_favicon">
                                            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </template>
                                    </div>
                                    <span x-text="(brandForm.app_name || 'MikroTik Manager') + ' - Dashboard'"></span>
                                    <span class="text-zinc-400 text-[10px] ml-2">✕</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between gap-2">
                            <label class="px-3.5 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-semibold cursor-pointer transition-colors">
                                <span>Pilih Favicon (.ico / .png)</span>
                                <input type="file" @change="handleFaviconFile($event)" accept=".ico,image/png,image/svg+xml" class="hidden">
                            </label>
                            <button 
                                type="button" 
                                x-show="setting.app_favicon || faviconPreview" 
                                @click="removeFavicon()" 
                                class="px-3.5 py-2 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 text-xs font-semibold transition-colors"
                            >
                                Reset Favicon
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button 
                        type="submit" 
                        :disabled="isSavingBranding" 
                        class="px-6 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-white text-white dark:text-zinc-900 font-bold text-xs transition-colors shadow-xs flex items-center gap-2"
                    >
                        <span x-show="isSavingBranding">Menyimpan Identitas...</span>
                        <span x-show="!isSavingBranding">Simpan Identitas & Icon Aplikasi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function profileSettingsManager() {
            return {
                activeTab: 'profile',
                user: @json($user ?? []),
                setting: @json($setting ?? []),
                
                // Forms
                userForm: {
                    name: '{{ $user->name ?? "" }}',
                    email: '{{ $user->email ?? "" }}'
                },
                passForm: {
                    current_password: '',
                    password: '',
                    password_confirmation: ''
                },
                brandForm: {
                    app_name: '{{ $setting->app_name ?? "MikroTik Manager" }}',
                    tagline: '{{ $setting->tagline ?? "Hotspot & Bandwidth Management" }}',
                    contact_phone: '{{ $setting->contact_phone ?? "" }}'
                },

                // Toggles & Flags
                showCurrentPass: false,
                showNewPass: false,
                isSavingProfile: false,
                isSavingPassword: false,
                isSavingBranding: false,

                // File previews & states
                avatarFile: null,
                avatarPreview: null,
                shouldRemoveAvatar: false,

                logoFile: null,
                logoPreview: null,
                shouldRemoveLogo: false,

                faviconFile: null,
                faviconPreview: null,
                shouldRemoveFavicon: false,

                handleAvatarFile(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.avatarFile = file;
                        this.shouldRemoveAvatar = false;
                        const reader = new FileReader();
                        reader.onload = (e) => this.avatarPreview = e.target.result;
                        reader.readAsDataURL(file);
                    }
                },

                removeAvatar() {
                    this.avatarFile = null;
                    this.avatarPreview = null;
                    this.user.avatar = null;
                    this.shouldRemoveAvatar = true;
                },

                handleLogoFile(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.logoFile = file;
                        this.shouldRemoveLogo = false;
                        const reader = new FileReader();
                        reader.onload = (e) => this.logoPreview = e.target.result;
                        reader.readAsDataURL(file);
                    }
                },

                removeLogo() {
                    this.logoFile = null;
                    this.logoPreview = null;
                    this.setting.app_logo = null;
                    this.shouldRemoveLogo = true;
                },

                handleFaviconFile(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.faviconFile = file;
                        this.shouldRemoveFavicon = false;
                        const reader = new FileReader();
                        reader.onload = (e) => this.faviconPreview = e.target.result;
                        reader.readAsDataURL(file);
                    }
                },

                removeFavicon() {
                    this.faviconFile = null;
                    this.faviconPreview = null;
                    this.setting.app_favicon = null;
                    this.shouldRemoveFavicon = true;
                },

                saveProfile() {
                    this.isSavingProfile = true;
                    const fd = new FormData();
                    fd.append('_method', 'PUT');
                    fd.append('name', this.userForm.name);
                    fd.append('email', this.userForm.email);
                    if (this.avatarFile) fd.append('avatar', this.avatarFile);
                    if (this.shouldRemoveAvatar) fd.append('remove_avatar', '1');

                    fetch('{{ route("settings.profile.update") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: fd
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isSavingProfile = false;
                        if (data.success) {
                            this.user = data.user;
                            window.showToast(data.message || 'Profil berhasil diperbarui!', 'success');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            window.showToast(data.message || 'Gagal memperbarui profil', 'error');
                        }
                    })
                    .catch(() => {
                        this.isSavingProfile = false;
                        window.showToast('Terjadi kesalahan koneksi', 'error');
                    });
                },

                savePassword() {
                    if (this.passForm.password !== this.passForm.password_confirmation) {
                        window.showToast('Konfirmasi password baru tidak cocok!', 'error');
                        return;
                    }

                    this.isSavingPassword = true;
                    fetch('{{ route("settings.profile.password") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            _method: 'PUT',
                            ...this.passForm
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isSavingPassword = false;
                        if (data.success) {
                            this.passForm = { current_password: '', password: '', password_confirmation: '' };
                            window.showToast(data.message || 'Password berhasil diubah!', 'success');
                        } else {
                            window.showToast(data.message || 'Gagal mengubah password', 'error');
                        }
                    })
                    .catch(() => {
                        this.isSavingPassword = false;
                        window.showToast('Terjadi kesalahan koneksi', 'error');
                    });
                },

                saveBranding() {
                    this.isSavingBranding = true;
                    const fd = new FormData();
                    fd.append('app_name', this.brandForm.app_name);
                    fd.append('tagline', this.brandForm.tagline);
                    fd.append('contact_phone', this.brandForm.contact_phone);
                    if (this.logoFile) fd.append('app_logo', this.logoFile);
                    if (this.shouldRemoveLogo) fd.append('remove_logo', '1');
                    if (this.faviconFile) fd.append('app_favicon', this.faviconFile);
                    if (this.shouldRemoveFavicon) fd.append('remove_favicon', '1');

                    fetch('{{ route("settings.profile.branding") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: fd
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isSavingBranding = false;
                        if (data.success) {
                            this.setting = data.setting;
                            window.showToast(data.message || 'Identitas aplikasi berhasil disimpan!', 'success');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            window.showToast(data.message || 'Gagal menyimpan branding', 'error');
                        }
                    })
                    .catch(() => {
                        this.isSavingBranding = false;
                        window.showToast('Terjadi kesalahan koneksi', 'error');
                    });
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
