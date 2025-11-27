<?php
/**
 * Test Payment Statistics
 */

define('APP_ACCESS', true);
require_once __DIR__ . '/../config/config.php';
Session::start();

echo "<h2>Payment Statistics Test</h2>";
echo "<pre>";

$payment = new Payment();

echo "\n=== TEST 1: All-Time Statistics ===\n";
$allTimeStats = $payment->getPaymentStatistics([]);
echo "Result: " . print_r($allTimeStats, true) . "\n";
echo "Paid Count: " . ($allTimeStats['paid_count'] ?? 'NULL') . "\n";
echo "Total Amount: " . ($allTimeStats['total_amount'] ?? 'NULL') . "\n";
echo "Average: " . ($allTimeStats['average_amount'] ?? 'NULL') . "\n";

echo "\n=== TEST 2: This Month Statistics ===\n";
$dateFrom = date('Y-m-01');
$dateTo = date('Y-m-t');
echo "Date From: {$dateFrom}\n";
echo "Date To: {$dateTo}\n";

$thisMonthStats = $payment->getPaymentStatistics([
    'date_from' => $dateFrom,
    'date_to' => $dateTo
]);
echo "Result: " . print_r($thisMonthStats, true) . "\n";
echo "Paid Count: " . ($thisMonthStats['paid_count'] ?? 'NULL') . "\n";
echo "Total Amount: " . ($thisMonthStats['total_amount'] ?? 'NULL') . "\n";
echo "Average: " . ($thisMonthStats['average_amount'] ?? 'NULL') . "\n";

echo "\n=== TEST 3: Check Payment.php File ===\n";
$paymentFile = file_get_contents(__DIR__ . '/../classes/Payment.php');
if (strpos($paymentFile, 'payment_date') !== false) {
    echo "✅ Payment.php contains 'payment_date'\n";
} else {
    echo "❌ Payment.php does NOT contain 'payment_date' (still using paid_at)\n";
}

if (strpos($paymentFile, 'paid_at') !== false) {
    echo "⚠️ Payment.php still contains 'paid_at' references\n";
} else {
    echo "✅ Payment.php does not contain 'paid_at'\n";
}

echo "\n=== TEST 4: Direct Database Query ===\n";
$db = Database::getInstance();
$directResult = $db->fetchOne(
    "SELECT 
        COUNT(*) as total_payments,
        SUM(r.total_amount) as total_amount,
        COUNT(CASE WHEN r.status = 'paid' THEN 1 END) as paid_count
    FROM requisitions r
    WHERE r.status IN ('paid', 'completed')
      AND DATE(r.payment_date) >= ?
      AND DATE(r.payment_date) <= ?",
    [$dateFrom, $dateTo]
);
echo "Direct Query Result: " . print_r($directResult, true) . "\n";

echo "</pre>";
?>