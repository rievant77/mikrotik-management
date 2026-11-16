<x-layouts.app>
    <x-slot:header>Analisis Trafik & Konten</x-slot:header>

    <div class="space-y-6" x-data="trafficAnalyticsManager(@js($data))">
        <!-- Header & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Analisis Trafik & Konten</h1>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold"
                         :class="analytics.is_rules_installed ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20'">
                        <span class="w-1.5 h-1.5 rounded-full" :class="analytics.is_rules_installed ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"></span>
                        <span x-text="analytics.is_rules_installed ? 'Mangle MikroTik Aktif' : 'Estimasi Sesi'"></span>
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Klasifikasi konsumsi bandwidth dan ranking pengguna per aplikasi
                </p>
            </div>

            <!-- Action Controls -->
            <div class="flex items-center gap-2">
                <!-- Kelola Signature & Port Button -->
                <button 
                    type="button" 
                    @click="openSignatureManager()" 
                    class="px-3.5 py-2 rounded-xl bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 text-xs font-semibold transition-colors border border-zinc-200 dark:border-zinc-700 shadow-xs flex items-center gap-2"
                    title="Kelola port game, domain, dan signature platform"
                >
                    <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    <span>Kelola Signature & Port</span>
                </button>

                <!-- Deploy Rules Button -->
                <button 
                    type="button" 
                    @click="deployRules()" 
                    :disabled="isDeploying"
                    class="px-3.5 py-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-white text-white dark:text-zinc-900 text-xs font-semibold transition-colors shadow-xs flex items-center gap-2"
                >
                    <svg class="w-4 h-4 text-emerald-400 dark:text-emerald-600" :class="{ 'animate-spin': isDeploying }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span x-text="isDeploying ? 'Memasang Filter...' : 'Pasang Filter di MikroTik'"></span>
                </button>
            </div>
        </div>

        <!-- KPI Metric Cards (4 Categories) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Video Streaming -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-xs relative overflow-hidden">
                <div class="absolute top-0 left-0 h-1 w-full bg-rose-500"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Streaming Video</span>
                    <div class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100" x-text="analytics.categories.video.formatted_bytes || '0 B'"></div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs font-semibold text-rose-600 dark:text-rose-400" x-text="(analytics.categories.video.percentage || 0) + '% dari total'"></span>
                        <span class="text-[11px] text-zinc-400">YouTube, TikTok, Netflix</span>
                    </div>
                </div>
            </div>

            <!-- 2. Social Media & Chat -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-xs relative overflow-hidden">
                <div class="absolute top-0 left-0 h-1 w-full bg-blue-500"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Sosmed & Chat</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100" x-text="analytics.categories.social_media.formatted_bytes || '0 B'"></div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs font-semibold text-blue-600 dark:text-blue-400" x-text="(analytics.categories.social_media.percentage || 0) + '% dari total'"></span>
                        <span class="text-[11px] text-zinc-400">WhatsApp, IG, FB, X</span>
                    </div>
                </div>
            </div>

            <!-- 3. Online Gaming -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-xs relative overflow-hidden">
                <div class="absolute top-0 left-0 h-1 w-full bg-emerald-500"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Online Gaming</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100" x-text="analytics.categories.gaming.formatted_bytes || '0 B'"></div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400" x-text="(analytics.categories.gaming.percentage || 0) + '% dari total'"></span>
                        <span class="text-[11px] text-zinc-400">MLBB, Free Fire, PUBG</span>
                    </div>
                </div>
            </div>

            <!-- 4. Cloud, Work & Browsing -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-xs relative overflow-hidden">
                <div class="absolute top-0 left-0 h-1 w-full bg-amber-500"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Cloud & Browsing</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100" x-text="analytics.categories.browsing.formatted_bytes || '0 B'"></div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs font-semibold text-amber-600 dark:text-amber-400" x-text="(analytics.categories.browsing.percentage || 0) + '% dari total'"></span>
                        <span class="text-[11px] text-zinc-400">Meet, Drive, E-Commerce</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Grid (Donut & Hourly Trend) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Donut Category Distribution -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Distribusi Kategori</h2>
                        <p class="text-xs text-zinc-500">Porsi konsumsi data berdasarkan jenis konten</p>
                    </div>
                    <span class="text-xs font-mono font-bold text-zinc-700 dark:text-zinc-300" x-text="analytics.formatted_total_traffic"></span>
                </div>
                <div id="donutChart" class="flex items-center justify-center min-h-[260px]"></div>
                
                <!-- Category Legend List -->
                <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0"></span>
                        <span class="text-zinc-600 dark:text-zinc-400 truncate">Video (<span x-text="analytics.categories.video.percentage + '%'"></span>)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shrink-0"></span>
                        <span class="text-zinc-600 dark:text-zinc-400 truncate">Sosmed (<span x-text="analytics.categories.social_media.percentage + '%'"></span>)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                        <span class="text-zinc-600 dark:text-zinc-400 truncate">Gaming (<span x-text="analytics.categories.gaming.percentage + '%'"></span>)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                        <span class="text-zinc-600 dark:text-zinc-400 truncate">Cloud/Web (<span x-text="analytics.categories.browsing.percentage + '%'"></span>)</span>
                    </div>
                </div>
            </div>

            <!-- 24-Hour Trend Area Chart -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-xs lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Tren Pemakaian 24 Jam (Hari Ini)</h2>
                        <p class="text-xs text-zinc-500">Aktivitas konsumsi bandwidth per jam menurut kategori aplikasi</p>
                    </div>
                    <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-[11px] font-medium text-zinc-600 dark:text-zinc-300">
                        <span>Puncak: 19:00 - 22:00</span>
                    </div>
                </div>
                <div id="hourlyChart" class="min-h-[260px]"></div>
            </div>
        </div>

        <!-- Platforms Breakdown Table -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xs overflow-hidden">
            <div class="p-5 border-b border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Rincian Aplikasi & Platform Terpopuler</h2>
                    <p class="text-xs text-zinc-500 mt-0.5">Klik baris aplikasi untuk melihat <span class="text-indigo-600 dark:text-indigo-400 font-semibold">ranking pengguna</span> yang mengakses aplikasi tersebut</p>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <span class="text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300" x-text="formatNumber(analytics.platforms.length) + ' Platform Terdeteksi'"></span>

                    <!-- Reset Counters Button -->
                    <button 
                        type="button" 
                        @click="resetCounters()" 
                        :disabled="isResetting"
                        class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-zinc-800 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-zinc-600 dark:text-zinc-400 hover:text-rose-600 dark:hover:text-rose-400 text-xs font-medium transition-colors border border-zinc-200 dark:border-zinc-700/80 flex items-center gap-1.5 shadow-xs"
                        title="Reset counter pemakaian trafik ke 0 di MikroTik"
                    >
                        <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': isResetting }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Reset Counter</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 text-zinc-500 font-semibold">
                            <th class="py-3.5 px-4 sm:px-6">Platform / Aplikasi</th>
                            <th class="py-3.5 px-4">Kategori Konten</th>
                            <th class="py-3.5 px-4">Total Pemakaian</th>
                            <th class="py-3.5 px-4 w-48">Porsi Konsumsi</th>
                            <th class="py-3.5 px-4 text-right">Detail & Rank</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                        <template x-for="(item, idx) in analytics.platforms" :key="item.platform">
                            <tr @click="openPlatformRank(item)" class="hover:bg-indigo-50/30 dark:hover:bg-indigo-950/20 cursor-pointer transition-colors group">
                                <td class="py-3.5 px-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 font-mono tracking-tight"
                                             :class="{
                                                 'bg-rose-500/10 text-rose-600 dark:text-rose-400': item.category_key === 'video',
                                                 'bg-blue-500/10 text-blue-600 dark:text-blue-400': item.category_key === 'social_media',
                                                 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400': item.category_key === 'gaming',
                                                 'bg-violet-500/10 text-violet-600 dark:text-violet-400': item.category_key === 'cloud_work',
                                                 'bg-amber-500/10 text-amber-600 dark:text-amber-400': item.category_key === 'browsing'
                                             }">
                                            <span x-text="item.platform.substring(0, 2).toUpperCase()"></span>
                                        </div>
                                        <div>
                                            <span class="font-bold text-zinc-900 dark:text-zinc-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors block" x-text="item.platform"></span>
                                            <span class="text-[11px] text-zinc-400" x-text="item.packets ? (formatNumber(item.packets) + ' packets') : 'Aktif'"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold"
                                          :class="{
                                              'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400': item.category_key === 'video',
                                              'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400': item.category_key === 'social_media',
                                              'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400': item.category_key === 'gaming',
                                              'bg-violet-50 dark:bg-violet-950/40 text-violet-700 dark:text-violet-400': item.category_key === 'cloud_work',
                                              'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400': item.category_key === 'browsing'
                                          }"
                                          x-text="item.category">
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100 font-mono text-xs" x-text="item.formatted_bytes"></span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-500"
                                                 :class="{
                                                     'bg-rose-500': item.category_key === 'video',
                                                     'bg-blue-500': item.category_key === 'social_media',
                                                     'bg-emerald-500': item.category_key === 'gaming',
                                                     'bg-violet-500': item.category_key === 'cloud_work',
                                                     'bg-amber-500': item.category_key === 'browsing'
                                                 }"
                                                 :style="'width: ' + (item.percentage || 0) + '%'"></div>
                                        </div>
                                        <span class="text-[11px] font-mono font-semibold text-zinc-600 dark:text-zinc-300 w-10 text-right" x-text="(item.percentage || 0) + '%'"></span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <button 
                                        type="button" 
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-zinc-100 dark:bg-zinc-800 group-hover:bg-indigo-600 group-hover:text-white dark:group-hover:bg-indigo-600 text-zinc-700 dark:text-zinc-300 text-xs font-semibold transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        <span>Rank User</span>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Table Card Footer Note -->
            <div class="px-5 py-3 border-t border-zinc-100 dark:border-zinc-800/80 bg-zinc-50/50 dark:bg-zinc-900/50 text-[11px] text-zinc-500 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Dihitung dari kernel MikroTik via Firewall Mangle (<span class="font-mono text-zinc-600 dark:text-zinc-400">/ip/firewall/mangle</span>), TLS SNI, dan port TCP/UDP.</span>
                </div>
                <button type="button" @click="openSignatureManager()" class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold shrink-0 flex items-center gap-1">
                    <span>Kelola Port Game & Domain</span>
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 1: Detail Pop-up User Rank Platform  -->
        <!-- ========================================== -->
        <div 
            x-show="showRankModal" 
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" 
            role="dialog" 
            aria-modal="true"
        >
            <!-- Backdrop -->
            <div 
                x-show="showRankModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-zinc-900/60 backdrop-blur-xs transition-opacity"
                @click="showRankModal = false"
            ></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div 
                    x-show="showRankModal"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl"
                >
                    <!-- Modal Header -->
                    <div class="px-6 py-5 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm font-mono">
                                <span x-text="(selectedPlatform?.platform || 'PL').substring(0, 2).toUpperCase()"></span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100" x-text="selectedPlatform?.platform || 'Detail Aplikasi'"></h3>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300" x-text="selectedPlatform?.category || 'Aplikasi'"></span>
                                </div>
                                <p class="text-xs text-zinc-500 mt-0.5">Peringkat pengguna hotspot yang paling banyak mengakses aplikasi ini</p>
                            </div>
                        </div>

                        <button 
                            type="button" 
                            @click="showRankModal = false"
                            class="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-5">
                        <!-- Summary 3-Column Strip -->
                        <div class="grid grid-cols-3 gap-3">
                            <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-800">
                                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Total Bandwidth</span>
                                <span class="text-base font-bold text-zinc-900 dark:text-zinc-100 font-mono mt-0.5 block" x-text="rankData?.formatted_total_bytes || selectedPlatform?.formatted_bytes || '0 B'"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-800">
                                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Pengguna Aktif</span>
                                <span class="text-base font-bold text-indigo-600 dark:text-indigo-400 mt-0.5 block">
                                    <span x-text="formatNumber(rankData?.active_users_count || (rankData?.users?.length || 0))"></span> User
                                </span>
                            </div>
                            <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-800">
                                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Kategori</span>
                                <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 mt-1 block truncate" x-text="rankData?.category || selectedPlatform?.category"></span>
                            </div>
                        </div>

                        <!-- Loading State -->
                        <div x-show="rankLoading" class="py-12 flex flex-col items-center justify-center space-y-3">
                            <svg class="w-8 h-8 text-indigo-600 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-xs text-zinc-500 font-medium">Memindai socket koneksi & pengguna aktif di router...</span>
                        </div>

                        <!-- Leaderboard Table -->
                        <div x-show="!rankLoading" class="rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                            <div class="max-h-80 overflow-y-auto">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead class="sticky top-0 bg-zinc-50 dark:bg-zinc-800/90 backdrop-blur-xs z-10 border-b border-zinc-200 dark:border-zinc-800 text-zinc-500 font-semibold">
                                        <tr>
                                            <th class="py-2.5 px-3 w-12 text-center">Rank</th>
                                            <th class="py-2.5 px-3">Pengguna & Perangkat</th>
                                            <th class="py-2.5 px-3">Kategori</th>
                                            <th class="py-2.5 px-3 text-right">Konsumsi</th>
                                            <th class="py-2.5 px-3 w-28">Porsi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                                        <template x-for="u in (rankData?.users || [])" :key="u.username + u.ip_address">
                                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                                <!-- Rank -->
                                                <td class="py-3 px-3 text-center">
                                                    <template x-if="u.rank === 1">
                                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300 font-bold text-xs ring-2 ring-amber-400/40">1</span>
                                                    </template>
                                                    <template x-if="u.rank === 2">
                                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-zinc-200 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200 font-bold text-xs">2</span>
                                                    </template>
                                                    <template x-if="u.rank === 3">
                                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900/60 dark:text-orange-300 font-bold text-xs">3</span>
                                                    </template>
                                                    <template x-if="u.rank > 3">
                                                        <span class="text-zinc-400 font-mono text-xs font-semibold" x-text="'#' + u.rank"></span>
                                                    </template>
                                                </td>

                                                <!-- User & Hostname -->
                                                <td class="py-3 px-3">
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-2 h-2 rounded-full shrink-0" :class="u.is_online ? 'bg-emerald-500 animate-pulse' : 'bg-zinc-300 dark:bg-zinc-600'"></span>
                                                        <div>
                                                            <span class="font-bold text-zinc-900 dark:text-zinc-100 block" x-text="u.username"></span>
                                                            <div class="flex items-center gap-1.5 text-[11px] text-zinc-400 font-mono">
                                                                <span x-text="u.device_name !== '-' ? u.device_name : u.ip_address"></span>
                                                                <span class="text-zinc-300 dark:text-zinc-600">•</span>
                                                                <span x-text="u.ip_address"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- User Category Chip -->
                                                <td class="py-3 px-3">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold"
                                                          :class="{
                                                              'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800': u.category === 'voucher',
                                                              'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800': u.category === 'member',
                                                              'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800': u.category === 'bypassed',
                                                              'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300': !['voucher', 'member', 'bypassed'].includes(u.category)
                                                          }"
                                                          x-text="u.category_label || 'Voucher'">
                                                    </span>
                                                </td>

                                                <!-- Data Volume -->
                                                <td class="py-3 px-3 text-right">
                                                    <span class="font-bold text-zinc-900 dark:text-zinc-100 font-mono text-xs block" x-text="u.formatted_bytes"></span>
                                                    <span class="text-[10px] text-zinc-400" x-text="u.connections ? (formatNumber(u.connections) + ' koneksi') : 'Aktif'"></span>
                                                </td>

                                                <!-- Percentage Bar -->
                                                <td class="py-3 px-3">
                                                    <div class="flex items-center gap-2">
                                                        <div class="flex-1 h-2 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                                            <div class="h-full bg-indigo-600 rounded-full transition-all duration-300" :style="'width: ' + (u.percentage || 0) + '%'"></div>
                                                        </div>
                                                        <span class="text-[10px] font-mono font-semibold text-zinc-500 w-8 text-right" x-text="(u.percentage || 0) + '%'"></span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>

                                        <!-- Empty State -->
                                        <template x-if="!rankData?.users || rankData.users.length === 0">
                                            <tr>
                                                <td colspan="5" class="py-8 text-center text-zinc-400">
                                                    <svg class="w-8 h-8 mx-auto text-zinc-300 dark:text-zinc-700 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                                    </svg>
                                                    <p class="font-medium text-xs text-zinc-600 dark:text-zinc-400">Belum ada user yang terdeteksi aktif pada aplikasi ini.</p>
                                                    <p class="text-[11px] text-zinc-400 mt-0.5">Filter mangle di MikroTik akan otomatis mencatat data saat ada user yang mengakses.</p>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 flex justify-end">
                        <button 
                            type="button" 
                            @click="showRankModal = false"
                            class="px-4 py-2 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-semibold transition-colors"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL 2: Kelola Signature & Port (Game/App)    -->
        <!-- ============================================== -->
        <div 
            x-show="showSignatureModal" 
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" 
            role="dialog" 
            aria-modal="true"
        >
            <!-- Backdrop -->
            <div 
                x-show="showSignatureModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-zinc-900/60 backdrop-blur-xs transition-opacity"
                @click="showSignatureModal = false"
            ></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div 
                    x-show="showSignatureModal"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl"
                >
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Kelola Signature & Port Aplikasi</h3>
                            <p class="text-xs text-zinc-500 mt-0.5">Kustomisasi port game online, domain CDN, dan TLS SNI agar klasifikasi bandwidth selalu presisi</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button 
                                type="button" 
                                @click="openAddSignature()"
                                class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-xs"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Tambah Platform</span>
                            </button>

                            <button 
                                type="button" 
                                @click="showSignatureModal = false"
                                class="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Filter Toolbar -->
                    <div class="px-6 py-3 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 flex flex-wrap items-center justify-between gap-3">
                        <!-- Category Filter Tabs -->
                        <div class="flex flex-wrap items-center gap-1">
                            <button 
                                type="button" 
                                @click="sigCategoryFilter = 'all'"
                                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors"
                                :class="sigCategoryFilter === 'all' ? 'bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900' : 'bg-transparent text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-800'"
                            >
                                Semua
                            </button>
                            <button 
                                type="button" 
                                @click="sigCategoryFilter = 'gaming'"
                                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors"
                                :class="sigCategoryFilter === 'gaming' ? 'bg-emerald-600 text-white' : 'bg-transparent text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-800'"
                            >
                                Game Online
                            </button>
                            <button 
                                type="button" 
                                @click="sigCategoryFilter = 'video'"
                                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors"
                                :class="sigCategoryFilter === 'video' ? 'bg-rose-600 text-white' : 'bg-transparent text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-800'"
                            >
                                Video
                            </button>
                            <button 
                                type="button" 
                                @click="sigCategoryFilter = 'social_media'"
                                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors"
                                :class="sigCategoryFilter === 'social_media' ? 'bg-blue-600 text-white' : 'bg-transparent text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-800'"
                            >
                                Sosmed
                            </button>
                            <button 
                                type="button" 
                                @click="sigCategoryFilter = 'cloud_work'"
                                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors"
                                :class="sigCategoryFilter === 'cloud_work' ? 'bg-violet-600 text-white' : 'bg-transparent text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-800'"
                            >
                                Cloud / Kerja
                            </button>
                            <button 
                                type="button" 
                                @click="sigCategoryFilter = 'browsing'"
                                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors"
                                :class="sigCategoryFilter === 'browsing' ? 'bg-amber-600 text-white' : 'bg-transparent text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-800'"
                            >
                                E-Commerce
                            </button>
                        </div>

                        <!-- Search Box -->
                        <div class="relative w-48 sm:w-64">
                            <input 
                                type="text" 
                                x-model="sigSearchQuery" 
                                placeholder="Cari aplikasi atau port..." 
                                class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-hidden focus:ring-2 focus:ring-indigo-500"
                            >
                            <svg class="w-4 h-4 text-zinc-400 absolute left-2.5 top-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Signatures List Body -->
                    <div class="p-6">
                        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                            <div class="max-h-96 overflow-y-auto">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead class="sticky top-0 bg-zinc-50 dark:bg-zinc-800/90 backdrop-blur-xs z-10 border-b border-zinc-200 dark:border-zinc-800 text-zinc-500 font-semibold">
                                        <tr>
                                            <th class="py-2.5 px-3">Aplikasi / Game</th>
                                            <th class="py-2.5 px-3">Kategori</th>
                                            <th class="py-2.5 px-3">Port Protokol</th>
                                            <th class="py-2.5 px-3">Domain / SNI Host</th>
                                            <th class="py-2.5 px-3 text-center">Status</th>
                                            <th class="py-2.5 px-3 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                                        <template x-for="sig in filteredSignatures" :key="sig.id">
                                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                                <td class="py-3 px-3">
                                                    <span class="font-bold text-zinc-900 dark:text-zinc-100 block" x-text="sig.name"></span>
                                                    <span class="font-mono text-[10px] text-zinc-400" x-text="sig.mark_name"></span>
                                                </td>
                                                <td class="py-3 px-3">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold"
                                                          :class="{
                                                              'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400': sig.category === 'video',
                                                              'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400': sig.category === 'social_media',
                                                              'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400': sig.category === 'gaming',
                                                              'bg-violet-50 dark:bg-violet-950/40 text-violet-700 dark:text-violet-400': sig.category === 'cloud_work',
                                                              'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400': sig.category === 'browsing'
                                                          }"
                                                          x-text="sig.category">
                                                    </span>
                                                </td>
                                                <td class="py-3 px-3">
                                                    <div class="space-y-0.5">
                                                        <template x-if="sig.udp_ports">
                                                            <div class="text-[11px] font-mono">
                                                                <span class="text-emerald-600 dark:text-emerald-400 font-bold">UDP:</span>
                                                                <span class="text-zinc-700 dark:text-zinc-300" x-text="sig.udp_ports"></span>
                                                            </div>
                                                        </template>
                                                        <template x-if="sig.tcp_ports">
                                                            <div class="text-[11px] font-mono">
                                                                <span class="text-blue-600 dark:text-blue-400 font-bold">TCP:</span>
                                                                <span class="text-zinc-700 dark:text-zinc-300" x-text="sig.tcp_ports"></span>
                                                            </div>
                                                        </template>
                                                        <template x-if="!sig.udp_ports && !sig.tcp_ports">
                                                            <span class="text-zinc-400 text-[11px]">Default (80/443)</span>
                                                        </template>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-3 max-w-xs truncate font-mono text-[11px] text-zinc-500 dark:text-zinc-400" :title="sig.tls_host || (sig.fqdn_domains ? sig.fqdn_domains.join(', ') : '-')">
                                                    <span x-text="sig.tls_host || (sig.fqdn_domains ? sig.fqdn_domains.join(', ') : '-')"></span>
                                                </td>
                                                <td class="py-3 px-3 text-center">
                                                    <button 
                                                        type="button" 
                                                        @click="toggleSignatureActive(sig)"
                                                        class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden"
                                                        :class="sig.is_active ? 'bg-indigo-600' : 'bg-zinc-200 dark:bg-zinc-700'"
                                                    >
                                                        <span 
                                                            class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"
                                                            :class="sig.is_active ? 'translate-x-4' : 'translate-x-0'"
                                                        ></span>
                                                    </button>
                                                </td>
                                                <td class="py-3 px-3 text-right">
                                                    <div class="flex items-center justify-end gap-1.5">
                                                        <button 
                                                            type="button" 
                                                            @click="openEditSignature(sig)" 
                                                            class="p-1 rounded-lg text-zinc-400 hover:text-indigo-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                                                            title="Edit Port & Domain"
                                                        >
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                        </button>
                                                        <template x-if="!sig.is_system">
                                                            <button 
                                                                type="button" 
                                                                @click="deleteSignature(sig)" 
                                                                class="p-1 rounded-lg text-zinc-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                                                                title="Hapus Signature"
                                                            >
                                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Controls -->
                    <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 flex flex-wrap items-center justify-between gap-3">
                        <button 
                            type="button" 
                            @click="resetSignaturesToDefault()" 
                            class="px-3 py-1.5 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-semibold transition-colors"
                        >
                            Reset ke Preset Bawaan
                        </button>

                        <div class="flex items-center gap-2">
                            <button 
                                type="button" 
                                @click="showSignatureModal = false"
                                class="px-4 py-2 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-semibold transition-colors"
                            >
                                Tutup
                            </button>
                            <button 
                                type="button" 
                                @click="deployRules(); showSignatureModal = false;" 
                                :disabled="isDeploying"
                                class="px-4 py-2 rounded-xl bg-zinc-900 dark:bg-zinc-100 hover:bg-zinc-800 dark:hover:bg-white text-white dark:text-zinc-900 text-xs font-bold transition-colors flex items-center gap-2 shadow-xs"
                            >
                                <svg class="w-4 h-4 text-emerald-400 dark:text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Terapkan Rules ke MikroTik</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- SUB-MODAL: Form Tambah / Edit Signature        -->
        <!-- ============================================== -->
        <div 
            x-show="showEditSignatureModal" 
            x-cloak
            class="fixed inset-0 z-60 overflow-y-auto"
            aria-labelledby="modal-title" 
            role="dialog" 
            aria-modal="true"
        >
            <div 
                x-show="showEditSignatureModal"
                class="fixed inset-0 bg-zinc-900/60 backdrop-blur-xs transition-opacity"
                @click="showEditSignatureModal = false"
            ></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div 
                    x-show="showEditSignatureModal"
                    class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                >
                    <div class="px-6 py-5 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100" x-text="editingSignature ? 'Edit Signature: ' + editingSignature.name : 'Tambah Platform / Game Baru'"></h3>
                        <button type="button" @click="showEditSignatureModal = false" class="p-1 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="saveSignature()" class="p-6 space-y-4">
                        <!-- Nama Platform -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nama Platform / Game <span class="text-rose-500">*</span></label>
                            <input 
                                type="text" 
                                x-model="sigForm.name" 
                                required 
                                placeholder="Contoh: Mobile Legends: Bang Bang"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500"
                            >
                        </div>

                        <!-- Kategori -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Kategori Konten <span class="text-rose-500">*</span></label>
                            <select 
                                x-model="sigForm.category" 
                                class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500"
                            >
                                <option value="gaming">Online Gaming</option>
                                <option value="video">Streaming Video</option>
                                <option value="social_media">Sosial Media & Chat</option>
                                <option value="cloud_work">Cloud & Kerja / Belajar</option>
                                <option value="browsing">Web & E-Commerce</option>
                            </select>
                        </div>

                        <!-- Port UDP -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Port UDP (Port Game Server)</label>
                            <input 
                                type="text" 
                                x-model="sigForm.udp_ports" 
                                placeholder="Contoh: 5000-5200,30000-30200,10003"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-mono focus:ring-2 focus:ring-indigo-500"
                            >
                            <span class="text-[10px] text-zinc-400 mt-1 block">Pisahkan dengan koma atau tanda hubung untuk rentang port.</span>
                        </div>

                        <!-- Port TCP -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Port TCP</label>
                            <input 
                                type="text" 
                                x-model="sigForm.tcp_ports" 
                                placeholder="Contoh: 5000-5200,5500-5700"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-mono focus:ring-2 focus:ring-indigo-500"
                            >
                        </div>

                        <!-- Domain FQDN -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Domain FQDN (Address-List)</label>
                            <input 
                                type="text" 
                                x-model="sigForm.fqdn_domains" 
                                placeholder="Contoh: mobilelegends.com, moonton.com"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-mono focus:ring-2 focus:ring-indigo-500"
                            >
                            <span class="text-[10px] text-zinc-400 mt-1 block">MikroTik akan otomatis me-resolve domain ini ke IP publik.</span>
                        </div>

                        <!-- TLS Host SNI -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">TLS Host (SNI Wildcard)</label>
                            <input 
                                type="text" 
                                x-model="sigForm.tls_host" 
                                placeholder="Contoh: *mobilelegends.com,*moonton.com"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-mono focus:ring-2 focus:ring-indigo-500"
                            >
                        </div>

                        <!-- QUIC Checkbox -->
                        <div class="flex items-center gap-2 pt-1">
                            <input type="checkbox" id="is_quic" x-model="sigForm.is_quic" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="is_quic" class="text-xs text-zinc-700 dark:text-zinc-300">Dukung QUIC (HTTP/3 over UDP port 443)</label>
                        </div>

                        <!-- Footer Actions -->
                        <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 flex justify-end gap-2">
                            <button 
                                type="button" 
                                @click="showEditSignatureModal = false"
                                class="px-4 py-2 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-semibold"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit" 
                                :disabled="isSavingSignature"
                                class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-colors flex items-center gap-2"
                            >
                                <svg x-show="isSavingSignature" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="isSavingSignature ? 'Menyimpan...' : 'Simpan Signature'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function trafficAnalyticsManager(initialData) {
            return {
                analytics: initialData,
                isDeploying: false,
                isResetting: false,
                donutChartInstance: null,
                hourlyChartInstance: null,
                pollTimer: null,
                currentPeriod: '{{ $period }}',

                // Pop-up User Rank state
                showRankModal: false,
                rankLoading: false,
                selectedPlatform: null,
                rankData: null,

                // Signature Management state
                showSignatureModal: false,
                signatures: [],
                signaturesLoading: false,
                sigCategoryFilter: 'all',
                sigSearchQuery: '',
                showEditSignatureModal: false,
                editingSignature: null,
                isSavingSignature: false,
                sigForm: {
                    name: '',
                    category: 'gaming',
                    tcp_ports: '',
                    udp_ports: '',
                    fqdn_domains: '',
                    tls_host: '',
                    is_quic: false,
                    is_active: true,
                },

                init() {
                    this.$nextTick(() => {
                        this.renderCharts();
                    });

                    // Real-time polling every 5 seconds
                    this.pollTimer = setInterval(() => {
                        this.fetchLiveData();
                    }, 5000);
                },

                destroy() {
                    if (this.pollTimer) {
                        clearInterval(this.pollTimer);
                        this.pollTimer = null;
                    }
                },

                fetchLiveData() {
                    fetch(`{{ route('traffic.live') }}?period=${this.currentPeriod}`)
                        .then(res => res.json())
                        .then(res => {
                            if (res.success && res.data) {
                                this.analytics = res.data;
                                this.updateCharts();
                            }
                        })
                        .catch(err => {
                            console.error('Error fetching live traffic data:', err);
                        });
                },

                updateCharts() {
                    if (this.donutChartInstance && this.analytics.donut_chart) {
                        const series = this.analytics.donut_chart.series.length > 0 ? this.analytics.donut_chart.series : [45, 25, 15, 15];
                        const labels = this.analytics.donut_chart.labels.length > 0 ? this.analytics.donut_chart.labels : ['Video', 'Sosmed', 'Gaming', 'Web/Cloud'];
                        const colors = this.analytics.donut_chart.colors.length > 0 ? this.analytics.donut_chart.colors : ['#f43f5e', '#3b82f6', '#10b981', '#f59e0b'];

                        this.donutChartInstance.updateOptions({
                            labels: labels,
                            colors: colors,
                            plotOptions: {
                                pie: {
                                    donut: {
                                        labels: {
                                            total: {
                                                formatter: () => this.analytics.formatted_total_traffic || '0 B',
                                            }
                                        }
                                    }
                                }
                            }
                        }, false, true);
                        this.donutChartInstance.updateSeries(series);
                    }

                    if (this.hourlyChartInstance && this.analytics.hourly_chart) {
                        this.hourlyChartInstance.updateOptions({
                            xaxis: {
                                categories: this.analytics.hourly_chart.categories,
                            }
                        }, false, true);
                        this.hourlyChartInstance.updateSeries(this.analytics.hourly_chart.series);
                    }
                },

                renderCharts() {
                    // 1. Render Donut Chart
                    const donutEl = document.querySelector('#donutChart');
                    if (donutEl && window.ApexCharts) {
                        if (this.donutChartInstance) this.donutChartInstance.destroy();

                        const isDark = document.documentElement.classList.contains('dark');
                        const options = {
                            series: this.analytics.donut_chart.series.length > 0 ? this.analytics.donut_chart.series : [45, 25, 15, 15],
                            labels: this.analytics.donut_chart.labels.length > 0 ? this.analytics.donut_chart.labels : ['Video', 'Sosmed', 'Gaming', 'Web/Cloud'],
                            colors: this.analytics.donut_chart.colors.length > 0 ? this.analytics.donut_chart.colors : ['#f43f5e', '#3b82f6', '#10b981', '#f59e0b'],
                            chart: {
                                type: 'donut',
                                height: 260,
                                background: 'transparent',
                            },
                            stroke: {
                                width: 2,
                                colors: isDark ? ['#18181b'] : ['#ffffff'],
                            },
                            dataLabels: {
                                enabled: false,
                            },
                            legend: {
                                show: false,
                            },
                            tooltip: {
                                theme: isDark ? 'dark' : 'light',
                                y: {
                                    formatter: (val) => val + ' MB',
                                }
                            },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: '72%',
                                        labels: {
                                            show: true,
                                            total: {
                                                show: true,
                                                label: 'Total Trafik',
                                                formatter: () => this.analytics.formatted_total_traffic || '0 B',
                                            }
                                        }
                                    }
                                }
                            }
                        };
                        this.donutChartInstance = new ApexCharts(donutEl, options);
                        this.donutChartInstance.render();
                    }

                    // 2. Render Hourly Area Chart
                    const hourlyEl = document.querySelector('#hourlyChart');
                    if (hourlyEl && window.ApexCharts) {
                        if (this.hourlyChartInstance) this.hourlyChartInstance.destroy();

                        const isDark = document.documentElement.classList.contains('dark');
                        const options = {
                            series: this.analytics.hourly_chart.series,
                            chart: {
                                type: 'area',
                                height: 260,
                                background: 'transparent',
                                toolbar: { show: false },
                                zoom: { enabled: false },
                            },
                            colors: ['#f43f5e', '#3b82f6', '#10b981', '#f59e0b'],
                            dataLabels: { enabled: false },
                            stroke: { curve: 'smooth', width: 2 },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.35,
                                    opacityTo: 0.05,
                                    stops: [0, 90, 100]
                                }
                            },
                            xaxis: {
                                categories: this.analytics.hourly_chart.categories,
                                labels: {
                                    style: { colors: isDark ? '#a1a1aa' : '#71717a', fontSize: '10px' },
                                    rotate: 0,
                                },
                                axisBorder: { show: false },
                                axisTicks: { show: false },
                            },
                            yaxis: {
                                labels: {
                                    style: { colors: isDark ? '#a1a1aa' : '#71717a', fontSize: '10px' },
                                    formatter: (val) => val + ' MB',
                                },
                            },
                            grid: {
                                borderColor: isDark ? '#27272a' : '#f4f4f5',
                                strokeDashArray: 3,
                            },
                            tooltip: {
                                theme: isDark ? 'dark' : 'light',
                                y: { formatter: (val) => val + ' MB' }
                            },
                            legend: {
                                position: 'top',
                                horizontalAlign: 'right',
                                labels: { colors: isDark ? '#d4d4d8' : '#3f3f46' },
                                fontSize: '11px',
                            }
                        };
                        this.hourlyChartInstance = new ApexCharts(hourlyEl, options);
                        this.hourlyChartInstance.render();
                    }
                },

                // Open Platform User Rank Pop-up Modal
                openPlatformRank(item) {
                    this.selectedPlatform = item;
                    this.showRankModal = true;
                    this.rankLoading = true;
                    this.rankData = null;

                    const platformKey = item.mark_name || item.platform;
                    fetch(`{{ url('/api/traffic-analytics/platforms') }}/${encodeURIComponent(platformKey)}/users`)
                        .then(res => res.json())
                        .then(res => {
                            this.rankLoading = false;
                            if (res.success && res.data) {
                                this.rankData = res.data;
                            }
                        })
                        .catch(err => {
                            this.rankLoading = false;
                            console.error('Error fetching platform user rank:', err);
                        });
                },

                // Open Signature Management Modal
                openSignatureManager() {
                    this.showSignatureModal = true;
                    this.loadSignatures();
                },

                loadSignatures() {
                    this.signaturesLoading = true;
                    fetch('{{ route("traffic.signatures") }}')
                        .then(res => res.json())
                        .then(res => {
                            this.signaturesLoading = false;
                            if (res.success) {
                                this.signatures = res.data;
                            }
                        })
                        .catch(() => {
                            this.signaturesLoading = false;
                        });
                },

                get filteredSignatures() {
                    return this.signatures.filter(sig => {
                        const matchesCategory = this.sigCategoryFilter === 'all' || sig.category === this.sigCategoryFilter;
                        const q = this.sigSearchQuery.toLowerCase();
                        const matchesSearch = !q || 
                            sig.name.toLowerCase().includes(q) || 
                            (sig.udp_ports && sig.udp_ports.includes(q)) || 
                            (sig.tcp_ports && sig.tcp_ports.includes(q)) ||
                            (sig.tls_host && sig.tls_host.toLowerCase().includes(q));
                        return matchesCategory && matchesSearch;
                    });
                },

                toggleSignatureActive(sig) {
                    sig.is_active = !sig.is_active;
                    fetch(`{{ url('/api/traffic-analytics/signatures') }}/${sig.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ is_active: sig.is_active })
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success && window.showToast) {
                            window.showToast(res.message, 'success');
                        }
                    })
                    .catch(() => {
                        sig.is_active = !sig.is_active;
                        if (window.showToast) window.showToast('Gagal mengubah status signature', 'error');
                    });
                },

                openAddSignature() {
                    this.editingSignature = null;
                    this.sigForm = {
                        name: '',
                        category: 'gaming',
                        tcp_ports: '',
                        udp_ports: '',
                        fqdn_domains: '',
                        tls_host: '',
                        is_quic: false,
                        is_active: true,
                    };
                    this.showEditSignatureModal = true;
                },

                openEditSignature(sig) {
                    this.editingSignature = sig;
                    this.sigForm = {
                        name: sig.name,
                        category: sig.category,
                        tcp_ports: sig.tcp_ports || '',
                        udp_ports: sig.udp_ports || '',
                        fqdn_domains: Array.isArray(sig.fqdn_domains) ? sig.fqdn_domains.join(', ') : (sig.fqdn_domains || ''),
                        tls_host: sig.tls_host || '',
                        is_quic: Boolean(sig.is_quic),
                        is_active: Boolean(sig.is_active),
                    };
                    this.showEditSignatureModal = true;
                },

                saveSignature() {
                    this.isSavingSignature = true;
                    const url = this.editingSignature 
                        ? `{{ url('/api/traffic-analytics/signatures') }}/${this.editingSignature.id}`
                        : '{{ route("traffic.signatures.store") }}';
                    const method = this.editingSignature ? 'PUT' : 'POST';

                    fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.sigForm)
                    })
                    .then(res => res.json())
                    .then(res => {
                        this.isSavingSignature = false;
                        if (res.success) {
                            if (window.showToast) window.showToast(res.message, 'success');
                            this.showEditSignatureModal = false;
                            this.loadSignatures();
                        } else {
                            if (window.showToast) window.showToast(res.message || 'Gagal menyimpan signature', 'error');
                        }
                    })
                    .catch(() => {
                        this.isSavingSignature = false;
                        if (window.showToast) window.showToast('Terjadi kesalahan koneksi', 'error');
                    });
                },

                deleteSignature(sig) {
                    if (!confirm(`Hapus signature "${sig.name}"?`)) return;

                    fetch(`{{ url('/api/traffic-analytics/signatures') }}/${sig.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            if (window.showToast) window.showToast(res.message, 'success');
                            this.loadSignatures();
                        }
                    })
                    .catch(() => {
                        if (window.showToast) window.showToast('Gagal menghapus signature', 'error');
                    });
                },

                resetSignaturesToDefault() {
                    if (!confirm('Kembalikan semua signature dan port ke preset bawaan sistem?')) return;

                    fetch('{{ route("traffic.signatures.reset-defaults") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            if (window.showToast) window.showToast(res.message, 'success');
                            this.loadSignatures();
                        }
                    })
                    .catch(() => {
                        if (window.showToast) window.showToast('Gagal mereset signature', 'error');
                    });
                },

                deployRules() {
                    this.isDeploying = true;
                    fetch('{{ route("traffic.deploy") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isDeploying = false;
                        if (data.success) {
                            if (window.showToast) window.showToast(data.message, 'success');
                            setTimeout(() => window.location.reload(), 1200);
                        } else {
                            if (window.showToast) window.showToast(data.message || 'Gagal memasang filter trafik', 'error');
                        }
                    })
                    .catch(() => {
                        this.isDeploying = false;
                        if (window.showToast) window.showToast('Terjadi kesalahan koneksi', 'error');
                    });
                },

                resetCounters() {
                    if (!confirm('Apakah Anda yakin ingin me-reset seluruh counter pemakaian trafik ke 0?')) {
                        return;
                    }

                    this.isResetting = true;
                    fetch('{{ route("traffic.reset-counters") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isResetting = false;
                        if (data.success) {
                            if (window.showToast) window.showToast(data.message, 'success');
                            setTimeout(() => window.location.reload(), 1200);
                        } else {
                            if (window.showToast) window.showToast(data.message || 'Gagal reset counter', 'error');
                        }
                    })
                    .catch(() => {
                        this.isResetting = false;
                        if (window.showToast) window.showToast('Terjadi kesalahan koneksi', 'error');
                    });
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
