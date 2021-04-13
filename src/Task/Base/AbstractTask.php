<?php

namespace Pars\Core\Task\Base;

use Pars\Core\Config\ParsConfig;
use Pars\Core\Database\ParsDatabaseAdapter;
use Pars\Core\Database\ParsDatabaseAdapterAwareInterface;
use Pars\Core\Database\ParsDatabaseAdapterAwareTrait;
use Pars\Core\Translation\ParsTranslator;
use Pars\Core\Translation\ParsTranslatorAwareInterface;
use Pars\Core\Translation\ParsTranslatorAwareTrait;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractTask
 * @package Pars\Core\Task\Base
 */
abstract class AbstractTask implements ParsDatabaseAdapterAwareInterface, ParsTranslatorAwareInterface
{
    use ParsDatabaseAdapterAwareTrait;
    use ParsTranslatorAwareTrait;
    protected ContainerInterface $container;

    protected ParsConfig $config;

    /**
     * @var int
     */
    private ?int $minute = null;

    /**
     * @var int
     */
    private ?int $hour = null;

    /**
     * @var int
     */
    private ?int $day = null;

    /**
     * @var bool
     */
    private bool $active = false;

    /**
     * @var array
     */
    protected array $taskConfig;

    /**
     * @var \DateTime
     */
    protected \DateTime $now;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * AbstractTask constructor.
     * @param array $config
     * @param \DateTime $now
     * @param ContainerInterface $container
     */
    public function __construct(array $config, \DateTime $now, ContainerInterface $container)
    {
        $this->container = $container;
        $this->setTranslator($container->get(ParsTranslator::class));
        $this->setDatabaseAdapter($container->get(ParsDatabaseAdapter::class));
        $this->logger = $container->get(\Psr\Log\LoggerInterface::class);
        $this->config = $container->get(ParsConfig::class);
        $this->taskConfig = $config;
        $this->now = $now;
        if (isset($config['active'])) {
            $this->setActive($config['active']);
        }
        if (isset($config['day'])) {
            $this->setDay($config['day']);
        }
        if (isset($config['hour'])) {
            $this->setHour($config['hour']);
        }
        if (isset($config['minute'])) {
            $this->setMinute($config['minute']);
        }
    }

    /**
     * @return mixed|ParsConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }




    /**
     * @return int
     */
    public function getMinute(): ?int
    {
        return $this->minute;
    }

    /**
     * @param int $minute
     *
     * @return $this
     */
    public function setMinute(?int $minute): self
    {
        $this->minute = $minute;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasMinute(): bool
    {
        return isset($this->minute);
    }

    /**
     * @return int
     */
    public function getHour(): ?int
    {
        return $this->hour;
    }

    /**
     * @param int $hour
     *
     * @return $this
     */
    public function setHour(?int $hour): self
    {
        $this->hour = $hour;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasHour(): bool
    {
        return isset($this->hour);
    }

    /**
     * @return int
     */
    public function getDay(): ?int
    {
        return $this->day;
    }

    /**
     * @param int $day
     *
     * @return $this
     */
    public function setDay(?int $day): self
    {
        $this->day = $day;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasDay(): bool
    {
        return isset($this->day);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return AbstractTask
     */
    public function setActive(bool $active): AbstractTask
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return array
     */
    public function getTaskConfig(): array
    {
        return $this->taskConfig;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @return \DateTime
     */
    public function getNow(): \DateTime
    {
        return $this->now;
    }

    /**
     * @return bool
     */
    public function isAllowed(): bool
    {
        $day = intval($this->now->format('w')) + 1;
        $hour = intval($this->now->format('H'));
        $minute = intval($this->now->format('i'));
        return (!isset($this->day) || $this->getDay() == $day)
            && (!isset($this->hour) || $this->getHour() == $hour)
            && (!isset($this->minute) || $this->getMinute() == $minute)
            && $this->isActive();
    }

    abstract public function execute(): void;
}
