<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAuth();
$pageTitle = 'Messages';
$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = sanitize($_POST['message']);
    $admStmt = $db->query("SELECT id FROM users WHERE role = 'admin' ORDER BY id ASC LIMIT 1");
    $receiverId = (int)$admStmt->fetchColumn() ?: 1;
    if ($message) {
        $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, subject, message, is_admin) VALUES (?, ?, 'User Message', ?, 0)");
        $stmt->execute([getCurrentUserId(), $receiverId, $message]);
        logActivity('message_sent', 'User sent a message', [], 'info');
        $sent = true;
    }
}

$threads = $db->prepare("
    SELECT m.*, u.name as sender_name, u.avatar as sender_avatar, u.last_activity as sender_last_activity,
    (SELECT COUNT(*) FROM messages WHERE thread_id = m.thread_id AND receiver_id = ? AND is_read = 0) as unread
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.sender_id = ? OR m.receiver_id = ?
    GROUP BY m.thread_id
    ORDER BY m.created_at DESC
    LIMIT 20
");
$threads->execute([getCurrentUserId(), getCurrentUserId(), getCurrentUserId()]);
$threads = $threads->fetchAll();

$messages = [];
$currentThreadId = null;
if (!empty($threads)) {
    $currentThreadId = $threads[0]['thread_id'];
    $stmt = $db->prepare("SELECT m.*, u.name as sender_name, u.avatar as sender_avatar, u.last_activity as sender_last_activity FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.thread_id = ? ORDER BY m.created_at ASC");
    $stmt->execute([$currentThreadId]);
    $messages = $stmt->fetchAll();
    $db->prepare("UPDATE messages SET is_read = 1, read_at = NOW() WHERE thread_id = ? AND receiver_id = ?")->execute([$currentThreadId, getCurrentUserId()]);
}

$adminUser = $db->query("SELECT id, name, avatar FROM users WHERE role = 'admin' ORDER BY id ASC LIMIT 1")->fetch();
$adminId = $adminUser ? (int)$adminUser['id'] : 1;
$adminName = $adminUser['name'] ?? 'ASAAS Studio Support';
$adminAvatar = $adminUser['avatar'] ?? '';
require __DIR__ . '/../includes/user-header.php';
?>
<style>
.messages-layout{display:grid;grid-template-columns:300px 1fr;background:var(--bg-white);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:1px solid var(--border);overflow:hidden;min-height:520px}
.messages-list{border-right:1px solid var(--border);overflow-y:auto;background:var(--bg-white)}
.messages-list-header{padding:16px 20px;font-weight:700;font-size:14px;border-bottom:1px solid var(--border);position:sticky;top:0;background:var(--bg-white);z-index:2}
.message-thread{display:flex;align-items:center;gap:12px;padding:14px 20px;cursor:pointer;transition:background 0.15s;border-bottom:1px solid var(--border)}
.message-thread:hover{background:var(--bg-light)}
.message-thread.active{background:rgba(232,99,42,0.04)}
.message-thread-avatar{width:38px;height:38px;border-radius:50%;min-width:38px;background:var(--primary);color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px}
.message-thread-info{flex:1;overflow:hidden}
.message-thread-name{display:flex;justify-content:space-between;align-items:center;font-size:14px;font-weight:600}
.message-thread-time{font-size:11px;font-weight:400;color:var(--text-muted)}
.message-thread-preview{font-size:13px;color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:2px}
.message-thread-unread{width:8px;height:8px;border-radius:50%;background:var(--primary);min-width:8px}
.chat-view{display:flex;flex-direction:column}
.chat-header{padding:16px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.chat-header-info{display:flex;align-items:center;gap:12px}
.chat-header-info h6{font-weight:700;font-size:15px}
.chat-header-status{font-size:12px;color:var(--success)}
.chat-header-actions{display:flex;gap:4px;margin-left:auto}
.btn-danger-outline{background:none;border:none;cursor:pointer;color:var(--text-secondary);padding:6px;border-radius:8px;transition:all 0.2s}
.btn-danger-outline:hover{background:rgba(239,68,68,0.1);color:#ef4444}
.chat-back{display:none;background:none;border:none;cursor:pointer;color:var(--text-secondary);padding:6px;border-radius:8px;transition:all 0.2s}
.chat-back:hover{background:var(--bg-light)}
.chat-messages{flex:1;padding:24px;overflow-y:auto;display:flex;flex-direction:column;gap:12px;min-height:350px;background:var(--bg-light)}
.chat-bubble{max-width:75%;padding:12px 16px;border-radius:12px;font-size:14px;line-height:1.5}
.chat-bubble.sent{background:var(--primary);color:white;align-self:flex-end;border-bottom-right-radius:4px}
.chat-bubble.received{background:var(--bg-white);color:var(--text-primary);align-self:flex-start;border-bottom-left-radius:4px;box-shadow:var(--shadow-sm)}
.chat-bubble-time{font-size:11px;opacity:0.7;margin-top:4px}
.chat-input{display:flex;gap:12px;padding:16px 24px;border-top:1px solid var(--border);background:var(--bg-white)}
.chat-input .form-input{flex:1}
.chat-typing{padding:0 24px 8px;font-size:12px;color:var(--text-muted);display:none}
.chat-typing.active{display:block}
.alert{display:flex;align-items:center;gap:8px;padding:14px 20px;border-radius:var(--radius-md);margin-bottom:20px;font-size:14px;font-weight:500}
.alert-success{background:rgba(76,175,80,0.08);border:1px solid rgba(76,175,80,0.2);color:#2E7D32}
.empty-state{text-align:center;padding:60px 24px}
.empty-state-icon{width:64px;height:64px;margin:0 auto 16px;background:var(--bg-light);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--text-muted)}
.form-input{width:100%;padding:12px 16px;border:2px solid var(--border);border-radius:var(--radius-sm);font-family:'Inter',sans-serif;font-size:15px;color:var(--text-primary);background:var(--bg-white);outline:none;transition:all 0.3s ease}
.form-input:focus{border-color:var(--primary);box-shadow:0 0 0 4px rgba(232,99,42,0.1)}
.btn{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;border:none;border-radius:var(--radius-md);font-family:'Inter',sans-serif;font-size:15px;font-weight:600;cursor:pointer;transition:all 0.3s ease;text-decoration:none}
.btn-primary{background:var(--primary);color:white}
.btn-primary:hover{background:var(--primary-dark);color:white}
.btn-icon{width:44px;height:44px;padding:0;display:flex;align-items:center;justify-content:center}
.new-msg-dot{width:6px;height:6px;border-radius:50%;background:var(--primary);display:none;position:absolute;top:50%;right:20px;transform:translateY(-50%)}
@media(max-width:1024px){.messages-layout{grid-template-columns:1fr}.chat-view:not(.active){display:none}.chat-view.active{display:flex}.chat-back{display:block}.messages-list.active{display:none}}
@media(max-width:768px){.user-content{padding:16px}}
</style>

<div class="page-header fade-in-up" style="margin-bottom:0">
    <div>
        <h1 class="page-title">Messages</h1>
        <p class="page-subtitle">Communicate with our team.</p>
    </div>
</div>

<?php if (isset($sent)): ?>
    <div class="alert alert-success"><i data-lucide="check-circle" size="18"></i> Message sent successfully!</div>
<?php endif; ?>

<div class="messages-layout reveal">
    <div class="messages-list">
        <div class="messages-list-header">Conversations</div>
        <?php if (empty($threads)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="message-square" size="28"></i></div>
                <p style="color:var(--text-muted);font-size:14px">No messages yet. Start a conversation below.</p>
            </div>
        <?php else: foreach ($threads as $idx => $t): ?>
            <div class="message-thread <?= $idx === 0 ? 'active' : '' ?>" data-thread="<?= $t['thread_id'] ?>" data-sender-id="<?= $t['sender_id'] ?>" data-sender-name="<?= htmlspecialchars($t['sender_name'] ?? 'User') ?>" data-sender-avatar="<?= htmlspecialchars($t['sender_avatar'] ?? '') ?>" data-sender-lastactivity="<?= htmlspecialchars($t['sender_last_activity'] ?? '') ?>">
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
            <div class="chat-header-info">
                <button class="chat-back" id="chat-back"><i data-lucide="arrow-left" size="20"></i></button>
                <div id="chat-avatar" style="width:36px;height:36px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:14px;overflow:hidden;flex-shrink:0;<?= !empty($adminAvatar) ? 'background:none' : '' ?>">
                    <?php if (!empty($adminAvatar)): ?>
                        <img src="<?= BASE_URL . $adminAvatar ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover">
                    <?php else: ?>
                        <?= strtoupper($adminName[0]) ?>
                    <?php endif; ?>
                </div>
                <div>
                    <h6 id="chat-name"><?= htmlspecialchars($adminName) ?></h6>
                    <p class="chat-header-status" id="chat-status" style="color:#9ca3af">Offline</p>
                </div>
            </div>
            <div class="chat-header-actions">
                <button type="button" id="chat-delete-btn" class="btn-danger-outline" title="Delete conversation"><i data-lucide="trash-2" size="18"></i></button>
            </div>
        </div>
        <div class="chat-messages" id="chat-messages">
            <?php if (empty($messages)): ?>
                <div class="empty-state" id="chat-empty">
                    <div class="empty-state-icon"><i data-lucide="message-square" size="28"></i></div>
                    <p style="color:var(--text-muted);font-size:14px">No messages yet. Say hello!</p>
                </div>
            <?php else: foreach ($messages as $msg): ?>
                <div class="chat-bubble <?= $msg['sender_id'] == getCurrentUserId() ? 'sent' : 'received' ?>" data-msg-id="<?= $msg['id'] ?>" style="display:flex;align-items:flex-end;gap:8px;<?= $msg['sender_id'] == getCurrentUserId() ? 'flex-direction:row-reverse' : '' ?>">
                    <?php if ($msg['sender_id'] != getCurrentUserId()): ?>
                        <div style="width:28px;height:28px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:11px;overflow:hidden;flex-shrink:0">
                            <?php if (!empty($adminAvatar)): ?>
                                <img src="<?= BASE_URL . $adminAvatar ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover">
                            <?php else: ?>
                                <?= strtoupper($adminName[0]) ?>
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
            <input type="text" id="chat-input-field" class="form-input" placeholder="Type your message..." autocomplete="off">
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
    var threads = document.querySelectorAll('.message-thread');
    var chatView = document.getElementById('chat-view');
    var messagesList = document.querySelector('.messages-list');
    var lastMsgCount = chatMessages ? chatMessages.querySelectorAll('.chat-bubble').length : 0;

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

    function createBubbleHTML(msg) {
        var isSent = msg.sender_id == currentUserId;
        var time = formatTime(msg.created_at);
        return '<div class="chat-bubble ' + (isSent ? 'sent' : 'received') + '" data-msg-id="' + msg.id + '" style="display:flex;align-items:flex-end;gap:8px;' + (isSent ? 'flex-direction:row-reverse' : '') + '"><div><div>' + escapeHtml(msg.message) + '</div><div class="chat-bubble-time">' + time + '</div></div></div>';
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function sendMessage() {
        var text = chatInput.value.trim();
        if (!text) return;
        chatInput.value = '';
        chatSendBtn.disabled = true;

        var fd = new FormData();
        fd.append('receiver_id', '<?= $adminId ?>');
        fd.append('message', text);
        fd.append('thread_id', currentThreadId || '');

        fetch(BASE_URL + 'api/index.php?action=send_message', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success && d.data) {
                    var empty = document.getElementById('chat-empty');
                    if (empty) empty.remove();
                    var html = '<div class="chat-bubble sent" data-msg-id="' + d.data.id + '" style="display:flex;align-items:flex-end;gap:8px;flex-direction:row-reverse"><div><div>' + escapeHtml(text) + '</div><div class="chat-bubble-time">Just now</div></div></div>';
                    chatMessages.insertAdjacentHTML('beforeend', html);
                    scrollToBottom();
                    if (!currentThreadId && d.data.id) {
                        currentThreadId = d.data.thread_id || null;
                    }
                }
                chatSendBtn.disabled = false;
            })
            .catch(function() {
                chatSendBtn.disabled = false;
                chatInput.value = text;
            });
    }

    chatSendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
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
                        chatMessages.innerHTML = '<div class="empty-state" id="chat-empty"><div class="empty-state-icon"><i data-lucide="message-square" size="28"></i></div><p style="color:var(--text-muted);font-size:14px">No messages yet. Start a conversation below.</p></div>';
                        currentThreadId = null;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                });
        });
    }

    threads.forEach(function(thread) {
        thread.addEventListener('click', function() {
            var threadId = this.getAttribute('data-thread');
            var senderName = this.getAttribute('data-sender-name');
            var senderAvatar = this.getAttribute('data-sender-avatar');
            var lastActivity = this.getAttribute('data-sender-lastactivity');

            threads.forEach(function(t) { t.classList.remove('active'); });
            this.classList.add('active');

            var unread = this.querySelector('.message-thread-unread');
            if (unread) unread.style.display = 'none';

            if (window.innerWidth <= 1024 && chatView && messagesList) {
                chatView.classList.add('active');
                messagesList.classList.add('active');
            }

            if (threadId == currentThreadId) return;

            currentThreadId = threadId;
            chatMessages.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-muted);font-size:14px">Loading messages...</div>';

            fetch(BASE_URL + 'api/index.php?action=get_messages&thread_id=' + threadId)
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success && d.data) {
                        chatMessages.innerHTML = '';
                        if (!d.data.messages || d.data.messages.length === 0) {
                            chatMessages.innerHTML = '<div class="empty-state" id="chat-empty"><div class="empty-state-icon"><i data-lucide="message-square" size="28"></i></div><p style="color:var(--text-muted);font-size:14px">No messages yet. Say hello!</p></div>';
                        } else {
                            d.data.messages.forEach(function(msg) {
                                chatMessages.insertAdjacentHTML('beforeend', createBubbleHTML(msg));
                            });
                        }
                        scrollToBottom();
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                });
        });
    });

    var backBtn = document.getElementById('chat-back');
    if (backBtn) {
        backBtn.addEventListener('click', function() {
            if (chatView) chatView.classList.remove('active');
            if (messagesList) messagesList.classList.remove('active');
        });
    }

    if (typeof lucide !== 'undefined') lucide.createIcons();
})();
</script>

<?php require __DIR__ . '/../includes/user-footer.php'; ?>
