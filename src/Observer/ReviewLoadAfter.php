<?php
/**
 * Copyright © 2016 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

namespace Ho\Review\Observer;

use Ho\Review\Api\Data\ConsiderationInterface;
use Ho\Review\Api\RatingConsiderationRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ReviewLoadAfter implements ObserverInterface
{
    /**
     * @var RatingConsiderationRepositoryInterface
     */
    private $considerationRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * ReviewLoadAfter constructor.
     *
     * @param RatingConsiderationRepositoryInterface $considerationRepository
     * @param SearchCriteriaBuilder                  $searchCriteriaBuilder
     * @param FilterBuilder                          $filterBuilder
     */
    public function __construct(
        RatingConsiderationRepositoryInterface $considerationRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->considerationRepository  = $considerationRepository;
        $this->searchCriteriaBuilder    = $searchCriteriaBuilder;
        $this->filterBuilder            = $filterBuilder;
    }

    /**
     * @param Observer $observer
     *
     * @return ReviewLoadAfter
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Review\Model\Review $review */
        $review = $observer->getObject();

        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            ConsiderationInterface::TYPE,
            ConsiderationInterface::CONSIDERATION_PROS
        )->create();

        $considerationProsCollection = $this->considerationRepository->getList($searchCriteria);
        $review->setData('consideration_pros', $this->getColumnValues($considerationProsCollection, 'value'));

        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            ConsiderationInterface::TYPE,
            ConsiderationInterface::CONSIDERATION_CONS
        )->create();

        $considerationConsCollection = $this->considerationRepository->getList($searchCriteria);
        $review->setData('consideration_cons', $this->getColumnValues($considerationConsCollection, 'value'));

        return $this;
    }

    /**
     * Retrieve field values from all items
     *
     * @param   string $colName
     * @return  array
     */
    private function getColumnValues($collection, string $colName): array
    {
        $col = [];
        foreach ($collection->getItems() as $item) {
            $col[] = $item->getData($colName);
        }

        return $col;
    }
}