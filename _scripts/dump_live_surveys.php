<?php
$url = 'https://406665014.student.yru.ac.th/api/storage/init.js';
$jsContent = file_get_contents($url);
if (preg_match('/serverStorage\s*=\s*(\{[\s\S]*?\});/', $jsContent, $matches)) {
    $storage = json_decode($matches[1], true);
    if (!empty($storage['yru_surveys'])) {
        $surveys = json_decode($storage['yru_surveys'], true);
        echo json_encode($surveys, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
