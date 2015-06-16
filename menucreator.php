<?php
ini_set('display_errors', -1);
error_reporting(E_ALL);

//[[!devmenucreater? &tpl=`row.menucreater` &outerTpl=`outer.menucreator`]] Базовый запуск сниппета
//$tpl - чанк для вывода меню. Если он пустой, меню не выводится. Переделать на вывод базового меню <ul> <li> <a> </li> </ul>
//$outerTpl - чанк для обёртки вывода
//$ulClass - класс для ul-контейнера меню
//$showHideMenu - показ скрытых пунктов меню. Возможные значения только 1. Вывести ошибку в логи, если значение другое
//$showSubMenu - показ пунктов подменю Возможные значения только 1. Вывести ошибку в логи, если значение другое

$hidemenu = FALSE;
$where = array('hidemenu'=>$hidemenu, 'published' => true, 'parent' => 0); //Стандартное условие для получения ресурсов

if (isset($showHideMenu) && $showHideMenu == 1) //Показываем скрытые пункты меню
{
    unset($where['hidemenu']);
}
$output = "";
$resources = $modx->getCollection('modResource',$where); //загружаем все ресурсы
if (isset($showSubMenu) && $showSubMenu == 1) //Если показываем подменю
{
        $parentIDs = array(); // Сюда запишем все ID-шники первого уровня
        foreach ($resources as $resource)
        {
            $parentIDs[] = $resource->get('id');
        }
        // Составляем запрос, чтобы выбрать ресурсы второго уровня
        $subwhere = array('hidemenu'=>$hidemenu, 'published' => true, 'parent:IN'=>$parentIDs);
        $subresources = $modx->getCollection('modResource',$subwhere);
        $parents = array(); // Здесь будем хранить уже дерево ресурсов, разбитых на группы по parent
        foreach ($subresources as $subresource)
        {
            $parentID = $subresource->get('parent');
            $parents[$parentID][] = $subresource;
        }
}
if (isset($tpl) && $tpl) //Если в вызове сниппета задан шаблон
{
        $template = $modx->getOption('tpl',$scriptProperties,$tpl); //загружаем наш чанк
        foreach ($resources as $resource)
        {
            $resourceArray = $resource->toArray(); //забираем все параметры ресурса для передачи их в чанк
            // Проверяем, были ли найдены дочерние ресурсы
            if (isset($parents) && isset($parents[$resourceArray['id']]) && is_array($parents[$resourceArray['id']]))
            {
                $subOutput = "";
                $subresources = $parents[$resourceArray['id']];
                foreach ($subresources as $subresource)
                {
                    $subresourceArray = $subresource->toArray();
                    $subOutput .= $modx->getChunk($template,$subresourceArray);
                }
            }
            $resourceArray['output'] = $subOutput;
            $output .= $modx->getChunk($template,$resourceArray);
        }
    }
}
else //Если в вызове сниппета не задан шаблон
{
    $output = 'empty tpl';
}

if (isset($ulClass) && $ulClass) //Если есть класс для ul обертки меню
{
    $classes = ' class="'.$ulClass.'"';
}
else //Если нет класса для ul обертки меню
{
    $classes = "";
}
return $modx->getChunk($outerTpl, array('output' => $output, 'classes' => $classes));
