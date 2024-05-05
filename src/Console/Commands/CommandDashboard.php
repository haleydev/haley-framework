<?php

namespace Haley\Console\Commands;

use Haley\Console\ConsoleMemory;
use Haley\Collections\Str;
use Haley\Shell\Shell;

class CommandDashboard
{
    public function run()
    {
        $commands = ConsoleMemory::$commands;
        $commands_list = [];
        $width = 0;

        foreach ($commands as $value) {
            if ($value['list']) {
                $title = $value['title'] ?? 'Available commands';

                if (!array_key_exists($title, $commands_list)) $commands_list[$title] = [];

                $command = $value['command'];
                $options = [];

                if (preg_match_all('/{(.*?)}/', $command, $matches)) {
                    foreach ($matches[0] as $k => $v) {
                        $required = Str::end($matches[1][$k] ?? '', '?') ? 'optional' : 'required';
                        $n = trim($matches[1][$k] ?? '', '?');

                        $command = trim(str_replace($v, '', $command));
                        $options[] = "[$required:$n]";
                    }
                };

                $options = implode(' ', $options);

                $start = Shell::green($command, false, false);

                if (!empty($options)) $start .= ' ' . Shell::blue($options, false, false);

                $end = null;

                if (!empty($value['description'])) $end = Shell::normal($value['description'], false, false);

                $strlen = strlen(Shell::decolorize($start));

                if ($end) $strlen += strlen(Shell::decolorize($end));
                if ($strlen > $width) $width = $strlen;

                $commands_list[$title][] = [
                    'start' => $start,
                    'end' => $end
                ];
            }
        }

        $width += 7;
        $header_title = ' HALEY FRAMEWORK ';
        $header_version = ' 1.0 beta ';
        $header_width = $width - (strlen($header_title) + strlen($header_version)) - 4;

        Shell::br()->green(str_repeat('-', $width), false)->br();

        Shell::green('|', false);
        Shell::yellow($header_title, false);
        Shell::green('|' . str_repeat('-', intval($header_width)) . '|', false);
        Shell::gray($header_version, false);
        Shell::green('|', false);

        Shell::br()->green(str_repeat('-', $width), false)->br()->br();

        foreach ($commands_list as $title => $value) {
            Shell::br()->yellow($title)->br();

            foreach ($value as $x) Shell::list($x['start'], $x['end'], '-', $width)->br();
        }

        Shell::br();
    }
}
