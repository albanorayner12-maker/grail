<?php
// 1. FORCE RUNTIME ERROR REPORTING BEFORE ANYTHING ELSE
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. START THE SESSION (If you pass case data or login status through sessions)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. INCLUDE DATABASE CONFIGURATION (⚠️ CHANGE 'db_connect.php' TO YOUR ACTUAL DB FILENAME)
require_once 'db.php'; 

// 4. DEFINE TRACKING VARIABLES SAFELY
// Looks for a case reference in the URL (?case_ref=123), then session, defaults to 'default_case'
$case_ref = $_GET['case_ref'] ?? $_SESSION['case_ref'] ?? 'default_case';


// ==========================================
// 5. ASYNCHRONOUS API ROUTER (AJAX TARGET)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'send') {
    require_once 'gemini_helper.php'; // Load our API script 
    
    $input = json_decode(file_get_contents('php://input'), true);
    $text = trim($input['message_text'] ?? '');

    if (!empty($text)) {
        // Check current chat routing mode status 
        $check_stmt = $pdo->prepare("SELECT chat_mode FROM case_messages WHERE case_ref = ? ORDER BY id DESC LIMIT 1");
        $check_stmt->execute([$case_ref]);
        $last_row = $check_stmt->fetch();

        // If the thread was ALREADY set to human, keep it human! 
        $current_mode = $last_row ? $last_row['chat_mode'] : 'ai'; 

        // Check if user is requesting human support right now 
        $request_human = false;
        $lower_text = strtolower($text);
        
        if (strpos($lower_text, 'admin') !== false || strpos($lower_text, 'human') !== false || strpos($lower_text, 'person') !== false) {
            if ($current_mode === 'ai') {
                $request_human = true;
            }
            $current_mode = 'human'; 
        }

        // Log the user's incoming text message
        $stmt = $pdo->prepare("INSERT INTO case_messages (case_ref, role_type, message_text, chat_mode) VALUES (?, 'user', ?, ?)");
        $stmt->execute([$case_ref, $text, $current_mode]); 

        // Process response generation based on the status flag 
        if ($current_mode === 'ai') {
            $ai_reply = getAiResponse($text);
            
            $ai_stmt = $pdo->prepare("INSERT INTO case_messages (case_ref, role_type, message_text, chat_mode) VALUES (?, 'recipient', ?, 'ai')");
            $ai_stmt->execute([$case_ref, $ai_reply]);
            
        } elseif ($request_human) {
            $handover_text = "🔄 AI Assistant: Transferring case history logs to the Investigator Desk. A live representative will review your ticket details shortly.";
            
            $handover_stmt = $pdo->prepare("INSERT INTO case_messages (case_ref, role_type, message_text, chat_mode) VALUES (?, 'recipient', ?, 'human')");
            $handover_stmt->execute([$case_ref, $handover_text]);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Empty message']);
    }
    exit;
}
?>
<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Support Helpdesk Desk</title>
                <style>
                    :root {
                        --bg-color: #f3f4f6;
                        --panel-bg: #ffffff;
                        --primary: #2563eb;
                        --primary-hover: #1d4ed8;
                        --user-bubble: #dbeafe;
                        --system-bubble: #f3f4f6;
                        --handover-bubble: #fef3c7;
                        --text-dark: #1f2937;
                    }
                    * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
                    body { background-color: var(--bg-color); color: var(--text-dark); display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        
                    .chat-app { width: 100%; max-width: 650px; background: var(--panel-bg); border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; overflow: hidden; height: 80vh; }
                    .chat-header { background: var(--primary); color: white; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
                    .chat-header h2 { font-size: 1.1rem; font-weight: 600; }
                    .ticket-badge { background: rgba(255, 255, 255, 0.2); padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-family: monospace; }
        
                    .system-banner { background: #eff6ff; border-bottom: 1px solid #e5e7eb; padding: 12px 20px; font-size: 0.85rem; color: #4b5563; line-height: 1.4; }
        
                    .message-window { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; background: #fafafa; }
                    .msg { max-width: 80%; padding: 12px 16px; border-radius: 12px; font-size: 0.95rem; line-height: 1.4; animation: fadeIn 0.2s ease-in-out; }
        
        
                    .msg.user { background: var(--user-bubble); align-self: flex-end; border-bottom-right-radius: 2px; color: #1e40af; }
                    .msg.recipient { background: var(--panel-bg); align-self: flex-start; border-bottom-left-radius: 2px; border: 1px solid #e5e7eb; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
                    .msg.system-alert { background: var(--handover-bubble); align-self: center; text-align: center; border-radius: 8px; font-size: 0.85rem; color: #92400e; border: 1px solid #fde68a; max-width: 90%; }
        
                    .input-footer { padding: 16px 20px; background: var(--panel-bg); border-top: 1px solid #e5e7eb; display: flex; gap: 10px; align-items: center; }
                    .input-footer input { flex: 1; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.95rem; outline: none; transition: border 0.15s ease; }
                    .input-footer input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); }
                    .input-footer button { padding: 12px 24px; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 500; font-size: 0.95rem; cursor: pointer; transition: background 0.15s ease; }
                    .input-footer button:hover { background: var(--primary-hover); }
        
                    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
                </style>
        </head>
    <body>
 
        <div class="chat-app">
            <!-- Header banner -->
            <div class="chat-header">
                <h2>Support Desk Engine</h2>
        <div class="ticket-badge">REF: <?php echo htmlspecialchars($case_ref); ?></div>
    </div>
    
    <!-- Instructional Banner -->
    <div class="system-banner">
        💡 <strong>Automation Node Active:</strong> Ask questions naturally. Type <code>admin</code> or <code>human</code> at any point to completely hand off this session to our live Investigator Desk.
    </div>

    <!-- Scrollable Chat History Log Container -->
    <div class="message-window" id="messageContainer">
        <div class="msg recipient">Hello! I am your AI support assistant. How can I assist you with your portal platform details today?</div>
    </div>

    <!-- Active Input Field Row -->
    <div class="input-footer">
        <input type="text" id="msgText" placeholder="Type your support request details..." onkeypress="handleKeyPress(event)">
        <button onclick="testSendMessage()">Send Message</button>
    </div>
    </div>

<script>
function handleKeyPress(event) {
    if (event.key === 'Enter') {
        testSendMessage();
    }
}

async function testSendMessage() {
    const inputField = document.getElementById('msgText');
    const container = document.getElementById('messageContainer');
    const msg = inputField.value.trim();
    if(!msg) return;

    // 1. Immediately append the user message into screen space layout
    appendMessage(msg, 'user');
    inputField.value = '';

    // 2. Transmit payload to self-contained backend file routing loop
    try {

    const response = await fetch('?action=send&case_ref=<?php echo urlencode($case_ref); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            message_text: msg
        })
    });

    const raw = await response.text();

    console.log(raw);

    const data = JSON.parse(raw);

    if(data.success){
        appendMessage(data.reply || "Success","recipient");
    }else{
        appendMessage(data.error,"system-alert");
    }

} catch(err){

    console.error(err);

    appendMessage(err.message,"system-alert");

}
}

function appendMessage(text, role) {
    const container = document.getElementById('messageContainer');
    const msgDiv = document.createElement('div');
    msgDiv.className = `msg ${role}`;
    msgDiv.textContent = text;
    container.appendChild(msgDiv);
    
    // Auto scroll view area downwards
    container.scrollTop = container.scrollHeight;
}
    </script>

    </body>
</html>

