<?php
$db = new PDO('mysql:host=localhost;dbname=kaamkhoji;charset=utf8mb4','root','');
$stmt = $db->query('SELECT id,title,salary,salary_min,salary_max FROM jobs LIMIT 20');
foreach ($stmt as $row) {
    echo implode(' | ', [$row['id'], $row['title'], $row['salary'], $row['salary_min'], $row['salary_max']]) . "\n";
}
$salaryMin = 170000; $salaryMax = 10000000;
$sql = "SELECT id,title,salary,salary_min,salary_max FROM jobs WHERE status='active' AND is_deleted=0 AND (deadline IS NULL OR deadline >= CURDATE()) AND salary_min IS NOT NULL AND salary_max IS NOT NULL AND salary_max >= ? AND salary_min <= ?";
$stmt2 = $db->prepare($sql);
$stmt2->execute([$salaryMin, $salaryMax]);
echo "\nFiltered rows:\n";
foreach ($stmt2 as $row) {
    echo implode(' | ', [$row['id'], $row['title'], $row['salary'], $row['salary_min'], $row['salary_max']]) . "\n";
}
