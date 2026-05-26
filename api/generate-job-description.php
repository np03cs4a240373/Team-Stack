<?php
require_once '../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

requireRole('employer');

$input    = json_decode(file_get_contents('php://input'), true);
$title    = trim($input['title']    ?? '');
$company  = trim($input['company']  ?? '');
$location = trim($input['location'] ?? '');
$type     = trim($input['type']     ?? 'full-time');

if (empty($title)) {
    http_response_code(400);
    echo json_encode(['error' => 'Job title is required to generate a description.']);
    exit;
}

$typeLabel = [
    'full-time'  => 'Full Time',
    'part-time'  => 'Part Time',
    'remote'     => 'Remote',
    'contract'   => 'Contract',
    'internship' => 'Internship',
][$type] ?? 'Full Time';

$prompt = "Write a professional job posting for the following position in Nepal:\n\n" .
    "Job Title: $title\n" .
    ($company  ? "Company: $company\n"  : '') .
    ($location ? "Location: $location\n" : '') .
    "Job Type: $typeLabel\n\n" .
    "Generate two sections:\n" .
    "1. DESCRIPTION: A compelling 3–4 paragraph job description covering the role overview, key responsibilities (as bullet points), and what makes this opportunity exciting. Target candidates in Nepal.\n" .
    "2. REQUIREMENTS: A bullet-point list of required skills, qualifications, and experience for this role in the Nepali job market.\n\n" .
    "Format your response exactly like this (no extra text before or after):\n" .
    "DESCRIPTION:\n[description text]\n\nREQUIREMENTS:\n[requirements text]";

$GEMINI_API_KEY = 'AIzaSyBIW52C8bAUQXqLW04bg_vn-90kW8AZj4U';
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $GEMINI_API_KEY;

$payload = json_encode([
    'contents'         => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
    'generationConfig' => ['maxOutputTokens' => 1024, 'temperature' => 0.75],
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response !== false && $httpCode === 200) {
    $data = json_decode($response, true);
    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

    if ($text) {
        // Parse DESCRIPTION and REQUIREMENTS sections
        $description  = '';
        $requirements = '';

        if (preg_match('/DESCRIPTION:\s*([\s\S]*?)(?=REQUIREMENTS:|$)/i', $text, $dm)) {
            $description = trim($dm[1]);
        }
        if (preg_match('/REQUIREMENTS:\s*([\s\S]*?)$/i', $text, $rm)) {
            $requirements = trim($rm[1]);
        }

        // Fallback: if parsing failed, put everything in description
        if (empty($description)) {
            $description = trim($text);
        }

        echo json_encode(['description' => $description, 'requirements' => $requirements]);
        exit;
    }
}

// API failed — use a structured template fallback
$description = "We are looking for a skilled $title" . ($company ? " to join $company" : '') . ($location ? " in $location" : '') . ".\n\n" .
    "As a $title, you will play a key role in our team, contributing to projects and initiatives that drive our organization forward. This is an excellent opportunity for a motivated professional to grow their career in a dynamic environment.\n\n" .
    "Key Responsibilities:\n" .
    "• Perform core duties related to the $title role\n" .
    "• Collaborate with cross-functional teams to achieve goals\n" .
    "• Contribute ideas and solutions to improve processes\n" .
    "• Maintain high standards of quality in all deliverables\n" .
    "• Report progress and updates to the management team";

$requirements = "• Relevant educational background or equivalent experience\n" .
    "• Proven experience in a similar $title role\n" .
    "• Strong communication and teamwork skills\n" .
    "• Ability to work independently and meet deadlines\n" .
    "• Proficiency in relevant tools and technologies\n" .
    "• Positive attitude and eagerness to learn";

echo json_encode(['description' => $description, 'requirements' => $requirements]);
