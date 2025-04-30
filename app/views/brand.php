<?php 
$pageTitle = htmlspecialchars($brand['name']) . ' - STR Works';
$custom_css = 'brand.css';
require_once ROOT_PATH . '/app/views/partials/header.php'; 
?>

<!-- Products Section -->
<div class="product-section">
    <div class="container">
        <h2 class="section-title">Products</h2>
        
        <!-- Modern Filter Section -->
        <div class="filter-container mb-4">
            <div class="filter-header" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="true">
                <h3><i class="fas fa-sliders-h me-2"></i>Filter Products</h3>
                <span class="filter-toggle"><i class="fas fa-chevron-down"></i></span>
            </div>
            
            <div class="collapse show" id="filterCollapse">
                <div class="filter-body">
                    <form action="brand.php" method="get" id="filter-form">
                        <input type="hidden" name="id" value="<?php echo $brandId; ?>">
                        
                        <div class="filter-grid">
                            <div class="filter-group">
                                <label for="category_id" class="filter-label">Category</label>
                                <div class="custom-select-wrapper">
                                    <select class="custom-select" id="category_id" name="category_id">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" <?php echo $categoryId == $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="filter-group">
                                <label for="make_id" class="filter-label">Vehicle Make</label>
                                <div class="custom-select-wrapper">
                                    <select class="custom-select" id="make_id" name="make_id">
                                        <option value="">All Makes</option>
                                        <?php foreach ($makes as $make): ?>
                                            <option value="<?php echo $make['id']; ?>" <?php echo $makeId == $make['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($make['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="filter-group">
                                <label for="model_id" class="filter-label">Vehicle Model</label>
                                <div class="custom-select-wrapper">
                                    <select class="custom-select" id="model_id" name="model_id" <?php echo empty($models) && empty($modelId) ? 'disabled' : ''; ?>>
                                        <option value="">All Models</option>
                                        <?php foreach ($models as $model): ?>
                                            <option value="<?php echo $model['id']; ?>" <?php echo $modelId == $model['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($model['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="filter-group">
                                <label for="series_id" class="filter-label">Vehicle Series</label>
                                <div class="custom-select-wrapper">
                                    <select class="custom-select" id="series_id" name="series_id" <?php echo empty($series) && empty($seriesId) ? 'disabled' : ''; ?>>
                                        <option value="">All Series</option>
                                        <?php foreach ($series as $seriesItem): ?>
                                            <option value="<?php echo $seriesItem['id']; ?>" <?php echo $seriesId == $seriesItem['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($seriesItem['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="btn-apply">
                                <i class="fas fa-check me-2"></i>Apply Filters
                            </button>
                            <a href="brand.php?id=<?php echo $brandId; ?>" class="btn-reset">
                                <i class="fas fa-undo me-2"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Active Filters Display -->
        <?php if ($categoryId > 0 || $makeId > 0 || $modelId > 0 || $seriesId > 0): ?>
        <div class="active-filters mb-4">
            <p><i class="fas fa-tag me-2"></i>Active Filters:</p>
            <div class="filter-tags">
                <?php if ($categoryId > 0): 
                    $categoryName = '';
                    foreach ($categories as $cat) {
                        if ($cat['id'] == $categoryId) {
                            $categoryName = $cat['name'];
                            break;
                        }
                    }
                ?>
                <span class="filter-tag">
                    <span class="filter-label">Category:</span> <?php echo htmlspecialchars($categoryName); ?>
                    <a href="<?php echo removeQueryParam($_SERVER['REQUEST_URI'], 'category_id'); ?>" class="remove-filter">×</a>
                </span>
                <?php endif; ?>
                
                <?php if ($makeId > 0): 
                    $makeName = '';
                    foreach ($makes as $mk) {
                        if ($mk['id'] == $makeId) {
                            $makeName = $mk['name'];
                            break;
                        }
                    }
                ?>
                <span class="filter-tag">
                    <span class="filter-label">Make:</span> <?php echo htmlspecialchars($makeName); ?>
                    <a href="<?php echo removeQueryParam($_SERVER['REQUEST_URI'], 'make_id'); ?>" class="remove-filter">×</a>
                </span>
                <?php endif; ?>
                
                <?php if ($modelId > 0 && !empty($models)): 
                    $modelName = '';
                    foreach ($models as $mdl) {
                        if ($mdl['id'] == $modelId) {
                            $modelName = $mdl['name'];
                            break;
                        }
                    }
                ?>
                <span class="filter-tag">
                    <span class="filter-label">Model:</span> <?php echo htmlspecialchars($modelName); ?>
                    <a href="<?php echo removeQueryParam($_SERVER['REQUEST_URI'], 'model_id'); ?>" class="remove-filter">×</a>
                </span>
                <?php endif; ?>
                
                <?php if ($seriesId > 0 && !empty($series)): 
                    $seriesName = '';
                    foreach ($series as $s) {
                        if ($s['id'] == $seriesId) {
                            $seriesName = $s['name'];
                            break;
                        }
                    }
                ?>
                <span class="filter-tag">
                    <span class="filter-label">Series:</span> <?php echo htmlspecialchars($seriesName); ?>
                    <a href="<?php echo removeQueryParam($_SERVER['REQUEST_URI'], 'series_id'); ?>" class="remove-filter">×</a>
                </span>
                <?php endif; ?>
                
                <a href="brand.php?id=<?php echo $brandId; ?>" class="clear-all-filters">Clear All</a>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Products Count -->
        <div class="products-count mb-4">
            <p><i class="fas fa-boxes me-2"></i><?php echo count($products); ?> products found</p>
        </div>
        
        <!-- Grid Layout like shop page -->
        <div class="grid-container">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <a href="product.php?id=<?php echo $product['id']; ?>">
                            <div class="product-image-container">
                                <img src="<?php echo !empty($product['primary_image']) ? htmlspecialchars($product['primary_image']) : 'assets/img/product-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                            </div>
                            <div class="product-info">
                                <h5 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                                <div class="product-price">$<?php echo number_format($product['amount'], 2); ?></div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-products">
                    <p>No products available for this brand with the selected filters.</p>
                    <?php if (isset($vehicleFilterApplied) && $vehicleFilterApplied): ?>
                        <div class="notification-alert">
                            <i class="fas fa-bell"></i>
                            <p>We've notified our team about your search. We'll consider adding products for this vehicle in the future!</p>
                        </div>
                    <?php endif; ?>
                    <a href="brand.php?id=<?php echo $brandId; ?>" class="btn btn-outline-primary mt-3">Clear Filters</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Back to Shop Button -->
        <div class="text-center mt-5">
            <a href="shop.php" class="back-to-shop">
                <i class="fas fa-arrow-left me-2"></i> Back to Shop
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const makeSelect = document.getElementById('make_id');
        const modelSelect = document.getElementById('model_id');
        const seriesSelect = document.getElementById('series_id');
        const filterHeader = document.querySelector('.filter-header');
        const filterToggle = document.querySelector('.filter-toggle');
        
        // Handle collapsible filter section
        if (filterHeader) {
            filterHeader.addEventListener('click', function() {
                const filterCollapse = document.getElementById('filterCollapse');
                const isExpanded = filterHeader.getAttribute('aria-expanded') === 'true';
                
                if (isExpanded) {
                    filterCollapse.classList.remove('show');
                    filterHeader.setAttribute('aria-expanded', 'false');
                } else {
                    filterCollapse.classList.add('show');
                    filterHeader.setAttribute('aria-expanded', 'true');
                }
            });
        }
        
        // Function to fetch models for a selected make
        function fetchModels(makeId) {
            if (!makeId) {
                updateSelect(modelSelect, [], 'All Models', true);
                updateSelect(seriesSelect, [], 'All Series', true);
                return;
            }
            
            // Show loading state
            updateSelect(modelSelect, [], 'Loading models...', true);
            
            // Fetch models using AJAX
            fetch('api/get_models.php?make_id=' + makeId)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        updateSelect(modelSelect, data, 'All Models', false);
                    } else {
                        updateSelect(modelSelect, [], 'No models available', true);
                    }
                })
                .catch(error => {
                    console.error('Error fetching models:', error);
                    updateSelect(modelSelect, [], 'Error loading models', true);
                });
        }
        
        // Function to fetch series for a selected model
        function fetchSeries(modelId) {
            if (!modelId) {
                updateSelect(seriesSelect, [], 'All Series', true);
                return;
            }
            
            // Show loading state
            updateSelect(seriesSelect, [], 'Loading series...', true);
            
            // Fetch series using AJAX
            fetch('api/get_series.php?model_id=' + modelId)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        updateSelect(seriesSelect, data, 'All Series', false);
                    } else {
                        updateSelect(seriesSelect, [], 'No series available', true);
                    }
                })
                .catch(error => {
                    console.error('Error fetching series:', error);
                    updateSelect(seriesSelect, [], 'Error loading series', true);
                });
        }
        
        // Helper function to update select options
        function updateSelect(selectElement, data, defaultOptionText, isDisabled) {
            selectElement.innerHTML = `<option value="">${defaultOptionText}</option>`;
            selectElement.disabled = isDisabled;
            
            if (data && data.length > 0) {
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    selectElement.appendChild(option);
                });
            }
        }
        
        // Event listeners
        if (makeSelect) {
            makeSelect.addEventListener('change', function() {
                fetchModels(this.value);
            });
        }
        
        if (modelSelect) {
            modelSelect.addEventListener('change', function() {
                fetchSeries(this.value);
            });
        }
        
        // Initialize dependent dropdowns
        if (makeSelect && makeSelect.value) {
            // If there's a selected value (e.g., from a previous filter)
            // Don't fetch again as the server already populated the options
        }
    });
</script>

<?php
/**
 * Helper function to remove a query parameter from a URL
 */
function removeQueryParam($url, $param) {
    $parsedUrl = parse_url($url);
    if (isset($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $queryParams);
        unset($queryParams[$param]);
        
        $newQuery = http_build_query($queryParams);
        
        $newUrl = $parsedUrl['path'];
        if (!empty($newQuery)) {
            $newUrl .= '?' . $newQuery;
        }
        
        return $newUrl;
    }
    
    return $url;
}
?>

