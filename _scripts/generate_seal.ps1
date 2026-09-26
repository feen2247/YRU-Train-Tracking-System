Add-Type -AssemblyName System.Drawing
$size = 400
$bmp = New-Object System.Drawing.Bitmap($size, $size)
$g = [System.Drawing.Graphics]::FromImage($bmp)
$g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
$g.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::AntiAliasGridFit
$g.Clear([System.Drawing.Color]::Transparent)

$color = [System.Drawing.Color]::FromArgb(235, 30, 64, 175) # Authentic corporate blue ink
$penOuter = New-Object System.Drawing.Pen($color, 5.5)
$penInner = New-Object System.Drawing.Pen($color, 2)
$penInner.DashStyle = [System.Drawing.Drawing2D.DashStyle]::Dash

# Concentric Circles
$g.DrawEllipse($penOuter, 14, 14, 372, 372)
$g.DrawEllipse($penInner, 30, 30, 340, 340)
$g.DrawEllipse($penOuter, 80, 80, 240, 240)

# Text setup
$brush = New-Object System.Drawing.SolidBrush($color)
$sf = New-Object System.Drawing.StringFormat
$sf.Alignment = [System.Drawing.StringAlignment]::Center
$sf.LineAlignment = [System.Drawing.StringAlignment]::Center

# Fonts
$fThaiOuter = New-Object System.Drawing.Font('Tahoma', 10, [System.Drawing.FontStyle]::Bold)
$fEngOuter = New-Object System.Drawing.Font('Arial', 9.5, [System.Drawing.FontStyle]::Bold)
$fCenterThai = New-Object System.Drawing.Font('Tahoma', 11, [System.Drawing.FontStyle]::Bold)
$fCenterEng = New-Object System.Drawing.Font('Arial', 13, [System.Drawing.FontStyle]::Bold)
$fRef = New-Object System.Drawing.Font('Arial', 8.5, [System.Drawing.FontStyle]::Bold)

# Outer ring texts
$g.DrawString('★ บริษัท เซ้าท์ พี.เค. อินเตอร์ กรุ๊ป จำกัด ★', $fThaiOuter, $brush, [System.Drawing.RectangleF]::new(35, 47, 330, 25), $sf)
$g.DrawString('★ SOUTH P.K. INTER GROUP CO., LTD. ★', $fEngOuter, $brush, [System.Drawing.RectangleF]::new(35, 328, 330, 25), $sf)

# Center circle contents
$g.DrawString('OFFICIAL E-SEAL', $fCenterEng, $brush, [System.Drawing.RectangleF]::new(80, 115, 240, 26), $sf)
$g.DrawString('บจก. เซ้าท์ พี.เค. อินเตอร์ กรุ๊ป', $fCenterThai, $brush, [System.Drawing.RectangleF]::new(80, 146, 240, 30), $sf)

# Center dividing lines
$penLine = New-Object System.Drawing.Pen($color, 1.8)
$g.DrawLine($penLine, 115, 188, 285, 188)
$g.DrawString('ตราประทับรับรองดิจิทัล', $fCenterThai, $brush, [System.Drawing.RectangleF]::new(80, 196, 240, 26), $sf)
$g.DrawLine($penLine, 115, 228, 285, 228)

$g.DrawString('VERIFIED & CERTIFIED', $fRef, $brush, [System.Drawing.RectangleF]::new(80, 238, 240, 18), $sf)
$g.DrawString('REF: #PK-SEC-2028', $fRef, $brush, [System.Drawing.RectangleF]::new(80, 258, 240, 18), $sf)

# Rotate slight angle (-3.5 deg) for realistic stamp impression
$bmpRot = New-Object System.Drawing.Bitmap($size, $size)
$gRot = [System.Drawing.Graphics]::FromImage($bmpRot)
$gRot.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
$gRot.TranslateTransform(200, 200)
$gRot.RotateTransform(-3.5)
$gRot.TranslateTransform(-200, -200)
$gRot.DrawImage($bmp, 0, 0)

$outPath = 'c:\Users\ASUS\Downloads\YRU-Train-Tracking-System\public\img\south-pk-seal.png'
$bmpRot.Save($outPath, [System.Drawing.Imaging.ImageFormat]::Png)

$g.Dispose()
$gRot.Dispose()
$bmp.Dispose()
$bmpRot.Dispose()
Write-Output "SUCCESS: $outPath"
