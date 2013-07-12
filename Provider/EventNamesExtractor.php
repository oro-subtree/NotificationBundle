<?php

namespace Oro\Bundle\NotificationBundle\Provider;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Bundle\FrameworkBundle\Translation\PhpExtractor;

/**
 * Extracts event names from a php files.
 *
 */
class EventNamesExtractor extends PhpExtractor
{
    const MESSAGE_TOKEN = 300;
    const IGNORE_TOKEN = 400;

    /**
     * Prefix for new found message.
     *
     * @var string
     */
    private $prefix = '';

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
            'oro.event.',
            self::MESSAGE_TOKEN,
            ',',
        ),
    );

    public function __construct(KernelInterface $kernel)
    {
        $directories = false;
        foreach ($kernel->getBundles() as $bundle) {
            /** @var $bundle \Symfony\Component\HttpKernel\Bundle\BundleInterface  */
            $directories[] = $bundle->getPath();
        }

        $this->directories = $directories;
    }

    /**
     * {@inheritDoc}
     */
    public function extract($directory, MessageCatalogue $catalog)
    {
        // load any existing translation files
        $finder = new Finder();

        foreach ($this->directories as $directory) {
            $files = $finder->files()->name('*.php')->in($directory);
            foreach ($files as $file) {
                $this->parseTokens(token_get_all(file_get_contents($file)), $catalog);
                die('sdf');
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
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
     * @param array            $tokens
     * @param MessageCatalogue $catalog
     */
    protected function parseTokens($tokens, MessageCatalogue $catalog)
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

                if ($message) {
                    $catalog->set($message, $this->prefix.$message);
                    break;
                }
            }
        }
    }
}
