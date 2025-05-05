<?php
/**
 * Vehicle Management Admin Page
 */

// Include initialization script
require_once __DIR__ . '/../../includes/init.php';

// Check if user is admin, redirect if not
if (!isAdmin()) {
    header('Location: ../index.php');
    exit;
}

// Process form submissions
$error = '';
$success = '';

// Handle Make CRUD operations
if (isset($_POST['add_make'])) {
    $makeName = trim($_POST['make_name']);
    if (empty($makeName)) {
        $error = 'Make name cannot be empty';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO vehicle_makes (name) VALUES (?)");
            $stmt->execute([$makeName]);
            $success = 'Vehicle make added successfully';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $error = 'This make already exists';
            } else {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
} elseif (isset($_POST['edit_make'])) {
    $makeId = (int)$_POST['make_id'];
    $makeName = trim($_POST['make_name']);
    
    if (empty($makeName)) {
        $error = 'Make name cannot be empty';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE vehicle_makes SET name = ? WHERE id = ?");
            $stmt->execute([$makeName, $makeId]);
            $success = 'Vehicle make updated successfully';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $error = 'This make already exists';
            } else {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
} elseif (isset($_POST['delete_make'])) {
    $makeId = (int)$_POST['make_id'];
    
    try {
        // Check if there are dependencies
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicle_models WHERE make_id = ?");
        $stmt->execute([$makeId]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $error = 'Cannot delete make with associated models. Delete models first.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM vehicle_makes WHERE id = ?");
            $stmt->execute([$makeId]);
            $success = 'Vehicle make deleted successfully';
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Handle Model CRUD operations
if (isset($_POST['add_model'])) {
    $makeId = (int)$_POST['make_id'];
    $modelName = trim($_POST['model_name']);
    
    if (empty($modelName)) {
        $error = 'Model name cannot be empty';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO vehicle_models (make_id, name) VALUES (?, ?)");
            $stmt->execute([$makeId, $modelName]);
            $success = 'Vehicle model added successfully';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $error = 'This model already exists for the selected make';
            } else {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
} elseif (isset($_POST['edit_model'])) {
    $modelId = (int)$_POST['model_id'];
    $makeId = (int)$_POST['make_id'];
    $modelName = trim($_POST['model_name']);
    
    if (empty($modelName)) {
        $error = 'Model name cannot be empty';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE vehicle_models SET name = ?, make_id = ? WHERE id = ?");
            $stmt->execute([$modelName, $makeId, $modelId]);
            $success = 'Vehicle model updated successfully';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $error = 'This model already exists for the selected make';
            } else {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
} elseif (isset($_POST['delete_model'])) {
    $modelId = (int)$_POST['model_id'];
    
    try {
        // Check if there are dependencies
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicle_series WHERE model_id = ?");
        $stmt->execute([$modelId]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $error = 'Cannot delete model with associated series. Delete series first.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM vehicle_models WHERE id = ?");
            $stmt->execute([$modelId]);
            $success = 'Vehicle model deleted successfully';
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Handle Series CRUD operations
if (isset($_POST['add_series'])) {
    $modelId = (int)$_POST['model_id'];
    $seriesName = trim($_POST['series_name']);
    
    if (empty($seriesName)) {
        $error = 'Series name cannot be empty';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO vehicle_series (model_id, name) VALUES (?, ?)");
            $stmt->execute([$modelId, $seriesName]);
            $success = 'Vehicle series added successfully';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $error = 'This series already exists for the selected model';
            } else {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
} elseif (isset($_POST['edit_series'])) {
    $seriesId = (int)$_POST['series_id'];
    $modelId = (int)$_POST['model_id'];
    $seriesName = trim($_POST['series_name']);
    
    if (empty($seriesName)) {
        $error = 'Series name cannot be empty';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE vehicle_series SET name = ?, model_id = ? WHERE id = ?");
            $stmt->execute([$seriesName, $modelId, $seriesId]);
            $success = 'Vehicle series updated successfully';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $error = 'This series already exists for the selected model';
            } else {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
} elseif (isset($_POST['delete_series'])) {
    $seriesId = (int)$_POST['series_id'];
    
    try {
        // Check if there are dependencies in vehicle_submissions
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicle_submissions WHERE series_id = ?");
        $stmt->execute([$seriesId]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $error = 'Cannot delete series with associated submissions. Delete submissions first.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM vehicle_series WHERE id = ?");
            $stmt->execute([$seriesId]);
            $success = 'Vehicle series deleted successfully';
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Handle Device CRUD operations
if (isset($_POST['add_device'])) {
    $seriesId = (int)$_POST['series_id'];
    $deviceName = trim($_POST['device_name']);
    $deviceDescription = trim($_POST['device_description'] ?? '');
    
    if (empty($deviceName)) {
        $error = 'Device name cannot be empty';
    } else {
        try {
            $result = addVehicleDevice($seriesId, $deviceName, $deviceDescription);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
} elseif (isset($_POST['edit_device'])) {
    $deviceId = (int)$_POST['device_id'];
    $seriesId = (int)$_POST['series_id'];
    $deviceName = trim($_POST['device_name']);
    $deviceDescription = trim($_POST['device_description'] ?? '');
    
    if (empty($deviceName)) {
        $error = 'Device name cannot be empty';
    } else {
        try {
            $result = updateVehicleDevice($deviceId, $seriesId, $deviceName, $deviceDescription);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
} elseif (isset($_POST['delete_device'])) {
    $deviceId = (int)$_POST['device_id'];
    
    try {
        $result = deleteVehicleDevice($deviceId);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Get all makes for display
$makes = getAllVehicleMakes();

// Get specific make details if editing
$editingMake = null;
if (isset($_GET['edit_make']) && !empty($_GET['edit_make'])) {
    $makeId = (int)$_GET['edit_make'];
    $stmt = $pdo->prepare("SELECT * FROM vehicle_makes WHERE id = ?");
    $stmt->execute([$makeId]);
    $editingMake = $stmt->fetch();
}

// Get models for a make if viewing
$viewingMakeId = null;
$models = [];
if (isset($_GET['view_models']) && !empty($_GET['view_models'])) {
    $viewingMakeId = (int)$_GET['view_models'];
    $models = getVehicleModelsByMake($viewingMakeId);
    
    // Get make name
    $stmt = $pdo->prepare("SELECT name FROM vehicle_makes WHERE id = ?");
    $stmt->execute([$viewingMakeId]);
    $viewingMakeName = $stmt->fetchColumn();
}

// Get specific model details if editing
$editingModel = null;
if (isset($_GET['edit_model']) && !empty($_GET['edit_model'])) {
    $modelId = (int)$_GET['edit_model'];
    $stmt = $pdo->prepare("SELECT * FROM vehicle_models WHERE id = ?");
    $stmt->execute([$modelId]);
    $editingModel = $stmt->fetch();
}

// Get series for a model if viewing
$viewingModelId = null;
$series = [];
if (isset($_GET['view_series']) && !empty($_GET['view_series'])) {
    $viewingModelId = (int)$_GET['view_series'];
    $series = getVehicleSeriesByModel($viewingModelId);
    
    // Get model name and make details
    $stmt = $pdo->prepare("SELECT vm.name as model_name, vmk.id as make_id, vmk.name as make_name 
                           FROM vehicle_models vm 
                           JOIN vehicle_makes vmk ON vm.make_id = vmk.id 
                           WHERE vm.id = ?");
    $stmt->execute([$viewingModelId]);
    $viewingModelDetails = $stmt->fetch();
}

// Get specific series details if editing
$editingSeries = null;
if (isset($_GET['edit_series']) && !empty($_GET['edit_series'])) {
    $seriesId = (int)$_GET['edit_series'];
    $stmt = $pdo->prepare("SELECT * FROM vehicle_series WHERE id = ?");
    $stmt->execute([$seriesId]);
    $editingSeries = $stmt->fetch();
    
    if ($editingSeries) {
        // Get model's make for dropdown
        $stmt = $pdo->prepare("SELECT vm.make_id FROM vehicle_models vm WHERE vm.id = ?");
        $stmt->execute([$editingSeries['model_id']]);
        $seriesModelMakeId = $stmt->fetchColumn();
    }
}

// After the code getting specific series details if editing
// Add code to get devices for a series
$viewingSeriesId = null;
$devices = [];
if (isset($_GET['view_devices']) && !empty($_GET['view_devices'])) {
    $viewingSeriesId = (int)$_GET['view_devices'];
    $devices = getVehicleDevicesBySeries($viewingSeriesId);
    
    // Get series name and model details
    $stmt = $pdo->prepare("SELECT vs.name as series_name, vm.id as model_id, vm.name as model_name, vmk.id as make_id, vmk.name as make_name 
                        FROM vehicle_series vs 
                        JOIN vehicle_models vm ON vs.model_id = vm.id 
                        JOIN vehicle_makes vmk ON vm.make_id = vmk.id 
                        WHERE vs.id = ?");
    $stmt->execute([$viewingSeriesId]);
    $viewingSeriesDetails = $stmt->fetch();
}

// Get specific device details if editing
$editingDevice = null;
if (isset($_GET['edit_device']) && !empty($_GET['edit_device'])) {
    $deviceId = (int)$_GET['edit_device'];
    $editingDevice = getVehicleDeviceById($deviceId);
    
    if ($editingDevice) {
        // Get series details for this device
        $stmt = $pdo->prepare("SELECT vs.id as series_id, vs.name as series_name, vm.id as model_id, vm.name as model_name, vmk.id as make_id, vmk.name as make_name 
                            FROM vehicle_devices vd 
                            JOIN vehicle_series vs ON vd.series_id = vs.id 
                            JOIN vehicle_models vm ON vs.model_id = vm.id 
                            JOIN vehicle_makes vmk ON vm.make_id = vmk.id 
                            WHERE vd.id = ?");
        $stmt->execute([$deviceId]);
        $editingDeviceDetails = $stmt->fetch();
    }
}

// Page title
$pageTitle = 'Manage Vehicles';

// Include admin header
require_once ROOT_PATH . '/app/views/admin/partials/header.php';
?>

<div class="admin-header">
    <h1>Manage Vehicles</h1>
    <div>
        <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        <button class="close-alert"><i class="fas fa-times"></i></button>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        <button class="close-alert"><i class="fas fa-times"></i></button>
    </div>
<?php endif; ?>

<?php if (isset($viewingModelDetails)): ?>
    <!-- Breadcrumb for series view -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="manage_vehicles.php">All Makes</a></li>
                <li class="breadcrumb-item"><a href="manage_vehicles.php?view_models=<?php echo $viewingModelDetails['make_id']; ?>">
                    <?php echo htmlspecialchars($viewingModelDetails['make_name']); ?> Models
                </a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($viewingModelDetails['model_name']); ?> Series</li>
            </ol>
        </nav>
    </div>
<?php elseif (isset($viewingMakeName)): ?>
    <!-- Breadcrumb for model view -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="manage_vehicles.php">All Makes</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($viewingMakeName); ?> Models</li>
            </ol>
        </nav>
    </div>
<?php endif; ?>

<?php if (isset($viewingModelId)): ?>
    <!-- Series Management Section -->
    <div class="mb-4">
        <div class="form-section">
            <h3>Manage Series for <?php echo htmlspecialchars($viewingModelDetails['model_name']); ?></h3>
            
            <?php if ($editingSeries): ?>
                <h4 class="mb-3">Edit Series</h4>
                <form method="post" action="manage_vehicles.php?view_series=<?php echo $viewingModelId; ?>">
                    <input type="hidden" name="series_id" value="<?php echo $editingSeries['id']; ?>">
                    <input type="hidden" name="model_id" value="<?php echo $viewingModelId; ?>">
                    <div class="mb-3">
                        <label for="series_name" class="form-label">Series Name:</label>
                        <input type="text" class="form-control" id="series_name" name="series_name" value="<?php echo htmlspecialchars($editingSeries['name']); ?>" required>
                    </div>
                    <div>
                        <button type="submit" name="edit_series" class="btn btn-primary">Update Series</button>
                        <a href="manage_vehicles.php?view_series=<?php echo $viewingModelId; ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>
                </form>
            <?php else: ?>
                <h4 class="mb-3">Add New Series</h4>
                <form method="post" action="manage_vehicles.php?view_series=<?php echo $viewingModelId; ?>">
                    <input type="hidden" name="model_id" value="<?php echo $viewingModelId; ?>">
                    <div class="mb-3">
                        <label for="series_name" class="form-label">Series Name:</label>
                        <input type="text" class="form-control" id="series_name" name="series_name" required>
                    </div>
                    <div>
                        <button type="submit" name="add_series" class="btn btn-primary">Add Series</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="form-section">
            <h3>Series List</h3>
            <?php if (empty($series)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No series found for this model.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($series as $s): ?>
                                <tr>
                                    <td><?php echo $s['id']; ?></td>
                                    <td><?php echo htmlspecialchars($s['name']); ?></td>
                                    <td>
                                        <a href="manage_vehicles.php?view_series=<?php echo $viewingModelId; ?>&edit_series=<?php echo $s['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="manage_vehicles.php?view_devices=<?php echo $s['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-microchip"></i> Manage Devices
                                        </a>
                                        <form method="post" action="manage_vehicles.php?view_series=<?php echo $viewingModelId; ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this series?');">
                                            <input type="hidden" name="series_id" value="<?php echo $s['id']; ?>">
                                            <button type="submit" name="delete_series" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php elseif (isset($viewingSeriesId)): ?>
    <!-- Device Management Section -->
    <div class="mb-4">
        <div class="form-section">
            <h3>Manage Devices for <?php echo htmlspecialchars($viewingSeriesDetails['series_name']); ?></h3>
            
            <!-- Breadcrumb for device view -->
            <div class="mb-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="manage_vehicles.php">All Makes</a></li>
                        <li class="breadcrumb-item"><a href="manage_vehicles.php?view_models=<?php echo $viewingSeriesDetails['make_id']; ?>">
                            <?php echo htmlspecialchars($viewingSeriesDetails['make_name']); ?> Models
                        </a></li>
                        <li class="breadcrumb-item"><a href="manage_vehicles.php?view_series=<?php echo $viewingSeriesDetails['model_id']; ?>">
                            <?php echo htmlspecialchars($viewingSeriesDetails['model_name']); ?> Series
                        </a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($viewingSeriesDetails['series_name']); ?> Devices</li>
                    </ol>
                </nav>
            </div>
            
            <?php if ($editingDevice): ?>
                <h4 class="mb-3">Edit Device</h4>
                <form method="post" action="manage_vehicles.php?view_devices=<?php echo $viewingSeriesId; ?>">
                    <input type="hidden" name="device_id" value="<?php echo $editingDevice['id']; ?>">
                    <input type="hidden" name="series_id" value="<?php echo $viewingSeriesId; ?>">
                    <div class="mb-3">
                        <label for="device_name" class="form-label">Device Name:</label>
                        <input type="text" class="form-control" id="device_name" name="device_name" value="<?php echo htmlspecialchars($editingDevice['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="device_description" class="form-label">Description (Optional):</label>
                        <textarea class="form-control" id="device_description" name="device_description" rows="3"><?php echo htmlspecialchars($editingDevice['description']); ?></textarea>
                    </div>
                    <div>
                        <button type="submit" name="edit_device" class="btn btn-primary">Update Device</button>
                        <a href="manage_vehicles.php?view_devices=<?php echo $viewingSeriesId; ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>
                </form>
            <?php else: ?>
                <h4 class="mb-3">Add New Device</h4>
                <form method="post" action="manage_vehicles.php?view_devices=<?php echo $viewingSeriesId; ?>">
                    <input type="hidden" name="series_id" value="<?php echo $viewingSeriesId; ?>">
                    <div class="mb-3">
                        <label for="device_name" class="form-label">Device Name:</label>
                        <input type="text" class="form-control" id="device_name" name="device_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="device_description" class="form-label">Description (Optional):</label>
                        <textarea class="form-control" id="device_description" name="device_description" rows="3"></textarea>
                    </div>
                    <div>
                        <button type="submit" name="add_device" class="btn btn-primary">Add Device</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="form-section">
            <h3>Device List</h3>
            <?php if (empty($devices)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No devices found for this series.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($devices as $device): ?>
                                <tr>
                                    <td><?php echo $device['id']; ?></td>
                                    <td><?php echo htmlspecialchars($device['name']); ?></td>
                                    <td><?php echo !empty($device['description']) ? htmlspecialchars($device['description']) : '<em>No description</em>'; ?></td>
                                    <td>
                                        <a href="manage_vehicles.php?view_devices=<?php echo $viewingSeriesId; ?>&edit_device=<?php echo $device['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form method="post" action="manage_vehicles.php?view_devices=<?php echo $viewingSeriesId; ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this device?');">
                                            <input type="hidden" name="device_id" value="<?php echo $device['id']; ?>">
                                            <button type="submit" name="delete_device" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php elseif (isset($viewingMakeId)): ?>
    <!-- Model Management Section -->
    <div class="mb-4">
        <div class="form-section">
            <h3>Manage Models for <?php echo htmlspecialchars($viewingMakeName); ?></h3>
            
            <?php if ($editingModel): ?>
                <h4 class="mb-3">Edit Model</h4>
                <form method="post" action="manage_vehicles.php?view_models=<?php echo $viewingMakeId; ?>">
                    <input type="hidden" name="model_id" value="<?php echo $editingModel['id']; ?>">
                    <div class="mb-3">
                        <label for="make_id" class="form-label">Make:</label>
                        <select class="form-select" id="make_id" name="make_id" required>
                            <?php foreach ($makes as $make): ?>
                                <option value="<?php echo $make['id']; ?>" <?php echo ($make['id'] == $editingModel['make_id'] ? 'selected' : ''); ?>>
                                    <?php echo htmlspecialchars($make['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="model_name" class="form-label">Model Name:</label>
                        <input type="text" class="form-control" id="model_name" name="model_name" value="<?php echo htmlspecialchars($editingModel['name']); ?>" required>
                    </div>
                    <div>
                        <button type="submit" name="edit_model" class="btn btn-primary">Update Model</button>
                        <a href="manage_vehicles.php?view_models=<?php echo $viewingMakeId; ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>
                </form>
            <?php else: ?>
                <h4 class="mb-3">Add New Model</h4>
                <form method="post" action="manage_vehicles.php?view_models=<?php echo $viewingMakeId; ?>">
                    <input type="hidden" name="make_id" value="<?php echo $viewingMakeId; ?>">
                    <div class="mb-3">
                        <label for="model_name" class="form-label">Model Name:</label>
                        <input type="text" class="form-control" id="model_name" name="model_name" required>
                    </div>
                    <div>
                        <button type="submit" name="add_model" class="btn btn-primary">Add Model</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="form-section">
            <h3>Model List</h3>
            <?php if (empty($models)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No models found for this make.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($models as $model): ?>
                                <tr>
                                    <td><?php echo $model['id']; ?></td>
                                    <td><?php echo htmlspecialchars($model['name']); ?></td>
                                    <td>
                                        <a href="manage_vehicles.php?view_series=<?php echo $model['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-list"></i> View Series
                                        </a>
                                        <a href="manage_vehicles.php?view_models=<?php echo $viewingMakeId; ?>&edit_model=<?php echo $model['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form method="post" action="manage_vehicles.php?view_models=<?php echo $viewingMakeId; ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this model?');">
                                            <input type="hidden" name="model_id" value="<?php echo $model['id']; ?>">
                                            <button type="submit" name="delete_model" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <!-- Make Management Section -->
    <div class="mb-4">
        <div class="form-section">
            <h3>Manage Vehicle Makes</h3>
            
            <?php if ($editingMake): ?>
                <h4 class="mb-3">Edit Make</h4>
                <form method="post" action="manage_vehicles.php">
                    <input type="hidden" name="make_id" value="<?php echo $editingMake['id']; ?>">
                    <div class="mb-3">
                        <label for="make_name" class="form-label">Make Name:</label>
                        <input type="text" class="form-control" id="make_name" name="make_name" value="<?php echo htmlspecialchars($editingMake['name']); ?>" required>
                    </div>
                    <div>
                        <button type="submit" name="edit_make" class="btn btn-primary">Update Make</button>
                        <a href="manage_vehicles.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>
                </form>
            <?php else: ?>
                <h4 class="mb-3">Add New Make</h4>
                <form method="post" action="manage_vehicles.php">
                    <div class="mb-3">
                        <label for="make_name" class="form-label">Make Name:</label>
                        <input type="text" class="form-control" id="make_name" name="make_name" required>
                    </div>
                    <div>
                        <button type="submit" name="add_make" class="btn btn-primary">Add Make</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="form-section">
            <h3>Make List</h3>
            <?php if (empty($makes)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No makes found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($makes as $make): ?>
                                <tr>
                                    <td><?php echo $make['id']; ?></td>
                                    <td><?php echo htmlspecialchars($make['name']); ?></td>
                                    <td>
                                        <a href="manage_vehicles.php?view_models=<?php echo $make['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-list"></i> View Models
                                        </a>
                                        <a href="manage_vehicles.php?edit_make=<?php echo $make['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form method="post" action="manage_vehicles.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this make?');">
                                            <input type="hidden" name="make_id" value="<?php echo $make['id']; ?>">
                                            <button type="submit" name="delete_make" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once ROOT_PATH . '/app/views/admin/partials/footer.php'; ?> 