<?php
// require_once '../../maincore.php';
require_once __DIR__.'/../../../models/routes.php';


// // echo json_encode($routes);


function getMenu($key = "name", $routes = 0, $parent = 0, $name = 'Root') { 
    if($routes == 0) $routes = getRoutes();
    $children = array_filter($routes, function($route) use($parent) {
        return $route['parent'] == $parent;
    });
    $res = [];
    $res['id'] = $parent;
    $res[$key] = $name;
    if(count($children) > 0) {
        $res['children'] = array_map(function($record) use ($routes, $key) {
            $result = [];
            $result['id'] = $record['id'];
            $result[$key] = $record['name'];
            $children = array_filter($routes, function($route) use($result, $key) {
                return $route['parent'] == $result['id'];
            });
            if(count($children)) {
                $result['children'] = getMenu($key, $routes, $record['id'], $record['name']);
            }
            return $result;
        }, $children);
    }
    return $res;
}



// function test($routes = 0, $parent = 0, $name = 'Root', $result = []) { 
//         if($routes == 0) $routes = getRoutes();
//         $children = array_filter($routes, function($route) use ($parent) {
//             return $route['parent'] == $parent;
//         });
//         if(count($children) === 0) {
            
//         }
        
// }