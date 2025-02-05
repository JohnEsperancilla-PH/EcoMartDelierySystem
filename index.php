<?php
session_start();

// Clear cart if requested
if (isset($_GET['clear']) && $_GET['clear'] == 1) {
    $_SESSION['cart'] = array();
    header("Location: index.php");
    exit;
}

// Initialize the cart session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Add product to cart if requested
if (isset($_GET['add'])) {
    $productId = intval($_GET['add']);
    // Increase quantity if already exists
    $_SESSION['cart'][$productId] = isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId] + 1 : 1;
    header("Location: index.php");
    exit;
}

// Connect to the database
$conn = new mysqli("localhost", "root", "", "pos_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get search and category filter from URL
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';

// Build SQL query with optional search and category filters
$sql = "SELECT * FROM products WHERE 1";
if ($search !== '') {
    $sql .= " AND name LIKE '%$search%'";
}
if ($category !== '' && $category !== 'All') {
    $sql .= " AND category = '$category'";
}

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Simple POS System</title>
  <!-- Use Bootstrap for a modern UI -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
      body { padding-top: 70px; }
      .placeholder-img {
          width: 100%;
          height: 150px;
          background-color: #ddd;
          display: flex;
          align-items: center;
          justify-content: center;
          color: #555;
          font-weight: bold;
      }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="#">POS System</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ml-auto">
          <li class="nav-item">
              <a class="nav-link" href="checkout.php">Checkout</a>
          </li>
          <li class="nav-item">
              <a class="nav-link" href="index.php?clear=1">Clear Cart</a>
          </li>
      </ul>
    </div>
</nav>
<div class="container-fluid">
   <div class="row">
      <!-- Static Category Sidebar -->
      <div class="col-md-2">
         <h5>Categories</h5>
         <div class="list-group">
             <a href="index.php" class="list-group-item list-group-item-action <?php echo ($category=='' || $category=='All') ? 'active' : ''; ?>">All</a>
             <a href="index.php?category=Beverages" class="list-group-item list-group-item-action <?php echo ($category=='Beverages') ? 'active' : ''; ?>">Beverages</a>
             <a href="index.php?category=Food" class="list-group-item list-group-item-action <?php echo ($category=='Food') ? 'active' : ''; ?>">Food</a>
             <a href="index.php?category=Desserts" class="list-group-item list-group-item-action <?php echo ($category=='Desserts') ? 'active' : ''; ?>">Desserts</a>
             <a href="index.php?category=Snacks" class="list-group-item list-group-item-action <?php echo ($category=='Snacks') ? 'active' : ''; ?>">Snacks</a>
         </div>
      </div>
      
      <!-- Main Content: Product Listing and Search -->
      <div class="col-md-7">
         <h2>Products</h2>
         <!-- Search Functionality -->
         <form method="GET" action="index.php" class="mb-3">
            <div class="input-group">
                 <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                 <?php
                 // Retain category filter on search
                 if ($category !== '') {
                     echo '<input type="hidden" name="category" value="' . htmlspecialchars($category) . '">';
                 }
                 ?>
                 <div class="input-group-append">
                      <button class="btn btn-outline-secondary" type="submit">Search</button>
                 </div>
            </div>
         </form>
         <div class="row">
         <?php if ($result && $result->num_rows > 0): ?>
             <?php while ($row = $result->fetch_assoc()): ?>
             <div class="col-md-4 mb-4">
                <div class="card">
                    <!-- Image Placeholder -->
                    <div class="placeholder-img">
                         Image
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                        <p class="card-text">$<?php echo number_format($row['price'], 2); ?></p>
                        <a href="index.php?add=<?php echo $row['id']; ?>" class="btn btn-primary">Add to Cart</a>
                    </div>
                </div>
             </div>
             <?php endwhile; ?>
         <?php else: ?>
             <div class="col-12">
                <p>No products found.</p>
             </div>
         <?php endif; ?>
         </div>
      </div>
      
      <!-- Cart Sidebar -->
      <div class="col-md-3">
         <h2>Cart</h2>
         <ul class="list-group">
            <?php
            if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
                $total = 0;
                foreach ($_SESSION['cart'] as $productId => $quantity) {
                    $sql = "SELECT * FROM products WHERE id = " . intval($productId);
                    $res = $conn->query($sql);
                    if ($res && $res->num_rows > 0) {
                        $product = $res->fetch_assoc();
                        $subtotal = $product['price'] * $quantity;
                        $total += $subtotal;
                        echo '<li class="list-group-item">';
                        echo htmlspecialchars($product['name']) . " x " . $quantity . " = $" . number_format($subtotal, 2);
                        echo '</li>';
                    }
                }
                echo '<li class="list-group-item active">Total: $' . number_format($total, 2) . '</li>';
            } else {
                echo '<li class="list-group-item">Cart is empty.</li>';
            }
            ?>
         </ul>
         <a href="checkout.php" class="btn btn-success btn-block mt-3">Checkout</a>
         <a href="index.php?clear=1" class="btn btn-danger btn-block mt-1">Clear Cart</a>
      </div>
   </div>
</div>
<!-- Bootstrap scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
