import { loadReparti, addReparto, updateReparto, deleteReparto, updateRepartiOrder, assignRepartoToItem } from './api.js';
import { displayError, updateRepartiDisplay, moveItemToReparto } from './uiManager.js';

export async function loadAndDisplayReparti() {
  try {
    const reparti = await loadReparti();
    updateRepartiDisplay(reparti);
  } catch (error) {
    displayError('Errore nel caricamento dei reparti: ' + error.message);
  }
}

export async function handleAddReparto(event) {
  event.preventDefault();
  const repartoNameInput = document.getElementById('repartoName');
  const nome = repartoNameInput.value.trim();

  if (nome) {
    try {
      const result = await addReparto(nome);
      if (result.success) {
        repartoNameInput.value = '';
        await loadAndDisplayReparti();
      } else {
        displayError(result.message);
      }
    } catch (error) {
      displayError('Errore nell\'aggiunta del reparto: ' + error.message);
    }
  }
}

export async function assignRepartoToItemUI(itemId, repartoId) {
  try {
    console.log(`Assegnazione dell'elemento ${itemId} al reparto ${repartoId}`);
    const result = await assignRepartoToItem(itemId, repartoId);
    if (result.success && result.item) {
      const li = document.querySelector(`li[data-id="${itemId}"]`);
      if (li) {
        // Aggiorna l'attributo data-reparto-id dell'elemento li
        li.setAttribute('data-reparto-id', repartoId || '');
        // Sposta l'elemento nel reparto corretto
        moveItemToReparto(li, repartoId);
        console.log('Reparto assegnato con successo');
      } else {
        console.error('Elemento non trovato:', itemId);
      }
    } else {
      console.error('Errore nell\'assegnazione del reparto:', result);
    }
  } catch (error) {
    console.error('Errore nell\'assegnazione del reparto:', error);
    displayError('Errore nell\'assegnazione del reparto: ' + error.message);
  }
}

export async function handleRepartiReorder(newOrder) {
  try {
    const result = await updateRepartiOrder(newOrder);
    if (result.success) {
      console.log('Ordine dei reparti aggiornato con successo');
      await loadAndDisplayReparti();
    } else {
      displayError(result.message);
    }
  } catch (error) {
    displayError('Errore nell\'aggiornamento dell\'ordine dei reparti: ' + error.message);
  }
}

loadAndDisplayReparti();