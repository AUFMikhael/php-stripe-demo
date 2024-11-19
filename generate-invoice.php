<?php

require "init.php";

// Fetch customers and products from Stripe
$customers = $stripe->customers->all(['limit' => 50])->data;
$products = $stripe->products->all()->data;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'] ?? null;
    $selected_products = $_POST['products'] ?? [];

    try {
        // Create an invoice for the selected customer
        $invoice = $stripe->invoices->create([
            'customer' => $customer_id
        ]);

        // Add selected products to the invoice as line items
        foreach ($selected_products as $product_id) {
            $product = $stripe->products->retrieve($product_id);
            $stripe->invoiceItems->create([
                'customer' => $customer_id,
                'price' => $product->default_price,
                'invoice' => $invoice->id
            ]);
        }

        // Finalize the invoice
        $stripe->invoices->finalizeInvoice($invoice->id);

        // Retrieve the finalized invoice
        $invoice = $stripe->invoices->retrieve($invoice->id);

        // Redirect with success message and invoice links
        header('Location: generate-invoice.php?success=1&invoice_pdf=' . urlencode($invoice->invoice_pdf) . '&hosted_invoice_url=' . urlencode($invoice->hosted_invoice_url));
        exit;
    } catch (Exception $e) {
        // Redirect with error message
        header('Location: generate-invoice.php?error=' . urlencode($e->getMessage()));
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Invoice</title>
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

        .success, .error {
            margin: 20px auto;
            padding: 10px;
            max-width: 600px;
            border-radius: 5px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <h1>Generate Invoice</h1>
    <a href="index.php">&larr; Back to Home</a>

    <!-- Display success or error messages -->
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="success">
            <p>Invoice created successfully!</p>
            <p><a href="<?php echo htmlspecialchars($_GET['invoice_pdf']); ?>" target="_blank">Download Invoice PDF</a></p>
            <p><a href="<?php echo htmlspecialchars($_GET['hosted_invoice_url']); ?>" target="_blank">View Payment Page</a></p>
        </div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="error">
            <p>Error: <?php echo htmlspecialchars($_GET['error']); ?></p>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <!-- Customer Dropdown -->
        <div>
            <label for="customer_id">Select Customer:</label>
            <select id="customer_id" name="customer_id" required>
                <option value="" disabled selected>-- Select a Customer --</option>
                <?php foreach ($customers as $customer): ?>
                    <option value="<?php echo htmlspecialchars($customer->id); ?>">
                        <?php echo htmlspecialchars($customer->name); ?> (<?php echo htmlspecialchars($customer->email); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

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
        <button type="submit">Generate Invoice</button>
    </form>
</body>
</html>
