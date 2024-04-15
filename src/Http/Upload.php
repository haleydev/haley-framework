<?php

namespace Haley\Http;

class Upload
{
    protected array|null $input = null;

    public function __construct(string $input)
    {
        $this->input = request()->file($input);
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if (empty($this->input['name'])) return false;

        if (is_string($this->input['name'])) {
            if (!is_uploaded_file($this->input['tmp_name']) or !empty($this->input['error'])) {
                return false;
            }
        } else {
            foreach ($this->input['tmp_name'] as $key => $tmp_name) {
                if (!is_uploaded_file($tmp_name) and !empty($this->input[$key]['error'])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return string|int
     */
    public function getSize(bool $format = true)
    {
        if (empty($this->input['name'])) return $format ? formatSize(0) : 0;

        $size = 0;

        if (is_array($this->input['size'])) {
            foreach ($this->input['size'] as $value) {
                $size += $value;
            }
        } else {
            $size = $this->input['size'];
        }

        return $format ? formatSize($size) : $size;
    }

    /**
     * @return string|array|null
     */
    public function getExtensions()
    {
        if (empty($this->input['name'])) return null;

        $extensions = [];

        if (is_array($this->input['name'])) {

            foreach ($this->input['name'] as $file) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);

                if (!in_array($extension, $extensions)) {
                    $extensions[] = $extension;
                }
            }

            return $extensions;
        }

        return pathinfo($this->input['name'], PATHINFO_EXTENSION);
    }

    /**
     * @return string|array|null
     */
    public function getOriginalNames()
    {
        if (empty($this->input['name'])) return null;

        return $this->input['name'];
    }

    /**
     * @return string|array|null
     */
    public function getBaseNames()
    {
        if (empty($this->input['name'])) return null;

        $extensions = [];

        if (is_array($this->input['name'])) {

            foreach ($this->input['name'] as $file) {
                $extension = pathinfo($file, PATHINFO_FILENAME);

                if (!in_array($extension, $extensions)) {
                    $extensions[] = $extension;
                }
            }

            return $extensions;
        }

        return pathinfo($this->input['name'], PATHINFO_FILENAME);
    }

    /**
     * @return array|string|false
     */
    public function save(string $path, string|array $names = [])
    {
        if (!$this->isValid()) return false;

        createDir($path);

        $names = $this->namesResolve($names);
        $path = directorySeparator($path);

        if (is_string($this->input['tmp_name'])) {
            move_uploaded_file($this->input['tmp_name'], $path . DIRECTORY_SEPARATOR . $names);
        } elseif (is_array($this->input['tmp_name'])) {
            foreach ($this->input['tmp_name'] as $key => $value) move_uploaded_file($value, $path . DIRECTORY_SEPARATOR . $names[$key]);            
        }

        return $names;
    }

    /**
     * @return string|array
     */
    protected function namesResolve(string|array $names)
    {
        $original_names = $this->getOriginalNames();

        if (is_string($original_names)) {
            if (empty($names)) {
                return md5(bin2hex(random_bytes(5))) . '.' . pathinfo($original_names, PATHINFO_EXTENSION);
            } else {
                is_array($names) ? $names = $names[0] : $names;
                return $names . '.' . pathinfo($original_names, PATHINFO_EXTENSION);
            }
        }

        empty($names) ? $names = [] : $names;
        is_string($names) ? $names = [$names] : $names;

        $new_names = [];
        foreach ($original_names as $key => $value) {
            if (isset($names[$key])) {
                $new_names[$key] = $names[$key] . '.' . pathinfo($value, PATHINFO_EXTENSION);
            } else {
                $new_names[$key] = md5(bin2hex(random_bytes(5))) . '.' . pathinfo($value, PATHINFO_EXTENSION);
            }
        }

        return $new_names;
    }
}
