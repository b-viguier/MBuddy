<?php

namespace bviguier\tests\MBuddy\TestUtils;

class TempFolder
{
    public function __construct(string $prefix)
    {
        $this->path = sys_get_temp_dir();
        $uniqueName = uniqid("MBuddyTest_{$prefix}_");
        $this->path .= "/$uniqueName/";
        mkdir($this->path, 0777, true);
    }

    public function directory(): \DirectoryIterator
    {
        return new \DirectoryIterator($this->path);
    }

    public string $path;
}
