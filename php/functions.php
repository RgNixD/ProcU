<?php
    // To prevent direct access to this file
    preventDirectAccess();

    function preventDirectAccess($filePath = null)
    {
        if ($filePath === null) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
            $filePath = $backtrace[0]['file'];
        }

        if (basename($_SERVER['PHP_SELF']) === basename($filePath)) {
            http_response_code(403);
            include __DIR__ . '/access_guard.php';
            exit;
        }
    }

    function generatePageTitle($pageTitle = '', $siteName = 'PPCS')
    {
        if (!empty($pageTitle)) {
            return $pageTitle . " - " . $siteName;
        }

        $filename = basename($_SERVER['PHP_SELF'], ".php");
        $dirname = basename(dirname($_SERVER['PHP_SELF']));

        $filename = str_replace(['-', '_'], ' ', $filename);
        $dirname = str_replace(['-', '_'], ' ', $dirname);

        $autoTitle = ucwords($filename);

        if (strtolower($filename) === 'index') {
            return ($dirname !== '.' && $dirname !== 'php')
                ? "Home - " . $siteName
                : $siteName;
        }

        return $autoTitle . " - " . $siteName;
    }