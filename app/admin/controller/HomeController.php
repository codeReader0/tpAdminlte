<?php

namespace app\admin\controller;

class HomeController extends AuthController
{
    public function index()
    {
        return $this->fetch();
    }
}
