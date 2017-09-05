<?php
/**
 * Whoops - php errors for cool kids
 * @author Filipe Dobreira <http://github.com/filp>
 */

namespace Whoops\Handler;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\VarDumper\Cloner\AbstractCloner;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use UnexpectedValueException;
use Whoops\Exception\Formatter;
use Whoops\Util\Misc;
use Whoops\Util\TemplateHelper;

class PrettyPageHandler extends Handler {
    /**
     * Search paths to be scanned for resources, in the reverse
     * order they're declared.
     *
     * @var array
     */
    private $searchPaths = [];

    /**
     * Fast lookup cache for known resource locations.
     *
     * @var array
     */
    private $resourceCache = [];

    /**
     * The name of the custom css file.
     *
     * @var string
     */
    private $customCss = null;

    /**
     * @var array[]
     */
    private $extraTables = [];

    /**
     * @var bool
     */
    private $handleUnconditionally = false;

    /**
     * @var string
     */
    private $pageTitle = "Whoops! There was an error.";

    /**
     * A string identifier for a known IDE/text editor, or a closure
     * that resolves a string that can be used to open a given file
     * in an editor. If the string contains the special substrings
     * %file or %line, they will be replaced with the correct data.
     *
     * @example
     *  "txmt://open?url=%file&line=%line"
     * @var mixed $editor
     */
    protected $editor;

    /**
     * A list of known editor strings
     * @var array
     */
    protected $editors = [
        "sublime"  => "subl://open?url=file://%file&line=%line",
        "textmate" => "txmt://open?url=file://%file&line=%line",
        "emacs"    => "emacs://open?url=file://%file&line=%line",
        "macvim"   => "mvim://open/?url=file://%file&line=%line",
        "phpstorm" => "phpstorm://open?file=%file&line=%line",
    ];

    /**
     * Constructor.
     */
    public function __construct() {
        if (ini_get('xdebug.file_link_format') || extension_loaded('xdebug')) {
            // Register editor using xdebug's file_link_format option.
            $this->editors['xdebug'] = function($file, $line) {
                return str_replace(['%f', '%l'], [$file, $line], ini_get('xdebug.file_link_format'));
            };
        }

        // Add the default, local resource search path:
        $this->searchPaths[] = __DIR__ . "/../Resources";
    }

    /**
     * @return int|null
     */
    public function handle() {
        if (!$this->handleUnconditionally) {
            // Check conditions for outputting HTML:
            // @todo: Make this more robust
            if (php_sapi_name() === 'cli') {
                // Help users who have been relying on an internal test value
                // fix their code to the proper method
                if (isset($_ENV['whoops-test']))
                    throw new \Exception('Use handleUnconditionally instead of whoops-test' . ' environment variable');

                return Handler::DONE;
            }
        }

        // @todo: Make this more dynamic
        $helper = new TemplateHelper();

        if (class_exists('Symfony\Component\VarDumper\Cloner\VarCloner')) {
            $cloner = new VarCloner();
            // Only dump object internals if a custom caster exists.
            $cloner->addCasters(['*' => function ($obj, $a, $stub, $isNested, $filter = 0) {
                $class = $stub->class;
                $classes = [$class => $class] + class_parents($class) + class_implements($class);

                foreach ($classes as $class) {
                    if (isset(AbstractCloner::$defaultCasters[$class]))
                        return $a;
                }

                // Remove all internals
                return [];
            }]);
            $helper->setCloner($cloner);
        }


    }
}