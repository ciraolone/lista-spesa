import { loadItems } from './api.js';
import { setupEventListeners, focusOnItemNameInput } from './eventHandlers.js';
import { loadAndDisplayReparti } from './repartiManager.js';
import { initializeRepartiUI, initializeDragAndDrop, organizeItemsByReparti } from './uiManager.js';

document.addEventListener('DOMContentLoaded', async function () {
  try {
    const { items } = await loadItems();
    if (Array.isArray(items)) {
      await loadAndDisplayReparti();
      organizeItemsByReparti(items);
      setupEventListeners();
      focusOnItemNameInput();
      initializeRepartiUI();
      initializeDragAndDrop();
    } else {
      console.error('Items is not an array:', items);
    }
  } catch (error) {
    console.error('Error loading items:', error);
  }
});