<?php

/**
 * Service container
 *
 * @package    fleks-util
 * @author     Wubbo Bos <wubbo@wubbobos.nl>
 * @copyright  Copyright (c) Wubbo Bos
 * @license    GPL
 * @link       https://github.com/wubmeister/fleks-util
 */

namespace Fleks\Util;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * The configuration
     *
     * @var Fleks\Util\Config
     */
    protected $config;

    /**
     * The created service instances
     *
     * @var array
     */
    protected $services = [];

    /**
     * Initializes a container with the specified configuration
     *
     * @param Fleks\Util\Config $config
     *   The configuration
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Checks whether a service is registered
     *
     * @param string $id The name of the service
     * @return bool true if the service is registered, false if not
     */
    public function has($id)
    {
        return ($this->config->has('factories') && $this->config->factories->has($id)) ||
            ($this->config->has('invokables') && $this->config->invokables->has($id));
    }

    /**
     * Returns an instance of a service. If the instance doesn't exist yet, it
     * will be created on the fly.
     *
     * @param string $id The name of the service
     * @return bool true if the service is registered, false if not
     */
    public function get($id)
    {
        if (!isset($this->services[$id])) {
            if ($this->config->has('factories') && $this->config->factories->has($id)) {
                $func = $this->config->factories->{$id};
                if (is_string($func)) {
                    $func = new $func();
                }
                $this->services[$id] = $func($this, $id);

            } else if ($this->config->has('invokables') && $this->config->invokables->has($id)) {
                $className = $this->config->invokables->{$id};
                $this->services[$id] = new $className();

            } else {
                throw new \Exception("Service '{$id}' could not be created");
            }
        }

        return $this->services[$id];
    }

    /**
     * Registers a service instance under a given name. Any existing service
     * with the same name will be overwritten.
     *
     * @param string $id The name of the service
     * @param mixed $service The service
     */
    public function set($id, $service)
    {
        $this->services[$id] = $service;
    }
}
