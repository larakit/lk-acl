<?php

namespace Larakit\Acl;

use Illuminate\Support\Str;
use Larakit\Me;

/**
 * Проверка авторизации на выполнение какого-либо действия
 * Вызывается:
 * <img src="http://i.stack.imgur.com/krsp2.png">
 * <code>
 * $reason = Acl::factory($user)->reason('delete');
 * if($reason){
 *      $e = new \Larakit\Exception($reason);
 *      throw $e;
 * }
 * </code>
 *
 * @package Larakit\Base
 */
class Acl {
    
    protected $model;
    
    /**
     * if($reason == AclUser::factory($model)->reason('ban')){
     *   throw new \Exception($reason);
     * }
     *
     * @param $model
     *
     * @return Acl
     */
    static function factory($model, $id = null) {
        if(is_string($model)) {
            $model_class = $model;
            if(!is_null($id)) {
                $model = $model_class::find($id);
            }
        } else {
            $model_class = get_class($model);
        }
        $acl_class = str_replace('\Models\\', '\Acl\\', Str::studly($model_class)) . 'Acl';
        if(!class_exists($acl_class)) {
            $acl_class = __CLASS__;
        }
        
        return new $acl_class($model);
    }
    
    function __construct($model) {
        $this->model = $model;
    }
    
    function only_admin() {
        return !Me::is_admin() ? 'Действие доступно только администратору' : false;
    }
    
    /**
     * Возвращает причину отказа на доступ
     * Если причин нет - возвращает false, что означает наличие доступа
     *
     * @param type $action
     *
     * @return boolean
     */
    function reason($action = 'manage') {
        $callback = [
            $this,
            'reason' . Str::studly($action),
        ];
        if(is_callable($callback)) {
            return call_user_func($callback);
        }
        
        //по умолчанию разрешаем все!
        return false;
    }
    
}
