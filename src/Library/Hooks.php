<?php

namespace CatalogManager\ExportBundle\Library;

use Contao\ArrayUtil;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

class Hooks
{


    public function setExport($strName)
    {

        if (!System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
            return null;
        }

        if (!isset($GLOBALS['TL_CATALOG_MANAGER']) || !is_array($GLOBALS['TL_CATALOG_MANAGER'])) {
            return null;
        }

        if (isset($GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strName])) {

            if ($GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strName]['useExport']) {

                ArrayUtil::arrayInsert($GLOBALS['TL_DCA'][$strName]['list']['global_operations'], 0, [
                    'export' => [
                        'icon' => 'tablewizard.svg',
                        'attributes' => 'onclick="Backend.getScrollOffset()"',
                        'href' => 'table=tl_catalog_export&destination=' . $strName,
                        'label' => &$GLOBALS['TL_LANG']['MOD']['catalog-manager-export']
                    ]
                ]);
            }
        }
    }

    public function modifyBackendModule(&$arrModule, $arrCatalog): void
    {
        if ($arrCatalog['useExport'] || $this->exportUsedInChildrenTables($arrCatalog['cTables'])) {
            $arrModule['tables'][] = 'tl_catalog_export';
        }
    }

    protected function exportUsedInChildrenTables($arrTables): bool
    {

        if (empty($arrTables)) {
            return false;
        }

        foreach ($arrTables as $strTable) {

            if ($GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strTable]['useExport']) {
                return true;
            }

            if (!empty($GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strTable]['cTables'])) {
                return $this->exportUsedInChildrenTables($GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strTable]['cTables']);
            }
        }

        return false;
    }
}