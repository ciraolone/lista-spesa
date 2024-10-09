let isEditMode = false;

export function setEditMode(mode) {
  isEditMode = mode;
}

export function getEditMode() {
  return isEditMode;
}

export function updateItemDisplay(li, item) {
  if (!li || !item) {
    console.error('Invalid li or item:', { li, item });
    return;
  }

  const { id, name, quantity, acquistato } = item;
  const itemNameElement = li.querySelector('.itemName');

  if (!itemNameElement) {
    console.error('itemNameElement not found in li:', li);
    return;
  }

  // Rimuovi eventuali contatori esistenti dal nome
  const cleanName = name.replace(/ \(\d+\)$/, '');

  // Aggiorna il testo dell'elemento
  itemNameElement.textContent = acquistato ? cleanName : (quantity > 1 ? `${cleanName} (${quantity})` : cleanName);

  li.dataset.id = id;
  li.dataset.quantity = quantity;

  const decreaseButton = li.querySelector('.decreaseQuantity');
  const increaseButton = li.querySelector('.increaseQuantity');
  const deleteButton = li.querySelector('.deleteItem');

  if (acquistato) {
    li.classList.add('acquistato');
    if (decreaseButton) decreaseButton.style.display = 'none';
    if (increaseButton) increaseButton.style.display = 'none';
    if (deleteButton) deleteButton.style.display = isEditMode ? 'inline-block' : 'none';
  } else {
    li.classList.remove('acquistato');
    if (decreaseButton) decreaseButton.disabled = quantity <= 1;
    if (deleteButton) deleteButton.style.display = 'none';
  }
}

export function createListItem(item) {
  if (!item || !item.id) {
    console.error('Invalid item data:', item);
    return null;
  }

  const li = document.createElement('li');
  li.dataset.id = item.id;
  li.dataset.lastBought = item.ultimo_acquisto || '';
  li.dataset.dataDaAcquistare = item.data_da_acquistare || '';
  li.draggable = true;
  li.innerHTML = `
    <span class="itemName"></span>
    <button class="decreaseQuantity">-</button>
    <button class="increaseQuantity">+</button>
    <button class="deleteItem">🗑️</button>
  `;
  updateItemDisplay(li, item);
  return li;
}