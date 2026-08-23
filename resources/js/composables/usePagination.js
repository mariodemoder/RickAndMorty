import { ref, computed } from 'vue';

export function usePagination() {
    const currentPage = ref(1);
    const lastPage = ref(1);
    const total = ref(0);
    const perPage = ref(20);

    const meta = computed(() => ({
        current_page: currentPage.value,
        last_page: lastPage.value,
        per_page: perPage.value,
        total: total.value,
    }));

    function updateFromResponse(response) {
        if (response?.meta) {
            currentPage.value = response.meta.current_page || 1;
            lastPage.value = response.meta.last_page || 1;
            total.value = response.meta.total || 0;
            perPage.value = response.meta.per_page || 20;
        } else if (response?.current_page !== undefined) {
            currentPage.value = response.current_page || 1;
            lastPage.value = response.last_page || 1;
            total.value = response.total || 0;
            perPage.value = response.per_page || 20;
        }
    }

    function goToPage(page) {
        if (page >= 1 && page <= lastPage.value) {
            currentPage.value = page;
        }
    }

    function reset() {
        currentPage.value = 1;
    }

    return {
        currentPage,
        lastPage,
        total,
        perPage,
        meta,
        updateFromResponse,
        goToPage,
        reset,
    };
}
