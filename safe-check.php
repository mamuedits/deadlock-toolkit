<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $processes = (int)$_POST['processes'];
    $resources = (int)$_POST['resources'];
    $total_resources = array_map('intval', explode(' ', $_POST['total_resources']));
    
    $_SESSION['processes'] = $processes;
    $_SESSION['resources'] = $resources;
    $_SESSION['total_resources'] = $total_resources;
    
    // Initialize matrices if not set
    if (!isset($_SESSION['allocation'])) {
        $_SESSION['allocation'] = array_fill(0, $processes, array_fill(0, $resources, 0));
    }
    if (!isset($_SESSION['max'])) {
        $_SESSION['max'] = array_fill(0, $processes, array_fill(0, $resources, 0));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matrix Input | Deadlock Toolkit</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto+Mono&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Enter Allocation and Maximum Need Matrices</h1>
        
        <form action="results.php" method="post">
            <h2>Allocation Matrix</h2>
            <p>Enter currently allocated resources for each process:</p>
            
            <table class="matrix-table">
                <tr>
                    <th>Process</th>
                    <?php for ($j = 0; $j < $_SESSION['resources']; $j++): ?>
                        <th><?= chr(65 + $j) ?></th>
                    <?php endfor; ?>
                </tr>
                
                <?php for ($i = 0; $i < $_SESSION['processes']; $i++): ?>
                <tr>
                    <td>P<?= $i ?></td>
                    <?php for ($j = 0; $j < $_SESSION['resources']; $j++): ?>
                        <td>
                            <input type="number" 
                                name="allocation[<?= $i ?>][<?= $j ?>]" 
                                value="<?= isset($_SESSION['allocation'][$i][$j]) ? $_SESSION['allocation'][$i][$j] : 0 ?>" 
                                min="0" 
                                required
                                class="matrix-input"
                                data-process="<?= $i ?>"
                                data-resource="<?= $j ?>">
                        </td>
                    <?php endfor; ?>
                </tr>
                <?php endfor; ?>
            </table>
            
            <h2>Maximum Need Matrix</h2>
            <p>Enter maximum need of resources for each process:</p>
            
            <table class="matrix-table">
                <tr>
                    <th>Process</th>
                    <?php for ($j = 0; $j < $_SESSION['resources']; $j++): ?>
                        <th><?= chr(65 + $j) ?></th>
                    <?php endfor; ?>
                </tr>
                
                <?php for ($i = 0; $i < $_SESSION['processes']; $i++): ?>
                <tr>
                    <td>P<?= $i ?></td  >
                    <?php for ($j = 0; $j < $_SESSION['resources']; $j++): ?>
                        <td>
                            <input type="number" 
                                name="max[<?= $i ?>][<?= $j ?>]" 
                                value="<?= isset($_SESSION['max'][$i][$j]) ? $_SESSION['max'][$i][$j] : 0 ?>" 
                                min="0" 
                                required
                                class="matrix-input"
                                data-process="<?= $i ?>"
                                data-resource="<?= $j ?>">
                        </td>
                    <?php endfor; ?>
                </tr>
                <?php endfor; ?>
            </table>
            
            <button type="submit" class="btn">Check Safety</button>
        </form>
    </div>
    
    <script src="script.js"></script>
</body>
</html>