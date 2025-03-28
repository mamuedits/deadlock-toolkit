<?php
session_start();

// Get input data
$processes = $_SESSION['processes'];
$resources = $_SESSION['resources'];
$total_resources = $_SESSION['total_resources'];
$allocation = $_POST['allocation'];
$max = $_POST['max'];

// Calculate need matrix
$need = [];
for ($i = 0; $i < $processes; $i++) {
    for ($j = 0; $j < $resources; $j++) {
        $need[$i][$j] = $max[$i][$j] - $allocation[$i][$j];
    }
}

// Calculate available resources
$allocated_resources = array_fill(0, $resources, 0);
for ($i = 0; $i < $processes; $i++) {
    for ($j = 0; $j < $resources; $j++) {
        $allocated_resources[$j] += $allocation[$i][$j];
    }
}

$available = [];
for ($j = 0; $j < $resources; $j++) {
    $available[$j] = $total_resources[$j] - $allocated_resources[$j];
}

// Safety algorithm
$work = $available;
$finish = array_fill(0, $processes, false);
$safe_sequence = [];
$steps = [];

// Initialize step counter
$step = 1;

// Safety check
while (count($safe_sequence) < $processes) {
    $found = false;
    
    // Record current state
    $current_step = [
        'step' => $step,
        'work' => implode(', ', $work),
        'finish' => implode(', ', array_map(function($val) { return $val ? 'true' : 'false'; }, $finish)),
        'process' => '',
        'action' => ''
    ];
    
    for ($i = 0; $i < $processes; $i++) {
        if (!$finish[$i]) {
            $can_allocate = true;
            for ($j = 0; $j < $resources; $j++) {
                if ($need[$i][$j] > $work[$j]) {
                    $can_allocate = false;
                    break;
                }
            }
            
            if ($can_allocate) {
                $current_step['process'] = "P$i";
                $current_step['action'] = "Can be allocated (Need: " . implode(', ', $need[$i]) . ")";
                
                // Execute process
                for ($j = 0; $j < $resources; $j++) {
                    $work[$j] += $allocation[$i][$j];
                }
                $finish[$i] = true;
                $safe_sequence[] = $i;
                $found = true;
                
                $current_step['action'] .= " -> Work updated to: " . implode(', ', $work);
                break;
            } else {
                $current_step['process'] = "P$i";
                $current_step['action'] = "Cannot be allocated (Need: " . implode(', ', $need[$i]) . ")";
            }
        }
    }
    
    $steps[] = $current_step;
    $step++;
    
    if (!$found) {
        break;
    }
}

$is_safe = (count($safe_sequence) == $processes);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results | Deadlock Toolkit</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto+Mono&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Deadlock Analysis Results</h1>
        
        <div class="results-section">
            <h2>Safety Check Steps</h2>
            <table class="steps-table">
                <tr>
                    <th>Step</th>
                    <th>Work</th>
                    <th>Finish</th>
                    <th>Process</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($steps as $step): ?>
                <tr>
                    <td><?= $step['step'] ?></td>
                    <td><?= $step['work'] ?></td>
                    <td><?= $step['finish'] ?></td>
                    <td><?= $step['process'] ?></td>
                    <td><?= $step['action'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <div class="results-section">
            <h2>Final State</h2>
            <table class="final-table">
                <tr>
                    <th>Process</th>
                    <th>Allocation</th>
                    <th>Max Need</th>
                    <th>Need</th>
                </tr>
                <?php for ($i = 0; $i < $processes; $i++): ?>
                <tr>
                    <td>P<?= $i ?></td>
                    <td><?= implode(', ', $allocation[$i]) ?></td>
                    <td><?= implode(', ', $max[$i]) ?></td>
                    <td><?= implode(', ', $need[$i]) ?></td>
                </tr>
                <?php endfor; ?>
                <tr class="highlight">
                    <td>Available</td>
                    <td colspan="3"><?= implode(', ', $available) ?></td>
                </tr>
                <tr class="highlight">
                    <td>Total Resources</td>
                    <td colspan="3"><?= implode(', ', $total_resources) ?></td>
                </tr>
                <tr class="highlight">
                    <td>Safe State</td>
                    <td colspan="3"><?= $is_safe ? 'Yes' : 'No' ?></td>
                </tr>
                <?php if ($is_safe): ?>
                <tr class="highlight">
                    <td>Safe Sequence</td>
                    <td colspan="3">
                        <?= implode(' → ', array_map(function($p) { return "P$p"; }, $safe_sequence)) ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        
        <div class="actions">
            <button onclick="showMessage('<?= $is_safe ? 'System is in a safe state!' : 'Warning: System is in an unsafe state (potential deadlock)!' ?>')" 
                    class="btn <?= $is_safe ? 'btn-success' : 'btn-danger' ?>">
                Show Safety Status
            </button>
            <a href="index.php" class="btn">Start Over</a>
        </div>
    </div>
    
    <script src="script.js"></script>
    <script>
        // Show popup message on page load
        window.onload = function() {
            showMessage('<?= $is_safe ? 'System is in a safe state!' : 'Warning: System is in an unsafe state (potential deadlock)!' ?>');
        };
    </script>
</body>
</html>