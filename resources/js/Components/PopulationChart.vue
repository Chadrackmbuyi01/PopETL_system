<template>
  <canvas ref="canvasRef"></canvas>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import {
  Chart,
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  Tooltip,
  Filler,
} from 'chart.js'

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Filler)

const props = defineProps({
  data: { type: Array, required: true }, // [{ year, population, country }]
})

const canvasRef = ref(null)
let chartInstance = null

function buildChart() {
  if (!canvasRef.value || !props.data.length) return

  chartInstance?.destroy()

  const labels = props.data.map(d => d.year)
  const values = props.data.map(d => d.population)

  chartInstance = new Chart(canvasRef.value, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Population',
        data: values,
        borderColor: '#166534',
        backgroundColor: 'rgba(22, 101, 52, 0.06)',
        borderWidth: 2.5,
        pointBackgroundColor: '#ffffff',
        pointBorderColor: '#166534',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
        fill: true,
        tension: 0.4,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ' ' + Number(ctx.parsed.y).toLocaleString(),
          },
          backgroundColor: '#ffffff',
          borderColor: '#e5e2d9',
          borderWidth: 1,
          titleColor: '#74777a',
          bodyColor: '#1a1c1e',
          padding: 12,
          cornerRadius: 8,
          displayColors: false,
        },
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: '#74777a', font: { family: 'IBM Plex Mono', size: 10 } },
        },
        y: {
          grid: { color: '#f0ede4', drawBorder: false },
          ticks: {
            color: '#74777a',
            font: { family: 'IBM Plex Mono', size: 10 },
            callback: val => Number(val).toLocaleString(),
            padding: 10,
          },
        },
      },
    },
  })
}

onMounted(buildChart)
watch(() => props.data, buildChart, { deep: true })
onBeforeUnmount(() => chartInstance?.destroy())
</script>