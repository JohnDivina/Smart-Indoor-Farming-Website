<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SmartFarm SMS worker</title>
    <!-- Legacy one-pass polling: avoid overlapping an SMS send. -->
    <meta http-equiv="refresh" content="45">
</head>
<body>
<?php
$lockPath = __DIR__ . DIRECTORY_SEPARATOR . 'receivesms.lock';
$lock = fopen($lockPath, 'c');
if ($lock === false) {
    http_response_code(500);
    exit('Could not open the SMS worker lock.');
}

try {
    if (!flock($lock, LOCK_EX | LOCK_NB)) {
        echo '<p>SMS worker is still running. This page will try again in 45 seconds.</p>';
        exit;
    }

    set_time_limit(120);
    $pythonCandidates = [
        dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Python313' . DIRECTORY_SEPARATOR . 'python.exe',
        'C:\\Users\\Server\\AppData\\Local\\Programs\\Python\\Python313\\python.exe',
    ];
    $python = null;
    foreach ($pythonCandidates as $candidate) {
        if (is_file($candidate)) {
            $python = $candidate;
            break;
        }
    }
    $script = __DIR__ . DIRECTORY_SEPARATOR . 'receive_sms.py';
    if ($python === null || !is_file($script)) {
        http_response_code(500);
        echo '<p>Python or receive_sms.py was not found.</p>';
        exit;
    }

    $command = escapeshellarg($python) . ' ' . escapeshellarg($script) . ' 2>&1';
    $output = shell_exec($command);
    echo '<pre>' . htmlspecialchars((string) $output, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>';
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}
?>
</body>
</html>
