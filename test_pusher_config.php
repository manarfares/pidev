<?php

require __DIR__ . '/vendor/autoload.php';

echo "=== Pusher Configuration Test ===\n\n";

// Manually set environment variables from .env file
$appId = '2118421';
$key = '7445a106fc77ee7f2426';
$secret = '8515808294e153cb7bb9';
$cluster = 'mt1';

echo "Environment Variables:\n";
echo "PUSHER_APP_ID: ✓ Set ($appId)\n";
echo "PUSHER_APP_KEY: ✓ Set ($key)\n";
echo "PUSHER_APP_SECRET: ✓ Set (hidden)\n";
echo "PUSHER_APP_CLUSTER: ✓ Set ($cluster)\n\n";

try {
    echo "Initializing Pusher...\n";
    $pusher = new Pusher\Pusher($key, $secret, $appId, [
        'cluster' => $cluster,
        'useTLS' => true
    ]);
    echo "✓ Pusher initialized successfully\n\n";
    
    echo "Sending test notification...\n";
    $result = $pusher->trigger('admin-channel', 'test-event', [
        'message' => 'Test notification from PHP script',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    if ($result) {
        echo "✓ Test notification sent successfully!\n";
        echo "✓ Pusher is working correctly!\n\n";
        echo "Check your Pusher dashboard at: https://dashboard.pusher.com/\n";
    } else {
        echo "✗ Failed to send notification\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== Test Complete ===\n";
