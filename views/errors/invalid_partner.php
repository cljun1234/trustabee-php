<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - Trustabee</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Roboto', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-100 max-w-md w-full text-center">
        <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fa-solid fa-ban text-2xl text-red-500"></i>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">Access Denied</h1>
        <p class="text-gray-600 mb-8 leading-relaxed">
            <?php echo htmlspecialchars($message ?? 'You are not authorized to view this page.'); ?>
        </p>

        <a href="/" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-sm w-full sm:w-auto">
            Go to Home Page
        </a>
    </div>
</body>
</html>
