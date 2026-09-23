<script setup>
defineProps({
    page: { type: Number, default: 1 },
    lastPage: { type: Number, default: 1 },
    total: { type: Number, default: 0 },
});

const emit = defineEmits(['page']);

function pages() {
    const p = [];
    const total = this.lastPage;
    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || Math.abs(i - this.page) <= 1) p.push(i);
        else if (p[p.length - 1] !== '...') p.push('...');
    }

    return p;
}
</script>

<template>
    <div v-if="lastPage > 1" class="flex items-center justify-between gap-3 px-4 lg:px-5 py-3.5 border-t border-slate-200">
        <span class="text-xs font-medium text-slate-500">
            Showing <b class="text-slate-700">{{ (page - 1) * 20 + (total ? 1 : 0) }}–{{ Math.min(page * 20, total) }}</b> of
            <b class="text-slate-700">{{ total }}</b> records
        </span>
        <div class="flex items-center gap-1">
            <button
                class="w-8 h-8 rounded-lg border border-slate-300 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700 flex items-center justify-center disabled:opacity-40 disabled:pointer-events-none"
                :disabled="page <= 1"
                @click="emit('page', page - 1)"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <button
                v-for="pg in pages()"
                :key="pg"
                :disabled="pg === '...'"
                @click="emit('page', pg)"
                class="min-w-8 h-8 px-2.5 rounded-lg text-xs font-bold border transition-colors"
                :class="pg === page ? 'bg-brand-600 text-white border-brand-600 shadow-sm' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50'"
            >
                {{ pg }}
            </button>
            <button
                class="w-8 h-8 rounded-lg border border-slate-300 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700 flex items-center justify-center disabled:opacity-40 disabled:pointer-events-none"
                :disabled="page >= lastPage"
                @click="emit('page', page + 1)"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </button>
        </div>
    </div>
</template>