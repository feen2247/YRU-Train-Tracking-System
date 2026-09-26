$path = "resources/views/passenger/home/index.blade.php"
$content = [System.IO.File]::ReadAllText($path, [System.Text.Encoding]::UTF8)

# Replace all potential corrupted text strings
$replacements = @{
    'เน€เธชเน‰เธ™เธ—เธฒเธ‡เน€เธ”เธดเธ™เธฃเธ–' = 'เส้นทางเดินรถ'
    'เธˆเธธเธ”เธˆเธญเธ”เธฃเธฑเธš-เธชเนˆเธ‡' = 'จุดจอดรับ-ส่ง'
    'เธชเธ–เธฒเธ™เธฐเธ เธฒเธฃเนƒเธซเน‰เธšเธฃเธดเธ เธฒเธฃ' = 'สถานะการให้บริการ'
    'เธขเธฑเธ‡เน„เธกเนˆเธกเธตเธฃเธฒเธขเธ เธฒเธฃเน€เธฃเธตเธขเธ เธฃเธ–เนƒเธ™เธ‚เธ“เธฐเธ™เธตเน‰' = 'ยังไม่มีรายการเรียกรถในขณะนี้'
    'เธ เธ”เธ›เธธเนˆเธก "เน€เธฃเธตเธขเธ เธฃเธ–เธ—เธตเนˆเธ™เธตเนˆ" เน€เธžเธทเนˆเธญเน€เธฃเธดเนˆเธกเน€เธฃเธตเธขเธ เธฃเธ–เน„เธŸเธŸเน‰เธฒ' = 'กดปุ่ม "เรียกรถที่นี่" เพื่อเริ่มเรียกรถไฟฟ้า'
    'เน€เธฃเธตเธขเธ เธฃเธ–เธ—เธตเนˆเธ™เธตเนˆ' = 'เรียกรถที่นี่'
}

foreach ($key in $replacements.Keys) {
    $content = $content.Replace($key, $replacements[$key])
}

# Ensure EV-01 default coords are at Stop 1 (6.549929, 101.291254)
$content = $content.Replace('"EV-01": "6.548900, 101.291700"', '"EV-01": "6.549929, 101.291254"')

[System.IO.File]::WriteAllText($path, $content, [System.Text.Encoding]::UTF8)
Write-Output "Fixed home/index.blade.php"
