<?php

use Bat\FileSystemTool;
use Beauty\TestFinder\AuthorTestFinder;
use Beauty\TestFinder\KazuyaTestFinder;use DirScanner\DirScanner;
use DirScanner\YorgDirScannerTool;

require_once "bigbang.php"; // start the universe







/**
 * Prerequisites:
 *
 * you want to execute every tests inside the ../planets directory,
 * and you've created a web accessible bnb directory, inside of which
 * you've create a symlink to the real planet directory.
 *
 * All your tests have the extension .test.php.
 *
 */
//------------------------------------------------------------------------------/
// COLLECT TESTS 
//------------------------------------------------------------------------------/
$dir = __DIR__ . "/../planets";
$testPageUrls = KazuyaTestFinder::create()
    ->addDir($dir)
    ->setDirName('bnb/planets')
    ->addExtension('.test.php')
    ->getTestPageUrls();

$openGroups = [
    'js',
    'myApp/kazam',
];




//------------------------------------------------------------------------------/
// DISPLAYING THE HTML PAGE
//------------------------------------------------------------------------------/
/**
 * Just copy the snippet below, it works every time...
 */
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <script src="http://code.jquery.com/jquery-2.1.4.min.js"></script>
    <!--    <script src="/libs/jquery/jquery-2.1.4.min.js"></script>-->
    <script src="/libs/beauty/js/beauty.js"></script>
    <title>Html page</title>
</head>

<body>
<div id="beauty-gui-container"></div>

<script>
    (function ($) {
        $(document).ready(function () {


            var tests = <?php echo json_encode($testPageUrls); ?>;
            var jContainer = $('#beauty-gui-container');
            var beauty = new window.beauty({
                tests: tests
            });
            beauty.loadTemplateWithJsonP('default', jContainer, function () {
                beauty.start(jContainer);
                beauty.closeAllGroups();
                beauty.openGroups(<?php echo json_encode($openGroups); ?>, true);
            });
        });
    })(jQuery);
</script>

</body>
</html>