<?php

require_once '../includes/auth.php';
require_once '../includes/db.php';

// Set response type to JSON
header('Content-Type: application/json');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$pdo = getPDO();

// Get search parameters (sanitized)
$keyword    = trim($_GET['keyword']    ?? '');
$location   = trim($_GET['location']   ?? '');
$type       = trim($_GET['type']       ?? '');
$experience = trim($_GET['experience'] ?? '');
$salaryMin  = (int)($_GET['salary_min'] ?? 0);
$salaryMax  = (int)($_GET['salary_max'] ?? 0);
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 20;
$offset     = ($page - 1) * $perPage;

$validTypes      = ['full-time','part-time','remote','contract','internship'];
$validExperience = ['entry','mid','senior'];

$baseWhere = "jobs.status = 'active' AND jobs.is_deleted = 0 AND (jobs.deadline IS NULL OR jobs.deadline >= CURDATE())";

// Build query dynamically using PDO prepared statements
$sql    = "SELECT jobs.*, users.name AS employer_name,
                  (SELECT COUNT(*) FROM applications WHERE job_id = jobs.id) AS app_count
           FROM jobs
           JOIN users ON jobs.employer_id = users.id
           WHERE $baseWhere";
$params = [];

// Keyword filter — title, company, description, requirements
if (!empty($keyword)) {
    $sql .= " AND (jobs.title LIKE ? OR jobs.company LIKE ? OR jobs.description LIKE ? OR jobs.requirements LIKE ?)";
    $like = '%' . $keyword . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

// Location filter
if (!empty($location)) {
    $sql .= " AND jobs.location LIKE ?";
    $params[] = '%' . $location . '%';
}

// Job type filter
if (!empty($type) && in_array($type, $validTypes)) {
    $sql .= " AND jobs.type = ?";
    $params[] = $type;
}

// Experience level filter
if (!empty($experience) && in_array($experience, $validExperience)) {
    $sql .= " AND jobs.experience_level = ?";
    $params[] = $experience;
}

// Salary range filter — match jobs whose salary range overlaps the selected range.
// This supports both numeric salary_min/max columns and fallback parsing of salary text.
$salaryExprMin = "CAST(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(SUBSTRING_INDEX(jobs.salary, '-', 1)), 'Rs.', ''), ',', ''), 'NPR', ''), ' ', '') AS UNSIGNED)";
$salaryExprMax = "CAST(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(SUBSTRING_INDEX(jobs.salary, '-', -1)), 'Rs.', ''), ',', ''), 'NPR', ''), ' ', '') AS UNSIGNED)";

if ($salaryMin > 0 && $salaryMax > 0) {
    if ($salaryMin > $salaryMax) {
        [$salaryMin, $salaryMax] = [$salaryMax, $salaryMin];
    }
    $sql .= " AND ((jobs.salary_min IS NOT NULL AND jobs.salary_max IS NOT NULL"
          . " AND jobs.salary_max >= ? AND jobs.salary_min <= ?)"
          . " OR ((jobs.salary_min IS NULL OR jobs.salary_max IS NULL)"
          . " AND {$salaryExprMax} >= ? AND {$salaryExprMin} <= ?))";
    $params[] = $salaryMin;
    $params[] = $salaryMax;
    $params[] = $salaryMin;
    $params[] = $salaryMax;
} elseif ($salaryMin > 0) {
    $sql .= " AND ((jobs.salary_max IS NOT NULL AND jobs.salary_max >= ?)"
          . " OR (jobs.salary_max IS NULL AND jobs.salary_min >= ?)"
          . " OR ({$salaryExprMax} >= ?))";
    $params[] = $salaryMin;
    $params[] = $salaryMin;
    $params[] = $salaryMin;
} elseif ($salaryMax > 0) {
    $sql .= " AND ((jobs.salary_min IS NOT NULL AND jobs.salary_min <= ?)"
          . " OR (jobs.salary_min IS NULL AND jobs.salary_max <= ?)"
          . " OR ({$salaryExprMin} <= ?))";
    $params[] = $salaryMax;
    $params[] = $salaryMax;
    $params[] = $salaryMax;
}

$sql .= " ORDER BY jobs.created_at DESC LIMIT $perPage OFFSET $offset";

// Execute with prepared statement
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

// Add is_saved flag for logged-in seekers
if (isLoggedIn() && getRole() === 'seeker' && !empty($jobs)) {
    $seekerId  = (int)getUserId();
    $ids       = implode(',', array_map('intval', array_column($jobs, 'id')));
    $savedStmt = $pdo->query("SELECT job_id FROM saved_jobs WHERE seeker_id = $seekerId AND job_id IN ($ids)");
    $savedIds  = $savedStmt->fetchAll(PDO::FETCH_COLUMN);
    $savedSet  = array_flip($savedIds);
    foreach ($jobs as &$job) {
        $job['is_saved'] = isset($savedSet[$job['id']]);
    }
    unset($job);
} else {
    foreach ($jobs as &$job) {
        $job['is_saved'] = false;
    }
    unset($job);
}

// Return as JSON
echo json_encode($jobs);
