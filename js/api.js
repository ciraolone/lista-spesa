import { displayError } from './uiManager.js';
import { createListItem, updateItemDisplay } from './itemManager.js';

export async function fetchWithErrorHandling(url, options = {}) {
  try {
    console.log('Sending request to:', url, 'with options:', options);
    const response = await fetch(url, options);
    console.log('Received response:', response);
    const contentType = response.headers.get("content-type");
    if (!contentType || !contentType.includes("application/json")) {
      const text = await response.text();
      console.error("Non-JSON response:", text);
      throw new Error("Oops! We haven't received a JSON response from the server.");
    }
    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
    }
    const data = await response.json();
    console.log('Parsed response data:', data);
    return data;
  } catch (error) {
    console.error("Fetch error:", error);
    displayError(`An error occurred: ${error.message}`);
    throw error;
  }
}

export async function loadItems() {
  try {
    const data = await fetchWithErrorHandling('api/items.php');
    if (data.success && Array.isArray(data.items)) {
      return { items: data.items };
    } else {
      console.error('Invalid data structure received:', data);
      return { items: [] };
    }
  } catch (error) {
    console.error('Error loading items:', error);
    return { items: [] };
  }
}

export async function addItem(itemName) {
  const formData = new FormData();
  formData.append('itemName', itemName);
  const data = await fetchWithErrorHandling('api/items.php', {
    method: 'POST',
    body: formData
  });

  if (data.success && data.item) {
    const toBuyList = document.getElementById('toBuyList');
    const boughtList = document.getElementById('boughtList');
    const existingItem = document.querySelector(`li[data-id="${data.item.id}"]`);

    if (existingItem) {
      // Rimuovi l'elemento esistente dalla sua posizione attuale
      existingItem.remove();

      // Aggiorna l'elemento con i nuovi dati
      updateItemDisplay(existingItem, data.item);

      // Sposta l'elemento nel reparto corretto o nella lista "da acquistare"
      if (data.item.reparto_id) {
        const repartoContainer = document.querySelector(`.reparto-container[data-reparto-id="${data.item.reparto_id}"]`);
        if (repartoContainer) {
          const repartoList = repartoContainer.querySelector('.items-list');
          repartoList.insertBefore(existingItem, repartoList.firstChild);
        } else {
          toBuyList.insertBefore(existingItem, toBuyList.firstChild);
        }
      } else {
        toBuyList.insertBefore(existingItem, toBuyList.firstChild);
      }
    } else {
      const newItem = createListItem(data.item);
      if (newItem) {
        if (data.item.reparto_id) {
          const repartoContainer = document.querySelector(`.reparto-container[data-reparto-id="${data.item.reparto_id}"]`);
          if (repartoContainer) {
            const repartoList = repartoContainer.querySelector('.items-list');
            repartoList.insertBefore(newItem, repartoList.firstChild);
          } else {
            toBuyList.insertBefore(newItem, toBuyList.firstChild);
          }
        } else {
          toBuyList.insertBefore(newItem, toBuyList.firstChild);
        }
      } else {
        console.error('Failed to create list item from server response:', data.item);
      }
    }
  } else {
    console.error('Server response:', data);
    throw new Error('Failed to add item: Server response did not include valid item data');
  }
}

export async function updateItemQuantity(itemId, action) {
  const data = await fetchWithErrorHandling('api/items.php', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: itemId, action: action })
  });

  if (data.success && data.item) {
    const listItem = document.querySelector(`li[data-id="${itemId}"]`);
    if (listItem) {
      if (action === 'delete') {
        listItem.remove();
      } else {
        const oldAcquistato = listItem.parentNode.id === 'boughtList';
        const newAcquistato = data.item.acquistato;

        updateItemDisplay(listItem, data.item);

        if (oldAcquistato !== newAcquistato) {
          listItem.remove();
          const targetList = newAcquistato ? document.getElementById('boughtList') : document.getElementById('toBuyList');
          targetList.insertBefore(listItem, targetList.firstChild);
        }
      }
    }
  }
  return data;
}

export async function loadReparti() {
  const data = await fetchWithErrorHandling('api/reparti.php');
  if (data.success) {
    return data.reparti;
  }
  return [];
}

export async function addReparto(nome) {
  console.log('Adding reparto:', nome);
  const formData = new FormData();
  formData.append('nome', nome);
  const data = await fetchWithErrorHandling('api/reparti.php', {
    method: 'POST',
    body: formData
  });
  console.log('Reparto add response:', data);
  return data;
}

export async function updateReparto(id, nome) {
  const data = await fetchWithErrorHandling('api/reparti.php', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id, nome })
  });
  return data;
}

export async function deleteReparto(id) {
  const data = await fetchWithErrorHandling('api/reparti.php', {
    method: 'DELETE',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id })
  });
  return data;
}

export async function assignRepartoToItem(itemId, repartoId) {
  console.log(`Chiamata API per assegnare l'elemento ${itemId} al reparto ${repartoId}`);
  const data = await fetchWithErrorHandling('api/items.php', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: itemId, action: 'assignReparto', repartoId: repartoId })
  });
  console.log('Risposta API:', data);
  return data;
}

export async function updateRepartiOrder(newOrder) {
  const data = await fetchWithErrorHandling('api/reparti.php', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'updateOrder', newOrder: newOrder })
  });
  return data;
}

