<?php

namespace Core\View\Compiler;

class CompilerPHP
{
    protected $view;

    public function run($view)
    {
        $this->view = $view;

        // comment
        $regex = "/{{--(.*?)--}}/s";
        if (preg_match_all($regex, $this->view, $match)) {
            foreach ($match as $key => $value) {
                $this->view = str_replace($value, '', $this->view);
            }
        }

        // echo
        $regex = "/{{(.*?)}}/s";
        if (preg_match_all($regex, $this->view, $match)) {
            foreach ($match[0] as $key => $value) {
                $this->view = str_replace($value, preg_replace('/( ){2,}/', '$1', '<?php echo ' . $match[1][$key] . ' ?>'), $this->view);
            }
        }

        // elseif
        $regex = "/@elseif\((.*?)\)/s";
        if (preg_match_all($regex, $this->view, $match)) {
            foreach ($match[0] as $key => $value) {
                $this->view = str_replace($value, '<?php elseif(' . $match[1][$key] . ') :?>', $this->view);
            }
        }

        // if
        $regex = "/@if\((.*?)\)/s";
        if (preg_match_all($regex, $this->view, $match)) {
            foreach ($match[0] as $key => $value) {
                $this->view = str_replace($value, '<?php if(' . $match[1][$key] . ') :?>', $this->view);
            }
        }

        // php
        $regex = "/@php(.*?)@endphp/s";
        if (preg_match_all($regex, $this->view, $match)) {
            foreach ($match[0] as $key => $value) {
                $this->view = str_replace($value, '<?php ' . $match[1][$key] . ' ?>', $this->view);
            }
        }

        // foreach
        $regex = "/@foreach\((.*?)\)/s";
        if (preg_match_all($regex, $this->view, $match)) {
            foreach ($match[0] as $key => $value) {
                $this->view = str_replace($value, '<?php foreach(' . $match[1][$key] . ') :?>', $this->view);
            }
        }

        // while
        $regex = "/@while\((.*?)\)/s";
        if (preg_match_all($regex, $this->view, $match)) {
            foreach ($match[0] as $key => $value) {
                $this->view = str_replace($value, '<?php while(' . $match[1][$key] . ') :?>', $this->view);
            }
        }

        $this->replaceTags();

        return $this->view;
    }

    private function replaceTags()
    {
        $search = ['@else', '@endif', '@endforeach', '@endwhile'];
        $replace = ['<?php else :?>', '<?php endif ?>', '<?php endforeach ?>', '<?php endwhile ?>'];

        $this->view = str_replace($search, $replace, $this->view);
    }
}
