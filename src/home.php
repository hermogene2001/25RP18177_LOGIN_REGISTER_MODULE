<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user information from session
$user_firstname = $_SESSION['user_firstname'] ?? '';
$user_lastname = $_SESSION['user_lastname'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';
$username = $user_firstname . ' ' . $user_lastname;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Welcome</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.8);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
        }

        .navbar-brand {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .navbar-menu {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .navbar-menu a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .navbar-menu a:hover {
            color: #667eea;
        }

        .logout-btn {
            background-color: #764ba2;
            color: white;
            padding: 0.5rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
            font-size: 0.9rem;
        }

        .logout-btn:hover {
            background-color: #63408a;
        }

        .container {
            background: white;
            padding: 3rem 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            margin-top: 80px;
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }

        .welcome-text {
            color: #667eea;
            font-size: 1.3rem;
            margin-bottom: 2rem;
            font-weight: 600;
        }

        .user-info {
            background-color: #f0f0f0;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem 0;
            text-align: left;
        }

        .user-info p {
            margin: 0.5rem 0;
            color: #333;
            font-size: 1rem;
        }

        .user-info label {
            font-weight: 600;
            color: #667eea;
        }

        .status-badge {
            display: inline-block;
            background-color: #d4edda;
            color: #155724;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            margin-top: 1rem;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .feature-card {
            background-color: #f9f9f9;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .feature-card h3 {
            color: #667eea;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .feature-card p {
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-brand">ShareRide</div>
        <div class="navbar-menu">
            <a href="home.php">Home</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <h1>Welcome! 🎉</h1>
        <div class="welcome-text">
            Hello, <strong><?php echo htmlspecialchars($username); ?></strong>
        </div>

        <div class="status-badge">✓ You are logged in</div>

        <div class="user-info">
            <p><label>Name:</label> <?php echo htmlspecialchars($username); ?></p>
            <p><label>Email:</label> <?php echo htmlspecialchars($user_email); ?></p>
        </div>

        <h2 style="color: #333; margin-top: 2rem;">Features</h2>
        <div class="features">
            <div class="feature-card">
                <h3>🚗 Find Rides</h3>
                <p>Search and book available rides</p>
            </div>
            <div class="feature-card">
                <h3>📍 Offer Rides</h3>
                <p>Share your commute with others</p>
            </div>
            <div class="feature-card">
                <h3>⭐ Ratings</h3>
                <p>View your ratings and reviews</p>
            </div>
            <div class="feature-card">
                <h3>💬 Messages</h3>
                <p>Chat with other users</p>
            </div>
        </div>
    </div>
</body>
</html>
