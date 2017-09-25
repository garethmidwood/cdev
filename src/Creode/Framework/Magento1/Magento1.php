<?php

namespace Creode\Framework\Magento1;

use Creode\Framework\Framework;

class Magento1 extends Framework
{
    const NAME = 'magento1';
    const LABEL = 'Magento 1';

    const MAGERUN = 'bin/n98-magerun.phar';

    /**
     * Returns commands to clear cache on this framework
     * @return array
     */
    public function clearCache()
    {
        return [
            [self::MAGERUN, 'cache:clean'],
            [self::MAGERUN, 'cache:flush']
        ];
    }

    /**
     * Returns commands to run updates on this framework
     * @return array
     */
    public function update()
    {
        return [
            [self::MAGERUN, 'sys:setup:run']
        ];
    }

    /**
     * Returns an array of tables that can have their data cleansed on dev environments
     * @return array
     */
    public function getDBTableCleanseList()
    {
        return [
            'adminnotification_inbox',
            'aw_core_logger',
            'dataflow_batch_export',
            'dataflow_batch_import',
            'log_customer',
            'log_quote',
            'log_summary',
            'log_summary_type',
            'log_url',
            'log_url_info',
            'log_visitor',
            'log_visitor_info',
            'log_visitor_online',
            'index_event',
            'report_event',
            'report_viewed_product_index',
            'report_compared_product_index',
            'catalog_compare_item',
            'catalogindex_aggregation',
            'catalogindex_aggregation_tag',
            'catalogindex_aggregation_to_tag',
            'core_session',
            'catalogsearch_result'
        ];
    }
}
