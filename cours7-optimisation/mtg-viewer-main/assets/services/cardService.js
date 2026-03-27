export async function fetchAllCards(setCode = null) {
    const url = setCode ? `/api/card/all?setCode=${encodeURIComponent(setCode)}` : '/api/card/all';
    const response = await fetch(url);
    if (!response.ok) throw new Error('Failed to fetch cards');
    const result = await response.json();
    return result;
}

export async function fetchCard(uuid) {
    const response = await fetch(`/api/card/${uuid}`);
    if (response.status === 404) return null;
    if (!response.ok) throw new Error('Failed to fetch card');
    const card = await response.json();
    card.text = card.text.replaceAll('\\n', '\n');
    return card;
}

export async function fetchSetCodes() {
    const response = await fetch('/api/card/set-codes');
    if (!response.ok) throw new Error('Failed to fetch set codes');
    return await response.json();
}

export async function searchCards(name, setCode = null) {
    const params = new URLSearchParams({ name });
    if (setCode) params.append('setCode', setCode);
    const response = await fetch(`/api/card/search?${params.toString()}`);
    if (!response.ok) throw new Error('Failed to search cards');
    return await response.json();
}
