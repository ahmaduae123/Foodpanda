<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first'); window.location.href='login.php';</script>";
    exit;
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<script>alert('Order not found'); window.location.href='index.php';</script>";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM order_tracking WHERE order_id = ? ORDER BY update_time DESC");
$stmt->execute([$order_id]);
$tracking = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order - Foodpanda Clone</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: linear-gradient(to bottom, #ff6f61, #ffccbc);
            color: #333;
        }
        .track-container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .track-container h2 {
            color: #e91e63;
            text-align: center;
        }
        .status {
            margin: 20px 0;
            padding: 10px;
            border-left: 5px solid #e91e63;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #e91e63;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px;
            text-align: center;
        }
        .btn:hover {
            background: #ad1457;
        }
        @media (max-width: 600px) {
            .track-container {
                margin: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="track-container">
        <h2>Track Order #<?php echo $order_id; ?></h2>
        <p>Status: <?php echo htmlspecialchars($order['status']); ?></p>
        <p>Total: $<?php echo number_format($order['total_amount'], 2); ?></p>
        <h3>Tracking Updates</h3>
        <?php foreach ($tracking as $track): ?>
            <div class="status">
                <p>Status: <?php echo htmlspecialchars($track['status']); ?></p>
                <p>Updated: <?php echo $track['update_time']; ?></p>
                <?php if ($track['location']): ?>
                    <p>Location: <?php echo htmlspecialchars($track['location']); ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <a href="#" class="btn" onclick="redirectTo('index.php')">Back to Home</a>
    </div>
    <script>
        function redirectTo(page) {
            window.location.href = page;
        }
        // Simulate real-time updates
        setInterval(() => {
            fetch('track.php?order_id=<?php echo $order_id; ?>')
                .then(response => response.text())
                .then(data => {
                    document.body.innerHTML = data;
                });
        }, 30000); // Refresh every 30 seconds
    </script>
</body>
</html>
