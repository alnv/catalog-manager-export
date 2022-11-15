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
    protected $arrColumns = [];
    protected $arrEntities = [];
    protected $blnParser = false;
    protected $blnIncludeHeader = false;


    public function __construct( $arrSettings ) {

        $arrMatch = \StringUtil::deserialize( $arrSettings['match'], true );
        $arrOrders = \StringUtil::deserialize( $arrSettings['order'], true );

        if ( isset( $arrMatch['query'] ) ) {

            $this->arrQuery = Toolkit::parseQueries( $arrMatch['query'] );
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
        $this->numLimit = $arrSettings['limit'];
        $this->numOffset = $arrSettings['offset'];
        $this->strTable = $arrSettings['destination'];
        $this->strType = $arrSettings['type'] ?: 'xlsx';
        $this->blnParser = $arrSettings['parser'] ? true : false;
        $this->blnIncludeHeader = $arrSettings['includeHeader'] ? true : false;
        $this->arrColumns = \StringUtil::deserialize( $arrSettings['columns'], true );

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

            foreach ( $this->arrHeader as $strTitle ) {

                $objSheet->setCellValueByColumnAndRow( $numIndex, $numRows, $strTitle );
                $numIndex++;
            }

            $numRows++;
        }

        foreach ( $this->arrEntities as $arrEntity ) {

            $numIndex = 1;

            foreach ( $this->arrHeader as $strFieldname => $strTitle ) {

                $strValue = $arrEntity[ $strFieldname ];

                if ( $strValue == null ) $strValue = '';

                $objSheet->setCellValueByColumnAndRow( $numIndex, $numRows, $strValue );
                $numIndex++;
            }

            $numRows++;
        }

        header('Content-Disposition: attachment;filename="' . $strFilename . '"');
        header('Cache-Control: max-age=0');

        switch ( $this->strType ) {

            case 'xls':

                header('Content-Type: application/vnd.ms-excel');
                $objXls = new Xls( $objSpreadsheet );
                $objXls->save( 'php://output' );

                exit;

            case 'xlsx':

                header('Content-Type: application/vnd.ms-excel');
                $objXls = new Xlsx( $objSpreadsheet );
                $objXls->save( 'php://output' );

                exit;

            case 'csv':

                header('Content-Type: text/csv');
                $objXls = new Csv( $objSpreadsheet );
                $objXls->save( 'php://output' );

                exit;
        }
    }


    protected function getEntities() {

        $arrQuery = [];
        $objSQLBuilder = new SQLQueryBuilder();
        $objCatalogFieldBuilder = new CatalogFieldBuilder();
        $objCatalogFieldBuilder->initialize( $this->strTable );

        $arrCatalog = $objCatalogFieldBuilder->getCatalog();
        $arrFields = [];

        foreach ($objCatalogFieldBuilder->getCatalogFields(true, null) as $strField => $arrField) {
            if ($arrField['type'] == 'upload') {
                $arrField['type'] = 'text';
                $arrField['_files'] = true;
            }
            $arrFields[$strField] = $arrField;
        }


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

        if (!$objEntities->numRows) {
            return null;
        }
        while ($objEntities->next()) {
            $arrEntity = [];
            foreach ($objEntities->row() as $strField => $strValue) {
                if (!Toolkit::isCoreTable($this->strTable)) {
                    if (isset($arrFields[$strField]['_files']) && $arrFields[$strField]['_files']) {
                        $arrValues = \StringUtil::deserialize($strValue, true);
                        if (is_array($arrValues) && !empty($arrValues)) {
                            $arrFiles = [];
                            foreach ($arrValues as $strUuid) {
                                if ($objFile = \FilesModel::findByUuid($strUuid)) {
                                    $arrFiles[] = $objFile->path;
                                }
                            }
                            $strValue = implode(',', $arrFiles);
                        }
                    }
                }
                $arrEntity[$strField] = $strValue;
            }

            if ($this->blnParser) {
                if (!Toolkit::isCoreTable($this->strTable)) {
                    foreach ($arrFields as $strFieldname => $arrField) {
                        $arrFields[$strFieldname]['dbIgnoreEmptyValues'] = true;
                    }
                    $arrEntity = Toolkit::parseCatalogValues($arrEntity, $arrFields, true);
                }
                else {
                    foreach ($arrEntity as $strFieldname => $varValue) {
                        $arrEntity[$strFieldname] = $this->parseField($varValue, $strFieldname, $arrEntity);
                    }
                }
            }
            $this->arrEntities[] = $arrEntity;
        }

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( !empty( $this->arrColumns ) && !in_array( $strFieldname, $this->arrColumns ) ) {

                continue;
            }

            $this->arrHeader[ $strFieldname ] = $arrField['_dcFormat']['label'][0] ?: $strFieldname;
        }


        // set order
        if ( !empty( $this->arrColumns ) ) {

            $arrOrder = [];

            foreach ( $this->arrColumns as $strFieldname ) {

                $arrOrder[ $strFieldname ] = $this->arrHeader[ $strFieldname ];
            }

            $this->arrHeader = $arrOrder;
        }
    }


    protected function parseField( $varValue, $strField, $arrValues ) {

        if ( $varValue === '' || $varValue === null ) {

            return $varValue;
        }

        $arrField = \Widget::getAttributesFromDca( $GLOBALS['TL_DCA'][ $this->strTable ]['fields'][ $strField ], $strField, $varValue, $strField, $this->strTable );

        if ( !isset( $arrField['type'] ) ) {

            return $varValue;
        }

        switch ( $arrField['type'] ) {

            case 'text':

                return $arrField['value'];

                break;

            case 'checkbox':
            case 'select':
            case 'radio':

                $varValue = !is_array( $arrField['value'] ) ? [ $arrField['value'] ] : $arrField['value'];
                $varValue = $this->getSelectedOptions( $varValue, $arrField['options'] );

                return is_array( $varValue ) ? implode( ', ', array_map(function ($arrValue){return $arrValue['label'];},$varValue) ) : $varValue;

                break;
        }

        return $arrField['value'];
    }


    protected function getSelectedOptions( $arrValues, $arrOptions ) {

        $arrReturn = [];

        if ( !is_array( $arrOptions ) || !is_array( $arrValues ) ) {

            return [];
        }

        foreach ( $arrOptions as $arrValue ) {

            if ( in_array( $arrValue['value'], $arrValues ) ) {

                $arrReturn[] = $arrValue;
            }
        }

        return $arrReturn;
    }
}