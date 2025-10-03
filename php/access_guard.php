<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 Forbidden</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-container {
            background: #fff;
            padding: 40px 30px;
            max-width: 480px;
            width: 100%;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .error-container h1 {
            font-size: 6rem;
            color: #dc3545;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .error-container i {
            font-size: 3rem;
            color: #ffc107;
            margin-bottom: 15px;
        }

        .error-container p {
            font-size: 1.1rem;
            color: #555;
            margin-bottom: 20px;
        }

        .error-container a {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #dc3545;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .error-container a:hover {
            background-color: #bd2130;
        }

        @media (max-width: 500px) {
            .error-container h1 {
                font-size: 4rem;
            }

            .error-container p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <i>🚫</i>
        <h1>403</h1>
        <p>Access Denied.<br>You are not allowed to access this file directly.</p>
        <a href="http://localhost/Purok%20Management%20System/">Go to Homepage</a>
    </div>
</body>
</html>
