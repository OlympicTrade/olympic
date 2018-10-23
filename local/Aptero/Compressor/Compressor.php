<?php
namespace Aptero\Compressor;

class Compressor {
    public function compress($files, $result)
    {
        if (file_exists($result)) {
            $compsTime = filemtime($result);
            foreach($files as $file) {
                if(filemtime($file) > $compsTime) {
                    $this->glueFiles($files, $result);
                    return;
                }
            }
        } else {
            $this->glueFiles($files, $result);
        }
    }

    public function glueFiles($files, $resultFile)
    {
        $content = '';
        foreach($files as $file) {
            $content .= file_get_contents($file);
        }

        $compressor = new self();

        $h = fopen($resultFile, 'w');
        fwrite($h, $content);
        fclose($h);

        $compressor->addSource($resultFile);
        $compressor->exec();
    }

    /**
     * addSource
     * Adds the filepath to be minified
     *
     * @param $source
     * @throws \Exception
     */
    public function addSource($source) {
        $this->source = $source;
        if(!$this->can_read_source()) {
            throw new \Exception('Source file "'.$source.'" can\'t be loaded. Make sure that the file exists on the given path.');
        }
        if(!$this->is_valid_source()) {
            throw new \Exception('Invalid type. This only works with JS and CSS files.');
        }
        $this->type = preg_replace('/^.*\.(css|js)$/i', '$1', $source);
        $this->data = $this->get_source_data();
    }

    /**
     * setTarget
     * Sets where you want to save the minified file
     *
     * @param	string
     */
    public function setTarget($target='') {
        $this->target = ($target == '' ? $this->get_default_target() : $target);
    }

    /**
     * exec
     * Does the minification process
     */
    public function exec() {
        if(!isset($this->source)) {
            throw new \Exception('There is no source defined. Use the class constructor or ->addSource(\'/source-dir/source.file\') to add a source to be minified.');
        }
        switch($this->type) {
            case 'css':
                $this->minifyCSS();
                break;
            case 'js':
                $this->minifyJS();
                break;
        }
        $this->save_to_file();
    }

    /**
     * get_source_data
     * Grabs the file data using file_get_contents
     *
     * @return string
     * @throws \Exception
     */
    private function get_source_data() {
        $data = @file_get_contents($this->source);
        if($data === false) {
            throw new \Exception('Can\'t read the contents of the source file.');
        } else {
            return $data;
        }
    }

    /**
     * can_read_source
     * Tells if the source file can be readed or not
     *
     * @return: bool
     */
    private function can_read_source() {
        return (@file_exists($this->source) && is_file($this->source) ? true : false);
    }

    /**
     * is_valid_source
     * Tells if the source is valid by its extension
     *
     * @return: bool
     */
    private function is_valid_source() {
        return preg_match('/^.*\.(css|js)$/i', $this->source);
    }

    /**
     * minifyCSS
     * Sets the minified data for CSS
     */
    private function minifyCSS() {
        //return $this->data;
        $this->set_minified_data($this->get_minified_data());
    }

    /**
     * minifyJS
     * Sets the minified data for JavaScript
     */
    private function minifyJS() {
        $jsComressor = new JsCompressor();
        $this->set_minified_data($jsComressor->compress($this->data));
    }

    /**
     * get_default_target
     * Set the default file.min.ext from $this->source
     *
     * @return: string
     */
    private function get_default_target() {
        return preg_replace('/(.*)\.([a-z]{2,3})$/i', '$1.$2', $this->source);
    }

    /**
     * get_minified_data
     * Returns the minified $this->data
     *
     * @return: string
     */
    private function get_minified_data() {
        return $this->strip_whitespaces($this->strip_linebreaks($this->strip_comments($this->data)));
    }

    /**
     * set_minified_data
     * Sets $this->minified_data and unset the $this->data
     */
    private function set_minified_data($string) {
        $this->minified_data = $string;
        unset($this->data);
    }

    /**
     * save_to_file
     * Saves the minified data to the target file
     */
    private function save_to_file()
    {
        $this->target = (!isset($this->target) ? $this->get_default_target() : $this->target);
        if(!isset($this->minified_data)) {
            throw new \Exception('There is no data to write to "'.$this->target.'"');
        }
        if(($handler = @fopen($this->target, 'w')) === false) {
            throw new \Exception('Can\'t open "' . $this->target . '" for writing.');
        }
        if(@fwrite($handler, $this->minified_data) === false) {
            throw new \Exception('The file "' . $path . '" could not be written to. Check if PHP has enough permissions.');
        }
        @fclose($handler);
    }

    /**
     * strip_whitespaces
     * Removes any whitespace inside/betwen ;:{}[] chars. It also safely removes the extra space inside () parentheses
     * @param $string
     * @return mixed
     */
    private function strip_whitespaces($string) {
        switch($this->type) {
            case 'css':
                $pattern = ';|:|,|\{|\}';
                break;
            case 'js':
                $pattern = ';|:|,|\{|\}|\[|\]';
                break;
        }
        return preg_replace('/\s*(' . $pattern .')\s*/', '$1', preg_replace('/\(\s*(.*)\s*\)/', '($1)', $string));
    }

    /**
     * strip_linebreaks
     * Removes any line break in the form of newline, carriage return, tab and extra spaces
     *
     * @param $string
     * @return mixed
     */
    private function strip_linebreaks($string) {
        return preg_replace('/(\\\?[\n\r\t]+|\s{2,})/', '', $string);
    }

    /**
     * strip_comments
     * Removes all the known comment types from the source
     *
     * @param $string
     * @return mixed
     */
    private function strip_comments($string) {
        // Don't touch anything inside a quote or regex
        $protected = '(?<![\\\/\'":])';
        // Comment types
        $multiline = '\/\*[^*]*\*+([^\/][^\*]*\*+)*\/'; // /* comment */
        $html = '<!--([\w\s]*?)-->'; // <!-- comment -->
        $ctype = '\/\/.*'; // //comment (Yo Dawg)!
        // The pattern
        $pattern = $protected;
        switch($this->type) {
            case 'css':
                $pattern .= $multiline;
                break;
        }
        return preg_replace('#'.$pattern.'#', '', $string);
    }

    /**
     * Attemps to fix the missing syntax for JavaScript
     * Note: It doesn't fix stupidity.
     *
     * @param $string
     * @return mixed
     */
}