<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: { type: String, default: '' },
    color: { type: String, default: 'bg-slate-100 text-slate-700' },
    dot: { type: String, default: '' },
});

const dotClass = computed(() => props.dot || '');
</script>

<template>
    <span class="cmp-pill border border-transparent" :class="color">
        <span v-if="dotClass" class="w-1.5 h-1.5 rounded-full" :class="dotClass"></span>
        <span v-else class="w-1.5 h-1.5 rounded-full bg-current opacity-40"></span>
        {{ label }}
    </span>
</template>