<?php
require_once '../classes/connection.php';

$admin_id = $_SESSION['admin_id'] ?? null;
?>
<script>
    /* ================= Chat Variables ================= */
    let selectedAdminId = null;        // currently active chat
    let chatInterval = null;           // interval to poll chat messages
    let statusInterval = null;         // interval to poll statuses
    let typingTimeout = null;          // timeout for typing indicator
    let displayedMessages = new Set(); // keep track of messages already displayed
    let lastMessageTime = null;        // last message timestamp for incremental fetch
    let unreadContacts = new Set();    // track contacts with unread messages

    /* ================= Load admin contacts ================= */
    function loadContacts() {
        $.get('../include/chat.php', { action: 'contacts' }, function(admins) {
            let html = '';
            if (!admins || admins.length === 0) {
                html = '<p class="text-center text-muted mt-3">No other admins found.</p>';
            } else {
                admins.forEach(adm => { html += contactHtml(adm); });
            }
            $('#chat-panel-body').html(html);

            // Click on contact opens chat and removes green dot
            $('.contact-item').click(function() {
                selectedAdminId = $(this).data('admin-id');
                unreadContacts.delete(selectedAdminId);
                $(this).find('.unread-dot').remove();
                openChat($(this).data('name'), $(this).data('avatar'));
            });

            // Start polling statuses every 5 seconds
            clearInterval(statusInterval);
            statusInterval = setInterval(updateStatusesBatch, 5000);
        }, 'json');
    }

    /* ================= Generate contact HTML ================= */
    function contactHtml(adm) {
        const status = adm.online ? 'Online' : 'Offline';
        const typing = adm.is_typing ? ' • typing...' : '';
        const unreadDot = unreadContacts.has(adm.admin_id) 
            ? '<span class="unread-dot" style="display:inline-block;width:10px;height:10px;background:green;border-radius:50%;margin-left:5px;"></span>' 
            : '';

        return `
        <div class="contact-item" data-admin-id="${adm.admin_id}" data-name="${adm.full_name}" data-avatar="${adm.profile_picture}" style="cursor:pointer; padding:10px; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center;">
                <img src="${'../uploads/profile/'+adm.profile_picture}" class="img-circle" style="width:45px; height:45px; margin-right:10px;">
                <div>
                    <strong>${adm.full_name.split(' ')[0]}</strong><br>
                    <small class="text-muted status-text">${status}${typing}</small>
                </div>
            </div>
            ${unreadDot}
        </div>`;
    }

    /* ================= Update statuses and handle new messages ================= */
    function updateStatusesBatch() {
        $.get('../include/chat.php', { action: 'status_all' }, function(allStatuses) {
            $('.contact-item').each(function() {
                const adminId = $(this).data('admin-id');
                const statusEl = $(this).find('.status-text');

                if (allStatuses[adminId]) {
                    const status = allStatuses[adminId].online ? 'Online' : 'Offline';
                    const typing = allStatuses[adminId].is_typing ? ' • typing...' : '';
                    statusEl.text(status + typing);

                    if (allStatuses[adminId].has_new_message && selectedAdminId != adminId) {
                        unreadContacts.add(adminId);

                        if ($(this).find('.unread-dot').length === 0) {
                            $(this).append('<span class="unread-dot" style="display:inline-block;width:10px;height:10px;background:green;border-radius:50%;margin-left:5px;"></span>');
                        }
                    }
                }
            });
        }, 'json');
    }

    /* ================= Open chat with specific admin ================= */
    function openChat(name, avatar) {
        $('#back-to-contacts').show();
        $('#chat-panel-footer').show();
        $('#chat-panel-body').fadeOut(200, function() {
            $(this).html('<div id="chat-messages"></div>').fadeIn(200);
            $('#chat-panel-body').prepend(`
                <div style="display:flex; align-items:center; margin-bottom:10px; border-bottom:1px solid #ecf0f1;">
                    <img src="${avatar}" class="img-circle" style="width:45px; height:45px; margin-right:10px;">
                    <h5 style="margin:0;">${name.split(' ')[0]}</h5>
                </div>
            `);
            displayedMessages.clear();
            lastMessageTime = null;
            loadMessages(true);
            clearInterval(chatInterval);
            chatInterval = setInterval(() => loadMessages(false), 2000);
        });
    }

    /* ================= Load messages incrementally with auto-scroll ================= */
    function loadMessages(forceScroll = false) {
        let params = { action: 'fetch', recipient_admin_id: selectedAdminId };
        if (lastMessageTime) params.since = lastMessageTime;

        $.get('../include/chat.php', params, function(messages) {
            if (!messages) return;

            const meId = <?= json_encode($admin_id) ?>;
            let html = '';
            let newestTime = lastMessageTime;
            const $chatContainer = $('#chat-messages');
            const isNearBottom = $chatContainer[0].scrollHeight - $chatContainer.scrollTop() - $chatContainer.outerHeight() < 50;

            messages.forEach(msg => {
                const messageKey = msg.sent_at + '_' + msg.sender_admin_id;
                if (!displayedMessages.has(messageKey)) {
                    displayedMessages.add(messageKey);

                    // If chat not open with sender, mark as unread
                    if (msg.sender_admin_id != meId && msg.sender_admin_id != selectedAdminId) {
                        unreadContacts.add(msg.sender_admin_id);
                        const contactElem = $(`.contact-item[data-admin-id="${msg.sender_admin_id}"]`);
                        if (contactElem.find('.unread-dot').length === 0) {
                            contactElem.append('<span class="unread-dot" style="display:inline-block;width:10px;height:10px;background:green;border-radius:50%;margin-left:5px;"></span>');
                        }
                    }

                    const side = msg.sender_admin_id == meId ? 'right' : 'left';
                    const bubbleColor = side === 'right' ? '#007bff' : '#ecf0f1';
                    const textColor = side === 'right' ? 'white' : 'black';
                    const displayName = msg.full_name.split(' ')[0];

                    const sentDate = new Date(msg.sent_at);
                    const now = new Date();
                    let displayTime = sentDate.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
                    if (sentDate.toDateString() !== now.toDateString()) {
                        displayTime = sentDate.toLocaleDateString() + ' ' + displayTime;
                    }

                    html += `
                    <div style="display:flex; margin-bottom:10px; justify-content:${side==='right'?'flex-end':'flex-start'};">
                        ${side==='left'?`<img src="${'../uploads/profile/'+msg.profile_picture}" class="img-circle" style="width:40px;height:40px;margin-right:10px;">`:''}
                        <div style="background:${bubbleColor}; color:${textColor}; padding:10px 15px; border-radius:15px; max-width:70%;">
                            <strong>${displayName}</strong><br>
                            ${$('<div>').text(msg.message).html()}
                            <div style="font-size:10px; margin-top:3px; text-align:${side==='right'?'right':'left'}; color:rgba(0,0,0,0.5);">${displayTime}</div>
                        </div>
                        ${side==='right'?`<img src="${'../uploads/profile/'+msg.profile_picture}" class="img-circle" style="width:40px;height:40px;margin-left:10px;">`:''}
                    </div>`;

                    if (!newestTime || msg.sent_at > newestTime) newestTime = msg.sent_at;
                }
            });

            if (html !== '') {
                $chatContainer.append(html);
                if (forceScroll || isNearBottom) {
                    $("#chat-panel-body").scrollTop($("#chat-panel-body")[0].scrollHeight);
                }
            }

            lastMessageTime = newestTime;
        }, 'json');
    }

    /* ================= Typing indicator ================= */
    $('#chat-input').on('input', function() {
        if (!selectedAdminId) return;

        clearTimeout(typingTimeout);
        $.post('../include/chat.php', { action: 'typing', is_typing: 1 });

        typingTimeout = setTimeout(() => {
            $.post('../include/chat.php', { action: 'typing', is_typing: 0 });
        }, 2000);
    });

    /* ================= Heartbeat ================= */
    setInterval(() => {
        $.post('../include/chat.php', { action: 'heartbeat' });
    }, 10000);

    /* ================= Send message ================= */
    $('#chat-send').click(function() {
        const msg = $('#chat-input').val().trim();
        if (msg === '' || !selectedAdminId) return;
        $.post('../include/chat.php', { action: 'send', recipient_admin_id: selectedAdminId, message: msg }, function() {
            $('#chat-input').val('');
            loadMessages(true);
        });
    });
    $('#chat-input').keypress(function(e){
        if (e.which === 13) $('#chat-send').click();
    });

    /* ================= Back to contacts ================= */
    $('#back-to-contacts').click(function(){
        selectedAdminId = null;
        displayedMessages.clear();
        lastMessageTime = null;
        clearInterval(chatInterval);
        $('#back-to-contacts').hide();
        $('#chat-panel-footer').hide();
        $('#chat-panel-body').fadeOut(200, function(){
            loadContacts();
            $(this).fadeIn(200);
        });
    });

    /* ================= Initial load ================= */
    loadContacts();
</script>
