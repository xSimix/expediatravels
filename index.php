<?php
/**
 * Root entry point that routes users to the appropriate experience
 * (mobile app-style or desktop web) based on the user agent.
 */

declare(strict_types=1);

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

$mobilePattern = '/(android|iphone|ipad|ipod|blackberry|bb10|mobile|iemobile|opera mini|opera mobi|silk)/i';

$isMobile = (bool) preg_match($mobilePattern, $userAgent);

$target = $isMobile ? '/aplicacion/' : '/sitio_web/';

header('Location: ' . $target, true, 302);
exit;
