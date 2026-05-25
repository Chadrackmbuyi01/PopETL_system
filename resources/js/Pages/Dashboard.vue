<template>
    <div class="dashboard">
        <!-- Header -->
        <header class="dash-header">
            <div class="header-inner">
                <div class="logo-area">
                    <div class="logo-mark">
                        <svg viewBox="0 0 32 32" fill="none">
                            <circle
                                cx="16"
                                cy="16"
                                r="14"
                                stroke="currentColor"
                                stroke-width="1.5"
                            />
                            <path
                                d="M8 22 Q12 10 16 18 Q20 26 24 14"
                                stroke="currentColor"
                                stroke-width="2"
                                fill="none"
                                stroke-linecap="round"
                            />
                        </svg>
                    </div>
                    <div>
                        <h1 class="logo-title">PopETL</h1>
                        <p class="logo-sub">Population Data Pipeline</p>
                    </div>
                </div>
                <div class="header-stats">
                    <div class="stat-pill">
                        <span class="stat-val">{{ totalRecords }}</span>
                        <span class="stat-label">Records</span>
                    </div>
                    <div class="stat-pill">
                        <span class="stat-val">{{ countries.length }}</span>
                        <span class="stat-label">Countries</span>
                    </div>
                </div>
            </div>
        </header>

        <main class="dash-main">
            <!-- Flash message -->
            <transition name="flash">
                <div
                    v-if="flashMessage"
                    class="flash-bar"
                    :class="`flash-${flashType}`"
                >
                    <span>{{ flashMessage }}</span>
                    <button class="flash-close" @click="clearFlash">×</button>
                </div>
            </transition>

            <!-- ETL Control Panel -->
            <section class="control-panel">
                <div class="panel-header">
                    <h2>Fetch Population Data</h2>
                    <p>
                        Extract and store population statistics from API-Ninjas
                    </p>
                </div>

                <div class="fetch-form">
                    <div class="form-row">
                        <!-- Country input with datalist -->
                        <div
                            class="field-group"
                            :class="{ 'has-error': errors.country }"
                        >
                            <label for="country-input">Country</label>
                            <div class="input-wrapper">
                                <svg
                                    class="input-icon"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <input
                                    id="country-input"
                                    v-model="selectedCountry"
                                    type="text"
                                    list="country-list"
                                    placeholder="e.g. United States, India, Germany…"
                                    class="text-input"
                                    :disabled="isFetching"
                                    @keydown.enter="triggerFetch"
                                />
                                <datalist id="country-list">
                                    <option
                                        v-for="c in countries"
                                        :key="c"
                                        :value="c"
                                    />
                                    <option value="United States" />
                                    <option value="India" />
                                    <option value="China" />
                                    <option value="Germany" />
                                    <option value="Brazil" />
                                    <option value="Japan" />
                                    <option value="Nigeria" />
                                    <option value="United Kingdom" />
                                    <option value="France" />
                                    <option value="Canada" />
                                    <option value="Australia" />
                                    <option value="South Africa" />
                                </datalist>
                            </div>
                            <p v-if="errors.country" class="field-error">
                                {{ errors.country[0] }}
                            </p>
                        </div>

                        <!-- Year input (optional) -->
                        <div
                            class="field-group"
                            :class="{ 'has-error': errors.year }"
                        >
                            <label for="year-input"
                                >Year
                                <span class="optional">optional</span></label
                            >
                            <input
                                id="year-input"
                                v-model.number="selectedYear"
                                type="number"
                                placeholder="e.g. 2020"
                                min="1900"
                                max="2025"
                                class="text-input year-input"
                                :disabled="isFetching"
                            />
                            <p v-if="errors.year" class="field-error">
                                {{ errors.year[0] }}
                            </p>
                        </div>

                        <!-- Fetch button -->
                        <div class="field-group btn-group">
                            <label>&nbsp;</label>
                            <button
                                class="fetch-btn"
                                :class="{ loading: isFetching }"
                                :disabled="
                                    isFetching || !selectedCountry.trim()
                                "
                                @click="triggerFetch"
                            >
                                <span v-if="!isFetching" class="btn-content">
                                    <svg
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                        width="16"
                                        height="16"
                                    >
                                        <path
                                            d="M10.75 2.75a.75.75 0 00-1.5 0v8.614L6.295 8.235a.75.75 0 10-1.09 1.03l4.25 4.5a.75.75 0 001.09 0l4.25-4.5a.75.75 0 00-1.09-1.03l-2.955 3.129V2.75z"
                                        />
                                        <path
                                            d="M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z"
                                        />
                                    </svg>
                                    Fetch &amp; Save
                                </span>
                                <span v-else class="btn-content">
                                    <span class="spinner"></span>
                                    Fetching…
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Filter bar -->
            <section class="filter-bar" v-if="hasRecords">
                <div class="filter-left">
                    <label for="filter-country">Filter by country:</label>
                    <select
                        id="filter-country"
                        v-model="filterCountry"
                        class="filter-select"
                        @change="applyFilter"
                    >
                        <option value="">All countries</option>
                        <option v-for="c in countries" :key="c" :value="c">
                            {{ c }}
                        </option>
                    </select>
                </div>
                <div class="record-count" v-if="pagination">
                    {{ pagination.total }} record{{
                        pagination.total !== 1 ? "s" : ""
                    }}
                </div>
            </section>

            <!-- Records table -->
            <section class="table-section">
                <div class="table-card">
                    <div v-if="isLoading" class="table-loading">
                        <div class="spinner large"></div>
                        <span>Loading records…</span>
                    </div>
                    <PopulationTable
                        v-else
                        :records="formattedRecords"
                        :pagination="pagination"
                        @delete="handleDelete"
                        @page="handlePage"
                    />
                </div>
            </section>

            <!-- Chart section (if multiple years for a country) -->
            <section v-if="chartData.length > 1" class="chart-section">
                <div class="chart-card">
                    <h3>
                        Population Trend —
                        {{ filterCountry || "All Countries" }}
                    </h3>
                    <div class="chart-area">
                        <PopulationChart :data="chartData" />
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { usePopulation } from "@/Composables/usePopulation";
import PopulationTable from "@/Components/PopulationTable.vue";
import PopulationChart from "@/Components/PopulationChart.vue";

// Props from Inertia (server-side initial data)
const props = defineProps({
    initialRecords: { type: Object, default: null },
    initialCountries: { type: Array, default: () => [] },
});

const {
    records,
    formattedRecords,
    pagination,
    countries,
    isLoading,
    isFetching,
    flashMessage,
    flashType,
    errors,
    hasRecords,
    loadRecords,
    loadCountries,
    fetchCountry,
    deleteRecord,
    goToPage,
    clearFlash,
} = usePopulation();

// ─── Local state ────────────────────────────────────────────────────────────
const selectedCountry = ref("");
const selectedYear = ref(null);
const filterCountry = ref("");

// ─── Init ───────────────────────────────────────────────────────────────────
onMounted(async () => {
    // Hydrate from Inertia initial props
    if (props.initialRecords?.data) {
        records.value = props.initialRecords.data;
        pagination.value = {
            currentPage: props.initialRecords.current_page,
            lastPage: props.initialRecords.last_page,
            perPage: props.initialRecords.per_page,
            total: props.initialRecords.total,
            from: props.initialRecords.from,
            to: props.initialRecords.to,
        };
    } else {
        await loadRecords();
    }

    if (props.initialCountries.length) {
        countries.value = props.initialCountries;
    } else {
        await loadCountries();
    }
});

// ─── Actions ─────────────────────────────────────────────────────────────────
async function triggerFetch() {
    if (!selectedCountry.value.trim()) return;
    await fetchCountry(
        selectedCountry.value.trim(),
        selectedYear.value || null,
    );
}

async function handleDelete(id) {
    await deleteRecord(id);
}

async function handlePage(page) {
    await goToPage(
        page,
        filterCountry.value ? { country: filterCountry.value } : {},
    );
}

async function applyFilter() {
    await loadRecords(
        filterCountry.value ? { country: filterCountry.value } : {},
    );
}

// ─── Computed ─────────────────────────────────────────────────────────────────
const totalRecords = computed(
    () => pagination.value?.total ?? records.value.length,
);

const chartData = computed(() => {
    const filtered = filterCountry.value
        ? records.value.filter((r) => r.country === filterCountry.value)
        : records.value;

    return [...filtered]
        .sort((a, b) => a.year - b.year)
        .map((r) => ({
            year: r.year,
            population: r.total_population,
            country: r.country,
        }));
});
</script>

<style scoped>
.dashboard {
    position: relative;
    z-index: 1;
    min-height: 100vh;
}

/* ── Header ──────────────────────────────────────────────────────── */
.dash-header {
    border-bottom: 1px solid var(--border);
    background: rgba(253, 252, 248, 0.85);
    backdrop-filter: blur(12px);
    position: sticky;
    top: 0;
    z-index: 100;
}

.header-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1.25rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.logo-area {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.logo-mark {
    width: 36px;
    height: 36px;
    color: var(--accent);
}

.logo-title {
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: -0.01em;
    color: var(--text-primary);
    line-height: 1;
}

.logo-sub {
    font-size: 0.65rem;
    color: var(--text-muted);
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin-top: 0.2rem;
}

.header-stats {
    display: flex;
    gap: 1rem;
}

.stat-pill {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    padding: 0.25rem 0.5rem;
}

.stat-val {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--accent);
    line-height: 1;
}

.stat-label {
    font-size: 0.65rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

/* ── Main ────────────────────────────────────────────────────────── */
.dash-main {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2.5rem 2rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* ── Flash bar ───────────────────────────────────────────────────── */
.flash-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-radius: var(--radius);
    font-size: 0.875rem;
    font-weight: 600;
    border: 1px solid transparent;
}

.flash-success {
    background: #f0fdf4;
    border-color: #bcf0da;
    color: #166534;
}
.flash-error {
    background: #fef2f2;
    border-color: #fecaca;
    color: #991b1b;
}
.flash-warning {
    background: #fffbeb;
    border-color: #fde68a;
    color: #92400e;
}

.flash-close {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.25rem;
    line-height: 1;
    opacity: 0.5;
    color: inherit;
    padding: 0 0 0 1rem;
    transition: opacity 0.15s;
}
.flash-close:hover {
    opacity: 1;
}

.flash-enter-active,
.flash-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.flash-enter-from,
.flash-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

/* ── Control Panel ───────────────────────────────────────────────── */
.control-panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 2rem;
    box-shadow: var(--shadow);
}

.panel-header {
    margin-bottom: 2rem;
}
.panel-header h2 {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
    letter-spacing: -0.01em;
}
.panel-header p {
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-top: 0.35rem;
}

.form-row {
    display: flex;
    gap: 1.25rem;
    align-items: flex-start;
    flex-wrap: wrap;
}

.field-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    flex: 1;
    min-width: 220px;
}
.field-group label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-secondary);
}

.optional {
    font-weight: 400;
    text-transform: none;
    letter-spacing: 0;
    color: var(--text-muted);
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    color: var(--text-muted);
    pointer-events: none;
}

.text-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    background: var(--surface-subtle);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--text-primary);
    font-size: 0.9rem;
    font-family: inherit;
    transition: all 0.2s;
    outline: none;
}

.year-input {
    padding-left: 1rem;
}

.text-input:focus {
    border-color: var(--accent);
    background: var(--surface);
    box-shadow: 0 0 0 4px var(--accent-subtle);
}

.text-input:disabled {
    opacity: 0.6;
    background: #f5f5f5;
    cursor: not-allowed;
}

.has-error .text-input {
    border-color: var(--red);
}

.field-error {
    font-size: 0.75rem;
    color: var(--red);
    font-weight: 500;
}

.btn-group {
    flex: 0 0 auto;
    align-self: flex-end;
}

.fetch-btn {
    padding: 0.75rem 2rem;
    background: var(--accent);
    color: #ffffff;
    border: none;
    border-radius: 10px;
    font-family: inherit;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s;
}

.fetch-btn:hover:not(:disabled) {
    background: #14532d;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(22, 101, 52, 0.15);
}

.fetch-btn:active:not(:disabled) {
    transform: translateY(0);
}

.fetch-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.625rem;
}

/* ── Spinner ─────────────────────────────────────────────────────── */
.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* ── Filter bar ──────────────────────────────────────────────────── */
.filter-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 0.5rem;
}

.filter-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.filter-select {
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text-primary);
    font-family: inherit;
    font-size: 0.875rem;
    outline: none;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2374777a'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1rem;
    transition: all 0.2s;
}

.filter-select:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-subtle);
}

.record-count {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--accent);
    background: var(--accent-subtle);
    border: 1px solid rgba(22, 101, 52, 0.1);
    border-radius: 6px;
    padding: 0.25rem 0.75rem;
}

/* ── Table card ──────────────────────────────────────────────────── */
.table-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.table-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.25rem;
    padding: 5rem 2rem;
    color: var(--text-muted);
}

/* ── Chart card ──────────────────────────────────────────────────── */
.chart-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 2rem;
    box-shadow: var(--shadow);
}

.chart-card h3 {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--text-muted);
    margin-bottom: 2rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.chart-area {
    height: 280px;
}

/* ── Responsive ──────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .header-inner {
        padding: 1rem 1.5rem;
    }
    .dash-main {
        padding: 1.5rem;
    }
    .form-row {
        flex-direction: column;
        gap: 1rem;
    }
    .field-group,
    .btn-group,
    .fetch-btn {
        width: 100%;
    }
}
</style>
