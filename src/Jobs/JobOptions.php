<?php

namespace Haley\Jobs;

class JobOptions
{
    public function name(string $value)
    {
        $key = array_key_last(JobMemory::$jobs);

        if (empty(JobMemory::$jobs[$key]['name'])) {
            JobMemory::$jobs[$key]['name'] = $value;
        } else {
            JobMemory::$jobs[$key]['name'] .= '.' . $value;
        }

        return $this;
    }

    public function description(string $value)
    {
        $key = array_key_last(JobMemory::$jobs);

        JobMemory::$jobs[$key]['description'] = $value;     

        return $this;
    }
}