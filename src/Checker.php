<?php

namespace JNSC;

/**
 * Checker
 *
 * @since 1.0.0
 */
class Checker
{
    /**
     * @var   array<string>
     */
    public $excludedPaths = [];

    /**
     * @var   array<string>
     */
    public $classmap;

    /**
     * @var   array<string>
     */
    public $update;

    /**
     * @var   array<string>
     */
    private $indicators = ['progress' => '.', 'error' => 'E'];

    /**
     * @var   array
     */
    private $messages = [];

    /**
     * Prepares the internal properties
     *
     * @param   array   $excludePaths   List of paths to exclude
     * @param   array   $classmap       A map of classes to be replaced and the replacements
     */
    public function __construct(array $excludePaths, array $classmap, bool $update)
    {
        $this->excludedPaths = $excludePaths;
        $this->classmap      = $classmap;
        $this->update        = $update;
    }

    /**
     * Filters the provided folder and then scans all the files found for errors
     *
     * @param   string   $folder   The folder to scan
     *
     * @throws   \Error  If any errors have been found during the scan
     *                   we throw the complete message stack back to the main script
     * @return   void
     */
    public function scan(string $folder)
    {
        $files = $this->filterPaths(
            $this->getFiles($folder),
            $this->excludedPaths
        );

        $this->scanForErrors($files);

        if (!empty($this->messages)) {
            throw new \Error(
                implode(PHP_EOL, $this->messages)
            );
        }
    }

    /**
     * Gets all PHP files in a folder (incl. subfolders)
     *
     * @param   string   $path      Parent folder
     *
     * @return   \RegexIterator
     */
    private function getFiles(string $path) : \RegexIterator
    {
        $directory = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
        $iterator  = new \RecursiveIteratorIterator($directory);

        return new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
    }

    /**
     * Filters paths
     *
     * @param   \Iterator|array   $files      List of files to filter
     * @param   array             $excluded   Paths to exclude
     *
     * @return   array
     */
    private function filterPaths($files, array $excluded = []) : array
    {
        $filtered = [];

        $default = [ '/vendor', '/node_modules' ];

        $exclude = array_merge($default, $excluded);

        foreach ($files as $file) {
            foreach ($exclude as $exc) {
                if (empty($exc) || false !== strpos($file[0], $exc)) {
                    continue 2;
                }
            }

            $filtered[] = $file[0];
        }

        return $filtered;
    }

    /**
     * Scan files for errors
     *
     * @param   array   $files   List of files to scan for errors
     *
     * @return   void
     */
    private function scanForErrors(array $files)
    {
        $i = 1;

        foreach ($files as $file) {
            if ($i % 65 == 0) {
                echo PHP_EOL;
            }

            $i++;

            $handler = file_get_contents($file);

            $errors = $this->scanFile($handler);

            if (empty($errors)) {
                echo $this->indicators['progress'];
                continue;
            }

            echo $this->indicators['error'];

            if ($this->update === true) {
                $this->updateFile($file, $errors, $handler);
            }

            $this->buildMessages($file, $errors);
        }
    }

    /**
     * Scans a single file for errors
     *
     * @param   string   $file   The file in string format
     *
     * @return   array
     */
    private function scanFile(string $file) : array
    {
        $classesFound = [];

        foreach ($this->classmap as $class => $ns) {
            $regex = '#\b[^\$\\\(\W]?' . $class . '\b#';

            preg_match_all($regex, $file, $matches, PREG_OFFSET_CAPTURE);

            $matches = array_filter($matches);

            if (!empty($matches)) {
                foreach ($matches[0] as $match) {
                    list($before) = str_split($file, $match[1]);

                    $ln = strlen($before) - strlen(str_replace("\n", "", $before)) + 1;

                    $classesFound[] = "{$class}#{$ln}";
                }
            }
        }

        return $classesFound;
    }

    /**
     * Update the file and fix the errors found, insert the use statements
     *
     * @param   string  $file     The file that the errors were found in
     * @param   array   $errors   List of errors
     *
     * @return   void
     */
    private function updateFile(string $file, array $errors, string $handler)
    {
        $excludes = ['JEventDispatcher', 'JDispatcher', 'JRequest'];
        /* Remove the line numbers */
        foreach ($errors as $idx => $error) {
            $errors[$idx] = substr($error, 0, strpos($error, "#"));
        }
        /* get unique values */
        $errors = array_unique($errors);
        $searches = $replaces = $uses = [];
        
        foreach ($errors as $class) {
            if (in_array($class, $excludes)) {
                continue;
            }
            $searches[] = $class;
            $replaces[] = substr($this->classmap[$class], strrpos($this->classmap[$class],'\\')+1);
            $uses[] = "use ".$this->classmap[$class].";";
        }
        $handler = str_replace($searches, $replaces, $handler);
        
        /* Insert a newline */
        $handler = substr_replace($handler, "\n\n", strpos($handler, "<?php")+5, 0);
        /* Now insert the uses */
        $handler = substr_replace($handler, implode("\n", $uses)."\n", strpos($handler, "\n")+2, 0);
        
        /* Write back the file */
        file_put_contents($file, $handler);
    }

    /**
     * Builds a visual presentation of the errors found
     *
     * @param   string  $file     The file that the errors were found in
     * @param   array   $errors   List of errors
     *
     * @return   void
     */
    private function buildMessages(string $file, array $errors)
    { 
        usort($errors, [$this, 'sortErrorsByLine']);

        $separator = str_repeat('-', 80);

        $msg  = "FILE: \033[96m{$file}\033[0m" . PHP_EOL;
        $msg .= $separator . PHP_EOL;

        list($classLength, $lineLength) = $this->calcLenght($errors);

        foreach ($errors as $found) {
            list($class, $line) = explode('#', $found);

            $replace = $this->classmap[$class];

            $classPadding = str_repeat(' ', $classLength - strlen($class));
            $linePadding  = str_repeat(' ', $lineLength - strlen($line));

            $msg .= "Line: {$line} {$linePadding}| ";
            $msg .= "Class found: \033[96m{$class}\033[0m {$classPadding}| ";
            $msg .= "Replace with: \033[92m{$replace}\033[0m" . PHP_EOL;
        }

        $msg .= $separator;

        $this->messages[] = $msg . PHP_EOL;
    }

    /**
     * Sort errors by line number
     *
     * @see buildMessages()
     *
     * @param   string   $a   Input a
     * @param   string   $b   Input b
     *
     * @return   integer
     */
    private function sortErrorsByLine(string $a, string $b) : int
    {
        $a = explode('#', $a);
        $b = explode('#', $b);

        return end($a) <=> end($b);
    }

    /**
     * Calculates the max string lenght of the class name and line number
     *
     * @see buildMessages()
     *
     * @param   array   $input   List of strings
     *
     * @return   array
     */
    private function calcLenght(array $input) : array
    {
        $cls = [];
        $ln  = [];

        foreach ($input as $value) {
            $arr   = explode('#', $value);
            $cls[] = strlen($arr[0]);
            $ln[]  = strlen($arr[1]);
        }

        return [
            max($cls),
            max($ln)
        ];
    }
}
