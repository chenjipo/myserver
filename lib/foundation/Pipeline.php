<?php
/**
 * 中间件调度
 */
namespace YXLib\foundation;

class Pipeline
{

    /**
     * The method to call on each pipe
     * @var string
     */
    protected $method = 'handle';

    /**
     * The object being passed throw the pipeline
     * @var mixed
     */
    protected $passable;

    /**
     * The array of class pipes
     * @var array
     */
    protected $pipes = [];

    /**
     * Set the object being sent through the pipeline
     *
     * @param $passable
     * @return $this
     */
    public function send($passable)
    {
        $this->passable = $passable;
        return $this;
    }

    /**
     * Set the method to call on the pipes
     * @param array $pipes
     * @return $this
     */
    public function through($pipes)
    {
        $this->pipes = $pipes;
        return $this;
    }

    /**
     * @param \Closure $destination
     * @return mixed
     */
    public function then(\Closure $destination)
    {
        $pipeline = array_reduce(array_reverse($this->pipes), $this->getSlice(), $destination);
        return $pipeline($this->passable);
    }


    /**
     * Get a Closure that represents a slice of the application onion
     * @return \Closure
     */
    protected function getSlice()
    {
        return function(\Closure $stack, $pipe){
            return function () use ($stack, $pipe) {
                return (new $pipe)->{$this->method}( $stack);
            };
        };
    }
}
