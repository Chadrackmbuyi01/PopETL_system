import { ref, computed } from "vue";
import axios from "axios";

// Axios instances with base config
const api = axios.create({
    baseURL: "/api",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
});

// Global interception: attach CRSF token for POST requests
api.interceptors.request.use((config) => {
    const token = document.head.querySelector('meta[name="csrf-token"]');
    if (token) config.headers["X-CSRF-TOKEN"] = token.content;

    return config;
});

export function usePopulation() {
    const records = ref([]); // Holds population data array
    const pagination = ref(null); // Pagination metadata from API
    const countries = ref([]); // Unique list of countries for filter dropdown
    const isLoading = ref(false); // Loading state for GET operations
    const isFetching = ref(false); // Loading state for POST (ETL) fetch operation
    const flashMessage = ref(null); // User notifications (success/error/info)
    const flashType = ref("success"); // 'success' | 'error' | 'info' | 'warning'
    const errors = ref({}); // Validation errors from API

    // ---- Flash helper ----
    let flashTimer = null;

    function setFlash(message, type = "success", duration = 5000) {
        flashMessage.value = message;
        flashType.value = type;
        if (flashTimer) clearTimeout(flashTimer);
        flashTimer = setTimeout(() => (flashMessage.value = null), duration);
    }

    function clearFlash() {
        flashMessage.value = null;
    }

    // --- Load records ---
    async function loadRecords(params = {}) {
        isLoading.value = true;
        errors.value = {};

        try {
            const { data } = await api.get("/population", { params });
            records.value = data.data;
            pagination.value = {
                currentPage: data.current_page,
                lastPage: data.last_page,
                perPage: data.per_page,
                total: data.total,
                from: data.from,
                to: data.to,
                links: data.links,
            };
        } catch (err) {
            setFlash(
                "Failed to load records. Please reflresh and try again.",
                "error",
            );
        } finally {
            isLoading.value = false;
        }
    }

    // --- Load countries ---
    async function loadCountries() {
        try {
            const { data } = await api.get("/population/countries");
            countries.value = data;
        } catch (err) {
            // Non critical - silently fail
        }
    }

    // --- Trigger ETL fetch ---
    async function fetchCountry(country, year = null) {
        isFetching.value = true;
        errors.value = {};
        clearFlash();

        try {
            const payload = { country };
            if (year) payload.year = year;

            const { data, status } = await api.post(
                "/population/fetch",
                payload,
            );

            setFlash(data.message, status === 200 ? "success" : "warning");
            await loadRecords();
            await loadCountries();

            return { success: true, data };
        } catch (err) {
            if (err.response?.status === 422) {
                // Laravel validation errors
                errors.value = err.response.data.errors ?? {};
                setFlash("Please fix the validation errors below.", "error");
            } else if (err.response?.status === 404) {
                setFlash(err.response.data.message, "warning");
            } else {
                setFlash(
                    err.response?.data?.message ??
                        "An unexpected error occurred. Please try again.",
                    "error",
                );
            }
            return { success: false };
        } finally {
            isFetching.value = false;
        }
    }

    // --- Delete record ---
    async function deleteRecord(id) {
        try {
            await api.delete(`/population/${id}`);
            records.value = records.value.filter((r) => r.id !== id);
            setFlash("Record deleted.", "success", 3000);
        } catch {
            setFlash("Failed to delete record.", "error");
        }
    }

    // ─── Pagination helper ─────────────────────────────────────────────────────
    async function goToPage(page, extraParams = {}) {
        await loadRecords({ page, ...extraParams });
    }

    // ─── Computed ──────────────────────────────────────────────────────────────
    const hasRecords = computed(() => records.value.length > 0);

    const formattedRecords = computed(() =>
        records.value.map((r) => ({
            ...r,
            total_population_fmt: Number(r.total_population).toLocaleString(),
            population_growth_fmt: r.population_growth
                ? `${Number(r.population_growth).toFixed(2)}%`
                : "—",
            population_density_fmt: r.population_density
                ? `${Number(r.population_density).toFixed(1)} /km²`
                : "—",
            extracted_at_fmt: new Date(r.extracted_at).toLocaleString(),
        })),
    );

    return {
        // State
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
        // Actions
        loadRecords,
        loadCountries,
        fetchCountry,
        deleteRecord,
        goToPage,
        setFlash,
        clearFlash,
    };
}

export { api }; // Exporting the configured Axios instance for use in other composables or components
