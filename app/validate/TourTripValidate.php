<?php
namespace app\validate;

class TourTripValidate extends BaseValidate
{
    protected $rule = [
        'pid'    => 'require', 
        'trip_title'     => 'require',
    ];

    protected $message   = [
       'pid' => '商品ID必须',
       'trip_title'  => '标题必须',
    ];

    protected $scene  = [
        'scene_saveadd'     =>  ['pid','trip_title'],
    ];

}
