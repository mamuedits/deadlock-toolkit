<?php
session_start();

//Get data from session
$processes = $_SESSION['processes'];
$resources = $_SESSION['resources'];
$allocation = $_SESSION['allocation'];
$max = $_SESSION['max'];
$total_resources = $_SESSION['total_resources'];


//Calculate need matrix
$need = [];
for ($i = 0; $i < $processes; $i++) {
    for ($j = 0; $j < $resources; $j++) {
        $need[$i][$j] = $max[$i][$j] - $allocation[$i][$j];
    }
}
//Check availability of resources
$available = [];
for ($j = 0; $j < $resources; $j++) {
    $available[$j] = $total_resources[$j] - $allocated_resources[$j];
}

//Deadlock detection
function isDeadlock($processes, $resources, $available, $allocation, $need) {
    $work = $available;
    $finish = array_fill(0, $processes, false);
    
    $deadlocked = [];
    for ($i = 0; $i < $processes; $i++) {
        $canProceed = true;
        for ($j = 0; $j < $resources; $j++) {
            if ($need[$i][$j] > $work[$j]) {
                $canProceed = false;
                break;
            }
        }
        if (!$canProceed) {
            $deadlocked[] = $i;
        }
    }
    
    return count($deadlocked) === $processes ? $deadlocked : false;
}

//Recovery
function terminateProcess($pid, &$allocation, &$available) {
    for ($j = 0; $j < count($available); $j++) {
        $available[$j] += $allocation[$pid][$j];
        $allocation[$pid][$j] = 0;
    }
    return "Terminated Process P$pid";
}

function preemptResource($pid, $rid, &$allocation, &$available) {
    $recovered = $allocation[$pid][$rid];
    $available[$rid] += $recovered;
    $allocation[$pid][$rid] = 0;
    return "Preempted Resource ".chr(65+$rid)." from P$pid (Recovered: $recovered)";
}

//Handle form submissions
$recovery_log = [];
if (isset($_POST['terminate'])) {
    $pid = $_POST['pid'];
    $recovery_log[] = terminateProcess($pid, $allocation, $available);
}

if (isset($_POST['preempt'])) {
    $pid = $_POST['pid'];
    $rid = $_POST['rid'];
    $recovery_log[] = preemptResource($pid, $rid, $allocation, $available);
}

//Check current state
$deadlocked = isDeadlock($processes, $resources, $available, $allocation, $need);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Recovery | Deadlock Toolkit</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto+Mono&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Deadlock Recovery Toolkit</h1>
        
        <?php if ($deadlocked !== false): ?>
            <div class="alert alert-danger">
                <strong>Deadlock Detected!</strong> Processes <?= implode(', ', array_map(fn($p) => "P$p", $deadlocked)) ?> are deadlocked.
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                System is currently in a safe state (no deadlock detected).
            </div>
        <?php endif; ?>
        
        <div class="recovery-actions">
            <h2>Recovery Actions</h2>
            
            <form method="post" class="recovery-form">
                <div class="form-group">
                    <label>Terminate Process:</label>
                    <select name="pid" required>
                        <?php for ($i = 0; $i < $processes; $i++): ?>
                            <option value="<?= $i ?>">P<?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" name="terminate" class="btn btn-danger">Terminate</button>
                </div>
            </form>
            
            <form method="post" class="recovery-form">
                <div class="form-group">
                    <label>Preempt Resource:</label>
                    <select name="pid" required>
                        <?php for ($i = 0; $i < $processes; $i++): ?>
                            <option value="<?= $i ?>">P<?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <select name="rid" required>
                        <?php for ($j = 0; $j < $resources; $j++): ?>
                            <option value="<?= $j ?>"><?= chr(65 + $j) ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" name="preempt" class="btn btn-warning">Preempt</button>
                </div>
            </form>
        </div>
        
        <?php if (!empty($recovery_log)): ?>
            <div class="recovery-log">
                <h3>Recovery Actions Taken:</h3>
                <ul>
                    <?php foreach ($recovery_log as $log): ?>
                        <li><?= $log ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="current-state">
            <h2>Current System State</h2>
            <div class="state-grid">
                <div class="state-card">
                    <h3>Available Resources</h3>
                    <ul>
                        <?php foreach ($available as $j => $val): ?>
                            <li><?= chr(65 + $j) ?>: <?= $val ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="state-card">
                    <h3>Allocation Matrix</h3>
                    <table class="state-table">
                        <tr>
                            <th>Process</th>
                            <?php for ($j = 0; $j < $resources; $j++): ?>
                                <th><?= chr(65 + $j) ?></th>
                            <?php endfor; ?>
                        </tr>
                        <?php for ($i = 0; $i < $processes; $i++): ?>
                        <tr>
                            <td>P<?= $i ?></td>
                            <?php for ($j = 0; $j < $resources; $j++): ?>
                                <td><?= $allocation[$i][$j] ?></td>
                            <?php endfor; ?>
                        </tr>
                        <?php endfor; ?>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="navigation">
            <a href="results.php" class="btn">Back to Safety Check</a>
            <a href="index.html" class="btn">Start Over</a>
        </div>
    </div>
</body>
</html>
