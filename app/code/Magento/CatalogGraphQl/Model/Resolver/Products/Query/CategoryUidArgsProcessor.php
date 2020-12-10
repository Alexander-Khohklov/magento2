<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Query;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;

/**
 * Parent category UID processor class for category uid and category id arguments
 */
class ParentCategoryUidArgsProcessor implements ArgumentsProcessorInterface
{
    private const ID = 'parent_id';

    private const UID = 'parent_uid';

    /** @var Uid */
    private $uidEncoder;

    /**
     * @param Uid $uidEncoder
     */
    public function __construct(Uid $uidEncoder)
    {
        $this->uidEncoder = $uidEncoder;
    }

    /**
     * Composite processor that loops through available processors for arguments that come from graphql input
     *
     * @param string $fieldName,
     * @param array $args
     * @return array
     * @throws GraphQlInputException
     */
    public function process(
        string $fieldName,
        array $args
    ): array {
        $parentUidFilter = $args['filter'][self::UID] ?? [];
        $parentIdFilter = $args['filter'][self::ID] ?? [];
        if (!empty($parentIdFilter)
            && !empty($parentUidFilter)
            && $fieldName === 'products') {
            throw new GraphQlInputException(
                __('`%1` and `%2` can\'t be used at the same time.', [self::ID, self::UID])
            );
        } elseif (!empty($parentUidFilter)) {
            if (isset($parentUidFilter['eq'])) {
                $args['filter'][self::ID]['eq'] = $this->uidEncoder->decode((string) $parentUidFilter['eq']);
            } elseif (!empty($parentUidFilter['in'])) {
                foreach ($parentUidFilter['in'] as $uid) {
                    $args['filter'][self::ID]['in'][] = $this->uidEncoder->decode((string) $uid);
                }
            }
            unset($args['filter'][self::UID]);
        }
        return $args;
    }
}
