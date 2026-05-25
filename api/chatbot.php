<?php
require_once '../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? '');
$history     = $input['history'] ?? [];

if ($userMessage === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Empty message']);
    exit;
}

// ============================================================
// RULE-BASED FALLBACK — always works, no API needed
// ============================================================
function getRuleBasedReply(string $msg): ?string {
    $m = strtolower($msg);

    // Greetings
    if (preg_match('/\b(hello|hi|hey|namaste|namaskar|yo)\b/', $m))
        return "Namaste! I'm KaamBot, your KaamKhoji assistant. I can help you find jobs, write cover letters, prepare for interviews, or guide you through the platform. What do you need?";

    // How to apply
    if (preg_match('/how.*(apply|application)|apply.*job|application.*process/', $m))
        return "To apply for a job on KaamKhoji:\n1. Browse jobs at Find Jobs\n2. Click a job to open its detail page\n3. Click \"Apply Now\"\n4. Write a cover letter (200–250 words)\n5. Upload your resume (PDF, max 5 MB)\n6. Submit — done! ✅\n\nYou can track your application status under My Applications.";

    // Cover letter
    if (preg_match('/cover.?letter|write.*letter|letter.*write/', $m))
        return "A strong cover letter for Nepal jobs should:\n• Start with a confident introduction (who you are, role you want)\n• Mention 2–3 relevant skills or achievements\n• Show why you want THIS company specifically\n• End with a clear call to action (\"I'd love to discuss…\")\n\nKeep it 200–250 words. Avoid copying templates — personalize every application!";

    // Resume / CV tips
    if (preg_match('/resume|cv\b|curriculum/', $m))
        return "Resume tips for Nepal job seekers:\n• Keep it to 1–2 pages\n• Put your strongest skills and experience first\n• Include measurable results (\"increased sales by 20%\")\n• Save as PDF before uploading\n• KaamKhoji accepts PDF resumes up to 5 MB\n\nNeed help with a specific section? Ask me!";

    // Interview tips
    if (preg_match('/interview|prepare.*job|job.*prepare/', $m))
        return "Interview tips:\n• Research the company thoroughly before going\n• Prepare answers for: \"Tell me about yourself\", \"Why this role?\", \"Your biggest weakness?\"\n• Dress professionally (formal for corporate, smart casual for startups)\n• Arrive 10 minutes early\n• Bring printed copies of your CV\n• Follow up with a thank-you email within 24 hours 🙏";

    // Saved jobs
    if (preg_match('/save.*job|saved.*job|bookmark/', $m))
        return "To save a job on KaamKhoji:\n• Click the bookmark icon (🔖) on any job card\n• View all saved jobs under \"Saved Jobs\" in the navigation\n• You can unsave a job anytime by clicking the icon again";

    // Track application / application status
    if (preg_match('/track|status|application.*status|my application/', $m))
        return "To track your applications:\n• Go to \"My Applications\" in the nav menu\n• You'll see each application with its status:\n  • Pending: employer hasn't reviewed yet\n  • Reviewed: employer has seen your application\n  • Accepted: congratulations! 🎉\n  • Rejected: keep applying, don't give up!\n\nStatus is updated by the employer.";

    // Post a job (employer)
    if (preg_match('/post.*job|create.*job|add.*job|employer.*post/', $m))
        return "To post a job as an employer:\n1. Log in as an Employer\n2. Click \"Post a Job\" in the navbar\n3. Fill in: Job Title, Company, Location, Type, Salary, Description, Requirements\n4. Click Post — your job goes live immediately! ✅\n\nYou can edit or close the job anytime from your Employer Dashboard.";

    // View applicants
    if (preg_match('/view.*applicant|see.*applicant|applicant.*list|who applied/', $m))
        return "To view applicants for your jobs:\n• Go to \"Applicants\" in the navbar (employer account)\n• Use the filter dropdown to select a specific job\n• You can see each applicant's name, email, cover letter, and resume (PDF)\n• Update their status: Pending → Reviewed → Accepted / Rejected";

    // Close / reopen job
    if (preg_match('/close.*job|reopen.*job|stop.*application|pause.*job/', $m))
        return "To close a job posting:\n• Go to your Employer Dashboard\n• Find the job in Your Job Postings\n• Click the \"Close\" button — no new applications will be accepted\n• Click \"Reopen\" anytime to make it active again";

    // Signup / register
    if (preg_match('/sign.?up|register|create.*account|new.*account/', $m))
        return "To create a KaamKhoji account:\n1. Click \"Sign Up\" in the top navigation\n2. Choose your role: Job Seeker or Employer\n3. Enter your name, email, and password\n4. Click Create Account — you're logged in instantly! 🚀\n\nRegistration is completely free.";

    // Login
    if (preg_match('/\blogin\b|\bsign.?in\b|\blog.?in\b/', $m))
        return "To log in:\n• Click \"Login\" in the top navigation\n• Enter your email and password\n• Click Login\n\nForgot your password? Click \"Forgot Password\" on the login page to reset it via email.";

    // Salary / pay
    if (preg_match('/salary|pay|wage|income|earn/', $m))
        return "Salary details are shown on each job card and detail page. You can also filter jobs by type (Full Time, Part Time, Remote, etc.) to narrow your search.\n\nFor negotiation tips: research market rates for your role in Nepal, highlight your skills, and always negotiate politely after receiving an offer. 💪";

    // Remote / work from home
    if (preg_match('/remote|work from home|wfh|online.*job/', $m))
        return "KaamKhoji has remote job listings! To find them:\n• Go to Find Jobs\n• In the \"All Types\" dropdown, select \"Remote\"\n• Browse all work-from-home opportunities in Nepal\n\nRemote jobs are marked with a green \"Remote\" badge on the card.";

    // Internship
    if (preg_match('/intern|internship|fresher|fresh graduate/', $m))
        return "Great news — KaamKhoji has internship listings perfect for freshers!\n• Go to Find Jobs\n• Filter by type: \"Internship\"\n• These are ideal for gaining experience right after graduation\n\nTip: A strong cover letter matters more than experience for internships. Show your enthusiasm and willingness to learn!";

    // Password reset / forgot password
    if (preg_match('/forgot.*password|reset.*password|change.*password|password.*forgot/', $m))
        return "To reset your password:\n1. Go to the Login page\n2. Click \"Forgot Password?\"\n3. Enter your registered email\n4. Check your email for a reset link\n5. Click the link and set a new password\n\nTo change your password while logged in, go to Profile → Change Password section.";

    // Profile / edit profile
    if (preg_match('/profile|edit.*info|update.*info|bio|phone.*number/', $m))
        return "To update your profile:\n• Click your avatar (top right) → Profile\n• You can update: Name, Phone (10 digits), Location, Bio\n• Upload a profile picture (JPG/PNG, max 3 MB)\n• Change your password in the same page\n\nYour email cannot be changed after registration.";

    // Contact / support
    if (preg_match('/contact|support|help|problem|issue|bug/', $m))
        return "For help with KaamKhoji:\n• Browse jobs: kaamkhoji.com/pages/jobs.php\n• Check your applications: My Applications\n• Employer dashboard: Dashboard → Employer\n\nIf you've found a bug or need direct support, please reach out to the KaamKhoji team. We're here to help! 🙏";

    // Thank you
    if (preg_match('/thank|thanks|dhanyabad|shukriya/', $m))
        return "You're welcome!  Best of luck with your job search. If you have more questions, I'm always here. नमस्ते!";

    // About KaamKhoji
    if (preg_match('/what is kaamkhoji|about kaamkhoji|kaamkhoji k ho/', $m))
        return "KaamKhoji is Nepal's free job portal connecting job seekers with employers.\n\n✅ Free for everyone\n✅ Real jobs from real employers\n✅ Apply with your resume (PDF)\n✅ Track your applications\n✅ Employers can post jobs and manage applicants\n\nKaamKhoji means \"Job Search\" in Nepali — that's exactly what we do! 🇳🇵";

    return null; // No rule matched
}

// ============================================================
// TRY GEMINI API FIRST, FALL BACK TO RULES
// ============================================================
$GEMINI_API_KEY = 'AIzaSyBIW52C8bAUQXqLW04bg_vn-90kW8AZj4U';

$systemPrompt = "You are KaamBot, a helpful assistant for KaamKhoji — Nepal's job portal. " .
    "You help job seekers find jobs, write cover letters, and prepare for interviews. " .
    "You also help employers post jobs and manage applicants. " .
    "Always be friendly, concise, and relevant to jobs and careers in Nepal. " .
    "Reply in the same language the user writes in (Nepali or English). " .
    "If the user asks about anything unrelated to jobs, careers, or KaamKhoji, politely apologize and redirect them: say you are only able to help with job-related topics. " .
    "Never use markdown dash lists (do not start lines with '- '). Use the bullet character • instead of dashes for any lists.";

$contents = [];
foreach (array_slice($history, -6) as $turn) {
    if (!isset($turn['role'], $turn['content'])) continue;
    $contents[] = ['role' => $turn['role'] === 'assistant' ? 'model' : 'user', 'parts' => [['text' => $turn['content']]]];
}
$contents[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];

$payload = json_encode([
    'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
    'contents'           => $contents,
    'generationConfig'   => ['maxOutputTokens' => 512, 'temperature' => 0.7],
]);

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $GEMINI_API_KEY;

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// If Gemini succeeds, return its reply
if ($response !== false && $httpCode === 200) {
    $data  = json_decode($response, true);
    $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    if ($reply) {
        echo json_encode(['reply' => $reply]);
        exit;
    }
}

// Gemini failed — try rule-based reply
$ruleReply = getRuleBasedReply($userMessage);
if ($ruleReply !== null) {
    echo json_encode(['reply' => $ruleReply]);
    exit;
}

// Generic fallback
echo json_encode(['reply' =>
    "Sorry, I can only help with job-related topics. Here's what I can assist with:\n" .
    "• Finding and applying for jobs\n" .
    "• Writing cover letters and resume tips\n" .
    "• Interview preparation\n" .
    "• Using KaamKhoji as a job seeker or employer\n\n" .
    "Try asking something like: \"How do I apply for a job?\" or \"Give me cover letter tips.\""
]);
