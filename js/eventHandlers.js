import { addItem, updateItemQuantity } from './api.js';
import { setEditMode, getEditMode, updateItemDisplay } from './itemManager.js';
import { updateButtonsVisibility } from './uiManager.js';

export function focusOnItemNameInput() {
  const itemNameInput = document.getElementById('itemName');
  itemNameInput.focus();
  itemNameInput.select();
}

export function setupEventListeners() {
  const form = document.getElementById('addItemForm');
  const itemNameInput = document.getElementById('itemName');
  const toggleEditModeButton = document.getElementById('toggleEditMode');

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    const itemName = itemNameInput.value.trim();
    if (itemName) {
      await addItem(itemName);
      itemNameInput.value = '';
      focusOnItemNameInput();
    }
  });

  document.addEventListener('click', async function (e) {
    const listItem = e.target.closest('li');
    if (!listItem) return;

    const itemId = listItem.dataset.id;

    if (e.target.classList.contains('increaseQuantity')) {
      const result = await updateItemQuantity(itemId, 'increase');
      if (result.success && result.item) {
        updateItemDisplay(listItem, result.item);
      }
    } else if (e.target.classList.contains('decreaseQuantity') && !e.target.disabled) {
      const result = await updateItemQuantity(itemId, 'decrease');
      if (result.success && result.item) {
        updateItemDisplay(listItem, result.item);
      }
    } else if (e.target.classList.contains('deleteItem')) {
      const result = await updateItemQuantity(itemId, 'delete');
      if (result.success) {
        listItem.remove();
      }
    } else if (!getEditMode()) {
      const result = await updateItemQuantity(itemId, 'toggleAcquistato');
      if (result.success && result.item) {
        const oldAcquistato = listItem.parentNode.id === 'boughtList';
        const newAcquistato = result.item.acquistato;

        updateItemDisplay(listItem, result.item);

        if (oldAcquistato !== newAcquistato) {
          listItem.remove();
          const targetList = newAcquistato ? document.getElementById('boughtList') : document.getElementById('toBuyList');
          targetList.insertBefore(listItem, targetList.firstChild);
        }
      }
    }
  });

  toggleEditModeButton.addEventListener('click', function () {
    const isEditMode = document.body.classList.toggle('edit-mode');
    setEditMode(isEditMode);
    toggleEditModeButton.textContent = isEditMode ? '👌' : '🔧';
    document.querySelectorAll('#toBuyList li, #boughtList li').forEach(li => {
      updateItemDisplay(li, {
        id: li.dataset.id,
        name: li.querySelector('.itemName').textContent,
        quantity: parseInt(li.dataset.quantity) || 1,
        acquistato: li.classList.contains('acquistato')
      });
    });
    updateButtonsVisibility();
  });
}