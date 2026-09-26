<?php
// Script to purge all fake mock reviews from live server

$url = 'https://406665014.student.yru.ac.th/api/storage/init.js';
$jsContent = file_get_contents($url);

$surveys = [];
if (preg_match('/serverStorage\s*=\s*(\{[\s\S]*?\});/', $jsContent, $matches)) {
    $storage = json_decode($matches[1], true);
    if (!empty($storage['yru_surveys'])) {
        $surveys = json_decode($storage['yru_surveys'], true);
        if (!is_array($surveys)) $surveys = [];
    }
}

echo "Found " . count($surveys) . " surveys on live server.\n";

$fakeEmails = ['fatimah@gmail.com', 'nuriyah@outlook.com', 'abdul@gmail.com'];
$fakeKeywords = [
    "ระมัดระวังคนข้ามถนนดีมาก", "ยิ้มแย้มแจ่มใส ทักทายผู้โดยสาร", "รถสะอาดเอี่ยม ขับนิ่งมาก",
    "ประสานงานกับสถานีดีเยี่ยม", "ไม่มีการกระตุกเลย", "มีจิตบริการสูงมาก", "เข้าจอดเทียบชานชาลาตรงจุด",
    "ลมโกรกสบาย", "ช่วยเหลือนักศึกษาขนของขึ้นรถ", "ให้ทางคนข้ามถนนเสมอ", "ไม่กระชาก",
    "รถสะอาดเรียบร้อย ขับนิ่ม นั่งสบาย", "ไม่ต้องรอนาน", "ช่วยพยุงตอนขึ้นรถ", "เขตจำกัดความเร็วเคร่งครัด",
    "มีน้ำใจบริการ", "บรรยากาศดีครับ", "อยากให้เพิ่มรอบช่วงเย็นเลิกเรียนครับ", "เป็นกันเอง",
    "ขับรถนุ่ม ไม่เร็ว ปลอดภัยดีมากค่ะ", "มีไมตรีจิต", "เข้าเทียบชานชาลาเป๊ะ", "พนักงานน่ารักมาก",
    "ลมเย็นดีค่ะ", "ช่วยแนะนำเส้นทางจุดจอดในมหาลัยดีมากครับ", "ผู้โดยสารให้คะแนนการบริการระดับดีเยี่ยม",
    "คนขับพูดจาสุภาพมากครับ รถขับนิ่มปลอดภัยดีมาก", "รถวิ่งช้าไปนิดนึง แต่อย่างอื่นดีหมดเลยค่ะ",
    "มารับตรงเวลา ดีมากครับ", "สุดยอดการให้บริการครับ ประทับใจมาก"
];

$realSurveys = array_values(array_filter($surveys, function($s) use ($fakeEmails, $fakeKeywords) {
    if (!is_array($s)) return false;
    $email = strtolower(trim($s['userEmail'] ?? ''));
    if (in_array($email, $fakeEmails)) return false;

    $comment = trim($s['comment'] ?? '');
    foreach ($fakeKeywords as $kw) {
        if (mb_strpos($comment, $kw) !== false) {
            return false;
        }
    }
    return true;
}));

echo "Remaining genuine user surveys: " . count($realSurveys) . "\n";
foreach ($realSurveys as $rs) {
    echo "- [{$rs['driverName']}] '{$rs['comment']}' by {$rs['userEmail']}\n";
}

// Sync cleaned surveys back to server
$postData = json_encode([
    'payload' => [
        'yru_surveys' => json_encode($realSurveys, JSON_UNESCAPED_UNICODE)
    ]
]);

$ch = curl_init('https://406665014.student.yru.ac.th/api/storage/sync');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
