<?php
use PetrGrishin\Pipe\Pipe;

/**
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

class PipeTest extends PHPUnit_Framework_TestCase {
    public function testPipe() {
        $passable = 'test';
        $i = 0;
        $that = $this;
        PipeTest__Middleware::$run = function($_passable) use ($passable, $that, &$i) {
            $i = $i + 1;
            $that->assertEquals($passable, $_passable);
        };
        $this->assertEquals(0, $i);
        Pipe::create($passable)
            ->through(array('PipeTest__Middleware'))
            ->then(function ($_passable) use ($passable, $that, &$i) {
                $this->assertEquals(1, $i);
                $i = $i + 1;
                $that->assertEquals($passable, $_passable);
            });
        $this->assertEquals(2, $i);
    }

    public function testPipeWithArguments() {
        $passable = 'test';
        $i = 0;
        $argValue = 'argValue';
        $that = $this;
        PipeTest__Middleware::$run = function($_passable, $arg1) use ($passable, $that, $argValue, &$i) {
            $i = $i + 1;
            $that->assertEquals($passable, $_passable);
            $that->assertEquals($argValue, $arg1);
        };
        $this->assertEquals(0, $i);
        Pipe::create($passable)
            ->through(array(array('PipeTest__Middleware', $argValue)))
            ->then(function ($_passable) use ($passable, $that, &$i) {
                $that->assertEquals(1, $i);
                $i = $i + 1;
                $that->assertEquals($passable, $_passable);
            });
        $this->assertEquals(2, $i);
    }
}

class PipeTest__Middleware {
    /** @var Closure */
    public static $run;


    public function __construct($arg1 = null) {
        $this->arg1 = $arg1;
    }

    public function __invoke($passable, Closure $next) {
        $run = self::$run;
        if ($this->arg1) {
            $run($passable, $this->arg1);
        } else {
            $run($passable);
        }
        return $next($passable);
    }
}