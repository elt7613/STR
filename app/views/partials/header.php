<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define base URL if not defined
if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}

// Get the current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Get all brands for the dropdown menu
$headerBrands = getAllBrands();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo isset($page_title) ? $page_title : 'STR Works'; ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/webp" href="assets/img/STR-logo.webp">

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <!-- Tailwind CSS for cart compatibility -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <!-- Custom page styles if needed -->
    <?php if (isset($custom_css)): ?>
    <link rel="stylesheet" href="assets/css/<?php echo $custom_css; ?>">
    <?php endif; ?>
    
    <!-- JavaScript -->
    <script src="assets/js/header.js" defer></script>
    <script src="assets/js/cart.js" defer></script>
    
    <!-- No custom scripts needed - using pure CSS approach -->
</head>
<body style="background: #fff;">
    <!-- Navigation Bar -->
    <header class="header">
        <div class="container header-container">
            <div class="logo">
                <a href="https://strworks.netpy.in/">
                    <img src="assets/img/STR-logo.webp" alt="STRworks India" class="logo-img">
                </a>
            </div>
            
            <nav class="nav">
                <ul class="nav-list">
                    <li class="nav-item"><a href="https://strworks.netpy.in/" class="nav-link">HOME</a></li>
                    <li class="nav-item"><a href="https://strworks.netpy.in/about-us/" class="nav-link">ABOUT US</a></li>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link" data-toggle="dropdown">BRANDS <i class="fas fa-chevron-down dropdown-icon"></i></a>
                        <div class="dropdown-menu">
                            <a href="https://strworks.netpy.in/titan-suspension/" class="dropdown-item">
                                TITAN SUSPENSION
                            </a>
                            <a href="https://strworks.netpy.in/haltech/" class="dropdown-item">
                                HALTECH
                            </a>
                            <a href="https://strworks.netpy.in/nashin-brakes/" class="dropdown-item">
                                NASHIN BRAKES
                            </a>
                            <a href="https://strworks.netpy.in/ultimate9/" class="dropdown-item">
                                ULTIMATE9
                            </a>
                            <a href="https://strworks.netpy.in/offroad-animal/" class="dropdown-item">
                                OFFROAD ANIMAL
                            </a>
                            <a href="https://strworks.netpy.in/jmax-engineering/" class="dropdown-item">
                                JMAX ENGINEERING
                            </a>
                            <!-- <?php if (!empty($headerBrands)): ?>
                                <?php foreach ($headerBrands as $brand): ?>
                                    <a href="brand.php?id=<?php echo $brand['id']; ?>" class="dropdown-item">
                                        <?php echo strtoupper(htmlspecialchars($brand['name'])); ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="dropdown-item">NO BRANDS AVAILABLE</div>
                            <?php endif; ?> -->
                        </div>
                    </li>
                    <li class="nav-item"><a href="shop.php" class="nav-link">SHOP</a></li>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link" data-toggle="dropdown">COMMUNITY <i class="fas fa-chevron-down dropdown-icon"></i></a>
                        <div class="dropdown-menu">
                            <a href="https://strworks.netpy.in/dealers/" class="dropdown-item">DEALERS</a>
                            <a href="https://strworks.netpy.in/premium-membership/" class="dropdown-item">PREMIUM MEMBERSHIP</a>
                        </div>
                    </li>
                    
                    <!-- Mobile-only profile menu -->
                    <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown mobile-only-profile">
                        <a href="#" class="nav-link" data-toggle="dropdown">MY ACCOUNT <i class="fas fa-chevron-down dropdown-icon"></i></a>
                        <div class="dropdown-menu">
                            <?php if (isAdmin()): ?>
                                <a href="admin/dashboard.php" class="dropdown-item">DASHBOARD</a>
                            <?php endif; ?>
                            <a href="profile.php" class="dropdown-item">MY PROFILE</a>
                            <a href="logout.php" class="dropdown-item">LOGOUT</a>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <!-- Header Actions Section -->
            <div class="header-actions">
                <div class="cart" id="cartButton">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="cart-icon">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <span class="cart-count" id="cartCount"><?php echo getCartItemCount(); ?></span>
                </div>
                
                <?php if (isLoggedIn()): ?>
                    <div class="user-menu dropdown">
                        <a href="#" class="nav-link" data-toggle="dropdown">
                            <?php if (!empty($_SESSION['profile_image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/profile_images/' . $_SESSION['profile_image'])): ?>
                                <?php
                                // Fix for both local and hostinger environments
                                $img_path = 'uploads/profile_images/' . htmlspecialchars($_SESSION['profile_image']);
                                
                                // Make sure we have a valid path that works in both environments
                                if (defined('BASE_URL')) {
                                    $img_url = rtrim(BASE_URL, '/') . '/' . $img_path;
                                } else {
                                    $img_url = '/' . $img_path;
                                }
                                ?>
                                <img src="<?php echo $img_url; ?>" alt="Profile" class="user-profile-image">
                            <?php else: ?>
                                <i class="fas fa-user-circle"></i>
                            <?php endif; ?> 
                            <span class="username-text"><?php echo htmlspecialchars($_SESSION['username']); ?></span> 
                            <i class="fas fa-chevron-down dropdown-icon"></i>
                        </a>
                        <div class="dropdown-menu">
                            <?php if (isAdmin()): ?>
                                <a href="admin/dashboard.php" class="dropdown-item">DASHBOARD</a>
                            <?php endif; ?>
                            <a href="profile.php" class="dropdown-item">MY PROFILE</a>
                            <a href="logout.php" class="dropdown-item">LOGOUT</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="index.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="login-btn">Login</a>
                <?php endif; ?>
            </div>
            
            <button class="mobile-menu-btn" aria-label="Toggle navigation menu" aria-expanded="false">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>
    </header>
    
    <!-- Cart Dropdown -->
    <div id="cartDropdown" class="cart-dropdown h-300 mt-4 w-80 bg-black shadow-lg p-4">
        <div id="cartItems" class="text-white">
            <!-- Cart items will be dynamically inserted here -->
            <p class="empty-cart-message">Your cart is empty</p>
        </div>
        <div class="flex gap-5">
            <button class="mt-4 w-3/5 font-semi-bold bg-pink-500 text-white py-2 text-sm view-cart-btn" onclick="window.location.href='cart.php'">
                VIEW CART
            </button>
            <button class="mt-4 w-3/5 font-semi-bold bg-black text-white py-2 text-sm bold border-white border-2 hover:border-pink-500 hover:text-pink-500 transition checkout-btn" onclick="window.location.href='cart.php'">
                CHECKOUT
            </button>
        </div>
    </div>
    
    <!-- Main Content -->
    <main>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?> 