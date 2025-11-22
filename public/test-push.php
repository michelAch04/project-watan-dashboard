<?php
// Test if WebPush can work
require __DIR__ . '/../vendor/autoload.php';

// Set OpenSSL config
putenv('OPENSSL_CONF=' . __DIR__ . '/../openssl.cnf');

echo "<h2>WebPush OpenSSL Test</h2>";

// Test OpenSSL EC key generation
$config = [
    'private_key_type' => OPENSSL_KEYTYPE_EC,
    'curve_name' => 'prime256v1'
];

echo "<p>Testing OpenSSL EC key generation...</p>";
$key = openssl_pkey_new($config);

if ($key === false) {
    echo "<p style='color: red;'>❌ FAILED: " . openssl_error_string() . "</p>";
    echo "<p>This means push notifications will NOT work.</p>";
    echo "<h3>Solution:</h3>";
    echo "<ol>";
    echo "<li>Edit <code>C:\\xampp\\apache\\conf\\extra\\httpd-xampp.conf</code></li>";
    echo "<li>Add this line after the first few lines:<br><code>SetEnv OPENSSL_CONF \"C:/xampp/htdocs/pwdashboard/openssl.cnf\"</code></li>";
    echo "<li>Restart Apache through XAMPP Control Panel</li>";
    echo "<li>Refresh this page</li>";
    echo "</ol>";
} else {
    echo "<p style='color: green;'>✅ SUCCESS: OpenSSL EC key generation works!</p>";
    echo "<p>Push notifications should work now!</p>";
    openssl_pkey_free($key);

    // Now test actual push
    echo "<hr>";
    echo "<h3>Testing Actual Push Notification</h3>";

    try {
        require __DIR__ . '/../bootstrap/app.php';
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        $user = App\Models\User::find(2);
        if ($user && $user->pushSubscriptions()->exists()) {
            $request = App\Models\RequestHeader::first();
            if ($request) {
                $notification = App\Models\InboxNotification::createForUser(
                    userId: $user->id,
                    requestId: $request->id,
                    type: 'test',
                    title: 'Browser Test',
                    message: 'Testing push from browser!'
                );
                echo "<p style='color: green;'>✅ Notification created (ID: {$notification->id})</p>";
                echo "<p>Check your device for the push notification!</p>";
            } else {
                echo "<p style='color: orange;'>No request found to attach notification to</p>";
            }
        } else {
            echo "<p style='color: orange;'>User 2 has no push subscriptions</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
}

echo "<hr><p><a href='/'>Back to Dashboard</a></p>";
