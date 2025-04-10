<?php
require_once 'database-init.php';
require 'vendor/autoload.php'; // Include PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$db = initDatabase();
$action = $_GET['action'] ?? '';

// Handle delete action
if ($action === 'delete' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    $db->exec('DELETE FROM game_results');
    $db->exec('DELETE FROM players');
    $message = 'All data has been deleted.';
}

// Handle export action
if ($action === 'export') {
    // Create a new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set headers
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Date/Time');
    $sheet->setCellValue('C1', 'Name');
    $sheet->setCellValue('D1', 'SSN');
    $sheet->setCellValue('E1', 'Phone');
    $sheet->setCellValue('F1', 'Terms Accepted');
    $sheet->setCellValue('G1', 'Flips Count');
    
    // Get data
    $query = '
        SELECT 
            p.id, 
            p.created_at, 
            p.name, 
            p.ssn, 
            p.phone, 
            p.terms_accepted, 
            gr.flips_count
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
        // Format date for Excel
        $formattedDate = date('d/m/Y H:i:s', strtotime($data['created_at']));
        
        $sheet->setCellValue('A' . $row, $data['id']);
        $sheet->setCellValue('B' . $row, $formattedDate);
        $sheet->setCellValue('C' . $row, $data['name']);
        $sheet->setCellValue('D' . $row, $data['ssn']);
        $sheet->setCellValue('E' . $row, $data['phone']);
        $sheet->setCellValue('F' . $row, $data['terms_accepted'] ? 'Yes' : 'No');
        $sheet->setCellValue('G' . $row, $data['flips_count'] ?? 'N/A');
        $row++;
    }
    
    // Create the XLSX file
    $writer = new Xlsx($spreadsheet);
    $filename = 'memory_game_data_' . date('Y-m-d_H-i-s') . '.xlsx';
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer->save('php://output');
    exit;
}

// Fetch all records for display
$query = '
    SELECT 
        p.id, 
        p.created_at, 
        p.name, 
        p.ssn, 
        p.phone, 
        p.terms_accepted, 
        gr.flips_count
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
    // Format the date/time in the desired format
    $row['formatted_date'] = date('d/m/Y H:i:s', strtotime($row['created_at']));
    $records[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Memory Game</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container admin-container">
        <h1>Administration</h1>
        
        <?php if (isset($message)): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>
        
        <div class="admin-actions">
            <a href="admin.php?action=export" class="btn export-btn">Export XLSX</a>
            <button class="btn delete-btn" onclick="confirmDelete()">Delete All Data</button>
        </div>
        
        <div class="data-table">
            <h2>Player Records (<?= count($records) ?>)</h2>
            
            <?php if (empty($records)): ?>
                <p>No records found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date/Time</th>
                            <th>Name</th>
                            <th>SSN</th>
                            <th>Phone</th>
                            <th>Terms</th>
                            <th>Flips</th>
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
                                <td><?= $record['terms_accepted'] ? 'Yes' : 'No' ?></td>
                                <td><?= $record['flips_count'] ?? 'N/A' ?></td>
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
            <h3>Confirm Deletion</h3>
            <p>Are you sure you want to delete all data? This action cannot be undone.</p>
            <form method="post" action="admin.php?action=delete">
                <input type="hidden" name="confirm" value="yes">
                <div class="dialog-buttons">
                    <button type="submit" class="btn delete-confirm-btn">Yes, Delete All</button>
                    <button type="button" class="btn cancel-btn" onclick="closeDialog()">Cancel</button>
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