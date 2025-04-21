<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vehicle Data - Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            background-color: #d4edda;
            color: #155724;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Vehicle Data</h1>
        
        <?php
        /**
         * Admin script to add vehicle data
         * THIS IS FOR DEVELOPMENT ONLY - SHOULD BE PROTECTED OR REMOVED IN PRODUCTION
         */
        
        // Include initialization script
        require_once __DIR__ . '/../../includes/init.php';
        
        // Example data
        $vehicleData = [
            'Toyota' => [
                'Hilux' => ['SR', 'SR5', 'Rogue', 'Rugged', 'Rugged X'],
                'LandCruiser' => ['70 Series', '200 Series', '300 Series', 'Prado'],
                'RAV4' => ['GX', 'GXL', 'Cruiser', 'Edge']
            ],
            'Ford' => [
                'Ranger' => ['XL', 'XLS', 'XLT', 'Wildtrak', 'Raptor'],
                'Everest' => ['Ambiente', 'Trend', 'Sport', 'Titanium'],
                'F-150' => ['XL', 'XLT', 'Lariat', 'King Ranch', 'Platinum']
            ],
            'Nissan' => [
                'Navara' => ['SL', 'ST', 'ST-X', 'PRO-4X'],
                'Patrol' => ['Ti', 'Ti-L'],
                'X-Trail' => ['ST', 'ST-L', 'Ti', 'Ti-L']
            ],
            'Mitsubishi' => [
                'Triton' => ['GLX', 'GLX+', 'GLS', 'GSR'],
                'Pajero Sport' => ['GLX', 'GLS', 'Exceed', 'GSR'],
                'Outlander' => ['ES', 'LS', 'Exceed', 'GSR']
            ],
            'Holden' => [
                'Colorado' => ['LS', 'LT', 'LTZ', 'Z71'],
                'Trailblazer' => ['LT', 'LTZ', 'Z71']
            ],
            'Isuzu' => [
                'D-Max' => ['SX', 'LS-M', 'LS-U', 'X-Terrain'],
                'MU-X' => ['LS-M', 'LS-U', 'LS-T']
            ],
            'Mazda' => [
                'BT-50' => ['XS', 'XT', 'XTR', 'GT'],
                'CX-5' => ['Maxx', 'Maxx Sport', 'Touring', 'GT', 'Akera']
            ],
            'Volkswagen' => [
                'Amarok' => ['Core', 'Sportline', 'Highline', 'Aventura'],
                'Touareg' => ['170TDI', '210TDI Elegance', '210TDI R-Line']
            ],
            'Suzuki' => [
                'Jimny' => ['Lite', 'GLX'],
                'Vitara' => ['Base', 'Turbo', 'Turbo AllGrip'],
                'Swift' => ['GL', 'GL Navigator', 'GLX Turbo']
            ],
            'Mercedes' => [
                'G-Class' => ['G 350 d', 'G 400 d', 'G 63 AMG'],
                'GLE' => ['300 d', '450', 'AMG 53', 'AMG 63'],
                'X-Class' => ['X220d', 'X250d', 'X350d']
            ],
            'Chevrolet' => [
                'Silverado' => ['LTZ', 'Trail Boss'],
                'Colorado' => ['WT', 'LT', 'Z71', 'ZR2']
            ]
        ];
        
        function addVehicleData($pdo, $data) {
            $added = ['makes' => 0, 'models' => 0, 'series' => 0];
            
            foreach ($data as $makeName => $models) {
                // Get make ID
                $stmt = $pdo->prepare("SELECT id FROM vehicle_makes WHERE name = ?");
                $stmt->execute([$makeName]);
                $make = $stmt->fetch();
                
                if (!$make) {
                    echo "<p>Make not found: $makeName. Skipping.</p>";
                    continue;
                }
                
                $makeId = $make['id'];
                $added['makes']++;
                
                foreach ($models as $modelName => $seriesList) {
                    // Add model
                    $stmt = $pdo->prepare("INSERT IGNORE INTO vehicle_models (make_id, name) VALUES (?, ?)");
                    $stmt->execute([$makeId, $modelName]);
                    
                    // Get model ID
                    $modelId = $pdo->lastInsertId();
                    if (!$modelId) {
                        $stmt = $pdo->prepare("SELECT id FROM vehicle_models WHERE make_id = ? AND name = ?");
                        $stmt->execute([$makeId, $modelName]);
                        $model = $stmt->fetch();
                        if ($model) {
                            $modelId = $model['id'];
                        } else {
                            echo "<p>Failed to add or find model: $modelName</p>";
                            continue;
                        }
                    } else {
                        $added['models']++;
                    }
                    
                    foreach ($seriesList as $seriesName) {
                        // Add series
                        $stmt = $pdo->prepare("INSERT IGNORE INTO vehicle_series (model_id, name) VALUES (?, ?)");
                        $result = $stmt->execute([$modelId, $seriesName]);
                        
                        if ($result && $stmt->rowCount() > 0) {
                            $added['series']++;
                        }
                    }
                }
            }
            
            return $added;
        }
        
        // Check if user is logged in (in a real app, add proper admin check)
        if (!isLoggedIn()) {
            die("<div class='alert alert-error'>Access denied. You must be logged in.</div>");
        }
        
        // Add the vehicle data
        try {
            $result = addVehicleData($pdo, $vehicleData);
            echo "<div class='result'>";
            echo "<h2>Vehicle data added successfully!</h2>";
            echo "<p>Added or found:</p>";
            echo "<ul>";
            echo "<li>Makes: " . $result['makes'] . "</li>";
            echo "<li>Models: " . $result['models'] . "</li>";
            echo "<li>Series: " . $result['series'] . "</li>";
            echo "</ul>";
            echo "<p><a href='/dashboard.php' class='btn'>Return to Dashboard</a></p>";
            echo "</div>";
        } catch (Exception $e) {
            echo "<div class='alert alert-error'>Error: " . $e->getMessage() . "</div>";
        }
        ?>
    </div>
</body>
</html> 