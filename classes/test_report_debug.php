<?php
/**
 * Debug page to test report data retrieval
 */

define('APP_ACCESS', true);
require_once __DIR__ . '/../config/config.php';
Session::start();
require_once __DIR__ . '/../middleware/auth-check.php';

$report = new Report();
$userId = Session::getUserId();

echo "<h1>Report Debug</h1>";
echo "<p>User ID: $userId</p>";

// Test basic query
$db = Database::getInstance();
$requisitions = $db->fetchAll("SELECT * FROM requisitions WHERE user_id = ? AND status != 'draft'", [$userId]);

echo "<h2>Total Requisitions (excluding drafts): " . count($requisitions) . "</h2>";

if (count($requisitions) > 0) {
    echo "<h3>Sample Requisition:</h3>";
    echo "<pre>" . print_r($requisitions[0], true) . "</pre>";
}

// Test report generation
$filters = [];
$reportData = $report->generatePersonalReport($filters, 1, 10);

echo "<h2>Report Generation Test</h2>";
echo "<p>Success: " . ($reportData['success'] ? 'Yes' : 'No') . "</p>";
echo "<p>Total Count: " . $reportData['statistics']['total_count'] . "</p>";
echo "<p>Total Amount: " . format_currency($reportData['statistics']['total_amount']) . "</p>";

echo "<h3>Chart Data:</h3>";
echo "<p>Trends: " . count($reportData['chart_data']['trends']) . " data points</p>";
if (!empty($reportData['chart_data']['trends'])) {
    echo "<pre>" . print_r($reportData['chart_data']['trends'], true) . "</pre>";
}

echo "<p>Categories: " . count($reportData['chart_data']['categories']) . " categories</p>";
if (!empty($reportData['chart_data']['categories'])) {
    echo "<pre>" . print_r($reportData['chart_data']['categories'], true) . "</pre>";
}

echo "<p>Status: " . count($reportData['chart_data']['status']) . " statuses</p>";
if (!empty($reportData['chart_data']['status'])) {
    echo "<pre>" . print_r($reportData['chart_data']['status'], true) . "</pre>";
}

echo "<p>Hourly Total: " . array_sum($reportData['chart_data']['hourly']) . "</p>";
echo "<p>Weekday Total: " . array_sum($reportData['chart_data']['weekday']) . "</p>";

echo "<h3>Hourly Data:</h3>";
echo "<pre>" . print_r($reportData['chart_data']['hourly'], true) . "</pre>";

echo "<h3>Weekday Data:</h3>";
echo "<pre>" . print_r($reportData['chart_data']['weekday'], true) . "</pre>";

echo "<h3>Monthly Data:</h3>";
echo "<pre>" . print_r($reportData['chart_data']['monthly'], true) . "</pre>";