<?php
session_start();
header('Content-Type: application/json');

// Change this password after upload.
const APP_PASSWORD = 'change-this-password';
const FILE_PATH = __DIR__ . '/leads.json';

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function require_login() {
    if (empty($_SESSION['logged_in'])) {
        respond(['ok' => false, 'error' => 'Unauthorized'], 401);
    }
}

function read_leads() {
    if (!file_exists(FILE_PATH)) {
        file_put_contents(FILE_PATH, json_encode([], JSON_PRETTY_PRINT));
    }

    $fp = fopen(FILE_PATH, 'r');
    if (!$fp) {
        respond(['ok' => false, 'error' => 'Cannot open leads file'], 500);
    }

    flock($fp, LOCK_SH);
    $content = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    $data = json_decode($content ?: '[]', true);
    return is_array($data) ? $data : [];
}

function write_leads($leads) {
    $fp = fopen(FILE_PATH, 'c+');
    if (!$fp) {
        respond(['ok' => false, 'error' => 'Cannot write leads file'], 500);
    }

    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode(array_values($leads), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
}

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?: [];

if ($action === 'login') {
    if (($input['password'] ?? '') === APP_PASSWORD) {
        $_SESSION['logged_in'] = true;
        respond(['ok' => true]);
    }
    respond(['ok' => false, 'error' => 'Wrong password'], 401);
}

if ($action === 'logout') {
    session_destroy();
    respond(['ok' => true]);
}

if ($action === 'check') {
    if (!empty($_SESSION['logged_in'])) {
        respond(['ok' => true]);
    }
    respond(['ok' => false, 'error' => 'Unauthorized'], 401);
}

require_login();

if ($action === 'load') {
    $leads = read_leads();
    respond(['ok' => true, 'leads' => $leads]);
}

if ($action === 'save') {
    $leads = read_leads();
    $id = $input['id'] ?? '';

    $leadData = [
        'name' => (string)($input['name'] ?? ''),
        'purpose' => (string)($input['purpose'] ?? ''),
        'phone' => (string)($input['phone'] ?? ''),
        'note' => (string)($input['note'] ?? ''),
        'updated_at' => date('c')
    ];

    if ($id !== '') {
        foreach ($leads as &$lead) {
            if ((string)$lead['id'] === (string)$id) {
                $lead = array_merge($lead, $leadData);
                write_leads($leads);
                respond(['ok' => true]);
            }
        }
        respond(['ok' => false, 'error' => 'Lead not found'], 404);
    }

    $newLead = array_merge([
        'id' => uniqid('lead_', true),
        'created_at' => date('c')
    ], $leadData);

    array_unshift($leads, $newLead); // newest on top
    write_leads($leads);
    respond(['ok' => true, 'lead' => $newLead]);
}

if ($action === 'delete') {
    $id = $input['id'] ?? '';
    $leads = read_leads();
    $filtered = array_values(array_filter($leads, function($lead) use ($id) {
        return (string)$lead['id'] !== (string)$id;
    }));

    write_leads($filtered);
    respond(['ok' => true]);
}

respond(['ok' => false, 'error' => 'Unknown action'], 400);
