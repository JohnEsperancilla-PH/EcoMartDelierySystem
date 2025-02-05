<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve personal information from POST
    $customer_name = trim($_POST['customer_name']);
    $email         = trim($_POST['email']);
    $contact       = trim($_POST['contact']);
    $address       = trim($_POST['address']);
    $zip_code      = trim($_POST['zip_code']);
    $payment_method= trim($_POST['payment_method']);
    $total = 0;
    
    // Connect to the database
    $conn = new mysqli("localhost", "root", "", "pos_db");
    if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
    }
    
    // Recalculate total from the session cart
    if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
        foreach ($_SESSION['cart'] as $productId => $quantity) {
           $sql = "SELECT * FROM products WHERE id = " . intval($productId);
           $res = $conn->query($sql);
           if ($res && $res->num_rows > 0) {
              $product = $res->fetch_assoc();
              $total += $product['price'] * $quantity;
           }
        }
    }
    
    // Insert the order using a prepared statement.
    $stmt = $conn->prepare("INSERT INTO orders (customer_name, email, contact, address, zip_code, payment_method, total, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    // Bind the parameters
    $stmt->bind_param("ssssssd", $customer_name, $email, $contact, $address, $zip_code, $payment_method, $total);
    
    // Execute the statement
    $stmt->execute();
    
    // Clear the cart after processing the order
    $_SESSION['cart'] = array();
    
    echo "<div class='container mt-5'>";
    echo "<h2>Order processed successfully.</h2>";
    echo "<p>Your Order ID is: " . $stmt->insert_id . "</p>";
    
    // Summary of checkout details
    echo "<h3>Checkout Summary:</h3>";
    echo "<p><strong>Name:</strong> " . htmlspecialchars($customer_name) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
    echo "<p><strong>Contact:</strong> " . htmlspecialchars($contact) . "</p>";
    echo "<p><strong>Address:</strong> " . htmlspecialchars($address) . "</p>";
    echo "<p><strong>Zip Code:</strong> " . htmlspecialchars($zip_code) . "</p>";
    echo "<p><strong>Payment Method:</strong> " . htmlspecialchars($payment_method) . "</p>";
    echo "<p><strong>Total Amount:</strong> $" . number_format($total, 2) . "</p>";
    
    echo "<a href='index.php' class='btn btn-primary'>Back to Products</a>";
    echo "</div>";
    
    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit;
}
?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
