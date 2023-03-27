<?php

namespace Growcode\Omnibus\Observer;

use Growcode\Omnibus\Model\CatalogRuleUpdateConsumer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\CatalogRule\Model\Rule;

class CatalogruleSaveAfter implements ObserverInterface
{
    /**
     * @var PublisherInterface
     */
    private PublisherInterface $publisher;

    /**
     * @param PublisherInterface $publisher
     */
    public function __construct(PublisherInterface $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var Rule $rule */
        $rule = $observer->getRule();
        $this->publisher->publish(CatalogRuleUpdateConsumer::QUEUE_NAME, intval($rule->getId()));
    }
}
