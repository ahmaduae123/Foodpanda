<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first'); window.location.href='login.php';</script>";
    exit;
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $item_id = (int)$_POST['item_id'];
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE item_id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($item) {
        $cart[$item_id] = isset($cart[$item_id]) ? $cart[$item_id] + 1 : 1;
        $_SESSION['cart'] = $cart;
    }
    exit;
}

$total = 0;
$items = [];
if ($cart) {
    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE item_id IN ($placeholders)");
    $stmt->execute(array_keys($cart));
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as $item) {
        $total += $item['price'] * $cart[$item['item_id']];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    $user_id = $_SESSION['user_id'];
    $delivery_address = $_POST['delivery_address'];
    $payment_method = $_POST['payment_method'];

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, restaurant_id, total_amount, payment_method, delivery_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $items[0]['restaurant_id'], $total, $payment_method, $delivery_address]);
        $order_id = $pdo->lastInsertId();

        foreach ($items as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['item_id'], $cart[$item['item_id']], $item['price']]);
        }

        $stmt = $pdo->prepare("INSERT INTO order_tracking (order_id, status) VALUES (?, 'Processing')");
        $stmt->execute([$order_id]);

        unset($_SESSION['cart']);
        $pdo->commit();
        echo "<script>window.location.href='track.php?order_id=$order_id';</script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Foodpanda Clone</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: linear-gradient(to bottom, #ff6f61, #ffccbc);
            color: #333;
        }
        .cart-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .cart-container h2 {
            color: #e91e63;
            text-align: center;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #e91e63;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px;
        }
        .btn:hover {
            background: #ad1457;
        }
        .form-group {
            margin: 20px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        @media (max-width: 600px) {
            .cart-container {
                margin: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="cart-container">
        <h2>Your Cart</h2>
        <?php if ($items): ?>
            <?php foreach ($items as $item): ?>
                <div class="cart-item">
                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                    <span>Qty: <?php echo $cart[$item['item_id']]; ?> x $<?php echo $item['price']; ?></span>
                </div>
            <?php endforeach; ?>
            <h3>Total: $<?php echo number_format($total, 2); ?></h3>
            <form method="POST">
                <div class="form-group">
                    <label for="delivery_address">Delivery Address</label>
                    <input type="text" id="delivery_address" name="delivery_address" required>
                </div>
                <div class="form-group">
                    <label for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="COD">Cash on Delivery</option>
                        <option value="Online">Online Payment</option>
                    </select>
                </div>
                <button type="submit" name="checkout" class="btn">Checkout</button>
            </form>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
        <a href="#" class="btn" onclick="redirectTo('index.php')">Continue Shopping</a>
    </div>
    <script>
        function redirectTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>
