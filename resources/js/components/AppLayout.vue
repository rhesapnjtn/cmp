<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useFilterStore } from '../stores/filters';
import FilterBar from './FilterBar.vue';

defineOptions({ name: 'AppLayout' });

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();
const filters = useFilterStore();
const sidebarOpen = ref(false);

const navGroups = [
    {
        label: 'Monitoring',
        items: [
            { label: 'Dashboard', to: '/', icon: 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z', perm: 'dashboard.view' },
            { label: 'Planning', to: '/planning', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', perm: 'planning.view' },
            { label: 'Risk Management', to: '/risk', icon: 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', perm: 'risk.view' },
            { label: 'Ongoing Business', to: '/ogb', icon: 'M17 20h5v-9l-5-6v15zm0 0v-6h5M3 20h5V9l5 6m0 0v5', perm: 'ogb.view' },
            { label: 'HSSE Committee', to: '/hsse', icon: 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z', perm: 'hsse.view' },
            { label: 'Compliance', to: '/compliance', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', perm: 'compliance.view' },
            { label: 'Reports', to: '/reports', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', perm: 'reports.view' },
        ],
    },
    {
        label: 'Administration',
        items: [
            { label: 'Users', to: '/master/users', icon: 'M17 20h5v-2a4 4 0 00-3-3.87M19 10a3 3 0 11-6 0 3 3 0 016 0zM17 20v-1a4 4 0 00-2-3.47M14 10a3 3 0 11-6 0 3 3 0 016 0zM14 20v-1a4 4 0 00-4-4H7a4 4 0 00-4 4v1', perm: 'users.manage' },
            { label: 'Units (Functions/Dept)', to: '/master/units', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', perm: 'masterdata.manage' },
            { label: 'Zones', to: '/master/zones', icon: 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7', perm: 'masterdata.manage' },
            { label: 'Sites', to: '/master/sites', icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z', perm: 'masterdata.manage' },
        ],
    },
];

const visibleItems = computed(() =>
    navGroups
        .map((g) => ({ ...g, items: g.items.filter((i) => auth.can(i.perm)) }))
        .filter((g) => g.items.length),
);

const pageDate = new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

const active = (item) => route.path === item.to || (item.to !== '/' && route.path.startsWith(item.to));

watch(
    () => route.path,
    () => (sidebarOpen.value = false),
);

onMounted(async () => {
    try {
        await auth.fetchMe();
    } catch (e) {
        await auth.logout();
    }
    await filters.load();
});

async function logout() {
    await auth.logout();
    router.push('/login');
}
</script>

<template>
    <div class="min-h-screen">
        <!-- Sidebar overlay (mobile) -->
        <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-[2px] lg:hidden" @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-100 transform transition-transform lg:translate-x-0 flex flex-col bg-gradient-to-b from-slate-900 via-slate-900 to-indigo-950/50"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Brand -->
            <div class="flex items-center gap-3 px-5 h-16 border-b border-white/10 shrink-0">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white font-extrabold text-xl shadow-lg shadow-brand-900/40">C</div>
                <div class="leading-tight">
                    <div class="font-extrabold text-white tracking-tight text-[15px]">CMP</div>
                    <div class="text-[11px] text-slate-400">Centralized Monitoring</div>
                </div>
            </div>

            <!-- Nav -->
            <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-6">
                <div v-for="group in visibleItems" :key="group.label">
                    <div class="px-2 text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">{{ group.label }}</div>
                    <div class="space-y-0.5">
                        <RouterLink
                            v-for="item in group.items"
                            :key="item.to"
                            :to="item.to"
                            class="group flex items-center gap-3 px-2.5 py-2 rounded-lg text-[13px] font-medium transition-colors"
                            :class="active(item) ? 'bg-brand-600 text-white shadow-md shadow-brand-900/30' : 'text-slate-300 hover:bg-white/5 hover:text-white'"
                        >
                            <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" /></svg>
                            <span class="truncate">{{ item.label }}</span>
                            <span v-if="active(item)" class="ml-auto w-1.5 h-1.5 rounded-full bg-white/80"></span>
                        </RouterLink>
                    </div>
                </div>
            </nav>

            <!-- User footer -->
            <div class="p-3">
                <div class="flex items-center gap-3 p-2.5 rounded-xl bg-white/5 border border-white/10">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center text-sm font-extrabold uppercase text-white shrink-0">
                        {{ (auth.user?.name || 'U').charAt(0) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-[13px] font-semibold text-white truncate">{{ auth.user?.name }}</div>
                        <div class="text-[11px] text-slate-400 truncate">{{ (auth.user?.roles || []).map((r) => r.name).join(', ') }}</div>
                    </div>
                    <button @click="logout" title="Logout" class="text-slate-400 hover:text-red-400 transition-colors shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main area -->
        <div class="lg:pl-64 flex flex-col min-h-screen">
            <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-slate-200 h-16 flex items-center px-4 lg:px-6 gap-3">
                <button @click="sidebarOpen = true" class="lg:hidden text-slate-600 mr-1 p-1 rounded-lg hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h1 class="text-base lg:text-lg font-bold text-slate-800 truncate">{{ route.meta.title || 'CMP' }}</h1>
                        <span class="hidden md:inline-block px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 text-[10px] font-bold uppercase tracking-wide">Corporate</span>
                    </div>
                    <p class="hidden sm:block text-[11px] text-slate-400">Centralized Monitoring Platform · Monitoring &amp; Assurance</p>
                </div>
                <div class="ml-auto flex items-center gap-2.5">
                    <span class="hidden md:inline-flex items-center gap-1.5 text-xs text-slate-500 bg-slate-100 border border-slate-200 rounded-lg px-3 py-1.5">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        {{ pageDate }}
                    </span>
                </div>
            </header>

            <main class="p-4 lg:p-6 flex-1">
                <div class="max-w-[1600px] mx-auto space-y-4">
                    <FilterBar />
                    <RouterView />
                </div>
            </main>

            <footer class="px-6 py-4 text-center text-[11px] text-slate-400 border-t border-slate-200">
                CMP — Centralized Monitoring Platform · © {{ new Date().getFullYear() }}
            </footer>
        </div>
    </div>
</template>