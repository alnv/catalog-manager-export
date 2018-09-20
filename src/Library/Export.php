<?php

namespace CatalogManager\ExportBundle\Library;

use CatalogManager\CatalogFieldBuilder as CatalogFieldBuilder;
use CatalogManager\SQLQueryBuilder as SQLQueryBuilder;
use CatalogManager\Toolkit as Toolkit;

class Export {

    protected $strType;
    protected $strTable;
    protected $numLimit = 0;
    protected $numOffset = 0;
    protected $arrQuery = [];
    protected $arrOrder = [];
    protected $arrHeader = [];
    protected $arrEntities = [];
    protected $blnParser = false;
    protected $blnIncludeHeader = false;


    public function __construct( $arrSettings ) {

        $arrMatch = \StringUtil::deserialize( $arrSettings['match'], true );
        $arrOrders = \StringUtil::deserialize( $arrSettings['order'], true );

        if ( isset( $arrMatch['query'] ) ) {

            $this->arrQuery = $arrMatch['query'];
        }

        if ( is_array($arrOrders ) && !empty( $arrOrders ) ) {

            foreach ( $arrOrders as $arrOrder ) {

                $this->arrOrder[] = [

                    'field' => $arrOrder['key'],
                    'order' => $arrOrder['value']
                ];
            }
        }

        $this->strTable = $arrSettings['table'];
        $this->numLimit = $arrSettings['limit'];
        $this->numOffset = $arrSettings['offset'];
        $this->strType = $arrSettings['type'] ?: 'xlsx';
        $this->blnParser = $arrSettings['parser'] ? true : false;
        $this->blnIncludeHeader = $arrSettings['includeHeader'] ? true : false;
        $this->getEntities();
    }


    public function initialize() {

        switch ( $this->strType ) {

            case 'xls':

                //

                break;

            case 'xlsx':

                //

                break;

            default:

                \System::log( 'Not supported "%s" type!', __METHOD__, TL_ERROR );

                break;
        }
    }


    protected function getEntities() {

        $arrQuery = [];
        $objSQLBuilder = new SQLQueryBuilder();
        $objCatalogFieldBuilder = new CatalogFieldBuilder();
        $objCatalogFieldBuilder->initialize( $this->strTable );

        $arrCatalog = $objCatalogFieldBuilder->getCatalog();
        $arrFields = $objCatalogFieldBuilder->getCatalogFields( true, null );

        $arrQuery['pagination'] = [];
        $arrQuery['where'] = $this->arrQuery;
        $arrQuery['table'] = $this->strTable;
        $arrQuery['orderBy'] = $this->arrOrder;

        if ( in_array( 'invisible', $arrCatalog['operations'] ) ) {

            $dteTime = \Date::floorToMinute();

            $arrQuery['where'][] = [

                'field' => 'tstamp',
                'operator' => 'gt',
                'value' => 0
            ];

            $arrQuery['where'][] = [

                [
                    'value' => '',
                    'field' => 'start',
                    'operator' => 'equal'
                ],

                [
                    'field' => 'start',
                    'operator' => 'lte',
                    'value' => $dteTime
                ]
            ];

            $arrQuery['where'][] = [

                [
                    'value' => '',
                    'field' => 'stop',
                    'operator' => 'equal'
                ],

                [
                    'field' => 'stop',
                    'operator' => 'gt',
                    'value' => $dteTime
                ]
            ];

            $arrQuery['where'][] = [

                'field' => 'invisible',
                'operator' => 'not',
                'value' => '1'
            ];
        }

        if ( $this->numLimit ) {

            $arrQuery['pagination']['limit'] = $this->numLimit;
        }

        if ( $this->numOffset ) {

            $arrQuery['pagination']['offset'] = $this->numOffset;
        }

        $objEntities = $objSQLBuilder->execute( $arrQuery );

        if ( !$objEntities->numRows ) {

            return null;
        }

        while ( $objEntities->next() ) {

            $arrEntity = $objEntities->row();

            if ( $this->blnParser ) {

                $arrEntity = Toolkit::parseCatalogValues( $arrEntity, $arrFields, true );
            }

            $this->arrEntities[] = $arrEntity;
        }

        if ( $this->blnIncludeHeader ) {

            foreach ( $arrFields as $strFieldname => $arrField ) {

                $this->arrHeader[] = $arrField['_dcFormat']['label'][0] ?: $strFieldname;
            }
        }
    }
}