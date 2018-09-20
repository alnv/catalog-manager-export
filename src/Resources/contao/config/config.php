<?php

$GLOBALS['TL_HOOKS']['catalogManagerModifyBackendModule'][] = [ 'CatalogManager\ExportBundle\Library\Hooks', 'modifyBackendModule' ];
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = [ 'export.library.hooks', 'setExport' ];