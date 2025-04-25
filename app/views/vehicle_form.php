<?php 
// Set page title
$pageTitle = 'Select Your Vehicle - STR Works';
$custom_css = 'vehicle_form.css';

// Include header
require_once __DIR__ . '/partials/header.php'; 
?>

<div class="container">
    <div class="vehicle-form-container">
        <div class="form-header">
            <h1>Select Your Vehicle</h1>
            <p>Choose your vehicle details below to find compatible products or register your vehicle with us.</p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!isLoggedIn()): ?>
        <div class="notification-alert">
            <i class="fas fa-info-circle"></i>
            <p>You must be logged in to submit this form. You can browse vehicle options, but submission requires a login.</p>
        </div>
        <?php endif; ?>

        <div id="vehicle-form">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="make">Make</label>
                    <select id="make" name="make" required class="form-control">
                        <option value="">Select Make</option>
                        <?php foreach ($makes as $make): ?>
                            <option value="<?php echo $make['id']; ?>" <?php echo (isset($selectedMake) && $selectedMake == $make['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($make['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="model">Model</label>
                    <select id="model" name="model" required class="form-control" <?php echo empty($models) ? 'disabled' : ''; ?>>
                        <option value="">Select Model</option>
                        <?php foreach ($models as $model): ?>
                            <option value="<?php echo $model['id']; ?>" <?php echo (isset($selectedModel) && $selectedModel == $model['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($model['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="series">Series</label>
                    <select id="series" name="series" required class="form-control" <?php echo empty($series) ? 'disabled' : ''; ?>>
                        <option value="">Select Series</option>
                        <?php foreach ($series as $seriesItem): ?>
                            <option value="<?php echo $seriesItem['id']; ?>" <?php echo (isset($selectedSeries) && $selectedSeries == $seriesItem['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($seriesItem['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <?php if (isLoggedIn()): ?>
                <div class="info-display">
                    <p><i class="fas fa-user-circle"></i> Your Contact Information</p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($_SESSION['phone'] ?? 'Not provided'); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email'] ?? 'Not provided'); ?></p>
                    <input type="hidden" name="phone" value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>">
                </div>
                <?php endif; ?>
                
                <div class="form-group text-center">
                    <button type="submit" name="submit_vehicle" class="btn btn-primary" <?php echo !isLoggedIn() ? 'disabled' : ''; ?>>
                        <?php echo !isLoggedIn() ? 'Login Required to Submit' : 'Submit Vehicle Information'; ?>
                    </button>
                    
                    <?php if (!isLoggedIn()): ?>
                    <div style="margin-top: 20px;">
                        <a href="index.php" class="btn" style="margin-right: 10px;">Login</a>
                        <a href="register.php" class="btn">Register</a>
                    </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const makeSelect = document.getElementById('make');
    const modelSelect = document.getElementById('model');
    const seriesSelect = document.getElementById('series');
    
    // Function to fetch models for a selected make
    function fetchModels(makeId) {
        if (!makeId) {
            modelSelect.innerHTML = '<option value="">Select Model</option>';
            modelSelect.disabled = true;
            seriesSelect.innerHTML = '<option value="">Select Series</option>';
            seriesSelect.disabled = true;
            return;
        }
        
        // Show loading indicator
        modelSelect.innerHTML = '<option value="">Loading models...</option>';
        modelSelect.disabled = true;
        modelSelect.classList.add('loading-select');
        
        // Fetch models using AJAX
        fetch('api/get_models.php?make_id=' + makeId)
            .then(response => response.json())
            .then(data => {
                let options = '<option value="">Select Model</option>';
                
                if (data && data.length > 0) {
                    data.forEach(model => {
                        options += `<option value="${model.id}">${model.name}</option>`;
                    });
                    modelSelect.disabled = false;
                } else {
                    options = '<option value="">No models available</option>';
                    // Add notification message
                    showNotification("No models found for this make. We'll consider adding more options in the future.");
                }
                
                modelSelect.innerHTML = options;
                modelSelect.classList.remove('loading-select');
            })
            .catch(error => {
                console.error('Error fetching models:', error);
                modelSelect.innerHTML = '<option value="">Error loading models</option>';
                modelSelect.classList.remove('loading-select');
            });
    }
    
    // Function to fetch series for a selected model
    function fetchSeries(modelId) {
        if (!modelId) {
            seriesSelect.innerHTML = '<option value="">Select Series</option>';
            seriesSelect.disabled = true;
            return;
        }
        
        // Show loading indicator
        seriesSelect.innerHTML = '<option value="">Loading series...</option>';
        seriesSelect.disabled = true;
        seriesSelect.classList.add('loading-select');
        
        // Fetch series using AJAX
        fetch('api/get_series.php?model_id=' + modelId)
            .then(response => response.json())
            .then(data => {
                let options = '<option value="">Select Series</option>';
                
                if (data && data.length > 0) {
                    data.forEach(series => {
                        options += `<option value="${series.id}">${series.name}</option>`;
                    });
                    seriesSelect.disabled = false;
                } else {
                    options = '<option value="">No series available</option>';
                    // Add notification message
                    showNotification("No series found for this model. We'll consider adding more options in the future.");
                }
                
                seriesSelect.innerHTML = options;
                seriesSelect.classList.remove('loading-select');
            })
            .catch(error => {
                console.error('Error fetching series:', error);
                seriesSelect.innerHTML = '<option value="">Error loading series</option>';
                seriesSelect.classList.remove('loading-select');
            });
    }
    
    // Function to show notification message
    function showNotification(message) {
        // Check if notification already exists and remove it
        const existingNotification = document.querySelector('.dynamic-notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'notification-alert dynamic-notification';
        notification.innerHTML = `
            <i class="fas fa-bell"></i>
            <p>${message}</p>
        `;
        
        // Insert after the series dropdown
        const formGroups = document.querySelectorAll('.form-group');
        formGroups[formGroups.length - 2].after(notification);
        
        // Animate the notification
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(10px)';
        notification.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateY(0)';
        }, 10);
    }
    
    // Make dropdown change event
    makeSelect.addEventListener('change', function() {
        const makeId = this.value;
        fetchModels(makeId);
    });
    
    // Model dropdown change event
    modelSelect.addEventListener('change', function() {
        const modelId = this.value;
        fetchSeries(modelId);
    });
    
    // If a make is already selected (on page load), fetch its models
    if (makeSelect.value) {
        fetchModels(makeSelect.value);
        
        // If a model is already selected, fetch its series
        if (modelSelect.options.length > 1 && modelSelect.value) {
            fetchSeries(modelSelect.value);
        }
    }
});
</script>
