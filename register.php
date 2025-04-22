<?php
require_once 'database-init.php';
session_start();

$errors = [];
$formData = [
    'name' => '',
    'ssn' => '',
    'phone' => '',
    'terms' => ''  
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $formData['name'] = trim($_POST['name'] ?? '');
    $formData['ssn'] = trim($_POST['ssn'] ?? '');
    $formData['phone'] = trim($_POST['phone'] ?? '');
    $formData['terms'] = $_POST['terms'] ?? '';  
    
    
    if (empty($formData['name'])) {
        $errors['name'] = 'Nome obrigatório';
    }
    
    
    if (empty($formData['ssn'])) {
        $errors['ssn'] = 'CPF obrigatório';
    } elseif (!preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $formData['ssn'])) {
        $errors['ssn'] = 'CPF deve conter todos os dígitos';
    } else {
        
        $db = initDatabase();
        $stmt = $db->prepare('SELECT id FROM players WHERE ssn = :ssn');
        $stmt->bindValue(':ssn', $formData['ssn']);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        if ($result) {
            $errors['ssn'] = 'Parece que você já participou!';
        }
    }
    
    
    if (empty($formData['phone'])) {
        $errors['phone'] = 'Telefone obrigatório';
    } elseif (!preg_match('/^\(\d{2}\) \d{5}-\d{4}$/', $formData['phone'])) {
        $errors['phone'] = 'Telefone deve conter todos os dígitos';
    }
    
    if (empty($formData['terms'])) {
        $errors['terms'] = 'Selecione uma opção';
    }
    
    
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

include('header.php');
?>

<body class="registro">
    <div class="container ctn-registro">
    <img src="images/img-registro.svg" alt="Queremos conhecer você" class="img-registro">
        <form method="post" action="" class="registration-form">
            <div class="form-group">
                <label for="name">Nome</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($formData['name']) ?>" class="<?= isset($errors['name']) ? 'has-error' : '' ?>">
                <?php if (isset($errors['name'])): ?>
                    <div class="tooltip"><?= $errors['name'] ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="ssn">CPF</label>
                <input type="text" id="ssn" name="ssn" placeholder="###.###.###-##" value="<?= htmlspecialchars($formData['ssn']) ?>" class="<?= isset($errors['ssn']) ? 'has-error' : '' ?>">
                <?php if (isset($errors['ssn'])): ?>
                    <div class="tooltip"><?= $errors['ssn'] ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="phone">Telefone</label>
                <input type="text" id="phone" name="phone" placeholder="(##) #####-####" value="<?= htmlspecialchars($formData['phone']) ?>" class="<?= isset($errors['phone']) ? 'has-error' : '' ?>">
                <?php if (isset($errors['phone'])): ?>
                    <div class="tooltip"><?= $errors['phone'] ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group terms-group">
                <label>É cooperado da Sicoob Credisul?</label>
                <div class="radio-group <?= isset($errors['terms']) ? 'has-error' : '' ?>">
                    <label class="radio-label">
                        <input type="radio" name="terms" value="yes" <?= $formData['terms'] === 'yes' ? 'checked' : '' ?>>
                        Sim
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="terms" value="no" <?= $formData['terms'] === 'no' ? 'checked' : '' ?>>
                        Não
                    </label>
                </div>
                <?php if (isset($errors['terms'])): ?>
                    <div class="tooltip"><?= $errors['terms'] ?></div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn btn-registro">Começar</button>
        </form>
    </div>
    
    <script>
        $(document).ready(function(){
            $('#ssn').mask('000.000.000-00');
            $('#phone').mask('(00) 00000-0000');
            
            
            setTimeout(function() {
                $('.tooltip').fadeOut(300);
            }, 2500);
        });
    </script>
</body>
</html>