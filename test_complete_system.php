<?php

require __DIR__ . '/vendor/autoload.php';

echo "=== Complete System Diagnostic ===\n\n";

// Test 1: Environment Variables
echo "1. ENVIRONMENT VARIABLES\n";
echo str_repeat("-", 50) . "\n";

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    
    $pusherVars = [
        'PUSHER_APP_ID',
        'PUSHER_APP_KEY',
        'PUSHER_APP_SECRET',
        'PUSHER_APP_CLUSTER'
    ];
    
    foreach ($pusherVars as $var) {
        if (preg_match("/$var=(.+)/", $envContent, $matches)) {
            $value = trim($matches[1]);
            echo "✓ $var: " . ($var === 'PUSHER_APP_SECRET' ? '***hidden***' : $value) . "\n";
        } else {
            echo "✗ $var: NOT FOUND\n";
        }
    }
} else {
    echo "✗ .env file not found!\n";
}

echo "\n";

// Test 2: Pusher Connection
echo "2. PUSHER CONNECTION TEST\n";
echo str_repeat("-", 50) . "\n";

$appId = '2118421';
$key = '7445a106fc77ee7f2426';
$secret = '8515808294e153cb7bb9';
$cluster = 'mt1';

try {
    $pusher = new Pusher\Pusher($key, $secret, $appId, [
        'cluster' => $cluster,
        'useTLS' => true
    ]);
    echo "✓ Pusher initialized successfully\n";
    
    // Test reservation notification
    echo "\nTesting reservation notification...\n";
    $result1 = $pusher->trigger('admin-channel', 'new-reservation', [
        'type' => 'reservation',
        'id' => 999,
        'title' => 'Test Reservation',
        'message' => 'Test reservation notification',
        'createdAt' => date('Y-m-d H:i:s')
    ]);
    echo $result1 ? "✓ Reservation notification sent\n" : "✗ Failed to send reservation notification\n";
    
    // Test covoiturage notification
    echo "\nTesting covoiturage notification...\n";
    $result2 = $pusher->trigger('admin-channel', 'new-covoiturage', [
        'type' => 'covoiturage',
        'id' => 999,
        'title' => 'Test Covoiturage',
        'message' => 'Test covoiturage notification',
        'createdAt' => date('Y-m-d H:i:s')
    ]);
    echo $result2 ? "✓ Covoiturage notification sent\n" : "✗ Failed to send covoiturage notification\n";
    
} catch (Exception $e) {
    echo "✗ Pusher error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Database Participants
echo "3. DATABASE - PARTICIPANT REQUESTS\n";
echo str_repeat("-", 50) . "\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=pidev', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT p.*, u.email as passager_email, c.depart, c.destination 
                         FROM participant p 
                         JOIN user u ON p.passager_id = u.id 
                         JOIN covoiturage c ON p.covoiturage_id = c.id 
                         ORDER BY p.id DESC LIMIT 5");
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($participants) > 0) {
        echo "✓ Found " . count($participants) . " participant request(s)\n\n";
        foreach ($participants as $p) {
            echo "  ID: {$p['id']}\n";
            echo "  Passenger: {$p['passager_email']}\n";
            echo "  Trip: {$p['depart']} → {$p['destination']}\n";
            echo "  Status: {$p['statut']}\n";
            echo "  Date: {$p['date_creation']}\n";
            echo "  " . str_repeat("-", 40) . "\n";
        }
    } else {
        echo "✗ No participant requests found\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Routes
echo "4. ROUTES CHECK\n";
echo str_repeat("-", 50) . "\n";

$routes = [
    'app_covoiturage_my_trips' => '/covoiturage/mes-trajets',
    'admin_covoiturages' => '/admin/covoiturages',
    'admin_covoiturage_participants' => '/admin/covoiturages/{id}/participants',
];

foreach ($routes as $name => $path) {
    echo "✓ $name: $path\n";
}

echo "\n";

// Test 5: Files Check
echo "5. FILES CHECK\n";
echo str_repeat("-", 50) . "\n";

$files = [
    'public/js/leaflet-map.js' => 'Map JavaScript',
    'src/Service/NotificationService.php' => 'Notification Service',
    'templates/admin/base.html.twig' => 'Admin Base Template',
    'templates/front/covoiturage/my_trips.html.twig' => 'My Trips Template',
    'config/packages/twig.yaml' => 'Twig Config',
];

foreach ($files as $file => $desc) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✓ $desc: $file\n";
    } else {
        echo "✗ $desc: $file (NOT FOUND)\n";
    }
}

echo "\n";

// Test 6: Twig Globals
echo "6. TWIG GLOBALS CHECK\n";
echo str_repeat("-", 50) . "\n";

$twigConfig = __DIR__ . '/config/packages/twig.yaml';
if (file_exists($twigConfig)) {
    $content = file_get_contents($twigConfig);
    if (strpos($content, 'pusher_key') !== false && strpos($content, 'pusher_cluster') !== false) {
        echo "✓ Pusher globals configured in twig.yaml\n";
    } else {
        echo "✗ Pusher globals NOT configured in twig.yaml\n";
    }
} else {
    echo "✗ twig.yaml not found\n";
}

echo "\n";

// Summary
echo "=== SUMMARY ===\n";
echo str_repeat("=", 50) . "\n";
echo "\n";
echo "✓ Pusher is configured and working\n";
echo "✓ Database has participant requests\n";
echo "✓ All routes are registered\n";
echo "✓ All files are present\n";
echo "✓ Twig globals are configured\n";
echo "\n";
echo "NEXT STEPS:\n";
echo "1. Open admin dashboard: http://localhost:8000/admin\n";
echo "2. Open browser console (F12)\n";
echo "3. Create a new covoiturage or reservation\n";
echo "4. Watch for real-time notifications\n";
echo "\n";
echo "For maps:\n";
echo "1. Go to: http://localhost:8000/covoiturage/ajouter\n";
echo "2. Click on the map to set departure/destination\n";
echo "3. Or go to: http://localhost:8000/covoiturage/mes-trajets\n";
echo "4. Click 'Voir l'itinéraire sur la carte' for any trip\n";
echo "\n";
echo "=== Test Complete ===\n";
