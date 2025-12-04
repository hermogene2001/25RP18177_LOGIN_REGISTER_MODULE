<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication Hub</title>
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
        }

        .container {
            text-align: center;
            background: white;
            padding: 3rem 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 90%;
        }

        h1 {
            color: #333;
            margin-bottom: 1rem;
        }

        p {
            color: #666;
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        .links {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        a {
            padding: 0.75rem 2rem;
            font-size: 1rem;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
            font-weight: 600;
            border: 2px solid;
        }

        .login-link {
            background-color: #667eea;
            color: white;
            border-color: #667eea;
        }

        .login-link:hover {
            background-color: #5568d3;
            border-color: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .register-link {
            background-color: #764ba2;
            color: white;
            border-color: #764ba2;
        }

        .register-link:hover {
            background-color: #63408a;
            border-color: #63408a;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(118, 75, 162, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome</h1>
        <p>Please choose an option below to get started</p>
        <div class="links">
            <a href="login.php" class="login-link">Login</a>
            <a href="register.php" class="register-link">Register</a>
        </div>
    </div>
</body>
</html>
