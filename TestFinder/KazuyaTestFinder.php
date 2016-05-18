<?php

namespace Beauty\TestFinder;

/*
 * LingTalfi 2016-05-13
 * 
 * 
 * 
 * About the Kazuya strategy
 * -----------------------------
 * 
 * A TestFinder's role is to find/gather the test pages (presumably on your local machine).
 * 
 * There are two main workflows when working with test pages:
 * 
 * - one root folder: all your tests are located into ONE root directory
 * - multiple root folders: your tests are scattered in multiple root directories (presumably on your machine).
 * 
 * Now remember that beauty is an html gui; this means we need a web server anyway, and therefore we will need to create
 * at least one web accessible folder to access our tests.
 * 
 * Kazuya's strategy is about taking advantage of that necessary web accessible folder, and use it as the only 
 * root for all your tests. This basically means that kazuya is based on the "one root folder" workflow.
 * 
 * Let's call the kazuya's web root directory the kazuya root directory.
 * 
 * Now since your tests might be scattered in various places of your machine, but since we want all of them
 * to be accessible from that one place at the same time, we need to create symlinks, inside the kazuya root directory,
 * which points to the various locations where the tests actually are.
 * 
 * 
 * 
 * Tutorial
 * ------------ 
 * 
 * Create a web accessible folder, I will call mine bnb.
 * Then, create a symlink, inside the bnb directory, for every "base test" directory that you have;
 * 
 * where a base test directory is a directory that contains directly or indirectly an arbitrary number of "test pages" (https://github.com/lingtalfi/Dreamer/blob/master/UnitTesting/BeautyNBeast/pattern.beautyNBeast.eng.md#test-page).
 * Note that Kazuya find tests recursively.
 * 
 * 
 * 
 * For instance, here is a working tree structure
 * 
 * - /path/to/mywebsite/www/bnb 
 * ----- planets ( symlink to /path/to/real/planets )      
 * ----- applicationOne ( symlink to /path/to/app1tests )      
 * ----- ...      
 *  
 * 
 * 
 * 
 * 
 * 
 */
use DirScanner\DirScanner;

class KazuyaTestFinder implements TestFinderInterface
{

    private $dirs;
    private $extensions;
    private $host;

    public function __construct()
    {
        $this->dirs = [];
        $this->extensions = [];
        $this->host = $_SERVER['HTTP_HOST'];

    }


    public static function create()
    {
        return new static();
    }




    //------------------------------------------------------------------------------/
    // IMPLEMENTS TestFinderInterface
    //------------------------------------------------------------------------------/
    /**
     * @return array
     *
     *      Returns an array of <item>.
     *      An <item> is either:
     *          - an array of test urls
     *          - an array of <item>
     *
     */
    public function getTestPageUrls() : array
    {
        $tests = [];


        // prepare the extension => length array for speed 
        $ext2Length = [];
        foreach ($this->extensions as $x) {
            $ext2Length[$x] = strlen($x);
        }


        // now parse the dirs and collects the tests
        foreach ($this->dirs as $dir) {
            $dirName = basename($dir);
            $files = DirScanner::create()
                ->setFollowLinks(true)
                ->scanDir($dir, function ($path, $rPath, $level) use ($ext2Length) {
                    foreach ($ext2Length as $xt => $len) {
                        if ($xt === substr($rPath, -1 * $len)) {
                            return $rPath;
                        }
                    }
                });
            foreach ($files as $file) {
                $tests = array_merge_recursive($tests, $this->arrayNest($file, $dirName, array_filter(explode('/', $file))));
            }
        }
        return $tests;
    }

    
    //------------------------------------------------------------------------------/
    // 
    //------------------------------------------------------------------------------/
    public function addDir(string $d)
    {
        $this->dirs[] = $d;
        return $this;
    }

    public function addExtension(string $extension)
    {
        $this->extensions [] = $extension;
        return $this;
    }

    public function setHost(string $host)
    {
        $this->host = $host;
        return $this;
    }



    //------------------------------------------------------------------------------/
    // 
    //------------------------------------------------------------------------------/
    private function arrayNest(string $file, string $dirName, array $array)
    {
        if (empty($array)) {
            return 0;
        }
        $firstValue = array_shift($array);
        $value = $this->arrayNest($file, $dirName, $array);
        if (0 === $value) {
            $value = 'http://' . $this->host . '/' . $dirName . '/' . $file;
            $firstValue = 0;
        }
        return array($firstValue => $value);
    }


}
