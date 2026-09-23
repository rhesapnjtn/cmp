<script setup>
import ApexCharts from 'apexcharts';
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    options: { type: Object, required: true },
    series: { type: Array, required: true },
    height: { type: [Number, String], default: 320 },
});

const el = ref(null);
let chart = null;

onMounted(() => {
    chart = new ApexCharts(el.value, {
        chart: { fontFamily: 'Inter, sans-serif', toolbar: { show: false }, zoom: { enabled: false }, foreColor: '#64748b' },
        ...props.options,
        series: props.series,
    });
    chart.render();
});

watch(
    () => [props.options, props.series],
    () => {
        if (!chart) return;
        chart.updateOptions(props.options);
        chart.updateSeries(props.series);
    },
    { deep: true },
);

onBeforeUnmount(() => chart?.destroy());
</script>

<template>
    <div ref="el"></div>
</template>