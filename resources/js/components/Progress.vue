<script setup>
defineProps({
    value: { type: Number, default: 0 },
    color: { type: String, default: 'bg-brand-500' },
    size: { type: String, default: 'h-2' },
    showLabel: { type: Boolean, default: false },
});
</script>

<template>
    <div class="flex items-center gap-2.5">
        <div class="flex-1 min-w-0">
            <div class="w-full rounded-full bg-slate-100 overflow-hidden ring-1 ring-inset ring-slate-200/60" :class="size">
                <div class="rounded-full transition-all duration-500" :class="[color, size]" :style="{ width: Math.min(value, 100) + '%' }"></div>
            </div>
        </div>
        <span v-if="showLabel" class="text-xs font-bold text-slate-600 whitespace-nowrap tabular-nums">{{ Math.round(value) }}%</span>
    </div>
</template>