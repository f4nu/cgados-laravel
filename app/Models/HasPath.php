<?php

namespace App\Models;

trait HasPath
{
    public function path(): string
    {
        $path = $this->name;
        $parent = $this->parent;

        while ($parent) {
            $path = $parent->name . '/' . $path;
            $parent = $parent->parent;
        }
        return preg_replace('#/+#', '/', $path);
    }
}
