<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAdmin(); $pageTitle = 'Bookings'; require __DIR__ . '/../includes/admin-header.php';

$db = Database::getInstance()->getConnection();

// Update booking status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { $msg = 'Invalid security token'; }
    else {
        $id = (int)($_POST['id'] ?? 0);
        if ($_POST['action'] === 'confirm') {
            $db->prepare("UPDATE bookings SET status='confirmed', notes=? WHERE id=?")->execute([sanitize($_POST['notes'] ?? ''), $id]);
            logActivity('booking_confirmed', 'Booking #'.$id.' confirmed');
        } elseif ($_POST['action'] === 'complete') {
            $db->prepare("UPDATE bookings SET status='completed', notes=? WHERE id=?")->execute([sanitize($_POST['notes'] ?? ''), $id]);
            logActivity('booking_completed', 'Booking #'.$id.' completed');
        } elseif ($_POST['action'] === 'cancel') {
            $db->prepare("UPDATE bookings SET status='cancelled', notes=? WHERE id=?")->execute([sanitize($_POST['notes'] ?? ''), $id]);
            logActivity('booking_cancelled', 'Booking #'.$id.' cancelled');
        } elseif ($_POST['action'] === 'delete') {
            $db->prepare("DELETE FROM bookings WHERE id=?")->execute([$id]);
            logActivity('booking_deleted', 'Booking #'.$id.' deleted');
        }
    }
}

$statusFilter = $_GET['status'] ?? '';
$params = [];
if ($statusFilter && in_array($statusFilter, ['pending','confirmed','completed','cancelled'])) {
    $where = "WHERE status = ?";
    $params[] = $statusFilter;
} else {
    $where = '';
}
$bookings = $db->prepare("SELECT * FROM bookings $where ORDER BY created_at DESC");
$bookings->execute($params);
$bookings = $bookings->fetchAll();
?>
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">Bookings</h1>
        <p class="page-subtitle">Consultation call requests from the website</p>
    </div>
    <div class="page-actions">
        <select class="form-select" style="width:auto;min-width:140px" onchange="var s=this.value;window.location.href='<?= BASE_URL ?>admin-bookings'+(s?'?status='+s:'')">
            <option value="">All Status</option>
            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="confirmed" <?= $statusFilter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
            <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
    </div>
</div>

<div class="table-container reveal">
    <?php if (empty($bookings)): ?>
        <p style="text-align:center;padding:48px;color:var(--text-muted)">No bookings found.</p>
    <?php else: ?>
    <table class="table">
        <thead><tr><th>Client</th><th>Contact</th><th>Preferred Date</th><th>Status</th><th>Submitted</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:34px;height:34px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:13px"><?= strtoupper($b['name'][0]) ?></div>
                            <span style="font-weight:600"><?= htmlspecialchars($b['name']) ?></span>
                        </div>
                    </td>
                    <td>
                        <div style="font-size:13px"><?= htmlspecialchars($b['email']) ?></div>
                        <?php if ($b['phone']): ?><div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($b['phone']) ?></div><?php endif; ?>
                    </td>
                    <td>
                        <?php if ($b['preferred_date']): ?>
                            <div style="font-size:13px;font-weight:600"><?= date('M j, Y', strtotime($b['preferred_date'])) ?></div>
                            <?php if ($b['preferred_time']): ?><div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($b['preferred_time']) ?></div><?php endif; ?>
                        <?php else: ?>
                            <span style="color:var(--text-muted)">Not specified</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-<?= $b['status'] === 'confirmed' ? 'primary' : ($b['status'] === 'completed' ? 'success' : ($b['status'] === 'cancelled' ? 'error' : 'warning')) ?>">
                            <?= ucfirst($b['status']) ?>
                        </span>
                    </td>
                    <td style="font-size:13px;color:var(--text-muted)"><?= date('M j, Y g:i A', strtotime($b['created_at'])) ?></td>
                    <td>
                        <div style="display:flex;gap:4px">
                            <button class="btn btn-ghost btn-icon btn-sm" onclick="viewBooking(<?= htmlspecialchars(json_encode($b)) ?>)" title="View"><i data-lucide="eye" size="14"></i></button>
                            <?php if ($b['status'] === 'pending'): ?>
                                <button class="btn btn-ghost btn-icon btn-sm" style="color:#2196F3" onclick="updateBooking(<?= $b['id'] ?>,'confirm')" title="Confirm"><i data-lucide="check" size="14"></i></button>
                            <?php endif; ?>
                            <?php if ($b['status'] === 'confirmed'): ?>
                                <button class="btn btn-ghost btn-icon btn-sm" style="color:#4CAF50" onclick="updateBooking(<?= $b['id'] ?>,'complete')" title="Mark Complete"><i data-lucide="check-circle" size="14"></i></button>
                            <?php endif; ?>
                            <?php if ($b['status'] !== 'cancelled' && $b['status'] !== 'completed'): ?>
                                <button class="btn btn-ghost btn-icon btn-sm" style="color:#FF9800" onclick="updateBooking(<?= $b['id'] ?>,'cancel')" title="Cancel"><i data-lucide="x" size="14"></i></button>
                            <?php endif; ?>
                            <button class="btn btn-ghost btn-icon btn-sm" style="color:#F44336" onclick="if(confirm('Delete this booking?'))updateBooking(<?= $b['id'] ?>,'delete')" title="Delete"><i data-lucide="trash-2" size="14"></i></button>
                        </div>
                    </td>
                </tr>
                <?php if ($b['message']): ?>
                <tr style="background:var(--bg-light)">
                    <td colspan="6" style="padding:4px 16px 12px 56px;font-size:13px;color:var(--text-secondary)">
                        <strong>Message:</strong> <?= htmlspecialchars($b['message']) ?>
                        <?php if ($b['notes']): ?><br><strong>Notes:</strong> <?= htmlspecialchars($b['notes']) ?><?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<form method="POST" id="bookingForm" style="display:none"><input type="hidden" name="id" id="bookingId"><input type="hidden" name="action" id="bookingAction"><input type="hidden" name="notes" id="bookingNotes"><input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>"></form>

<script>
function viewBooking(b) {
    var msg = 'Name: ' + b.name + '\nEmail: ' + b.email + (b.phone ? '\nPhone: ' + b.phone : '') + (b.preferred_date ? '\nDate: ' + b.preferred_date : '') + (b.preferred_time ? '\nTime: ' + b.preferred_time : '') + (b.message ? '\n\nMessage:\n' + b.message : '') + '\n\nStatus: ' + b.status + '\nSubmitted: ' + b.created_at;
    alert(msg);
}
function updateBooking(id, action) {
    var notes = '';
    if (action === 'confirm' || action === 'cancel') {
        notes = prompt('Add notes (optional):');
        if (notes === null) return;
    }
    document.getElementById('bookingId').value = id;
    document.getElementById('bookingAction').value = action;
    document.getElementById('bookingNotes').value = notes || '';
    document.getElementById('bookingForm').submit();
}
</script>

<script src="<?= BASE_URL ?>assets/js/admin.js"></script>
<script src="<?= BASE_URL ?>assets/js/animations.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>lucide.createIcons();</script>
</body></html>
