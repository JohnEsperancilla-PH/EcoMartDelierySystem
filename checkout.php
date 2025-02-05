<?php
session_start();
$total = 0;
$orderItems = [];

// Calculate order overview and total
if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    $conn = new mysqli("localhost", "root", "", "pos_db");
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }
    foreach ($_SESSION['cart'] as $productId => $quantity) {
       $sql = "SELECT * FROM products WHERE id = " . intval($productId);
       $res = $conn->query($sql);
       if ($res && $res->num_rows > 0) {
          $product = $res->fetch_assoc();
          $subtotal = $product['price'] * $quantity;
          $total += $subtotal;
          $orderItems[] = [
              'name' => $product['name'],
              'quantity' => $quantity,
              'price' => $product['price'],
              'subtotal' => $subtotal
          ];
       }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Checkout - POS System</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
      body { 
          padding-top: 70px; 
          padding-bottom: 50px; /* Added bottom padding for the page */
      }
      /* Added padding to the QR code section */
      #qr_section {
         display: none;
         padding-top: 20px;
      }
      .qr-placeholder {
          width: 200px;
          height: 200px;
          background-color: #ddd;
          display: flex;
          align-items: center;
          justify-content: center;
          color: #555;
          font-weight: bold;
          margin-top: 10px;
      }
      /* Custom class for additional spacing below the Back to Products button */
      .btn-back {
          margin-bottom: 30px;
      }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="#">POS Checkout</a>
</nav>
<div class="container">
   <h2 class="mt-4">Checkout</h2>
   <div class="row">
      <!-- Customer Information Form -->
      <div class="col-md-6">
         <form action="process_checkout.php" method="post">
             <div class="form-group">
                 <label for="email">Email</label>
                 <input type="email" class="form-control" id="email" name="email" required>
             </div>
             <div class="form-group">
                 <label for="customer_name">Customer Name</label>
                 <input type="name" class="form-control" id="customer_name" name="customer_name" required>
             </div>
             <div class="form-group">
                 <label for="contact">Contact Number</label>
                 <input type="text" class="form-control" id="contact" name="contact" required>
             </div>
             <div class="form-group">
                 <label for="address">Address</label>
                 <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
             </div>
             <div class="form-group">
                 <label for="zip_code">Zip Code</label>
                 <input type="text" class="form-control" id="zip_code" name="zip_code" required>
             </div>
             <div class="form-group">
                 <label for="payment_method">Payment Method</label>
                 <select class="form-control" id="payment_method" name="payment_method" required>
                     <option value="cash">Cash on Delivery</option>
                     <option value="gcash">G-Cash</option>
                 </select>
             </div>
             
             <!-- QR Code Placeholder for G-Cash -->
             <div id="qr_section">
                 <p>Scan the QR code using your G-Cash app:</p>
                 <div class="qr-placeholder">QR Code Placeholder</div>
             </div>
             
             <button type="submit" class="btn btn-success mt-3">Process Order</button>
         </form>
         <!-- Added custom class for spacing below the Back to Products button -->
         <a href="index.php" class="btn btn-secondary btn-back mt-3">Back to Products</a>
      </div>
      
      <!-- Order Overview -->
      <div class="col-md-6">
         <h4>Order Overview</h4>
         <?php if (!empty($orderItems)): ?>
         <table class="table">
            <thead>
               <tr>
                  <th>Product</th>
                  <th>Qty</th>
                  <th>Price</th>
                  <th>Subtotal</th>
               </tr>
            </thead>
            <tbody>
               <?php foreach ($orderItems as $item): ?>
               <tr>
                  <td><?php echo htmlspecialchars($item['name']); ?></td>
                  <td><?php echo $item['quantity']; ?></td>
                  <td>$<?php echo number_format($item['price'], 2); ?></td>
                  <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
               </tr>
               <?php endforeach; ?>
               <tr>
                  <td colspan="3"><strong>Total</strong></td>
                  <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
               </tr>
            </tbody>
         </table>
         <?php else: ?>
         <p>No items in cart.</p>
         <?php endif; ?>
      </div>
      <div id="qr_section" style="display: none;">
         <form action="process_payment.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
               <label for="reference_number">GCash Reference Number:</label>
               <input type="text" class="form-control" id="reference_number" name="reference_number" required>
            </div>
            <div class="form-group">
               <label for="receipt_screenshot">Upload Receipt Screenshot:</label>
               <input type="file" class="form-control-file" id="receipt_screenshot" name="receipt_screenshot" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
         </form>
      </div>
   </div>
</div>
<!-- jQuery and Bootstrap scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle QR code display based on payment method selection
    $(document).ready(function(){
        $('#payment_method').change(function(){
            if ($(this).val() === 'gcash') {
                $('#qr_section').slideDown();
            } else {
                $('#qr_section').slideUp();
            }
        }).trigger('change');
    });
</script>
</body>
</html>
