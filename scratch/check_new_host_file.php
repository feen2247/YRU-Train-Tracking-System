<?php
$conn = @ftp_connect('host.site.yru.ac.th', 21, 15);
if (!$conn || !@ftp_login($conn, 's406665014', 'Tasnee_047')) {
    die("FTP login failed\n");
}
ftp_pasv($conn, true);

$tempFile = tempnam(sys_get_temp_dir(), 'remote_home');
ftp_get($conn, $tempFile, 'htdocs/406665014.site.yru.ac.th/resources/views/passenger/home/index.blade.php', FTP_BINARY);
ftp_close($conn);

$content = file_get_contents($tempFile);
unlink($tempFile);

preg_match('/function getGarageClusterIcon[\s\S]*?^\s*\}/m', $content, $match);
echo "=== FUNCTION ON NEW HOST ===\n";
echo ($match[0] ?? 'NOT FOUND') . "\n";
