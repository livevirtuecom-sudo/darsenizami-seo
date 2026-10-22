<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, no-store, no-cache, max-age=0, s-maxage=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Vary: *');
header('X-Robots-Tag: noindex, nofollow');
try {
    $privateGeo = dirname(__DIR__) . '/dn-lms-private/geolocation.php';
    if (file_exists($privateGeo)) {
        require_once $privateGeo;
        $region = function_exists('visitor_region') ? visitor_region() : 'PK';
    } else {
        $country = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? $_SERVER['GEOIP_COUNTRY_CODE'] ?? 'PK';
        $region = strtoupper($country) === 'PK' ? 'PK' : 'INTL';
    }
    if (!in_array($region, ['PK', 'INTL'], true)) $region = 'PK';
    $pakistan = $region === 'PK';
    echo json_encode([
        'region' => $region,
        'currency' => $pakistan ? 'PKR' : 'USD',
        'fees' => [
            'admission' => $pakistan ? 3000 : 30,
            'group' => $pakistan ? 3000 : 30,
            'individual' => $pakistan ? 8000 : 100,
        ],
    ], JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(503);
    echo json_encode(['error' => 'Please contact admissions for your fee.']);
}
