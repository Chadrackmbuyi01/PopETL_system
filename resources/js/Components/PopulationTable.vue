<template>
    <div class="table-wrapper">
        <!-- Empty state -->
        <div v-if="!records.length" class="empty-state">
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 2.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125m16.5 2.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"
                />
            </svg>
            <p>No population records yet.</p>
            <span>Use the form above to fetch data for a country.</span>
        </div>

        <!-- Table -->
        <template v-else>
            <div class="table-scroll">
                <table class="pop-table">
                    <thead>
                        <tr>
                            <th>Country</th>
                            <th>Year</th>
                            <th class="num">Population</th>
                            <th class="num">Growth</th>
                            <th class="num">Density</th>
                            <th>Extracted</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="record in records"
                            :key="record.id"
                            class="pop-row"
                        >
                            <td class="country-cell">
                                <span class="country-badge">{{
                                    record.country
                                }}</span>
                            </td>
                            <td class="year-cell">{{ record.year }}</td>
                            <td class="num pop-num">
                                {{ record.total_population_fmt }}
                            </td>
                            <td
                                class="num growth-cell"
                                :class="growthClass(record.population_growth)"
                            >
                                {{ record.population_growth_fmt }}
                            </td>
                            <td class="num">
                                {{ record.population_density_fmt }}
                            </td>
                            <td class="date-cell">
                                {{ record.extracted_at_fmt }}
                            </td>
                            <td class="action-cell">
                                <button
                                    class="delete-btn"
                                    title="Delete record"
                                    @click="$emit('delete', record.id)"
                                >
                                    <svg
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                        width="14"
                                        height="14"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="pagination && pagination.lastPage > 1"
                class="pagination"
            >
                <span class="pagination-info">
                    Showing {{ pagination.from }}–{{ pagination.to }} of
                    {{ pagination.total }}
                </span>
                <div class="pagination-controls">
                    <button
                        :disabled="pagination.currentPage === 1"
                        class="page-btn"
                        @click="$emit('page', pagination.currentPage - 1)"
                    >
                        ← Prev
                    </button>

                    <span class="page-indicator">
                        {{ pagination.currentPage }} / {{ pagination.lastPage }}
                    </span>

                    <button
                        :disabled="
                            pagination.currentPage === pagination.lastPage
                        "
                        class="page-btn"
                        @click="$emit('page', pagination.currentPage + 1)"
                    >
                        Next →
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
defineProps({
    records: { type: Array, required: true },
    pagination: { type: Object, default: null },
});

defineEmits(["delete", "page"]);

function growthClass(growth) {
    if (growth === null || growth === undefined) return "";
    return Number(growth) >= 0 ? "positive" : "negative";
}
</script>

<style scoped>
.table-wrapper {
    width: 100%;
}

/* Empty state */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 4rem 2rem;
    color: var(--text-muted);
}
.empty-state svg {
    width: 48px;
    height: 48px;
    opacity: 0.3;
    margin-bottom: 0.5rem;
}
.empty-state p {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
    color: var(--text-secondary);
}
.empty-state span {
    font-size: 0.875rem;
}

/* Table */
.table-scroll {
    overflow-x: auto;
}

.pop-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.pop-table thead th {
    padding: 1rem 1.25rem;
    text-align: left;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border);
    background: var(--surface-subtle);
    white-space: nowrap;
}
.pop-table thead th.num {
    text-align: right;
}

.pop-row {
    border-bottom: 1px solid var(--border);
    transition: all 0.2s;
}
.pop-row:hover {
    background: var(--surface-hover);
}
.pop-row:last-child {
    border-bottom: none;
}

.pop-table td {
    padding: 1.125rem 1.25rem;
    vertical-align: middle;
    color: var(--text-primary);
}
.pop-table td.num {
    text-align: right;
    font-variant-numeric: tabular-nums;
}

.country-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: var(--accent-subtle);
    color: var(--accent);
    border: 1px solid rgba(22, 101, 52, 0.1);
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
}

.year-cell {
    font-weight: 600;
    color: var(--text-secondary);
    font-variant-numeric: tabular-nums;
}

.pop-num {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-primary);
}

.growth-cell {
    font-weight: 600;
}
.growth-cell.positive {
    color: var(--green);
}
.growth-cell.negative {
    color: var(--red);
}

.date-cell {
    font-size: 0.75rem;
    color: var(--text-muted);
    white-space: nowrap;
}

.action-cell {
    width: 48px;
    text-align: center;
}

.delete-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: 1px solid transparent;
    border-radius: 8px;
    background: transparent;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.2s;
}
.delete-btn:hover {
    background: var(--red-subtle);
    border-color: rgba(220, 38, 38, 0.1);
    color: var(--red);
}

/* Pagination */
.pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-top: 1px solid var(--border);
    background: var(--surface-subtle);
    font-size: 0.8rem;
    color: var(--text-muted);
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.page-btn {
    padding: 0.5rem 1rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--surface);
    color: var(--text-secondary);
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.page-btn:hover:not(:disabled) {
    background: var(--surface-hover);
    border-color: var(--accent);
    color: var(--accent);
    transform: translateY(-1px);
}
.page-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.page-indicator {
    font-weight: 700;
    color: var(--text-primary);
    min-width: 80px;
    text-align: center;
    letter-spacing: 0.05em;
}
</style>
