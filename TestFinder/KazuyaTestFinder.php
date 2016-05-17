<?php

namespace Beauty\TestFinder;

/*
 * LingTalfi 2016-05-13
 * 
 * How to use?
 * 
 * 
 * Prerequisites
 * -----------------
 * 
 * Create a web accessible folder, I will call mine bnb.
 * Foreach directory that you scan, create a corresponding symlink
 * in the bnb directory.
 * 
 * For instance, if you scan the /path/to/real/planets directory,
 * add the bnb/planets corresponding symlink. 
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
    private $dirName;
    private $host;

    public function __construct()
    {
        $this->dirs = [];
        $this->extensions = [];
        $this->host = $_SERVER['HTTP_HOST'];
        $this->dirName = 'bnb';

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
            $files = DirScanner::create()->scanDir($dir, function ($path, $rPath, $level) use ($ext2Length) {
                foreach ($ext2Length as $xt => $len) {
                    if ($xt === substr($rPath, -1 * $len)) {
                        return $rPath;
                    }
                }
            });
            foreach ($files as $file) {
                $tests = array_merge_recursive($tests, $this->arrayNest($file, array_filter(explode('/', $file))));
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

    public function setDirName(string $dirName)
    {
        $this->dirName = $dirName;
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
    private function arrayNest(string $file, array $array)
    {
        if (empty($array)) {
            return 0;
        }
        $firstValue = array_shift($array);
        $value = $this->arrayNest($file, $array);
        if (0 === $value) {
            $value = 'http://' . $this->host . '/' . $this->dirName . '/' . $file;
            $firstValue = 0;
        }
        return array($firstValue => $value);
    }


}
