<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/config/config.php';

echo "Testing Budget Methods...\n\n";

try {
    $budget = new Budget();
    
    // Test 1: Get active budget for department 1 (change this to your department ID)
    $departmentId = 1; // CHANGE THIS TO YOUR ACTUAL DEPARTMENT ID
    
    echo "Test 1: Getting active budget for department {$departmentId}...\n";
    $activeBudget = $budget->getActiveBudget($departmentId);
    
    if ($activeBudget) {
        echo "✅ Active budget found:\n";
        echo "   - Budget ID: {$activeBudget['id']}\n";
        echo "   - Budget Amount: ₦" . number_format($activeBudget['budget_amount'], 2) . "\n";
        echo "   - Available Amount: ₦" . number_format($activeBudget['available_amount'], 2) . "\n";
    } else {
        echo "❌ No active budget found for this department\n";
        exit;
    }
    
    echo "\n";
    
    // Test 2: Check availability
    $testAmount = 1000.00;
    echo "Test 2: Checking if ₦" . number_format($testAmount, 2) . " is available...\n";
    $isAvailable = $budget->checkAvailability($departmentId, $testAmount);
    echo ($isAvailable ? "✅" : "❌") . " Budget availability: " . ($isAvailable ? "SUFFICIENT" : "INSUFFICIENT") . "\n";
    
    echo "\n";
    
    // Test 3: Try allocation (we won't actually commit this)
    echo "Test 3: Testing allocation method (dry run)...\n";
    $testRequisitionId = 9999; // Fake requisition ID
    
    try {
        $result = $budget->allocateBudget(
            $activeBudget['id'],
            $testRequisitionId,
            $testAmount,
            'Test allocation - will be rolled back'
        );
        
        if ($result['success']) {
            echo "✅ Allocation method executed successfully\n";
            echo "   Message: {$result['message']}\n";
            
            // Manually rollback for safety
            $db = Database::getInstance();
            if ($db->getConnection()->inTransaction()) {
                $db->rollback();
                echo "   (Transaction rolled back - no actual changes made)\n";
            }
        } else {
            echo "❌ Allocation failed: {$result['message']}\n";
        }
    } catch (Exception $e) {
        echo "❌ Exception in allocateBudget: " . $e->getMessage() . "\n";
        echo "   Stack trace: " . $e->getTraceAsString() . "\n";
    }
    
    echo "\n✅ All budget method tests completed!\n";
    
} catch (Exception $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}