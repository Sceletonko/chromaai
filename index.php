<?php
// Jednoduchý PHP upload logik
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['local_file'])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $file_name = basename($_FILES["local_file"]["name"]);
    $target_file = $target_dir . time() . "_" . $file_name;
    
    if (move_uploaded_file($_FILES["local_file"]["tmp_name"], $target_file)) {
        $upload_success = true;
        $uploaded_file = [
            'name' => $file_name,
            'path' => $target_file,
            'type' => $_FILES["local_file"]["type"],
            'ext' => strtolower(pathinfo($file_name, PATHINFO_EXTENSION))
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChromaAi - Premium AI Platform</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="logo.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-purple: #9b30ff;
            --dark-purple: #7a1fd1;
            --light-purple: #f3e8ff;
            --light-bg: #ffffff;
            --text-color: #1a1a1a;
            --border-color: #e5e7eb;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Top Header */
        header {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            padding: 15px 30px;
            gap: 20px;
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1000;
        }

        .header-logo img {
            height: 35px;
            width: auto;
        }

        .profile-dropdown img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid var(--primary-purple);
        }

        .menu-toggle {
            font-size: 1.5rem;
            color: var(--primary-purple);
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            display: flex;
            align-items: center;
        }

        /* Sidebar / Offcanvas */
        .offcanvas {
            border-right: 1px solid var(--border-color);
            background-color: #fcfaff;
        }

        .offcanvas-header {
            border-bottom: 1px solid var(--border-color);
        }

        .chat-history-item {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 5px;
            cursor: pointer;
            transition: background 0.2s;
            font-size: 0.9rem;
            color: #4b5563;
        }

        .chat-history-item:hover {
            background-color: var(--light-purple);
            color: var(--dark-purple);
        }

        /* Hero Section */
        .hero-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            margin-top: 60px;
            position: relative;
        }

        .welcome-text {
            margin-bottom: 30px;
            text-align: center;
            z-index: 1;
        }

        .welcome-text h1 {
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .welcome-text p {
            color: #6b7280;
        }

        /* Chat Input Area */
        .chat-container {
            width: 100%;
            max-width: 800px;
            position: relative;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(155, 48, 255, 0.08);
            transition: border-color 0.3s, box-shadow 0.3s;
            padding: 15px;
            z-index: 10;
        }

        .chat-container:focus-within {
            border-color: var(--primary-purple);
            box-shadow: 0 4px 25px rgba(155, 48, 255, 0.15);
        }

        /* File Preview Area */
        .file-preview-area {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
            padding: 0 10px;
        }

        .file-preview-item {
            position: relative;
            background: var(--light-purple);
            border-radius: 12px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(155, 48, 255, 0.2);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .file-preview-item img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 6px;
        }

        .file-preview-item i {
            font-size: 1.5rem;
            color: var(--primary-purple);
        }

        .file-preview-item .file-name {
            font-size: 0.85rem;
            font-weight: 500;
            max-width: 120px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .file-preview-item .remove-file {
            cursor: pointer;
            color: #9ca3af;
            font-size: 1rem;
            transition: color 0.2s;
        }

        .file-preview-item .remove-file:hover {
            color: #ef4444;
        }

        .chat-textarea {
            width: 100%;
            border: none;
            outline: none;
            resize: none;
            font-size: 1rem;
            min-height: 60px;
            max-height: 200px;
            padding: 5px 10px;
            background: transparent;
        }

        .chat-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #f9f9f9;
        }

        .left-controls, .right-controls {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .control-btn {
            background: none;
            border: none;
            color: #9ca3af;
            font-size: 1.2rem;
            cursor: pointer;
            transition: color 0.2s;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .control-btn:hover {
            color: var(--primary-purple);
        }

        .send-btn {
            background-color: var(--primary-purple);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .btn-action {
            background-color: #fff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 10px 20px;
            font-size: 0.9rem;
            font-weight: 500;
            color: #4b5563;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
        }

        .btn-action:hover {
            background-color: var(--light-purple);
            border-color: var(--primary-purple);
            color: var(--dark-purple);
        }

        .btn-action i {
            color: var(--primary-purple);
            font-size: 1.1rem;
        }

        /* Background elements */
        .bg-gradient {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background: radial-gradient(circle at 50% -20%, rgba(155, 48, 255, 0.15) 0%, rgba(255,255,255,0) 70%);
            z-index: -1;
        }

        .scratch {
            position: absolute;
            background: linear-gradient(90deg, transparent, rgba(155, 48, 255, 0.15), transparent);
            height: 2px;
            width: 300px;
            z-index: -1;
            filter: blur(1px);
        }
        
        .scratch-1 { top: 15%; left: -50px; transform: rotate(-35deg); }
        .scratch-2 { top: 60%; right: -100px; transform: rotate(145deg); width: 400px; }
        .scratch-3 { bottom: 10%; left: 10%; transform: rotate(-15deg); width: 250px; }
        .scratch-4 { top: 20%; right: 20%; transform: rotate(70deg); width: 150px; opacity: 0.5; }

        /* Upload Toast/Alert */
        .upload-status {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 2000;
        }
        
        /* Input badge styling */
        .input-badge {
            background-color: var(--light-purple);
            color: var(--dark-purple);
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-right: 5px;
            display: none;
            align-items: center;
            gap: 5px;
        }
        
        .input-badge i {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <div class="bg-gradient"></div>
    <div class="scratch scratch-1"></div>
    <div class="scratch scratch-2"></div>
    <div class="scratch scratch-3"></div>
    <div class="scratch scratch-4"></div>

    <header>
        <div class="header-logo">
            <img src="logo.png" alt="ChromaAi Logo">
        </div>

        <button class="menu-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasHistory" aria-controls="offcanvasHistory">
            <i class="bi bi-list"></i>
        </button>

        <div class="ms-auto dropdown profile-dropdown">
            <img src="https://ui-avatars.com/api/?name=Lukas&background=9b30ff&color=fff" alt="Profile" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" aria-labelledby="profileDropdown">
                <li><a class="dropdown-item rounded" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li><a class="dropdown-item rounded" href="https://discord.gg/8nb72489hp" target="_blank"><i class="bi bi-discord me-2"></i>Discord</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item rounded text-danger" href="#"><i class="bi bi-box-arrow-right me-2"></i>Log out</a></li>
            </ul>
        </div>
    </header>

    <!-- Sidebar History -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasHistory" aria-labelledby="offcanvasHistoryLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title fw-bold" id="offcanvasHistoryLabel">Chat History</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="chat-history-item"><i class="bi bi-chat-left-text me-2"></i> How to build a website</div>
            <div class="chat-history-item"><i class="bi bi-chat-left-text me-2"></i> Python script for data...</div>
            <div class="chat-history-item"><i class="bi bi-chat-left-text me-2"></i> Marketing strategy 2024</div>
            <div class="text-center mt-4">
                <p class="text-muted small">Your recent chats will appear here</p>
            </div>
        </div>
    </div>

    <main class="hero-section">
        <div class="welcome-text">
            <h1>Hi, I'm ChromaAi</h1>
            <p>Always here to help you get things done</p>
        </div>

        <div class="chat-container">
            <form id="chatForm" action="chat.php" method="GET">
                <!-- File Preview -->
                <div id="filePreview" class="file-preview-area <?php echo isset($uploaded_file) ? 'd-flex' : 'd-none'; ?>">
                    <?php if (isset($uploaded_file)): ?>
                        <div class="file-preview-item" id="uploadedFileItem">
                            <?php if (strpos($uploaded_file['type'], 'image') !== false): ?>
                                <img src="<?php echo $uploaded_file['path']; ?>" alt="Preview">
                            <?php elseif ($uploaded_file['ext'] === 'php'): ?>
                                <i class="bi bi-filetype-php"></i>
                            <?php else: ?>
                                <i class="bi bi-file-earmark-text"></i>
                            <?php endif; ?>
                            <span class="file-name"><?php echo $uploaded_file['name']; ?></span>
                            <i class="bi bi-x-lg remove-file" onclick="removeFile()"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex align-items-start">
                    <span id="typeBadge" class="input-badge"></span>
                    <textarea name="q" id="chatInput" class="chat-textarea" placeholder="Message ChromaAi..." autofocus></textarea>
                </div>
            </form>

            <div class="chat-controls">
                <div class="left-controls">
                    <form id="uploadForm" action="index.php" method="POST" enctype="multipart/form-data" class="d-none">
                        <input type="file" name="local_file" id="localFileUpload" onchange="document.getElementById('uploadForm').submit()">
                    </form>
                    
                    <div class="dropdown">
                        <button class="control-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                        <ul class="dropdown-menu shadow border-0">
                            <li><button class="dropdown-item" onclick="document.getElementById('localFileUpload').click()"><i class="bi bi-laptop me-2"></i> Local upload</button></li>
                        </ul>
                    </div>
                    
                    <button class="control-btn" title="Tools"><i class="bi bi-cpu-fill"></i></button>
                    <button class="control-btn" title="Skills"><i class="bi bi-stars"></i></button>
                </div>
                <div class="right-controls">
                    <span class="text-muted small me-2">Auto Model <i class="bi bi-chevron-down ms-1"></i></span>
                    <button class="send-btn" id="sendBtn">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <button class="btn-action" onclick="setPromptType('image')">
                <i class="bi bi-image"></i> Create Image
            </button>
            <button class="btn-action" onclick="setPromptType('code')">
                <i class="bi bi-code-slash"></i> Create Code
            </button>
        </div>
    </main>

    <?php if (isset($upload_success)): ?>
    <div class="upload-status alert alert-success alert-dismissible fade show shadow" role="alert">
        <strong>Success!</strong> File uploaded successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const chatInput = document.getElementById('chatInput');
        const sendBtn = document.getElementById('sendBtn');
        const typeBadge = document.getElementById('typeBadge');
        const chatForm = document.getElementById('chatForm');

        chatInput.addEventListener('input', function() {
            if (this.value.trim().length > 0) {
                sendBtn.style.opacity = '1';
                sendBtn.style.cursor = 'pointer';
            } else {
                sendBtn.style.opacity = '0.6';
                sendBtn.style.cursor = 'not-allowed';
            }
            
            // Auto resize
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.value.trim().length > 0) {
                    chatForm.submit();
                }
            }
        });

        function setPromptType(type) {
            typeBadge.style.display = 'inline-flex';
            if (type === 'image') {
                typeBadge.innerHTML = '<i class="bi bi-image"></i> Image';
            } else {
                typeBadge.innerHTML = '<i class="bi bi-code-slash"></i> Code';
            }
            chatInput.focus();
        }

        // Hide badge if backspaced at start
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && chatInput.value === '' && typeBadge.style.display === 'inline-flex') {
                typeBadge.style.display = 'none';
            }
        });

        function removeFile() {
            const preview = document.getElementById('filePreview');
            preview.classList.remove('d-flex');
            preview.classList.add('d-none');
            preview.innerHTML = '';
        }
    </script>
</body>
</html>
