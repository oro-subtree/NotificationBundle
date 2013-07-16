<?php

namespace Oro\Bundle\NotificationBundle\Provider;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Finder\Finder;

/**
 * Extracts event names from a php files.
 *
 */
class EventNamesExtractor
{
    const MESSAGE_TOKEN = 300;
    const IGNORE_TOKEN = 400;
    const EVENTS_PREFIX = 'oro.event.';

    /**
     * Found/extracted event names
     *
     * @var array
     */
    protected $eventNames = array();

    /**
     * The sequence that captures translation messages.
     *
     * @var array
     */
    protected $sequences = array(
        array(
            '->',
            'dispatch',
            '(',
            self::MESSAGE_TOKEN,
            ',',
        ),
    );

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $entityClass;

    public function __construct(ObjectManager $em, $entityClass)
    {
        $this->em = $em;
        $this->entityClass = $entityClass;
    }

    /**
     * Extract event names and return them in array
     *
     * @param string $directory
     * @return array
     */
    public function extract($directory)
    {
        $finder = new Finder();
        $files = $finder->files()->name('*.php')->exclude('Tests')->in($directory);
        foreach ($files as $file) {
            $this->parseTokens(token_get_all(file_get_contents($file)));
        }

        return $this->eventNames;
    }

    /**
     * Save extracted messages to db
     */
    public function dumpToDb()
    {
        $existingNames = $this->em->getRepository($this->entityClass)->findAll();
        if (!empty($existingNames)) {
            if ($existingNames instanceof $this->entityClass) {
                $existingNames = array($existingNames->getName());
            } else {
                $existingNames = array_map(
                    function ($item) {
                        return $item->getName();
                    },
                    $existingNames
                );
            }

            $existingNames = array_flip($existingNames);
        }

        foreach ($this->eventNames as $eventName) {
            if (isset($existingNames[$eventName])) {
                continue;
            }

            $event = new $this->entityClass($eventName);
            $this->em->persist($event);
        }

        $this->em->flush();
    }

    /**
     * Normalizes a token.
     *
     * @param mixed $token
     * @return string
     */
    protected function normalizeToken($token)
    {
        if (is_array($token)) {
            return $token[1];
        }

        return $token;
    }

    /**
     * Extracts trans message from php tokens.
     *
     * @param array $tokens
     */
    protected function parseTokens($tokens)
    {
        foreach ($tokens as $key => $token) {
            foreach ($this->sequences as $sequence) {
                $message = '';

                foreach ($sequence as $id => $item) {
                    if ($this->normalizeToken($tokens[$key + $id]) == $item) {
                        continue;
                    } elseif (self::MESSAGE_TOKEN == $item) {
                        $message = $this->normalizeToken($tokens[$key + $id]);
                    } elseif (self::IGNORE_TOKEN == $item) {
                        continue;
                    } else {
                        break;
                    }
                }

                $message = trim($message, '\'');
                if ($message && substr($message, 0, strlen(self::EVENTS_PREFIX)) == self::EVENTS_PREFIX) {
                    $this->eventNames[$message] = $message;
                    break;
                }
            }
        }
    }
}
