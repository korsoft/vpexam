<?php declare(strict_types=1);

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

use Monolog\Logger;

/**
 * Stores to STDIN of any process, specified by a command.
 *
 * Usage example:
 * <pre>
 * $log = new Logger('myLogger');
 * $log->pushHandler(new ProcessHandler('/usr/bin/php /var/www/monolog/someScript.php'));
 * </pre>
 *
 * @author Kolja Zuelsdorf <koljaz@web.de>
 */
class ProcessHandler extends AbstractProcessingHandler {

}