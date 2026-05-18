<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_id = $_GET['id'] ?? null;
$pdo = get_db_connection();

// Handle initial query from index.php
if (isset($_GET['q']) && !$chat_id) {
    $prompt = $_GET['q'];
    $model = $_GET['model'] ?? 'meta-llama/llama-3-8b-instruct:free';
    
    // Create new chat
    $stmt = $pdo->prepare("INSERT INTO chats (user_id, title, model) VALUES (?, ?, ?)");
    $title = substr($prompt, 0, 50) . (strlen($prompt) > 50 ? '...' : '');
    $stmt->execute([$user_id, $title, $model]);
    $new_chat_id = $pdo->lastInsertId();
    
    // Save user message
    $stmt = $pdo->prepare("INSERT INTO messages (chat_id, role, content) VALUES (?, 'user', ?)");
    $stmt->execute([$new_chat_id, $prompt]);
    
    // We could call AI here, but it's better to redirect and let the client-side handle it 
    // or just show the user message and let the client-side trigger the AI response.
    // Actually, let's redirect to the new chat page.
    header("Location: chat.php?id=$new_chat_id&first=1");
    exit();
}

$chat_title = "New Chat";
$messages = [];
$current_model = 'meta-llama/llama-3-8b-instruct:free';

if ($chat_id) {
    $stmt = $pdo->prepare("SELECT title, model FROM chats WHERE id = ? AND user_id = ?");
    $stmt->execute([$chat_id, $user_id]);
    $chat = $stmt->fetch();
    if ($chat) {
        $chat_title = $chat['title'];
        $current_model = $chat['model'];
        
        $stmt = $pdo->prepare("SELECT * FROM messages WHERE chat_id = ? ORDER BY id ASC");
        $stmt->execute([$chat_id]);
        $messages = $stmt->fetchAll();
    } else {
        header("Location: chat.php");
        exit();
    }
}

// Get history for sidebar
$stmt = $pdo->prepare("SELECT id, title FROM chats WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
$stmt->execute([$user_id]);
$chat_history = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChromaAi - Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
    <link rel="icon" type="image/png" href="logo.png">
    <style>
        :root {
            --primary-purple: #9b30ff;
            --light-purple: #f3e8ff;
            --sidebar-bg: #f8f9fa;
            --chat-bg: #ffffff;
            --bubble-user: #f4f4f4;
            --bubble-ai: #ffffff;
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--chat-bg); height: 100vh; display: flex; overflow: hidden; }
        
        /* Sidebar */
        .sidebar { width: 280px; background: var(--sidebar-bg); border-right: 1px solid #e5e7eb; display: flex; flex-direction: column; transition: transform 0.3s; }
        .sidebar-header { padding: 20px; border-bottom: 1px solid #e5e7eb; }
        .sidebar-content { flex: 1; overflow-y: auto; padding: 10px; }
        .history-item { padding: 10px 15px; border-radius: 10px; cursor: pointer; margin-bottom: 5px; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center; gap: 10px; color: #4b5563; }
        .history-item:hover, .history-item.active { background: #e5e7eb; color: #000; }
        .new-chat-btn { width: 100%; border: 1px solid #e5e7eb; background: white; padding: 10px; border-radius: 10px; font-weight: 600; margin-bottom: 20px; transition: all 0.2s; }
        .new-chat-btn:hover { background: var(--light-purple); border-color: var(--primary-purple); }

        /* Main Chat Area */
        .main-chat { flex: 1; display: flex; flex-direction: column; position: relative; }
        .chat-header { padding: 15px 30px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; }
        .messages-container { flex: 1; overflow-y: auto; padding: 40px 20px; }
        .message-wrapper { max-width: 800px; margin: 0 auto 30px auto; display: flex; flex-direction: column; }
        .message-role { font-weight: 700; font-size: 0.85rem; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
        .user-role { color: #6b7280; }
        .ai-role { color: var(--primary-purple); }
        .message-content { line-height: 1.6; font-size: 1rem; position: relative; }
        .message-content img { max-width: 100%; border-radius: 12px; margin-bottom: 10px; }
        
        /* Code blocks */
        pre { background: #1e1e1e; color: #fff; padding: 15px; border-radius: 10px; position: relative; margin: 15px 0; }
        .copy-code-btn { position: absolute; top: 10px; right: 10px; background: rgba(255,255,255,0.1); border: none; color: #fff; padding: 4px 8px; border-radius: 5px; font-size: 0.75rem; cursor: pointer; }
        .copy-code-btn:hover { background: rgba(255,255,255,0.2); }

        /* Message Actions */
        .message-actions { display: flex; gap: 15px; margin-top: 10px; opacity: 0; transition: opacity 0.2s; }
        .message-wrapper:hover .message-actions { opacity: 1; }
        .action-btn { background: none; border: none; color: #9ca3af; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 5px; }
        .action-btn:hover { color: var(--primary-purple); }

        /* Input Area */
        .input-area { padding: 20px; max-width: 800px; margin: 0 auto; width: 100%; position: relative; }
        .input-container { background: #fff; border: 1px solid #e5e7eb; border-radius: 20px; padding: 10px 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .input-container:focus-within { border-color: var(--primary-purple); }
        textarea { width: 100%; border: none; outline: none; resize: none; min-height: 40px; max-height: 200px; padding: 10px 0; }
        .controls { display: flex; justify-content: space-between; align-items: center; margin-top: 5px; }
        .send-btn { background: var(--primary-purple); color: white; border: none; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: transform 0.2s; }
        .send-btn:hover { transform: scale(1.1); }
        .send-btn:disabled { background: #e5e7eb; cursor: not-allowed; }

        /* Mobile adjustments */
        @media (max-width: 768px) {
            .sidebar { position: absolute; z-index: 1000; transform: translateX(-100%); height: 100%; }
            .sidebar.open { transform: translateX(0); }
        }
        
        .image-preview-container { display: flex; gap: 10px; margin-bottom: 10px; }
        .image-preview { position: relative; width: 60px; height: 60px; }
        .image-preview img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; }
        .remove-img { position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; }

        .loading-dots span { animation: blink 1.4s infinite both; font-size: 1.5rem; }
        .loading-dots span:nth-child(2) { animation-delay: 0.2s; }
        .loading-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes blink { 0% { opacity: 0.2; } 20% { opacity: 1; } 100% { opacity: 0.2; } }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="index.php" class="text-decoration-none d-flex align-items-center gap-2">
                <img src="logo.png" height="30">
                <span class="fw-bold text-dark fs-5">ChromaAi</span>
            </a>
        </div>
        <div class="sidebar-content">
            <button class="new-chat-btn" onclick="location.href='chat.php'"><i class="bi bi-plus-lg"></i> New Chat</button>
            <div class="text-muted small fw-bold mb-2 px-2">RECENT CHATS</div>
            <div id="chatHistory">
                <?php foreach ($chat_history as $h): ?>
                    <div class="history-item <?php echo $chat_id == $h['id'] ? 'active' : ''; ?>" onclick="location.href='chat.php?id=<?php echo $h['id']; ?>'">
                        <i class="bi bi-chat-left"></i> <?php echo htmlspecialchars($h['title']); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="p-3 border-top">
            <div class="d-flex align-items-center gap-2">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name']); ?>&background=9b30ff&color=fff" class="rounded-circle" width="30">
                <span class="small fw-bold"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            </div>
        </div>
    </div>

    <!-- Main Chat -->
    <div class="main-chat">
        <div class="chat-header">
            <button class="btn d-md-none" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="bi bi-list fs-4"></i></button>
            <div class="dropdown">
                <button class="btn btn-link text-dark text-decoration-none dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown">
                    <span id="modelDisplay">Auto Model</span>
                </button>
                <ul class="dropdown-menu shadow border-0">
                    <li class="dropdown-header">GROQ MODELS</li>
                    <li><button class="dropdown-item" onclick="setModel('Llama 3 70B', 'groq/llama3-70b-8192')">Llama 3 70B (Groq)</button></li>
                    <li><button class="dropdown-item" onclick="setModel('Mixtral 8x7B', 'groq/mixtral-8x7b-32768')">Mixtral 8x7B (Groq)</button></li>
                    <li><button class="dropdown-item" onclick="setModel('Llama 3.2 Vision', 'groq/llama-3.2-11b-vision-preview')">Llama 3.2 Vision (Groq)</button></li>
                    <li class="dropdown-divider"></li>
                    <li class="dropdown-header">OPENROUTER MODELS</li>
                    <li><button class="dropdown-item" onclick="setModel('Llama 3 8B', 'meta-llama/llama-3-8b-instruct:free')">Llama 3 (8B)</button></li>
                    <li><button class="dropdown-item" onclick="setModel('Mistral 7B', 'mistralai/mistral-7b-instruct:free')">Mistral 7B</button></li>
                    <li><button class="dropdown-item" onclick="setModel('Gemini 1.5 Flash', 'google/gemini-flash-1.5')">Gemini 1.5 Flash</button></li>
                </ul>
            </div>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-sm btn-outline-secondary rounded-pill">Home</a>
                <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill">Logout</a>
            </div>
        </div>

        <div class="messages-container" id="messagesContainer">
            <?php if (empty($messages)): ?>
                <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center opacity-50">
                    <img src="logo.png" height="80" class="mb-3">
                    <h3>How can I help you today?</h3>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message-wrapper" data-id="<?php echo $msg['id']; ?>">
                        <div class="message-role <?php echo $msg['role'] === 'user' ? 'user-role' : 'ai-role'; ?>">
                            <?php if ($msg['role'] === 'user'): ?>
                                <i class="bi bi-person-circle"></i> YOU
                            <?php else: ?>
                                <i class="bi bi-stars"></i> ChromaAi
                            <?php endif; ?>
                        </div>
                        <div class="message-content" id="msg-<?php echo $msg['id']; ?>">
                            <?php if ($msg['image_url']): ?>
                                <img src="<?php echo $msg['image_url']; ?>" alt="Uploaded image">
                            <?php endif; ?>
                            <div class="text-body"><?php echo htmlspecialchars($msg['content']); ?></div>
                        </div>
                        <div class="message-actions">
                            <button class="action-btn" onclick="copyMessage(<?php echo $msg['id']; ?>)"><i class="bi bi-copy"></i> Copy</button>
                            <?php if ($msg['role'] === 'user'): ?>
                                <button class="action-btn" onclick="editMessage(<?php echo $msg['id']; ?>)"><i class="bi bi-pencil"></i> Edit</button>
                            <?php else: ?>
                                <button class="action-btn" onclick="regenerate()"><i class="bi bi-arrow-clockwise"></i> Regenerate</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="input-area">
            <div id="imagePreviewContainer" class="image-preview-container d-none"></div>
            <div class="input-container">
                <textarea id="chatInput" placeholder="Type a message..." rows="1"></textarea>
                <div class="controls">
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm text-muted" onclick="document.getElementById('fileInput').click()"><i class="bi bi-image"></i></button>
                        <button class="btn btn-sm text-muted" onclick="setPromptType('image')"><i class="bi bi-stars"></i></button>
                        <button class="btn btn-sm text-muted" onclick="setPromptType('code')"><i class="bi bi-code-slash"></i></button>
                        <input type="file" id="fileInput" class="d-none" accept="image/*" onchange="handleFileUpload(this)">
                    </div>
                    <button class="send-btn" id="sendBtn" disabled onclick="sendMessage()"><i class="bi bi-arrow-up"></i></button>
                </div>
            </div>
            <p class="text-center text-muted small mt-2">ChromaAi can make mistakes. Check important info.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script>
        let chatId = <?php echo json_encode($chat_id); ?>;
        let currentModel = <?php echo json_encode($current_model); ?>;
        let currentImageUrl = null;

        const messagesContainer = document.getElementById('messagesContainer');
        const chatInput = document.getElementById('chatInput');
        const sendBtn = document.getElementById('sendBtn');
        const modelDisplay = document.getElementById('modelDisplay');

        // Initialize marked with highlight.js
        marked.setOptions({
            highlight: function(code, lang) {
                const language = hljs.getLanguage(lang) ? lang : 'plaintext';
                return hljs.highlight(code, { language }).value;
            },
            langPrefix: 'hljs language-'
        });

        function setModel(name, id) {
            currentModel = id;
            modelDisplay.innerText = name;
        }

        function setPromptType(type) {
            if (type === 'image') {
                chatInput.value = '/imagine ';
                chatInput.placeholder = 'Describe the image you want to create...';
            } else {
                chatInput.value = '/code ';
                chatInput.placeholder = 'Describe the code you want to generate...';
            }
            chatInput.focus();
            chatInput.dispatchEvent(new Event('input'));
        }

        // Auto-resize textarea
        chatInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
            sendBtn.disabled = this.value.trim() === '' && !currentImageUrl;
        });

        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        async function sendMessage() {
            const prompt = chatInput.value.trim();
            if (!prompt && !currentImageUrl) return;

            // Clear input
            chatInput.value = '';
            chatInput.style.height = 'auto';
            sendBtn.disabled = true;
            const imageUrl = currentImageUrl;
            clearImage();

            // Append user message to UI
            appendMessage('user', prompt, imageUrl);
            
            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

            // Show loading
            const loadingId = appendLoading();

            try {
                const formData = new FormData();
                formData.append('action', 'send');
                formData.append('chat_id', chatId || '');
                formData.append('prompt', prompt);
                formData.append('model', currentModel);
                if (imageUrl) formData.append('image_url', imageUrl);

                const response = await fetch('ajax_chat.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                removeLoading(loadingId);

                if (data.success) {
                    if (!chatId) {
                        chatId = data.chat_id;
                        window.history.replaceState(null, '', 'chat.php?id=' + chatId);
                        updateSidebar();
                    }
                    appendMessage('assistant', data.response);
                } else {
                    appendMessage('assistant', 'Error: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                removeLoading(loadingId);
                appendMessage('assistant', 'Error connecting to server.');
            }
            
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function appendMessage(role, content, imageUrl = null) {
            // Remove welcome text if first message
            if (messagesContainer.querySelector('h3')) {
                messagesContainer.innerHTML = '';
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'message-wrapper';
            
            const roleDiv = document.createElement('div');
            roleDiv.className = 'message-role ' + (role === 'user' ? 'user-role' : 'ai-role');
            roleDiv.innerHTML = role === 'user' ? '<i class="bi bi-person-circle"></i> YOU' : '<i class="bi bi-stars"></i> ChromaAi';
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            
            let html = '';
            if (imageUrl) html += `<img src="${imageUrl}">`;
            
            if (role === 'assistant') {
                html += `<div class="text-body">${marked.parse(content)}</div>`;
            } else {
                html += `<div class="text-body">${escapeHtml(content)}</div>`;
            }
            contentDiv.innerHTML = html;

            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'message-actions';
            actionsDiv.innerHTML = `
                <button class="action-btn" onclick="copyText(this)"><i class="bi bi-copy"></i> Copy</button>
                ${role === 'user' ? `<button class="action-btn" onclick="alert('Feature coming soon')"><i class="bi bi-pencil"></i> Edit</button>` : `<button class="action-btn" onclick="regenerate()"><i class="bi bi-arrow-clockwise"></i> Regenerate</button>`}
            `;

            wrapper.appendChild(roleDiv);
            wrapper.appendChild(contentDiv);
            wrapper.appendChild(actionsDiv);
            messagesContainer.appendChild(wrapper);

            // Add copy buttons to code blocks
            contentDiv.querySelectorAll('pre code').forEach((block) => {
                const pre = block.parentNode;
                const btn = document.createElement('button');
                btn.className = 'copy-code-btn';
                btn.innerText = 'Copy';
                btn.onclick = () => {
                    navigator.clipboard.writeText(block.innerText);
                    btn.innerText = 'Copied!';
                    setTimeout(() => btn.innerText = 'Copy', 2000);
                };
                pre.appendChild(btn);
            });
        }

        function appendLoading() {
            const id = 'loading-' + Date.now();
            const wrapper = document.createElement('div');
            wrapper.className = 'message-wrapper';
            wrapper.id = id;
            wrapper.innerHTML = `
                <div class="message-role ai-role"><i class="bi bi-stars"></i> ChromaAi</div>
                <div class="message-content">
                    <div class="loading-dots"><span>.</span><span>.</span><span>.</span></div>
                </div>
            `;
            messagesContainer.appendChild(wrapper);
            return id;
        }

        function removeLoading(id) {
            const el = document.getElementById(id);
            if (el) el.remove();
        }

        async function regenerate() {
            if (!chatId) return;
            const loadingId = appendLoading();
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

            try {
                const formData = new FormData();
                formData.append('action', 'regenerate');
                formData.append('chat_id', chatId);
                formData.append('model', currentModel);

                const response = await fetch('ajax_chat.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                removeLoading(loadingId);

                if (data.success) {
                    // Refresh the last message or just append?
                    // For simplicity, let's refresh the page for now or re-append
                    location.reload();
                }
            } catch (e) {
                removeLoading(loadingId);
            }
        }

        async function editMessage(messageId) {
            const wrapper = document.querySelector(`.message-wrapper[data-id="${messageId}"]`);
            const contentDiv = wrapper.querySelector('.text-body');
            const originalContent = contentDiv.innerText;
            
            const newContent = prompt('Edit your message:', originalContent);
            if (newContent === null || newContent.trim() === '' || newContent === originalContent) return;

            try {
                const formData = new FormData();
                formData.append('action', 'edit');
                formData.append('message_id', messageId);
                formData.append('content', newContent);

                const response = await fetch('ajax_chat.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    // Refresh chat to show updated state and new AI response
                    location.reload();
                } else {
                    alert(data.error);
                }
            } catch (e) {
                alert('Error editing message');
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        async function updateSidebar() {
            const response = await fetch('ajax_chat.php', {
                method: 'POST',
                body: new URLSearchParams({'action': 'get_history'})
            });
            const data = await response.json();
            const container = document.getElementById('chatHistory');
            container.innerHTML = '';
            data.chats.forEach(c => {
                const item = document.createElement('div');
                item.className = 'history-item' + (chatId == c.id ? ' active' : '');
                item.innerHTML = `<i class="bi bi-chat-left"></i> ${escapeHtml(c.title)}`;
                item.onclick = () => location.href = 'chat.php?id=' + c.id;
                container.appendChild(item);
            });
        }

        async function handleFileUpload(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            
            const formData = new FormData();
            formData.append('local_file', file);

            try {
                const response = await fetch('index.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                
                if (data.success) {
                    currentImageUrl = data.file.path;
                    showImagePreview(currentImageUrl);
                    sendBtn.disabled = false;
                } else {
                    alert(data.error);
                }
            } catch (e) {
                console.error('Upload failed', e);
            }
        }

        function showImagePreview(url) {
            const container = document.getElementById('imagePreviewContainer');
            container.innerHTML = `
                <div class="image-preview">
                    <img src="${url}">
                    <div class="remove-img" onclick="clearImage()">&times;</div>
                </div>
            `;
            container.classList.remove('d-none');
        }

        function clearImage() {
            currentImageUrl = null;
            document.getElementById('imagePreviewContainer').classList.add('d-none');
            document.getElementById('imagePreviewContainer').innerHTML = '';
            document.getElementById('fileInput').value = '';
            if (chatInput.value.trim() === '') sendBtn.disabled = true;
        }

        // Initialize existing messages with marked
        document.querySelectorAll('.message-wrapper .ai-role + .message-content .text-body').forEach(el => {
            el.innerHTML = marked.parse(el.textContent);
        });
        hljs.highlightAll();

        // Check for first message trigger
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('first') && chatId) {
            // Trigger AI response for the first message already saved in DB
            (async () => {
                const loadingId = appendLoading();
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                try {
                    const formData = new FormData();
                    formData.append('action', 'regenerate'); // regenerat works here because it will see no assistant msg and generate one
                    formData.append('chat_id', chatId);
                    formData.append('model', currentModel);

                    const response = await fetch('ajax_chat.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    removeLoading(loadingId);
                    if (data.success) {
                        appendMessage('assistant', data.response);
                        // Clean URL
                        window.history.replaceState(null, '', 'chat.php?id=' + chatId);
                    }
                } catch (e) {
                    removeLoading(loadingId);
                }
            })();
        }
    </script>
</body>
</html>
