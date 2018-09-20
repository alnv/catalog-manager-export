<?php

namespace CatalogManager\ExportBundle\Library;

use CatalogManager\CatalogFieldBuilder as CatalogFieldBuilder;
use CatalogManager\SQLQueryBuilder as SQLQueryBuilder;
use CatalogManager\Toolkit as Toolkit;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class Export {


    protected $strName;
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

        $this->strName = $arrSettings['name'];
        $this->strTable = $arrSettings['table'];
        $this->numLimit = $arrSettings['limit'];
        $this->numOffset = $arrSettings['offset'];
        $this->strType = $arrSettings['type'] ?: 'xlsx';
        $this->blnParser = $arrSettings['parser'] ? true : false;
        $this->blnIncludeHeader = $arrSettings['includeHeader'] ? true : false;

        $this->getEntities();
    }


    public function initialize() {

        $numRows = 1;
        $objUser = \BackendUser::getInstance();
        $objSpreadsheet = new Spreadsheet();
        $strFilename =  \StringUtil::generateAlias( $this->strName ) . '.' . $this->strType;

        $objSpreadsheet->getProperties()
            ->setTitle( $this->strName )
            ->setCreator( $objUser->username )
            ->setLastModifiedBy( $objUser->username );

        $objSheet = $objSpreadsheet->getActiveSheet();

        if ( $this->blnIncludeHeader ) {

            $numIndex = 1;

            foreach ( $this->arrHeader as $strTitel ) {

                $objSheet->setCellValueByColumnAndRow( $numIndex, $numRows, $strTitel );
                $numIndex++;
            }

            $numRows++;
        }

        foreach ( $this->arrEntities as $arrEntity ) {

            $numIndex = 1;

            foreach ( $this->arrHeader as $strFieldname => $strTitel ) {

                $strValue = $arrEntity[ $strFieldname ];

                if ( $strValue == null ) $strValue = '';

                $objSheet->setCellValueByColumnAndRow( $numIndex, $numRows, $strValue );

                $numIndex++;
            }

            $numRows++;
        }

        switch ( $this->strType ) {

            case 'xls':

                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $strFilename . '"');
                header('Cache-Control: max-age=0');

                $objXls = new Xls( $objSpreadsheet );
                $objXls->save( 'php://output' );

                exit;

            case 'xlsx':

                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $strFilename . '"');
                header('Cache-Control: max-age=0');

                $objXls = new Xlsx( $objSpreadsheet );
                $objXls->save( 'php://output' );

                exit;

            case 'csv':

                header('Content-Type: text/csv');
                header('Content-Disposition: attachment;filename="' . $strFilename . '"');
                header('Cache-Control: max-age=0');

                $objXls = new Csv( $objSpreadsheet );
                $objXls->save( 'php://output' );

                exit;

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

        foreach ( $arrFields as $strFieldname => $arrField ) {

            $this->arrHeader[ $strFieldname ] = $arrField['_dcFormat']['label'][0] ?: $strFieldname;
        }
    }
}