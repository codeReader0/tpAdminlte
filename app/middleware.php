<?php

use think\middleware\SessionInit;

return [
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    // 多语言加载
    // \think\middleware\LoadLangPack::class,
    // Session初始化
    SessionInit::class,
    // 跨域
    // \app\common\behavior\CrossDomainCrossDomain::class,
];
