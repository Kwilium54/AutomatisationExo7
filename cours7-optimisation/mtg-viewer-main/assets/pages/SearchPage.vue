<script setup>
import { onMounted, ref, watch } from 'vue';
import { fetchSetCodes, searchCards } from '../services/cardService.js';

const query = ref('');
const selectedSetCode = ref('');
const cards = ref([]);
const setCodes = ref([]);
const loadingCards = ref(false);
const error = ref(null);

onMounted(async () => {
    try {
        setCodes.value = await fetchSetCodes();
    } catch (e) {
        // non-blocking: set code list is optional
    }
});

// Surveille la saisie et le setCode : déclenche la recherche automatiquement à partir de 3 caractères
watch([query, selectedSetCode], async ([newQuery]) => {
    if (newQuery.length < 3) {
        cards.value = [];
        error.value = null;
        return;
    }
    loadingCards.value = true;
    error.value = null;
    try {
        cards.value = await searchCards(newQuery, selectedSetCode.value || null);
    } catch (e) {
        error.value = 'Une erreur est survenue lors de la recherche.';
    } finally {
        loadingCards.value = false;
    }
});
</script>

<template>
    <div>
        <h1>Rechercher une Carte</h1>
        <input
            v-model="query"
            type="text"
            placeholder="Rechercher par nom (min. 3 caractères)"
        />
        <select v-model="selectedSetCode">
            <option value="">Toutes les éditions</option>
            <option v-for="code in setCodes" :key="code" :value="code">{{ code }}</option>
        </select>
    </div>
    <div class="card-list">
        <div v-if="loadingCards">Loading...</div>
        <div v-else-if="error">{{ error }}</div>
        <div v-else-if="query.length >= 3 && cards.length === 0">Aucune carte trouvée.</div>
        <div v-else>
            <div class="card" v-for="card in cards" :key="card.id">
                <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }"> {{ card.name }} - {{ card.uuid }} </router-link>
            </div>
        </div>
    </div>
</template>
