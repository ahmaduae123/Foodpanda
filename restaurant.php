<?php
session_start();
require 'db.php';

$restaurant_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE restaurant_id = ?");
$stmt->execute([$restaurant_id]);
$restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$restaurant) {
    echo "<script>alert('Restaurant not found'); window.location.href='index.php';</script>";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM menu_items WHERE restaurant_id = ?");
$stmt->execute([$restaurant_id]);
$menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique categories for filtering
$categories = array_unique(array_column($menu_items, 'category'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($restaurant['name']); ?> - Foodpanda Clone</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: linear-gradient(to bottom, #ff6f61, #ffccbc);
            color: #333;
        }
        .header {
            background: #e91e63;
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        .header h1 {
            margin: 0;
            font-size: 2.5em;
        }
        .filter {
            padding: 20px;
            text-align: center;
        }
        .filter input {
            padding: 10px;
            width: 200px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            outline: none;
        }
        .container {
            display: flex;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            gap: 20px;
        }
        .sidebar {
            width: 200px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .sidebar h3 {
            margin: 0 0 10px;
            color: #e91e63;
        }
        .sidebar label {
            display: block;
            margin: 10px 0;
            cursor: pointer;
            color: #555;
        }
        .sidebar input[type="checkbox"] {
            margin-right: 5px;
        }
        .menu-grid {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .menu-item {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            transition: transform 0.3s;
            cursor: pointer;
            position: relative;
        }
        .menu-item:hover {
            transform: scale(1.05);
        }
        .menu-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .menu-item-content {
            padding: 10px;
            text-align: center;
        }
        .menu-item-content h3 {
            margin: 0;
            color: #e91e63;
            font-size: 1.2em;
        }
        .menu-item-content p {
            margin: 5px 0;
            color: #555;
        }
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background: #e91e63;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9em;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn:hover {
            background: #ad1457;
        }
        #item-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        #item-modal .modal-content {
            background: white;
            margin: 50px auto;
            padding: 20px;
            width: 300px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        @media (max-width: 800px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                box-sizing: border-box;
            }
            .menu-grid {
                grid-template-columns: 1fr;
            }
            .header h1 {
                font-size: 1.8em;
            }
            .filter input {
                width: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo htmlspecialchars($restaurant['name']); ?></h1>
        <p><?php echo htmlspecialchars($restaurant['cuisine']); ?> - Rating: <?php echo htmlspecialchars($restaurant['rating']); ?></p>
    </div>
    <div class="filter">
        <input type="text" id="search" placeholder="Search menu items..." onkeyup="filterMenu()">
    </div>
    <div class="container">
        <div class="sidebar">
            <h3>Categories</h3>
            <?php foreach ($categories as $category): ?>
                <label>
                    <input type="checkbox" class="category-filter" value="<?php echo htmlspecialchars(strtolower($category)); ?>" onchange="filterMenu()">
                    <?php echo htmlspecialchars($category); ?>
                </label>
            <?php endforeach; ?>
        </div>
        <div class="menu-grid" id="menu-grid">
            <?php foreach ($menu_items as $item): ?>
                <div class="menu-item" data-name="<?php echo htmlspecialchars(strtolower($item['name'])); ?>" data-category="<?php echo htmlspecialchars(strtolower($item['category'])); ?>">
                    <img src="<?php echo htmlspecialchars($item['image'] ?: 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <div class="menu-item-content">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p>$<?php echo htmlspecialchars($item['price']); ?></p>
                        <button class="btn" onclick="showItemDetails(<?php echo $item['item_id']; ?>)">Add to Cart</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal for Item Details -->
    <div id="item-modal">
        <div class="modal-content">
            <h3 id="modal-name"></h3>
            <p id="modal-price"></p>
            <p id="modal-description"></p>
            <button class="btn" onclick="addToCart(selectedItemId)">Confirm Add to Cart</button>
            <button class="btn" style="background:#666;" onclick="closeModal()">Close</button>
        </div>
    </div>

    <script>
        let selectedItemId = null;

        function filterMenu() {
            const search = document.getElementById('search').value.toLowerCase();
            const selectedCategories = Array.from(document.querySelectorAll('.category-filter:checked'))
                                           .map(checkbox => checkbox.value);
            const items = document.querySelectorAll('.menu-item');

            items.forEach(item => {
                const name = item.getAttribute('data-name');
                const category = item.getAttribute('data-category');
                const matchesSearch = name.includes(search);
                const matchesCategory = selectedCategories.length === 0 || selectedCategories.includes(category);
                item.style.display = matchesSearch && matchesCategory ? 'block' : 'none';
            });
        }

        function showItemDetails(itemId) {
            selectedItemId = itemId;
            const item = <?php echo json_encode($menu_items); ?>.find(i => i.item_id === itemId);
            if (item) {
                document.getElementById('modal-name').textContent = item.name;
                document.getElementById('modal-price').textContent = '$' + item.price;
                document.getElementById('modal-description').textContent = item.description || 'No description available';
                document.getElementById('item-modal').style.display = 'block';
            }
        }

        function closeModal() {
            document.getElementById('item-modal').style.display = 'none';
        }

        function addToCart(itemId) {
            fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'item_id=' + itemId + '&action=add'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(data => {
                alert('Item added to cart!');
                closeModal();
                redirectTo('cart.php');
            })
            .catch(error => {
                alert('Error adding item to cart: ' + error.message);
            });
        }

        function redirectTo(page) {
            window.location.href = page;
        }

        // Ensure clicking outside the modal closes it
        document.getElementById('item-modal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>
