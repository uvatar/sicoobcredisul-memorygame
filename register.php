<?php
require_once 'database-init.php';
session_start();

$errors = [];
$formData = [
    'name' => '',
    'ssn' => '',
    'phone' => '',
    'terms' => ''  // Changed from boolean to string
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $formData['name'] = trim($_POST['name'] ?? '');
    $formData['ssn'] = trim($_POST['ssn'] ?? '');
    $formData['phone'] = trim($_POST['phone'] ?? '');
    $formData['terms'] = $_POST['terms'] ?? '';  // Get the selected radio value
    
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
    
    if (empty($formData['terms'])) {
        $errors['terms'] = 'Please select an option';
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
        $stmt->bindValue(':terms', $formData['terms'] === 'yes' ? 1 : 0, SQLITE3_INTEGER);
        
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
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($formData['name']) ?>" class="<?= isset($errors['name']) ? 'has-error' : '' ?>">
                <?php if (isset($errors['name'])): ?>
                    <div class="tooltip"><?= $errors['name'] ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="ssn">Social Security Number:</label>
                <input type="text" id="ssn" name="ssn" placeholder="123.456.789-01" value="<?= htmlspecialchars($formData['ssn']) ?>" class="<?= isset($errors['ssn']) ? 'has-error' : '' ?>">
                <?php if (isset($errors['ssn'])): ?>
                    <div class="tooltip"><?= $errors['ssn'] ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="text" id="phone" name="phone" placeholder="(12) 34567-8901" value="<?= htmlspecialchars($formData['phone']) ?>" class="<?= isset($errors['phone']) ? 'has-error' : '' ?>">
                <?php if (isset($errors['phone'])): ?>
                    <div class="tooltip"><?= $errors['phone'] ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group terms-group">
                <label>Do you accept our terms?</label>
                <div class="radio-group <?= isset($errors['terms']) ? 'has-error' : '' ?>">
                    <label class="radio-label">
                        <input type="radio" name="terms" value="yes" <?= $formData['terms'] === 'yes' ? 'checked' : '' ?>>
                        Yes
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="terms" value="no" <?= $formData['terms'] === 'no' ? 'checked' : '' ?>>
                        No
                    </label>
                </div>
                <?php if (isset($errors['terms'])): ?>
                    <div class="tooltip"><?= $errors['terms'] ?></div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn">Continue to Game</button>
        </form>
    </div>
    
    <script>
        $(document).ready(function(){
            $('#ssn').mask('000.000.000-00');
            $('#phone').mask('(00) 00000-0000');
            
            // Auto-hide tooltips after 1.5 seconds
            setTimeout(function() {
                $('.tooltip').fadeOut(300);
            }, 1500);
        });
    </script>
</body>
</html>