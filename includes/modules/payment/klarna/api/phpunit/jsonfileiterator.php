<?php
class JSONFileIterator implements Iterator {
    protected $file;
    protected $key = 0;
    protected $current;

    public function __construct($file) {
        $this->file = fopen($file, 'r');
    }

    public function __destruct() {
        fclose($this->file);
    }

    public function rewind() {
        rewind($this->file);
        $this->current = fgets($this->file);
        $this->key = 0;
    }

    public function valid() {
        return !feof($this->file);
    }

    public function key() {
        return $this->key;
    }

    public function current() {
        $tmp = json_decode ($this->current, true);
        $out = array();
        foreach ($tmp as $v) {
            if (is_string ($v)) {
                $out[] = utf8_decode ($v);
            } else {
                $out[] = $v;
            }
        }
        return $out;
    }

    public function next() {
        $this->current = fgets($this->file);
        $this->key++;
    }
}
?>
