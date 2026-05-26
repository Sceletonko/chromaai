<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

try {
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

        // If it's an image generation, we might want to store the image URL differently or just as content
        
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
    } else if ($action === 'delete_chat') {
        $chat_id = $_POST['chat_id'] ?? null;
        
        // Verify chat belongs to user
        $stmt = $pdo->prepare("SELECT id FROM chats WHERE id = ? AND user_id = ?");
        $stmt->execute([$chat_id, $user_id]);
        if ($stmt->fetch()) {
            // Delete messages first (or rely on CASCADE if set, but let's be safe)
            $stmt = $pdo->prepare("DELETE FROM messages WHERE chat_id = ?");
            $stmt->execute([$chat_id]);
            
            $stmt = $pdo->prepare("DELETE FROM chats WHERE id = ?");
            $stmt->execute([$chat_id]);
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Chat not found or unauthorized']);
        }
    } else if ($action === 'rename_chat') {
        $chat_id = $_POST['chat_id'] ?? null;
        $new_title = $_POST['title'] ?? '';
        
        if (empty($new_title)) {
            echo json_encode(['error' => 'Title cannot be empty']);
            exit();
        }
        
        // Verify chat belongs to user
        $stmt = $pdo->prepare("SELECT id FROM chats WHERE id = ? AND user_id = ?");
        $stmt->execute([$chat_id, $user_id]);
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE chats SET title = ? WHERE id = ?");
            $stmt->execute([$new_title, $chat_id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Chat not found or unauthorized']);
        }
    }
} catch (Exception $e) {
    error_log("AJAX Chat Error: " . $e->getMessage());
    echo json_encode(['error' => 'Server Error: ' . $e->getMessage()]);
}

function call_ai_api($model, $messages, $image_url = null) {
    $last_msg = end($messages);
    $prompt = $last_msg['content'];

    // Handle /imagine command
    if (is_string($prompt) && stripos($prompt, '/imagine') === 0) {
        $img_prompt = trim(substr($prompt, strlen('/imagine')));
        if (empty($img_prompt)) $img_prompt = "A beautiful landscape";
        return call_huggingface_image($img_prompt);
    }

    // Handle /code command - adds a system instruction for better code
    if (is_string($prompt) && stripos($prompt, '/code') === 0) {
        $code_prompt = trim(substr($prompt, strlen('/code')));
        $messages[count($messages)-1]['content'] = "You are an expert software developer. Provide only the code with brief explanation. Task: " . $code_prompt;
    }

    // Determine which API to use
    if (strpos($model, 'groq/') === 0) {
        return call_groq_api(str_replace('groq/', '', $model), $messages);
    } elseif (strpos($model, 'gemini') !== false && get_env_var('GOOGLE_AI_API_KEY')) {
        return call_google_ai($model, $messages);
    } elseif (strpos($model, 'huggingface/') === 0) {
        return call_huggingface_text(str_replace('huggingface/', '', $model), $messages);
    } else {
        return call_openrouter_api($model, $messages, $image_url);
    }
}

function call_groq_api($model, $messages) {
    $api_key = get_env_var('GROQ_API_KEY', '');
    if (empty($api_key)) return "Groq API Key not configured.";
    
    $url = "https://api.groq.com/openai/v1/chat/completions";
    return post_json_api($url, $api_key, ['model' => $model, 'messages' => $messages]);
}

function call_openrouter_api($model, $messages, $image_url = null) {
    $api_key = get_env_var('OPENROUTER_API_KEY', '');
    if (empty($api_key)) return "OpenRouter API Key not configured.";
    
    $url = "https://openrouter.ai/api/v1/chat/completions";
    
    // Handle image for vision models
    if ($image_url && (strpos($model, 'vision') !== false || strpos($model, 'gemini') !== false || strpos($model, 'llama-3.2') !== false)) {
        $final_image_url = $image_url;
        if (strpos($image_url, 'http') !== 0 || strpos($image_url, 'localhost') !== false || strpos($image_url, $_SERVER['HTTP_HOST'] ?? '') !== false) {
            $path = ltrim(parse_url($image_url, PHP_URL_PATH), '/');
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $final_image_url = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }
        $last = array_pop($messages);
        $last['content'] = [
            ['type' => 'text', 'text' => $last['content']],
            ['type' => 'image_url', 'image_url' => ['url' => $final_image_url]]
        ];
        $messages[] = $last;
    }

    $headers = [
        'HTTP-Referer: http://localhost',
        'X-Title: ChromaAi'
    ];
    return post_json_api($url, $api_key, ['model' => $model, 'messages' => $messages], $headers);
}

function call_google_ai($model, $messages) {
    $api_key = get_env_var('GOOGLE_AI_API_KEY', '');
    if (empty($api_key)) return "Google AI API Key not configured.";
    
    // Convert OpenAI format to Gemini format
    $contents = [];
    foreach ($messages as $msg) {
        $role = ($msg['role'] === 'assistant') ? 'model' : 'user';
        $contents[] = [
            'role' => $role,
            'parts' => [['text' => $msg['content']]]
        ];
    }
    
    // Gemini models in direct API don't use prefixes like google/
    $model_id = str_replace('google/', '', $model);
    if (strpos($model_id, 'gemini') === false) $model_id = 'gemini-1.5-flash';
    
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model_id}:generateContent?key=" . $api_key;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['contents' => $contents]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $result = curl_exec($ch);
    $decoded = json_decode($result, true);
    curl_close($ch);
    
    if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
        return $decoded['candidates'][0]['content']['parts'][0]['text'];
    }
    return "Google AI Error: " . ($decoded['error']['message'] ?? 'Unknown error');
}

function call_huggingface_image($prompt) {
    $api_key = get_env_var('HUGGINGFACE_API_KEY', '');
    if (empty($api_key)) return "HuggingFace API Key not configured.";
    
    $model = "runwayml/stable-diffusion-v1-5"; // Default image model
    $url = "https://api-inference.huggingface.co/models/" . $model;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['inputs' => $prompt]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json'
    ]);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200 && !empty($result)) {
        $filename = 'gen_' . time() . '.png';
        $path = 'uploads/' . $filename;
        if (!file_exists('uploads')) mkdir('uploads', 0777, true);
        file_put_contents($path, $result);
        return "I've generated this image for you: \n\n![Generated Image]($path)";
    }
    
    $err = json_decode($result, true);
    return "HuggingFace Image Error: " . ($err['error'] ?? 'Service unavailable or model loading');
}

function call_huggingface_text($model, $messages) {
    $api_key = get_env_var('HUGGINGFACE_API_KEY', '');
    $url = "https://api-inference.huggingface.co/models/" . $model;
    
    $prompt = "";
    foreach ($messages as $msg) {
        $prompt .= $msg['role'] . ": " . $msg['content'] . "\n";
    }
    $prompt .= "assistant: ";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'inputs' => $prompt,
        'parameters' => ['max_new_tokens' => 500]
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json'
    ]);
    
    $result = curl_exec($ch);
    $decoded = json_decode($result, true);
    curl_close($ch);
    
    if (isset($decoded[0]['generated_text'])) {
        $text = $decoded[0]['generated_text'];
        return str_replace($prompt, '', $text);
    }
    return "HuggingFace Text Error: " . ($decoded['error'] ?? 'Unknown error');
}

function post_json_api($url, $api_key, $data, $extra_headers = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $headers = array_merge([
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ], $extra_headers);
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $result = curl_exec($ch);
    if (curl_errno($ch)) return 'Curl Error:' . curl_error($ch);
    
    $decoded = json_decode($result, true);
    curl_close($ch);
    
    if (isset($decoded['choices'][0]['message']['content'])) {
        return $decoded['choices'][0]['message']['content'];
    }
    
    $error_detail = $decoded['error']['message'] ?? $decoded['error'] ?? 'Unknown error';
    return "API Error: " . (is_array($error_detail) ? json_encode($error_detail) : $error_detail);
}
