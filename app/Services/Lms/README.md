# LMS Services

Folder ini berisi services untuk koneksi ke LMS eksternal.

## File:
- `LmsConnectorInterface.php` - Interface untuk semua connector
- `SpadaConnector.php` - Connector untuk SPADA Polinema (Moodle-based)
  - URL: https://slc.polinema.ac.id/spada/
  - Auth via: https://siakad.polinema.ac.id/beranda
- `MoodleConnector.php` - Generic Moodle connector
- `GoogleClassroomConnector.php` - Connector untuk Google Classroom API
- `CanvasConnector.php` - Connector untuk Canvas LMS API

## SPADA Polinema Integration

SPADA Polinema menggunakan sistem Moodle dengan autentikasi melalui SIAKAD.

### Authentication Flow:
1. Login ke SIAKAD (https://siakad.polinema.ac.id/beranda)
2. SIAKAD redirect ke SPADA dengan session cookie
3. Gunakan session untuk akses SPADA API/scraping

### Methods:
- `authenticate($username, $password)` - Login via SIAKAD
- `isAuthenticated()` - Check session validity
- `getCourses()` - Get list mata kuliah
- `getAssignments($courseId)` - Get tugas dari MK
- `getMaterials($courseId)` - Get materi dari MK

### Usage:
```php
$spada = new SpadaConnector();
$spada->authenticate('username', 'password');

$courses = $spada->getCourses();
foreach ($courses as $course) {
    $assignments = $spada->getAssignments($course['external_id']);
    $materials = $spada->getMaterials($course['external_id']);
}
```

### Notes:
- SPADA berbasis Moodle, bisa gunakan Moodle Web Services API
- Jika API tidak tersedia, fallback ke web scraping
- Session disimpan di cache (6 jam)
- Butuh credentials SIAKAD mahasiswa untuk testing

