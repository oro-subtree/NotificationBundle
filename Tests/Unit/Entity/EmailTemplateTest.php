<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Entity;

use Oro\Bundle\NotificationBundle\Entity\EmailTemplate;

class EmailTemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailTemplate
     */
    protected $emailTemplate;

    public function setUp()
    {
        $this->emailTemplate = new EmailTemplate('update_entity.html.twig', 'abc');

        $this->assertEquals('abc', $this->emailTemplate->getContent());
        $this->assertTrue($this->emailTemplate->getIsSystem());
        $this->assertEquals('html', $this->emailTemplate->getType());
    }

    public function tearDown()
    {
        unset($this->emailTemplate);
    }

    /**
     * Test setters, getters
     */
    public function testSettersGetters()
    {
        foreach (array('name', 'id', 'isSystem', 'parent', 'subject', 'content', 'locale') as $field) {
            $this->emailTemplate->{'set'.ucfirst($field)}('abc');
            $this->assertEquals('abc', $this->emailTemplate->{'get'.ucfirst($field)}());
        }

        $this->assertEquals('update_entity', $this->emailTemplate->getName());
    }
}
