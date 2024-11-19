<?php

require "init.php";

$products = [];

try {
    $products = $stripe->products->all()->data; // Retrieve only the 'data' array
} catch (Exception $e) {
    error_log('Error fetching products: ' . $e->getMessage()); // Log the error for debugging
    echo "<p>Error fetching products. Please try again later.</p>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        .products {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .product {
            flex: 1 1 calc(33.333% - 20px);
            max-width: calc(33.333% - 20px);
            border: 1px solid #ccc;
            padding: 10px;
            box-sizing: border-box;
            text-align: center;
            border-radius: 5px;
        }
        .product img {
            max-width: 100%;
            height: 200px;
            margin-bottom: 10px;
        }
        @media (max-width: 768px) {
            .product {
                flex: 1 1 calc(50% - 20px);
            }
        }
        @media (max-width: 480px) {
            .product {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body>
    <h1>Product List</h1>
    <a href="index.php">&larr; Back to Home</a>
    <div class="products">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="product">
                    <h2><?php echo htmlspecialchars($product->name); ?></h2>
                    <?php if (!empty($product->images)): ?>
                        <img src="<?php echo htmlspecialchars(end($product->images)); ?>" alt="<?php echo htmlspecialchars($product->name); ?>">
                    <?php endif; ?>
                    <?php
                    try {
                        $price = $stripe->prices->retrieve($product->default_price);
                        echo "<p>Price: " . strtoupper($price->currency) . " " . number_format($price->unit_amount / 100, 2) . "</p>";
                    } catch (Exception $e) {
                        echo "<p>Error retrieving price</p>";
                    }
                    ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No products found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
