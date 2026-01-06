<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['shifts']) && is_array($_POST['shifts'])) {
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE shifts SET name = ?, start_time = ?, end_time = ? WHERE id = ?");
            
            foreach ($_POST['shifts'] as $shift) {
                $id = intval($shift['id']);
                $name = sanitize($shift['name']);
                $start_time = $shift['start_time'];
                $end_time = $shift['end_time'];
                
                $stmt->execute([$name, $start_time, $end_time, $id]);
            }
            
            $pdo->commit();
            
            // Success parameter could be added for a toast notification
            redirect(public_url('settings'));
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            die("Ayarlar güncellenirken hata oluştu: " . $e->getMessage());
        }
    } else {
         redirect(public_url('settings'));
    }
} else {
    redirect(public_url('settings'));
}
?>
