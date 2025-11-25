<?php
/**
 * GateWey Requisition Management System
 * Progress Tracker Component
 * 
 * File: includes/progress-tracker.php
 * Purpose: Visual display of requisition approval progress
 * 
 * Usage:
 * include 'includes/progress-tracker.php';
 * renderProgressTracker($requisitionId, $requisition);
 */

if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

/**
 * Render progress tracker for a requisition
 * 
 * @param int $requisitionId Requisition ID
 * @param array $requisition Requisition data
 */
function renderProgressTracker($requisitionId, $requisition) {
    // Initialize WorkflowEngine
    $workflow = new WorkflowEngine();
    
    // Get approval progress
    $progress = $workflow->getApprovalProgress($requisitionId);
    
    // Get approval history
    $approval = new Approval();
    $history = $approval->getApprovalHistory($requisitionId);
    
    // Determine if requisition is completed or rejected
    $isCompleted = ($requisition['status'] === STATUS_COMPLETED);
    $isRejected = ($requisition['status'] === STATUS_REJECTED);
    $isPaid = ($requisition['status'] === STATUS_PAID);
    
    ?>
    <div class="progress-tracker-container">
        <h5 class="progress-tracker-title">
            <i class="fas fa-route"></i> Approval Progress
        </h5>
        
        <div class="progress-tracker">
            <?php if (empty($progress)): ?>
                <div class="progress-stage">
                    <div class="progress-stage-icon bg-secondary">
                        <i class="fas fa-file"></i>
                    </div>
                    <div class="progress-stage-content">
                        <div class="progress-stage-label">Draft</div>
                        <div class="progress-stage-status text-muted">Not yet submitted</div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($progress as $index => $stage): ?>
                    <?php
                    // Determine stage status
                    $isActive = $stage['is_current'];
                    $isComplete = $stage['is_completed'];
                    $isRejectedStage = $stage['is_rejected'];
                    $isPending = !$isComplete && !$isRejectedStage && !$isActive;
                    
                    // Icon and color classes
                    if ($isRejectedStage) {
                        $iconClass = 'fas fa-times-circle';
                        $bgClass = 'bg-danger';
                        $stageClass = 'stage-rejected';
                    } elseif ($isComplete) {
                        $iconClass = 'fas fa-check-circle';
                        $bgClass = 'bg-success';
                        $stageClass = 'stage-completed';
                    } elseif ($isActive) {
                        $iconClass = 'fas fa-clock';
                        $bgClass = 'bg-warning';
                        $stageClass = 'stage-active';
                    } else {
                        $iconClass = 'fas fa-circle';
                        $bgClass = 'bg-secondary';
                        $stageClass = 'stage-pending';
                    }
                    
                    // Get approval data if exists
                    $approvalData = $stage['approval_data'];
                    ?>
                    
                    <div class="progress-stage <?php echo $stageClass; ?>">
                        <div class="progress-stage-icon <?php echo $bgClass; ?>">
                            <i class="<?php echo $iconClass; ?>"></i>
                        </div>
                        
                        <div class="progress-stage-content">
                            <div class="progress-stage-label">
                                <?php echo htmlspecialchars($stage['role_name']); ?>
                            </div>
                            
                            <?php if ($approvalData): ?>
                                <div class="progress-stage-status">
                                    <?php if ($isRejectedStage): ?>
                                        <span class="text-danger">
                                            <i class="fas fa-times-circle"></i> Rejected
                                        </span>
                                    <?php elseif ($isComplete): ?>
                                        <span class="text-success">
                                            <i class="fas fa-check-circle"></i> Approved
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="progress-stage-info">
                                    <div class="text-muted small">
                                        <?php 
                                        $approverName = trim($approvalData['first_name'] . ' ' . $approvalData['last_name']);
                                        echo htmlspecialchars($approverName); 
                                        ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?php echo format_datetime($approvalData['created_at']); ?>
                                    </div>
                                    
                                    <?php if ($approvalData['comments']): ?>
                                        <div class="progress-stage-comment mt-2">
                                            <i class="fas fa-comment"></i>
                                            <?php echo nl2br(htmlspecialchars($approvalData['comments'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="progress-stage-status">
                                    <?php if ($isActive): ?>
                                        <span class="text-warning">
                                            <i class="fas fa-clock"></i> Awaiting Approval
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <i class="fas fa-circle"></i> Pending
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($index < count($progress) - 1): ?>
                            <div class="progress-stage-connector 
                                <?php echo ($isComplete || $isRejectedStage) ? 'connector-active' : 'connector-inactive'; ?>">
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <!-- Payment Stage (Finance Member) -->
                <?php if ($isPaid || $isCompleted): ?>
                    <div class="progress-stage stage-completed">
                        <div class="progress-stage-icon bg-success">
                            <i class="fas fa-money-check-alt"></i>
                        </div>
                        <div class="progress-stage-content">
                            <div class="progress-stage-label">Payment Processed</div>
                            <div class="progress-stage-status">
                                <span class="text-success">
                                    <i class="fas fa-check-circle"></i> Paid
                                </span>
                            </div>
                            <?php if ($requisition['paid_at']): ?>
                                <div class="progress-stage-info">
                                    <div class="text-muted small">
                                        <?php echo format_datetime($requisition['paid_at']); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($isCompleted): ?>
                            <div class="progress-stage-connector connector-active"></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Receipt Upload Stage -->
                <?php if ($isCompleted): ?>
                    <div class="progress-stage stage-completed">
                        <div class="progress-stage-icon bg-primary">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="progress-stage-content">
                            <div class="progress-stage-label">Receipt Submitted</div>
                            <div class="progress-stage-status">
                                <span class="text-primary">
                                    <i class="fas fa-check-circle"></i> Completed
                                </span>
                            </div>
                            <?php if ($requisition['receipt_uploaded_at']): ?>
                                <div class="progress-stage-info">
                                    <div class="text-muted small">
                                        <?php echo format_datetime($requisition['receipt_uploaded_at']); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($isRejected && $requisition['rejection_reason']): ?>
            <div class="alert alert-danger mt-3">
                <h6 class="alert-heading">
                    <i class="fas fa-exclamation-triangle"></i> Rejection Reason
                </h6>
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($requisition['rejection_reason'])); ?></p>
                
                <?php if ($requisition['rejected_by_id']): ?>
                    <?php
                    $db = Database::getInstance();
                    $rejecter = $db->fetchOne(
                        "SELECT first_name, last_name, email FROM users WHERE id = ?",
                        [$requisition['rejected_by_id']]
                    );
                    if ($rejecter):
                    ?>
                        <hr>
                        <p class="mb-0 small text-muted">
                            Rejected by: <?php echo htmlspecialchars($rejecter['first_name'] . ' ' . $rejecter['last_name']); ?>
                            (<?php echo htmlspecialchars($rejecter['email']); ?>)
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <style>
    /* Progress Tracker Styles */
    .progress-tracker-container {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: var(--spacing-6);
        margin-bottom: var(--spacing-6);
    }
    
    .progress-tracker-title {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin-bottom: var(--spacing-6);
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }
    
    .progress-tracker {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-4);
        position: relative;
    }
    
    .progress-stage {
        display: flex;
        align-items: flex-start;
        gap: var(--spacing-4);
        position: relative;
        padding: var(--spacing-4);
        border-radius: var(--radius-md);
        transition: var(--transition-fast);
    }
    
    .progress-stage:hover {
        background: var(--bg-hover);
    }
    
    .progress-stage.stage-active {
        background: var(--warning-light);
        border: 2px solid var(--warning);
    }
    
    .progress-stage.stage-completed {
        background: var(--success-light);
    }
    
    .progress-stage.stage-rejected {
        background: var(--danger-light);
    }
    
    .progress-stage-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
        flex-shrink: 0;
        box-shadow: var(--shadow-sm);
    }
    
    .progress-stage-content {
        flex: 1;
    }
    
    .progress-stage-label {
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin-bottom: var(--spacing-1);
    }
    
    .progress-stage-status {
        font-size: var(--font-size-sm);
        margin-bottom: var(--spacing-2);
    }
    
    .progress-stage-info {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }
    
    .progress-stage-comment {
        background: var(--bg-base);
        border-left: 3px solid var(--primary);
        padding: var(--spacing-3);
        border-radius: var(--radius-sm);
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-top: var(--spacing-2);
    }
    
    .progress-stage-connector {
        position: absolute;
        left: 23px;
        top: 48px;
        bottom: -20px;
        width: 2px;
        z-index: -1;
    }
    
    .connector-active {
        background: var(--success);
    }
    
    .connector-inactive {
        background: var(--border-color);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .progress-stage {
            flex-direction: column;
            text-align: center;
        }
        
        .progress-stage-connector {
            display: none;
        }
    }
    </style>
    <?php
}