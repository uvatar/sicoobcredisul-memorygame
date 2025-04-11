<?php
require_once 'database-init.php';
require 'vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


$timezone = new DateTimeZone('America/Porto_Velho'); 

$db = initDatabase();
$action = $_GET['action'] ?? '';
$message = '';


if ($action === 'delete' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    $db->exec('DELETE FROM game_results');
    $db->exec('DELETE FROM players');
    $message = 'Todos os dados foram apagados.';
}


if ($action === 'update_settings' && isset($_POST['max_flips'])) {
    $maxFlips = (int)$_POST['max_flips'];
    if ($maxFlips > 0) {
        $stmt = $db->prepare('UPDATE game_settings SET setting_value = :value, updated_at = CURRENT_TIMESTAMP WHERE setting_name = :name');
        $stmt->bindValue(':name', 'max_flips');
        $stmt->bindValue(':value', $maxFlips);
        $stmt->execute();
        $message = 'Configuração alterada.';
    } else {
        $message = 'Número máximo de flips deve ser maior que 0.';
    }
}


$stmt = $db->prepare('SELECT setting_value FROM game_settings WHERE setting_name = :name');
$stmt->bindValue(':name', 'max_flips');
$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
$maxFlips = $result ? (int)$result['setting_value'] : 12;


if ($action === 'export') {
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Data (UTC-4)');
    $sheet->setCellValue('C1', 'Nome');
    $sheet->setCellValue('D1', 'CPF');
    $sheet->setCellValue('E1', 'Telefone');
    $sheet->setCellValue('F1', 'Cooperado');
    $sheet->setCellValue('G1', 'Flips');
    $sheet->setCellValue('H1', 'Venceu');
    
    
    $query = '
        SELECT 
            p.id, 
            p.created_at, 
            p.name, 
            p.ssn, 
            p.phone, 
            p.terms_accepted, 
            gr.flips_count,
            gr.won
        FROM 
            players p
        LEFT JOIN 
            game_results gr ON p.id = gr.player_id
        ORDER BY 
            p.created_at DESC
    ';
    
    $result = $db->query($query);
    $row = 2;
    
    while ($data = $result->fetchArray(SQLITE3_ASSOC)) {
        
        $date = new DateTime($data['created_at'], new DateTimeZone('UTC'));
        $date->setTimezone($timezone);
        $formattedDate = $date->format('d/m/Y H:i:s');
        
        $sheet->setCellValue('A' . $row, $data['id']);
        $sheet->setCellValue('B' . $row, $formattedDate);
        $sheet->setCellValue('C' . $row, $data['name']);
        $sheet->setCellValue('D' . $row, $data['ssn']);
        $sheet->setCellValue('E' . $row, $data['phone']);
        $sheet->setCellValue('F' . $row, $data['terms_accepted'] ? 'Sim' : 'Não');
        $sheet->setCellValue('G' . $row, $data['flips_count'] ?? 'N/A');
        $sheet->setCellValue('H' . $row, isset($data['won']) ? ($data['won'] ? 'Sim' : 'Não') : 'N/A');
        $row++;
    }
    
    
    $writer = new Xlsx($spreadsheet);
    $filename = 'jogodamemoria_resultados_' . date('Y-m-d_H-i-s') . '.xlsx';
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer->save('php://output');
    exit;
}


$query = '
    SELECT 
        p.id, 
        p.created_at, 
        p.name, 
        p.ssn, 
        p.phone, 
        p.terms_accepted, 
        gr.flips_count,
        gr.won
    FROM 
        players p
    LEFT JOIN 
        game_results gr ON p.id = gr.player_id
    ORDER BY 
        p.created_at DESC
';

$result = $db->query($query);
$records = [];

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    
    $date = new DateTime($row['created_at'], new DateTimeZone('UTC'));
    $date->setTimezone($timezone);
    $row['formatted_date'] = $date->format('d/m/Y H:i:s');
    $records[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container admin-container">
        <h1>Administração</h1>
        
        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>
        
        <div class="settings-section">
            <h2>Configurações</h2>
            <form method="post" action="admin.php?action=update_settings" class="settings-form">
                <div class="form-group">
                    <label class="admin-flips" for="max_flips">Flips</label>
                    <input type="number" id="max_flips" name="max_flips" value="<?= $maxFlips ?>" min="1" required>
                    <p class="field-description">Número máximo de flips permitidos antes do fim do jogo</p>
                </div>
                <button type="submit" class="btn admin-btn">Atualizar</button>
            </form>
        </div>
        
        <div class="admin-actions">
            <a href="index.php" class="btn admin-btn">Voltar para o jogo</a>
            <a href="admin.php?action=export" class="btn export-btn">Exportar</a>
            <button class="btn delete-btn" onclick="confirmDelete()">Apagar tudo</button>
        </div>
        
        <div class="data-table">
            <h2>Participantes (<?= count($records) ?>)</h2>
            
            <?php if (empty($records)): ?>
                <p>Nenhum participantes encontrado.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Telefone</th>
                            <th>Cooperado</th>
                            <th>Flips</th>
                            <th>Venceu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $record): ?>
                            <tr>
                                <td><?= $record['id'] ?></td>
                                <td><?= $record['formatted_date'] ?></td>
                                <td><?= htmlspecialchars($record['name']) ?></td>
                                <td><?= htmlspecialchars($record['ssn']) ?></td>
                                <td><?= htmlspecialchars($record['phone']) ?></td>
                                <td><?= $record['terms_accepted'] ? 'Sim' : 'Não' ?></td>
                                <td><?= $record['flips_count'] ?? 'N/A' ?></td>
                                <td><?= isset($record['won']) ? ($record['won'] ? 'Sim' : 'Não') : 'N/A' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>
    
    <!-- Delete Confirmation Dialog -->
    <div id="delete-dialog" class="dialog" style="display: none;">
        <div class="dialog-content">
            <h3>Confirmar a exclusão</h3>
            <p>Tem certeza que quer apagar todos os dados? Essa ação não pode ser desfeita.</p>
            <form method="post" action="admin.php?action=delete">
                <input type="hidden" name="confirm" value="yes">
                <div class="dialog-buttons">
                    <button type="submit" class="btn delete-confirm-btn">Excluir</button>
                    <button type="button" class="btn admin-btn" onclick="closeDialog()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function confirmDelete() {
            document.getElementById('delete-dialog').style.display = 'flex';
        }
        
        function closeDialog() {
            document.getElementById('delete-dialog').style.display = 'none';
        }
    </script>
</body>
</html>