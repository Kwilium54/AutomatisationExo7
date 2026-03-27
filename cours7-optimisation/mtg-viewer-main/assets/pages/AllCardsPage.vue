<script setup>
import { onMounted, ref, watch } from 'vue';
import { fetchAllCards, fetchSetCodes } from '../services/cardService';

const cards = ref([]);
const setCodes = ref([]);
const selectedSetCode = ref('');
const loadingCards = ref(true);
const loadingSetCodes = ref(true);

async function loadCards() {
    loadingCards.value = true;
    cards.value = await fetchAllCards(selectedSetCode.value || null);
    loadingCards.value = false;
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
            <div class="card-result" v-for="card in cards" :key="card.id">
                <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }">
                    {{ card.name }} <span>({{ card.uuid }})</span>
                </router-link>
            </div>
        </div>
    </div>
</template>
