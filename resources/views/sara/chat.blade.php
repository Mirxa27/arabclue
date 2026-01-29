<!DOCTYPE html>
<html>
<head>
    <title>Sara Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold mb-4">Sara - AI Travel Assistant</h1>
        <div class="bg-white rounded-lg shadow-lg p-6">
            <p class="text-gray-600">Welcome to Sara! Your AI travel assistant is ready to help you find the perfect property.</p>
            
            <div class="mt-6">
                <h2 class="text-xl font-semibold mb-4">Configuration</h2>
                <pre class="bg-gray-100 p-4 rounded">{{ json_encode($config ?? [], JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    </div>
</body>
</html>