<script setup>
import { ref, watch } from 'vue';
import { searchCards } from '../services/cardService.js';

const query = ref('');
const cards = ref([]);
const loadingCards = ref(false);
const error = ref(null);

// Surveille la saisie : déclenche la recherche automatiquement à partir de 3 caractères
watch(query, async (newValue) => {
    if (newValue.length < 3) {
        cards.value = [];
        error.value = null;
        return;
    }
    loadingCards.value = true;
    error.value = null;
    try {
        cards.value = await searchCards(newValue);
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
