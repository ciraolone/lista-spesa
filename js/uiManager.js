import { loadAndDisplayReparti, assignRepartoToItemUI, handleRepartiReorder } from './repartiManager.js';
import { addReparto } from './api.js';
import { updateItemDisplay } from './itemManager.js';

export function displayError(message) {
  console.error("Error:", message);
  alert(message);
}

export function updateButtonsVisibility() {
  document.querySelectorAll('.items-list li').forEach(li => {
    const decreaseButton = li.querySelector('.decreaseQuantity');
    const increaseButton = li.querySelector('.increaseQuantity');
    const deleteButton = li.querySelector('.deleteItem');
    const isAcquistato = li.classList.contains('acquistato');

    if (isAcquistato) {
      decreaseButton.style.display = 'none';
      increaseButton.style.display = 'none';
      deleteButton.style.display = document.body.classList.contains('edit-mode') ? '' : 'none';
    } else {
      decreaseButton.style.display = document.body.classList.contains('edit-mode') ? '' : 'none';
      increaseButton.style.display = document.body.classList.contains('edit-mode') ? '' : 'none';
      deleteButton.style.display = 'none';
    }
  });
}

export function initializeRepartiUI() {
  const manageRepartiBtn = document.getElementById('manageReparti');
  const repartiPopup = document.getElementById('repartiPopup');
  const closeRepartiPopup = repartiPopup.querySelector('.close');
  const addRepartoForm = document.getElementById('addRepartoForm');

  manageRepartiBtn.addEventListener('click', () => {
    repartiPopup.style.display = 'block';
    loadAndDisplayReparti();
  });

  closeRepartiPopup.addEventListener('click', () => {
    repartiPopup.style.display = 'none';
  });

  window.addEventListener('click', (event) => {
    if (event.target == repartiPopup) {
      repartiPopup.style.display = 'none';
    }
  });

  addRepartoForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const repartoName = document.getElementById('repartoName').value.trim();
    if (repartoName) {
      try {
        const result = await addReparto(repartoName);
        if (result.success) {
          document.getElementById('repartoName').value = '';
          await loadAndDisplayReparti();
        } else {
          displayError(result.message || 'Errore nell\'aggiunta del reparto');
        }
      } catch (error) {
        displayError('Errore nell\'aggiunta del reparto: ' + error.message);
      }
    }
  });
}

function handleDragStart(evt) {
  evt.target.classList.add('dragging');
}

function handleDragEnd(evt) {
  evt.target.classList.remove('dragging');
  handleItemMove(evt);
}

function handleItemMove(evt) {
  const item = evt.item;
  const toList = evt.to;
  const fromList = evt.from;

  if (toList !== fromList) {
    const newRepartoContainer = toList.closest('.reparto-container');
    const newRepartoId = newRepartoContainer ? newRepartoContainer.dataset.repartoId : null;

    if (toList.id === 'boughtList' || fromList.id === 'boughtList') {
      item.classList.toggle('acquistato');
      updateItemDisplay(item, {
        id: item.dataset.id,
        name: item.querySelector('.itemName').textContent,
        quantity: parseInt(item.dataset.quantity) || 1,
        acquistato: item.classList.contains('acquistato')
      });
    }

    assignRepartoToItemUI(item.dataset.id, newRepartoId);
  }

  updateButtonsVisibility();
}

function updateRepartiOrder() {
  const repartiContainers = document.querySelectorAll('.reparto-container');
  const newOrder = Array.from(repartiContainers).map(container => container.dataset.repartoId);
  handleRepartiReorder(newOrder);
}

export function initializeDragAndDrop() {
  const shoppingList = document.getElementById('shoppingList');
  const toBuyList = document.getElementById('toBuyList');
  const boughtList = document.getElementById('boughtList');
  const repartiContainer = document.getElementById('repartiContainer');

  const sortableOptions = {
    group: 'shared',
    animation: 150,
    onStart: handleDragStart,
    onEnd: handleDragEnd
  };

  new Sortable(toBuyList, sortableOptions);
  new Sortable(boughtList, sortableOptions);

  document.querySelectorAll('.reparto-container .items-list').forEach(list => {
    new Sortable(list, sortableOptions);
  });

  new Sortable(repartiContainer, {
    animation: 150,
    handle: '.reparto-heading',
    onEnd: updateRepartiOrder
  });
}

export function createRepartoContainer(reparto) {
  const container = document.createElement('div');
  container.className = 'reparto-container';
  container.dataset.repartoId = reparto.id;

  const heading = document.createElement('h3');
  heading.className = 'reparto-heading';
  heading.textContent = reparto.nome;

  const itemsList = document.createElement('ul');
  itemsList.className = 'items-list';

  container.appendChild(heading);
  container.appendChild(itemsList);

  new Sortable(itemsList, {
    group: 'shared',
    animation: 150,
    onStart: handleDragStart,
    onEnd: handleDragEnd
  });

  return container;
}

export function moveItemToReparto(itemElement, repartoId) {
  if (!itemElement) {
    console.error('itemElement is null or undefined');
    return;
  }

  const repartoContainer = repartoId ? document.querySelector(`.reparto-container[data-reparto-id="${repartoId}"]`) : null;
  if (repartoContainer) {
    const itemsList = repartoContainer.querySelector('.items-list');
    if (itemsList) {
      itemsList.appendChild(itemElement);
    } else {
      console.error('items-list not found in reparto container:', repartoContainer);
    }
  } else if (repartoId === null) {
    const toBuyList = document.getElementById('toBuyList');
    if (toBuyList) {
      toBuyList.appendChild(itemElement);
    } else {
      console.error('toBuyList not found');
    }
  } else {
    console.error('Reparto container not found for id:', repartoId);
  }
}

export function updateRepartiDisplay(reparti) {
  const repartiContainer = document.getElementById('repartiContainer');
  repartiContainer.innerHTML = '';

  reparti.forEach(reparto => {
    const repartoElement = createRepartoContainer(reparto);
    repartiContainer.appendChild(repartoElement);
  });
}

export function organizeItemsByReparti(items) {
  if (!Array.isArray(items)) {
    console.error('Items is not an array:', items);
    return;
  }

  const toBuyList = document.getElementById('toBuyList');
  const boughtList = document.getElementById('boughtList');
  const repartiContainers = {};

  if (toBuyList) toBuyList.innerHTML = '';
  if (boughtList) boughtList.innerHTML = '';
  document.querySelectorAll('.reparto-container .items-list').forEach(list => list.innerHTML = '');

  items.forEach(item => {
    const li = createListItem(item);
    if (!li) return; // Skip if createListItem returns null

    if (item.acquistato && boughtList) {
      boughtList.appendChild(li);
    } else if (item.reparto_id) {
      if (!repartiContainers[item.reparto_id]) {
        const repartoContainer = document.querySelector(`.reparto-container[data-reparto-id="${item.reparto_id}"]`);
        if (repartoContainer) {
          repartiContainers[item.reparto_id] = repartoContainer.querySelector('.items-list');
        }
      }
      if (repartiContainers[item.reparto_id]) {
        repartiContainers[item.reparto_id].appendChild(li);
      } else if (toBuyList) {
        toBuyList.appendChild(li);
      }
    } else if (toBuyList) {
      toBuyList.appendChild(li);
    }
  });
}

function createListItem(item) {
  const li = document.createElement('li');
  li.dataset.id = item.id;
  li.dataset.quantity = item.quantity;
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