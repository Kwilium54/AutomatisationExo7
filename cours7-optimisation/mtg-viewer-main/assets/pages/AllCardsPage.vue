<script setup>
import { onMounted, ref, watch } from 'vue';
import { fetchAllCards, fetchSetCodes } from '../services/cardService';

const cards = ref([]);
const setCodes = ref([]);
const selectedSetCode = ref('');
const currentPage = ref(1);
const totalPages = ref(1);
const total = ref(0);
const loadingCards = ref(true);
const loadingSetCodes = ref(true);

async function loadCards() {
    loadingCards.value = true;
    const result = await fetchAllCards(selectedSetCode.value || null, currentPage.value);
    cards.value = result.items;
    totalPages.value = result.totalPages;
    total.value = result.total;
    loadingCards.value = false;
}

function prevPage() {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
}

function nextPage() {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
}

onMounted(async () => {
    try {
        setCodes.value = await fetchSetCodes();
    } finally {
        loadingSetCodes.value = false;
    }
    loadCards();
});

watch(selectedSetCode, () => {
    currentPage.value = 1;
    loadCards();
});

watch(currentPage, () => {
    loadCards();
});
</script>

<template>
    <div>
        <h1>Toutes les cartes</h1>
        <select v-model="selectedSetCode" :disabled="loadingSetCodes">
            <option value="">Toutes les éditions</option>
            <option v-for="code in setCodes" :key="code" :value="code">{{ code }}</option>
        </select>
    </div>
    <div class="card-list">
        <div v-if="loadingCards">Loading...</div>
        <div v-else>
            <p>{{ total }} cartes — page {{ currentPage }} / {{ totalPages }}</p>
            <div class="card-result" v-for="card in cards" :key="card.id">
                <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }">
                    {{ card.name }} <span>({{ card.uuid }})</span>
                </router-link>
            </div>
            <div>
                <button :disabled="currentPage <= 1" @click="prevPage">← Précédent</button>
                <span> Page {{ currentPage }} / {{ totalPages }} </span>
                <button :disabled="currentPage >= totalPages" @click="nextPage">Suivant →</button>
            </div>
        </div>
    </div>
</template>
