<?php
$tpl = $modx->getOption('tpl', $scriptProperties, 'row.menucreater');
$outerTpl = $modx->getOption('outerTpl', $scriptProperties, 'outer.menucreater');
$ulClass = $modx->getOption('ulClass', $scriptProperties, 'menu');
$classes = $ulClass ? ' class="'.$ulClass.'"' : "";
$showHideMenu = $modx->getOption('showHideMenu', $scriptProperties, 0);
$showSubMenu = $modx->getOption('showSubMenu', $scriptProperties, 0);
$outputSeparator = $modx->getOption('outputSeparator', $scriptProperties, PHP_EOL);

$where = array('hidemenu' => 0, 'published' => 1, 'parent' => 0);
if (isset($showHideMenu) && $showHideMenu == 1) {
    $where['hidemenu'] = 1;
}

$resources = $modx->getCollection('modResource',$where);

if ($showSubMenu == 1) {
    $parentIDs = array();
    foreach ($resources as $resource) {
        $parentIDs[] = $resource->get('id');
    }
    
    unset($where['parent']);
    $where['parent:IN'] = $parentIDs;
    $subresources = $modx->getCollection('modResource', $where);
    
    $parents = array();
    foreach ($subresources as $subresource) {
        $parentID = $subresource->get('parent');
        $parents[$parentID][] = $subresource;
    }
}

$output = array();
foreach ($resources as $resource) {
    $resArr = $resource->toArray();

    if (isset($parents) && isset($parents[$resArr['id']]) && is_array($parents[$resArr['id']])) {
        $subOutput = array();
        $subresources = $parents[$resArr['id']];

        foreach ($subresources as $subresource) {
            $subresArr = $subresource->toArray();
            $subOutput[] = $modx->getChunk($tpl,$subresArr);
        }
    }
    $resArr['output'] = implode($outputSeparator, $subOutput);
    $output[] = $modx->getChunk($tpl,$resArr);
}
return $modx->getChunk($outerTpl, array('output' => $output, 'classes' => $classes));
