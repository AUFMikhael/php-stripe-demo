<?php

require "init.php";

// Fetch products from Stripe
$products = $stripe->products->all()->data;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_products = $_POST['products'] ?? [];

    $line_items = [];
    foreach ($selected_products as $product_id) {
        $product = $stripe->products->retrieve($product_id);
        $line_items[] = [
            'price' => $product->default_price,
            'quantity' => 1
        ];
    }

    try {
        // Create a payment link with the selected products
        $payment_link = $stripe->paymentLinks->create([
            'line_items' => $line_items
        ]);

        // Redirect to the payment link URL
        header('Location: ' . $payment_link->url);
        exit;
    } catch (Exception $e) {
        // Redirect with error message
        header('Location: generate-payment-link.php?error=' . urlencode($e->getMessage()));
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Payment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        form div {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
        }

        select, input[type="checkbox"] {
            margin-right: 10px;
        }

        button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error {
            margin: 20px auto;
            padding: 10px;
            max-width: 600px;
            border-radius: 5px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <h1>Payment</h1>
    <a href="index.php">&larr; Back to Home</a>

    <!-- Display error messages -->
    <?php if (isset($_GET['error'])): ?>
        <div class="error">
            <p>Error: <?php echo htmlspecialchars($_GET['error']); ?></p>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <!-- Products List -->
        <div>
            <label>Products:</label>
            <?php foreach ($products as $product): ?>
                <div>
                    <input type="checkbox" name="products[]" value="<?php echo htmlspecialchars($product->id); ?>">
                    <?php echo htmlspecialchars($product->name); ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Submit Button -->
        <button type="submit">Generate Payment Link</button>
    </form>
</body>
</html>
