export async function fetchAllCards(setCode = null, page = 1) {
    const params = new URLSearchParams({ page });
    if (setCode) params.append('setCode', setCode);
    const response = await fetch(`/api/card/all?${params.toString()}`);
    if (!response.ok) throw new Error('Failed to fetch cards');
    return await response.json();
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
