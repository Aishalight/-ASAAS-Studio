<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAdmin(); $pageTitle = 'Messages';

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    header('Content-Type: application/json');
    $reply = sanitize($_POST['reply']);
    $threadId = (int)($_POST['thread_id'] ?? 0);
    if ($reply && $threadId) {
        $userStmt = $db->prepare("SELECT sender_id FROM messages WHERE thread_id = ? AND sender_id != ? ORDER BY created_at ASC LIMIT 1");
        $userStmt->execute([$threadId, getCurrentUserId()]);
        $receiverId = (int)$userStmt->fetchColumn();
        if (!$receiverId) {
            $userStmt2 = $db->prepare("SELECT receiver_id FROM messages WHERE thread_id = ? AND sender_id != ? ORDER BY created_at ASC LIMIT 1");
            $userStmt2->execute([$threadId, getCurrentUserId()]);
            $receiverId = (int)$userStmt2->fetchColumn();
        }
        if ($receiverId) {
            $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, subject, message, thread_id, is_admin) VALUES (?, ?, 'Admin Reply', ?, ?, 1)");
            $stmt->execute([getCurrentUserId(), $receiverId, $reply, $threadId]);
            createNotification($receiverId, 'message', 'Admin Reply', $reply, BASE_URL . 'messages');
            logActivity('message_reply', 'Admin replied to message', [], 'info');
            echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Could not find recipient']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing fields']);
    }
    exit;
}

require __DIR__ . '/../includes/admin-header.php';

$adminId = getCurrentUserId();
$threads = $db->prepare("
    SELECT m.*, u.name as sender_name, u.email as sender_email, u.avatar as sender_avatar, u.last_activity as sender_last_activity,
    (SELECT COUNT(*) FROM messages WHERE thread_id = m.thread_id AND receiver_id = ? AND is_read = 0) as unread
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.receiver_id = ? OR m.sender_id = ?
    GROUP BY m.thread_id
    ORDER BY m.created_at DESC
    LIMIT 20
");
$threads->execute([$adminId, $adminId, $adminId]);
$threads = $threads->fetchAll();

$messages = [];
$currentThreadId = null;
if (!empty($threads)) {
    $currentThreadId = $threads[0]['thread_id'];
    $stmt = $db->prepare("SELECT m.*, u.name as sender_name, u.avatar as sender_avatar, u.last_activity as sender_last_activity FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.thread_id = ? ORDER BY m.created_at ASC");
    $stmt->execute([$currentThreadId]);
    $messages = $stmt->fetchAll();
    $db->prepare("UPDATE messages SET is_read = 1, read_at = NOW() WHERE thread_id = ? AND receiver_id = ?")->execute([$currentThreadId, $adminId]);
}
?>
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">Messages</h1>
        <p class="page-subtitle">Customer inquiries and support conversations</p>
    </div>
    <div class="page-actions">
        <button type="button" id="mark-all-read-btn" class="btn btn-sm btn-outline" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border:1px solid var(--border);border-radius:var(--radius-md);background:var(--bg-white);color:var(--text-secondary);font-size:13px;font-weight:600;cursor:pointer;transition:all 0.2s" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-secondary)'"><i data-lucide="check-check" size="16"></i> Mark all read</button>
        <select class="form-select" style="width:auto;min-width:140px"><option>All Messages</option><option>Unread</option><option>Read</option></select>
    </div>
</div>

<div class="messages-layout reveal">
    <div class="messages-list">
        <div class="messages-list-header">Conversations <?php if (!empty($threads)): ?><span class="badge badge-primary" style="margin-left:8px"><?= count($threads) ?></span><?php endif; ?></div>
        <?php if (empty($threads)): ?>
            <div style="text-align:center;padding:40px 20px;color:var(--text-muted);font-size:14px">No conversations yet.</div>
        <?php else: foreach ($threads as $idx => $t): ?>
            <div class="message-thread <?= $idx === 0 ? 'active' : ($t['unread'] > 0 ? '' : 'read') ?>" data-thread="<?= $t['thread_id'] ?>" data-sender-id="<?= $t['sender_id'] ?>" data-sender-name="<?= htmlspecialchars($t['sender_name'] ?? 'User') ?>" data-sender-email="<?= htmlspecialchars($t['sender_email'] ?? '') ?>" data-sender-avatar="<?= htmlspecialchars($t['sender_avatar'] ?? '') ?>" data-sender-lastactivity="<?= htmlspecialchars($t['sender_last_activity'] ?? '') ?>">
                <?php $tAvatar = !empty($t['sender_avatar']) ? BASE_URL . $t['sender_avatar'] : ''; ?>
                <div style="position:relative;flex-shrink:0">
                    <div class="message-thread-avatar" style="overflow:hidden;<?= $tAvatar ? 'background:none' : '' ?>">
                        <?php if ($tAvatar): ?>
                            <img src="<?= $tAvatar ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover">
                        <?php else: ?>
                            <?= strtoupper(($t['sender_name'] ?? 'U')[0]) ?>
                        <?php endif; ?>
                    </div>
                    <span style="position:absolute;bottom:0;right:0;width:10px;height:10px;border-radius:50%;border:2px solid var(--bg-white);background:<?= isOnline($t['sender_last_activity'] ?? '') ? '#22c55e' : '#9ca3af' ?>"></span>
                </div>
                <div class="message-thread-info">
                    <div class="message-thread-name">
                        <span><?= htmlspecialchars($t['sender_name'] ?? 'User') ?></span>
                        <span class="message-thread-time"><?= timeAgo($t['created_at']) ?></span>
                    </div>
                    <div class="message-thread-preview"><?= htmlspecialchars(truncate($t['message'] ?? '', 60)) ?></div>
                </div>
                <?php if ($t['unread'] > 0): ?><div class="message-thread-unread"></div><?php endif; ?>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <div class="chat-view" id="chat-view">
        <div class="chat-header">
            <div style="display:flex;align-items:center;gap:10px">
                <div id="chat-avatar" style="width:36px;height:36px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:14px;overflow:hidden;flex-shrink:0">
                    <?php if (!empty($threads) && !empty($threads[0]['sender_avatar'])): ?>
                        <img src="<?= BASE_URL . $threads[0]['sender_avatar'] ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover">
                    <?php else: ?>
                        <?= strtoupper(($threads[0]['sender_name'] ?? '?')[0]) ?>
                    <?php endif; ?>
                </div>
                <div>
                    <h6 style="font-weight:700" id="chat-name"><?= !empty($threads) ? htmlspecialchars($threads[0]['sender_name']) : 'No conversation selected' ?></h6>
                    <p style="font-size:12px;color:var(--text-muted)" id="chat-email"><?= !empty($threads) ? htmlspecialchars($threads[0]['sender_email']) : '' ?></p>
                </div>
            </div>
            <div style="margin-left:auto;display:flex;gap:4px">
                <button type="button" id="chat-delete-btn" style="background:none;border:none;cursor:pointer;color:var(--text-secondary);padding:6px;border-radius:8px;transition:all 0.2s" title="Delete conversation" onmouseover="this.style.background='rgba(239,68,68,0.1)';this.style.color='#ef4444'" onmouseout="this.style.background='none';this.style.color='var(--text-secondary)'"><i data-lucide="trash-2" size="18"></i></button>
            </div>
        </div>
        <div class="chat-messages" id="chat-messages">
            <?php if (empty($messages)): ?>
                <div style="text-align:center;padding:60px 24px;color:var(--text-muted);font-size:14px" id="chat-empty">Select a conversation to start.</div>
            <?php else: foreach ($messages as $msg): ?>
                <div class="chat-bubble <?= $msg['sender_id'] == getCurrentUserId() ? 'sent' : 'received' ?>" data-msg-id="<?= $msg['id'] ?>" style="display:flex;align-items:flex-end;gap:8px;<?= $msg['sender_id'] == getCurrentUserId() ? 'flex-direction:row-reverse' : '' ?>">
                    <?php if ($msg['sender_id'] != getCurrentUserId()): ?>
                        <div style="width:28px;height:28px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:11px;overflow:hidden;flex-shrink:0">
                            <?php if (!empty($msg['sender_avatar'])): ?>
                                <img src="<?= BASE_URL . $msg['sender_avatar'] ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover">
                            <?php else: ?>
                                <?= strtoupper(($msg['sender_name'] ?? '?')[0]) ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <?= htmlspecialchars($msg['message']) ?>
                        <div class="chat-bubble-time"><?= timeAgo($msg['created_at']) ?></div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
        <form id="chat-form" class="chat-input" onsubmit="return false;">
            <input type="text" id="chat-input-field" class="form-input" placeholder="Type your reply..." autocomplete="off">
            <button type="button" id="chat-send-btn" class="btn btn-primary btn-icon"><i data-lucide="send" size="18"></i></button>
        </form>
    </div>
</div>

<script>
(function(){
    var currentThreadId = <?= json_encode($currentThreadId) ?>;
    var currentUserId = <?= json_encode(getCurrentUserId()) ?>;
    var pollInterval = null;
    var chatMessages = document.getElementById('chat-messages');
    var chatForm = document.getElementById('chat-form');
    var chatInput = document.getElementById('chat-input-field');
    var chatSendBtn = document.getElementById('chat-send-btn');
    var threadElements = document.querySelectorAll('.message-thread');

    function scrollToBottom() {
        if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    scrollToBottom();

    function formatTime(dateStr) {
        var d = new Date(dateStr.length === 10 ? dateStr + 'T00:00:00+01:00' : dateStr.replace(' ', 'T') + '+01:00');
        var now = new Date();
        var diff = Math.floor((now - d) / 1000);
        if (diff < 0) diff = 0;
        if (diff < 60) return 'Just now';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function createBubbleHTML(msg) {
        var isSent = msg.sender_id == currentUserId;
        var time = formatTime(msg.created_at);
        var avatarHTML = '';
        if (!isSent) {
            var av = msg.sender_avatar || '';
            avatarHTML = '<div style="width:28px;height:28px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:11px;overflow:hidden;flex-shrink:0' + (av ? ';background:none' : '') + '">';
            if (av) {
                avatarHTML += '<img src="' + BASE_URL + av + '" alt="Avatar" style="width:100%;height:100%;object-fit:cover">';
            } else {
                avatarHTML += (msg.sender_name || '?')[0].toUpperCase();
            }
            avatarHTML += '</div>';
        }
        return '<div class="chat-bubble ' + (isSent ? 'sent' : 'received') + '" data-msg-id="' + msg.id + '" style="display:flex;align-items:flex-end;gap:8px;' + (isSent ? 'flex-direction:row-reverse' : '') + '">' + avatarHTML + '<div><div>' + escapeHtml(msg.message) + '</div><div class="chat-bubble-time">' + time + '</div></div></div>';
    }

    function sendMessage() {
        var text = chatInput.value.trim();
        if (!text || !currentThreadId) return;
        chatInput.value = '';
        chatSendBtn.disabled = true;

        var fd = new FormData();
        fd.append('reply', text);
        fd.append('thread_id', currentThreadId);

        fetch(BASE_URL + 'admin/messages.php?action=send_reply', { method: 'POST', body: fd })
            .then(function(r) { return r.text().then(function(t) { return { ok: r.ok, text: t }; }); })
            .then(function(res) {
                var empty = document.getElementById('chat-empty');
                if (empty) empty.remove();
                var html = '<div class="chat-bubble sent" data-msg-id="new-' + Date.now() + '" style="display:flex;align-items:flex-end;gap:8px;flex-direction:row-reverse"><div><div>' + escapeHtml(text) + '</div><div class="chat-bubble-time">Just now</div></div></div>';
                chatMessages.insertAdjacentHTML('beforeend', html);
                scrollToBottom();
                chatSendBtn.disabled = false;
            })
            .catch(function() { chatSendBtn.disabled = false; chatInput.value = text; });
    }

    chatSendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    });

    function pollMessages() {
        if (!currentThreadId) return;
        fetch(BASE_URL + 'api/index.php?action=get_messages&thread_id=' + currentThreadId)
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (!d.success || !d.data || !d.data.messages) return;
                var msgs = d.data.messages;
                var empty = document.getElementById('chat-empty');
                if (empty && msgs.length > 0) empty.remove();
                var newCount = 0;
                msgs.forEach(function(msg) {
                    if (!chatMessages.querySelector('[data-msg-id="' + msg.id + '"]')) {
                        chatMessages.insertAdjacentHTML('beforeend', createBubbleHTML(msg));
                        newCount++;
                    }
                });
                if (newCount > 0) {
                    scrollToBottom();
                }
            })
            .catch(function(){});
    }

    if (pollInterval) clearInterval(pollInterval);
    pollInterval = setInterval(pollMessages, 3000);

    var deleteBtn = document.getElementById('chat-delete-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (!currentThreadId) return;
            if (!confirm('Delete this entire conversation? This cannot be undone.')) return;
            var fd = new FormData();
            fd.append('thread_id', currentThreadId);
            fetch(BASE_URL + 'api/index.php?action=delete_thread', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success) {
                        var threadEl = document.querySelector('.message-thread[data-thread="' + currentThreadId + '"]');
                        if (threadEl) threadEl.remove();
                        chatMessages.innerHTML = '<div style="text-align:center;padding:60px 24px;color:var(--text-muted);font-size:14px" id="chat-empty">Select a conversation to start.</div>';
                        document.getElementById('chat-name').textContent = 'No conversation selected';
                        document.getElementById('chat-email').textContent = '';
                        currentThreadId = null;
                    }
                });
        });
    }

    var markAllReadBtn = document.getElementById('mark-all-read-btn');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function() {
            fetch(BASE_URL + 'api/index.php?action=mark_all_messages_read', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: '' })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success) {
                        document.querySelectorAll('.message-thread-unread').forEach(function(el) { el.style.display = 'none'; });
                        document.querySelectorAll('.message-thread').forEach(function(el) { el.classList.remove('unread'); });
                    }
                });
        });
    }

    threadElements.forEach(function(thread) {
        thread.addEventListener('click', function() {
            var threadId = this.getAttribute('data-thread');
            var senderName = this.getAttribute('data-sender-name');
            var senderEmail = this.getAttribute('data-sender-email');
            var senderAvatar = this.getAttribute('data-sender-avatar');

            threadElements.forEach(function(t) { t.classList.remove('active'); });
            this.classList.add('active');

            var unread = this.querySelector('.message-thread-unread');
            if (unread) unread.style.display = 'none';

            if (threadId == currentThreadId) return;

            currentThreadId = threadId;
            chatMessages.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-muted);font-size:14px">Loading messages...</div>';

            document.getElementById('chat-name').textContent = senderName;
            document.getElementById('chat-email').textContent = senderEmail;

            var avatarEl = document.getElementById('chat-avatar');
            if (senderAvatar) {
                avatarEl.style.background = 'none';
                avatarEl.innerHTML = '<img src="' + BASE_URL + senderAvatar + '" alt="Avatar" style="width:100%;height:100%;object-fit:cover">';
            } else {
                avatarEl.style.background = '';
                avatarEl.textContent = senderName[0].toUpperCase();
            }

            fetch(BASE_URL + 'api/index.php?action=get_messages&thread_id=' + threadId)
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success && d.data) {
                        chatMessages.innerHTML = '';
                        if (!d.data.messages || d.data.messages.length === 0) {
                            chatMessages.innerHTML = '<div style="text-align:center;padding:60px 24px;color:var(--text-muted);font-size:14px" id="chat-empty">No messages yet.</div>';
                        } else {
                            d.data.messages.forEach(function(msg) {
                                chatMessages.insertAdjacentHTML('beforeend', createBubbleHTML(msg));
                            });
                        }
                        scrollToBottom();
                    }
                });

            fetch(BASE_URL + 'api/index.php?action=mark_all_messages_read', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'thread_id=' + currentThreadId }).catch(function(){});
        });
    });
})();
</script>

<script src="<?= BASE_URL ?>assets/js/admin.js"></script>
<script src="<?= BASE_URL ?>assets/js/animations.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>lucide.createIcons();</script>
</body></html>
