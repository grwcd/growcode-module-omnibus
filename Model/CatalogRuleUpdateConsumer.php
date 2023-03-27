<?php

namespace Growcode\Omnibus\Model;

use DateTime;
use DateTimeZone;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\Rule as CatalogRule;
use Magento\Framework\Exception\NoSuchEntityException;
use Growcode\Omnibus\Service\HistoricalPrice;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

class CatalogRuleUpdateConsumer
{
    const QUEUE_NAME = 'growcode.omnibus.catalog_rule.update';

    /**
     * @var HistoricalPrice
     */
    private HistoricalPrice $historicalPrice;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var CatalogRuleRepositoryInterface
     */
    private CatalogRuleRepositoryInterface $catalogRuleRepository;

    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $localeDate;

    /**
     * @param HistoricalPrice $historicalPrice
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     * @param CatalogRuleRepositoryInterface $catalogRuleRepository
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        HistoricalPrice                $historicalPrice,
        ProductRepositoryInterface     $productRepository,
        LoggerInterface                $logger,
        CatalogRuleRepositoryInterface $catalogRuleRepository,
        TimezoneInterface              $localeDate
    )
    {
        $this->historicalPrice = $historicalPrice;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->catalogRuleRepository = $catalogRuleRepository;
        $this->localeDate = $localeDate;
    }

    /**
     * @param int $ruleId
     * @throws NoSuchEntityException
     */
    public function process(int $ruleId)
    {
        /** @var Rule $catalogRule */
        $catalogRule = $this->catalogRuleRepository->get($ruleId);

        if ($catalogRule->getIsActive()
            && $this->checkRuleTime($catalogRule)
            && in_array(0, $catalogRule->getCustomerGroupIds())) {
            $this->logger->info("[Growcode_Omnibus] Processing catalog rule (ID $ruleId) update");
            $productIds = $catalogRule->getMatchingProductIds();

            foreach ($productIds as $productId => $websiteIds) {
                try {
                    $product = $this->productRepository->getById($productId);
                    $this->historicalPrice->savePriceChange($product, $product->getPrice());
                } catch (Exception $e) {
                    continue;
                }
            }

            $this->logger->info("[Growcode_Omnibus] Finished processing catalog rule (ID $ruleId) update");
        }
    }

    /**
     * @param CatalogRule $rule
     * @return bool
     */
    protected function checkRuleTime(Rule $rule): bool
    {
        $now = $this->localeDate->date();

        try {
            $from = $this->getRuleStartTime($rule);
            $to = $this->getRuleEndTime($rule);

            return ($now >= $from && $now < $to);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Return end of date when rule stops (23:59:59)
     * @param Rule $rule
     * @return DateTime
     * @throws Exception
     */
    protected function getRuleEndTime(Rule $rule): DateTime
    {
        return $this->localeDate->date(
            (new DateTime($rule->getToDate(), new DateTimeZone($this->localeDate->getConfigTimezone())))
                ->modify('+ 1 day')->modify('- 1 sec'),
        );
    }

    /**
     * @param Rule $rule
     * @return DateTime
     * @throws Exception
     */
    protected function getRuleStartTime(Rule $rule): DateTime
    {
        return $this->localeDate->date(
            (new DateTime($rule->getFromDate(), new DateTimeZone($this->localeDate->getConfigTimezone())))
        );
    }
}
