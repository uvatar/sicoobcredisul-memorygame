<?php
require_once 'database-init.php';
session_start();

$errors = [];
$formData = [
    'name' => '',
    'ssn' => '',
    'phone' => '',
    'terms' => false
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $formData['name'] = trim($_POST['name'] ?? '');
    $formData['ssn'] = trim($_POST['ssn'] ?? '');
    $formData['phone'] = trim($_POST['phone'] ?? '');
    $formData['terms'] = isset($_POST['terms']) && $_POST['terms'] === 'yes';
    
    // Basic validation
    if (empty($formData['name'])) {
        $errors['name'] = 'Name is required';
    }
    
    // SSN validation (###.###.###-##)
    if (empty($formData['ssn'])) {
        $errors['ssn'] = 'Social security number is required';
    } elseif (!preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $formData['ssn'])) {
        $errors['ssn'] = 'SSN must be in format ###.###.###-##';
    } else {
        // Check if SSN is unique
        $db = initDatabase();
        $stmt = $db->prepare('SELECT id FROM players WHERE ssn = :ssn');
        $stmt->bindValue(':ssn', $formData['ssn']);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        if ($result) {
            $errors['ssn'] = 'This SSN has already been registered. You can only play once.';
        }
    }
    
    // Phone validation ((##) #####-####)
    if (empty($formData['phone'])) {
        $errors['phone'] = 'Phone number is required';
    } elseif (!preg_match('/^\(\d{2}\) \d{5}-\d{4}$/', $formData['phone'])) {
        $errors['phone'] = 'Phone must be in format (##) #####-####';
    }
    
    if (!$formData['terms']) {
        $errors['terms'] = 'You must accept the terms';
    }
    
    // If no errors, save to database and redirect
    if (empty($errors)) {
        $db = initDatabase();
        
        $stmt = $db->prepare('
            INSERT INTO players (name, ssn, phone, terms_accepted)
            VALUES (:name, :ssn, :phone, :terms)
        ');
        
        $stmt->bindValue(':name', $formData['name']);
        $stmt->bindValue(':ssn', $formData['ssn']);
        $stmt->bindValue(':phone', $formData['phone']);
        $stmt->bindValue(':terms', $formData['terms'] ? 1 : 0, SQLITE3_INTEGER);
        
        $stmt->execute();
        $playerId = $db->lastInsertRowID();
        
        $_SESSION['player_id'] = $playerId;
        $_SESSION['flips_count'] = 0;
        
        header('Location: game.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - Memory Game</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/jquery.mask.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Player Registration</h1>
        
        <form method="post" action="" class="registration-form">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($formData['name']) ?>">
                <?php if (isset($errors['name'])): ?>
                    <span class="error"><?= $errors['name'] ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="ssn">Social Security Number:</label>
                <input type="text" id="ssn" name="ssn" placeholder="123.456.789-01" value="<?= htmlspecialchars($formData['ssn']) ?>">
                <?php if (isset($errors['ssn'])): ?>
                    <span class="error"><?= $errors['ssn'] ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="text" id="phone" name="phone" placeholder="(12) 34567-8901" value="<?= htmlspecialchars($formData['phone']) ?>">
                <?php if (isset($errors['phone'])): ?>
                    <span class="error"><?= $errors['phone'] ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group terms-group">
                <label>
                    <input type="checkbox" name="terms" value="yes" <?= $formData['terms'] ? 'checked' : '' ?>>
                    I accept the terms and conditions
                </label>
                <?php if (isset($errors['terms'])): ?>
                    <span class="error"><?= $errors['terms'] ?></span>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn">Continue to Game</button>
        </form>
    </div>
    
    <script>
        $(document).ready(function(){
            $('#ssn').mask('000.000.000-00');
            $('#phone').mask('(00) 00000-0000');
        });
    </script>
</body>
</html>