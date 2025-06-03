<?php
session_start();
require 'db.php';

// Fetch restaurants
$stmt = $pdo->query("SELECT * FROM restaurants LIMIT 4");
$restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foodpanda Clone - Home</title>
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
        .nav {
            display: flex;
            justify-content: center;
            gap: 20px;
            padding: 10px;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .nav a {
            text-decoration: none;
            color: #e91e63;
            font-weight: bold;
            font-size: 1.1em;
        }
        .nav a:hover {
            color: #ad1457;
        }
        .restaurant-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .restaurant-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        .restaurant-card:hover {
            transform: scale(1.05);
        }
        .restaurant-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .restaurant-card h3 {
            margin: 10px;
            color: #e91e63;
        }
        .restaurant-card p {
            margin: 0 10px 10px;
            color: #555;
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
            .restaurant-grid {
                grid-template-columns: 1fr;
            }
            .header h1 {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Foodpanda Clone</h1>
        <div class="nav">
            <a href="#" onclick="redirectTo('login.php')">Login</a>
            <a href="#" onclick="redirectTo('signup.php')">Signup</a>
            <a href="#" onclick="redirectTo('cart.php')">Cart</a>
        </div>
    </div>
    <div class="restaurant-grid">
        <?php foreach ($restaurants as $restaurant): ?>
            <div class="restaurant-card">
                <img src="<?php echo htmlspecialchars($restaurant['image']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>">
                <h3><?php echo htmlspecialchars($restaurant['name']); ?></h3>
                <p><?php echo htmlspecialchars($restaurant['cuisine']); ?> - <?php echo htmlspecialchars($restaurant['location']); ?></p>
                <p>Rating: <?php echo htmlspecialchars($restaurant['rating']); ?></p>
                <a href="#" class="btn" onclick="redirectTo('restaurant.php?id=<?php echo $restaurant['restaurant_id']; ?>')">View Menu</a>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        function redirectTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>
