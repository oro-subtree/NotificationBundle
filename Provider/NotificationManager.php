<?php

namespace Oro\Bundle\NotificationBundle\Provider;

use JMS\Serializer\EventDispatcher\EventDispatcherInterface;
use Oro\Bundle\NotificationBundle\Event\Handler\EventHandlerInterface;
use Symfony\Component\EventDispatcher\Event;

class NotificationManager
{
    /**
     * @var EventHandlerInterface[] handlers
     */
    protected $handlers;

    public function __construct()
    {
        $this->handlers = array();
    }

    /**
     * Add handler to list
     *
     * @param EventHandlerInterface $handler
     */
    public function addHandler(EventHandlerInterface $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * Process events with handlers
     */
    public function process(Event $event)
    {
        /** @var EventHandlerInterface $handler */
        foreach ($this->handlers as $handler) {
            $handler->handle($event);
            if ($event->isPropagationStopped()) {
                break;
            }
        }
    }

    /**
     * Return list of handlers
     *
     * @return EventHandlerInterface[]
     */
    public function getHandlers()
    {
        return $this->handlers;
    }
}
