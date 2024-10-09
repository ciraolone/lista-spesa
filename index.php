<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'database.php';

$conn = getDbConnection();
$result = $conn->query("SELECT * FROM items ORDER BY created_at DESC");
$items = $result->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<h1>La Mia Lista della Spesa</h1>

<form id="addItemForm">
    <input type="text" id="itemName" name="itemName" placeholder="Nome dell'articolo" required>
    <button type="submit">🛒</button>
</form>

<div class="button-container">
    <button id="toggleEditMode">🔧</button>
    <button id="manageReparti">🪜</button>
</div>

<div id="shoppingList">
    <h2>Da Acquistare</h2>
    <ul id="toBuyList" class="items-list">
        <!-- Gli elementi senza reparto verranno inseriti qui -->
    </ul>
    
    <div id="repartiContainer">
        <!-- I reparti e i loro elementi verranno inseriti qui dinamicamente -->
    </div>
</div>

<div id="shoppedList">
<h2>Acquistati</h2>
<ul id="boughtList" class="items-list">
    <!-- Gli elementi acquistati verranno inseriti qui -->
</ul>
</div>

<?php include 'components/repartiPopup.php'; ?>

<?php include 'includes/footer.php'; ?>