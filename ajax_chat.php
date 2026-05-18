<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

$pdo = get_db_connection();

if ($action === 'send' || $action === 'regenerate') {
    $chat_id = $_POST['chat_id'] ?? null;
    $prompt = $_POST['prompt'] ?? '';
    $model = $_POST['model'] ?? 'meta-llama/llama-3-8b-instruct:free';
    $image_url = $_POST['image_url'] ?? null;

    if (!$chat_id) {
        // Create new chat
        $stmt = $pdo->prepare("INSERT INTO chats (user_id, title, model) VALUES (?, ?, ?)");
        $title = substr($prompt, 0, 50) . (strlen($prompt) > 50 ? '...' : '');
        $stmt->execute([$user_id, $title, $model]);
        $chat_id = $pdo->lastInsertId();
    }

    if ($action === 'send') {
        // Save user message
        $stmt = $pdo->prepare("INSERT INTO messages (chat_id, role, content, image_url) VALUES (?, 'user', ?, ?)");
        $stmt->execute([$chat_id, $prompt, $image_url]);
    } else if ($action === 'regenerate') {
        // Delete last assistant message if it exists for this chat
        $stmt = $pdo->prepare("DELETE FROM messages WHERE chat_id = ? AND role = 'assistant' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$chat_id]);
        
        // Get the last user message to regenerate from
        $stmt = $pdo->prepare("SELECT content, image_url FROM messages WHERE chat_id = ? AND role = 'user' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$chat_id]);
        $last_user_msg = $stmt->fetch();
        if ($last_user_msg) {
            $prompt = $last_user_msg['content'];
            $image_url = $last_user_msg['image_url'];
        }
    }

    // Get chat history for AI context
    $stmt = $pdo->prepare("SELECT role, content FROM messages WHERE chat_id = ? ORDER BY id ASC");
    $stmt->execute([$chat_id]);
    $history = $stmt->fetchAll();

    $messages = [];
    foreach ($history as $msg) {
        $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
    }

    // Call AI API
    $response_text = call_ai_api($model, $messages, $image_url);

    // Save assistant message
    $stmt = $pdo->prepare("INSERT INTO messages (chat_id, role, content) VALUES (?, 'assistant', ?)");
    $stmt->execute([$chat_id, $response_text]);

    echo json_encode([
        'success' => true,
        'chat_id' => $chat_id,
        'response' => $response_text,
        'model' => $model
    ]);

} else if ($action === 'edit') {
    $message_id = $_POST['message_id'] ?? null;
    $new_content = $_POST['content'] ?? '';
    
    // Verify message belongs to user
    $stmt = $pdo->prepare("SELECT m.chat_id FROM messages m JOIN chats c ON m.chat_id = c.id WHERE m.id = ? AND c.user_id = ? AND m.role = 'user'");
    $stmt->execute([$message_id, $user_id]);
    $msg = $stmt->fetch();
    
    if ($msg) {
        $chat_id = $msg['chat_id'];
        // Update user message
        $stmt = $pdo->prepare("UPDATE messages SET content = ? WHERE id = ?");
        $stmt->execute([$new_content, $message_id]);
        
        // Delete all messages after this one in the chat
        $stmt = $pdo->prepare("DELETE FROM messages WHERE chat_id = ? AND id > ?");
        $stmt->execute([$chat_id, $message_id]);
        
        echo json_encode(['success' => true, 'chat_id' => $chat_id]);
    } else {
        echo json_encode(['error' => 'Message not found or unauthorized']);
    }
} else if ($action === 'get_history') {
    $stmt = $pdo->prepare("SELECT id, title, created_at FROM chats WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $chats = $stmt->fetchAll();
    echo json_encode(['chats' => $chats]);
}

function call_ai_api($model, $messages, $image_url = null) {
    $is_groq = strpos($model, 'groq/') === 0;
    $api_key = '';
    $url = '';
    
    if ($is_groq) {
        $api_key = $_ENV['GROQ_API_KEY'] ?? '';
        $url = "https://api.groq.com/openai/v1/chat/completions";
        $model = str_replace('groq/', '', $model);
    } else {
        $api_key = $_ENV['OPENROUTER_API_KEY'] ?? '';
        $url = "https://openrouter.ai/api/v1/chat/completions";
    }

    if (empty($api_key)) return "API Key not configured.";

    // Handle image for vision models
    if ($image_url && (strpos($model, 'vision') !== false || strpos($model, 'gemini') !== false || strpos($model, 'llama-3.2') !== false)) {
        $last_msg = array_pop($messages);
        $last_msg['content'] = [
            ['type' => 'text', 'text' => $last_msg['content']],
            ['type' => 'image_url', 'image_url' => ['url' => $image_url]]
        ];
        $messages[] = $last_msg;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ];
    if (!$is_groq) {
        $headers[] = 'HTTP-Referer: http://localhost';
        $headers[] = 'X-Title: ChromaAi';
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $data = [
        'model' => $model,
        'messages' => $messages
    ];
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $result = curl_exec($ch);
    if (curl_errno($ch)) return 'Error:' . curl_error($ch);
    
    $decoded = json_decode($result, true);
    curl_close($ch);
    
    if (isset($decoded['choices'][0]['message']['content'])) {
        return $decoded['choices'][0]['message']['content'];
    } else {
        return "API Error: " . ($decoded['error']['message'] ?? 'Unknown error');
    }
}
